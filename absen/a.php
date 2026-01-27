<?php
// settings.php - Configuration Editor for Attendance System

// Load existing config
$config_file = 'config.json';
$config = [];

if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true);
} else {
    // Default configuration matching the JSON structure
    $config = [
        "default_work_schedule" => [
            "start_time" => "08:00",
            "end_time" => "16:00"
        ],
        "division_schedules" => [
            "SD" => [
                "name" => "Sekolah Dasar",
                "start_time" => "07:00",
                "end_time" => "15:00",
                "ids" => [191,185,186,187,188,189,190,184,183,179,176,175,172,169,168,166,165,164,163,162,161,153,152,150,149,143,139,138,137,129,124,115,15,14,13,12,11,21,23,24,26,30,31,33,39,40,42,49,50,51,52,53,54,56,58,63,102,106,109,111,113]
            ],
            "YYS" => [
                "name" => "Yayasan",
                "start_time" => "08:00",
                "end_time" => "16:00",
                "ids" => [1,2,146,181]
            ],
            "SMP" => [
                "name" => "SMP",
                "start_time" => "07:30",
                "end_time" => "15:30",
                "ids" => [170,157,156,154,65,72,73,79,80,83,84,87,112,119,132,133,174,178,180]
            ],
            "TK" => [
                "name" => "Taman Kanak-Kanak",
                "start_time" => "07:30",
                "end_time" => "14:30",
                "ids" => [151,92,93,97,99,100,101,103,104,182]
            ],
            "TAAM" => [
                "name" => "TAAM",
                "start_time" => "08:00",
                "end_time" => "16:00",
                "ids" => [114]
            ],
            "TAUD" => [
                "name" => "TAUD",
                "start_time" => "07:30",
                "end_time" => "14:30",
                "ids" => [126,127,159,173]
            ]
        ],
        "holidays" => [
            "2025" => []
        ],
        "special_days" => [
            "2025" => []
        ],
        "late_policy" => [
            "grace_period_minutes" => 5,
            "levels" => [
                [
                    "name" => "Terlambat Ringan",
                    "min_minutes" => 6,
                    "max_minutes" => 15,
                    "penalty_amount" => 20000,
                    "penalty_type" => "fixed",
                    "color" => "#fff3cd",
                    "icon" => "⚠️"
                ],
                [
                    "name" => "Terlambat Sedang",
                    "min_minutes" => 16,
                    "max_minutes" => 30,
                    "penalty_amount" => 10000,
                    "penalty_type" => "fixed",
                    "color" => "#ffeaa7",
                    "icon" => "🟡"
                ],
                [
                    "name" => "Terlambat Berat",
                    "min_minutes" => 31,
                    "max_minutes" => 60,
                    "penalty_amount" => 25000,
                    "penalty_type" => "fixed",
                    "color" => "#fdcb6e",
                    "icon" => "🟠"
                ],
                [
                    "name" => "Terlambat Sangat Berat",
                    "min_minutes" => 61,
                    "max_minutes" => 999,
                    "penalty_amount" => 99999,
                    "penalty_type" => "fixed",
                    "color" => "#e17055",
                    "icon" => "🔴"
                ]
            ]
        ],
        "early_departure_policy" => [
            "grace_period_minutes" => 5,
            "levels" => [
                [
                    "name" => "Pulang Awal Ringan",
                    "min_minutes" => 6,
                    "max_minutes" => 30,
                    "penalty_amount" => 10000,
                    "penalty_type" => "fixed",
                    "color" => "#ddd6fe",
                    "icon" => "🟣"
                ],
                [
                    "name" => "Pulang Awal Berat",
                    "min_minutes" => 31,
                    "max_minutes" => 999,
                    "penalty_amount" => 25000,
                    "penalty_type" => "fixed",
                    "color" => "#c084fc",
                    "icon" => "🟪"
                ]
            ]
        ],
        "absence_policy" => [
            "full_day_penalty" => 100000,
            "half_day_penalty" => 50000
        ],
        "currency" => [
            "symbol" => "Rp",
            "format" => "Rp %s"
        ]
    ];
}

