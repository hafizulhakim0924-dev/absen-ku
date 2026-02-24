<?php
// index.php
session_start();
date_default_timezone_set('Asia/Jakarta');
// Konfigurasi
define('UPLOAD_DIR', __DIR__ . '/upload/');
define('CONFIG_FILE', __DIR__ . '/config.json');
define('SPECIAL_SCHEDULES_FILE', __DIR__ . '/datajadwalkhusus.json');

// Buat folder upload jika belum ada
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Handler untuk API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save_data':
            $jsonData = $_POST['data'] ?? '';
            $filename = $_POST['filename'] ?? 'absensi_' . date('Y-m-d_His') . '.json';
            
            if (empty($jsonData)) {
                echo json_encode(['success' => false, 'message' => 'Data kosong']);
                exit;
            }
            
            $filepath = UPLOAD_DIR . $filename;
            $result = file_put_contents($filepath, $jsonData);
            
            if ($result !== false) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Data berhasil disimpan',
                    'filename' => $filename,
                    'filepath' => $filepath
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data']);
            }
            exit;
            
        case 'get_saved_files':
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
            // Sort by date descending
            usort($files, function($a, $b) {
                return strcmp($b['date'], $a['date']);
            });
            echo json_encode(['success' => true, 'files' => $files]);
            exit;
            
        case 'load_data':
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
            echo json_encode(['success' => true, 'data' => $data]);
            exit;
            
        case 'delete_data':
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
            
        // Settings handlers - handle via AJAX
        case 'settings_action':
            $settingsAction = $_POST['settings_action'] ?? '';
            $configFile = CONFIG_FILE;
            
            // Load config
            if (!file_exists($configFile)) {
                echo json_encode(['success' => false, 'message' => 'Config file not found']);
                exit;
            }
            
            $config = json_decode(file_get_contents($configFile), true);
            if (!$config) {
                echo json_encode(['success' => false, 'message' => 'Invalid config file']);
                exit;
            }
            
            $result = ['success' => false, 'message' => 'Unknown action'];
            
            switch ($settingsAction) {
                case 'update_schedule':
                    if (isset($_POST['default_start_time'])) {
                        $config['default_work_schedule']['start_time'] = $_POST['default_start_time'];
                        $config['default_work_schedule']['end_time'] = $_POST['default_end_time'];
                    }
                    foreach ($config['division_schedules'] as $key => &$division) {
                        if (isset($_POST["division_start_{$key}"])) {
                            $division['start_time'] = $_POST["division_start_{$key}"];
                            $division['end_time'] = $_POST["division_end_{$key}"];
                            if (isset($_POST["friday_start_{$key}"]) && isset($_POST["friday_end_{$key}"])) {
                                if (!isset($division['friday_schedule'])) {
                                    $division['friday_schedule'] = [];
                                }
                                $division['friday_schedule']['start_time'] = $_POST["friday_start_{$key}"];
                                $division['friday_schedule']['end_time'] = $_POST["friday_end_{$key}"];
                            }
                        }
                    }
                    $result = ['success' => true, 'message' => 'Jadwal kerja berhasil diperbarui!'];
                    break;
                    
                case 'add_holiday':
                    $year = date('Y', strtotime($_POST['holiday_date']));
                    if (!isset($config['holidays'][$year])) {
                        $config['holidays'][$year] = [];
                    }
                    $divisions = isset($_POST['holiday_divisions']) && is_array($_POST['holiday_divisions'])
                        ? array_values($_POST['holiday_divisions'])
                        : [];
                    if (in_array('all', $divisions) || empty($divisions)) {
                        $divisions = [];
                    }
                    $config['holidays'][$year][] = [
                        'date' => $_POST['holiday_date'],
                        'name' => $_POST['holiday_name'],
                        'type' => $_POST['holiday_type'],
                        'description' => $_POST['holiday_description'] ?? '',
                        'divisions' => $divisions
                    ];
                    usort($config['holidays'][$year], function($a, $b) {
                        return strtotime($a['date']) - strtotime($b['date']);
                    });
                    $result = ['success' => true, 'message' => 'Hari libur berhasil ditambahkan!'];
                    break;
                    
                case 'delete_holiday':
                    $year = $_POST['year'];
                    $index = intval($_POST['index']);
                    if (isset($config['holidays'][$year][$index])) {
                        unset($config['holidays'][$year][$index]);
                        $config['holidays'][$year] = array_values($config['holidays'][$year]);
                        $result = ['success' => true, 'message' => 'Hari libur berhasil dihapus!'];
                    }
                    break;
                    
                case 'update_penalties':
                    $config['late_policy']['grace_period_minutes'] = intval($_POST['late_grace_period']);
                    foreach ($config['late_policy']['levels'] as $i => &$level) {
                        $level['penalty_amount'] = intval($_POST["late_penalty_$i"]);
                    }
                    $config['early_departure_policy']['grace_period_minutes'] = intval($_POST['early_grace_period']);
                    foreach ($config['early_departure_policy']['levels'] as $i => &$level) {
                        $level['penalty_amount'] = intval($_POST["early_penalty_$i"]);
                    }
                    $config['absence_policy']['full_day_penalty'] = intval($_POST['full_day_penalty']);
                    $config['absence_policy']['half_day_penalty'] = intval($_POST['half_day_penalty']);
                    $result = ['success' => true, 'message' => 'Kebijakan denda berhasil diperbarui!'];
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
                        $result = ['success' => false, 'message' => 'Semua field harus diisi!'];
                        break;
                    }
                    
                    if (strtotime($startDate) > strtotime($endDate)) {
                        $result = ['success' => false, 'message' => 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir!'];
                        break;
                    }
                    
                    // Check if division exists
                    if (!isset($config['division_schedules'][$division])) {
                        $result = ['success' => false, 'message' => 'Divisi tidak ditemukan!'];
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
                    
                    // Save to file
                    $jsonContent = json_encode($specialSchedules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    if (file_put_contents($specialSchedulesFile, $jsonContent) !== false) {
                        $result = ['success' => true, 'message' => 'Jadwal khusus berhasil ditambahkan!', 'schedules' => $specialSchedules];
                    } else {
                        $result = ['success' => false, 'message' => 'Gagal menyimpan jadwal khusus!'];
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
                        
                        // Save to file
                        $jsonContent = json_encode($specialSchedules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        if (file_put_contents($specialSchedulesFile, $jsonContent) !== false) {
                            $result = ['success' => true, 'message' => 'Jadwal khusus berhasil dihapus!'];
                        } else {
                            $result = ['success' => false, 'message' => 'Gagal menghapus jadwal khusus!'];
                        }
                    } else {
                        $result = ['success' => false, 'message' => 'Jadwal khusus tidak ditemukan!'];
                    }
                    break;
            }
            
            // Save config (except for special schedules which use separate file)
            if ($result['success'] && $settingsAction !== 'add_special_schedule' && $settingsAction !== 'delete_special_schedule') {
                $jsonContent = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if (file_put_contents($configFile, $jsonContent) === false) {
                    $result = ['success' => false, 'message' => 'Gagal menyimpan konfigurasi!'];
                }
            }
            
            echo json_encode($result);
            exit;
    }
}

// Load config for display
$configExists = file_exists(CONFIG_FILE);
$configData = $configExists ? file_get_contents(CONFIG_FILE) : null;

// Load config for settings
$config = null;
if ($configExists) {
    $config = json_decode($configData, true);
}

// Helper functions for settings
function loadConfig($file) {
    if (!file_exists($file)) {
        return null;
    }
    $content = file_get_contents($file);
    return json_decode($content, true);
}

function saveConfig($file, $data) {
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($file, $jsonContent);
}

// Prepare settings data
$settingsMessage = '';
$settingsMessageType = '';
$allHolidays = [];
$specialSchedules = [];

if ($config) {
    // Collect all holidays
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
                            'divisions' => $holiday['divisions'] ?? [],
                            'year' => $year,
                            'index' => $index
                        ];
                    }
                }
            }
        }
    }
    usort($allHolidays, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    
    // Get special schedules from separate file
    $specialSchedules = [];
    if (file_exists(SPECIAL_SCHEDULES_FILE)) {
        $content = file_get_contents(SPECIAL_SCHEDULES_FILE);
        $specialSchedules = json_decode($content, true) ?: [];
    } else {
        // Create empty file if not exists
        file_put_contents(SPECIAL_SCHEDULES_FILE, '[]');
    }
    
    if (is_array($specialSchedules) && !empty($specialSchedules)) {
        usort($specialSchedules, function($a, $b) {
            $dateA = isset($a['start_date']) ? strtotime($a['start_date']) : 0;
            $dateB = isset($b['start_date']) ? strtotime($b['start_date']) : 0;
            return $dateA - $dateB;
        });
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Karyawan - Multi Mesin Terbaru</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; padding: 10px; }
        
        /* SPA Navigation Tabs */
        .spa-nav {
            background: white;
            border-bottom: 2px solid #e0e0e0;
            padding: 0;
            margin: 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .spa-nav-tabs {
            display: flex;
            flex-wrap: wrap;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .spa-nav-tab {
            padding: 15px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            font-weight: 500;
            color: #666;
            background: white;
            border: none;
            font-size: 14px;
        }
        .spa-nav-tab:hover {
            background: #f8f9fa;
            color: #333;
        }
        .spa-nav-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
            background: #f8f9fa;
        }
        .spa-content {
            display: none;
            animation: fadeIn 0.3s;
            width: 100%;
            min-height: calc(100vh - 60px);
        }
        .spa-content.active {
            display: block !important;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .spa-iframe {
            width: 100%;
            border: none;
            min-height: calc(100vh - 60px);
            background: white;
            display: block;
        }
        h1 { margin: 10px 0; font-size: 20px; }
        h3 { margin: 15px 0 10px 0; font-size: 16px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .month-buttons { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 8px; 
            margin: 15px 0; 
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .month-buttons button { 
            padding: 10px 18px; 
            border: 2px solid #e0e0e0; 
            background: white; 
            cursor: pointer; 
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #333;
            transition: all 0.3s;
        }
        .month-buttons button:hover {
            border-color: #667eea;
            background: #f5f7ff;
            color: #667eea;
        }
        .month-buttons button.active { 
            background: #667eea; 
            color: white; 
            border-color: #667eea;
        }
        .file-section { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 15px; 
            margin: 20px 0; 
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .file-input-group { 
            padding: 15px; 
            border: 2px solid #e0e0e0; 
            border-radius: 6px;
            background: #fafafa;
            transition: all 0.3s;
        }
        .file-input-group:hover {
            border-color: #667eea;
            background: #f5f7ff;
        }
        .file-input-group label { 
            font-weight: bold; 
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }
        .file-input-group button { 
            margin: 5px 0; 
            padding: 8px 16px; 
            border: 1px solid #667eea; 
            background: #667eea; 
            color: white;
            cursor: pointer; 
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.3s;
        }
        .file-input-group button:hover:not(:disabled) {
            background: #5568d3;
            border-color: #5568d3;
        }
        .file-input-group button:disabled { 
            opacity: 0.5; 
            cursor: not-allowed;
            background: #999;
            border-color: #999;
        }
        .file-input-group div {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
            padding: 5px;
            background: white;
            border-radius: 4px;
            min-height: 20px;
        }
        .process-button { 
            background: #667eea; 
            color: white; 
            padding: 12px 30px; 
            border: none; 
            cursor: pointer; 
            margin: 20px 0; 
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
        }
        .process-button:hover:not(:disabled) {
            background: #5568d3;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
            transform: translateY(-1px);
        }
        .process-button:disabled { 
            background: #999; 
            cursor: not-allowed;
            box-shadow: none;
        }
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
        .export-section button:hover { background: #f0f0f0; }
        .save-button { background: #27ae60 !important; color: white !important; font-weight: bold; }
        .save-button:hover { background: #229954 !important; }
        .load-button { background: #3498db !important; color: white !important; }
        .load-button:hover { background: #2980b9 !important; }
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
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; border: 1px solid #999; border-radius: 5px; }
        .saved-files-list { max-height: 400px; overflow-y: auto; }
        .file-item { padding: 10px; border: 1px solid #ddd; margin: 5px 0; display: flex; justify-content: space-between; align-items: center; }
        .file-item:hover { background: #f9f9f9; }
        .file-actions button { margin-left: 5px; padding: 5px 10px; font-size: 11px; }
        .bulk-permit-section { background: #e8f5e9; padding: 15px; border: 2px solid #4caf50; border-radius: 5px; margin: 15px 0; }
        .bulk-permit-section h4 { margin: 0 0 10px 0; color: #2e7d32; }
        .bulk-controls { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin: 10px 0; }
        .bulk-controls label { font-weight: bold; font-size: 12px; display: block; margin-bottom: 3px; }
        .bulk-controls select, .bulk-controls input { width: 100%; padding: 6px; border: 1px solid #999; }
        .apply-bulk-btn { background: #4caf50 !important; color: white !important; padding: 10px 20px; border: none; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .apply-bulk-btn:hover { background: #45a049 !important; }
    </style>
</head>
<body>
    <!-- SPA Navigation -->
    <nav class="spa-nav">
        <ul class="spa-nav-tabs">
            <li><button class="spa-nav-tab active" onclick="showSPATab('home', this)" data-tab="home">🏠 Halaman Utama</button></li>
            <li><button class="spa-nav-tab" onclick="showSPATab('edit', this)" data-tab="edit">✏️ Edit Data</button></li>
            <li><button class="spa-nav-tab" onclick="showSPATab('settings', this)" data-tab="settings">⚙️ Pengaturan</button></li>
            <li><button class="spa-nav-tab" onclick="showSPATab('izin', this)" data-tab="izin">📝 Izin & Cuti</button></li>
            <li><button class="spa-nav-tab" onclick="showSPATab('work_schedule', this)" data-tab="work_schedule">📅 Jadwal Kerja</button></li>
        </ul>
    </nav>
    
    <!-- SPA Content Areas -->
    <div id="spa-content-home" class="spa-content active">
        <div class="container">
            <h1>Sistem Absensi Karyawan - terbaru(Server Version)</h1>
        
        <div id="configStatus" class="alert alert-warning">
            <h3>Memuat konfigurasi sistem...</h3>
            <p>Mencari file config.json...</p>
        </div>
        
        <div id="mainContent" style="display:none;">
            <div>
                <h3>Pilih Bulan dan Tahun untuk Data Absensi</h3>
                <div style="margin-bottom: 15px;">
                    <label for="yearSelect" style="font-weight: bold; margin-right: 10px;">Pilih Tahun:</label>
                    <select id="yearSelect" style="padding: 8px 12px; font-size: 14px; border: 1px solid #ccc; border-radius: 4px; min-width: 120px;">
                        <?php 
                        $currentYear = date('Y');
                        // Rentang tahun dari 2020 sampai 2030 untuk mencegah miss data
                        for ($y = 2020; $y <= 2030; $y++) {
                            $selected = ($y == $currentYear) ? ' selected' : '';
                            echo "<option value=\"$y\"$selected>$y</option>";
                        }
                        ?>
                    </select>
                </div>
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
                    <strong>Bulan Terpilih: <span id="monthName">-</span> <span id="monthYear"><?php echo date('Y'); ?></span></strong>
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
                    
                    <!-- SECTION IZIN MASSAL (BARU) -->
                    <div class="bulk-permit-section">
                        <h4>⚡ Izin Massal (Cepat)</h4>
                        <p style="font-size: 11px; color: #666; margin: 5px 0;">Terapkan izin untuk banyak karyawan sekaligus</p>
                        
                        <div class="bulk-controls">
                            <div>
                                <label>Target Karyawan:</label>
                                <select id="bulkTarget">
                                    <option value="all">🌐 Semua Karyawan</option>
                                    <option value="division">🏢 Unit/Divisi Tertentu</option>
                                </select>
                            </div>
                            
                            <div id="bulkDivisionSelector" style="display:none;">
                                <label>Pilih Unit/Divisi:</label>
                                <select id="bulkDivision">
                                    <option value="">-- Pilih Divisi --</option>
                                </select>
                            </div>
                            
                            <div>
                                <label>Tanggal Izin:</label>
                                <input type="number" id="bulkDate" min="1" max="31" placeholder="1-31">
                            </div>
                            
                            <div>
                                <label>Jenis Izin:</label>
                                <select id="bulkPermitType">
                                    <option value="full">Izin Penuh (Tidak Perlu Absen)</option>
                                    <option value="arrival">Izin Kedatangan Saja</option>
                                    <option value="departure">Izin Kepulangan Saja</option>
                                </select>
                            </div>
                        </div>
                        
                        <div style="margin: 10px 0;">
                            <label><strong>Keterangan:</strong></label>
                            <input type="text" id="bulkReason" placeholder="Contoh: Hari Raya, Acara Sekolah, dll" style="width: 100%; padding: 6px; border: 1px solid #999; margin-top: 5px;">
                        </div>
                        
                        <button onclick="applyBulkPermit()" class="apply-bulk-btn">✓ Terapkan Izin Massal</button>
                    </div>
                    
                    <hr style="margin: 20px 0;">
                    
                    <!-- SECTION IZIN INDIVIDUAL (EXISTING) -->
                    <h4>➕ Tambah Izin Individual</h4>
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

            <div id="savedFilesModal" class="modal">
                <div class="modal-content">
                    <h3>Data Tersimpan di Server</h3>
                    <div id="savedFilesList" class="saved-files-list">
                        <p style="text-align: center; color: #999;">Memuat daftar file...</p>
                    </div>
                    <div style="margin-top: 15px; text-align: center;">
                        <button onclick="closeSavedFilesModal()" style="padding: 8px 15px; border: 1px solid #999; background: white; cursor: pointer;">Tutup</button>
                    </div>
                </div>
            </div>

            <div id="exportSection" class="export-section" style="display:none;">
                <h3>Simpan & Export Data</h3>
                <button onclick="saveToServer()" class="save-button">💾 Simpan ke Server</button>
                <button onclick="openSavedFilesModal()" class="load-button">📂 Muat dari Server</button>
                <button onclick="exportToExcel('detailed')">📊 Export Detail Kehadiran</button>
                <button onclick="exportToExcel('summary')">📋 Export Ringkasan Denda</button>
                <button onclick="exportToExcel('filtered')" id="exportFilteredBtn" style="display:none;">🔍 Export Data Terfilter</button>
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
        // Config dari PHP
        const CONFIG_DATA = <?php echo $configData ?? 'null'; ?>;
        // Special Schedules dari PHP
        const SPECIAL_SCHEDULES_DATA = <?php echo json_encode($specialSchedules ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        
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
        let selectedYear = new Date().getFullYear();
        
        const monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        function loadConfig() {
            const statusDiv = document.getElementById('configStatus');
            
            if (CONFIG_DATA) {
                config = CONFIG_DATA;
                configLoaded = true;
                statusDiv.className = 'alert alert-success';
                statusDiv.innerHTML = '<h3>Konfigurasi Berhasil Dimuat</h3><p>Sistem siap digunakan - Jadwal kerja telah dikonfigurasi</p>';
                enableApplication();
            } else {
                statusDiv.className = 'alert alert-error';
                statusDiv.innerHTML = '<h3>Error Memuat Konfigurasi</h3><p>File config.json tidak ditemukan di server.</p><p>Pastikan file config.json berada di direktori root.</p>';
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize year selector to current year
            selectedYear = new Date().getFullYear();
            document.getElementById('yearSelect').value = selectedYear;
            loadConfig();
            setupEventListeners();
        });

        function enableApplication() {
            document.getElementById('mainContent').style.display = 'block';
        }

        function setupEventListeners() {
            // Year selection listener
            document.getElementById('yearSelect').addEventListener('change', function() {
                if (!checkConfigRequired()) return;
                selectedYear = parseInt(this.value);
                updateMonthYearDisplay();
                // Reset month selection when year changes
                if (selectedMonth) {
                    document.querySelectorAll('button[data-month]').forEach(b => b.classList.remove('active'));
                    selectedMonth = null;
                    document.getElementById('selectedMonthInfo').style.display = 'none';
                    document.getElementById('warningMessage').style.display = 'block';
                    document.getElementById('fileSection').style.display = 'none';
                    // Clear file selections
                    ['A', 'B', 'C', 'D'].forEach(machine => {
                        document.getElementById(`fileInput${machine}`).value = '';
                        document.getElementById(`status${machine}`).textContent = 'Belum ada file';
                        document.getElementById(`button${machine}`).style.background = '';
                        filesData[machine] = null;
                    });
                    updateProcessButton();
                }
            });
            
            document.querySelectorAll('button[data-month]').forEach(button => {
                button.addEventListener('click', function() {
                    if (!checkConfigRequired()) return;
                    document.querySelectorAll('button[data-month]').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    selectedMonth = parseInt(this.getAttribute('data-month'));
                    updateMonthYearDisplay();
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

        function updateMonthYearDisplay() {
            if (selectedMonth) {
                document.getElementById('monthName').textContent = monthNames[selectedMonth];
                document.getElementById('monthYear').textContent = selectedYear;
            }
        }
        
        function checkConfigRequired() {
            if (!configLoaded || !config) {
                alert('Konfigurasi sistem belum dimuat. Hubungi administrator.');
                return false;
            }
            return true;
        }

        // ========== FUNGSI SIMPAN & MUAT KE SERVER ==========
        
        async function saveToServer() {
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

            const filename = `Absensi_${monthNames[selectedMonth]}_${selectedYear}_${Date.now()}.json`;
            
            showNotification('⏳ Menyimpan data ke server...', 'info');

            const formData = new FormData();
            formData.append('action', 'save_data');
            formData.append('data', JSON.stringify(dataToSave, null, 2));
            formData.append('filename', filename);

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification(`✅ Data berhasil disimpan: ${result.filename}`, 'success');
                } else {
                    showNotification('❌ Gagal menyimpan: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('❌ Error: ' + error.message, 'error');
            }
        }

        async function openSavedFilesModal() {
            document.getElementById('savedFilesModal').style.display = 'block';
            await loadSavedFilesList();
        }

        function closeSavedFilesModal() {
            document.getElementById('savedFilesModal').style.display = 'none';
        }

        async function loadSavedFilesList() {
            const listDiv = document.getElementById('savedFilesList');
            listDiv.innerHTML = '<p style="text-align: center; color: #999;">Memuat daftar file...</p>';

            const formData = new FormData();
            formData.append('action', 'get_saved_files');

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success && result.files.length > 0) {
                    let html = '';
                    result.files.forEach(file => {
                        const sizeKB = Math.round(file.size / 1024);
                        html += `
                            <div class="file-item">
                                <div style="flex: 1;">
                                    <strong>${file.name}</strong><br>
                                    <small style="color: #666;">${file.date} | ${sizeKB} KB</small>
                                </div>
                                <div class="file-actions">
                                    <button onclick="loadFromServer('${file.name}')" style="background: #3498db; color: white; border: none; cursor: pointer;">Muat</button>
                                    <button onclick="deleteFromServer('${file.name}')" style="background: #e74c3c; color: white; border: none; cursor: pointer;">Hapus</button>
                                </div>
                            </div>
                        `;
                    });
                    listDiv.innerHTML = html;
                } else {
                    listDiv.innerHTML = '<p style="text-align: center; color: #999;">Tidak ada file tersimpan</p>';
                }
            } catch (error) {
                listDiv.innerHTML = '<p style="text-align: center; color: red;">Error memuat daftar file</p>';
            }
        }

        async function loadFromServer(filename) {
            showNotification('⏳ Memuat data dari server...', 'info');

            const formData = new FormData();
            formData.append('action', 'load_data');
            formData.append('filename', filename);

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const loadedData = JSON.parse(result.data);
                    
                    if (!loadedData.version || !loadedData.processedData) {
                        throw new Error('Format JSON tidak valid');
                    }

                    selectedMonth = loadedData.month;
                    selectedYear = loadedData.year || new Date().getFullYear();
                    processedData = loadedData.processedData;
                    permitData = loadedData.permitData || {};
                    originalEmployeesArray = loadedData.originalEmployeesArray || [];

                    document.querySelectorAll('button[data-month]').forEach(b => b.classList.remove('active'));
                    const monthButton = document.querySelector(`button[data-month="${selectedMonth}"]`);
                    if (monthButton) monthButton.classList.add('active');
                    
                    document.getElementById('yearSelect').value = selectedYear;
                    document.getElementById('monthName').textContent = monthNames[selectedMonth];
                    document.getElementById('monthYear').textContent = selectedYear;
                    document.getElementById('selectedMonthInfo').style.display = 'block';

                    setupFilterOptions(processedData);
                    displayCombinedResults(processedData);
                    
                    document.getElementById('summary').style.display = 'block';
                    document.getElementById('searchSection').style.display = 'block';
                    document.getElementById('exportSection').style.display = 'block';
                    document.getElementById('results').style.display = 'block';

                    closeSavedFilesModal();
                    showNotification(`✅ Data ${loadedData.monthName} ${loadedData.year} berhasil dimuat!`, 'success');
                } else {
                    showNotification('❌ Gagal memuat: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('❌ Error: ' + error.message, 'error');
            }
        }

        async function deleteFromServer(filename) {
            if (!confirm(`Hapus file ${filename}?`)) return;

            const formData = new FormData();
            formData.append('action', 'delete_data');
            formData.append('filename', filename);

            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('✅ File berhasil dihapus', 'success');
                    await loadSavedFilesList();
                } else {
                    showNotification('❌ Gagal menghapus: ' + result.message, 'error');
                }
            } catch (error) {
                showNotification('❌ Error: ' + error.message, 'error');
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

        // ========== FUNGSI SISTEM LAINNYA (SAMA SEPERTI VERSI ORIGINAL) ==========

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

        function isHoliday(date, month, year, divisionKey) {
            const dateStr = `${year}-${month.toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}`;
            const holidays = getAllHolidays();
            for (const holiday of holidays) {
                if (holiday.date !== dateStr) continue;
                // Divisi terdampak: kosong/undefined = semua divisi; otherwise hanya jika divisi ada di list
                const divs = holiday.divisions;
                if (!divs || divs.length === 0 || divs.includes('all')) return holiday;
                if (divisionKey && divs.includes(divisionKey)) return holiday;
                if (!divisionKey) return holiday; // backward: tidak pass divisi = tetap libur (semua)
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

        function isWorkingDay(date, month, year, divisionKey) {
            const dateObj = new Date(year, month - 1, date);
            const isWeekend = dateObj.getDay() === 0 || dateObj.getDay() === 6;
            const holiday = isHoliday(date, month, year, divisionKey);
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
            // First, check for special schedules (date range based) - PRIORITY HIGHEST
            if (date && month && year && SPECIAL_SCHEDULES_DATA && Array.isArray(SPECIAL_SCHEDULES_DATA)) {
                const dateStr = `${year}-${month.toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}`;
                const currentDate = new Date(year, month - 1, date);
                
                for (const specialSchedule of SPECIAL_SCHEDULES_DATA) {
                    if (specialSchedule.division === divisionKey) {
                        const startDate = new Date(specialSchedule.start_date);
                        const endDate = new Date(specialSchedule.end_date);
                        startDate.setHours(0, 0, 0, 0);
                        endDate.setHours(23, 59, 59, 999);
                        currentDate.setHours(0, 0, 0, 0);
                        
                        if (currentDate >= startDate && currentDate <= endDate) {
                            return {
                                start_time: specialSchedule.start_time,
                                end_time: specialSchedule.end_time,
                                isSpecialSchedule: true,
                                description: specialSchedule.description || ''
                            };
                        }
                    }
                }
            }
            
            // Then check for special events (single date)
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
            
            // Default: use division schedule or default schedule
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
                    baseSchedule = config?.default_work_schedule || { start_time: "08:00", end_time: "16:00" };
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
                                const dayInfo = getDayInfo(tanggal, selectedMonth, selectedYear, employeeData[id].divisi);
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
            
            // Populate bulk division options
            populateBulkDivisionOptions();
            
            // Setup bulk target selector
            document.getElementById('bulkTarget').addEventListener('change', function() {
                const divSelector = document.getElementById('bulkDivisionSelector');
                if (this.value === 'division') {
                    divSelector.style.display = 'block';
                } else {
                    divSelector.style.display = 'none';
                }
            });
            
            updatePermitList();
        }
function populateBulkDivisionOptions() {
            const bulkDivisionSelect = document.getElementById('bulkDivision');
            bulkDivisionSelect.innerHTML = '<option value="">-- Pilih Divisi --</option>';
            
            if (config && config.division_schedules) {
                Object.keys(config.division_schedules).forEach(divKey => {
                    const divName = config.division_schedules[divKey].name || divKey;
                    const option = document.createElement('option');
                    option.value = divKey;
                    option.textContent = divName;
                    bulkDivisionSelect.appendChild(option);
                });
            }
        }

        function applyBulkPermit() {
            const target = document.getElementById('bulkTarget').value;
            const bulkDate = parseInt(document.getElementById('bulkDate').value);
            const bulkType = document.getElementById('bulkPermitType').value;
            const bulkReason = document.getElementById('bulkReason').value.trim() || 'Izin Massal';
            const bulkDivision = document.getElementById('bulkDivision').value;
            
            if (!bulkDate || bulkDate < 1 || bulkDate > 31) {
                alert('❌ Masukkan tanggal yang valid (1-31)!');
                return;
            }
            
            if (target === 'division' && !bulkDivision) {
                alert('❌ Pilih divisi terlebih dahulu!');
                return;
            }
            
            if (!processedData || !processedData.employees) {
                alert('❌ Tidak ada data karyawan. Proses file terlebih dahulu!');
                return;
            }
            
            let affectedEmployees = [];
            
            if (target === 'all') {
                affectedEmployees = Object.keys(processedData.employees);
            } else if (target === 'division') {
                affectedEmployees = Object.keys(processedData.employees).filter(id => {
                    return processedData.employees[id].divisi === bulkDivision;
                });
            }
            
            if (affectedEmployees.length === 0) {
                alert('❌ Tidak ada karyawan yang sesuai dengan filter!');
                return;
            }
            
            const confirmMsg = `Terapkan ${getPermitTypeName(bulkType)} untuk ${affectedEmployees.length} karyawan pada tanggal ${bulkDate} ${monthNames[selectedMonth]}?\n\nKeterangan: ${bulkReason}`;
            
            if (!confirm(confirmMsg)) {
                return;
            }
            
            let addedCount = 0;
            affectedEmployees.forEach(employeeId => {
                const permitKey = `${employeeId}-${bulkDate}`;
                permitData[permitKey] = {
                    id: employeeId,
                    date: bulkDate,
                    type: bulkType,
                    reason: bulkReason,
                    month: selectedMonth,
                    year: selectedYear,
                    isBulk: true
                };
                addedCount++;
            });
            
            showNotification(`✅ Berhasil menambahkan ${getPermitTypeName(bulkType)} untuk ${addedCount} karyawan!`, 'success');
            
            document.getElementById('bulkDate').value = '';
            document.getElementById('bulkReason').value = '';
            updatePermitList();
            
            if (processedData) {
                processAllFiles();
            }
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
            showNotification(`${typeName} ditambahkan untuk ID ${employeeId} pada tanggal ${date} ${monthNames[selectedMonth]}`, 'success');
            
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
                const bulkBadge = permit.isBulk ? ' <span style="background:#ff9800;color:white;padding:2px 5px;border-radius:3px;font-size:9px;">MASSAL</span>' : '';
                html += `
                    <div style="padding: 8px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                        <div style="flex: 1;">
                            <strong>ID ${permit.id}</strong> - ${employeeName}
                           <span class="permit-badge ${badgeClass}">${typeName}</span>${bulkBadge}<br>
                            <span style="font-size: 11px; color: #666;">Tanggal: ${permit.date} ${monthNames[selectedMonth]} - ${permit.reason}</span>
                        </div>
                        <button onclick="removePermit('${key}')" style="padding: 4px 10px; background: white; border: 1px solid #999; cursor: pointer; font-size: 11px;">Hapus</button>
                    </div>
                `;
            });
            html += '</div>';
            permitList.innerHTML = html;
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
                <td colspan="4" style="text-align: right; padding: 12px;"><strong>Total untuk ${monthNames[selectedMonth]} ${selectedYear}:</strong></td>
                <td style="text-align: center; padding: 12px;"><strong>${totalEmployees} Karyawan</strong></td>
                <td style="text-align: center; padding: 12px;"><strong>${formatCurrency(totalPenalties)}</strong></td>
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
                            const dayInfo = getDayInfo(date, selectedMonth, selectedYear, getDivisionById(id));
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
        
        function getDayInfo(tanggal, month, year, divisionKey) {
            const date = new Date(year, month - 1, tanggal);
            const dayName = dayNames[date.getDay()];
            const isWeekend = date.getDay() === 0 || date.getDay() === 6;
            const holiday = isHoliday(tanggal, month, year, divisionKey);
            const specialEvent = getSpecialEvent(tanggal, month, year);
            return {
                dayName: dayName,
                isWeekend: isWeekend,
                fullDate: date,
                holiday: holiday,
                specialEvent: specialEvent,
                isWorkingDay: isWorkingDay(tanggal, month, year, divisionKey)
            };
        }
        
function parseTimeString(timeStr) {
    if (!timeStr) return { hour: 0, minute: 0 };
    const cleanStr = timeStr.toString().trim();
    if (!cleanStr) return { hour: 0, minute: 0 };
    
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
        
        function parseWorkStartTime(divisionKey = null, date = null, month = null, year = null) {
    const schedule = getDivisionSchedule(divisionKey, date, month, year);
    if (!schedule || !schedule.start_time) return { hour: 8, minute: 0 };
    const [hour, minute] = schedule.start_time.split(':');
    return { hour: parseInt(hour), minute: parseInt(minute) };
}

function parseWorkEndTime(divisionKey = null, date = null, month = null, year = null) {
    const schedule = getDivisionSchedule(divisionKey, date, month, year);
    if (!schedule || !schedule.end_time) return { hour: 16, minute: 0 };
    const [hour, minute] = schedule.end_time.split(':');
    return { hour: parseInt(hour), minute: parseInt(minute) };
}
        
function splitCombinedTimes(timeStr) {
    if (!timeStr) return [];
    const cleanStr = timeStr.toString().trim().replace(/\s+/g, '');
    if (!cleanStr) return [];
    
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
                
                // Check if there's a special schedule that overrides default schedule
                let hasSpecialSchedule = schedule.isSpecialSchedule || false;
                let specialScheduleNote = '';
                
                // Calculate what the penalty would be with default schedule (if special schedule is active)
                if (hasSpecialSchedule && !permit) {
                    // Get default schedule for comparison
                    let defaultSchedule;
                    if (config && config.division_schedules && config.division_schedules[divisionKey]) {
                        const divisionData = config.division_schedules[divisionKey];
                        if (divisionData && divisionData.start_time && divisionData.end_time) {
                            defaultSchedule = {start_time: divisionData.start_time, end_time: divisionData.end_time};
                            if (dayOfWeek === 5 && divisionData.friday_schedule) {
                                defaultSchedule = {
                                    start_time: divisionData.friday_schedule.start_time,
                                    end_time: divisionData.friday_schedule.end_time
                                };
                            }
                        } else {
                            defaultSchedule = config?.default_work_schedule || { start_time: "08:00", end_time: "16:00" };
                        }
                    } else {
                        defaultSchedule = config?.default_work_schedule || { start_time: "08:00", end_time: "16:00" };
                    }
                    
                    // Calculate penalty based on default schedule
                    if (type === 'arrival') {
                        const arrival = parseTimeString(entry.time);
                        const arrivalMinutes = arrival.hour * 60 + arrival.minute;
                        const defaultStart = parseTimeString(defaultSchedule.start_time);
                        const defaultStartMinutes = defaultStart.hour * 60 + defaultStart.minute;
                        if (arrivalMinutes > defaultStartMinutes) {
                            const lateMinutes = arrivalMinutes - defaultStartMinutes;
                            const latePolicy = getLatePolicy(lateMinutes);
                            if (latePolicy) {
                                specialScheduleNote = ` <span style="color: #e74c3c; font-size: 10px; font-style: italic;">(Sebenarnya terlambat ${lateMinutes} menit dari jadwal default, tapi tidak kena denda karena ada jadwal khusus)</span>`;
                            }
                        }
                    } else {
                        const departure = parseTimeString(entry.time);
                        const departureMinutes = departure.hour * 60 + departure.minute;
                        const defaultEnd = parseTimeString(defaultSchedule.end_time);
                        const defaultEndMinutes = defaultEnd.hour * 60 + defaultEnd.minute;
                        if (departureMinutes < defaultEndMinutes) {
                            const earlyMinutes = defaultEndMinutes - departureMinutes;
                            const earlyPolicy = getEarlyDeparturePolicy(earlyMinutes);
                            if (earlyPolicy) {
                                specialScheduleNote = ` <span style="color: #e74c3c; font-size: 10px; font-style: italic;">(Sebenarnya pulang ${earlyMinutes} menit lebih awal dari jadwal default, tapi tidak kena denda karena ada jadwal khusus)</span>`;
                            }
                        }
                    }
                    
                    if (schedule.description) {
                        scheduleInfo = `(Jadwal Khusus: ${schedule.start_time}-${schedule.end_time} - ${schedule.description})`;
                    } else {
                        scheduleInfo = `(Jadwal Khusus: ${schedule.start_time}-${schedule.end_time})`;
                    }
                }

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
                    if (schedule.isFriday && !hasSpecialSchedule) {
                        exceptionInfo = `JUMAT`;
                        scheduleInfo = `(Jumat: ${schedule.start_time}-${schedule.end_time})`;
                    }
                    
                    if (permit) {
                        permitInfo = ` - ${getPermitTypeName(permit.type)}: ${permit.reason}`;
                    } else {
                        if (type === 'arrival') {
                            const lateMinutes = calculateLateMinutes(entry.time, divisionKey, date, month, year);
                            const latePolicy = getLatePolicy(lateMinutes);
                            if (latePolicy) {
                                penaltyAmount = latePolicy.penalty_amount;
                                penaltyInfo = ` - ${latePolicy.name} (+${lateMinutes} menit) ${formatCurrency(penaltyAmount)}`;
                            }
                        } else {
                            const earlyMinutes = calculateEarlyDepartureMinutes(entry.time, divisionKey, date, month, year);
                            const earlyPolicy = getEarlyDeparturePolicy(earlyMinutes);
                            if (earlyPolicy) {
                                penaltyAmount = earlyPolicy.penalty_amount;
                                penaltyInfo = ` - ${earlyPolicy.name} (-${earlyMinutes} menit) ${formatCurrency(penaltyAmount)}`;
                            }
                        }
                    }
                }

                return {
                    ...entry,
                    type: type,
                    label: type === 'arrival' ? 'Kedatangan' : 'Kepulangan',
                    penaltyInfo: penaltyInfo + (specialScheduleNote || ''),
                    penaltyAmount: penaltyAmount,
                    permitInfo: permitInfo,
                    formattedTime: formatTime(entry.time),
                    scheduleInfo: scheduleInfo,
                    exceptionInfo: exceptionInfo
                };
            });
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

        function checkAttendanceCompleteness(attendanceByDate, divisionKey, month, year, employeeId) {
            const daysInMonth = new Date(year, month, 0).getDate();
            const incompleteWorkingDays = [];
            const absentWorkingDays = [];
            
            for (let date = 1; date <= daysInMonth; date++) {
                const dayInfo = getDayInfo(date, month, year, divisionKey);
                if (dayInfo.isWorkingDay) {
                    const fullDayPermit = getPermitForEmployee(employeeId, date, 'full');
                    if (fullDayPermit && fullDayPermit.type === 'full') {
                        continue;
                    }
                    const dayAttendance = attendanceByDate[date] || [];
                    if (dayAttendance.length === 0) {
                        absentWorkingDays.push(date);
                    } else {
                        const categorizedEntries = categorizeAttendanceEntries(dayAttendance, divisionKey, date, month, year, employeeId);
                        const arrivalPermit = getPermitForEmployee(employeeId, date, 'arrival');
                        const departurePermit = getPermitForEmployee(employeeId, date, 'departure');
                        const hasArrival = categorizedEntries.some(entry => entry.type === 'arrival');
                        const hasDeparture = categorizedEntries.some(entry => entry.type === 'departure');
                        const needsArrival = !arrivalPermit;
                        const needsDeparture = !departurePermit;
                        if ((needsArrival && !hasArrival) || (needsDeparture && !hasDeparture)) {
                            incompleteWorkingDays.push(date);
                        }
                    }
                }
            }
            
// Denda maksimal per hari adalah 15rb (sama dengan incomplete attendance penalty)
const maxDailyPenalty = getIncompleteAttendancePenalty();

return {
    incompleteWorkingDays: incompleteWorkingDays,
    absentWorkingDays: absentWorkingDays,
    incompletePenalty: incompleteWorkingDays.length * maxDailyPenalty,
    absentPenalty: absentWorkingDays.length * maxDailyPenalty // Tidak hadir penuh = 15rb (bukan 100rb)
};
        }

        function toggleDetail(detailId, button) {
            const detailDiv = document.getElementById(detailId);
            if (detailDiv.style.display === 'none' || detailDiv.style.display === '') {
                detailDiv.style.display = 'block';
                button.textContent = 'Sembunyikan';
            } else {
                detailDiv.style.display = 'none';
                button.textContent = 'Lihat Semua';
            }
        }

function exportToExcel(type) {
    if (!processedData) {
        alert('Tidak ada data untuk diekspor. Proses file terlebih dahulu.');
        return;
    }
    let dataToExport;
    let filename;
    if (type === 'filtered') {
        dataToExport = filteredEmployeesArray.reduce((acc, emp) => {
            acc[emp.id] = {
                nama: emp.nama,
                divisi: emp.divisi,
                attendanceByDate: processedData.employees[emp.id].attendanceByDate
            };
            return acc;
        }, {});
        filename = `Absensi_${monthNames[selectedMonth]}_${selectedYear}_Filtered.xlsx`;
    } else {
        dataToExport = processedData.employees;
        filename = `Absensi_${monthNames[selectedMonth]}_${selectedYear}_${type === 'detailed' ? 'Detail' : 'Ringkasan'}.xlsx`;
    }
    
    const sortedIds = Object.keys(dataToExport).sort((a, b) => parseInt(a) - parseInt(b));
    let worksheetData = [];
    
    if (type === 'detailed' || type === 'filtered') {
        worksheetData.push(['ID', 'Nama', 'Divisi', 'Tanggal', 'Hari', 'Jam Datang', 'Jam Pulang', 'Status Hari', 'Izin', 'Denda Terlambat', 'Denda Pulang Awal', 'Denda Tidak Absen Datang', 'Denda Tidak Absen Pulang', 'Denda Tidak Hadir Sama Sekali', 'Total Denda Hari', 'Keterangan']);
        
        sortedIds.forEach(id => {
            const employee = dataToExport[id];
            const attendanceByDate = employee.attendanceByDate || processedData.employees[id].attendanceByDate;
            const completenessInfo = checkAttendanceCompleteness(attendanceByDate, employee.divisi, selectedMonth, selectedYear, id);
            const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
            
            for (let date = 1; date <= daysInMonth; date++) {
                const dayInfo = getDayInfo(date, selectedMonth, selectedYear, employee.divisi);
                const dayAttendance = attendanceByDate[date] || [];
                let jamDatang = '';
                let jamPulang = '';
                let statusHari = '';
                let izinInfo = '';
                let dendaTerlambat = 0;
                let dendaPulangAwal = 0;
                let dendaTidakAbsenDatang = 0;
                let dendaTidakAbsenPulang = 0;
                let dendaTidakHadirSamaSekali = 0;
                let keterangan = '';
                
                const currentDate = new Date(selectedYear, selectedMonth - 1, date);
                const dayOfWeek = currentDate.getDay();
                const permitKey = `${id}-${date}`;
                const permit = permitData[permitKey];
                
                if (permit && permit.month === selectedMonth && permit.year === selectedYear) {
                    izinInfo = `${getPermitTypeName(permit.type)}: ${permit.reason}`;
                }
                
                if (dayInfo.holiday) {
                    statusHari = `Libur: ${dayInfo.holiday.name}`;
                } else if (dayInfo.specialEvent) {
                    statusHari = `Event: ${dayInfo.specialEvent.name}`;
                } else if (dayInfo.isWeekend) {
                    statusHari = 'Weekend';
                } else if (dayOfWeek === 5) {
                    statusHari = 'Hari Kerja (Jumat)';
                } else {
                    statusHari = 'Hari Kerja';
                }
                
                if (dayAttendance.length > 0) {
                    const categorizedEntries = categorizeAttendanceEntries(dayAttendance, employee.divisi, date, selectedMonth, selectedYear, id);
                    const arrivalEntries = categorizedEntries.filter(entry => entry.type === 'arrival');
                    const departureEntries = categorizedEntries.filter(entry => entry.type === 'departure');
                    
                    const hasIncompleteAttendancePenalty = checkDayIncompleteAttendance(categorizedEntries, dayInfo.isWorkingDay, id, date);
                    
                    if (arrivalEntries.length > 0) {
                        jamDatang = arrivalEntries[0].formattedTime;
                        if (!arrivalEntries[0].permitInfo) {
                            dendaTerlambat = arrivalEntries[0].penaltyAmount;
                            if (hasIncompleteAttendancePenalty && dendaTerlambat > 0) {
                                keterangan = 'Denda terlambat dibatalkan (kehadiran tidak lengkap)';
                                dendaTerlambat = 0;
                            }
                        }
                    }
                    if (departureEntries.length > 0) {
                        jamPulang = departureEntries[0].formattedTime;
                        if (!departureEntries[0].permitInfo) {
                            dendaPulangAwal = departureEntries[0].penaltyAmount;
                            if (hasIncompleteAttendancePenalty && dendaPulangAwal > 0) {
                                if (keterangan) keterangan += '; ';
                                keterangan += 'Denda pulang awal dibatalkan (kehadiran tidak lengkap)';
                                dendaPulangAwal = 0;
                            }
                        }
                    }
                    
                    // Cek apakah ada absen yang tidak lengkap di hari kerja
                    if (dayInfo.isWorkingDay) {
                        const arrivalPermit = getPermitForEmployee(id, date, 'arrival');
                        const departurePermit = getPermitForEmployee(id, date, 'departure');
                        const fullPermit = getPermitForEmployee(id, date, 'full');
                        
                        if (!fullPermit) {
                            // Ada sebagian absen (hitung terpisah)
                            if (!arrivalPermit && !arrivalEntries.length) {
                                dendaTidakAbsenDatang = getIncompleteAttendancePenalty();
                            }
                            if (!departurePermit && !departureEntries.length) {
                                dendaTidakAbsenPulang = getIncompleteAttendancePenalty();
                            }
                        }
                    }
                } else if (dayInfo.isWorkingDay) {
                    // Tidak ada data attendance sama sekali untuk hari kerja
                    const fullPermit = getPermitForEmployee(id, date, 'full');
                    if (!fullPermit) {
                        // Tidak hadir sama sekali
                        dendaTidakHadirSamaSekali = getIncompleteAttendancePenalty();
                        keterangan = 'Tidak hadir sama sekali (tidak ada absen datang dan pulang)';
                    }
                }
                
                // Hitung total denda hari
                let totalDendaHari = dendaTerlambat + dendaPulangAwal + dendaTidakAbsenDatang + dendaTidakAbsenPulang + dendaTidakHadirSamaSekali;

                worksheetData.push([id, employee.nama, getDivisionFullName(employee.divisi), date, dayInfo.dayName, jamDatang, jamPulang, statusHari, izinInfo, dendaTerlambat, dendaPulangAwal, dendaTidakAbsenDatang, dendaTidakAbsenPulang, dendaTidakHadirSamaSekali, totalDendaHari, keterangan]);
            }
        });
    } else if (type === 'summary') {
        worksheetData.push(['ID', 'Nama', 'Divisi', 'Total Hari Hadir', 'Hari Tidak Lengkap', 'Hari Tidak Hadir', 'Izin Penuh', 'Izin Kedatangan', 'Izin Kepulangan', 'Denda Terlambat', 'Denda Pulang Awal', 'Denda Tidak Absen Datang', 'Denda Tidak Absen Pulang', 'Denda Tidak Hadir Sama Sekali', 'Total Denda']);
        
        sortedIds.forEach(id => {
            const employee = dataToExport[id];
            const attendanceByDate = employee.attendanceByDate || processedData.employees[id].attendanceByDate;
            const completenessInfo = checkAttendanceCompleteness(attendanceByDate, employee.divisi, selectedMonth, selectedYear, id);
            let totalDendaTerlambat = 0;
            let totalDendaPulangAwal = 0;
            
            Object.keys(attendanceByDate).forEach(tanggal => {
                const categorizedEntries = categorizeAttendanceEntries(attendanceByDate[tanggal], employee.divisi, parseInt(tanggal), selectedMonth, selectedYear, id);
                const dayInfo = getDayInfo(parseInt(tanggal), selectedMonth, selectedYear, employee.divisi);
                const hasIncompleteAttendancePenalty = checkDayIncompleteAttendance(categorizedEntries, dayInfo.isWorkingDay, id, parseInt(tanggal));
                
                categorizedEntries.forEach(entry => {
                    if (entry.type === 'arrival' && !entry.permitInfo) {
                        if (!hasIncompleteAttendancePenalty) {
                            totalDendaTerlambat += entry.penaltyAmount;
                        }
                    } else if (entry.type === 'departure' && !entry.permitInfo) {
                        if (!hasIncompleteAttendancePenalty) {
                            totalDendaPulangAwal += entry.penaltyAmount;
                        }
                    }
                });
            });
            
            const permitCounts = {full: 0, arrival: 0, departure: 0};
            Object.keys(permitData).forEach(key => {
                const permit = permitData[key];
                if (permit.id === id && permit.month === selectedMonth && permit.year === selectedYear) {
                    permitCounts[permit.type]++;
                }
            });
            
            // Hitung denda tidak absen datang, pulang, dan tidak hadir sama sekali
            const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();
            let dendaTidakAbsenDatang = 0;
            let dendaTidakAbsenPulang = 0;
            let dendaTidakHadirSamaSekali = 0;

            for (let date = 1; date <= daysInMonth; date++) {
                const dayInfo = getDayInfo(date, selectedMonth, selectedYear, employee.divisi);
                if (dayInfo.isWorkingDay) {
                    const fullPermit = getPermitForEmployee(id, date, 'full');
                    if (!fullPermit) {
                        const dayAttendance = attendanceByDate[date] || [];
                        if (dayAttendance.length > 0) {
                            // Ada absen, cek apakah lengkap
                            const categorizedEntries = categorizeAttendanceEntries(dayAttendance, employee.divisi, date, selectedMonth, selectedYear, id);
                            const arrivalPermit = getPermitForEmployee(id, date, 'arrival');
                            const departurePermit = getPermitForEmployee(id, date, 'departure');
                            const hasArrival = categorizedEntries.some(entry => entry.type === 'arrival');
                            const hasDeparture = categorizedEntries.some(entry => entry.type === 'departure');
                            
                            if (!arrivalPermit && !hasArrival) {
                                dendaTidakAbsenDatang += getIncompleteAttendancePenalty();
                            }
                            if (!departurePermit && !hasDeparture) {
                                dendaTidakAbsenPulang += getIncompleteAttendancePenalty();
                            }
                        } else {
                            // Tidak ada absen sama sekali
                            dendaTidakHadirSamaSekali += getIncompleteAttendancePenalty();
                        }
                    }
                }
            }

            const totalDenda = totalDendaTerlambat + totalDendaPulangAwal + dendaTidakAbsenDatang + dendaTidakAbsenPulang + dendaTidakHadirSamaSekali;
            worksheetData.push([id, employee.nama, getDivisionFullName(employee.divisi), Object.keys(attendanceByDate).length, completenessInfo.incompleteWorkingDays.length, completenessInfo.absentWorkingDays.length, permitCounts.full, permitCounts.arrival, permitCounts.departure, totalDendaTerlambat, totalDendaPulangAwal, dendaTidakAbsenDatang, dendaTidakAbsenPulang, dendaTidakHadirSamaSekali, totalDenda]);
        });
    }
    
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(worksheetData);
    const colWidths = worksheetData[0].map((_, i) => {
        const maxLength = Math.max(...worksheetData.map(row => row[i] ? row[i].toString().length : 0));
        return { wch: Math.min(Math.max(maxLength + 2, 10), 30) };
    });
    ws['!cols'] = colWidths;
    const sheetName = type === 'detailed' ? 'Detail Kehadiran' : type === 'filtered' ? 'Data Terfilter' : 'Ringkasan Denda';
    XLSX.utils.book_append_sheet(wb, ws, sheetName);
    XLSX.writeFile(wb, filename);
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
        
        // SPA Navigation
        function showSPATab(tabName, clickedElement) {
            console.log('Switching to tab:', tabName);
            
            // Hide all content
            document.querySelectorAll('.spa-content').forEach(content => {
                content.classList.remove('active');
                content.style.display = 'none';
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.spa-nav-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected content
            const contentDiv = document.getElementById('spa-content-' + tabName);
            if (contentDiv) {
                contentDiv.classList.add('active');
                contentDiv.style.display = 'block';
                console.log('Content div found and shown:', contentDiv.id);
            } else {
                console.error('Content div not found for tab:', tabName);
            }
            
            // Add active class to clicked tab
            if (clickedElement) {
                clickedElement.classList.add('active');
            } else {
                // Find tab by data-tab attribute
                const tabButton = document.querySelector(`[data-tab="${tabName}"]`);
                if (tabButton) {
                    tabButton.classList.add('active');
                }
            }
            
            // Update URL without refresh
            const newUrl = window.location.pathname + '?tab=' + tabName;
            window.history.pushState({tab: tabName}, '', newUrl);
        }
        
        // Initialize tab from URL
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab') || 'home';
        if (initialTab !== 'home') {
            setTimeout(() => {
                showSPATab(initialTab);
            }, 100);
        } else {
            // Ensure home tab is active on initial load
            showSPATab('home');
        }
        
        // Handle browser back/forward
        window.addEventListener('popstate', function(event) {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'home';
            showSPATab(tab);
        });
    </script>
    
    <!-- Other SPA Content Areas -->
    <div id="spa-content-edit" class="spa-content" style="display: none;">
        <iframe class="spa-iframe" src="edit.php" id="iframe-edit"></iframe>
    </div>
    
    <div id="spa-content-settings" class="spa-content" style="display: none;">
        <?php
        // Include settings content
        if ($config) {
            include __DIR__ . '/settings_content.php';
        } else {
            echo '<div style="padding: 20px; text-align: center; background: white; margin: 20px; border-radius: 8px;">Error: Config tidak ditemukan</div>';
        }
        ?>
    </div>
    
    <div id="spa-content-izin" class="spa-content" style="display: none;">
        <iframe class="spa-iframe" src="izin.php" id="iframe-izin"></iframe>
    </div>
    
    <div id="spa-content-work_schedule" class="spa-content" style="display: none;">
        <iframe class="spa-iframe" src="work_schedule.php" id="iframe-work_schedule"></iframe>
    </div>
</body>
</html>