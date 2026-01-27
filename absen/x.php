<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Karyawan - Multi Mesin 2025</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; background: white; }
        .container { max-width: 1400px; margin: 0 auto; padding: 10px; }
        h1 { margin: 10px 0; font-size: 20px; }
        h3 { margin: 15px 0 10px 0; font-size: 16px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .month-buttons { display: flex; flex-wrap: wrap; gap: 5px; margin: 10px 0; }
        .month-buttons button { padding: 5px 10px; border: 1px solid #999; background: white; cursor: pointer; }
        .month-buttons button.active { background: #000; color: white; }
        .file-section { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; margin: 10px 0; }
        .file-input-group { padding: 10px; border: 1px solid #ddd; }
        .file-input-group label { font-weight: bold; }
        .file-input-group button { margin: 5px 0; padding: 5px 10px; border: 1px solid #999; background: white; cursor: pointer; }
        .file-input-group button:disabled { opacity: 0.5; cursor: not-allowed; }
        .process-button { background: #000; color: white; padding: 8px 20px; border: none; cursor: pointer; margin: 10px 0; }
        .process-button:disabled { background: #999; }
        .search-section { margin: 10px 0; padding: 10px; border: 1px solid #ddd; }
        .search-controls { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .search-input { padding: 5px; border: 1px solid #999; min-width: 200px; }
        .filter-select { padding: 5px; border: 1px solid #999; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 10px 0; }
        .stat-card { background: #f5f5f5; border: 1px solid #ddd; padding: 10px; text-align: center; }
        .stat-card h4 { margin: 0 0 5px 0; font-size: 12px; }
        .stat-card .stat-number { font-size: 20px; font-weight: bold; }
        .save-section { margin: 10px 0; text-align: center; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; }
        .save-section button { background: #000; color: white; border: none; padding: 10px 20px; margin: 5px; cursor: pointer; font-size: 14px; font-weight: bold; }
        .save-section button:hover { background: #333; }
        .load-section { margin: 10px 0; padding: 10px; background: #e8f4fd; border: 1px solid #74b9ff; }
        .load-section h4 { margin: 0 0 10px 0; font-size: 14px; }
        .saved-files { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-top: 10px; }
        .file-item { padding: 10px; background: white; border: 1px solid #ddd; cursor: pointer; }
        .file-item:hover { background: #f0f0f0; }
        .file-item .file-name { font-weight: bold; font-size: 12px; }
        .file-item .file-date { font-size: 11px; color: #666; }
        .results-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        .results-table th { background: #f5f5f5; border: 1px solid #ddd; padding: 8px; text-align: left; }
        .results-table td { padding: 6px; border: 1px solid #ddd; vertical-align: top; }
        .attendance-detail { max-width: 500px; font-size: 11px; max-height: 300px; overflow-y: auto; }
        .penalty-info { color: red; font-weight: bold; }
        .day-info { margin-bottom: 5px; padding: 3px 5px; background: #f9f9f9; border-left: 2px solid #999; }
        .time-entry { margin-left: 8px; }
        .toggle-btn { background: white; border: 1px solid #999; padding: 3px 6px; cursor: pointer; font-size: 10px; }
        .summary-row { background: #f5f5f5 !important; font-weight: bold; }
        .loading { text-align: center; padding: 20px; }
        .alert { padding: 10px; margin: 10px 0; border: 1px solid #ddd; }
        .alert-warning { background: #fffacd; }
        .alert-success { background: #d4edda; }
        .alert-error { background: #f8d7da; }
        .alert-info { background: #d1ecf1; }
        .pagination { display: flex; justify-content: center; gap: 5px; margin: 10px 0; }
        .pagination button { padding: 5px 10px; border: 1px solid #999; background: white; cursor: pointer; }
        .pagination button.active { background: #000; color: white; }
        .pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
        .permit-badge { display: inline-block; padding: 2px 6px; margin-left: 5px; font-size: 10px; border-radius: 3px; font-weight: bold; }
        .permit-full { background: #74b9ff; color: white; }
        .permit-arrival { background: #fdcb6e; color: white; }
        .permit-departure { background: #e17055; color: white; }
        .waived-penalty { color: #999; text-decoration: line-through; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sistem Absensi Karyawan - Multi Mesin 2025</h1>
        
        <div id="configStatus" class="alert alert-warning">
            <h3>Memuat konfigurasi sistem...</h3>
            <p>Mencari file config.json...</p>
        </div>
        
        <div id="mainContent" style="display:none;">
            <div class="load-section">
                <h4>📂 Data Tersimpan</h4>
                <button onclick="loadSavedFiles()" style="padding: 5px 15px; background: #74b9ff; color: white; border: none; cursor: pointer;">Refresh Daftar</button>
                <div id="savedFilesList" class="saved-files"></div>
            </div>

            <div>
                <h3>Pilih Bulan untuk Data Absensi</h3>
                <div class="month-buttons">
                    <button data-month="1">Januari</button>
                    <button data-month="2">Februari</button>
                    <button data-month="3">Maret</button>
                    <button data-month="4">April</button>
                    <button data-month="5">Mei</button>
                    <button data-month="6">Juni</button>
                    <button data-month="7">Juli</button>
                    <button data-month="8">Agustus</button>
                    <button data-month="9">September</button>
                    <button data-month="10">Oktober</button>
                    <button data-month="11">November</button>
                    <button data-month="12">Desember</button>
                </div>
                <div id="selectedMonthInfo" style="display:none;">
                    <strong>Bulan Terpilih: <span id="monthName">-</span> <span id="monthYear">2025</span></strong>
                </div>
            </div>
            
            <div id="warningMessage" class="alert alert-warning">
                Silakan pilih bulan terlebih dahulu sebelum upload file
            </div>
            
            <div id="fileSection" style="display:none;">
                <h3>Upload File dari Setiap Mesin Absensi</h3>
                
                <div class="file-section">
                    <div class="file-input-group">
                        <label>Mesin A:</label>
                        <input type="file" id="fileInputA" accept=".xlsx,.xls" style="display:none;" disabled />
                        <button id="buttonA" disabled onclick="selectFile('A')">Pilih File Mesin A</button>
                        <div id="statusA">Belum ada file</div>
                    </div>
                    
                    <div class="file-input-group">
                        <label>Mesin B:</label>
                        <input type="file" id="fileInputB" accept=".xlsx,.xls" style="display:none;" disabled />
                        <button id="buttonB" disabled onclick="selectFile('B')">Pilih File Mesin B</button>
                        <div id="statusB">Belum ada file</div>
                    </div>
                    
                    <div class="file-input-group">
                        <label>Mesin C:</label>
                        <input type="file" id="fileInputC" accept=".xlsx,.xls" style="display:none;" disabled />
                        <button id="buttonC" disabled onclick="selectFile('C')">Pilih File Mesin C</button>
                        <div id="statusC">Belum ada file</div>
                    </div>
                    
                    <div class="file-input-group">
                        <label>Mesin D:</label>
                        <input type="file" id="fileInputD" accept=".xlsx,.xls" style="display:none;" disabled />
                        <button id="buttonD" disabled onclick="selectFile('D')">Pilih File Mesin D</button>
                        <div id="statusD">Belum ada file</div>
                    </div>
                </div>
                
                <button class="process-button" id="processButton" disabled onclick="processAllFiles()">
                    Proses Semua File
                </button>
            </div>
            
            <div id="loading" class="loading" style="display:none;">
                <p>Memproses dan menggabungkan file...</p>
            </div>
            
            <div id="summary" style="display:none;">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h4>Total Karyawan</h4>
                        <div class="stat-number" id="totalEmployees">-</div>
                    </div>
                    <div class="stat-card">
                        <h4>File Diproses</h4>
                        <div class="stat-number" id="filesProcessed">-</div>
                    </div>
                    <div class="stat-card">
                        <h4>Total Record</h4>
                        <div class="stat-number" id="totalRecords">-</div>
                    </div>
                    <div class="stat-card">
                        <h4>Total Denda</h4>
                        <div class="stat-number" id="totalPenalties">-</div>
                    </div>
                </div>
            </div>

            <div id="saveSection" class="save-section" style="display:none;">
                <h3>💾 Simpan Data</h3>
                <p style="margin: 5px 0; font-size: 13px;">Data akan otomatis tersimpan ke folder <strong>/dataperbulan/</strong></p>
                <button onclick="saveToJSON()">💾 Simpan Data ke Server</button>
            </div>

            <div id="searchSection" class="search-section" style="display:none;">
                <h3>Pencarian dan Filter Data</h3>
                <div class="search-controls">
                    <input type="text" id="searchInput" class="search-input" placeholder="Cari berdasarkan ID, Nama, atau Divisi...">
                    <select id="divisionFilter" class="filter-select">
                        <option value="">Semua Divisi</option>
                    </select>
                    <select id="penaltyFilter" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="with-penalty">Ada Denda</option>
                        <option value="no-penalty">Tanpa Denda</option>
                    </select>
                    <button onclick="clearFilters()" style="padding: 5px 10px; border: 1px solid #999; background: white; cursor: pointer;">Reset</button>
                    <button onclick="openPermitModal()" style="padding: 5px 10px; border: 1px solid #999; background: white; cursor: pointer;">Kelola Izin</button>
                </div>
                <div style="margin-top: 10px;">
                    <span id="searchResults">Menampilkan semua data</span>
                </div>
            </div>

            <div id="permitModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; border: 1px solid #999;">
                    <h3>Kelola Data Izin Karyawan</h3>
                    <div style="margin: 15px 0;">
                        <label><strong>ID Karyawan:</strong></label>
                        <input type="text" id="permitEmployeeId" style="width: 100%; padding: 5px; border: 1px solid #999; margin-top: 5px;">
                    </div>
                    <div style="margin: 15px 0;">
                        <label><strong>Tanggal Izin:</strong></label>
                        <input type="number" id="permitDate" min="1" max="31" placeholder="1-31" style="width: 100%; padding: 5px; border: 1px solid #999; margin-top: 5px;">
                    </div>
                    <div style="margin: 15px 0;">
                        <label><strong>Jenis Izin:</strong></label>
                        <select id="permitType" style="width: 100%; padding: 5px; border: 1px solid #999; margin-top: 5px;">
                            <option value="full">Izin Satu Hari Penuh</option>
                            <option value="arrival">Izin Kedatangan Saja (Tidak Perlu Absen Datang)</option>
                            <option value="departure">Izin Kepulangan Saja (Tidak Perlu Absen Pulang)</option>
                        </select>
                        <div style="font-size: 11px; color: #666; margin-top: 5px; padding: 5px; background: #f9f9f9;">
                            <strong>Keterangan:</strong><br>
                            • <strong>Izin Penuh:</strong> Tidak perlu absen datang dan pulang (bebas denda hari itu)<br>
                            • <strong>Izin Kedatangan:</strong> Tidak perlu absen datang, tapi harus absen pulang<br>
                            • <strong>Izin Kepulangan:</strong> Harus absen datang, tapi tidak perlu absen pulang
                        </div>
                    </div>
                    <div style="margin: 15px 0;">
                        <label><strong>Keterangan (opsional):</strong></label>
                        <input type="text" id="permitReason" placeholder="Sakit, Cuti, Keperluan Keluarga, dll" style="width: 100%; padding: 5px; border: 1px solid #999; margin-top: 5px;">
                    </div>
                    <div style="margin: 15px 0; text-align: center;">
                        <button onclick="addPermit()" style="padding: 8px 15px; background: #000; color: white; border: none; cursor: pointer; margin-right: 5px;">Tambah Izin</button>
                        <button onclick="closePermitModal()" style="padding: 8px 15px; border: 1px solid #999; background: white; cursor: pointer;">Tutup</button>
                    </div>
                    <hr style="margin: 20px 0;">
                    <h4>Daftar Izin untuk <span id="permitMonthName"></span>:</h4>
                    <div id="permitList" style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-top: 10px;">
                        <p style="color: #999; text-align: center;">Belum ada data izin</p>
                    </div>
                </div>
            </div>
            
            <div id="results" style="display:none;">
                <h3>Data Absensi Gabungan</h3>
                <div class="pagination" id="paginationTop" style="display:none;"></div>
                <div style="overflow-x: auto;">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th width="60">ID</th>
                                <th width="120">Divisi</th>
                                <th width="200">Nama</th>
                                <th width="400">Detail Kehadiran per Tanggal</th>
                                <th width="80">Total Hari Hadir</th>
                                <th width="120">Total Denda</th>
                            </tr>
                        </thead>
                        <tbody id="resultsTable">
                        </tbody>
                    </table>
                </div>
                <div class="pagination" id="paginationBottom" style="display:none;"></div>
            </div>
        </div>
    </div>

    <script>
        let config = null;
        let configLoaded = false;
        let processedData = null;
        let originalEmployeesArray = [];
        let filteredEmployeesArray = [];
        let currentPage = 1;
        let recordsPerPage = 20;
        let permitData = {};
        let savedDataVersion = "1.0";

        let filesData = {A: null, B: null, C: null, D: null};
        let selectedMonth = null;
        let selectedYear = 2025;
        
        const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        function loadConfigFromFile() {
            const statusDiv = document.getElementById('configStatus');
            fetch('./config.json')
                .then(response => {
                    if (!response.ok) throw new Error('Config file tidak ditemukan');
                    return response.json();
                })
                .then(newConfig => {
                    config = newConfig;
                    configLoaded = true;
                    statusDiv.className = 'alert alert-success';
                    statusDiv.innerHTML = '<h3>Konfigurasi Berhasil Dimuat</h3><p>Sistem siap digunakan - Jadwal kerja telah dikonfigurasi</p>';
                    enableApplication();
                    loadPermitDataFromStorage();
                    loadSavedFiles();
                })
                .catch(error => {
                    statusDiv.className = 'alert alert-error';
                    statusDiv.innerHTML = '<h3>Error Memuat Konfigurasi</h3><p>File config.json tidak ditemukan di direktori yang sama dengan file HTML ini.</p><p>Pastikan file config.json berada di folder yang sama.</p>';
                });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            loadConfigFromFile();
            setupEventListeners();
        });

        function enableApplication() {
            document.getElementById('mainContent').style.display = 'block';
        }

        function setupEventListeners() {
            document.querySelectorAll('button[data-month]').forEach(button => {
                button.addEventListener('click', function() {
                    if (!checkConfigRequired()) return;
                    document.querySelectorAll('button[data-month]').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    selectedMonth = parseInt(this.getAttribute('data-month'));
                    document.getElementById('monthName').textContent = monthNames[selectedMonth];
                    document.getElementById('selectedMonthInfo').style.display = 'block';
                    enableFileSection();
                    document.getElementById('warningMessage').style.display = 'none';
                });
            });

            ['A', 'B', 'C', 'D'].forEach(machine => {
                document.getElementById(`fileInput${machine}`).addEventListener('change', (event) => {
                    handleFileUpload(event, machine);
                });
            });

            document.getElementById('searchInput').addEventListener('input', applyFilters);
            document.getElementById('divisionFilter').addEventListener('change', applyFilters);
            document.getElementById('penaltyFilter').addEventListener('change', applyFilters);
        }

        function checkConfigRequired() {
            if (!configLoaded || !config) {
                alert('Konfigurasi sistem belum dimuat. Pastikan file config.json berada di direktori yang sama dengan file HTML ini.');
                return false;
            }
            return true;
        }

        // ========== FUNGSI SIMPAN & MUAT JSON ==========
        
        function saveToJSON() {
            if (!processedData || !selectedMonth) {
                alert('Tidak ada data untuk disimpan. Proses file terlebih dahulu!');
                return;
            }

            const dataToSave = {
                version: savedDataVersion,
                savedDate: new Date().toISOString(),
                month: selectedMonth,
                year: selectedYear,
                monthName: monthNames[selectedMonth],
                processedData: processedData,
                permitData: permitData,
                originalEmployeesArray: originalEmployeesArray,
                stats: {
                    totalEmployees: processedData.stats.totalEmployees,
                    filesProcessed: processedData.stats.filesProcessed,
                    totalRecords: processedData.stats.totalRecords
                }
            };

            const jsonString = JSON.stringify(dataToSave);
            
            // Kirim ke server PHP
            fetch('save_json.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    filename: `Absensi_${monthNames[selectedMonth]}_${selectedYear}.json`,
                    data: jsonString
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showNotification('✅ Data berhasil disimpan ke server!', 'success');
                    loadSavedFiles(); // Refresh daftar file
                } else {
                    showNotification('❌ Error: ' + result.message, 'error');
                }
            })
            .catch(error => {
                showNotification('❌ Error menyimpan data: ' + error.message, 'error');
            });
        }

        function loadSavedFiles() {
            fetch('list_json.php')
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        displaySavedFiles(result.files);
                    } else {
                        document.getElementById('savedFilesList').innerHTML = '<p style="text-align:center; color:#999;">Belum ada data tersimpan</p>';
                    }
                })
                .catch(error => {
                    console.error('Error loading saved files:', error);
                });
        }

        function displaySavedFiles(files) {
            const container = document.getElementById('savedFilesList');
            if (files.length === 0) {
                container.innerHTML = '<p style="text-align:center; color:#999;">Belum ada data tersimpan</p>';
                return;
            }

            let html = '';
            files.forEach(file => {
                html += `
                    <div class="file-item" onclick="loadFromServer('${file.filename}')">
                        <div class="file-name">${file.filename}</div>
                        <div class="file-date">📅 ${file.date}</div>
                        <div class="file-date">💾 ${file.size}</div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        function loadFromServer(filename) {
            if (!confirm(`Load data dari ${filename}?`)) return;

            fetch('load_json.php?filename=' + encodeURIComponent(filename))
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const loadedData = JSON.parse(result.data);
                        
                        if (!loadedData.version || !loadedData.processedData) {
                            throw new Error('Format JSON tidak valid');
                        }

                        selectedMonth = loadedData.month;
                        selectedYear = loadedData.year;
                        processedData = loadedData.processedData;
                        permitData = loadedData.permitData || {};
                        originalEmployeesArray = loadedData.originalEmployeesArray || [];

                        document.querySelectorAll('button[data-month]').forEach(b => b.classList.remove('active'));
                        const monthButton = document.querySelector(`button[data-month="${selectedMonth}"]`);
                        if (monthButton) monthButton.classList.add('active');
                        
                        document.getElementById('monthName').textContent = monthNames[selectedMonth];
                        document.getElementById('selectedMonthInfo').style.display = 'block';

                        setupFilterOptions(processedData);
                        displayCombinedResults(processedData);
                        
                        document.getElementById('summary').style.display = 'block';
                        document.getElementById('saveSection').style.display = 'block';
                        document.getElementById('searchSection').style.display = 'block';
                        document.getElementById('results').style.display = 'block';

                        showNotification(`✅ Data ${loadedData.monthName} ${loadedData.year} berhasil dimuat!`, 'success');
                    } else {
                        showNotification('❌ Error: ' + result.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('❌ Error membaca file: ' + error.message, 'error');
                });
        }

        function autoSavePermitData() {
            try {
                const permitDataStr = JSON.stringify(permitData);
                localStorage.setItem('absensi_permit_data', permitDataStr);
            } catch (error) {
                console.error('Error auto-save permit data:', error);
            }
        }

        function loadPermitDataFromStorage() {
            try {
                const savedPermitData = localStorage.getItem('absensi_permit_data');
                if (savedPermitData) {
                    permitData = JSON.parse(savedPermitData);
                    console.log('Permit data loaded from storage');
                }
            } catch (error) {
                console.error('Error loading permit data:', error);
                permitData = {};
            }
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '10000';
            notification.style.maxWidth = '400px';
            notification.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
            notification.innerHTML = `<strong>${message}</strong>`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transition = 'opacity 0.5s';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }

        // ========== FUNGSI SISTEM LAINNYA (sama seperti sebelumnya) ==========

        function getAllHolidays() {
            if (!config || !config.holidays) return [];
            const allHolidays = [];
            for (const key in config.holidays) {
                if (key !== '2025' && config.holidays[key]) {
                    const holiday = config.holidays[key];
                    if (holiday.date && holiday.name) allHolidays.push(holiday);
                }
            }
            if (config.holidays['2025'] && Array.isArray(config.holidays['2025'])) {
                allHolidays.push(...config.holidays['2025']);
            }
            return allHolidays;
        }

        function isHoliday(date, month, year) {
            const dateStr = `${year}-${month.toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}`;
            const holidays = getAllHolidays();
            for (const holiday of holidays) {
                if (holiday.date === dateStr) return holiday;
            }