// Handle form submission
$message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['save_config'])) {
            // Update default work schedule
            if (isset($_POST['default_start_time']) && isset($_POST['default_end_time'])) {
                $config['default_work_schedule']['start_time'] = $_POST['default_start_time'];
                $config['default_work_schedule']['end_time'] = $_POST['default_end_time'];
            }
            
            // Update division schedules
            if (isset($_POST['division_schedule'])) {
                foreach ($_POST['division_schedule'] as $div_code => $schedule) {
                    if (isset($config['division_schedules'][$div_code])) {
                        $config['division_schedules'][$div_code]['start_time'] = $schedule['start_time'];
                        $config['division_schedules'][$div_code]['end_time'] = $schedule['end_time'];
                        $config['division_schedules'][$div_code]['name'] = $schedule['name'];
                        
                        // Update IDs
                        if (!empty($schedule['ids'])) {
                            $ids = array_map('trim', explode(',', $schedule['ids']));
                            $ids = array_map('intval', array_filter($ids, 'is_numeric'));
                            $config['division_schedules'][$div_code]['ids'] = array_values($ids);
                        }
                    }
                }
            }
            
            // Update late policy
            if (isset($_POST['late_grace_period'])) {
                $config['late_policy']['grace_period_minutes'] = (int)$_POST['late_grace_period'];
            }
            
            // Update late levels
            if (isset($_POST['late_level'])) {
                $config['late_policy']['levels'] = [];
                foreach ($_POST['late_level'] as $level) {
                    if (!empty($level['name'])) {
                        $config['late_policy']['levels'][] = [
                            'name' => $level['name'],
                            'min_minutes' => (int)$level['min_minutes'],
                            'max_minutes' => (int)$level['max_minutes'],
                            'penalty_amount' => (int)$level['penalty_amount'],
                            'penalty_type' => 'fixed',
                            'color' => $level['color'],
                            'icon' => $level['icon']
                        ];
                    }
                }
            }
            
            // Update early departure policy
            if (isset($_POST['early_grace_period'])) {
                $config['early_departure_policy']['grace_period_minutes'] = (int)$_POST['early_grace_period'];
            }
            
            // Update early levels
            if (isset($_POST['early_level'])) {
                $config['early_departure_policy']['levels'] = [];
                foreach ($_POST['early_level'] as $level) {
                    if (!empty($level['name'])) {
                        $config['early_departure_policy']['levels'][] = [
                            'name' => $level['name'],
                            'min_minutes' => (int)$level['min_minutes'],
                            'max_minutes' => (int)$level['max_minutes'],
                            'penalty_amount' => (int)$level['penalty_amount'],
                            'penalty_type' => 'fixed',
                            'color' => $level['color'],
                            'icon' => $level['icon']
                        ];
                    }
                }
            }
            
            // Update absence policy
            if (isset($_POST['full_day_penalty'])) {
                $config['absence_policy']['full_day_penalty'] = (int)$_POST['full_day_penalty'];
            }
            if (isset($_POST['half_day_penalty'])) {
                $config['absence_policy']['half_day_penalty'] = (int)$_POST['half_day_penalty'];
            }
            
            // Save to file
            if (file_put_contents($config_file, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                $message = 'Konfigurasi berhasil disimpan!';
            } else {
                $error_message = 'Gagal menyimpan konfigurasi!';
            }
        }
        
        // Handle adding new late level
        if (isset($_POST['add_late_level'])) {
            $config['late_policy']['levels'][] = [
                'name' => 'Level Baru',
                'min_minutes' => 1,
                'max_minutes' => 5,
                'penalty_amount' => 5000,
                'penalty_type' => 'fixed',
                'color' => '#ffffff',
                'icon' => '⭕'
            ];
        }
        
        // Handle adding new early level
        if (isset($_POST['add_early_level'])) {
            $config['early_departure_policy']['levels'][] = [
                'name' => 'Level Baru',
                'min_minutes' => 1,
                'max_minutes' => 5,
                'penalty_amount' => 5000,
                'penalty_type' => 'fixed',
                'color' => '#ffffff',
                'icon' => '⭕'
            ];
        }
        
        // Handle removing late level
        if (isset($_POST['remove_late_level'])) {
            $index = (int)$_POST['remove_late_level'];
            if (isset($config['late_policy']['levels'][$index])) {
                unset($config['late_policy']['levels'][$index]);
                $config['late_policy']['levels'] = array_values($config['late_policy']['levels']);
            }
        }
        
        // Handle removing early level
        if (isset($_POST['remove_early_level'])) {
            $index = (int)$_POST['remove_early_level'];
            if (isset($config['early_departure_policy']['levels'][$index])) {
                unset($config['early_departure_policy']['levels'][$index]);
                $config['early_departure_policy']['levels'] = array_values($config['early_departure_policy']['levels']);
            }
        }
        
    } catch (Exception $e) {
        $error_message = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

// Download config
if (isset($_GET['download'])) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="config.json"');
    echo json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Initialize arrays if they don't exist
if (!isset($config['division_schedules'])) {
    $config['division_schedules'] = [];
}
if (!isset($config['late_policy']['levels'])) {
    $config['late_policy']['levels'] = [];
}
if (!isset($config['early_departure_policy']['levels'])) {
    $config['early_departure_policy']['levels'] = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem Absensi</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header { 
            text-align: center; 
            padding: 30px 20px; 
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white; 
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 2.2em;
            font-weight: 300;
        }
        
        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .alert {
            margin: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            border: 1px solid;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert.success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert.error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .section { 
            margin: 0; 
            border-bottom: 1px solid #e9ecef;
        }
        
        .section:last-child {
            border-bottom: none;
        }
        
        .section-header { 
            background: #f8f9fa; 
            padding: 20px; 
            font-weight: 600;
            font-size: 1.1em;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
        }
        
        .section-body { 
            padding: 25px; 
        }
        
        .form-group { 
            margin-bottom: 20px; 
        }
        
        .form-row { 
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px; 
            margin-bottom: 20px; 
        }
        
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600;
            color: #495057;
        }
        
        input, textarea, select { 
            width: 100%; 
            padding: 12px 15px; 
            border: 2px solid #e9ecef; 
            border-radius: 8px; 
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
        }
        
        button { 
            padding: 12px 20px; 
            background: #2196F3; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        button:hover { 
            background: #1976D2; 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }
        
        button.danger { 
            background: #f44336; 
        }
        
        button.danger:hover { 
            background: #d32f2f; 
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
        }
        
        button.success { 
            background: #4caf50; 
        }
        
        button.success:hover { 
            background: #388e3c; 
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }
        
        .level-item, .division-item { 
            background: #f8f9fa; 
            border: 2px solid #e9ecef; 
            border-radius: 12px; 
            padding: 20px; 
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .level-item:hover, .division-item:hover {
            border-color: #2196F3;
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.1);
        }
        
        .level-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 15px; 
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .level-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1em;
            font-weight: 600;
        }
        
        .color-preview { 
            width: 24px; 
            height: 24px; 
            border-radius: 50%; 
            display: inline-block; 
            margin-left: 10px; 
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .json-preview { 
            background: #2d3748; 
            color: #e2e8f0; 
            padding: 20px; 
            border-radius: 8px; 
            font-family: 'Courier New', monospace; 
            font-size: 12px; 
            overflow-x: auto; 
            max-height: 500px; 
            overflow-y: auto;
            line-height: 1.5;
        }
        
        .tabs { 
            display: flex; 
            background: #f8f9fa; 
            overflow-x: auto;
            border-bottom: 1px solid #dee2e6;
        }
        
        .tab { 
            padding: 18px 25px; 
            cursor: pointer; 
            border-bottom: 3px solid transparent; 
            text-decoration: none; 
            color: #6c757d;
            font-weight: 600;
            transition: all 0.3s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tab.active { 
            background: white; 
            border-bottom-color: #2196F3; 
            color: #2196F3; 
        }
        
        .tab:hover:not(.active) {
            background: #e9ecef;
            color: #495057;
        }
        
        .tab-content { 
            display: none; 
        }
        
        .tab-content.active { 
            display: block; 
        }
        
        .save-section {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        
        .save-section button {
            font-size: 16px;
            padding: 15px 30px;
            margin: 0 10px;
        }
        
        .main-save-btn {
            background: linear-gradient(135deg, #4caf50, #45a049);
            font-size: 18px;
            padding: 18px 35px;
        }
        
        .main-save-btn:hover {
            background: linear-gradient(135deg, #45a049, #3d8b3d);
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-wrap: wrap;
            }
            
            .tab {
                flex: 1;
                min-width: 120px;
            }
            
            .level-header {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Pengaturan Sistem Absensi</h1>
            <p>Konfigurasi kebijakan keterlambatan, pulang awal, jadwal kerja, dan divisi</p>
        </div>

        <?php if ($message): ?>
            <div class="alert success">
                <span>✅</span>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert error">
                <span>❌</span>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <div class="tabs">
            <a href="#work-schedule" class="tab active" onclick="showTab('work-schedule', this)">📅 Jadwal Kerja</a>
            <a href="#divisions" class="tab" onclick="showTab('divisions', this)">🏢 Divisi</a>
            <a href="#late-policy" class="tab" onclick="showTab('late-policy', this)">⏰ Kebijakan Terlambat</a>
            <a href="#early-departure" class="tab" onclick="showTab('early-departure', this)">🏃 Pulang Awal</a>
            <a href="#absence-policy" class="tab" onclick="showTab('absence-policy', this)">❌ Kebijakan Absen</a>
            <a href="#preview" class="tab" onclick="showTab('preview', this)">👁️ Preview JSON</a>
        </div>

        <form method="post">
            <!-- Work Schedule Tab -->
            <div id="work-schedule" class="tab-content active">
                <div class="section">
                    <div class="section-header">📅 Jadwal Kerja Default</div>
                    <div class="section-body">
                        <div class="form-row">
                            <div>
                                <label for="default_start_time">Jam Masuk Default</label>
                                <input type="time" id="default_start_time" name="default_start_time" 
                                       value="<?php echo isset($config['default_work_schedule']) ? $config['default_work_schedule']['start_time'] : '08:00'; ?>">
                            </div>
                            <div>
                                <label for="default_end_time">Jam Keluar Default</label>
                                <input type="time" id="default_end_time" name="default_end_time" 
                                       value="<?php echo isset($config['default_work_schedule']) ? $config['default_work_schedule']['end_time'] : '16:00'; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Divisions Tab -->
            <div id="divisions" class="tab-content">
                <div class="section">
                    <div class="section-header">🏢 Pengaturan Divisi dan Jadwal Khusus</div>
                    <div class="section-body">
                        <?php foreach ($config['division_schedules'] as $div_code => $division): ?>
                        <div class="division-item">
                            <h4 style="margin-top: 0; color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 10px;">
                                <?php echo htmlspecialchars($division['name']); ?> (<?php echo htmlspecialchars($div_code); ?>)
                            </h4>
                            <div class="form-row">
                                <div>
                                    <label>Nama Divisi</label>
                                    <input type="text" name="division_schedule[<?php echo $div_code; ?>][name]" 
                                           value="<?php echo htmlspecialchars($division['name']); ?>">
                                </div>
                                <div>
                                    <label>Jam Masuk</label>
                                    <input type="time" name="division_schedule[<?php echo $div_code; ?>][start_time]" 
                                           value="<?php echo $division['start_time']; ?>">
                                </div>
                                <div>
                                    <label>Jam Keluar</label>
                                    <input type="time" name="division_schedule[<?php echo $div_code; ?>][end_time]" 
                                           value="<?php echo $division['end_time']; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>ID Karyawan (pisahkan dengan koma)</label>
                                <textarea name="division_schedule[<?php echo $div_code; ?>][ids]" rows="3" 
                                          placeholder="1,2,3,4,5..."><?php echo implode(',', $division['ids']); ?></textarea>
                                <small style="color: #6c757d; margin-top: 5px; display: block;">
                                    Total: <?php echo count($division['ids']); ?> karyawan
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Holidays Tab -->
            <div id="holidays" class="tab-content">
                <div class="section">
                    <div class="section-header">🎉 Hari Libur Nasional & Keagamaan</div>
                    <div class="section-body">
                        <p style="color: #6c757d; margin-bottom: 20px;">
                            Kelola hari libur nasional dan keagamaan. Pada hari libur, tidak ada kewajiban absensi dan tidak ada perhitungan keterlambatan.
                        </p>
                        
                        <?php 
                        $holidays = isset($config['holidays']['2025']) ? $config['holidays']['2025'] : [];
                        foreach ($holidays as $index => $holiday): ?>
                        <div class="level-item">
                            <div class="level-header">
                                <div class="level-title">
                                    <span><?php echo $holiday['type'] == 'national' ? '🇮🇩' : '🕌'; ?></span>
                                    <span><?php echo htmlspecialchars($holiday['name']); ?></span>
                                    <span style="color: #6c757d; font-size: 0.9em;"><?php echo date('d M Y', strtotime($holiday['date'])); ?></span>
                                </div>
                                <button type="submit" name="remove_holiday" value="<?php echo $index; ?>" 
                                        class="danger" onclick="return confirm('Hapus hari libur ini?')">🗑️ Hapus</button>
                            </div>
                            <div class="form-row">
                                <div>
                                    <label>Tanggal</label>
                                    <input type="date" name="holiday[<?php echo $index; ?>][date]" 
                                           value="<?php echo $holiday['date']; ?>" required>
                                </div>
                                <div>
                                    <label>Nama Hari Libur</label>
                                    <input type="text" name="holiday[<?php echo $index; ?>][name]" 
                                           value="<?php echo htmlspecialchars($holiday['name']); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div>
                                    <label>Jenis</label>
                                    <select name="holiday[<?php echo $index; ?>][type]">
                                        <option value="national" <?php echo $holiday['type'] == 'national' ? 'selected' : ''; ?>>Nasional</option>
                                        <option value="religious" <?php echo $holiday['type'] == 'religious' ? 'selected' : ''; ?>>Keagamaan</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Deskripsi</label>
                                    <input type="text" name="holiday[<?php echo $index; ?>][description]" 
                                           value="<?php echo htmlspecialchars($holiday['description']); ?>">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" name="add_holiday" class="success">➕ Tambah Hari Libur Baru</button>
                    </div>
                </div>
            </div>

            <!-- Meetings Tab -->
            <div id="meetings" class="tab-content">
                <div class="section">
                    <div class="section-header">📋 Rapat, Evaluasi & Event Khusus</div>
                    <div class="section-body">
                        <p style="color: #6c757d; margin-bottom: 20px;">
                            Kelola jadwal rapat, evaluasi, dan event khusus yang memiliki jadwal kerja berbeda dari biasanya. 
                            Berbeda dengan hari libur, pada hari rapat tetap ada kewajiban absensi dengan jadwal yang telah ditentukan.
                        </p>
                        
                        <?php 
                        $special_days = isset($config['special_days']['2025']) ? $config['special_days']['2025'] : [];
                        foreach ($special_days as $index => $special): ?>
                        <div class="level-item">
                            <div class="level-header">
                                <div class="level-title">
                                    <span>
                                        <?php 
                                        switch($special['type']) {
                                            case 'meeting': echo '📋'; break;
                                            case 'evaluation': echo '📊'; break;
                                            case 'training': echo '🎓'; break;
                                            case 'event': echo '🎪'; break;
                                            default: echo '⭐'; break;
                                        }
                                        ?>
                                    </span>
                                    <span><?php echo htmlspecialchars($special['name']); ?></span>
                                    <span style="color: #6c757d; font-size: 0.9em;">
                                        <?php echo date('d M Y', strtotime($special['date'])); ?>
                                        <?php if (isset($special['custom_schedule'])): ?>
                                            • <?php echo $special['custom_schedule']['start_time']; ?>-<?php echo $special['custom_schedule']['end_time']; ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <button type="submit" name="remove_special_day" value="<?php echo $index; ?>" 
                                        class="danger" onclick="return confirm('Hapus rapat/event ini?')">🗑️ Hapus</button>
                            </div>
                            
                            <div class="form-row">
                                <div>
                                    <label>Tanggal</label>
                                    <input type="date" name="special_day[<?php echo $index; ?>][date]" 
                                           value="<?php echo $special['date']; ?>" required>
                                </div>
                                <div>
                                    <label>Nama Rapat/Event</label>
                                    <input type="text" name="special_day[<?php echo $index; ?>][name]" 
                                           value="<?php echo htmlspecialchars($special['name']); ?>" 
                                           placeholder="contoh: Rapat Koordinasi Bulanan" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div>
                                    <label>Jenis Kegiatan</label>
                                    <select name="special_day[<?php echo $index; ?>][type]">
                                        <option value="meeting" <?php echo $special['type'] == 'meeting' ? 'selected' : ''; ?>>📋 Rapat</option>
                                        <option value="evaluation" <?php echo $special['type'] == 'evaluation' ? 'selected' : ''; ?>>📊 Evaluasi</option>
                                        <option value="training" <?php echo $special['type'] == 'training' ? 'selected' : ''; ?>>🎓 Pelatihan</option>
                                        <option value="event" <?php echo $special['type'] == 'event' ? 'selected' : ''; ?>>🎪 Acara Khusus</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Divisi yang Mengikuti</label>
                                    <select name="special_day[<?php echo $index; ?>][divisions][]" multiple style="height: 100px;">
                                        <option value="all" <?php echo in_array('all', $special['divisions']) ? 'selected' : ''; ?>>
                                            👥 Semua Divisi
                                        </option>
                                        <?php foreach ($config['division_schedules'] as $div_code => $division): ?>
                                        <option value="<?php echo $div_code; ?>" <?php echo in_array($div_code, $special['divisions']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($division['name']); ?> (<?php echo $div_code; ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small style="color: #6c757d; margin-top: 5px; display: block;">
                                        Tahan Ctrl/Cmd untuk memilih beberapa divisi
                                    </small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Deskripsi/Agenda</label>
                                <textarea name="special_day[<?php echo $index; ?>][description]" rows="2" 
                                          placeholder="Contoh: Rapat koordinasi evaluasi kinerja semester..."><?php echo htmlspecialchars($special['description']); ?></textarea>
                            </div>
                            
                            <!-- Custom Schedule Section -->
                            <div style="background: linear-gradient(135deg, #e3f2fd, #f3e5f5); border-radius: 12px; padding: 20px; margin-top: 15px; border: 1px solid #e1bee7;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                    <span style="font-size: 1.2em;">⏰</span>
                                    <h5 style="margin: 0; color: #4a148c; font-weight: 600;">Jadwal Khusus Rapat/Event</h5>
                                </div>
                                <div class="form-row">
                                    <div>
                                        <label>Jam Mulai</label>
                                        <input type="time" name="special_day[<?php echo $index; ?>][start_time]" 
                                               value="<?php echo isset($special['custom_schedule']) ? $special['custom_schedule']['start_time'] : ''; ?>"
                                               style="border-color: #9c27b0;">
                                    </div>
                                    <div>
                                        <label>Jam Selesai</label>
                                        <input type="time" name="special_day[<?php echo $index; ?>][end_time]" 
                                               value="<?php echo isset($special['custom_schedule']) ? $special['custom_schedule']['end_time'] : ''; ?>"
                                               style="border-color: #9c27b0;">
                                    </div>
                                </div>
                                <div style="background: rgba(76, 175, 80, 0.1); border-left: 4px solid #4caf50; padding: 10px; margin-top: 10px; border-radius: 4px;">
                                    <small style="color: #2e7d32; font-weight: 500;">
                                        💡 <strong>Tips:</strong> Jika dikosongkan, akan menggunakan jadwal kerja normal sesuai divisi masing-masing.
                                        Jika diisi, semua peserta wajib mengikuti jadwal khusus ini.
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Meeting Status Indicator -->
                            <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #17a2b8;">
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.9em;">
                                    <span><strong>Status:</strong></span>
                                    <span style="color: #17a2b8;">
                                        <?php 
                                        $event_date = new DateTime($special['date']);
                                        $today = new DateTime();
                                        if ($event_date < $today) {
                                            echo '✅ Sudah Selesai';
                                        } elseif ($event_date->format('Y-m-d') == $today->format('Y-m-d')) {
                                            echo '🔥 Hari Ini';
                                        } else {
                                            echo '📅 Akan Datang';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div style="font-size: 0.85em; color: #6c757d; margin-top: 5px;">
                                    Peserta: <?php echo count($special['divisions']) == 1 && $special['divisions'][0] == 'all' ? 'Semua Divisi' : implode(', ', $special['divisions']); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Add New Meeting Button -->
                        <div style="text-align: center; margin-top: 25px;">
                            <button type="submit" name="add_special_day" class="success" style="padding: 15px 25px; font-size: 16px;">
                                ➕ Tambah Rapat/Event Baru
                            </button>
                        </div>
                        
                        <!-- Quick Templates -->
                        <div style="background: #f8f9fa; border-radius: 10px; padding: 20px; margin-top: 25px;">
                            <h5 style="margin: 0 0 15px 0; color: #495057;">🚀 Template Cepat</h5>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                                <div style="font-size: 0.9em; color: #6c757d;">
                                    <strong>Rapat Bulanan:</strong><br>
                                    Jadwal: 09:00-15:00<br>
                                    Semua divisi
                                </div>
                                <div style="font-size: 0.9em; color: #6c757d;">
                                    <strong>Evaluasi Tahunan:</strong><br>
                                    Jadwal: 08:30-16:30<br>
                                    Semua divisi
                                </div>
                                <div style="font-size: 0.9em; color: #6c757d;">
                                    <strong>Rapat Divisi:</strong><br>
                                    Jadwal: Normal<br>
                                    Divisi tertentu
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Late Policy Tab -->
            <div id="late-policy" class="tab-content">
                <div class="section">
                    <div class="section-header">⏰ Kebijakan Keterlambatan</div>
                    <div class="section-body">
                        <div class="form-group">
                            <label for="late_grace_period">Toleransi Keterlambatan (menit)</label>
                            <input type="number" id="late_grace_period" name="late_grace_period" min="0" max="60"
                                   value="<?php echo $config['late_policy']['grace_period_minutes']; ?>">
                            <small style="color: #6c757d; margin-top: 5px; display: block;">
                                Karyawan tidak akan dikenai denda jika terlambat dalam rentang waktu ini
                            </small>
                        </div>
                        
                        <?php foreach ($config['late_policy']['levels'] as $index => $level): ?>
                        <div class="level-item">
                            <div class="level-header">
                                <div class="level-title">
                                    <span><?php echo $level['icon']; ?></span>
                                    <span><?php echo htmlspecialchars($level['name']); ?></span>
                                    <span class="color-preview" style="background-color: <?php echo $level['color']; ?>"></span>
                                </div>
                                <button type="submit" name="remove_late_level" value="<?php echo $index; ?>" 
                                        class="danger" onclick="return confirm('Hapus level ini?')">🗑️ Hapus</button>
                            </div>
                            <div class="form-row">
                                <div>
                                    <label>Nama Level</label>
                                    <input type="text" name="late_level[<?php echo $index; ?>][name]" 
                                           value="<?php echo htmlspecialchars($level['name']); ?>">
                                </div>
                                <div>
                                    <label>Icon</label>
                                    <input type="text" name="late_level[<?php echo $index; ?>][icon]" 
                                           value="<?php echo htmlspecialchars($level['icon']); ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div>
                                    <label>Minimum Menit</label>
                                    <input type="number" name="late_level[<?php echo $index; ?>][min_minutes]" 
                                           value="<?php echo $level['min_minutes']; ?>" min="1">
                                </div>
                                <div>
                                    <label>Maximum Menit</label>
                                    <input type="number" name="late_level[<?php echo $index; ?>][max_minutes]" 
                                           value="<?php echo $level['max_minutes']; ?>" min="1">
                                </div>
                            </div>
                            <div class="form-row">
                                <div>
                                    <label>Denda (Rp)</label>
                                    <input type="number" name="late_level[<?php echo $index; ?>][penalty_amount]" 
                                           value="<?php echo $level['penalty_amount']; ?>" min="0">
                                </div>
                                <div>
                                    <label>Warna Background</label>
                                    <input type="color" name="late_level[<?php echo $index; ?>][color]" 
                                           value="<?php echo $level['color']; ?>">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" name="add_late_level" class="success">➕ Tambah Level Baru</button>
                    </div>
                </div>
            </div>

            <!-- Early Departure Tab -->
            <div id="early-departure" class="tab-content">
                <div class="section">
                    <div class="section-header">🏃 Kebijakan Pulang Awal</div>
                    <div class="section-body">
                        <div class="form-group">
                            <label for="early_grace_period">Toleransi Pulang Awal (menit)</label>
                            <input type="number" id="early_grace_period" name="early_grace_period" min="0" max="60"
                                   value="<?php echo $config['early_departure_policy']['grace_period_minutes']; ?>">
                            <small style="color: #6c757d; margin-top: 5px; display: block;">
                                Karyawan tidak akan dikenai denda jika pulang awal dalam rentang waktu ini
                            </small>
                        </div>
                        
                        <?php foreach ($config['early_departure_policy']['levels'] as $index => $level): ?>
                        <div class="level-item">
                            <div class="level-header">
                                <div class="level-title">
                                    <span><?php echo $level['icon']; ?></span>
                                    <span><?php echo htmlspecialchars($level['name']); ?></span>
                                    <span class="color-preview" style="background-color: <?php echo $level['color']; ?>"></span>
                                </div>
                                <button type="submit" name="remove_early_level" value="<?php echo $index; ?>" 
                                        class="danger" onclick="return confirm('Hapus level ini?')">🗑️ Hapus</button>
                            </div>
                            <div class="form-row">
                                <div>
                                    <label>Nama Level</label>
                                    <input type="text" name="early_level[<?php echo $index; ?>][name]" 
                                           value="<?php echo htmlspecialchars($level['name']); ?>">
                                </div>
                                <div>
                                    <label>Icon</label>
                                    <input type="text" name="early_level[<?php echo $index; ?>][icon]" 
                                           value="<?php echo htmlspecialchars($level['icon']); ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div>
                                    <label>Minimum Menit</label>
                                    <input type="number" name="early_level[<?php echo $index; ?>][min_minutes]" 
                                           value="<?php echo $level['min_minutes']; ?>" min="1">
                                </div>
                                <div>
                                    <label>Maximum Menit</label>
                                    <input type="number" name="early_level[<?php echo $index; ?>][max_minutes]" 
                                           value="<?php echo $level['max_minutes']; ?>" min="1">
                                </div>
                            </div>
                            <div class="form-row">
                                <div>
                                    <label>Denda (Rp)</label>
                                    <input type="number" name="early_level[<?php echo $index; ?>][penalty_amount]" 
                                           value="<?php echo $level['penalty_amount']; ?>" min="0">
                                </div>
                                <div>
                                    <label>Warna Background</label>
                                    <input type="color" name="early_level[<?php echo $index; ?>][color]" 
                                           value="<?php echo $level['color']; ?>">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" name="add_early_level" class="success">➕ Tambah Level Baru</button>
                    </div>
                </div>
            </div>

            <!-- Absence Policy Tab -->
            <div id="absence-policy" class="tab-content">
                <div class="section">
                    <div class="section-header">❌ Kebijakan Absen</div>
                    <div class="section-body">
                        <div class="form-row">
                            <div>
                                <label for="full_day_penalty">Denda Absen Sehari Penuh (Rp)</label>
                                <input type="number" id="full_day_penalty" name="full_day_penalty" min="0"
                                       value="<?php echo $config['absence_policy']['full_day_penalty']; ?>">
                                <small style="color: #6c757d; margin-top: 5px; display: block;">
                                    Denda untuk tidak masuk kerja sama sekali dalam sehari
                                </small>
                            </div>
                            <div>
                                <label for="half_day_penalty">Denda Absen Setengah Hari (Rp)</label>
                                <input type="number" id="half_day_penalty" name="half_day_penalty" min="0"
                                       value="<?php echo $config['absence_policy']['half_day_penalty']; ?>">
                                <small style="color: #6c757d; margin-top: 5px; display: block;">
                                    Denda untuk absen sebagian hari (hanya masuk/keluar)
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Tab -->
            <div id="preview" class="tab-content">
                <div class="section">
                    <div class="section-header">👁️ Preview Konfigurasi JSON</div>
                    <div class="section-body">
                        <p style="color: #6c757d; margin-bottom: 15px;">
                            Ini adalah preview dari konfigurasi yang akan disimpan. Pastikan semua data sudah benar sebelum menyimpan.
                        </p>
                        <pre class="json-preview"><?php echo json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
                    </div>
                </div>
            </div>
        </form>

        <div class="save-section">
            <form method="post" style="display: inline;">
                <button type="submit" name="save_config" class="main-save-btn">💾 Simpan Semua Pengaturan</button>
            </form>
            <a href="?download=1" class="success" style="display: inline-block; padding: 15px 30px; background: #17a2b8; color: white; text-decoration: none; border-radius: 8px; margin-left: 15px; font-weight: 600;">📥 Download Config</a>
        </div>
    </div>

    <script>
        function showTab(tabId, element) {
            // Prevent default link behavior
            event.preventDefault();
            
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to clicked tab
            element.classList.add('active');
        }
        
        // Add input validation
        document.addEventListener('DOMContentLoaded', function() {
            // Validate time inputs
            const timeInputs = document.querySelectorAll('input[type="time"]');
            timeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value) {
                        this.style.borderColor = '#28a745';
                    }
                });
            });
            
            // Validate number inputs
            const numberInputs = document.querySelectorAll('input[type="number"]');
            numberInputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (parseInt(this.value) < 0) {
                        this.value = 0;
                    }
                });
            });
            
            // Auto-format currency inputs
            const currencyInputs = document.querySelectorAll('input[name*="penalty_amount"], input[name*="_penalty"]');
            currencyInputs.forEach(input => {
                input.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, '');
                    if (value) {
                        // Format as Indonesian Rupiah
                        this.setAttribute('title', 'Rp ' + parseInt(value).toLocaleString('id-ID'));
                    }
                });
            });
            
            // Validate employee IDs format
            const idInputs = document.querySelectorAll('textarea[name*="[ids]"]');
            idInputs.forEach(input => {
                input.addEventListener('blur', function() {
                    let value = this.value.trim();
                    if (value) {
                        // Remove extra spaces and validate format
                        let ids = value.split(',').map(id => id.trim()).filter(id => id !== '');
                        let validIds = ids.filter(id => /^\d+$/.test(id));
                        
                        if (validIds.length !== ids.length) {
                            alert('Beberapa ID tidak valid. Hanya angka yang diperbolehkan.');
                            this.focus();
                        } else {
                            this.value = validIds.join(',');
                            // Update counter
                            let counter = this.nextElementSibling;
                            if (counter && counter.tagName === 'SMALL') {
                                counter.textContent = 'Total: ' + validIds.length + ' karyawan';
                            }
                        }
                    }
                });
            });
            
            // Validate date inputs (prevent past dates for future events)
            const dateInputs = document.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const selectedDate = new Date(this.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    
                    if (selectedDate < today) {
                        // Allow past dates but show a warning for special days
                        if (this.name.includes('special_day')) {
                            if (!confirm('Tanggal yang dipilih sudah lewat. Yakin ingin melanjutkan?')) {
                                this.value = '';
                            }
                        }
                    }
                });
            });
            
            // Handle multiple select for divisions
            const multiSelects = document.querySelectorAll('select[multiple]');
            multiSelects.forEach(select => {
                select.addEventListener('change', function() {
                    const allOption = this.querySelector('option[value="all"]');
                    const otherOptions = Array.from(this.querySelectorAll('option:not([value="all"])'));
                    
                    if (allOption && allOption.selected) {
                        // If "all" is selected, deselect others
                        otherOptions.forEach(option => option.selected = false);
                    } else if (otherOptions.some(option => option.selected) && allOption) {
                        // If any specific division is selected, deselect "all"
                        allOption.selected = false;
                    }
                });
            });
            
            // Confirm before removing levels
            const removeButtons = document.querySelectorAll('button[name*="remove_"]');
            removeButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Yakin ingin menghapus level ini? Tindakan ini tidak dapat dibatalkan.')) {
                        e.preventDefault();
                    }
                });
            });
        });
        
        // Auto-save draft (optional - stores in localStorage)
        function autoSaveDraft() {
            const form = document.querySelector('form');
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            localStorage.setItem('attendance_config_draft', JSON.stringify(data));
        }
        
        // Load draft on page load (optional)
        function loadDraft() {
            const draft = localStorage.getItem('attendance_config_draft');
            if (draft && confirm('Ditemukan draft tersimpan. Muat draft?')) {
                const data = JSON.parse(draft);
                Object.keys(data).forEach(key => {
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.value = data[key];
                    }
                });
            }
        }
        
        // Clear draft after successful save
        function clearDraft() {
            localStorage.removeItem('attendance_config_draft');
        }
        
        // Save draft periodically
        setInterval(autoSaveDraft, 30000); // Every 30 seconds
    </script>
</body>
</html>