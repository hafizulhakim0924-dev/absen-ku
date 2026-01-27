<?php
// edit.php
session_start();

// Konfigurasi
define('UPLOAD_DIR', __DIR__ . '/upload/');
define('CONFIG_FILE', __DIR__ . '/config.json');

// Pastikan folder upload ada
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Handler untuk API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_files':
            $files = [];
            if (is_dir(UPLOAD_DIR)) {
                $fileList = scandir(UPLOAD_DIR);
                foreach ($fileList as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                        $filepath = UPLOAD_DIR . $file;
                        $files[] = [
                            'name' => $file,
                            'size' => filesize($filepath),
                            'date' => date('Y-m-d H:i:s', filemtime($filepath))
                        ];
                    }
                }
            }
            usort($files, function($a, $b) {
                return strcmp($b['date'], $a['date']);
            });
            echo json_encode(['success' => true, 'files' => $files]);
            exit;
            
        case 'load_file':
            $filename = $_POST['filename'] ?? '';
            if (empty($filename)) {
                echo json_encode(['success' => false, 'message' => 'Nama file tidak valid']);
                exit;
            }
            
            $filepath = UPLOAD_DIR . basename($filename);
            if (!file_exists($filepath)) {
                echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']);
                exit;
            }
            
            $data = file_get_contents($filepath);
            $jsonData = json_decode($data, true);
            
            if ($jsonData === null) {
                echo json_encode(['success' => false, 'message' => 'Format JSON tidak valid']);
                exit;
            }
            
            echo json_encode(['success' => true, 'data' => $jsonData]);
            exit;
            
        case 'save_file':
            $filename = $_POST['filename'] ?? '';
            $jsonData = $_POST['data'] ?? '';
            
            if (empty($filename) || empty($jsonData)) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
                exit;
            }
            
            // Validasi JSON
            $testDecode = json_decode($jsonData);
            if ($testDecode === null) {
                echo json_encode(['success' => false, 'message' => 'Format JSON tidak valid']);
                exit;
            }
            
            $filepath = UPLOAD_DIR . basename($filename);
            
            // Backup file lama
            if (file_exists($filepath)) {
                $backupPath = UPLOAD_DIR . 'backup_' . date('YmdHis') . '_' . basename($filename);
                copy($filepath, $backupPath);
            }
            
            $result = file_put_contents($filepath, $jsonData);
            
            if ($result !== false) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Data berhasil disimpan',
                    'filename' => $filename
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data']);
            }
            exit;
            
        case 'delete_file':
            $filename = $_POST['filename'] ?? '';
            if (empty($filename)) {
                echo json_encode(['success' => false, 'message' => 'Nama file tidak valid']);
                exit;
            }
            
            $filepath = UPLOAD_DIR . basename($filename);
            if (!file_exists($filepath)) {
                echo json_encode(['success' => false, 'message' => 'File tidak ditemukan']);
                exit;
            }
            
            if (unlink($filepath)) {
                echo json_encode(['success' => true, 'message' => 'File berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus file']);
            }
            exit;
    }
}

