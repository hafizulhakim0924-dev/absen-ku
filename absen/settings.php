<?php
session_start();

// Konfigurasi - menggunakan konstanta yang sama dengan index.php
define('CONFIG_FILE', __DIR__ . '/config.json');
define('SPECIAL_SCHEDULES_FILE', __DIR__ . '/datajadwalkhusus.json');

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
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($file, $jsonContent);
}

// Load existing config
$config = loadConfig(CONFIG_FILE);
if (!$config) {
    die("Error: Cannot load config.json file");
}

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_schedule':
            // Update default schedule
            if (isset($_POST['default_start_time'])) {
                $config['default_work_schedule']['start_time'] = $_POST['default_start_time'];
                $config['default_work_schedule']['end_time'] = $_POST['default_end_time'];
            }
            
            // Update division schedules
            foreach ($config['division_schedules'] as $key => &$division) {
                if (isset($_POST["division_start_{$key}"])) {
                    $division['start_time'] = $_POST["division_start_{$key}"];
                    $division['end_time'] = $_POST["division_end_{$key}"];
                    
                    // Update Friday schedule if provided
                    if (isset($_POST["friday_start_{$key}"]) && isset($_POST["friday_end_{$key}"])) {
                        if (!isset($division['friday_schedule'])) {
                            $division['friday_schedule'] = [];
                        }
                        $division['friday_schedule']['start_time'] = $_POST["friday_start_{$key}"];
                        $division['friday_schedule']['end_time'] = $_POST["friday_end_{$key}"];
                    }
                }
            }
            $message = 'Jadwal kerja berhasil diperbarui!';
            $messageType = 'success';
            break;
            
        case 'add_holiday':
            $year = date('Y', strtotime($_POST['holiday_date']));
            if (!isset($config['holidays'][$year])) {
                $config['holidays'][$year] = [];
            }
            
            $config['holidays'][$year][] = [
                'date' => $_POST['holiday_date'],
                'name' => $_POST['holiday_name'],
                'type' => $_POST['holiday_type'],
                'description' => $_POST['holiday_description'] ?? ''
            ];
            
            usort($config['holidays'][$year], function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });
            
            $message = 'Hari libur berhasil ditambahkan!';
            $messageType = 'success';
            break;
            
        case 'delete_holiday':
            $year = $_POST['year'];
            $index = $_POST['index'];
            if (isset($config['holidays'][$year][$index])) {
                unset($config['holidays'][$year][$index]);
                $config['holidays'][$year] = array_values($config['holidays'][$year]);
                $message = 'Hari libur berhasil dihapus!';
                $messageType = 'success';
            }
            break;
            
        case 'update_penalties':
            // Update late policy
            $config['late_policy']['grace_period_minutes'] = intval($_POST['late_grace_period']);
            foreach ($config['late_policy']['levels'] as $i => &$level) {
                $level['penalty_amount'] = intval($_POST["late_penalty_$i"]);
            }
            
            // Update early departure policy
            $config['early_departure_policy']['grace_period_minutes'] = intval($_POST['early_grace_period']);
            foreach ($config['early_departure_policy']['levels'] as $i => &$level) {
                $level['penalty_amount'] = intval($_POST["early_penalty_$i"]);
            }
            
            // Update absence policy
            $config['absence_policy']['full_day_penalty'] = intval($_POST['full_day_penalty']);
            $config['absence_policy']['half_day_penalty'] = intval($_POST['half_day_penalty']);
            
            $message = 'Kebijakan denda berhasil diperbarui!';
            $messageType = 'success';
            break;
            
        case 'add_special_schedule':
            // Load special schedules from separate file
            $specialSchedulesFile = SPECIAL_SCHEDULES_FILE;
            $specialSchedules = [];
            if (file_exists($specialSchedulesFile)) {
                $content = file_get_contents($specialSchedulesFile);
                $specialSchedules = json_decode($content, true) ?: [];
            }
            
            // Validate dates
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            $division = $_POST['division'] ?? '';
            $startTime = $_POST['special_start_time'] ?? '';
            $endTime = $_POST['special_end_time'] ?? '';
            
            if (empty($startDate) || empty($endDate) || empty($division) || empty($startTime) || empty($endTime)) {
                $message = 'Semua field harus diisi!';
                $messageType = 'error';
                break;
            }
            
            if (strtotime($startDate) > strtotime($endDate)) {
                $message = 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir!';
                $messageType = 'error';
                break;
            }
            
            // Check if division exists
            if (!isset($config['division_schedules'][$division])) {
                $message = 'Divisi tidak ditemukan!';
                $messageType = 'error';
                break;
            }
            
            // Add new schedule
            $specialSchedules[] = [
                'id' => uniqid('sk_'),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'division' => $division,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'description' => $_POST['special_description'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Sort by start_date
            usort($specialSchedules, function($a, $b) {
                $dateA = isset($a['start_date']) ? strtotime($a['start_date']) : 0;
                $dateB = isset($b['start_date']) ? strtotime($b['start_date']) : 0;
                return $dateA - $dateB;
            });
            
            // Save to separate file
            $jsonContent = json_encode($specialSchedules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (file_put_contents($specialSchedulesFile, $jsonContent) !== false) {
                $message = 'Jadwal khusus berhasil ditambahkan!';
                $messageType = 'success';
            } else {
                $message = 'Gagal menyimpan jadwal khusus!';
                $messageType = 'error';
            }
            break;
            
        case 'delete_special_schedule':
            // Load special schedules from separate file
            $specialSchedulesFile = SPECIAL_SCHEDULES_FILE;
            $specialSchedules = [];
            if (file_exists($specialSchedulesFile)) {
                $content = file_get_contents($specialSchedulesFile);
                $specialSchedules = json_decode($content, true) ?: [];
            }
            
            $index = intval($_POST['index']);
            if (isset($specialSchedules[$index])) {
                unset($specialSchedules[$index]);
                $specialSchedules = array_values($specialSchedules);
                
                // Save to separate file
                $jsonContent = json_encode($specialSchedules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if (file_put_contents($specialSchedulesFile, $jsonContent) !== false) {
                    $message = 'Jadwal khusus berhasil dihapus!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus jadwal khusus!';
                    $messageType = 'error';
                }
            } else {
                $message = 'Jadwal khusus tidak ditemukan!';
                $messageType = 'error';
            }
            break;
    }
    
    // Save config after any update (except for special schedules which use separate file)
    if ($message && $action !== 'add_special_schedule' && $action !== 'delete_special_schedule') {
        if (!saveConfig(CONFIG_FILE, $config)) {
            $message = 'Error: Gagal menyimpan konfigurasi!';
            $messageType = 'error';
        }
    }
}

// Collect all holidays into one array for display
$allHolidays = [];
if (!empty($config['holidays'])) {
    foreach ($config['holidays'] as $year => $holidays) {
        if (is_array($holidays)) {
            foreach ($holidays as $index => $holiday) {
                if (isset($holiday['date'])) {
                    $allHolidays[] = [
                        'date' => $holiday['date'],
                        'name' => $holiday['name'] ?? '',
                        'type' => $holiday['type'] ?? 'national',
                        'description' => $holiday['description'] ?? '',
                        'year' => $year,
                        'index' => $index
                    ];
                }
            }
        }
    }
}
// Sort by date
usort($allHolidays, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

// Load special schedules from separate file
$specialSchedules = [];
if (file_exists(SPECIAL_SCHEDULES_FILE)) {
    $content = file_get_contents(SPECIAL_SCHEDULES_FILE);
    $specialSchedules = json_decode($content, true) ?: [];
} else {
    // Create empty file if not exists
    file_put_contents(SPECIAL_SCHEDULES_FILE, '[]');
}

// Sort special schedules by start_date
if (is_array($specialSchedules) && !empty($specialSchedules)) {
    usort($specialSchedules, function($a, $b) {
        $dateA = isset($a['start_date']) ? strtotime($a['start_date']) : 0;
        $dateB = isset($b['start_date']) ? strtotime($b['start_date']) : 0;
        return $dateA - $dateB;
    });
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem Absensi</title>
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
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            animation: slideDown 0.3s;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        .tabs-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            flex-wrap: wrap;
        }
        
        .tab {
            flex: 1;
            min-width: 150px;
            padding: 15px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: #666;
            text-align: center;
            transition: all 0.3s;
        }
        
        .tab:hover {
            background: #e9ecef;
        }
        
        .tab.active {
            background: white;
            color: #667eea;
            border-bottom: 3px solid #667eea;
        }
        
        .tab-content {
            padding: 30px;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        }
        
        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.2em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Schedule Table */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .schedule-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }
        
        .schedule-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }
        
        .schedule-table tr:hover {
            background: #f8f9fa;
        }
        
        .schedule-table tr:last-child td {
            border-bottom: none;
        }
        
        .schedule-table input[type="time"] {
            width: 100%;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .schedule-table input[type="time"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .division-name {
            font-weight: 600;
            color: #333;
        }
        
        .friday-schedule {
            background: #fff3cd;
            padding: 8px;
            border-radius: 4px;
            margin-top: 5px;
        }
        
        .friday-schedule label {
            font-size: 12px;
            color: #856404;
            font-weight: 600;
        }
        
        .friday-schedule .form-row {
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 5px;
        }
        
        /* Holidays Table */
        .holidays-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .holidays-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }
        
        .holidays-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .holidays-table tr:hover {
            background: #f8f9fa;
        }
        
        .holidays-table tr:last-child td {
            border-bottom: none;
        }
        
        .holiday-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .holiday-type.national {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .holiday-type.religious {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .table-empty {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .penalty-group {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .penalty-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: white;
            border-radius: 4px;
        }
        
        .penalty-info {
            flex: 1;
        }
        
        .penalty-input {
            width: 150px;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
        }
        
        .penalty-input:focus {
            outline: none;
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Pengaturan Sistem Absensi</h1>
            <p>Kelola konfigurasi jadwal kerja, hari libur, dan kebijakan absensi</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?>" id="alertMessage">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="tabs-container">
            <div class="tabs">
                <button class="tab active" onclick="showTab('schedules')">📅 Jadwal Kerja</button>
                <button class="tab" onclick="showTab('holidays')">🎉 Hari Libur</button>
                <button class="tab" onclick="showTab('penalties')">💰 Kebijakan Denda</button>
            </div>
            
            <div class="tab-content">
                <!-- Tab Jadwal Kerja -->
                <div id="schedules" class="tab-pane active">
                    <form method="POST" id="scheduleForm">
                        <input type="hidden" name="action" value="update_schedule">
                        
                        <div class="card">
                            <h3>⏰ Jadwal Default</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Jam Masuk:</label>
                                    <input type="time" name="default_start_time" value="<?php echo $config['default_work_schedule']['start_time'] ?? '08:00'; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Jam Pulang:</label>
                                    <input type="time" name="default_end_time" value="<?php echo $config['default_work_schedule']['end_time'] ?? '16:00'; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <h3>🏢 Jadwal per Divisi</h3>
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th style="width: 200px;">Divisi</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Pulang</th>
                                        <th>Jadwal Khusus Jumat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($config['division_schedules'] as $key => $division): ?>
                                        <tr>
                                            <td class="division-name">
                                                <?php echo htmlspecialchars($division['name']); ?><br>
                                                <small style="color: #666; font-weight: normal;">(<?php echo $key; ?>)</small>
                                            </td>
                                            <td>
                                                <input type="time" 
                                                       name="division_start_<?php echo $key; ?>" 
                                                       value="<?php echo $division['start_time'] ?? '08:00'; ?>" 
                                                       required>
                                            </td>
                                            <td>
                                                <input type="time" 
                                                       name="division_end_<?php echo $key; ?>" 
                                                       value="<?php echo $division['end_time'] ?? '16:00'; ?>" 
                                                       required>
                                            </td>
                                            <td>
                                                <div class="friday-schedule">
                                                    <label>Jumat:</label>
                                                    <div class="form-row">
                                                        <div class="form-group" style="margin-bottom: 0;">
                                                            <input type="time" 
                                                                   name="friday_start_<?php echo $key; ?>" 
                                                                   value="<?php echo $division['friday_schedule']['start_time'] ?? $division['start_time'] ?? '08:00'; ?>" 
                                                                   placeholder="Masuk">
                                                        </div>
                                                        <div class="form-group" style="margin-bottom: 0;">
                                                            <input type="time" 
                                                                   name="friday_end_<?php echo $key; ?>" 
                                                                   value="<?php echo $division['friday_schedule']['end_time'] ?? $division['end_time'] ?? '16:00'; ?>" 
                                                                   placeholder="Pulang">
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <button type="submit" class="btn">💾 Simpan Semua Jadwal</button>
                    </form>
                    
                    <!-- Jadwal Khusus untuk Tanggal Tertentu -->
                    <div class="card" style="margin-top: 30px;">
                        <h3>📆 Jadwal Khusus untuk Tanggal Tertentu</h3>
                        <p style="color: #666; margin-bottom: 15px; font-size: 14px;">
                            Atur jadwal khusus untuk periode tertentu (misal: Januari tgl 5-28, jam masuk X sampai Y untuk divisi tertentu)
                        </p>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="add_special_schedule">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tanggal Mulai:</label>
                                    <input type="date" name="start_date" required>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Akhir:</label>
                                    <input type="date" name="end_date" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Divisi:</label>
                                    <select name="division" required>
                                        <option value="">Pilih Divisi</option>
                                        <?php foreach ($config['division_schedules'] as $key => $division): ?>
                                            <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($division['name']); ?> (<?php echo $key; ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Keterangan (Opsional):</label>
                                    <input type="text" name="special_description" placeholder="Contoh: Masa Ujian, Libur Semester, dll">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Jam Masuk:</label>
                                    <input type="time" name="special_start_time" required>
                                </div>
                                <div class="form-group">
                                    <label>Jam Pulang:</label>
                                    <input type="time" name="special_end_time" required>
                                </div>
                            </div>
                            <button type="submit" class="btn">➕ Tambah Jadwal Khusus</button>
                        </form>
                    </div>
                    
                    <!-- Daftar Jadwal Khusus -->
                    <div class="card" style="margin-top: 20px;">
                        <h3>📋 Daftar Jadwal Khusus</h3>
                        <?php if (!empty($specialSchedules)): ?>
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th style="width: 150px;">Periode</th>
                                        <th style="width: 120px;">Divisi</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Pulang</th>
                                        <th>Keterangan</th>
                                        <th style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($specialSchedules as $index => $schedule): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo date('d/m/Y', strtotime($schedule['start_date'])); ?></strong><br>
                                                <small style="color: #666;">s/d <?php echo date('d/m/Y', strtotime($schedule['end_date'])); ?></small>
                                            </td>
                                            <td>
                                                <?php 
                                                $divName = $config['division_schedules'][$schedule['division']]['name'] ?? $schedule['division'];
                                                echo htmlspecialchars($divName);
                                                ?>
                                            </td>
                                            <td><strong><?php echo $schedule['start_time']; ?></strong></td>
                                            <td><strong><?php echo $schedule['end_time']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($schedule['description'] ?: '-'); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus jadwal khusus ini?');">
                                                    <input type="hidden" name="action" value="delete_special_schedule">
                                                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                                                    <button type="submit" class="btn btn-danger btn-small">🗑️ Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="table-empty">
                                <p>📅 Belum ada jadwal khusus yang ditambahkan.</p>
                                <p style="font-size: 12px; color: #999; margin-top: 10px;">Gunakan form di atas untuk menambahkan jadwal khusus baru.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Tab Hari Libur -->
                <div id="holidays" class="tab-pane">
                    <!-- Form Tambah Hari Libur -->
                    <div class="card">
                        <h3>➕ Tambah Hari Libur Baru</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_holiday">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tanggal:</label>
                                    <input type="date" name="holiday_date" required>
                                </div>
                                <div class="form-group">
                                    <label>Nama Hari Libur:</label>
                                    <input type="text" name="holiday_name" placeholder="Contoh: Hari Raya Idul Fitri" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tipe:</label>
                                    <select name="holiday_type" required>
                                        <option value="national">🇮🇩 Nasional</option>
                                        <option value="religious">🕌 Keagamaan</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Deskripsi (Opsional):</label>
                                    <input type="text" name="holiday_description" placeholder="Keterangan tambahan">
                                </div>
                            </div>
                            <button type="submit" class="btn">➕ Tambah Hari Libur</button>
                        </form>
                    </div>
                    
                    <!-- Daftar Hari Libur - Satu Tabel -->
                    <div class="card">
                        <h3>📋 Daftar Semua Hari Libur</h3>
                        <?php if (!empty($allHolidays)): ?>
                            <table class="holidays-table">
                                <thead>
                                    <tr>
                                        <th style="width: 120px;">Tanggal</th>
                                        <th style="width: 80px;">Hari</th>
                                        <th>Nama Hari Libur</th>
                                        <th style="width: 120px;">Tipe</th>
                                        <th>Deskripsi</th>
                                        <th style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allHolidays as $holiday): ?>
                                        <?php
                                        $dateObj = new DateTime($holiday['date']);
                                        $dayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$dateObj->format('w')];
                                        ?>
                                        <tr>
                                            <td><strong><?php echo date('d/m/Y', strtotime($holiday['date'])); ?></strong></td>
                                            <td><?php echo $dayName; ?></td>
                                            <td><?php echo htmlspecialchars($holiday['name']); ?></td>
                                            <td>
                                                <span class="holiday-type <?php echo $holiday['type']; ?>">
                                                    <?php echo $holiday['type'] === 'national' ? '🇮🇩 Nasional' : '🕌 Keagamaan'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($holiday['description'] ?: '-'); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus hari libur ini?');">
                                                    <input type="hidden" name="action" value="delete_holiday">
                                                    <input type="hidden" name="year" value="<?php echo $holiday['year']; ?>">
                                                    <input type="hidden" name="index" value="<?php echo $holiday['index']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-small">🗑️ Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="table-empty">
                                <p>📅 Belum ada hari libur yang ditambahkan.</p>
                                <p style="font-size: 12px; color: #999; margin-top: 10px;">Gunakan form di atas untuk menambahkan hari libur baru.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Tab Kebijakan Denda -->
                <div id="penalties" class="tab-pane">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_penalties">
                        
                        <!-- Kebijakan Keterlambatan -->
                        <div class="card">
                            <h3>⏰ Kebijakan Keterlambatan</h3>
                            <div class="form-group">
                                <label>Masa Tenggang (menit):</label>
                                <input type="number" name="late_grace_period" value="<?php echo $config['late_policy']['grace_period_minutes']; ?>" required>
                                <small style="color: #666;">Karyawan tidak dikenakan denda jika terlambat dalam masa tenggang ini.</small>
                            </div>
                            
                            <div class="penalty-group">
                                <strong style="display: block; margin-bottom: 15px;">Tingkat Denda Keterlambatan:</strong>
                                <?php foreach ($config['late_policy']['levels'] as $index => $level): ?>
                                    <div class="penalty-row">
                                        <div class="penalty-info">
                                            <strong><?php echo $level['name']; ?></strong><br>
                                            <small style="color: #666;"><?php echo $level['min_minutes']; ?>-<?php echo $level['max_minutes']; ?> menit</small>
                                        </div>
                                        <div>
                                            <input type="number" 
                                                   name="late_penalty_<?php echo $index; ?>" 
                                                   value="<?php echo $level['penalty_amount']; ?>" 
                                                   class="penalty-input" 
                                                   required>
                                            <span style="margin-left: 5px; color: #666;">Rp</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Kebijakan Pulang Awal -->
                        <div class="card">
                            <h3>🏃 Kebijakan Pulang Awal</h3>
                            <div class="form-group">
                                <label>Masa Tenggang (menit):</label>
                                <input type="number" name="early_grace_period" value="<?php echo $config['early_departure_policy']['grace_period_minutes']; ?>" required>
                                <small style="color: #666;">Karyawan tidak dikenakan denda jika pulang lebih awal dalam masa tenggang ini.</small>
                            </div>
                            
                            <div class="penalty-group">
                                <strong style="display: block; margin-bottom: 15px;">Tingkat Denda Pulang Awal:</strong>
                                <?php foreach ($config['early_departure_policy']['levels'] as $index => $level): ?>
                                    <div class="penalty-row">
                                        <div class="penalty-info">
                                            <strong><?php echo $level['name']; ?></strong><br>
                                            <small style="color: #666;"><?php echo $level['min_minutes']; ?>-<?php echo $level['max_minutes']; ?> menit sebelum jam pulang</small>
                                        </div>
                                        <div>
                                            <input type="number" 
                                                   name="early_penalty_<?php echo $index; ?>" 
                                                   value="<?php echo $level['penalty_amount']; ?>" 
                                                   class="penalty-input" 
                                                   required>
                                            <span style="margin-left: 5px; color: #666;">Rp</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Kebijakan Absen -->
                        <div class="card">
                            <h3>❌ Kebijakan Absen</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Denda Absen Sehari Penuh (Rp):</label>
                                    <input type="number" name="full_day_penalty" value="<?php echo $config['absence_policy']['full_day_penalty']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Denda Absen Setengah Hari (Rp):</label>
                                    <input type="number" name="half_day_penalty" value="<?php echo $config['absence_policy']['half_day_penalty']; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn">💾 Simpan Semua Kebijakan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab panes
            const panes = document.querySelectorAll('.tab-pane');
            panes.forEach(pane => pane.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab pane
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
        
        // Auto-hide alerts after 5 seconds
        const alertMessage = document.getElementById('alertMessage');
        if (alertMessage) {
            setTimeout(function() {
                alertMessage.style.opacity = '0';
                alertMessage.style.transition = 'opacity 0.5s';
                setTimeout(function() {
                    alertMessage.remove();
                }, 500);
            }, 5000);
        }
    </script>
</body>
</html>
