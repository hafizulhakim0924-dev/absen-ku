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
        :root { --primary: #1976D2; --primary-hover: #1565C0; --bg: #f0f4f8; --card: #fff; --border: #e2e8f0; --text: #1e293b; --text-muted: #64748b; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); min-height: 100vh; padding: 12px; font-size: 13px; color: var(--text); }
        .container { max-width: 1000px; margin: 0 auto; }
        .header {
            background: var(--card);
            padding: 12px 16px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            text-align: center;
            margin-bottom: 12px;
            border: 1px solid var(--border);
        }
        .header h1 { color: var(--text); font-size: 16px; margin-bottom: 4px; }
        .header p { color: var(--text-muted); font-size: 12px; }
        .nav-links { text-align: center; margin-bottom: 12px; }
        .nav-links a {
            display: inline-block;
            padding: 6px 14px;
            background: var(--card);
            color: var(--primary);
            text-decoration: none;
            border-radius: 4px;
            margin: 0 4px;
            font-weight: 600;
            font-size: 12px;
            border: 1px solid var(--border);
        }
        .nav-links a:hover { background: #eff6ff; border-color: var(--primary); }
        .alert { padding: 8px 12px; margin-bottom: 12px; border-radius: 4px; font-weight: 500; font-size: 12px; border: 1px solid var(--border); }
        .alert.success { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .alert.error { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .card {
            background: var(--card);
            border-radius: 6px;
            padding: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            margin-bottom: 12px;
            border: 1px solid var(--border);
        }
        .card h2 { color: var(--text); margin-bottom: 12px; font-size: 14px; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; margin-bottom: 4px; font-weight: 600; color: var(--text); font-size: 11px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 12px;
            transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        .form-group textarea { resize: vertical; min-height: 60px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn {
            background: var(--primary);
            color: white;
            padding: 6px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn:hover { background: var(--primary-hover); }
        .btn-danger { background: #dc2626; padding: 4px 10px; font-size: 11px; }
        .btn-danger:hover { background: #b91c1c; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        .table th, .table td { padding: 8px 10px; text-align: left; border-bottom: 1px solid var(--border); }
        .table th { background: #f1f5f9; font-weight: 600; font-size: 11px; color: var(--text-muted); }
        .table tr:hover { background: #f8fafc; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 3px; font-size: 10px; font-weight: 600; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-expired { background: #fee2e2; color: #991b1b; }
        .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-bottom: 12px; }
        .stat-card {
            background: var(--primary);
            color: white;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid var(--primary-hover);
        }
        .stat-card h3 { font-size: 11px; margin-bottom: 6px; opacity: 0.9; }
        .stat-card .stat-number { font-size: 22px; font-weight: bold; }
        .filter-section { background: #f8fafc; padding: 10px; border-radius: 4px; margin-bottom: 12px; border: 1px solid var(--border); }
        .filter-section input, .filter-section select { padding: 5px 10px; border: 1px solid var(--border); border-radius: 4px; margin-right: 8px; font-size: 12px; }
        .empty-state { text-align: center; padding: 24px; color: var(--text-muted); font-size: 12px; }
        .empty-state svg { width: 48px; height: 48px; margin-bottom: 10px; opacity: 0.5; }
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