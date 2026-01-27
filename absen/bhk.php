<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembaca Excel Absensi - Multi File</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .config-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #007bff;
            border-radius: 8px;
            color: white;
        }

        .config-section h3 {
            margin: 0 0 10px 0;
            color: white;
        }

        .config-file-button {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .config-file-button:hover {
            background: rgba(255,255,255,0.3);
        }

        .config-file-button.loaded {
            background: rgba(255,255,255,0.9);
            color: #007bff;
            border-color: white;
        }

        .config-status {
            margin-top: 8px;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 14px;
        }

        .config-loaded {
            background: rgba(255,255,255,0.9);
            color: #007bff;
        }

        .config-error {
            background: #dc3545;
            color: white;
        }

        .config-required {
            background: #f39c12;
            color: white;
        }

        .disabled-overlay {
            opacity: 0.3;
            pointer-events: none;
        }

        .month-selection {
            margin-bottom: 20px;
            padding: 15px;
            background: #6c757d;
            border-radius: 8px;
            color: white;
        }
        .month-selection h3 {
            margin: 0 0 10px 0;
            color: white;
        }
        .month-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
        }
        .month-button {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 8px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .month-button:hover {
            background: rgba(255,255,255,0.3);
        }
        .month-button.selected {
            background: rgba(255,255,255,0.9);
            color: #6c757d;
            border-color: white;
        }
        .selected-month-info {
            margin-top: 10px;
            padding: 8px;
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }
        .file-input-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            opacity: 0.5;
        }
        .file-input-section.enabled {
            opacity: 1;
        }
        .file-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .file-input-wrapper {
            flex: 1;
            min-width: 180px;
        }
        .file-input-wrapper label {
            display: block;
            font-weight: bold;
            margin-bottom: 4px;
            color: #495057;
        }
        .file-button {
            background: #007bff;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
        }
        .file-button:hover {
            background: #0056b3;
        }
        .file-button.selected {
            background: #28a745;
        }
        .file-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .process-button {
            background: #dc3545;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 8px;
        }
        .process-button:hover {
            background: #c82333;
        }
        .process-button:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .file-status {
            margin-top: 4px;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .file-loaded {
            background: #d4edda;
            color: #155724;
        }
        .file-empty {
            background: #f8d7da;
            color: #721c24;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .loading {
            text-align: center;
            color: #007bff;
            font-weight: bold;
        }
        .attendance-detail {
            max-width: 500px;
            font-size: 12px;
            max-height: 400px;
            overflow-y: auto;
        }
        .date-header {
            font-weight: bold;
            color: #007bff;
            margin-top: 6px;
            padding: 2px 4px;
            background: #e3f2fd;
            border-radius: 3px;
        }
        .date-header:first-child {
            margin-top: 0;
        }
        .day-name {
            font-size: 10px;
            color: #666;
            font-weight: normal;
        }
        .weekend {
            background: #fff3cd !important;
            color: #856404 !important;
        }
        .holiday {
            background: #ffd7d7 !important;
            color: #dc3545 !important;
        }
        .special-event {
            background: #e1f5fe !important;
            color: #0277bd !important;
        }
        .time-entry {
            margin: 2px 0;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 11px;
        }
        .time-entry.arrival { 
            background: #e8f5e8; 
            border-left: 3px solid #4caf50; 
        }
        .time-entry.departure { 
            background: #ffebee; 
            border-left: 3px solid #f44336; 
        }
        .time-entry.machine-a { 
            background: #e3f2fd; 
            border-left: 3px solid #2196f3; 
        }
        .time-entry.machine-b { 
            background: #f3e5f5; 
            border-left: 3px solid #9c27b0; 
        }
        .time-entry.machine-c { 
            background: #e8f5e8; 
            border-left: 3px solid #4caf50; 
        }
        .time-entry.machine-d { 
            background: #fff3e0; 
            border-left: 3px solid #ff9800; 
        }
        
        .penalty-indicator {
            color: #dc3545;
            font-weight: bold;
            font-size: 11px;
            margin-left: 4px;
            padding: 1px 4px;
            border-radius: 2px;
            display: inline-block;
        }
        
        .toggle-button {
            background: #28a745;
            color: white;
            border: none;
            padding: 2px 6px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 10px;
            margin-left: 4px;
        }
        .toggle-button:hover {
            background: #218838;
        }
        .attendance-summary {
            font-weight: bold;
            color: #495057;
            margin-bottom: 4px;
        }
        .attendance-full {
            display: none;
        }
        .summary-stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }
        .stat-item {
            text-align: center;
            font-weight: bold;
            background: white;
            padding: 10px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 20px;
            color: #007bff;
            margin-top: 4px;
        }
        .warning-message {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
        }
        .penalty-summary {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            margin-top: 8px;
            border-left: 4px solid #dc3545;
        }
        .penalty-total {
            font-weight: bold;
            color: #dc3545;
            font-size: 12px;
        }
        .schedule-indicator {
            font-size: 9px;
            color: #666;
            font-style: italic;
            margin-left: 4px;
        }
        .exception-indicator {
            font-size: 9px;
            color: #dc3545;
            font-weight: bold;
            margin-left: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pembaca Excel Absensi - Multi Mesin 2025</h1>
        
        <div class="config-section">
            <h3>Konfigurasi Sistem (WAJIB)</h3>
            <div>
                <label>Upload file config.json (diperlukan untuk menjalankan aplikasi):</label>
                <input type="file" id="configInput" accept=".json" style="display:none;" />
                <button id="configButton" class="config-file-button" onclick="document.getElementById('configInput').click()">Upload Config.json</button>
                <div id="configStatus" class="config-status config-required">Config belum dimuat - aplikasi tidak dapat berjalan</div>
            </div>
        </div>
        
        <div id="mainContent" class="disabled-overlay">
            <div class="month-selection">
                <h3>Pilih Bulan untuk Data Absensi</h3>
                <div class="month-grid">
                    <button class="month-button" data-month="1">Januari</button>
                    <button class="month-button" data-month="2">Februari</button>
                    <button class="month-button" data-month="3">Maret</button>
                    <button class="month-button" data-month="4">April</button>
                    <button class="month-button" data-month="5">Mei</button>
                    <button class="month-button" data-month="6">Juni</button>
                    <button class="month-button" data-month="7">Juli</button>
                    <button class="month-button" data-month="8">Agustus</button>
                    <button class="month-button" data-month="9">September</button>
                    <button class="month-button" data-month="10">Oktober</button>
                    <button class="month-button" data-month="11">November</button>
                    <button class="month-button" data-month="12">Desember</button>
                </div>
                <div id="selectedMonthInfo" class="selected-month-info" style="display:none;">
                    Bulan Terpilih: <span id="monthName">-</span> <span id="monthYear">2025</span>
                </div>
            </div>
            
            <div id="warningMessage" class="warning-message">
                Silakan pilih bulan terlebih dahulu sebelum upload file
            </div>
            
            <div id="fileSection" class="file-input-section">
                <h3>Upload File dari Setiap Mesin Absensi</h3>
                
                <div class="file-group">
                    <div class="file-input-wrapper">
                        <label>Mesin A:</label>
                        <input type="file" id="fileInputA" accept=".xlsx,.xls" style="display:none;" disabled />
                        <button id="buttonA" class="file-button" disabled onclick="selectFile('A')">Pilih File Mesin A</button>
                        <div id="statusA" class="file-status file-empty">Belum ada file</div>
                    </div>
                    
                    <div class="file-input-wrapper">
                        <label>Mesin B:</label>
                        <input type="file" id="fileInputB" accept=".xlsx,.xls" style="display:none;" disabled />
                        <button id="buttonB" class="file-button" disabled onclick="selectFile('B')">Pilih File Mesin B</button>
                        <div id="statusB" class="file-status file-empty">Belum ada file</div>
                    </div>
                    
                    <div class="file-input-wrapper">
                        <label>Mesin C:</label>
                        <input type="file" id="fileInputC" accept=".xlsx,.xls" style="display:none;" disabled />
                        <button id="buttonC" class="file-button" disabled onclick="selectFile('C')">Pilih File Mesin C</button>
                        <div id="statusC" class="file-status file-empty">Belum ada file</div>
                    </div>
                    
                    <div class="file-input-wrapper">
                        <label>Mesin D:</label>
                        <input type="file" id="fileInputD" accept=".xlsx,.xls" style="display:none;" disabled />
                        <button id="buttonD" class="file-button" disabled onclick="selectFile('D')">Pilih File Mesin D</button>
                        <div id="statusD" class="file-status file-empty">Belum ada file</div>
                    </div>
                </div>
                
                <button id="processButton" class="process-button" disabled onclick="processAllFiles()">
                    Proses Semua File
                </button>
            </div>
            
            <div id="loading" style="display:none;" class="loading">
                <p>Memproses dan menggabungkan file...</p>
            </div>
            
            <div id="summary" style="display:none;" class="summary-stats">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div>Total Karyawan</div>
                        <div class="stat-value" id="totalEmployees">-</div>
                    </div>
                    <div class="stat-item">
                        <div>File Diproses</div>
                        <div class="stat-value" id="filesProcessed">-</div>
                    </div>
                    <div class="stat-item">
                        <div>Total Record</div>
                        <div class="stat-value" id="totalRecords">-</div>
                    </div>
                    <div class="stat-item">
                        <div>Total Denda</div>
                        <div class="stat-value" id="totalPenalties">-</div>
                    </div>
                </div>
            </div>
            
            <div id="results">
                <h3>Data Absensi Gabungan:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Divisi</th>
                            <th>Nama</th>
                            <th>Detail Kehadiran per Tanggal</th>
                            <th>Total Hari Hadir</th>
                            <th>Total Denda</th>
                        </tr>
                    </thead>
                    <tbody id="resultsTable">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let config = null;
        let configLoaded = false;

        let filesData = {
            A: null,
            B: null,
            C: null,
            D: null
        };
        
        let selectedMonth = null;
        let selectedYear = 2025;
        
        const monthNames = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        const dayNames = [
            'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
        ];

        function loadConfigFromFile() {
            const statusDiv = document.getElementById('configStatus');
            const button = document.getElementById('configButton');
            
            statusDiv.textContent = 'Mencari config.json...';
            statusDiv.className = 'config-status';
            
            fetch('./config.json')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Config file tidak ditemukan');
                    }
                    return response.json();
                })
                .then(newConfig => {
                    config = newConfig;
                    configLoaded = true;
                    statusDiv.textContent = 'Config berhasil dimuat dari config.json';
                    statusDiv.className = 'config-status config-loaded';
                    button.className = 'config-file-button loaded';
                    button.textContent = 'Config Loaded';
                    enableApplication();
                    console.log('Config loaded successfully:', config);
                })
                .catch(error => {
                    statusDiv.textContent = 'config.json tidak ditemukan - silakan upload manual';
                    statusDiv.className = 'config-status config-error';
                    console.log('Config file tidak ditemukan');
                });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            loadConfigFromFile();
        });

        document.getElementById('configInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const statusDiv = document.getElementById('configStatus');
            const button = document.getElementById('configButton');
            
            if (!file) return;
            
            statusDiv.textContent = 'Memuat config...';
            statusDiv.className = 'config-status';
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const newConfig = JSON.parse(e.target.result);
                    
                    if (!newConfig.division_schedules && !newConfig.default_work_schedule) {
                        throw new Error('Config tidak lengkap - harus memiliki division_schedules atau default_work_schedule');
                    }
                    
                    if (!newConfig.late_policy) {
                        throw new Error('Config tidak lengkap - harus memiliki late_policy');
                    }
                    
                    config = newConfig;
                    configLoaded = true;
                    statusDiv.textContent = `Config berhasil dimuat: ${file.name}`;
                    statusDiv.className = 'config-status config-loaded';
                    button.className = 'config-file-button loaded';
                    button.textContent = 'Config Loaded';
                    enableApplication();
                } catch (error) {
                    statusDiv.textContent = `Error: ${error.message}`;
                    statusDiv.className = 'config-status config-error';
                    configLoaded = false;
                }
            };
            
            reader.readAsText(file);
        });

        function enableApplication() {
            const mainContent = document.getElementById('mainContent');
            mainContent.classList.remove('disabled-overlay');
        }

        function checkConfigRequired() {
            if (!configLoaded || !config) {
                alert('Config diperlukan untuk menjalankan aplikasi. Silakan upload file config.json terlebih dahulu.');
                return false;
            }
            return true;
        }

        // Check if date is holiday
        function isHoliday(date, month, year) {
            if (!config || !config.holidays) return null;
            
            const dateStr = `${year}-${month.toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}`;
            
            for (const holiday of config.holidays) {
                if (holiday.date === dateStr) {
                    return holiday;
                }
            }
            return null;
        }

        // Check if date has special event
        function getSpecialEvent(date, month, year) {
            if (!config || !config.special_events) return null;
            
            const dateStr = `${year}-${month.toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}`;
            
            for (const event of config.special_events) {
                if (event.date === dateStr) {
                    return event;
                }
            }
            return null;
        }
        
        document.querySelectorAll('.month-button').forEach(button => {
            button.addEventListener('click', function() {
                if (!checkConfigRequired()) return;
                
                document.querySelectorAll('.month-button').forEach(b => b.classList.remove('selected'));
                
                this.classList.add('selected');
                selectedMonth = parseInt(this.getAttribute('data-month'));
                
                document.getElementById('monthName').textContent = monthNames[selectedMonth];
                document.getElementById('selectedMonthInfo').style.display = 'block';
                
                enableFileSection();
                
                document.getElementById('warningMessage').style.display = 'none';
            });
        });
        
        function enableFileSection() {
            const fileSection = document.getElementById('fileSection');
            fileSection.classList.add('enabled');
            
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
        
        function getDivisionById(id) {
            if (!config || !config.division_schedules) return 'Unknown';
            
            const idNum = parseInt(id);
            
            for (const [divisionKey, divisionData] of Object.entries(config.division_schedules)) {
                if (divisionData.ids && divisionData.ids.includes(idNum)) {
                    return divisionKey;
                }
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
                    baseSchedule = {
                        start_time: divisionData.start_time,
                        end_time: divisionData.end_time
                    };
                } else {
                    baseSchedule = config?.default_work_schedule || { start_time: "08:00", end_time: "16:00" };
                }
            }

            // Check for special event override
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

        function formatCurrency(amount) {
            if (!config || !config.currency) return `Rp ${amount.toLocaleString('id-ID')}`;
            return config.currency.format.replace('%s', amount.toLocaleString('id-ID'));
        }

        function getLatePolicy(lateMinutes) {
            if (!config || !config.late_policy) return null;
            
            if (lateMinutes <= config.late_policy.grace_period_minutes) {
                return null;
            }

            for (const level of config.late_policy.levels) {
                if (lateMinutes >= level.min_minutes && lateMinutes <= level.max_minutes) {
                    return level;
                }
            }

            return null;
        }

        function getEarlyDeparturePolicy(earlyMinutes) {
            if (!config || !config.early_departure_policy) return null;
            
            if (earlyMinutes <= config.early_departure_policy.grace_period_minutes) {
                return null;
            }

            for (const level of config.early_departure_policy.levels) {
                if (earlyMinutes >= level.min_minutes && earlyMinutes <= level.max_minutes) {
                    return level;
                }
            }

            return null;
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
                specialEvent: specialEvent
            };
        }
        
        function parseTimeString(timeStr) {
            const cleanStr = timeStr.toString().trim();
            
            if (cleanStr.match(/^\d{1,2}:\d{2}$/)) {
                const parts = cleanStr.split(':');
                return {
                    hour: parseInt(parts[0]),
                    minute: parseInt(parts[1])
                };
            } else if (cleanStr.match(/^\d{4}$/)) {
                return {
                    hour: parseInt(cleanStr.substring(0, 2)),
                    minute: parseInt(cleanStr.substring(2, 4))
                };
            } else if (cleanStr.match(/^\d{3}$/)) {
                return {
                    hour: parseInt(cleanStr.substring(0, 1)),
                    minute: parseInt(cleanStr.substring(1, 3))
                };
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
            
            const combinedPattern1 = cleanStr.match(/^(\d{1,2}:\d{2})(\d{1,2}:\d{2})$/);
            if (combinedPattern1) {
                return [combinedPattern1[1], combinedPattern1[2]];
            }
            
            const mixedPattern = cleanStr.match(/^(\d{4})(\d{1,2}:\d{2})$/);
            if (mixedPattern) {
                return [mixedPattern[1], mixedPattern[2]];
            }
            
            if (cleanStr.match(/^\d{8}$/)) {
                const time1 = cleanStr.substring(0, 4);
                const time2 = cleanStr.substring(4, 8);
                return [time1, time2];
            }
            
            if (cleanStr.match(/^\d{7}$/)) {
                const option1 = [cleanStr.substring(0, 3), cleanStr.substring(3, 7)];
                const option2 = [cleanStr.substring(0, 4), cleanStr.substring(4, 7)];
                
                const validOption1 = option1.filter(t => isValidTimeString(t)).length;
                const validOption2 = option2.filter(t => isValidTimeString(t)).length;
                
                if (validOption1 >= validOption2 && validOption1 > 0) {
                    return option1;
                } else if (validOption2 > 0) {
                    return option2;
                }
            }
            
            const allDigits = cleanStr.match(/\d+/g);
            if (allDigits && allDigits.length >= 2) {
                const validTimes = allDigits.filter(digits => isValidTimeString(digits));
                if (validTimes.length >= 2) {
                    return validTimes;
                }
            }
            
            return [cleanStr];
        }
        
        function isValidTimeString(timeStr) {
            if (!timeStr || timeStr.length < 3) return false;
            
            if (timeStr.match(/^\d{1,2}:\d{2}$/)) {
                const parts = timeStr.split(':');
                const hour = parseInt(parts[0]);
                const minute = parseInt(parts[1]);
                return hour <= 23 && minute <= 59;
            }
            
            if (timeStr.match(/^\d{3,4}$/)) {
                let hour, minute;
                if (timeStr.length === 4) {
                    hour = parseInt(timeStr.substring(0, 2));
                    minute = parseInt(timeStr.substring(2, 4));
                } else {
                    hour = parseInt(timeStr.substring(0, 1));
                    minute = parseInt(timeStr.substring(1, 3));
                }
                return hour <= 23 && minute <= 59;
            }
            
            return false;
        }
        
        function parseTimeToHour(timeStr) {
            if (timeStr.match(/^\d{4}$/)) {
                const hourStr = timeStr.substring(0, 2);
                return parseInt(hourStr) || 0;
            } else if (timeStr.match(/^\d{3}$/)) {
                const hourStr = timeStr.substring(0, 1);
                return parseInt(hourStr) || 0;
            } else if (timeStr.match(/\d{1,2}[:.]\d{2}/)) {
                const parts = timeStr.split(/[:.]/);
                return parseInt(parts[0]) || 0;
            }
            return 0;
        }
        
        function categorizeAttendanceEntries(entries, divisionKey, date, month, year) {
            if (!entries || entries.length === 0) return [];
            
            const sortedEntries = entries.sort((a, b) => a.time.localeCompare(b.time));
            
            return sortedEntries.map(entry => {
                const hour = parseTimeToHour(entry.time);
                const type = hour < 12 ? 'arrival' : 'departure';
                
                let penaltyInfo = '';
                let penaltyAmount = 0;
                
                const schedule = getDivisionSchedule(divisionKey, date, month, year);
                let scheduleInfo = `<span class="schedule-indicator">(${schedule.start_time}-${schedule.end_time})</span>`;
                
                // Check if it's holiday or special event - no penalties applied
                const dayInfo = entry.dayInfo;
                let exceptionInfo = '';
                
                if (dayInfo.holiday) {
                    exceptionInfo = `<span class="exception-indicator">[LIBUR: ${dayInfo.holiday.name}]</span>`;
                } else if (dayInfo.specialEvent) {
                    exceptionInfo = `<span class="exception-indicator">[EVENT: ${dayInfo.specialEvent.name}]</span>`;
                    if (schedule.isSpecialEvent) {
                        scheduleInfo = `<span class="schedule-indicator">(Event: ${schedule.start_time}-${schedule.end_time})</span>`;
                    }
                } else {
                    // Only calculate penalties if not holiday or special event
                    if (type === 'arrival') {
                        const lateMinutes = calculateLateMinutes(entry.time, divisionKey, date, month, year);
                        const latePolicy = getLatePolicy(lateMinutes);
                        
                        if (latePolicy) {
                            penaltyAmount = latePolicy.penalty_amount;
                            penaltyInfo = `<span class="penalty-indicator" style="background-color: ${latePolicy.color}; color: #000;">${latePolicy.icon} ${latePolicy.name} (+${lateMinutes} menit) ${formatCurrency(penaltyAmount)}</span>`;
                        }
                    } else {
                        const earlyMinutes = calculateEarlyDepartureMinutes(entry.time, divisionKey, date, month, year);
                        const earlyPolicy = getEarlyDeparturePolicy(earlyMinutes);
                        
                        if (earlyPolicy) {
                            penaltyAmount = earlyPolicy.penalty_amount;
                            penaltyInfo = `<span class="penalty-indicator" style="background-color: ${earlyPolicy.color}; color: #000;">${earlyPolicy.icon} ${earlyPolicy.name} (-${earlyMinutes} menit) ${formatCurrency(penaltyAmount)}</span>`;
                        }
                    }
                }
                
                return {
                    ...entry,
                    type: type,
                    label: type === 'arrival' ? 'Kedatangan' : 'Kepulangan',
                    penaltyInfo: penaltyInfo,
                    penaltyAmount: penaltyAmount,
                    formattedTime: formatTime(entry.time),
                    scheduleInfo: scheduleInfo,
                    exceptionInfo: exceptionInfo
                };
            });
        }
        
        function toggleDetail(detailId, button) {
            const detailDiv = document.getElementById(detailId);
            
            if (detailDiv.style.display === 'none' || detailDiv.style.display === '') {
                detailDiv.style.display = 'block';
                button.textContent = 'Sembunyikan';
            } else {
                detailDiv.style.display = 'none';
                button.textContent = 'Tampilkan Semua';
            }
        }
        
        ['A', 'B', 'C', 'D'].forEach(machine => {
            document.getElementById(`fileInput${machine}`).addEventListener('change', (event) => {
                handleFileUpload(event, machine);
            });
        });
        
        function handleFileUpload(event, machine) {
            if (!checkConfigRequired()) return;
            
            const file = event.target.files[0];
            const statusDiv = document.getElementById(`status${machine}`);
            const button = document.getElementById(`button${machine}`);
            
            if (!file) {
                filesData[machine] = null;
                statusDiv.textContent = 'Belum ada file';
                statusDiv.className = 'file-status file-empty';
                button.className = 'file-button';
                updateProcessButton();
                return;
            }
            
            statusDiv.textContent = 'Memuat...';
            statusDiv.className = 'file-status';
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {type: 'array'});
                    
                    let sheetIndex = 2;
                    if (workbook.SheetNames.length < 3) {
                        sheetIndex = 0;
                    }
                    
                    const sheetName = workbook.SheetNames[sheetIndex];
                    const worksheet = workbook.Sheets[sheetName];
                    
                    const jsonData = XLSX.utils.sheet_to_json(worksheet, {
                        header: 1,
                        defval: '',
                        raw: false
                    });
                    
                    filesData[machine] = jsonData;
                    statusDiv.textContent = `${file.name} (${jsonData.length} baris)`;
                    statusDiv.className = 'file-status file-loaded';
                    button.className = 'file-button selected';
                    
                    updateProcessButton();
                    
                } catch (error) {
                    console.error('Error:', error);
                    statusDiv.textContent = `Error: ${error.message}`;
                    statusDiv.className = 'file-status file-empty';
                    button.className = 'file-button';
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
            
            setTimeout(() => {
                try {
                    const combinedData = combineAllMachineData();
                    displayCombinedResults(combinedData);
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('summary').style.display = 'block';
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error memproses file: ' + error.message);
                    document.getElementById('loading').style.display = 'none';
                }
            }, 100);
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
                    
                    if (namaRowIndex >= data.length || absensiRowIndex >= data.length) {
                        break;
                    }
                    
                    const namaRow = data[namaRowIndex];
                    const absensiRow = data[absensiRowIndex];
                    
                    let nama = '';
                    let id = '';
                    
                    if (namaRow.length > 10 && namaRow[10]) {
                        nama = namaRow[10].toString().trim();
                    }
                    
                    if (namaRow.length > 2 && namaRow[2]) {
                        id = namaRow[2].toString().trim();
                    }
                    
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
                                if (dayInfo.fullDate.getMonth() !== selectedMonth - 1) {
                                    continue;
                                }
                                
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
        
        function displayCombinedResults(combinedData) {
            const resultsTable = document.getElementById('resultsTable');
            resultsTable.innerHTML = '';
            
            const employees = combinedData.employees;
            const stats = combinedData.stats;
            
            document.getElementById('totalEmployees').textContent = stats.totalEmployees;
            document.getElementById('filesProcessed').textContent = stats.filesProcessed;
            document.getElementById('totalRecords').textContent = stats.totalRecords;
            
            let totalDaysPresent = 0;
            let employeeCount = 0;
            let grandTotalPenalties = 0;
            
            const sortedIds = Object.keys(employees).sort((a, b) => parseInt(a) - parseInt(b));
            
            sortedIds.forEach((id, index) => {
                const employee = employees[id];
                const attendanceByDate = employee.attendanceByDate;
                
                const daysPresent = Object.keys(attendanceByDate).length;
                totalDaysPresent += daysPresent;
                employeeCount++;
                
                const sortedDates = Object.keys(attendanceByDate).sort((a, b) => parseInt(a) - parseInt(b));
                
                let attendancePreview = '';
                let attendanceFull = '';
                let totalEmployeePenalties = 0;
                
                for (const tanggal of sortedDates.slice(0, 5)) {
                    const dayInfo = attendanceByDate[tanggal][0].dayInfo;
                    let headerClass = 'date-header';
                    
                    if (dayInfo.holiday) {
                        headerClass += ' holiday';
                    } else if (dayInfo.specialEvent) {
                        headerClass += ' special-event';
                    } else if (dayInfo.isWeekend) {
                        headerClass += ' weekend';
                    }
                    
                    attendancePreview += `<div class="${headerClass}">Tgl ${tanggal} <span class="day-name">(${dayInfo.dayName})</span>:</div>`;
                    
                    const categorizedEntries = categorizeAttendanceEntries(attendanceByDate[tanggal], employee.divisi, parseInt(tanggal), selectedMonth, selectedYear);
                    
                    for (const entry of categorizedEntries) {
                        const machineInfo = `Mesin ${entry.machine}`;
                        const debugInfo = entry.originalValue !== entry.time ? ` [dari: ${entry.originalValue}]` : '';
                        attendancePreview += `<div class="time-entry ${entry.type} machine-${entry.machine.toLowerCase()}">${entry.label}: ${entry.formattedTime} (${entry.column}) - ${machineInfo}${entry.scheduleInfo}${entry.exceptionInfo}${entry.penaltyInfo}${debugInfo}</div>`;
                        totalEmployeePenalties += entry.penaltyAmount;
                    }
                }
                
                for (const tanggal of sortedDates) {
                    const dayInfo = attendanceByDate[tanggal][0].dayInfo;
                    let headerClass = 'date-header';
                    
                    if (dayInfo.holiday) {
                        headerClass += ' holiday';
                    } else if (dayInfo.specialEvent) {
                        headerClass += ' special-event';
                    } else if (dayInfo.isWeekend) {
                        headerClass += ' weekend';
                    }
                    
                    attendanceFull += `<div class="${headerClass}">Tgl ${tanggal} <span class="day-name">(${dayInfo.dayName})</span>:</div>`;
                    
                    const categorizedEntries = categorizeAttendanceEntries(attendanceByDate[tanggal], employee.divisi, parseInt(tanggal), selectedMonth, selectedYear);
                    
                    for (const entry of categorizedEntries) {
                        const machineInfo = `Mesin ${entry.machine}`;
                        const debugInfo = entry.originalValue !== entry.time ? ` [dari: ${entry.originalValue}]` : '';
                        attendanceFull += `<div class="time-entry ${entry.type} machine-${entry.machine.toLowerCase()}">${entry.label}: ${entry.formattedTime} (${entry.column}) - ${machineInfo}${entry.scheduleInfo}${entry.exceptionInfo}${entry.penaltyInfo}${debugInfo}</div>`;
                        if (!sortedDates.slice(0, 5).includes(tanggal)) {
                            totalEmployeePenalties += entry.penaltyAmount;
                        }
                    }
                }
                
                grandTotalPenalties += totalEmployeePenalties;
                
                const detailId = `detail_${index}`;
                
                let attendanceDetail = '';
                if (sortedDates.length === 0) {
                    attendanceDetail = '<div style="color: #999;">Tidak ada data kehadiran</div>';
                } else {
                    let penaltySummary = '';
                    if (totalEmployeePenalties > 0) {
                        penaltySummary = `<div class="penalty-summary">
                            <div class="penalty-total">Total Denda: ${formatCurrency(totalEmployeePenalties)}</div>
                        </div>`;
                    }
                    
                    const divisionSchedule = getDivisionSchedule(employee.divisi);
                    const scheduleDisplay = `<div style="font-size: 11px; color: #666; margin-bottom: 4px;">Jadwal: ${divisionSchedule.start_time} - ${divisionSchedule.end_time}</div>`;
                    
                    attendanceDetail = `
                        ${scheduleDisplay}
                        <div class="attendance-summary">
                            ${daysPresent} hari hadir di ${monthNames[selectedMonth]}
                            ${sortedDates.length > 5 ? `<button class="toggle-button" onclick="toggleDetail('${detailId}', this)">Tampilkan Semua</button>` : ''}
                        </div>
                        ${sortedDates.length > 5 ? attendancePreview : attendanceFull}
                        ${sortedDates.length > 5 ? `<div id="${detailId}" class="attendance-full">${attendanceFull}</div>` : ''}
                        ${penaltySummary}
                    `;
                }
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${id}</strong></td>
                    <td>${getDivisionFullName(employee.divisi)}</td>
                    <td>${employee.nama}</td>
                    <td class="attendance-detail">${attendanceDetail}</td>
                    <td style="text-align: center; font-weight: bold;">${daysPresent}</td>
                    <td style="text-align: center; font-weight: bold; color: ${totalEmployeePenalties > 0 ? '#dc3545' : '#28a745'};">${formatCurrency(totalEmployeePenalties)}</td>
                `;
                resultsTable.appendChild(tr);
            });
            
            document.getElementById('totalPenalties').textContent = formatCurrency(grandTotalPenalties);
            
            if (employeeCount === 0) {
                resultsTable.innerHTML = `<tr><td colspan="6">Tidak ada data karyawan yang ditemukan untuk bulan ${monthNames[selectedMonth]} ${selectedYear}.</td></tr>`;
            } else {
                const summaryRow = document.createElement('tr');
                summaryRow.style.background = '#f8f9fa';
                summaryRow.style.fontWeight = 'bold';
                summaryRow.innerHTML = `
                    <td colspan="4" style="text-align: right;"><strong>Total untuk ${monthNames[selectedMonth]} ${selectedYear}:</strong></td>
                    <td style="text-align: center;"><strong>${employeeCount} Karyawan</strong></td>
                    <td style="text-align: center; color: #dc3545;"><strong>${formatCurrency(grandTotalPenalties)}</strong></td>
                `;
                resultsTable.appendChild(summaryRow);
            }
        }
        
        window.addEventListener('load', function() {
            setTimeout(() => {
                if (configLoaded) {
                    const currentMonth = new Date().getMonth() + 1;
                    if (currentMonth >= 1 && currentMonth <= 12) {
                        const currentMonthButton = document.querySelector(`[data-month="${currentMonth}"]`);
                        if (currentMonthButton) {
                            currentMonthButton.click();
                        }
                    }
                }
            }, 1000);
        });
    </script>
</body>
</html>