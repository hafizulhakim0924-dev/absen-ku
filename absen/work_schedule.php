<?php
// work_schedule.php
session_start();

// Simple file-based storage (you can replace this with database)
$schedules_file = 'schedules.json';

// Default schedules for all divisions
$default_schedules = [
    'SD' => [
        'name' => 'Sekolah Dasar',
        'clock_in' => '07:30',
        'clock_out' => '14:00',
        'friday_clock_in' => '07:30',
        'friday_clock_out' => '11:30',
        'late_limit' => 15, // minutes
        'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']
    ],
    'SMP' => [
        'name' => 'Sekolah Menengah Pertama',
        'clock_in' => '07:00',
        'clock_out' => '15:00',
        'friday_clock_in' => '07:00',
        'friday_clock_out' => '11:30',
        'late_limit' => 15,
        'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']
    ],
    'YYS' => [
        'name' => 'Yayasan',
        'clock_in' => '08:00',
        'clock_out' => '16:00',
        'friday_clock_in' => '08:00',
        'friday_clock_out' => '11:30',
        'late_limit' => 30,
        'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
    ],
    'TK' => [
        'name' => 'Taman Kanak-kanak',
        'clock_in' => '07:30',
        'clock_out' => '12:00',
        'friday_clock_in' => '07:30',
        'friday_clock_out' => '11:00',
        'late_limit' => 15,
        'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']
    ],
    'TAAM' => [
        'name' => 'TAAM',
        'clock_in' => '08:00',
        'clock_out' => '15:00',
        'friday_clock_in' => '08:00',
        'friday_clock_out' => '11:30',
        'late_limit' => 20,
        'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
    ],
    'TAUD' => [
        'name' => 'TAUD',
        'clock_in' => '08:00',
        'clock_out' => '14:00',
        'friday_clock_in' => '08:00',
        'friday_clock_out' => '11:30',
        'late_limit' => 20,
        'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
    ]
];

// Load existing schedules or use defaults
function loadSchedules() {
    global $schedules_file, $default_schedules;
    
    if (file_exists($schedules_file)) {
        $content = file_get_contents($schedules_file);
        $schedules = json_decode($content, true);
        
        if ($schedules === null) {
            return $default_schedules;
        }
        
        // Merge with defaults to ensure all divisions exist and have friday fields
        $merged = [];
        foreach ($default_schedules as $key => $default) {
            $merged[$key] = array_merge($default, $schedules[$key] ?? []);
            if (empty($merged[$key]['friday_clock_in'])) {
                $merged[$key]['friday_clock_in'] = $merged[$key]['clock_in'];
            }
            if (empty($merged[$key]['friday_clock_out'])) {
                $merged[$key]['friday_clock_out'] = $merged[$key]['clock_out'];
            }
        }
        return $merged;
    }
    
    return $default_schedules;
}

// Save schedules to file
function saveSchedules($schedules) {
    global $schedules_file;
    file_put_contents($schedules_file, json_encode($schedules, JSON_PRETTY_PRINT));
}

// Handle form submission
$message = '';
$schedules = loadSchedules();

if ($_POST) {
    if (isset($_POST['update_schedule'])) {
        $division = $_POST['division'];
        
        if (isset($schedules[$division])) {
            $schedules[$division]['clock_in'] = $_POST['clock_in'];
            $schedules[$division]['clock_out'] = $_POST['clock_out'];
            $schedules[$division]['late_limit'] = (int)$_POST['late_limit'];
            $schedules[$division]['working_days'] = isset($_POST['working_days']) ? $_POST['working_days'] : [];
            // Jadwal khusus Jumat per divisi
            $schedules[$division]['friday_clock_in'] = $_POST['friday_clock_in'] ?? $schedules[$division]['clock_in'];
            $schedules[$division]['friday_clock_out'] = $_POST['friday_clock_out'] ?? $schedules[$division]['clock_out'];
            
            saveSchedules($schedules);
            $message = "Jadwal untuk divisi {$schedules[$division]['name']} berhasil diperbarui!";
        }
    }
    
    if (isset($_POST['reset_all'])) {
        $schedules = $default_schedules;
        saveSchedules($schedules);
        $message = "Semua jadwal berhasil direset ke pengaturan default!";
    }
}

