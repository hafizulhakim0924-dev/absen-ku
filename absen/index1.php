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
        .export-section { margin: 10px 0; text-align: center; }
        .export-section button { background: white; border: 1px solid #999; padding: 8px 15px; margin: 3px; cursor: pointer; }
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
        .pagination { display: flex; justify-content: center; gap: 5px; margin: 10px 0; }
        .pagination button { padding: 5px 10px; border: 1px solid #999; background: white; cursor: pointer; }
        .pagination button.active { background: #000; color: white; }
        .pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
        .permit-badge { display: inline-block; padding: 2px 6px; margin-left: 5px; font-size: 10px; border-radius: 3px; font-weight: bold; }
        .permit-full { background: #74b9ff; color: white; }
        .permit-arrival { background: #fdcb6e; color: white; }
        .permit-departure { background: #e17055; color: white; }
        .waived-penalty { color: #999; text-decoration: line-through; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; border: 1px solid #999; }
        .history-item { padding: 12px; border: 1px solid #ddd; margin: 8px 0; background: #f9f9f9; cursor: pointer; transition: background 0.2s; }
        .history-item:hover { background: #e8f4f8; }
        .history-item-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .history-item-title { font-weight: bold; font-size: 14px; }
        .history-item-date { font-size: 11px; color: #666; }
        .history-item-stats { font-size: 12px; color: #444; }
        .history-actions { margin-top: 8px; }
        .history-actions button { padding: 4px 8px; font-size: 11px; margin-right: 5px; border: 1px solid #999; background: white; cursor: pointer; }
        .history-actions button.delete { color: red; }
        .save-json-btn { background: #27ae60 !important; color: white !important; }
        .tab-buttons { display: flex; gap: 5px; margin: 10px 0; border-bottom: 2px solid #ddd; }
        .tab-buttons button { padding: 8px 15px; border: none; background: white; cursor: pointer; border-bottom: 3px solid transparent; }
        .tab-buttons button.active { border-bottom-color: #000; font-weight: bold; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sistem Absensi Karyawan - Multi Mesin 2025</h1>
        
        <div class="tab-buttons">
            <button class="active" onclick="switchTab('input')">Input Data</button>
            <button onclick="switchTab('history')">Riwayat</button>
        </div>
        
        <div id="inputTab" class="tab-content active">
            <div id="configStatus" class="alert alert-warning">
                <h3>Memuat konfigurasi sistem...</h3>
                <p>Mencari file config.json...</p>
            </div>
            
            <div id="mainContent" style="display:none;">
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

                <div id="permitModal" class="modal">
                    <div class="modal-content">
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

                <div id="exportSection" class="export-section" style="display:none;">
                    <h3>Export dan Simpan Data</h3>
                    <button onclick="exportToExcel('detailed')">Export Detail Kehadiran (Excel)</button>
                    <button onclick="exportToExcel('summary')">Export Ringkasan Denda (Excel)</button>
                    <button onclick="exportToExcel('filtered')" id="exportFilteredBtn" style="display:none;">Export Data Terfilter (Excel)</button>
                    <button class="save-json-btn" onclick="saveToJSON()">💾 Simpan ke Riwayat (JSON)</button>
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
        
        <div id="historyTab" class="tab-content">
            <h3>Riwayat Data Absensi</h3>
            <div id="historyList" style="margin: 20px 0;">
                <p style="text-align: center; color: #999; padding: 40px;">Belum ada riwayat data tersimpan</p>
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
        let savedHistory = {};

        let filesData = {A: null, B: null, C: null, D: null};
        let selectedMonth = null;
        let selectedYear = 2025;
        
        const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        function switchTab(tabName) {
            document.querySelectorAll('.tab-buttons button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            if (tabName === 'input') {
                document.querySelector('.tab-buttons button:first-child').classList.add('active');
                document.getElementById('inputTab').classList.add('active');
            } else if (tabName === 'history') {
                document.querySelector('.tab-buttons button:last-child').classList.add('active');
                document.getElementById('historyTab').classList.add('active');
                loadHistory();
            }
        }

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
                    loadSavedHistory();
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

        function loadSavedHistory() {
            const saved = localStorage.getItem('attendanceHistory');
            if (saved) {
                try {
                    savedHistory = JSON.parse(saved);
                } catch (e) {
                    savedHistory = {};
                }
            }
        }

        function saveToJSON() {
            if (!processedData || !selectedMonth) {
                alert('Tidak ada data untuk disimpan. Proses file terlebih dahulu.');
                return;
            }

            const historyKey = `${selectedYear}-${selectedMonth.toString().padStart(2, '0')}`;
            const timestamp = new Date().toISOString();
            
            const dataToSave = {
                month: selectedMonth,
                year: selectedYear,
                monthName: monthNames[selectedMonth],
                timestamp: timestamp,
                displayDate: new Date().toLocaleString('id-ID'),
                processedData: processedData,
                originalEmployeesArray: originalEmployeesArray,
                permitData: Object.values(permitData).filter(p => p.month === selectedMonth && p.year === selectedYear),
                stats: {
                    totalEmployees: processedData.stats.totalEmployees,
                    filesProcessed: processedData.stats.filesProcessed,
                    totalRecords: processedData.stats.totalRecords,
                    totalPenalties: originalEmployeesArray.reduce((sum, emp) => sum + emp.totalPenalty, 0)
                }
            };

            savedHistory[historyKey] = dataToSave;
            localStorage.setItem('attendanceHistory', JSON.stringify(savedHistory));

            const blob = new Blob([JSON.stringify(dataToSave, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Absensi_${monthNames[selectedMonth]}_${selectedYear}.json`;
            a.click();
            URL.revokeObjectURL(url);

            alert(`Data berhasil disimpan ke riwayat!\n\n${monthNames[selectedMonth]} ${selectedYear}\nTotal Karyawan: ${dataToSave.stats.totalEmployees}\nTotal Denda: ${formatCurrency(dataToSave.stats.totalPenalties)}`);
        }

        function loadHistory() {
            const historyList = document.getElementById('historyList');
            const historyKeys = Object.keys(savedHistory).sort().reverse();

            if (historyKeys.length === 0) {
                historyList.innerHTML = '<p style="text-align: center; color: #999; padding: 40px;">Belum ada riwayat data tersimpan</p>';
                return;
            }

            let html = '';
            historyKeys.forEach(key => {
                const data = savedHistory[key];
                html += `
                    <div class="history-item">
                        <div class="history-item-header">
                            <div class="history-item-title">📊 Absensi ${data.monthName} ${data.year}</div>
                            <div class="history-item-date">Disimpan: ${data.displayDate}</div>
                        </div>
                        <div class="history-item-stats">
                            👥 ${data.stats.totalEmployees} Karyawan | 
                            📁 ${data.stats.filesProcessed} File | 
                            📝 ${data.stats.totalRecords} Record | 
                            💰 Total Denda: ${formatCurrency(data.stats.totalPenalties)}
                        </div>
                        <div class="history-actions">
                            <button onclick="loadHistoryData('${key}')">📂 Muat Data</button>
                            <button onclick="downloadHistoryJSON('${key}')">💾 Download JSON</button>
                            <button onclick="exportHistoryToExcel('${key}', 'summary')">📊 Export Excel (Ringkasan)</button>
                            <button onclick="exportHistoryToExcel('${key}', 'detailed')">📋 Export Excel (Detail)</button>
                            <button class="delete" onclick="deleteHistory('${key}')">🗑️ Hapus</button>
                        </div>
                    </div>
                `;
            });

            historyList.innerHTML = html;
        }

        function loadHistoryData(historyKey) {
            const data = savedHistory[historyKey];
            if (!data) {
                alert('Data tidak ditemukan!');
                return;
            }

            selectedMonth = data.month;
            selectedYear = data.year;
            processedData = data.processedData;
            originalEmployeesArray = data.originalEmployeesArray;
            
            permitData = {};
            data.permitData.forEach(permit => {
                const key = `${permit.id}-${permit.date}`;
                permitData[key] = permit;
            });

            document.querySelectorAll('button[data-month]').forEach(b => b.classList.remove('active'));
            const monthButton = document.querySelector(`button[data-month="${selectedMonth}"]`);
            if (monthButton) monthButton.classList.add('active');

            document.getElementById('monthName').textContent = monthNames[selectedMonth];
            document.getElementById('selectedMonthInfo').style.display = 'block';

            setupFilterOptions(processedData);
            displayCombinedResults(processedData);

            document.getElementById('summary').style.display = 'block';
            document.getElementById('searchSection').style.display = 'block';
            document.getElementById('exportSection').style.display = 'block';
            document.getElementById('results').style.display = 'block';

            switchTab('input');
            
            alert(`Data ${monthNames[selectedMonth]} ${selectedYear} berhasil dimuat dari riwayat!`);
        }

        function downloadHistoryJSON(historyKey) {
            const data = savedHistory[historyKey];
            if (!data) {
                alert('Data tidak ditemukan!');
                return;
            }

            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Absensi_${data.monthName}_${data.year}.json`;
            a.click();
            URL.revokeObjectURL(url);
        }

        function exportHistoryToExcel(historyKey, type) {
            const data = savedHistory[historyKey];
            if (!data) {
                alert('Data tidak ditemukan!');
                return;
            }

            const tempProcessedData = processedData;
            const tempOriginalEmployeesArray = originalEmployeesArray;
            const tempSelectedMonth = selectedMonth;
            const tempSelectedYear = selectedYear;
            const tempPermitData= permitData;

            processedData = data.processedData;
            originalEmployeesArray = data.originalEmployeesArray;
            selectedMonth = data.month;
            selectedYear = data.year;
            permitData = {};
            data.permitData.forEach(permit => {
                const key = `${permit.id}-${permit.date}`;
                permitData[key] = permit;
            });

            exportToExcel(type);

            processedData = tempProcessedData;
            originalEmployeesArray = tempOriginalEmployeesArray;
            selectedMonth = tempSelectedMonth;
            selectedYear = tempSelectedYear;
            permitData = tempPermitData;
        }

        function deleteHistory(historyKey) {
            const data = savedHistory[historyKey];
            if (!data) return;

            if (confirm(`Hapus data ${data.monthName} ${data.year}?\n\nData yang dihapus tidak dapat dikembalikan.`)) {
                delete savedHistory[historyKey];
                localStorage.setItem('attendanceHistory', JSON.stringify(savedHistory));
                loadHistory();
                alert('Data berhasil dihapus dari riwayat!');
            }
        }

        function checkConfigRequired() {
            if (!configLoaded) {
                alert('Konfigurasi belum dimuat. Tunggu hingga config.json berhasil dimuat.');
                return false;
            }
            return true;
        }

        function enableFileSection() {
            document.getElementById('fileSection').style.display = 'block';
            ['A', 'B', 'C', 'D'].forEach(machine => {
                document.getElementById(`fileInput${machine}`).disabled = false;
                document.getElementById(`button${machine}`).disabled = false;
            });
        }

        function selectFile(machine) {
            document.getElementById(`fileInput${machine}`).click();
        }

        function handleFileUpload(event, machine) {
            const file = event.target.files[0];
            if (file) {
                filesData[machine] = file;
                document.getElementById(`status${machine}`).textContent = `✓ ${file.name}`;
                document.getElementById(`status${machine}`).style.color = 'green';
                checkEnableProcessButton();
            }
        }

        function checkEnableProcessButton() {
            const hasAnyFile = Object.values(filesData).some(file => file !== null);
            document.getElementById('processButton').disabled = !hasAnyFile;
        }

        function openPermitModal() {
            if (!selectedMonth) {
                alert('Pilih bulan terlebih dahulu!');
                return;
            }
            document.getElementById('permitMonthName').textContent = `${monthNames[selectedMonth]} ${selectedYear}`;
            displayPermitList();
            document.getElementById('permitModal').style.display = 'block';
        }

        function closePermitModal() {
            document.getElementById('permitModal').style.display = 'none';
            document.getElementById('permitEmployeeId').value = '';
            document.getElementById('permitDate').value = '';
            document.getElementById('permitType').value = 'full';
            document.getElementById('permitReason').value = '';
        }

        function addPermit() {
            const employeeId = document.getElementById('permitEmployeeId').value.trim();
            const dateNum = parseInt(document.getElementById('permitDate').value);
            const permitType = document.getElementById('permitType').value;
            const reason = document.getElementById('permitReason').value.trim();

            if (!employeeId) {
                alert('ID Karyawan harus diisi!');
                return;
            }

            if (!dateNum || dateNum < 1 || dateNum > 31) {
                alert('Tanggal harus antara 1-31!');
                return;
            }

            const key = `${employeeId}-${dateNum}`;
            permitData[key] = {
                id: employeeId,
                date: dateNum,
                month: selectedMonth,
                year: selectedYear,
                type: permitType,
                reason: reason || '-'
            };

            displayPermitList();
            
            if (processedData) {
                recalculateWithPermits();
            }

            document.getElementById('permitEmployeeId').value = '';
            document.getElementById('permitDate').value = '';
            document.getElementById('permitReason').value = '';
            
            alert('Izin berhasil ditambahkan!');
        }

        function removePermit(key) {
            if (confirm('Hapus data izin ini?')) {
                delete permitData[key];
                displayPermitList();
                
                if (processedData) {
                    recalculateWithPermits();
                }
            }
        }

        function displayPermitList() {
            const permitList = document.getElementById('permitList');
            const currentMonthPermits = Object.values(permitData).filter(p => 
                p.month === selectedMonth && p.year === selectedYear
            ).sort((a, b) => a.date - b.date || a.id.localeCompare(b.id));

            if (currentMonthPermits.length === 0) {
                permitList.innerHTML = '<p style="color: #999; text-align: center;">Belum ada data izin</p>';
                return;
            }

            let html = '<div style="font-size: 12px;">';
            currentMonthPermits.forEach(permit => {
                const typeLabel = permit.type === 'full' ? 'Izin Penuh' : 
                                 permit.type === 'arrival' ? 'Izin Kedatangan' : 'Izin Kepulangan';
                const typeColor = permit.type === 'full' ? '#74b9ff' : 
                                 permit.type === 'arrival' ? '#fdcb6e' : '#e17055';
                
                const key = `${permit.id}-${permit.date}`;
                html += `
                    <div style="padding: 8px; border: 1px solid #ddd; margin: 5px 0; background: white;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>${permit.id}</strong> - Tanggal ${permit.date}
                                <span style="display: inline-block; padding: 2px 6px; margin-left: 5px; background: ${typeColor}; color: white; border-radius: 3px; font-size: 10px;">${typeLabel}</span>
                            </div>
                            <button onclick="removePermit('${key}')" style="padding: 3px 8px; background: #ff6b6b; color: white; border: none; cursor: pointer; border-radius: 3px;">Hapus</button>
                        </div>
                        ${permit.reason !== '-' ? `<div style="font-size: 11px; color: #666; margin-top: 3px;">Keterangan: ${permit.reason}</div>` : ''}
                    </div>
                `;
            });
            html += '</div>';
            permitList.innerHTML = html;
        }

        function recalculateWithPermits() {
            originalEmployeesArray.forEach(emp => {
                let totalPenalty = 0;
                Object.keys(emp.attendanceByDate).forEach(dateStr => {
                    const dateNum = parseInt(dateStr);
                    const key = `${emp.id}-${dateNum}`;
                    const permit = permitData[key];
                    const dayData = emp.attendanceByDate[dateStr];

                    if (permit) {
                        if (permit.type === 'full') {
                            dayData.arrivalPenalty = 0;
                            dayData.departurePenalty = 0;
                            dayData.arrivalPenaltyWaived = dayData.originalArrivalPenalty || 0;
                            dayData.departurePenaltyWaived = dayData.originalDeparturePenalty || 0;
                        } else if (permit.type === 'arrival') {
                            dayData.arrivalPenalty = 0;
                            dayData.arrivalPenaltyWaived = dayData.originalArrivalPenalty || 0;
                        } else if (permit.type === 'departure') {
                            dayData.departurePenalty = 0;
                            dayData.departurePenaltyWaived = dayData.originalDeparturePenalty || 0;
                        }
                        dayData.permit = permit;
                    } else {
                        if (dayData.originalArrivalPenalty !== undefined) {
                            dayData.arrivalPenalty = dayData.originalArrivalPenalty;
                        }
                        if (dayData.originalDeparturePenalty !== undefined) {
                            dayData.departurePenalty = dayData.originalDeparturePenalty;
                        }
                        dayData.arrivalPenaltyWaived = 0;
                        dayData.departurePenaltyWaived = 0;
                        delete dayData.permit;
                    }

                    totalPenalty += (dayData.arrivalPenalty || 0) + (dayData.departurePenalty || 0);
                });
                emp.totalPenalty = totalPenalty;
            });

            applyFilters();
            updateStatistics();
        }

        function processAllFiles() {
            if (!checkConfigRequired()) return;
            if (!selectedMonth) {
                alert('Pilih bulan terlebih dahulu!');
                return;
            }

            const filesToProcess = Object.entries(filesData).filter(([, file]) => file !== null);
            
            if (filesToProcess.length === 0) {
                alert('Tidak ada file yang dipilih!');
                return;
            }

            document.getElementById('loading').style.display = 'block';
            document.getElementById('results').style.display = 'none';
            document.getElementById('summary').style.display = 'none';

            const promises = filesToProcess.map(([machine, file]) => {
                return readExcelFile(file, machine);
            });

            Promise.all(promises)
                .then(results => {
                    const combinedData = combineAllData(results);
                    processedData = combinedData;
                    setupFilterOptions(combinedData);
                    displayCombinedResults(combinedData);
                    document.getElementById('loading').style.display = 'none';
                })
                .catch(error => {
                    document.getElementById('loading').style.display = 'none';
                    alert('Error memproses file: ' + error.message);
                });
        }

        function readExcelFile(file, machineName) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        const data = new Uint8Array(e.target.result);
                        const workbook = XLSX.read(data, {type: 'array'});
                        const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                        const jsonData = XLSX.utils.sheet_to_json(firstSheet);
                        resolve({machine: machineName, data: jsonData});
                    } catch (error) {
                        reject(error);
                    }
                };
                reader.onerror = reject;
                reader.readAsArrayBuffer(file);
            });
        }

        function combineAllData(results) {
            const employeesMap = {};
            let totalRecords = 0;

            results.forEach(result => {
                result.data.forEach(row => {
                    const id = String(row['ID'] || row['id'] || '').trim();
                    const name = String(row['Nama'] || row['nama'] || row['Name'] || row['name'] || '').trim();
                    const division = String(row['Divisi'] || row['divisi'] || row['Division'] || row['division'] || 'Tidak Diketahui').trim();
                    const dateValue = row['Tanggal'] || row['tanggal'] || row['Date'] || row['date'];
                    const timeValue = row['Waktu'] || row['waktu'] || row['Time'] || row['time'];

                    if (!id || !dateValue || !timeValue) return;

                    totalRecords++;

                    if (!employeesMap[id]) {
                        employeesMap[id] = {
                            id: id,
                            name: name,
                            division: division,
                            attendanceByDate: {},
                            daysPresent: 0,
                            totalPenalty: 0
                        };
                    }

                    const dateObj = parseExcelDate(dateValue);
                    if (!dateObj || dateObj.getMonth() + 1 !== selectedMonth || dateObj.getFullYear() !== selectedYear) {
                        return;
                    }

                    const dateKey = dateObj.getDate();
                    const timeStr = parseExcelTime(timeValue);

                    if (!employeesMap[id].attendanceByDate[dateKey]) {
                        employeesMap[id].attendanceByDate[dateKey] = {
                            date: dateObj,
                            arrivals: [],
                            departures: []
                        };
                    }

                    const dayOfWeek = dateObj.getDay();
                    const schedule = config.workSchedule[dayOfWeek];

                    if (schedule && schedule.isWorkDay) {
                        const timeMinutes = timeToMinutes(timeStr);
                        const arrivalEnd = timeToMinutes(schedule.arrivalEnd);
                        const departureStart = timeToMinutes(schedule.departureStart);

                        if (timeMinutes <= arrivalEnd + 60) {
                            employeesMap[id].attendanceByDate[dateKey].arrivals.push(timeStr);
                        }
                        if (timeMinutes >= departureStart - 60) {
                            employeesMap[id].attendanceByDate[dateKey].departures.push(timeStr);
                        }
                    }
                });
            });

            Object.values(employeesMap).forEach(emp => {
                Object.keys(emp.attendanceByDate).forEach(dateKey => {
                    const dayData = emp.attendanceByDate[dateKey];
                    const date = dayData.date;
                    const dayOfWeek = date.getDay();
                    const schedule = config.workSchedule[dayOfWeek];

                    if (schedule && schedule.isWorkDay) {
                        dayData.schedule = schedule;
                        
                        const earliestArrival = dayData.arrivals.length > 0 ? 
                            dayData.arrivals.reduce((min, time) => timeToMinutes(time) < timeToMinutes(min) ? time : min) : null;
                        const latestDeparture = dayData.departures.length > 0 ?
                            dayData.departures.reduce((max, time) => timeToMinutes(time) > timeToMinutes(max) ? time : max) : null;

                        dayData.firstArrival = earliestArrival;
                        dayData.lastDeparture = latestDeparture;

                        let arrivalPenalty = 0;
                        let departurePenalty = 0;

                        if (earliestArrival) {
                            const arrivalMinutes = timeToMinutes(earliestArrival);
                            const scheduleArrivalEnd = timeToMinutes(schedule.arrivalEnd);
                            if (arrivalMinutes > scheduleArrivalEnd) {
                                const lateMinutes = arrivalMinutes - scheduleArrivalEnd;
                                arrivalPenalty = Math.ceil(lateMinutes / config.penaltyRules.lateArrivalPer) * config.penaltyRules.lateArrivalAmount;
                            }
                        } else {
                            arrivalPenalty = config.penaltyRules.noArrivalAmount;
                        }

                        if (latestDeparture) {
                            const departureMinutes = timeToMinutes(latestDeparture);
                            const scheduleDepartureStart = timeToMinutes(schedule.departureStart);
                            if (departureMinutes < scheduleDepartureStart) {
                                const earlyMinutes = scheduleDepartureStart - departureMinutes;
                                departurePenalty = Math.ceil(earlyMinutes / config.penaltyRules.earlyDeparturePer) * config.penaltyRules.earlyDepartureAmount;
                            }
                        } else {
                            departurePenalty = config.penaltyRules.noDepartureAmount;
                        }

                        dayData.originalArrivalPenalty = arrivalPenalty;
                        dayData.originalDeparturePenalty = departurePenalty;
                        dayData.arrivalPenalty = arrivalPenalty;
                        dayData.departurePenalty = departurePenalty;

                        const dateNum = date.getDate();
                        const permitKey = `${emp.id}-${dateNum}`;
                        const permit = permitData[permitKey];

                        if (permit) {
                            if (permit.type === 'full') {
                                dayData.arrivalPenaltyWaived = arrivalPenalty;
                                dayData.departurePenaltyWaived = departurePenalty;
                                dayData.arrivalPenalty = 0;
                                dayData.departurePenalty = 0;
                            } else if (permit.type === 'arrival') {
                                dayData.arrivalPenaltyWaived = arrivalPenalty;
                                dayData.arrivalPenalty = 0;
                            } else if (permit.type === 'departure') {
                                dayData.departurePenaltyWaived = departurePenalty;
                                dayData.departurePenalty = 0;
                            }
                            dayData.permit = permit;
                        }

                        emp.totalPenalty += dayData.arrivalPenalty + dayData.departurePenalty;
                        emp.daysPresent++;
                    }
                });
            });

            originalEmployeesArray = Object.values(employeesMap).sort((a, b) => a.id.localeCompare(b.id));

            return {
                employees: employeesMap,
                stats: {
                    totalEmployees: Object.keys(employeesMap).length,
                    filesProcessed: results.length,
                    totalRecords: totalRecords
                }
            };
        }

        function parseExcelDate(value) {
            if (typeof value === 'number') {
                const date = new Date((value - 25569) * 86400 * 1000);
                return date;
            }
            if (typeof value === 'string') {
                const parsed = new Date(value);
                if (!isNaN(parsed.getTime())) return parsed;
            }
            if (value instanceof Date) {
                return value;
            }
            return null;
        }

        function parseExcelTime(value) {
            if (typeof value === 'number') {
                const totalMinutes = Math.round(value * 24 * 60);
                const hours = Math.floor(totalMinutes / 60);
                const minutes = totalMinutes % 60;
                return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
            }
            if (typeof value === 'string') {
                return value.trim();
            }
            return String(value);
        }

        function timeToMinutes(timeStr) {
            const [hours, minutes] = timeStr.split(':').map(Number);
            return hours * 60 + minutes;
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }

        function setupFilterOptions(data) {
            const divisions = new Set();
            Object.values(data.employees).forEach(emp => {
                divisions.add(emp.division);
            });

            const divisionFilter = document.getElementById('divisionFilter');
            divisionFilter.innerHTML = '<option value="">Semua Divisi</option>';
            Array.from(divisions).sort().forEach(div => {
                divisionFilter.innerHTML += `<option value="${div}">${div}</option>`;
            });
        }

        function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const divisionFilter = document.getElementById('divisionFilter').value;
            const penaltyFilter = document.getElementById('penaltyFilter').value;

            filteredEmployeesArray = originalEmployeesArray.filter(emp => {
                const matchesSearch = !searchTerm || 
                    emp.id.toLowerCase().includes(searchTerm) ||
                    emp.name.toLowerCase().includes(searchTerm) ||
                    emp.division.toLowerCase().includes(searchTerm);

                const matchesDivision = !divisionFilter || emp.division === divisionFilter;

                const matchesPenalty = !penaltyFilter ||
                    (penaltyFilter === 'with-penalty' && emp.totalPenalty > 0) ||
                    (penaltyFilter === 'no-penalty' && emp.totalPenalty === 0);

                return matchesSearch && matchesDivision && matchesPenalty;
            });

            currentPage = 1;
            displayFilteredResults();

            const hasFilters = searchTerm || divisionFilter || penaltyFilter;
            document.getElementById('exportFilteredBtn').style.display = hasFilters ? 'inline-block' : 'none';
            document.getElementById('searchResults').textContent = 
                `Menampilkan ${filteredEmployeesArray.length} dari ${originalEmployeesArray.length} karyawan`;
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('divisionFilter').value = '';
            document.getElementById('penaltyFilter').value = '';
            applyFilters();
        }

        function displayCombinedResults(data) {
            filteredEmployeesArray = originalEmployeesArray;
            displayFilteredResults();
            
            document.getElementById('summary').style.display = 'block';
            document.getElementById('searchSection').style.display = 'block';
            document.getElementById('exportSection').style.display = 'block';
            document.getElementById('results').style.display = 'block';

            updateStatistics();
        }

        function updateStatistics() {
            document.getElementById('totalEmployees').textContent = originalEmployeesArray.length;
            document.getElementById('filesProcessed').textContent = processedData.stats.filesProcessed;
            document.getElementById('totalRecords').textContent = processedData.stats.totalRecords;
            
            const totalPenalties = originalEmployeesArray.reduce((sum, emp) => sum + emp.totalPenalty, 0);
            document.getElementById('totalPenalties').textContent = formatCurrency(totalPenalties);
        }

        function displayFilteredResults() {
            const tbody = document.getElementById('resultsTable');
            const startIdx = (currentPage - 1) * recordsPerPage;
            const endIdx = startIdx + recordsPerPage;
            const pageData = filteredEmployeesArray.slice(startIdx, endIdx);

            let html = '';
            pageData.forEach(emp => {
                html += `
                    <tr>
                        <td>${emp.id}</td>
                        <td>${emp.division}</td>
                        <td>${emp.name}</td>
                        <td>
                            <button class="toggle-btn" onclick="toggleDetail('detail-${emp.id}')">Tampilkan Detail</button>
                            <div id="detail-${emp.id}" class="attendance-detail" style="display:none; margin-top: 10px;">
                                ${generateAttendanceDetail(emp)}
                            </div>
                        </td>
                        <td style="text-align: center;">${emp.daysPresent}</td>
                        <td class="penalty-info">${formatCurrency(emp.totalPenalty)}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
            renderPagination();
        }

        function generateAttendanceDetail(emp) {
            const dates = Object.keys(emp.attendanceByDate).map(Number).sort((a, b) => a - b);
            let html = '';

            dates.forEach(dateNum => {
                const dayData = emp.attendanceByDate[dateNum];
                const date = dayData.date;
                const dayName = dayNames[date.getDay()];
                const schedule = dayData.schedule;

                if (!schedule) return;

                const permitBadge = dayData.permit ? 
                    `<span class="permit-badge permit-${dayData.permit.type}">
                        ${dayData.permit.type === 'full' ? 'IZIN PENUH' : 
                          dayData.permit.type === 'arrival' ? 'IZIN DATANG' : 'IZIN PULANG'}
                    </span>` : '';

                html += `<div class="day-info">`;
                html += `<strong>${dayName}, ${dateNum} ${monthNames[selectedMonth]} ${selectedYear}</strong> ${permitBadge}`;
                html += `<div style="font-size: 10px; color: #666;">Jadwal: ${schedule.arrivalEnd} | ${schedule.departureStart}</div>`;

                if (dayData.firstArrival) {
                    html += `<div class="time-entry">Datang: ${dayData.firstArrival}`;
                    if (dayData.arrivalPenalty > 0) {
                        html += ` <span class="penalty-info">(Denda: ${formatCurrency(dayData.arrivalPenalty)})</span>`;
                    } else if (dayData.arrivalPenaltyWaived > 0) {
                        html += ` <span class="waived-penalty">(Denda dibebaskan: ${formatCurrency(dayData.arrivalPenaltyWaived)})</span>`;
                    }
                    html += `</div>`;
                } else {
                    html += `<div class="time-entry">Datang: <span style="color: red;">Tidak Absen</span>`;
                    if (dayData.arrivalPenalty > 0) {
                        html += ` <span class="penalty-info">(Denda: ${formatCurrency(dayData.arrivalPenalty)})</span>`;
                    } else if (dayData.arrivalPenaltyWaived > 0) {
                        html += ` <span class="waived-penalty">(Denda dibebaskan: ${formatCurrency(dayData.arrivalPenaltyWaived)})</span>`;
                    }
                    html += `</div>`;
                }

                if (dayData.lastDeparture) {
                    html += `<div class="time-entry">Pulang: ${dayData.lastDeparture}`;
                    if (dayData.departurePenalty > 0) {
                        html += ` <span class="penalty-info">(Denda: ${formatCurrency(dayData.departurePenalty)})</span>`;
                    } else if (dayData.departurePenaltyWaived > 0) {
                        html += ` <span class="waived-penalty">(Denda dibebaskan: ${formatCurrency(dayData.departurePenaltyWaived)})</span>`;
                    }
                    html += `</div>`;
                } else {
                    html += `<div class="time-entry">Pulang: <span style="color: red;">Tidak Absen</span>`;
                    if (dayData.departurePenalty > 0) {
                        html += ` <span class="penalty-info">(Denda: ${formatCurrency(dayData.departurePenalty)})</span>`;
                    } else if (dayData.departurePenaltyWaived > 0) {
                        html += ` <span class="waived-penalty">(Denda dibebaskan: ${formatCurrency(dayData.departurePenaltyWaived)})</span>`;
                    }
                    html += `</div>`;
                }

                if (dayData.permit && dayData.permit.reason !== '-') {
                    html += `<div style="font-size: 10px; color: #666; margin-top: 3px;">Keterangan: ${dayData.permit.reason}</div>`;
                }

                html += `</div>`;
            });

            return html || '<p style="color: #999;">Tidak ada data kehadiran</p>';
        }

        function toggleDetail(id) {
            const element = document.getElementById(id);
            if (element.style.display === 'none') {
                element.style.display = 'block';
                element.previousElementSibling.textContent = 'Sembunyikan Detail';
            } else {
                element.style.display = 'none';
                element.previousElementSibling.textContent = 'Tampilkan Detail';
            }
        }

        function renderPagination() {
            const totalPages = Math.ceil(filteredEmployeesArray.length / recordsPerPage);
            
            if (totalPages <= 1) {
                document.getElementById('paginationTop').style.display = 'none';
                document.getElementById('paginationBottom').style.display = 'none';
                return;
            }

            let html = '';
            
            html += `<button ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">‹ Prev</button>`;
            
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    html += `<button class="${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += `<span style="padding: 5px;">...</span>`;
                }
            }
            
            html += `<button ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">Next ›</button>`;
            
            document.getElementById('paginationTop').innerHTML = html;
            document.getElementById('paginationBottom').innerHTML = html;
            document.getElementById('paginationTop').style.display = 'flex';
            document.getElementById('paginationBottom').style.display = 'flex';
        }

        function changePage(page) {
            currentPage = page;
            displayFilteredResults();
            window.scrollTo({top: 0, behavior: 'smooth'});
        }

        function exportToExcel(type) {
            if (!processedData) {
                alert('Tidak ada data untuk diekspor!');
                return;
            }

            const dataToExport = (type === 'filtered' && filteredEmployeesArray.length < originalEmployeesArray.length) 
                ? filteredEmployeesArray 
                : originalEmployeesArray;

            let wsData = [];

            if (type === 'summary' || type === 'filtered') {
                wsData.push(['Ringkasan Denda Absensi - ' + monthNames[selectedMonth] + ' ' + selectedYear]);
                wsData.push([]);
                wsData.push(['ID', 'Divisi', 'Nama', 'Hari Hadir', 'Total Denda']);

                dataToExport.forEach(emp => {
                    wsData.push([
                        emp.id,
                        emp.division,emp.name,
                        emp.daysPresent,
                        emp.totalPenalty
                    ]);
                });

                wsData.push([]);
                wsData.push(['Total Karyawan', dataToExport.length]);
                wsData.push(['Total Denda Keseluruhan', dataToExport.reduce((sum, emp) => sum + emp.totalPenalty, 0)]);

            } else if (type === 'detailed') {
                wsData.push(['Detail Kehadiran Lengkap - ' + monthNames[selectedMonth] + ' ' + selectedYear]);
                wsData.push([]);
                wsData.push(['ID', 'Divisi', 'Nama', 'Tanggal', 'Hari', 'Jadwal Datang', 'Jadwal Pulang', 'Absen Datang', 'Absen Pulang', 'Denda Datang', 'Denda Pulang', 'Status Izin', 'Keterangan']);

                dataToExport.forEach(emp => {
                    const dates = Object.keys(emp.attendanceByDate).map(Number).sort((a, b) => a - b);
                    
                    dates.forEach(dateNum => {
                        const dayData = emp.attendanceByDate[dateNum];
                        const date = dayData.date;
                        const dayName = dayNames[date.getDay()];
                        const schedule = dayData.schedule;

                        if (!schedule) return;

                        const permitStatus = dayData.permit ? 
                            (dayData.permit.type === 'full' ? 'Izin Penuh' :
                             dayData.permit.type === 'arrival' ? 'Izin Datang' : 'Izin Pulang') : '-';
                        
                        const permitReason = dayData.permit ? dayData.permit.reason : '-';

                        let arrivalPenaltyText = '';
                        if (dayData.arrivalPenalty > 0) {
                            arrivalPenaltyText = dayData.arrivalPenalty;
                        } else if (dayData.arrivalPenaltyWaived > 0) {
                            arrivalPenaltyText = `0 (Dibebaskan: ${dayData.arrivalPenaltyWaived})`;
                        } else {
                            arrivalPenaltyText = 0;
                        }

                        let departurePenaltyText = '';
                        if (dayData.departurePenalty > 0) {
                            departurePenaltyText = dayData.departurePenalty;
                        } else if (dayData.departurePenaltyWaived > 0) {
                            departurePenaltyText = `0 (Dibebaskan: ${dayData.departurePenaltyWaived})`;
                        } else {
                            departurePenaltyText = 0;
                        }

                        wsData.push([
                            emp.id,
                            emp.division,
                            emp.name,
                            `${dateNum}/${selectedMonth}/${selectedYear}`,
                            dayName,
                            schedule.arrivalEnd,
                            schedule.departureStart,
                            dayData.firstArrival || 'Tidak Absen',
                            dayData.lastDeparture || 'Tidak Absen',
                            arrivalPenaltyText,
                            departurePenaltyText,
                            permitStatus,
                            permitReason
                        ]);
                    });

                    wsData.push([
                        '', '', '', '', '', '', '', '', 'TOTAL DENDA:', emp.totalPenalty, '', '', ''
                    ]);
                    wsData.push([]);
                });
            }

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(wsData);

            const colWidths = type === 'detailed' 
                ? [{wch: 10}, {wch: 15}, {wch: 25}, {wch: 12}, {wch: 10}, {wch: 12}, {wch: 12}, {wch: 12}, {wch: 12}, {wch: 15}, {wch: 15}, {wch: 15}, {wch: 20}]
                : [{wch: 10}, {wch: 15}, {wch: 25}, {wch: 12}, {wch: 15}];
            ws['!cols'] = colWidths;

            XLSX.utils.book_append_sheet(wb, ws, 'Data Absensi');

            const fileName = type === 'detailed' 
                ? `Absensi_Detail_${monthNames[selectedMonth]}_${selectedYear}.xlsx`
                : type === 'filtered'
                ? `Absensi_Filtered_${monthNames[selectedMonth]}_${selectedYear}.xlsx`
                : `Absensi_Ringkasan_${monthNames[selectedMonth]}_${selectedYear}.xlsx`;

            XLSX.writeFile(wb, fileName);
        }

        window.onclick = function(event) {
            const modal = document.getElementById('permitModal');
            if (event.target === modal) {
                closePermitModal();
            }
        }
    </script>
</body>
</html>