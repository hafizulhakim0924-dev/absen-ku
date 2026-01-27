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
                    <button onclick="openSaveModal()" style="padding: 5px 10px; border: 1px solid #000; background: #000; color: white; cursor: pointer;">💾 Simpan Data</button>
                    <button onclick="openLoadModal()" style="padding: 5px 10px; border: 1px solid #999; background: white; cursor: pointer;">📂 Muat Data</button>
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

            <div id="saveModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; max-width: 500px; width: 90%; border: 1px solid #999;">
                    <h3>Simpan Data Absensi</h3>
                    <p style="color: #666; font-size: 13px;">Data absensi bulan <strong><span id="saveMonthDisplay"></span></strong> akan disimpan ke file JSON</p>
                    <div style="margin: 15px 0;">
                        <label><strong>Nama File (opsional):</strong></label>
                        <input type="text" id="saveFileName" placeholder="Contoh: Data Absensi April 2025" style="width: 100%; padding: 8px; border: 1px solid #999; margin-top: 5px;">
                        <div style="font-size: 11px; color: #666; margin-top: 5px;">Kosongkan untuk menggunakan nama default</div>
                    </div>
                    <div style="margin: 15px 0; text-align: center;">
                        <button onclick="saveDataToJSON()" style="padding: 8px 20px; background: #000; color: white; border: none; cursor: pointer; margin-right: 5px;">Simpan</button>
                        <button onclick="closeSaveModal()" style="padding: 8px 20px; border: 1px solid #999; background: white; cursor: pointer;">Batal</button>
                    </div>
                </div>
            </div>

            <div id="loadModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; max-width: 800px; width: 90%; max-height: 80vh; overflow-y: auto; border: 1px solid #999;">
                    <h3>Muat Data Tersimpan</h3>
                    <div style="margin: 15px 0;">
                        <label><strong>Upload File JSON:</strong></label>
                        <input type="file" id="loadJSONFile" accept=".json" style="width: 100%; padding: 8px; border: 1px solid #999; margin-top: 5px;">
                        <button onclick="loadDataFromJSON()" style="padding: 6px 15px; background: #000; color: white; border: none; cursor: pointer; margin-top: 5px;">Muat File</button>
                    </div>
                    <hr style="margin: 20px 0;">
                    <h4>Riwayat Data Tersimpan di Browser:</h4>
                    <div id="savedDataList" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-top: 10px;">
                        <p style="color: #999; text-align: center;">Belum ada data tersimpan di browser</p>
                    </div>
                    <div style="margin: 15px 0; text-align: center;">
                        <button onclick="closeLoadModal()" style="padding: 8px 20px; border: 1px solid #999; background: white; cursor: pointer;">Tutup</button>
                    </div>
                </div>
            </div>

            <div id="editModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1001;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; max-width: 900px; width: 95%; max-height: 90vh; overflow-y: auto; border: 1px solid #999;">
                    <h3>Edit Data Absensi - <span id="editEmployeeInfo"></span></h3>
                    <div id="editContent" style="margin: 15px 0;">
                        <p>Loading...</p>
                    </div>
                    <div style="margin: 15px 0; text-align: center;">
                        <button onclick="saveEditedData()" style="padding: 8px 20px; background: #27ae60; color: white; border: none; cursor: pointer; margin-right: 5px;">Simpan Perubahan</button>
                        <button onclick="closeEditModal()" style="padding: 8px 20px; border: 1px solid #999; background: white; cursor: pointer;">Tutup</button>
                    </div>
                </div>
            </div>

            <div id="exportSection" class="export-section" style="display:none;">
                <h3>Export Data ke Excel</h3>
                <button onclick="exportToExcel('detailed')">Export Detail Kehadiran</button>
                <button onclick="exportToExcel('summary')">Export Ringkasan Denda</button>
                <button onclick="exportToExcel('filtered')" id="exportFilteredBtn" style="display:none;">Export Data Terfilter</button>
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
                                <th width="100">Aksi</th>
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
        let savedDataHistory = [];

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
                })
                .catch(error => {
                    statusDiv.className = 'alert alert-error';
                    statusDiv.innerHTML = '<h3>Error Memuat Konfigurasi</h3><p>File config.json tidak ditemukan di direktori yang sama dengan file HTML ini.</p><p>Pastikan file config.json berada di folder yang sama.</p>';
                });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            loadConfigFromFile();
            setupEventListeners();
            loadSavedDataHistory();
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
            return null;
        }

        function getSpecialEvent(date, month, year) {
            if (!config || !config.special_events) return null;
            const dateStr = `${year}-${month.toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}`;
            for (const event of config.special_events) {
                if (event.date === dateStr) return event;
            }
            return null;
        }

        function isWorkingDay(date, month, year) {
            const dateObj = new Date(year, month - 1, date);
            const isWeekend = dateObj.getDay() === 0 || dateObj.getDay() === 6;
            const holiday = isHoliday(date, month, year);
            return !isWeekend && !holiday;
        }

        function getIncompleteAttendancePenalty() {
            if (config && config.absence_policy && config.absence_policy.incomplete_attendance_penalty) {
                return config.absence_policy.incomplete_attendance_penalty;
            }
            return 15000;
        }

        function getDivisionById(id) {
            if (!config || !config.division_schedules) return 'Unknown';
            const idNum = parseInt(id);
            for (const [divisionKey, divisionData] of Object.entries(config.division_schedules)) {
                if (divisionData.ids && divisionData.ids.includes(idNum)) return divisionKey;
            }
            return 'Unknown';
        }

        function getDivisionFullName(divisionKey) {
            if (!config || !config.division_schedules) return divisionKey;
            return config.division_schedules[divisionKey]?.name || divisionKey;
        }

        function getDivisionSchedule(divisionKey, date = null, month = null, year = null) {
            let baseSchedule;
            if (!config || !config.division_schedules) {
                baseSchedule = config?.default_work_schedule || { start_time: "08:00", end_time: "16:00" };
            } else {
                const divisionData = config.division_schedules[divisionKey];
                if (divisionData && divisionData.start_time && divisionData.end_time) {
                    baseSchedule = {start_time: divisionData.start_time, end_time: divisionData.end_time};
                    if (date && month && year) {
                        const currentDate = new Date(year, month - 1, date);
                        const dayOfWeek = currentDate.getDay();
                        if (dayOfWeek === 5 && divisionData.friday_schedule) {
                            baseSchedule = {
                                start_time: divisionData.friday_schedule.start_time,
                                end_time: divisionData.friday_schedule.end_time,
                                isFriday: true
                            };
                        }
                    }
                } else {
                    baseSchedule = config?.default_work_schedule || { start_timebaseSchedule = config?.default_work_schedule || { start_time: "08:00", end_time: "16:00" };
                }
            }
            if (date && month && year) {
                const specialEvent = getSpecialEvent(date, month, year);
                if (specialEvent && specialEvent.start_time && specialEvent.end_time) {
                    return {
                        start_time: specialEvent.start_time,
                        end_time: specialEvent.end_time,
                        isSpecialEvent: true,
                        eventName: specialEvent.name
                    };
                }
            }
            return baseSchedule;
        }

        function formatCurrency(amount) {
            if (!config || !config.currency) return `Rp ${amount.toLocaleString('id-ID')}`;
            return config.currency.format.replace('%s', amount.toLocaleString('id-ID'));
        }

        function getLatePolicy(lateMinutes) {
            if (!config || !config.late_policy) return null;
            if (lateMinutes <= config.late_policy.grace_period_minutes) return null;
            for (const level of config.late_policy.levels) {
                if (lateMinutes >= level.min_minutes && lateMinutes <= level.max_minutes) return level;
            }
            return null;
        }

        function getEarlyDeparturePolicy(earlyMinutes) {
            if (!config || !config.early_departure_policy) return null;
            if (earlyMinutes <= config.early_departure_policy.grace_period_minutes) return null;
            for (const level of config.early_departure_policy.levels) {
                if (earlyMinutes >= level.min_minutes && earlyMinutes <= level.max_minutes) return level;
            }
            return null;
        }

        function parseWorkStartTime(divisionKey = null, date = null, month = null, year = null) {
            const schedule = getDivisionSchedule(divisionKey, date, month, year);
            const [hour, minute] = schedule.start_time.split(':');
            return { hour: parseInt(hour), minute: parseInt(minute) };
        }

        function parseWorkEndTime(divisionKey = null, date = null, month = null, year = null) {
            const schedule = getDivisionSchedule(divisionKey, date, month, year);
            const [hour, minute] = schedule.end_time.split(':');
            return { hour: parseInt(hour), minute: parseInt(minute) };
        }

        function enableFileSection() {
            document.getElementById('fileSection').style.display = 'block';
            ['A', 'B', 'C', 'D'].forEach(machine => {
                document.getElementById(`fileInput${machine}`).disabled = false;
                document.getElementById(`button${machine}`).disabled = false;
            });
        }
        
        function selectFile(machine) {
            if (!checkConfigRequired()) return;
            if (!selectedMonth) {
                alert('Pilih bulan terlebih dahulu!');
                return;
            }
            document.getElementById(`fileInput${machine}`).click();
        }

        function handleFileUpload(event, machine) {
            if (!checkConfigRequired()) return;
            const file = event.target.files[0];
            const statusDiv = document.getElementById(`status${machine}`);
            const button = document.getElementById(`button${machine}`);
            if (!file) {
                filesData[machine] = null;
                statusDiv.textContent = 'Belum ada file';
                button.style.background = '';
                updateProcessButton();
                return;
            }
            statusDiv.textContent = 'Memuat...';
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {type: 'array'});
                    let sheetIndex = 2;
                    if (workbook.SheetNames.length < 3) sheetIndex = 0;
                    const sheetName = workbook.SheetNames[sheetIndex];
                    const worksheet = workbook.Sheets[sheetName];
                    const jsonData = XLSX.utils.sheet_to_json(worksheet, {header: 1, defval: '', raw: false});
                    filesData[machine] = jsonData;
                    statusDiv.textContent = `${file.name} (${jsonData.length} baris)`;
                    button.style.background = '#27ae60';
                    button.style.color = 'white';
                    updateProcessButton();
                } catch (error) {
                    statusDiv.textContent = `Error: ${error.message}`;
                    button.style.background = '';
                    filesData[machine] = null;
                    updateProcessButton();
                }
            };
            reader.readAsArrayBuffer(file);
        }

        function updateProcessButton() {
            const hasAnyFile = Object.values(filesData).some(data => data !== null);
            const processButton = document.getElementById('processButton');
            processButton.disabled = !hasAnyFile || !selectedMonth || !configLoaded;
            if (hasAnyFile && selectedMonth && configLoaded) {
                const fileCount = Object.values(filesData).filter(data => data !== null).length;
                processButton.textContent = `Proses ${fileCount} File`;
            } else {
                processButton.textContent = 'Proses Semua File';
            }
        }

        function processAllFiles() {
            if (!checkConfigRequired()) return;
            if (!selectedMonth) {
                alert('Pilih bulan terlebih dahulu!');
                return;
            }
            document.getElementById('loading').style.display = 'block';
            document.getElementById('summary').style.display = 'none';
            document.getElementById('searchSection').style.display = 'none';
            document.getElementById('exportSection').style.display = 'none';
            document.getElementById('results').style.display = 'none';
            
            setTimeout(() => {
                try {
                    const combinedData = combineAllMachineData();
                    processedData = combinedData;
                    setupFilterOptions(combinedData);
                    displayCombinedResults(combinedData);
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('summary').style.display = 'block';
                    document.getElementById('searchSection').style.display = 'block';
                    document.getElementById('exportSection').style.display = 'block';
                    document.getElementById('results').style.display = 'block';
                } catch (error) {
                    alert('Error memproses file: ' + error.message);
                    document.getElementById('loading').style.display = 'none';
                }
            }, 100);
        }

        function setupFilterOptions(combinedData) {
            const divisionFilter = document.getElementById('divisionFilter');
            const divisions = new Set();
            Object.values(combinedData.employees).forEach(employee => {
                divisions.add(employee.divisi);
            });
            divisionFilter.innerHTML = '<option value="">Semua Divisi</option>';
            Array.from(divisions).sort().forEach(division => {
                const option = document.createElement('option');
                option.value = division;
                option.textContent = getDivisionFullName(division);
                divisionFilter.appendChild(option);
            });
        }

        function combineAllMachineData() {
            const employeeData = {};
            let totalRecords = 0;
            let filesProcessed = 0;
            
            ['A', 'B', 'C', 'D'].forEach(machine => {
                if (!filesData[machine]) return;
                filesProcessed++;
                const data = filesData[machine];
                
                for (let namaRowIndex = 4; namaRowIndex < data.length; namaRowIndex += 2) {
                    const absensiRowIndex = namaRowIndex + 1;
                    if (namaRowIndex >= data.length || absensiRowIndex >= data.length) break;
                    
                    const namaRow = data[namaRowIndex];
                    const absensiRow = data[absensiRowIndex];
                    let nama = '';
                    let id = '';
                    
                    if (namaRow.length > 10 && namaRow[10]) nama = namaRow[10].toString().trim();
                    if (namaRow.length > 2 && namaRow[2]) id = namaRow[2].toString().trim();
                    if (!nama || !id) continue;
                    
                    if (!employeeData[id]) {
                        employeeData[id] = {
                            nama: nama,
                            divisi: getDivisionById(id),
                            attendanceByDate: {}
                        };
                    }
                    
                    for (let colIndex = 0; colIndex < Math.min(31, absensiRow.length); colIndex++) {
                        const cellValue = absensiRow[colIndex];
                        if (cellValue && cellValue.toString().trim() !== '') {
                            const timeValue = cellValue.toString().trim();
                            if (timeValue.match(/\d{1,2}[:.]\d{2}/) || timeValue.match(/\d{3,}/)) {
                                const tanggal = getDateFromColumnIndex(colIndex);
                                const columnLetter = getColumnLetter(colIndex);
                                const dayInfo = getDayInfo(tanggal, selectedMonth, selectedYear);
                                if (dayInfo.fullDate.getMonth() !== selectedMonth - 1) continue;
                                
                                if (!employeeData[id].attendanceByDate[tanggal]) {
                                    employeeData[id].attendanceByDate[tanggal] = [];
                                }
                                
                                const splitTimes = splitCombinedTimes(timeValue);
                                splitTimes.forEach(singleTime => {
                                    if (singleTime && singleTime.trim() !== '') {
                                        employeeData[id].attendanceByDate[tanggal].push({
                                            time: singleTime.trim(),
                                            column: columnLetter,
                                            machine: machine,
                                            dayInfo: dayInfo,
                                            originalValue: timeValue
                                        });
                                        totalRecords++;
                                    }
                                });
                            }
                        }
                    }
                }
            });
            
            return {
                employees: employeeData,
                stats: {
                    totalEmployees: Object.keys(employeeData).length,
                    filesProcessed: filesProcessed,
                    totalRecords: totalRecords
                }
            };
        }

        function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const divisionFilter = document.getElementById('divisionFilter').value;
            const penaltyFilter = document.getElementById('penaltyFilter').value;
            if (!processedData) return;
            
            filteredEmployeesArray = originalEmployeesArray.filter(employee => {
                const matchesSearch = !searchTerm || 
                    employee.id.toLowerCase().includes(searchTerm) ||
                    employee.nama.toLowerCase().includes(searchTerm) ||
                    getDivisionFullName(employee.divisi).toLowerCase().includes(searchTerm);
                const matchesDivision = !divisionFilter || employee.divisi === divisionFilter;
                let matchesPenalty = true;
                if (penaltyFilter === 'with-penalty') {
                    matchesPenalty = employee.totalPenalty > 0;
                } else if (penaltyFilter === 'no-penalty') {
                    matchesPenalty = employee.totalPenalty === 0;
                }
                return matchesSearch && matchesDivision && matchesPenalty;
            });
            
            currentPage = 1;
            displayFilteredResults();
            updateSearchResults();
            
            const exportFilteredBtn = document.getElementById('exportFilteredBtn');
            if (filteredEmployeesArray.length < originalEmployeesArray.length) {
                exportFilteredBtn.style.display = 'inline-block';
            } else {
                exportFilteredBtn.style.display = 'none';
            }
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('divisionFilter').value = '';
            document.getElementById('penaltyFilter').value = '';
            applyFilters();
        }

        function updateSearchResults() {
            const totalResults = filteredEmployeesArray.length;
            const totalOriginal = originalEmployeesArray.length;
            const searchResultsDiv = document.getElementById('searchResults');
            if (totalResults === totalOriginal) {
                searchResultsDiv.textContent = `Menampilkan semua ${totalResults} karyawan`;
            } else {
                searchResultsDiv.textContent = `Menampilkan ${totalResults} dari ${totalOriginal} karyawan`;
            }
        }

        function openPermitModal() {
            if (!selectedMonth) {
                alert('Pilih bulan terlebih dahulu!');
                return;
            }
            document.getElementById('permitMonthName').textContent = `${monthNames[selectedMonth]} ${selectedYear}`;
            document.getElementById('permitModal').style.display = 'block';
            updatePermitList();
        }

        function closePermitModal() {
            document.getElementById('permitModal').style.display = 'none';
            document.getElementById('permitEmployeeId').value = '';
            document.getElementById('permitDate').value = '';
            document.getElementById('permitType').value = 'full';
            document.getElementById('permitReason').value = '';
        }

        function getPermitTypeName(type) {
            const types = {
                'full': 'Izin Penuh',
                'arrival': 'Izin Kedatangan',
                'departure': 'Izin Kepulangan'
            };
            return types[type] || type;
        }

        function getPermitBadgeClass(type) {
            const classes = {
                'full': 'permit-full',
                'arrival': 'permit-arrival',
                'departure': 'permit-departure'
            };
            return classes[type] || 'permit-full';
        }

        function addPermit() {
            const employeeId = document.getElementById('permitEmployeeId').value.trim();
            const date = parseInt(document.getElementById('permitDate').value);
            const type = document.getElementById('permitType').value;
            const reason = document.getElementById('permitReason').value.trim() || 'Izin';
            
            if (!employeeId) {
                alert('Masukkan ID Karyawan!');
                return;
            }
            if (!date || date < 1 || date > 31) {
                alert('Masukkan tanggal yang valid (1-31)!');
                return;
            }
            
            const permitKey = `${employeeId}-${date}`;
            permitData[permitKey] = {
                id: employeeId,
                date: date,
                type: type,
                reason: reason,
                month: selectedMonth,
                year: selectedYear
            };
            
            const typeName = getPermitTypeName(type);
            alert(`${typeName} ditambahkan untuk ID ${employeeId} pada tanggal ${date} ${monthNames[selectedMonth]}`);
            
            document.getElementById('permitEmployeeId').value = '';
            document.getElementById('permitDate').value = '';
            document.getElementById('permitType').value = 'full';
            document.getElementById('permitReason').value = '';
            updatePermitList();
            
            if (processedData) {
                processAllFiles();
            }
        }

        function removePermit(permitKey) {
            if (confirm('Hapus data izin ini?')) {
                delete permitData[permitKey];
                updatePermitList();
                if (processedData) {
                    processAllFiles();
                }
            }
        }

        function updatePermitList() {
            const permitList = document.getElementById('permitList');
            const permits = Object.values(permitData).filter(p => p.month === selectedMonth && p.year === selectedYear);
            if (permits.length === 0) {
                permitList.innerHTML = '<p style="color: #999; text-align: center;">Belum ada data izin</p>';
                return;
            }
            
            permits.sort((a, b) => {
                if (a.id !== b.id) return a.id.localeCompare(b.id);
                return a.date - b.date;
            });
            
            let html = '<div style="font-size: 12px;">';
            permits.forEach(permit => {
                const key = `${permit.id}-${permit.date}`;
                const employeeName = processedData?.employees[permit.id]?.nama || 'Unknown';
                const badgeClass = getPermitBadgeClass(permit.type);
                const typeName = getPermitTypeName(permit.type);
                html += `
                    <div style="padding: 8px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                        <div style="flex: 1;">
                            <strong>ID ${permit.id}</strong> - ${employeeName}
                            <span class="permit-badge ${badgeClass}">${typeName}</span><br>
                            <span style="font-size: 11px; color: #666;">Tanggal: ${permit.date} ${monthNames[selectedMonth]} - ${permit.reason}</span>
                        </div>
                        <button onclick="removePermit('${key}')" style="padding: 4px 10px; background: white; border: 1px solid #999; cursor: pointer; font-size: 11px;">Hapus</button>
                    </div>
                `;
            });
            html += '</div>';
            permitList.innerHTML = html;
        }

        // ============ FUNGSI SAVE/LOAD DATA ============
        
        function loadSavedDataHistory() {
            try {
                const saved = localStorage.getItem('attendanceHistory');
                if (saved) {
                    savedDataHistory = JSON.parse(saved);
                }
            } catch (e) {
                savedDataHistory = [];
            }
        }

        function saveSavedDataHistory() {
            try {
                localStorage.setItem('attendanceHistory', JSON.stringify(savedDataHistory));
            } catch (e) {
                console.error('Error saving history:', e);
            }
        }

        function openSaveModal() {
            if (!processedData || !selectedMonth) {
                alert('Tidak ada data untuk disimpan. Proses file terlebih dahulu.');
                return;
            }
            document.getElementById('saveMonthDisplay').textContent = `${monthNames[selectedMonth]} ${selectedYear}`;
            document.getElementById('saveFileName').value = '';
            document.getElementById('saveModal').style.display = 'block';
        }

        function closeSaveModal() {
            document.getElementById('saveModal').style.display = 'none';
        }

        function saveDataToJSON() {
            if (!processedData) {
                alert('Tidak ada data untuk disimpan!');
                return;
            }

            const customFileName = document.getElementById('saveFileName').value.trim();
            const defaultFileName = `Absensi_${monthNames[selectedMonth]}_${selectedYear}`;
            const displayName = customFileName || defaultFileName;
            
            const dataToSave = {
                version: '1.0',
                savedAt: new Date().toISOString(),
                savedAtDisplay: new Date().toLocaleString('id-ID'),
                fileName: displayName,
                month: selectedMonth,
                monthName: monthNames[selectedMonth],
                year: selectedYear,
                processedData: processedData,
                permitData: permitData,
                config: config,
                stats: {
                    totalEmployees: processedData.stats.totalEmployees,
                    filesProcessed: processedData.stats.filesProcessed,
                    totalRecords: processedData.stats.totalRecords
                }
            };

            // Simpan ke riwayat browser
            savedDataHistory.unshift({
                id: Date.now(),
                fileName: displayName,
                month: selectedMonth,
                monthName: monthNames[selectedMonth],
                year: selectedYear,
                savedAt: dataToSave.savedAt,
                savedAtDisplay: dataToSave.savedAtDisplay,
                totalEmployees: processedData.stats.totalEmployees
            });
            
            // Batasi riwayat maksimal 50 item
            if (savedDataHistory.length > 50) {
                savedDataHistory = savedDataHistory.slice(0, 50);
            }
            
            saveSavedDataHistory();
            
            // Simpan data lengkap ke localStorage dengan key unik
            try {
                const storageKey = `attendance_${dataToSave.savedAt}`;
                localStorage.setItem(storageKey, JSON.stringify(dataToSave));
            } catch (e) {
                console.error('Error saving to localStorage:', e);
            }

            // Download sebagai file JSON
            const jsonString = JSON.stringify(dataToSave, null, 2);
            const blob = new Blob([jsonString], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${displayName}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            alert(`Data berhasil disimpan!\n\nFile: ${displayName}.json\nBulan: ${monthNames[selectedMonth]} ${selectedYear}\nTotal Karyawan: ${processedData.stats.totalEmployees}`);
            closeSaveModal();
        }

        function openLoadModal() {
            document.getElementById('loadModal').style.display = 'block';
            updateSavedDataList();
        }

        function closeLoadModal() {
            document.getElementById('loadModal').style.display = 'none';
        }

        function updateSavedDataList() {
            const listDiv = document.getElementById('savedDataList');
            
            if (savedDataHistory.length === 0) {
                listDiv.innerHTML = '<p style="color: #999; text-align: center;">Belum ada data tersimpan di browser</p>';
                return;
            }

            let html = '<div style="font-size: 12px;">';
            savedDataHistory.forEach(item => {
                html += `
                    <div style="padding: 10px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                        <div style="flex: 1;">
                            <strong>${item.fileName}</strong><br>
                            <span style="font-size: 11px; color: #666;">
                                Bulan: ${item.monthName} ${item.year} | 
                                ${item.totalEmployees} karyawan | 
                                Disimpan: ${item.savedAtDisplay}
                            </span>
                        </div>
                        <div style="display: flex; gap: 5px;">
                            <button onclick="loadFromBrowser('${item.savedAt}')" style="padding: 5px 12px; background: #27ae60; color: white; border: none; cursor: pointer; font-size: 11px;">Muat</button>
                            <button onclick="deleteFromBrowser('${item.savedAt}')" style="padding: 5px 12px; background: #e74c3c; color: white; border: none; cursor: pointer; font-size: 11px;">Hapus</button>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            listDiv.innerHTML = html;
        }

        function loadDataFromJSON() {
            const fileInput = document.getElementById('loadJSONFile');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Pilih file JSON terlebih dahulu!');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = JSON.parse(e.target.result);
                    applyLoadedData(data);
                    closeLoadModal();
                } catch (error) {
                    alert('Error membaca file JSON: ' + error.message);
                }
            };
            reader.readAsText(file);
        }

        function loadFromBrowser(savedAt) {
            try {
                const storageKey = `attendance_${savedAt}`;
                const dataString = localStorage.getItem(storageKey);
                
                if (!dataString) {
                    alert('Data tidak ditemukan di browser!');
                    return;
                }

                const data = JSON.parse(dataString);
                applyLoadedData(data);
                closeLoadModal();
            } catch (error) {
                alert('Error memuat data: ' + error.message);
            }
        }

        function deleteFromBrowser(savedAt) {
            if (!confirm('Hapus data ini dari browser?')) return;

            try {
                const storageKey = `attendance_${savedAt}`;
                localStorage.removeItem(storageKey);
                
                savedDataHistory = savedDataHistory.filter(item => item.savedAt !== savedAt);
                saveSavedDataHistory();
                
                updateSavedDataList();
                alert('Data berhasil dihapus!');
            } catch (error) {
                alert('Error menghapus data: ' + error.message);
            }
        }

        function applyLoadedData(data) {
            if (!data.processedData || !data.month || !data.year) {
                alert('Format file JSON tidak valid!');
                return;
            }

            // Set bulan dan tahun
            selectedMonth = data.month;
            selectedYear = data.year;
            
            // Update UI bulan
            document.querySelectorAll('button[data-month]').forEach(b => b.classList.remove('active'));
            const monthButton = document.querySelector(`button[data-month="${selectedMonth}"]`);
            if (monthButton) monthButton.classList.add('active');
            
            document.getElementById('monthName').textContent = monthNames[selectedMonth];
            document.getElementById('monthYear').textContent = selectedYear;
            document.getElementById('selectedMonthInfo').style.display = 'block';
            
            // Load data
            processedData = data.processedData;
            permitData = data.permitData || {};
            
            // Tampilkan data
            setupFilterOptions(processedData);
            displayCombinedResults(processedData);
            
            document.getElementById('fileSection').style.display = 'none';
            document.getElementById('summary').style.display = 'block';
            document.getElementById('searchSection').style.display = 'block';
            document.getElementById('exportSection').style.display = 'block';
            document.getElementById('results').style.display = 'block';
            document.getElementById('warningMessage').style.display = 'none';

            alert(`Data berhasil dimuat!\n\nBulan: ${data.monthName} ${data.year}\nTotal Karyawan: ${data.stats.totalEmployees}\nDisimpan pada: ${data.savedAtDisplay}`);
        }

        // ============ FUNGSI EDIT DATA ============
        
        let currentEditEmployeeId = null;

        function openEditModal(employeeId) {
            if (!processedData || !processedData.employees[employeeId]) {
                alert('Data karyawan tidak ditemukan!');
                return;
            }

            currentEditEmployeeId = employeeId;
            const employee = processedData.employees[employeeId];
            
            document.getElementById('editEmployeeInfo').textContent = `${employee.nama} (ID: ${employeeId})`;
            document.getElementById('editModal').style.display = 'block';
            
            renderEditContent(employeeId, employee);
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            currentEditEmployeeId = null;
        }

        function renderEditContent(employeeId, employee) {
            const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
            const attendanceByDate = employee.attendanceByDate;
            
            let html = `
                <div style="background: #f5f5f5; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd;">
                    <strong>Divisi:</strong> ${getDivisionFullName(employee.divisi)}<br>
                    <strong>Bulan:</strong> ${monthNames[selectedMonth]} ${selectedYear}<br>
                    <strong>Jadwal Kerja:</strong> ${getDivisionSchedule(employee.divisi).start_time} - ${getDivisionSchedule(employee.divisi).end_time}
                </div>
                <div style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
            `;

            for (let date = 1; date <= daysInMonth; date++) {
                const dayInfo = getDayInfo(date, selectedMonth, selectedYear);
                const dayAttendance = attendanceByDate[date] || [];
                
                html += `
                    <div style="padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; background: ${dayInfo.isWorkingDay ? '#fff' : '#f9f9f9'};">
                        <div style="font-weight: bold; margin-bottom: 8px;">
                            ${date} ${monthNames[selectedMonth]} (${dayInfo.dayName})
                            ${dayInfo.holiday ? `- <span style="color: #e74c3c;">Libur: ${dayInfo.holiday.name}</span>` : ''}
                            ${dayInfo.isWeekend ? `- <span style="color: #999;">Weekend</span>` : ''}
                        </div>
                `;

                if (dayAttendance.length > 0) {
                    const categorizedEntries = categorizeAttendanceEntries(dayAttendance, employee.divisi, date, selectedMonth, selectedYear, employeeId);
                    
                    html += '<table style="width: 100%; font-size: 12px; margin-top: 5px;">';
                    html += '<tr><th style="border: 1px solid #ddd; padding: 5px; background: #f5f5f5;">Tipe</th><th style="border: 1px solid #ddd; padding: 5px; background: #f5f5f5;">Waktu</th><th style="border: 1px solid #ddd; padding: 5px; background: #f5f5f5;">Mesin</th><th style="border: 1px solid #ddd; padding: 5px; background: #f5f5f5;">Aksi</th></tr>';
                    
                    categorizedEntries.forEach((entry, idx) => {
                        html += `
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 5px;">${entry.type === 'arrival' ? 'Datang' : 'Pulang'}</td>
                                <td style="border: 1px solid #ddd; padding: 5px;">
                                    <input type="time" id="edit_${employeeId}_${date}_${idx}" value="${entry.formattedTime}" style="padding: 3px; border: 1px solid #999;">
                                </td>
                                <td style="border: 1px solid #ddd; padding: 5px;">Mesin ${entry.machine}</td>
                                <td style="border: 1px solid #ddd; padding: 5px; text-align: center;">
                                    <button onclick="deleteAttendanceEntry('<button onclick="deleteAttendanceEntry('${employeeId}', ${date}, ${idx})" style="padding: 3px 8px; background: #e74c3c; color: white; border: none; cursor: pointer; font-size: 11px;">Hapus</button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += '</table>';
                } else {
                    html += '<div style="color: #999; font-style: italic; margin-top: 5px;">Tidak ada data absensi</div>';
                }

                if (dayInfo.isWorkingDay) {
                    html += `<button onclick="addAttendanceEntry('${employeeId}', ${date})" style="padding: 5px 10px; background: #27ae60; color: white; border: none; cursor: pointer; font-size: 11px; margin-top: 5px;">+ Tambah Absen</button>`;
                }

                html += '</div>';
            }

            html += '</div>';
            document.getElementById('editContent').innerHTML = html;
        }

        function deleteAttendanceEntry(employeeId, date, entryIndex) {
            if (!confirm('Hapus data absensi ini?')) return;

            const employee = processedData.employees[employeeId];
            const dayAttendance = employee.attendanceByDate[date];
            
            if (!dayAttendance || entryIndex < 0 || entryIndex >= dayAttendance.length) {
                alert('Data tidak ditemukan!');
                return;
            }

            const categorizedEntries = categorizeAttendanceEntries(dayAttendance, employee.divisi, date, selectedMonth, selectedYear, employeeId);
            const entryToDelete = categorizedEntries[entryIndex];
            
            // Hapus entry yang sesuai dari array asli
            employee.attendanceByDate[date] = dayAttendance.filter(entry => 
                !(entry.time === entryToDelete.time && entry.machine === entryToDelete.machine)
            );

            // Jika tidak ada lagi data di tanggal tersebut, hapus key-nya
            if (employee.attendanceByDate[date].length === 0) {
                delete employee.attendanceByDate[date];
            }

            renderEditContent(employeeId, employee);
            alert('Data absensi berhasil dihapus!');
        }

        function addAttendanceEntry(employeeId, date) {
            const time = prompt('Masukkan waktu absensi (format HH:MM, contoh: 08:30):');
            if (!time) return;

            if (!time.match(/^\d{1,2}:\d{2}$/)) {
                alert('Format waktu tidak valid! Gunakan format HH:MM (contoh: 08:30)');
                return;
            }

            const [hour, minute] = time.split(':');
            if (parseInt(hour) > 23 || parseInt(minute) > 59) {
                alert('Waktu tidak valid!');
                return;
            }

            const employee = processedData.employees[employeeId];
            
            if (!employee.attendanceByDate[date]) {
                employee.attendanceByDate[date] = [];
            }

            const dayInfo = getDayInfo(date, selectedMonth, selectedYear);
            
            employee.attendanceByDate[date].push({
                time: time,
                column: 'MANUAL',
                machine: 'EDIT',
                dayInfo: dayInfo,
                originalValue: time
            });

            // Urutkan ulang berdasarkan waktu
            employee.attendanceByDate[date].sort((a, b) => a.time.localeCompare(b.time));

            renderEditContent(employeeId, employee);
            alert('Data absensi berhasil ditambahkan!');
        }

        function saveEditedData() {
            if (!currentEditEmployeeId) return;

            const employee = processedData.employees[currentEditEmployeeId];
            const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
            
            // Update waktu dari input fields
            for (let date = 1; date <= daysInMonth; date++) {
                const dayAttendance = employee.attendanceByDate[date];
                if (!dayAttendance) continue;

                const categorizedEntries = categorizeAttendanceEntries(dayAttendance, employee.divisi, date, selectedMonth, selectedYear, currentEditEmployeeId);
                
                categorizedEntries.forEach((entry, idx) => {
                    const input = document.getElementById(`edit_${currentEditEmployeeId}_${date}_${idx}`);
                    if (input && input.value) {
                        // Update waktu di entry asli
                        const originalEntry = dayAttendance.find(e => 
                            e.time === entry.time && e.machine === entry.machine
                        );
                        if (originalEntry) {
                            originalEntry.time = input.value;
                            originalEntry.originalValue = input.value;
                        }
                    }
                });

                // Urutkan ulang
                dayAttendance.sort((a, b) => a.time.localeCompare(b.time));
            }

            // Refresh tampilan
            displayCombinedResults(processedData);
            closeEditModal();
            alert('Perubahan berhasil disimpan!\n\nJangan lupa simpan data ke JSON agar perubahan tidak hilang.');
        }

        function getPermitForEmployee(employeeId, date, type) {
            const permitKey = `${employeeId}-${date}`;
            const permit = permitData[permitKey];
            if (!permit || permit.month !== selectedMonth || permit.year !== selectedYear) return null;
            if (permit.type === 'full') return permit;
            if (permit.type === type) return permit;
            return null;
        }

        function displayFilteredResults() {
            const startIndex = (currentPage - 1) * recordsPerPage;
            const endIndex = startIndex + recordsPerPage;
            const pageData = filteredEmployeesArray.slice(startIndex, endIndex);
            const resultsTable = document.getElementById('resultsTable');
            resultsTable.innerHTML = '';
            
            pageData.forEach((employee, index) => {
                const globalIndex = startIndex + index;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${employee.id}</strong></td>
                    <td>${getDivisionFullName(employee.divisi)}</td>
                    <td><strong>${employee.nama}</strong></td>
                    <td class="attendance-detail">${employee.attendanceDetail}</td>
                    <td style="text-align: center; font-weight: bold;">${employee.daysPresent}</td>
                    <td style="text-align: center; font-weight: bold; color: ${employee.totalPenalty > 0 ? 'red' : 'green'};">${formatCurrency(employee.totalPenalty)}</td>
                    <td style="text-align: center;">
                        <button onclick="openEditModal('${employee.id}')" style="padding: 5px 10px; background: #3498db; color: white; border: none; cursor: pointer; font-size: 11px;">Edit</button>
                    </td>
                `;
                resultsTable.appendChild(tr);
            });
            
            if (filteredEmployeesArray.length <= recordsPerPage && currentPage === 1) {
                addSummaryRow(resultsTable);
            }
            setupPagination();
        }

        function addSummaryRow(table) {
            const totalPenalties = filteredEmployeesArray.reduce((sum, emp) => sum + emp.totalPenalty, 0);
            const totalEmployees = filteredEmployeesArray.length;
            const summaryRow = document.createElement('tr');
            summaryRow.className = 'summary-row';
            summaryRow.innerHTML = `
                <td colspan="5" style="text-align: right; padding: 12px;"><strong>Total untuk ${monthNames[selectedMonth]} ${selectedYear}:</strong></td>
                <td style="text-align: center; padding: 12px;"><strong>${formatCurrency(totalPenalties)}</strong></td>
                <td></td>
            `;
            table.appendChild(summaryRow);
        }

        function setupPagination() {
            const totalPages = Math.ceil(filteredEmployeesArray.length / recordsPerPage);
            const paginationTop = document.getElementById('paginationTop');
            const paginationBottom = document.getElementById('paginationBottom');
            if (totalPages <= 1) {
                paginationTop.style.display = 'none';
                paginationBottom.style.display = 'none';
                return;
            }
            const paginationHTML = createPaginationHTML(totalPages);
            paginationTop.innerHTML = paginationHTML;
            paginationBottom.innerHTML = paginationHTML;
            paginationTop.style.display = 'flex';
            paginationBottom.style.display = 'flex';
        }

        function createPaginationHTML(totalPages) {
            let html = '';
            html += `<button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Prev</button>`;
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);
            
            if (startPage > 1) {
                html += `<button onclick="changePage(1)">1</button>`;
                if (startPage > 2) html += `<span style="padding: 8px;">...</span>`;
            }
            
            for (let i = startPage; i <= endPage; i++) {
                html += `<button onclick="changePage(${i})" ${i === currentPage ? 'class="active"' : ''}>${i}</button>`;
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) html += `<span style="padding: 8px;">...</span>`;
                html += `<button onclick="changePage(${totalPages})">${totalPages}</button>`;
            }
            html += `<button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
            return html;
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredEmployeesArray.length / recordsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            displayFilteredResults();
        }

        function displayCombinedResults(combinedData) {
            const employees = combinedData.employees;
            const stats = combinedData.stats;
            document.getElementById('totalEmployees').textContent = stats.totalEmployees;
            document.getElementById('filesProcessed').textContent = stats.filesProcessed;
            document.getElementById('totalRecords').textContent = stats.totalRecords;
            
            originalEmployeesArray = [];
            let grandTotalPenalties = 0;
            const sortedIds = Object.keys(employees).sort((a, b) => parseInt(a) - parseInt(b));
            
            sortedIds.forEach((id) => {
                const employee = employees[id];
                const attendanceByDate = employee.attendanceByDate;
                const daysPresent = Object.keys(attendanceByDate).length;
                const completenessInfo = checkAttendanceCompleteness(attendanceByDate, employee.divisi, selectedMonth, selectedYear, id);
                const sortedDates = Object.keys(attendanceByDate).sort((a, b) => parseInt(a) - parseInt(b));
                
                let totalEmployeePenalties = 0;
                let attendanceDetail = '';
                totalEmployeePenalties += completenessInfo.incompletePenalty + completenessInfo.absentPenalty;
                
                if (sortedDates.length === 0 && completenessInfo.absentWorkingDays.length === 0) {
                    attendanceDetail = '<div class="day-info">Tidak ada data kehadiran</div>';
                } else {
                    const divisionSchedule = getDivisionSchedule(employee.divisi);
                    let scheduleDisplay = `<div style="font-size: 11px; color: #666; margin-bottom: 8px; padding: 4px 8px; background: #e8f4fd;">Jadwal: ${divisionSchedule.start_time} - ${divisionSchedule.end_time}`;
                    if (config && config.division_schedules && config.division_schedules[employee.divisi] && config.division_schedules[employee.divisi].friday_schedule) {
                        const fridaySchedule = config.division_schedules[employee.divisi].friday_schedule;
                        scheduleDisplay += ` | Jumat: ${fridaySchedule.start_time} - ${fridaySchedule.end_time}`;
                    }
                    scheduleDisplay += `</div>`;
                    
                    let attendancePreview = '';
                    let attendanceFull = '';
                    const previewLimit = 7;
                    
                    for (let i = 0; i < sortedDates.length; i++) {
                        const tanggal = sortedDates[i];
                        const dayInfo = attendanceByDate[tanggal][0].dayInfo;
                        let dayContent = `<div class="day-info"><strong>${tanggal} ${monthNames[selectedMonth]} (${dayInfo.dayName})</strong></div>`;
                        const categorizedEntries = categorizeAttendanceEntries(attendanceByDate[tanggal], employee.divisi, parseInt(tanggal), selectedMonth, selectedYear, id);
                        
                        const hasIncompleteAttendancePenalty = checkDayIncompleteAttendance(categorizedEntries, dayInfo.isWorkingDay, id, parseInt(tanggal));
                        
                        if (dayInfo.isWorkingDay) {
                            const arrivalPermit = getPermitForEmployee(id, parseInt(tanggal), 'arrival');
                            const departurePermit = getPermitForEmployee(id, parseInt(tanggal), 'departure');
                            const hasArrival = categorizedEntries.some(entry => entry.type === 'arrival');
                            const hasDeparture = categorizedEntries.some(entry => entry.type === 'departure');
                            
                            if (!arrivalPermit && !hasArrival) {
                                dayContent += `<div class="time-entry" style="color: #e67e22;">Tidak Ada Absen Datang - Denda: ${formatCurrency(getIncompleteAttendancePenalty())}</div>`;
                            }
                            if (!departurePermit && !hasDeparture) {
                                dayContent += `<div class="time-entry" style="color: #e67e22;">Tidak Ada Absen Pulang - Denda: ${formatCurrency(getIncompleteAttendancePenalty())}</div>`;
                            }
                        }
                        
                        for (const entry of categorizedEntries) {
                            const machineInfo = `Mesin ${entry.machine}`;
                            const typeIcon = entry.type === 'arrival' ? '→' : '←';
                            
                            let penaltyDisplay = entry.penaltyInfo;
                            let actualPenalty = entry.penaltyAmount;
                            
                            if (hasIncompleteAttendancePenalty && entry.penaltyAmount > 0 && !entry.permitInfo) {
                                penaltyDisplay = `<span class="waived-penalty">${entry.penaltyInfo}</span> <span style="color: #27ae60; font-size: 10px;">(Dibatalkan karena kehadiran tidak lengkap)</span>`;
                                actualPenalty = 0;
                            }
                            
                            dayContent += `<div class="time-entry">${typeIcon} ${entry.label}: <strong>${entry.formattedTime}</strong> (${entry.column}) - ${machineInfo} ${entry.scheduleInfo} ${entry.exceptionInfo}`;
                            if (entry.permitInfo) {
                                dayContent += `<span style="color: #0984e3; font-weight: bold;">${entry.permitInfo}</span>`;
                            }
                            if (penaltyDisplay) {
                                dayContent += `<span class="penalty-info">${penaltyDisplay}</span>`;
                            }
                            dayContent += `</div>`;
                            
                            if (i < previewLimit) {
                                totalEmployeePenalties += actualPenalty;
                            }
                        }
                        
                        if (i < previewLimit) {
                            attendancePreview += dayContent;
                        }
                        attendanceFull += dayContent;
                        if (i >= previewLimit) {
                            categorizedEntries.forEach(entry => {
                                let actualPenalty = entry.penaltyAmount;
                                if (hasIncompleteAttendancePenalty && entry.penaltyAmount > 0 && !entry.permitInfo) {
                                    actualPenalty = 0;
                                }
                                totalEmployeePenalties += actualPenalty;
                            });
                        }
                    }
                    
                    if (completenessInfo.absentWorkingDays.length > 0) {
                        const absentDaysText = completenessInfo.absentWorkingDays.map(d => {
                            const permitKey = `${id}-${d}`;
                            if (permitData[permitKey] && permitData[permitKey].type === 'full') {
                                return `${d} (${getPermitTypeName(permitData[permitKey].type)}: ${permitData[permitKey].reason})`;
                            }
                            return d.toString();
                        }).join(', ');
                        const absentInfo = `<div class="day-info" style="background: #ffeaa7; border-left-color: #fdcb6e;">Hari Kerja Tidak Hadir: ${absentDaysText}${completenessInfo.absentPenalty > 0 ? ` - Denda: ${formatCurrency(completenessInfo.absentPenalty)}` : ''}</div>`;
                        attendancePreview += absentInfo;
                        attendanceFull += absentInfo;
                    }
                    
                    const permitDays = [];
                    for (let date = 1; date <= new Date(selectedYear, selectedMonth, 0).getDate(); date++) {
                        const permitKey = `${id}-${date}`;
                        if (permitData[permitKey] && permitData[permitKey].type === 'full') {
                            const dayInfo = getDayInfo(date, selectedMonth, selectedYear);
                            if (dayInfo.isWorkingDay && !attendanceByDate[date]) {
                                permitDays.push({ date, reason: permitData[permitKey].reason, type: permitData[permitKey].type });
                            }
                        }
                    }
                    
                    if (permitDays.length > 0) {
                        const permitInfo = permitDays.map(p => `${p.date} (${p.reason})`).join(', ');
                        const permitDisplay = `<div class="day-info" style="background: #dfe6e9; border-left-color: #74b9ff;">Hari Izin Penuh: ${permitInfo}</div>`;
                        attendancePreview += permitDisplay;
                        attendanceFull += permitDisplay;
                    }
                    
                    let penaltySummary = '';
                    if (totalEmployeePenalties > 0) {
                        let penaltyBreakdown = [];
                        if (completenessInfo.incompletePenalty > 0) {
                            penaltyBreakdown.push(`Kehadiran Tidak Lengkap: ${formatCurrency(completenessInfo.incompletePenalty)}`);
                        }
                        if (completenessInfo.absentPenalty > 0) {
                            penaltyBreakdown.push(`Tidak Hadir: ${formatCurrency(completenessInfo.absentPenalty)}`);
                        }
                        penaltySummary = `<div style="background: #fadbd8; padding: 8px; margin-top: 8px; border-left: 4px solid red;">
                            <div style="font-weight: bold; color: red;">Total Denda: ${formatCurrency(totalEmployeePenalties)}</div>
                            ${penaltyBreakdown.length > 0 ? `<div style="font-size: 11px; margin-top: 4px;">${penaltyBreakdown.join(' | ')}</div>` : ''}
                        </div>`;
                    }
                    
                    const detailId = `detail_${originalEmployeesArray.length}`;
                    attendanceDetail = `
                        ${scheduleDisplay}
                        <div style="font-weight: bold; margin-bottom: 8px;">
                            ${daysPresent} hari hadir di ${monthNames[selectedMonth]}
                            ${sortedDates.length > previewLimit ? `<button class="toggle-btn" onclick="toggleDetail('${detailId}', this)">Lihat Semua</button>` : ''}
                        </div>
                        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 8px; background: #fdfdfd;">
                            ${sortedDates.length > previewLimit ? attendancePreview : attendanceFull}
                            ${sortedDates.length > previewLimit ? `<div id="${detailId}" style="display:none;">${attendanceFull}</div>` : ''}
                        </div>
                        ${penaltySummary}
                    `;
                }
                
                grandTotalPenalties += totalEmployeePenalties;
                originalEmployeesArray.push({
                    id: id,
                    nama: employee.nama,
                    divisi: employee.divisi,
                    daysPresent: daysPresent,
                    totalPenalty: totalEmployeePenalties,
                    attendanceDetail: attendanceDetail
                });
            });
            
            document.getElementById('totalPenalties').textContent = formatCurrency(grandTotalPenalties);
            filteredEmployeesArray = [...originalEmployeesArray];
            currentPage = 1;
            displayFilteredResults();
            updateSearchResults();
        }

        function checkDayIncompleteAttendance(categorizedEntries, isWorkingDay, employeeId, date) {
            if (!isWorkingDay) return false;
            
            const arrivalPermit = getPermitForEmployee(employeeId, date, 'arrival');
            const departurePermit = getPermitForEmployee(employeeId, date, 'departure');
            const fullPermit = getPermitForEmployee(employeeId, date, 'full');
            
            if (fullPermit) return false;
            
            const hasArrival = categorizedEntries.some(entry => entry.type === 'arrival');
            const hasDeparture = categorizedEntries.some(entry => entry.type === 'departure');
            
            const needsArrival = !arrivalPermit;
            const needsDeparture = !departurePermit;
            
            if ((needsArrival && !hasArrival) || (needsDeparture && !hasDeparture)) {
                return true;
            }
            
            return false;
        }

        function getDateFromColumnIndex(colIndex) {
            return colIndex + 1;
        }
        
        function getColumnLetter(colIndex) {
            let result = '';
            while (colIndex >= 0) {
                result = String.fromCharCode((colIndex % 26) + 65) + result;
                colIndex = Math.floor(colIndex / 26) - 1;
            }
            return result;
        }
        
        function getDayInfo(tanggal, month, year) {
            const date = new Date(year, month - 1, tanggal);
            const dayName = dayNames[date.getDay()];
            const isWeekend = date.getDay() === 0 || date.getDay() === 6;
            const holiday = isHoliday(tanggal, month, year);
            const specialEvent = getSpecialEvent(tanggal, month, year);
            return {
                dayName: dayName,
                isWeekend: isWeekend,
                fullDate: date,
                holiday: holiday,
                specialEvent: specialEvent,
                isWorkingDay: isWorkingDay(tanggal, month, year)
            };
        }
        
        function parseTimeString(timeStr) {
            const cleanStr = timeStr.toString().trim();
            if (cleanStr.match(/^\d{1,2}:\d{2}$/)) {
                const parts = cleanStr.split(':');
                return {hour: parseInt(parts[0]), minute: parseInt(parts[1])};
            } else if (cleanStr.match(/^\d{4}$/)) {
                return {hour: parseInt(cleanStr.substring(0, 2)), minute: parseInt(cleanStr.substring(2, 4))};
            } else if (cleanStr.match(/^\d{3}$/)) {
                return {hour: parseInt(cleanStr.substring(0, 1)), minute: parseInt(cleanStr.substring(1, 3))};
            }
            return { hour: 0, minute: 0 };
        }
        
        function calculateLateMinutes(arrivalTime, divisionKey, date, month, year) {
            const arrival = parseTimeString(arrivalTime);
            const arrivalMinutes = arrival.hour * 60 + arrival.minute;
            const workStart = parseWorkStartTime(divisionKey, date, month, year);
            const workStartMinutes = workStart.hour * 60 + workStart.minute;
            if (arrivalMinutes > workStartMinutes) {
                return arrivalMinutes - workStartMinutes;
            }
            return 0;
        }

        function calculateEarlyDepartureMinutes(departureTime, divisionKey, date, month, year) {
            const departure = parseTimeString(departureTime);
            const departureMinutes = departure.hour * 60 + departure.minute;
            const workEnd = parseWorkEndTime(divisionKey, date, month, year);
            const workEndMinutes = workEnd.hour * 60 + workEnd.minute;
            if (departureMinutes < workEndMinutes) {
                return workEndMinutes - departureMinutes;
            }
            return 0;
        }
        
        function formatTime(timeStr) {
            const time = parseTimeString(timeStr);
            return `${time.hour.toString().padStart(2, '0')}:${time.minute.toString().padStart(2, '0')}`;
        }
        
        function splitCombinedTimes(timeStr) {
            const cleanStr = timeStr.toString().trim().replace(/\s+/g, '');
            if (cleanStr.match(/^\d{1,2}:\d{2}$/) || cleanStr.match(/^\d{3,4}$/)) {
                return [cleanStr];
            }
            const times = [];
            if (cleanStr.match(/\d{1,2}:\d{2}[\s,;-]+\d{1,2}:\d{2}/)) {
                const parts = cleanStr.split(/[\s,;-]+/);
                return parts.filter(part => part.match(/^\d{1,2}:\d{2}$/));
            }
            const timeRegex = /(\d{1,2}:\d{2})/g;
            let match;
            while ((match = timeRegex.exec(cleanStr)) !== null) {
                times.push(match[1]);
            }
            if (times.length >= 2) {
                return times;
            }
            const fourDigitRegex = /(\d{4})/g;
            const fourDigitMatches = [];
            while ((match = fourDigitRegex.exec(cleanStr)) !== null) {
                const timeStr = match[1];
                const hour = parseInt(timeStr.substring(0, 2));
                const minute = parseInt(timeStr.substring(2, 4));
                if (hour <= 23 && minute <= 59) {
                    fourDigitMatches.push(timeStr);
                }
            }
            if (fourDigitMatches.length >= 2) {
                return fourDigitMatches;
            }
            if (cleanStr.match(/^\d{8}$/)) {
                const time1 = cleanStr.substring(0, 4);
                const time2 = cleanStr.substring(4, 8);
                return [time1, time2];
            }
            return [cleanStr];
        }
        
        function categorizeAttendanceEntries(entries, divisionKey, date, month, year, employeeId) {
            if (!entries || entries.length === 0) return [];
            
            const sortedEntries = entries.sort((a, b) => a.time.localeCompare(b.time));
            
            const arrivalEntries = [];
            const departureEntries = [];
            
            const currentDate = new Date(year, month - 1, date);
            const dayOfWeek = currentDate.getDay();
            const isFriday = dayOfWeek === 5;
            
            const cutoffHour = isFriday ? 10 : 12;
            
            sortedEntries.forEach(entry => {
                const hour = parseTimeToHour(entry.time);
                if (hour < cutoffHour) {
                    arrivalEntries.push(entry);
                } else {
                    departureEntries.push(entry);
                }
            });
            
            const selectedEntries = [];
            if (arrivalEntries.length > 0) {
                selectedEntries.push(arrivalEntries[0]);
            }
            if (departureEntries.length > 0) {
                selectedEntries.push(departureEntries[departureEntries.length - 1]);
            }
            
            return selectedEntries.map(entry => {
                const hour = parseTimeToHour(entry.time);
                const type = hour < cutoffHour ? 'arrival' : 'departure';
                let penaltyInfo = '';
                let penaltyAmount = 0;
                let permitInfo = '';
                const schedule = getDivisionSchedule(divisionKey, date, month, year);
                let scheduleInfo = `(${schedule.start_time}-${schedule.end_time})`;
                const dayInfo = entry.dayInfo;
                let exceptionInfo = '';
                const permit = getPermitForEmployee(employeeId, date, type);

                if (dayOfWeek === 0 || dayOfWeek === 6) {
                    exceptionInfo = `WEEKEND`;
                } else if (dayInfo.holiday) {
                    exceptionInfo = `LIBUR: ${dayInfo.holiday.name}`;
                } else if (dayInfo.specialEvent) {
                    exceptionInfo = `EVENT: ${dayInfo.specialEvent.name}`;
                    if (schedule.isSpecialEvent) {
                        scheduleInfo = `(Event: ${schedule.start_time}-${schedule.end_time})`;
                    }
                } else {
                    if (schedule.isFriday) {
                        exceptionInfo = `JUMAT`;
                        scheduleInfo = `(Jumat: ${schedule.start_time}-${schedule.end_time})`;
                    }
                    
                    if (permit) {
                        permitInfo = ` - ${getPermitTypeName(permit.type)}: ${permit.reason}`;
                    } else {
                        if (type === 'arrival') {
                            const lateMinutes = calculateLateMinutes(entry.time, divisionKey, date, month, year);
                            if (lateMinutes > 0) {
                                const policy = getLatePolicy(lateMinutes);
                                if (policy) {
                                    penaltyAmount = policy.penalty;
                                    penaltyInfo = ` - Terlambat ${lateMinutes} menit, Denda: ${formatCurrency(penaltyAmount)}`;
                                }
                            }
                        } else {
                            const earlyMinutes = calculateEarlyDepartureMinutes(entry.time, divisionKey, date, month, year);
                            if (earlyMinutes > 0) {
                                const policy = getEarlyDeparturePolicy(earlyMinutes);
                                if (policy) {
                                    penaltyAmount = policy.penalty;
                                    penaltyInfo = ` - Pulang Cepat ${earlyMinutes} menit, Denda: ${formatCurrency(penaltyAmount)}`;
                                }
                            }
                        }
                    }
                }
                
                return {
                    time: entry.time,
                    formattedTime: formatTime(entry.time),
                    column: entry.column,
                    machine: entry.machine,
                    type: type,
                    label: type === 'arrival' ? 'Datang' : 'Pulang',
                    penaltyInfo: penaltyInfo,
                    penaltyAmount: penaltyAmount,
                    scheduleInfo: scheduleInfo,
                    exceptionInfo: exceptionInfo,
                    permitInfo: permitInfo
                };
            });
        }

        function parseTimeToHour(timeStr) {
            const time = parseTimeString(timeStr);
            return time.hour + (time.minute / 60);
        }
        
        function checkAttendanceCompleteness(attendanceByDate, divisionKey, month, year, employeeId) {
            const daysInMonth = new Date(year, month, 0).getDate();
            let incompleteDays = [];
            let absentWorkingDays = [];
            let incompleteCount = 0;
            let absentCount = 0;
            
            for (let date = 1; date <= daysInMonth; date++) {
                const dayInfo = getDayInfo(date, month, year);
                if (!dayInfo.isWorkingDay) continue;
                
                const arrivalPermit = getPermitForEmployee(employeeId, date, 'arrival');
                const departurePermit = getPermitForEmployee(employeeId, date, 'departure');
                const fullPermit = getPermitForEmployee(employeeId, date, 'full');
                
                if (fullPermit) continue;
                
                const dayAttendance = attendanceByDate[date];
                
                if (!dayAttendance || dayAttendance.length === 0) {
                    absentWorkingDays.push(date);
                    absentCount++;
                    continue;
                }
                
                const categorizedEntries = categorizeAttendanceEntries(dayAttendance, divisionKey, date, month, year, employeeId);
                const hasArrival = categorizedEntries.some(entry => entry.type === 'arrival');
                const hasDeparture = categorizedEntries.some(entry => entry.type === 'departure');
                
                const needsArrival = !arrivalPermit;
                const needsDeparture = !departurePermit;
                
                if ((needsArrival && !hasArrival) || (needsDeparture && !hasDeparture)) {
                    incompleteDays.push(date);
                    incompleteCount++;
                }
            }
            
            const incompletePenalty = incompleteCount * getIncompleteAttendancePenalty();
            const absentPenalty = absentCount * getIncompleteAttendancePenalty();
            
            return {
                incompleteDays: incompleteDays,
                absentWorkingDays: absentWorkingDays,
                incompleteCount: incompleteCount,
                absentCount: absentCount,
                incompletePenalty: incompletePenalty,
                absentPenalty: absentPenalty
            };
        }
        
        function toggleDetail(detailId, button) {
            const detailDiv = document.getElementById(detailId);
            if (detailDiv.style.display === 'none') {
                detailDiv.style.display = 'block';
                button.textContent = 'Sembunyikan';
            } else {
                detailDiv.style.display = 'none';
                button.textContent = 'Lihat Semua';
            }
        }
        
        function exportToExcel(type) {
            if (!processedData) {
                alert('Tidak ada data untuk di-export!');
                return;
            }
            
            let ws_data = [];
            let filename = '';
            
            if (type === 'detailed') {
                filename = `Detail_Kehadiran_${monthNames[selectedMonth]}_${selectedYear}.xlsx`;
                ws_data.push(['ID', 'Divisi', 'Nama', 'Tanggal', 'Hari', 'Waktu', 'Tipe', 'Mesin', 'Kolom', 'Jadwal', 'Status', 'Denda']);
                
                const dataToExport = type === 'filtered' ? filteredEmployeesArray : originalEmployeesArray;
                dataToExport.forEach(employee => {
                    const empData = processedData.employees[employee.id];
                    const sortedDates = Object.keys(empData.attendanceByDate).sort((a, b) => parseInt(a) - parseInt(b));
                    
                    sortedDates.forEach(date => {
                        const dayInfo = getDayInfo(parseInt(date), selectedMonth, selectedYear);
                        const categorizedEntries = categorizeAttendanceEntries(
                            empData.attendanceByDate[date], 
                            empData.divisi, 
                            parseInt(date), 
                            selectedMonth, 
                            selectedYear,
                            employee.id
                        );
                        
                        categorizedEntries.forEach(entry => {
                            const schedule = getDivisionSchedule(empData.divisi, parseInt(date), selectedMonth, selectedYear);
                            let statusText = entry.exceptionInfo || 'Normal';
                            if (entry.permitInfo) statusText = entry.permitInfo.replace(' - ', '');
                            
                            ws_data.push([
                                employee.id,
                                getDivisionFullName(empData.divisi),
                                empData.nama,
                                `${date} ${monthNames[selectedMonth]} ${selectedYear}`,
                                dayInfo.dayName,
                                entry.formattedTime,
                                entry.label,
                                `Mesin ${entry.machine}`,
                                entry.column,
                                `${schedule.start_time}-${schedule.end_time}`,
                                statusText,
                                entry.penaltyAmount
                            ]);
                        });
                    });
                });
            } else if (type === 'summary') {
                filename = `Ringkasan_Denda_${monthNames[selectedMonth]}_${selectedYear}.xlsx`;
                ws_data.push(['ID', 'Divisi', 'Nama', 'Total Hari Hadir', 'Total Denda', 'Keterangan']);
                
                const dataToExport = type === 'filtered' ? filteredEmployeesArray : originalEmployeesArray;
                dataToExport.forEach(employee => {
                    const empData = processedData.employees[employee.id];
                    const completenessInfo = checkAttendanceCompleteness(
                        empData.attendanceByDate, 
                        empData.divisi, 
                        selectedMonth, 
                        selectedYear,
                        employee.id
                    );
                    
                    let keterangan = [];
                    if (completenessInfo.incompleteCount > 0) {
                        keterangan.push(`${completenessInfo.incompleteCount} hari tidak lengkap`);
                    }
                    if (completenessInfo.absentCount > 0) {
                        keterangan.push(`${completenessInfo.absentCount} hari tidak hadir`);
                    }
                    
                    ws_data.push([
                        employee.id,
                        getDivisionFullName(empData.divisi),
                        empData.nama,
                        employee.daysPresent,
                        employee.totalPenalty,
                        keterangan.join(', ') || 'Lengkap'
                    ]);
                });
                
                const totalPenalty = dataToExport.reduce((sum, emp) => sum + emp.totalPenalty, 0);
                ws_data.push(['', '', 'TOTAL', '', totalPenalty, '']);
            } else if (type === 'filtered') {
                filename = `Data_Terfilter_${monthNames[selectedMonth]}_${selectedYear}.xlsx`;
                ws_data.push(['ID', 'Divisi', 'Nama', 'Total Hari Hadir', 'Total Denda']);
                
                filteredEmployeesArray.forEach(employee => {
                    ws_data.push([
                        employee.id,
                        getDivisionFullName(employee.divisi),
                        employee.nama,
                        employee.daysPresent,
                        employee.totalPenalty
                    ]);
                });
            }
            
            const ws = XLSX.utils.aoa_to_sheet(ws_data);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Data");
            XLSX.writeFile(wb, filename);
        }
    </script>
</body>
</html>