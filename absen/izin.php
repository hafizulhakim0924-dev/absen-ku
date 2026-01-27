<?php
session_start();

// Path ke file config dan izin
$configFile = 'config.json';
$izinFile = 'izin.json';

// Function untuk membaca config
function loadConfig($file) {
    if (!file_exists($file)) {
        return null;
    }
    $content = file_get_contents($file);
    return json_decode($content, true);
}

// Function untuk menyimpan config
function saveConfig($file, $data) {
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($file, $jsonContent);
}

// Function untuk membaca data izin
function loadIzin($file) {
    if (!file_exists($file)) {
        return ['izin_list' => []];
    }
    $content = file_get_contents($file);
    return json_decode($content, true);
}

// Function untuk menyimpan data izin
function saveIzin($file, $data) {
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($file, $jsonContent);
}

// Load config dan izin
$config = loadConfig($configFile);
$izinData = loadIzin($izinFile);

if (!$config) {
    die("Error: Cannot load config.json file");
}

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_izin':
            $newIzin = [
                'id' => uniqid(),
                'employee_id' => $_POST['employee_id'],
                'employee_name' => $_POST['employee_name'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'reason' => $_POST['reason'],
                'description' => $_POST['description'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $izinData['izin_list'][] = $newIzin;
            
            // Sort by start date
            usort($izinData['izin_list'], function($a, $b) {
                return strtotime($b['start_date']) - strtotime($a['start_date']);
            });
            
            if (saveIzin($izinFile, $izinData)) {
                $message = 'Data izin berhasil ditambahkan!';
                $messageType = 'success';
            } else {
                $message = 'Error: Gagal menyimpan data izin!';
                $messageType = 'error';
            }
            break;
            
        case 'delete_izin':
            $izinId = $_POST['izin_id'];
            $izinData['izin_list'] = array_values(array_filter($izinData['izin_list'], function($izin) use ($izinId) {
                return $izin['id'] !== $izinId;
            }));
            
            if (saveIzin($izinFile, $izinData)) {
                $message = 'Data izin berhasil dihapus!';
                $messageType = 'success';
            } else {
                $message = 'Error: Gagal menghapus data izin!';
                $messageType = 'error';
            }
            break;
    }
}

// Get list of employees from divisions
$employeeList = [];
foreach ($config['division_schedules'] as $divKey => $division) {
    if (isset($division['ids']) && is_array($division['ids'])) {
        foreach ($division['ids'] as $empId) {
            $employeeList[$empId] = [
                'id' => $empId,
                'division' => $division['name']
            ];
        }
    }
}
ksort($employeeList);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Izin Karyawan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #333;
            font-size: 2em;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
        }
        
        .nav-links {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .nav-links a {
            display: inline-block;
            padding: 10px 20px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            margin: 0 5px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .nav-links a:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 13px;
            color: #495057;
        }
        
        .table td {
            font-size: 14px;
        }
        
        .table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-expired {
            background: #f8d7da;
            color: #721c24;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card h3 {
            font-size: 14px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .stat-card .stat-number {
            font-size: 32px;
            font-weight: bold;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .filter-section input, .filter-section select {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-right: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Kelola Izin Karyawan</h1>
            <p>Tambah dan kelola data izin/cuti karyawan</p>
        </div>
        
        <div class="nav-links">
            <a href="index.html">📊 Data Absensi</a>
            <a href="settings.php">⚙️ Pengaturan</a>
            <a href="izin.php">📋 Kelola Izin</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php
        // Calculate statistics
        $totalIzin = count($izinData['izin_list']);
        $activeIzin = 0;
        $today = date('Y-m-d');
        
        foreach ($izinData['izin_list'] as $izin) {
            if ($izin['start_date'] <= $today && $izin['end_date'] >= $today) {
                $activeIzin++;
            }
        }
        ?>
        
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Izin</h3>
                <div class="stat-number"><?php echo $totalIzin; ?></div>
            </div>
            <div class="stat-card">
                <h3>Izin Aktif Hari Ini</h3>
                <div class="stat-number"><?php echo $activeIzin; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Karyawan</h3>
                <div class="stat-number"><?php echo count($employeeList); ?></div>
            </div>
        </div>
        
        <!-- Form Tambah Izin -->
        <div class="card">
            <h2>➕ Tambah Data Izin Baru</h2>
            <form method="POST" id="izinForm">
                <input type="hidden" name="action" value="add_izin">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>ID Karyawan: *</label>
                        <select name="employee_id" id="employeeSelect" required onchange="updateEmployeeName()">
                            <option value="">-- Pilih ID Karyawan --</option>
                            <?php foreach ($employeeList as $emp): ?>
                                <option value="<?php echo $emp['id']; ?>" data-division="<?php echo htmlspecialchars($emp['division']); ?>">
                                    <?php echo $emp['id']; ?> - <?php echo htmlspecialchars($emp['division']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nama Karyawan: *</label>
                        <input type="text" name="employee_name" id="employeeName" placeholder="Masukkan nama karyawan" required>
                        <small style="color: #6c757d;">Nama akan muncul otomatis dari data Excel saat memproses absensi</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tanggal Mulai: *</label>
                        <input type="date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai: *</label>
                        <input type="date" name="end_date" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Jenis Izin: *</label>
                    <select name="reason" required>
                        <option value="">-- Pilih Jenis Izin --</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Izin Pribadi">Izin Pribadi</option>
                        <option value="Dinas Luar">Dinas Luar</option>
                        <option value="Izin Keluarga">Izin Keluarga</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Keterangan:</label>
                    <textarea name="description" placeholder="Keterangan tambahan (opsional)"></textarea>
                </div>
                
                <button type="submit" class="btn">💾 Simpan Data Izin</button>
            </form>
        </div>
        
        <!-- Daftar Izin -->
        <div class="card">
            <h2>📜 Daftar Izin Karyawan</h2>
            
            <div class="filter-section">
                <input type="text" id="searchFilter" placeholder="🔍 Cari ID atau Nama..." onkeyup="filterTable()">
                <select id="statusFilter" onchange="filterTable()">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="expired">Selesai</option>
                </select>
            </div>
            
            <?php if (empty($izinData['izin_list'])): ?>
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p>Belum ada data izin yang ditambahkan</p>
                </div>
            <?php else: ?>
                <table class="table" id="izinTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Durasi</th>
                            <th>Jenis Izin</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($izinData['izin_list'] as $izin): ?>
                            <?php
                            $startDate = new DateTime($izin['start_date']);
                            $endDate = new DateTime($izin['end_date']);
                            $today = new DateTime();
                            $duration = $startDate->diff($endDate)->days + 1;
                            
                            $isActive = ($startDate <= $today && $endDate >= $today);
                            $status = $isActive ? 'active' : 'expired';
                            $statusLabel = $isActive ? 'Aktif' : 'Selesai';
                            $badgeClass = $isActive ? 'badge-active' : 'badge-expired';
                            ?>
                            <tr data-status="<?php echo $status; ?>">
                                <td><strong><?php echo htmlspecialchars($izin['employee_id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($izin['employee_name']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($izin['start_date'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($izin['end_date'])); ?></td>
                                <td><?php echo $duration; ?> hari</td>
                                <td><?php echo htmlspecialchars($izin['reason']); ?></td>
                                <td><?php echo htmlspecialchars($izin['description']); ?></td>
                                <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></span></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_izin">
                                        <input type="hidden" name="izin_id" value="<?php echo $izin['id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus data izin ini?')">🗑️ Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updateEmployeeName() {
            // This function can be enhanced to auto-fill name from a database
            // For now, user needs to input the name manually
            const select = document.getElementById('employeeSelect');
            const nameInput = document.getElementById('employeeName');
            
            if (select.value) {
                nameInput.focus();
            }
        }
        
        function filterTable() {
            const searchFilter = document.getElementById('searchFilter').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const table = document.getElementById('izinTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const id = row.cells[0].textContent.toLowerCase();
                const name = row.cells[1].textContent.toLowerCase();
                const status = row.getAttribute('data-status');
                
                const matchesSearch = id.includes(searchFilter) || name.includes(searchFilter);
                const matchesStatus = !statusFilter || status === statusFilter;
                
                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>