$days_of_week = [
    'monday' => 'Senin',
    'tuesday' => 'Selasa', 
    'wednesday' => 'Rabu',
    'thursday' => 'Kamis',
    'friday' => 'Jumat',
    'saturday' => 'Sabtu',
    'sunday' => 'Minggu'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Jadwal Kerja - Semua Divisi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
        }
        
        h1 {
            text-align: center;
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        
        .message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .schedules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .division-card {
            border: 1px solid #ccc;
            padding: 15px;
            background: #f9f9f9;
        }
        
        .division-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .form-group {
            margin-bottom: 10px;
        }
        
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        
        .working-days {
            margin-top: 10px;
        }
        
        .working-days label {
            font-weight: normal;
            display: inline;
            margin-right: 10px;
        }
        
        .working-days input[type="checkbox"] {
            width: auto;
            margin-right: 5px;
        }
        
        .days-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 5px;
            margin-top: 5px;
        }
        
        .btn {
            background: #007bff;
            color: white;
            padding: 8px 15px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .reset-section {
            text-align: center;
            padding: 20px;
            border-top: 2px solid #ddd;
            margin-top: 20px;
        }
        
        .current-schedule {
            background: #e9ecef;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
        }
        
        .schedule-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 14px;
        }
        
        .schedule-info div {
            padding: 5px;
            background: white;
            border: 1px solid #ddd;
        }
        
        .working-days-display {
            grid-column: span 2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pengaturan Jadwal Kerja - Semua Divisi</h1>
        
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="schedules-grid">
            <?php foreach ($schedules as $div_code => $schedule): ?>
                <div class="division-card">
                    <div class="division-title"><?php echo htmlspecialchars($schedule['name']); ?> (<?php echo $div_code; ?>)</div>
                    
                    <div class="current-schedule">
                        <strong>Jadwal Saat Ini:</strong>
                        <div class="schedule-info">
                            <div><strong>Masuk (Senin–Kamis):</strong> <?php echo $schedule['clock_in']; ?></div>
                            <div><strong>Keluar (Senin–Kamis):</strong> <?php echo $schedule['clock_out']; ?></div>
                            <?php
                            $friIn = $schedule['friday_clock_in'] ?? $schedule['clock_in'];
                            $friOut = $schedule['friday_clock_out'] ?? $schedule['clock_out'];
                            ?>
                            <div><strong>Jumat Masuk:</strong> <?php echo $friIn; ?></div>
                            <div><strong>Jumat Keluar:</strong> <?php echo $friOut; ?></div>
                            <div><strong>Batas Terlambat:</strong> <?php echo $schedule['late_limit']; ?> menit</div>
                            <div class="working-days-display">
                                <strong>Hari Kerja:</strong> 
                                <?php 
                                $work_days = [];
                                foreach ($schedule['working_days'] as $day) {
                                    $work_days[] = $days_of_week[$day];
                                }
                                echo implode(', ', $work_days);
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="division" value="<?php echo $div_code; ?>">
                        
                        <div class="form-group">
                            <label>Jam Masuk:</label>
                            <input type="time" name="clock_in" value="<?php echo $schedule['clock_in']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Jam Keluar:</label>
                            <input type="time" name="clock_out" value="<?php echo $schedule['clock_out']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Batas Keterlambatan (menit):</label>
                            <input type="number" name="late_limit" value="<?php echo $schedule['late_limit']; ?>" min="0" max="120" required>
                        </div>
                        
                        <div class="form-group friday-schedule" style="margin-top:12px; padding:10px; background:#f0f7ff; border:1px solid #b8d4e8; border-radius:6px;">
                            <label style="display:block; margin-bottom:8px;">🕐 Jadwal Khusus Jumat (masing-masing divisi bisa berbeda):</label>
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                                <div>
                                    <label style="font-size:12px;">Jumat Masuk</label>
                                    <input type="time" name="friday_clock_in" value="<?php echo htmlspecialchars($schedule['friday_clock_in'] ?? $schedule['clock_in']); ?>">
                                </div>
                                <div>
                                    <label style="font-size:12px;">Jumat Keluar</label>
                                    <input type="time" name="friday_clock_out" value="<?php echo htmlspecialchars($schedule['friday_clock_out'] ?? $schedule['clock_out']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="working-days">
                            <label><strong>Hari Kerja:</strong></label>
                            <div class="days-grid">
                                <?php foreach ($days_of_week as $day_code => $day_name): ?>
                                    <label>
                                        <input type="checkbox" name="working_days[]" value="<?php echo $day_code; ?>"
                                               <?php echo in_array($day_code, $schedule['working_days']) ? 'checked' : ''; ?>>
                                        <?php echo $day_name; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_schedule" class="btn">Update Jadwal</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="reset-section">
            <h3>Reset Semua Jadwal</h3>
            <p>Mengembalikan semua jadwal ke pengaturan default</p>
            <form method="POST" onsubmit="return confirm('Yakin ingin reset semua jadwal ke default?');">
                <button type="submit" name="reset_all" class="btn btn-danger">Reset Semua Jadwal</button>
            </form>
        </div>
        
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6;">
            <h4>Keterangan:</h4>
            <ul style="margin: 10px 0;">
                <li><strong>Jam Masuk/Keluar:</strong> Waktu standar kerja (Senin–Kamis) untuk divisi</li>
                <li><strong>Jadwal Khusus Jumat:</strong> Masing-masing divisi bisa atur jam masuk & keluar Jumat berbeda (misal Jumat pulang lebih awal)</li>
                <li><strong>Batas Keterlambatan:</strong> Maksimal menit terlambat yang masih diterima</li>
                <li><strong>Hari Kerja:</strong> Hari-hari aktif kerja (tidak termasuk libur nasional)</li>
                <li>Data disimpan dalam file <code>schedules.json</code></li>
                <li>Perubahan akan langsung berlaku setelah di-update</li>
            </ul>
        </div>
    </div>
</body>
</html>