// Load config
$configExists = file_exists(CONFIG_FILE);
$configData = $configExists ? file_get_contents(CONFIG_FILE) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Absensi - Sistem Absensi Karyawan</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .header { background: #2c3e50; color: white; padding: 15px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header p { font-size: 14px; color: #ecf0f1; }
        .nav-links { margin-top: 10px; }
        .nav-links a { color: #3498db; text-decoration: none; margin-right: 15px; font-size: 14px; }
        .nav-links a:hover { text-decoration: underline; }
        .container { max-width: 1400px; margin: 20px auto; padding: 0 20px; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; overflow: hidden; }
        .card-header { background: #34495e; color: white; padding: 15px 20px; font-size: 18px; font-weight: bold; }
        .card-body { padding: 20px; }
        .file-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; }
        .file-card { border: 2px solid #e0e0e0; border-radius: 8px; padding: 15px; transition: all 0.3s; cursor: pointer; }
        .file-card:hover { border-color: #3498db; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(52,152,219,0.2); }
        .file-card.selected { border-color: #27ae60; background: #e8f8f5; }
        .file-name { font-weight: bold; font-size: 16px; color: #2c3e50; margin-bottom: 8px; word-break: break-word; }
        .file-info { font-size: 12px; color: #7f8c8d; margin-bottom: 5px; }
        .file-actions { margin-top: 10px; display: flex; gap: 8px; }
        .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; transition: all 0.3s; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-secondary { background: #95a5a6; color: white; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .editor-section { display: none; }
        .editor-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .search-bar { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; }
        .search-bar input, .search-bar select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .search-bar input { flex: 1; min-width: 200px; }
        .employee-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .employee-table th { background: #34495e; color: white; padding: 12px 8px; text-align: left; position: sticky; top: 0; z-index: 10; }
        .employee-table td { padding: 10px 8px; border-bottom: 1px solid #e0e0e0; }
        .employee-table tr:hover { background: #f8f9fa; }
        .employee-table tbody { max-height: 600px; overflow-y: auto; }
        .edit-cell { position: relative; }
        .edit-btn-small { padding: 4px 8px; font-size: 11px; margin: 2px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000; }
        .modal-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 25px; max-width: 800px; width: 90%; max-height: 85vh; overflow-y: auto; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #34495e; padding-bottom: 10px; }
        .modal-header h3 { font-size: 20px; color: #2c3e50; }
        .close-modal { font-size: 28px; cursor: pointer; color: #7f8c8d; }
        .close-modal:hover { color: #e74c3c; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .form-group textarea { resize: vertical; min-height: 100px; font-family: monospace; }
        .attendance-editor { border: 1px solid #ddd; border-radius: 5px; padding: 15px; background: #f9f9f9; }
        .attendance-day { margin-bottom: 15px; padding: 10px; background: white; border-radius: 5px; border-left: 4px solid #3498db; }
        .attendance-day-header { font-weight: bold; margin-bottom: 8px; color: #2c3e50; }
        .attendance-time { display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; align-items: center; margin-bottom: 8px; }
        .attendance-time input { padding: 6px; border: 1px solid #ddd; border-radius: 4px; }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 5px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 2000; animation: slideIn 0.3s; max-width: 400px; }
        .notification.success { background: #27ae60; color: white; }
        .notification.error { background: #e74c3c; color: white; }
        .notification.info { background: #3498db; color: white; }
        @keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .stats-bar { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; text-align: center; }
        .stat-box.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-box.red { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
        .stat-box.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-label { font-size: 12px; opacity: 0.9; margin-bottom: 5px; }
        .stat-value { font-size: 24px; font-weight: bold; }
        .loading { text-align: center; padding: 40px; color: #7f8c8d; }
        .empty-state { text-align: center; padding: 60px 20px; color: #95a5a6; }
        .empty-state-icon { font-size: 64px; margin-bottom: 20px; }
        .table-wrapper { overflow-x: auto; max-height: 600px; border: 1px solid #e0e0e0; border-radius: 5px; }
        .penalty-badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .penalty-late { background: #fff3cd; color: #856404; }
        .penalty-early { background: #f8d7da; color: #721c24; }
        .penalty-incomplete { background: #d1ecf1; color: #0c5460; }
        .day-status { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 10px; margin-left: 5px; }
        .day-weekend { background: #e9ecef; color: #495057; }
        .day-holiday { background: #f8d7da; color: #721c24; }
        .day-working { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📝 Edit Data Absensi</h1>
        <p>Kelola dan edit data absensi yang tersimpan</p>
        <div class="nav-links">
            <a href="index.php">← Kembali ke Halaman Utama</a>
        </div>
    </div>

    <div class="container">
        <!-- File List Section -->
        <div class="card" id="fileListSection">
            <div class="card-header">📁 Daftar File Absensi Tersimpan</div>
            <div class="card-body">
                <div id="loadingFiles" class="loading">Memuat daftar file...</div>
                <div id="fileGrid" class="file-grid" style="display: none;"></div>
                <div id="emptyState" class="empty-state" style="display: none;">
                    <div class="empty-state-icon">📭</div>
                    <h3>Tidak Ada File Tersimpan</h3>
                    <p>Belum ada data absensi yang tersimpan di server.</p>
                </div>
            </div>
        </div>

        <!-- Editor Section -->
        <div class="card editor-section" id="editorSection">
            <div class="card-header">✏️ Editor Data Absensi</div>
            <div class="card-body">
                <div class="editor-header">
                    <div>
                        <h3 id="editingFileName" style="color: #2c3e50; margin-bottom: 5px;">-</h3>
                        <p id="editingFileInfo" style="color: #7f8c8d; font-size: 14px;">-</p>
                    </div>
                    <div>
                        <button class="btn btn-success" onclick="saveChanges()">💾 Simpan Perubahan</button>
                        <button class="btn btn-secondary" onclick="closeEditor()">❌ Tutup</button>
                    </div>
                </div>

                <div class="stats-bar" id="statsBar"></div>

                <div class="search-bar">
                    <input type="text" id="searchEmployee" placeholder="🔍 Cari ID, Nama, atau Divisi..." oninput="filterEmployees()">
                    <select id="filterDivision" onchange="filterEmployees()">
                        <option value="">Semua Divisi</option>
                    </select>
                    <select id="filterPenalty" onchange="filterEmployees()">
                        <option value="">Semua Status</option>
                        <option value="with">Ada Denda</option>
                        <option value="without">Tanpa Denda</option>
                    </select>
                    <button class="btn btn-secondary" onclick="resetFilters()">🔄 Reset Filter</button>
                </div>

                <div class="table-wrapper">
                    <table class="employee-table">
                        <thead>
                            <tr>
                                <th width="80">ID</th>
                                <th width="200">Nama</th>
                                <th width="150">Divisi</th>
                                <th width="100">Hari Hadir</th>
                                <th width="120">Total Denda</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Employee -->
    <div id="editEmployeeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Data Karyawan</h3>
                <span class="close-modal" onclick="closeEditEmployeeModal()">&times;</span>
            </div>
            <div id="editEmployeeForm">
                <div class="form-group">
                    <label>ID Karyawan:</label>
                    <input type="text" id="editEmpId" readonly style="background: #f0f0f0;">
                </div>
                <div class="form-group">
                    <label>Nama Karyawan:</label>
                    <input type="text" id="editEmpName">
                </div>
                <div class="form-group">
                    <label>Divisi:</label>
                    <input type="text" id="editEmpDivision" readonly style="background: #f0f0f0;">
                </div>
                <div class="form-group">
                    <label>Data Kehadiran:</label>
                    <div id="attendanceEditor" class="attendance-editor"></div>
                </div>
                <div style="text-align: right; margin-top: 20px;">
                    <button class="btn btn-success" onclick="saveEmployeeChanges()">💾 Simpan</button>
                    <button class="btn btn-secondary" onclick="closeEditEmployeeModal()">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const CONFIG_DATA = <?php echo $configData ?? 'null'; ?>;
        let currentFileData = null;
        let currentFileName = null;
        let editingEmployeeId = null;
        let allEmployees = [];
        let filteredEmployees = [];

        const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        // Load files on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadFileList();
        });

        async function loadFileList() {
            try {
                const formData = new FormData();
                formData.append('action', 'get_files');
                
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();
                
                document.getElementById('loadingFiles').style.display = 'none';
                
                if (result.success && result.files.length > 0) {
                    displayFileList(result.files);
                } else {
                    document.getElementById('emptyState').style.display = 'block';
                }
            } catch (error) {
                showNotification('Error memuat file: ' + error.message, 'error');
                document.getElementById('loadingFiles').style.display = 'none';
                document.getElementById('emptyState').style.display = 'block';
            }
        }

        function displayFileList(files) {
            const fileGrid = document.getElementById('fileGrid');
            fileGrid.style.display = 'grid';
            fileGrid.innerHTML = '';
            
            files.forEach(file => {
                const sizeKB = Math.round(file.size / 1024);
                const fileCard = document.createElement('div');
                fileCard.className = 'file-card';
                fileCard.innerHTML = `
                    <div class="file-name">📄 ${file.name}</div>
                    <div class="file-info">📅 ${file.date}</div>
                    <div class="file-info">💾 ${sizeKB} KB</div>
                    <div class="file-actions">
                        <button class="btn btn-primary btn" onclick="loadFileForEdit('${file.name}')">✏️ Edit</button>
                        <button class="btn btn-danger btn" onclick="deleteFile('${file.name}')">🗑️ Hapus</button>
                    </div>
                `;
                fileGrid.appendChild(fileCard);
            });
        }

        async function loadFileForEdit(filename) {
            showNotification('Memuat file...', 'info');
            
            try {
                const formData = new FormData();
                formData.append('action', 'load_file');
                formData.append('filename', filename);
                
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) {
                    currentFileData = result.data;
                    currentFileName = filename;
                    displayEditor();
                    showNotification('File berhasil dimuat!', 'success');
                } else {
                    showNotification('Error: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('Error memuat file: ' + error.message, 'error');
            }
        }

        function displayEditor() {
            document.getElementById('fileListSection').style.display = 'none';
            document.getElementById('editorSection').style.display = 'block';
            
            // Check data structure
            console.log('Current File Data:', currentFileData);
            
            const monthName = currentFileData.monthName || 'Unknown';
            const year = currentFileData.year || new Date().getFullYear();
            
            document.getElementById('editingFileName').textContent = currentFileName;
            
            // Build employees array from originalEmployeesArray or processedData
            allEmployees = [];
            let employees = {};
            let totalEmployees = 0;
            
            // Try to get employees from originalEmployeesArray first
            if (currentFileData.originalEmployeesArray && Array.isArray(currentFileData.originalEmployeesArray)) {
                currentFileData.originalEmployeesArray.forEach(emp => {
                    // Get full data from processedData.employees
                    const fullEmpData = currentFileData.processedData?.employees?.[emp.id];
                    
                    allEmployees.push({
                        id: emp.id,
                        nama: emp.nama,
                        divisi: emp.divisi,
                        daysPresent: emp.daysPresent || 0,
                        totalPenalty: emp.totalPenalty || 0,
                        attendanceByDate: fullEmpData?.attendanceByDate || {}
                    });
                });
                totalEmployees = allEmployees.length;
            } 
            // Fallback to processedData.employees
            else if (currentFileData.processedData?.employees) {
                employees = currentFileData.processedData.employees;
                
                Object.keys(employees).sort((a, b) => parseInt(a) - parseInt(b)).forEach(id => {
                    const emp = employees[id];
                    const daysPresent = Object.keys(emp.attendanceByDate || {}).length;
                    
                    // Calculate total penalty from attendance data
                    let totalPenalty = 0;
                    Object.values(emp.attendanceByDate || {}).forEach(dayEntries => {
                        dayEntries.forEach(entry => {
                            if (entry.penaltyAmount) {
                                totalPenalty += entry.penaltyAmount;
                            }
                        });
                    });
                    
                    allEmployees.push({
                        id: id,
                        nama: emp.nama,
                        divisi: emp.divisi,
                        daysPresent: daysPresent,
                        totalPenalty: totalPenalty,
                        attendanceByDate: emp.attendanceByDate || {}
                    });
                });
                totalEmployees = Object.keys(employees).length;
            }
            
            document.getElementById('editingFileInfo').textContent = `${monthName} ${year} - ${totalEmployees} Karyawan`;
            
            filteredEmployees = [...allEmployees];
            
            // Populate division filter
            const divisions = [...new Set(allEmployees.map(e => e.divisi))].sort();
            const divFilter = document.getElementById('filterDivision');
            divFilter.innerHTML = '<option value="">Semua Divisi</option>';
            divisions.forEach(div => {
                const divisionName = getDivisionFullName(div);
                divFilter.innerHTML += `<option value="${div}">${divisionName}</option>`;
            });
            
            displayStats();
            displayEmployees();
        }

        function getDivisionFullName(divisionKey) {
            if (!CONFIG_DATA || !CONFIG_DATA.division_schedules) return divisionKey;
            const divData = CONFIG_DATA.division_schedules[divisionKey];
            return divData?.name || divisionKey;
        }

        function displayStats() {
            const statsBar = document.getElementById('statsBar');
            const totalEmployees = allEmployees.length;
            const totalDays = allEmployees.reduce((sum, e) => sum + e.daysPresent, 0);
            const totalPenalties = allEmployees.reduce((sum, e) => sum + e.totalPenalty, 0);
            
            const monthName = currentFileData.monthName || 'Unknown';
            const year = currentFileData.year || new Date().getFullYear();
            
            statsBar.innerHTML = `
                <div class="stat-box blue">
                    <div class="stat-label">Total Karyawan</div>
                    <div class="stat-value">${totalEmployees}</div>
                </div>
                <div class="stat-box green">
                    <div class="stat-label">Total Hari Hadir</div>
                    <div class="stat-value">${totalDays}</div>
                </div>
                <div class="stat-box red">
                    <div class="stat-label">Total Denda</div>
                    <div class="stat-value">Rp ${totalPenalties.toLocaleString('id-ID')}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Periode</div>
                    <div class="stat-value" style="font-size: 16px;">${monthName} ${year}</div>
                </div>
            `;
        }

        function displayEmployees() {
            const tbody = document.getElementById('employeeTableBody');
            tbody.innerHTML = '';
            
            if (filteredEmployees.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 40px; color: #999;">Tidak ada data karyawan</td></tr>';
                return;
            }
            
            filteredEmployees.forEach(emp => {
                const divisionName = getDivisionFullName(emp.divisi);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><strong>${emp.id}</strong></td>
                    <td>${emp.nama}</td>
                    <td>${divisionName}</td>
                    <td style="text-align: center;">${emp.daysPresent}</td>
                    <td style="text-align: center; color: ${emp.totalPenalty > 0 ? 'red' : 'green'}; font-weight: bold;">
                        Rp ${emp.totalPenalty.toLocaleString('id-ID')}
                    </td>
                    <td>
                        <button class="btn btn-primary edit-btn-small" onclick="openEditEmployee('${emp.id}')">✏️ Edit</button>
                        <button class="btn btn-danger edit-btn-small" onclick="deleteEmployee('${emp.id}')">🗑️ Hapus</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function filterEmployees() {
            const searchTerm = document.getElementById('searchEmployee').value.toLowerCase();
            const divisionFilter = document.getElementById('filterDivision').value;
            const penaltyFilter = document.getElementById('filterPenalty').value;
            
            filteredEmployees = allEmployees.filter(emp => {
                const matchSearch = !searchTerm || 
                    emp.id.toLowerCase().includes(searchTerm) ||
                    emp.nama.toLowerCase().includes(searchTerm) ||
                    emp.divisi.toLowerCase().includes(searchTerm);
                
                const matchDivision = !divisionFilter || emp.divisi === divisionFilter;
                
                let matchPenalty = true;
                if (penaltyFilter === 'with') matchPenalty = emp.totalPenalty > 0;
                if (penaltyFilter === 'without') matchPenalty = emp.totalPenalty === 0;
                
                return matchSearch && matchDivision && matchPenalty;
            });
            
            displayEmployees();
        }

        function resetFilters() {
            document.getElementById('searchEmployee').value = '';
            document.getElementById('filterDivision').value = '';
            document.getElementById('filterPenalty').value = '';
            filterEmployees();
        }

        function openEditEmployee(employeeId) {
            editingEmployeeId = employeeId;
            const emp = allEmployees.find(e => e.id === employeeId);
            
            if (!emp) {
                showNotification('Data karyawan tidak ditemukan!', 'error');
                return;
            }
            
            document.getElementById('editEmpId').value = emp.id;
            document.getElementById('editEmpName').value = emp.nama;
            document.getElementById('editEmpDivision').value = getDivisionFullName(emp.divisi);
            
            // Display attendance data
            const attendanceEditor = document.getElementById('attendanceEditor');
            attendanceEditor.innerHTML = '';
            
            const sortedDates = Object.keys(emp.attendanceByDate).sort((a, b) => parseInt(a) - parseInt(b));
            
            if (sortedDates.length === 0) {
                attendanceEditor.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">Tidak ada data kehadiran untuk karyawan ini</p>';
            } else {
                const monthName = currentFileData.monthName || 'Unknown';
                
                sortedDates.forEach(date => {
                    const entries = emp.attendanceByDate[date];
                    const dayDiv = document.createElement('div');
                    dayDiv.className = 'attendance-day';
                    
                    let entriesHtml = '';
                    entries.forEach((entry, idx) => {
                        const timeValue = entry.time || entry.formattedTime || '';
                        const machineValue = entry.machine || '';
                        
                        entriesHtml += `
                            <div class="attendance-time" id="entry_${date}_${idx}">
                                <input type="text" value="${timeValue}" data-date="${date}" data-index="${idx}" data-field="time" class="attendance-input" placeholder="Jam (HH:MM atau HHMM)">
                                <input type="text" value="${machineValue}" data-date="${date}" data-index="${idx}" data-field="machine" class="attendance-input" placeholder="Mesin (A/B/C/D)">
                                <button class="btn btn-danger edit-btn-small" onclick="removeAttendanceEntry('${date}', ${idx})">❌</button>
                            </div>
                        `;
                    });
                    
                    dayDiv.innerHTML = `
                        <div class="attendance-day-header">
                            📅 Tanggal ${date} ${monthName}
                            <button class="btn btn-primary edit-btn-small" onclick="addAttendanceEntry('${date}')" style="float: right;">➕ Tambah Absensi</button>
                        </div>
                        ${entriesHtml}
                    `;
                    
                    attendanceEditor.appendChild(dayDiv);
                });
            }
            
            // Add button to add new day
            const addDayBtn = document.createElement('div');
            addDayBtn.style.textAlign = 'center';
            addDayBtn.style.marginTop = '15px';
            addDayBtn.innerHTML = `
                <button class="btn btn-success" onclick="addNewDay()">➕ Tambah Hari Baru</button>
            `;
            attendanceEditor.appendChild(addDayBtn);
            
            document.getElementById('editEmployeeModal').style.display = 'block';
        }

        function addNewDay() {
            const newDate = prompt('Masukkan tanggal (1-31):');
            if (!newDate) return;
            
            const dateNum = parseInt(newDate);
            if (isNaN(dateNum) || dateNum < 1 || dateNum > 31) {
                alert('Tanggal tidak valid! Masukkan angka 1-31');
                return;
            }
            
            const emp = allEmployees.find(e => e.id === editingEmployeeId);
            if (!emp) return;
            
            if (emp.attendanceByDate[dateNum]) {
                alert(`Tanggal ${dateNum} sudah ada! Gunakan tombol "➕ Tambah Absensi" untuk menambah data pada tanggal tersebut.`);
                return;
            }
            
            emp.attendanceByDate[dateNum] = [{
                time: '08:00',
                machine: 'A',
                column: 'A'
            }];
            
            emp.daysPresent = Object.keys(emp.attendanceByDate).length;
            
            openEditEmployee(editingEmployeeId);
            showNotification(`Tanggal ${dateNum} berhasil ditambahkan!`, 'success');
        }

        function closeEditEmployeeModal() {
            document.getElementById('editEmployeeModal').style.display = 'none';
            editingEmployeeId = null;
        }

        function addAttendanceEntry(date) {
            const emp = allEmployees.find(e => e.id === editingEmployeeId);
            if (!emp) return;
            
            if (!emp.attendanceByDate[date]) {
                emp.attendanceByDate[date] = [];
            }
            
            emp.attendanceByDate[date].push({
                time: '08:00',
                machine: 'A',
                column: 'A',
                dayInfo: {}
            });
            
            openEditEmployee(editingEmployeeId);
        }

        function removeAttendanceEntry(date, index) {
            if (!confirm('Hapus data absensi ini?')) return;
            
            const emp = allEmployees.find(e => e.id === editingEmployeeId);
            if (!emp || !emp.attendanceByDate[date]) return;
            
            emp.attendanceByDate[date].splice(index, 1);
            
            if (emp.attendanceByDate[date].length === 0) {
                delete emp.attendanceByDate[date];
            }
            
            openEditEmployee(editingEmployeeId);
        }

        function saveEmployeeChanges() {
            const newName = document.getElementById('editEmpName').value.trim();
            
            if (!newName) {
                alert('Nama karyawan tidak boleh kosong!');
                return;
            }
            
            const emp = allEmployees.find(e => e.id === editingEmployeeId);
            if (!emp) return;
            
            emp.nama = newName;
            
            // Update attendance data from inputs
            const inputs = document.querySelectorAll('.attendance-input');
            inputs.forEach(input => {
                const date = input.dataset.date;
                const index = parseInt(input.dataset.index);
                const field = input.dataset.field;
                const value = input.value.trim();
                
                if (emp.attendanceByDate[date] && emp.attendanceByDate[date][index]) {
                    emp.attendanceByDate[date][index][field] = value;
                }
            });
            
            // Update in currentFileData
            currentFileData.processedData.employees[editingEmployeeId].nama = newName;
            currentFileData.processedData.employees[editingEmployeeId].attendanceByDate = emp.attendanceByDate;
            
            // Update originalEmployeesArray if exists
            if (currentFileData.originalEmployeesArray) {
                const origEmp = currentFileData.originalEmployeesArray.find(e => e.id === editingEmployeeId);
                if (origEmp) {
                    origEmp.nama = newName;
                }
            }
            
            closeEditEmployeeModal();
            displayEmployees();
            showNotification('Data karyawan berhasil diupdate!', 'success');
        }

        function deleteEmployee(employeeId) {
            if (!confirm(`Hapus data karyawan ID ${employeeId}?\n\nPeringatan: Data yang dihapus tidak dapat dikembalikan!`)) {
                return;
            }
            
            // Remove from currentFileData
            delete currentFileData.processedData.employees[employeeId];
            
            // Remove from originalEmployeesArray if exists
            if (currentFileData.originalEmployeesArray) {
                currentFileData.originalEmployeesArray = currentFileData.originalEmployeesArray.filter(e => e.id !== employeeId);
            }
            
            // Remove from allEmployees
            allEmployees = allEmployees.filter(e => e.id !== employeeId);
            
            // Update stats
            if (currentFileData.processedData.stats) {
                currentFileData.processedData.stats.totalEmployees = Object.keys(currentFileData.processedData.employees).length;
            }
            
            filterEmployees();
            displayStats();
            showNotification('Karyawan berhasil dihapus!', 'success');
        }

        async function saveChanges() {
            if (!confirm('Simpan semua perubahan ke file?\n\nFile lama akan dibackup otomatis.')) {
                return;
            }
            
            showNotification('Menyimpan perubahan...', 'info');
            
            try {
                const formData = new FormData();
                formData.append('action', 'save_file');
                formData.append('filename', currentFileName);
                formData.append('data', JSON.stringify(currentFileData, null, 2));
                
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) {
                    showNotification('✅ Perubahan berhasil disimpan!', 'success');
                } else {
                    showNotification('❌ Error: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('❌ Error menyimpan: ' + error.message, 'error');
            }
        }

        function closeEditor() {
            if (confirm('Tutup editor? Perubahan yang belum disimpan akan hilang.')) {
                currentFileData = null;
                currentFileName = null;
                allEmployees = [];
                filteredEmployees = [];
                
                document.getElementById('editorSection').style.display = 'none';
                document.getElementById('fileListSection').style.display = 'block';
                
                loadFileList();
            }
        }

        async function deleteFile(filename) {
            if (!confirm(`Hapus file ${filename}?\n\nPeringatan: File yang dihapus tidak dapat dikembalikan!`)) {
                return;
            }
            
            showNotification('Menghapus file...', 'info');
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete_file');
                formData.append('filename', filename);
                
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) {
                    showNotification('✅ File berhasil dihapus!', 'success');
                    loadFileList();
                } else {
                    showNotification('❌ Error: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('❌ Error menghapus: ' + error.message, 'error');
            }
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `<strong>${message}</strong>`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transition = 'opacity 0.5s';
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editEmployeeModal');
            if (event.target === modal) {
                closeEditEmployeeModal();
            }
        }
    </script>
</body>
</html>