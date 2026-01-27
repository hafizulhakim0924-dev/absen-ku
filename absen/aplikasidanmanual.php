<?php
session_start();

// Cek login
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$servername = "localhost";
$username = "ypikhair_admin";
$password = "hakim123123123";
$dbname = "ypikhair_datautama";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

date_default_timezone_set('Asia/Jakarta');

// Ambil bulan dan tahun yang dipilih (default: bulan ini)
$selected_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$month_name = date('F Y', strtotime($selected_month . '-01'));

$all_payments = [];

// 1. Ambil dari tabel pembayaran
$query = "SELECT p.*, s.name as student_name, s.class, 'pembayaran' as source
          FROM pembayaran p 
          LEFT JOIN students s ON p.student_id = s.id 
          WHERE DATE_FORMAT(p.tanggal, '%Y-%m') = '$selected_month'
          ORDER BY p.tanggal DESC";

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $all_payments[] = [
        'tanggal' => $row['tanggal'],
        'student_name' => $row['student_name'],
        'class' => $row['class'],
        'nama_tagihan' => $row['nama_tagihan'],
        'jumlah_bayar' => $row['jumlah_bayar'],
        'dibayar_melalui' => $row['dibayar_melalui'] ?? '-',
        'source' => 'pembayaran'
    ];
}

// 2. Ambil dari tabel payments (hanya yang status = berhasil)
$query2 = "SELECT pm.*, s.name as student_name, s.class 
           FROM payments pm
           LEFT JOIN students s ON pm.student_id = s.id
           WHERE pm.status = 'berhasil'
           AND DATE_FORMAT(pm.updated_at, '%Y-%m') = '$selected_month'
           ORDER BY pm.updated_at DESC";

$result2 = $conn->query($query2);
if ($result2) {
    while ($row = $result2->fetch_assoc()) {
        $all_payments[] = [
            'tanggal' => $row['updated_at'] ?? $row['payment_date'] ?? date('Y-m-d H:i:s'),
            'student_name' => $row['student_name'],
            'class' => $row['class'],
            'nama_tagihan' => $row['tagihan'] ?? 'N/A',
            'jumlah_bayar' => $row['nominal'] ?? 0,
            'dibayar_melalui' => 'Aplikasi',
            'source' => 'payments'
        ];
    }
}

// 3. Ambil dari payments.json
$json_file = $_SERVER['DOCUMENT_ROOT'] . '/payments.json';
if (file_exists($json_file)) {
    $json_data = json_decode(file_get_contents($json_file), true);
    if (is_array($json_data)) {
        foreach ($json_data as $payment) {
            $payment_date = $payment['waktu_input'] ?? $payment['date'] ?? $payment['tanggal'] ?? date('Y-m-d H:i:s');
            $payment_month = date('Y-m', strtotime($payment_date));
            
            if ($payment_month == $selected_month) {
                $sid = isset($payment['student_id']) ? intval($payment['student_id']) : 0;
                $student_info = null;
                if ($sid > 0) {
                    $student_info = $conn->query("SELECT name, class FROM students WHERE id=$sid")->fetch_assoc();
                }
                
                $all_payments[] = [
                    'tanggal' => $payment_date,
                    'student_name' => $student_info['name'] ?? $payment['student_name'] ?? $payment['name'] ?? 'Unknown',
                    'class' => $student_info['class'] ?? $payment['class'] ?? '-',
                    'nama_tagihan' => $payment['tagihan'] ?? $payment['payment_type'] ?? $payment['nama_tagihan'] ?? 'N/A',
                    'jumlah_bayar' => $payment['nominal'] ?? $payment['amount'] ?? $payment['jumlah_bayar'] ?? 0,
                    'dibayar_melalui' => $payment['inputted_by'] ?? $payment['payment_method'] ?? $payment['dibayar_melalui'] ?? '-',
                    'source' => 'json'
                ];
            }
        }
    }
}

// Analisis data per hari
$days_in_month = date('t', strtotime($selected_month . '-01'));
$daily_stats = [];

// Inisialisasi semua hari dalam bulan
for ($i = 1; $i <= $days_in_month; $i++) {
    $day = str_pad($i, 2, '0', STR_PAD_LEFT);
    $daily_stats[$day] = [
        'aplikasi' => 0,
        'manual' => 0,
        'total' => 0,
        'count_aplikasi' => 0,
        'count_manual' => 0
    ];
}

// Hitung pembayaran per hari
$total_aplikasi = 0;
$total_manual = 0;
$count_aplikasi = 0;
$count_manual = 0;

foreach ($all_payments as $payment) {
    $day = date('d', strtotime($payment['tanggal']));
    $metode = $payment['dibayar_melalui'];
    $jumlah = $payment['jumlah_bayar'];
    
    // Pisahkan Aplikasi vs Manual
    if (stripos($metode, 'aplikasi') !== false) {
        $daily_stats[$day]['aplikasi'] += $jumlah;
        $daily_stats[$day]['count_aplikasi']++;
        $total_aplikasi += $jumlah;
        $count_aplikasi++;
    } else {
        $daily_stats[$day]['manual'] += $jumlah;
        $daily_stats[$day]['count_manual']++;
        $total_manual += $jumlah;
        $count_manual++;
    }
    
    $daily_stats[$day]['total'] += $jumlah;
}

// Generate list bulan untuk dropdown (12 bulan terakhir)
$month_options = [];
for ($i = 0; $i < 12; $i++) {
    $month_date = date('Y-m', strtotime("-$i months"));
    $month_label = date('F Y', strtotime($month_date . '-01'));
    $month_options[$month_date] = $month_label;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analisis Pembayaran Harian - <?= $month_name ?></title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    padding: 10px; 
    margin: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.container {
    max-width: 1400px;
    margin: 20px auto;
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
h1 { 
    margin: 0 0 20px 0; 
    color: #333;
    font-size: 32px;
    text-align: center;
    border-bottom: 4px solid #4CAF50;
    padding-bottom: 15px;
}
.header-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}
.month-selector {
    display: flex;
    align-items: center;
    gap: 10px;
}
.month-selector label {
    font-weight: bold;
    color: #333;
}
.month-selector select {
    padding: 10px 15px;
    border: 2px solid #667eea;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    background: white;
}
.back-btn {
    display: inline-block;
    padding: 12px 24px;
    background: #4CAF50;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
    transition: background 0.3s;
}
.back-btn:hover {
    background: #45a049;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    text-align: center;
    transition: transform 0.3s;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    opacity: 0.9;
}
.stat-card .value {
    font-size: 24px;
    font-weight: bold;
    margin: 10px 0;
}
.stat-card .subvalue {
    font-size: 14px;
    opacity: 0.8;
}
.chart-container {
    background: #f9f9f9;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
.chart-container h2 {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 22px;
    border-left: 5px solid #4CAF50;
    padding-left: 15px;
}
.chart-wrapper {
    position: relative;
    height: 450px;
}
</style>
</head>
<body>

<div class="container">
    <div class="header-controls">
        <a href="dashboard_analisis.php" class="back-btn">← Kembali ke Dashboard Utama</a>
        
        <div class="month-selector">
            <label for="month-select">📅 Pilih Bulan:</label>
            <select id="month-select" onchange="changeMonth(this.value)">
                <?php foreach ($month_options as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $value == $selected_month ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <h1>📊 Analisis Pembayaran Harian - <?= $month_name ?></h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>💰 Total Bulan Ini</h3>
            <div class="value">Rp <?= number_format($total_aplikasi + $total_manual, 0, ',', '.') ?></div>
            <div class="subvalue"><?= $count_aplikasi + $count_manual ?> Transaksi</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <h3>📱 Via Aplikasi</h3>
            <div class="value">Rp <?= number_format($total_aplikasi, 0, ',', '.') ?></div>
            <div class="subvalue"><?= $count_aplikasi ?> Transaksi (<?= $total_aplikasi + $total_manual > 0 ? round($total_aplikasi / ($total_aplikasi + $total_manual) * 100, 1) : 0 ?>%)</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <h3>👤 Manual (Dian+Nurul)</h3>
            <div class="value">Rp <?= number_format($total_manual, 0, ',', '.') ?></div>
            <div class="subvalue"><?= $count_manual ?> Transaksi (<?= $total_aplikasi + $total_manual > 0 ? round($total_manual / ($total_aplikasi + $total_manual) * 100, 1) : 0 ?>%)</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <h3>📊 Rata-rata Harian</h3>
            <div class="value">Rp <?= number_format(($total_aplikasi + $total_manual) / $days_in_month, 0, ',', '.') ?></div>
            <div class="subvalue">Per hari dalam bulan ini</div>
        </div>
    </div>

    <!-- Grafik 1: Perbandingan Aplikasi vs Manual per Hari (Stacked Bar) -->
    <div class="chart-container">
        <h2>📊 Perbandingan Harian: Aplikasi vs Manual</h2>
        <div class="chart-wrapper">
            <canvas id="dailyComparisonChart"></canvas>
        </div>
    </div>

    <!-- Grafik 2: Total Pembayaran per Hari (Line) -->
    <div class="chart-container">
        <h2>📈 Trend Total Pembayaran per Hari</h2>
        <div class="chart-wrapper">
            <canvas id="dailyTrendChart"></canvas>
        </div>
    </div>

    <!-- Grafik 3: Jumlah Transaksi per Hari (Bar) -->
    <div class="chart-container">
        <h2>🔢 Jumlah Transaksi per Hari</h2>
        <div class="chart-wrapper">
            <canvas id="transactionCountChart"></canvas>
        </div>
    </div>
</div>

<script>
// Data dari PHP
const dailyData = <?= json_encode($daily_stats) ?>;
const daysInMonth = <?= $days_in_month ?>;

// Prepare data for charts
const labels = [];
const aplikasiData = [];
const manualData = [];
const totalData = [];
const countAplikasiData = [];
const countManualData = [];

for (let i = 1; i <= daysInMonth; i++) {
    const day = String(i).padStart(2, '0');
    labels.push(i);
    aplikasiData.push(dailyData[day].aplikasi);
    manualData.push(dailyData[day].manual);
    totalData.push(dailyData[day].total);
    countAplikasiData.push(dailyData[day].count_aplikasi);
    countManualData.push(dailyData[day].count_manual);
}

// 1. Grafik Perbandingan Harian (Stacked Bar)
const ctx1 = document.getElementById('dailyComparisonChart').getContext('2d');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Aplikasi',
                data: aplikasiData,
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            },
            {
                label: 'Manual (Dian+Nurul)',
                data: manualData,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                stacked: true,
                title: {
                    display: true,
                    text: 'Tanggal'
                }
            },
            y: {
                stacked: true,
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            },
            legend: {
                position: 'top',
                labels: {
                    font: {
                        size: 14
                    },
                    padding: 15
                }
            }
        }
    }
});

// 2. Grafik Trend Total (Line)
const ctx2 = document.getElementById('dailyTrendChart').getContext('2d');
new Chart(ctx2, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Total Pembayaran',
                data: totalData,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6
            },
            {
                label: 'Aplikasi',
                data: aplikasiData,
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 5
            },
            {
                label: 'Manual',
                data: manualData,
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: false,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 5
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Tanggal'
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            },
            legend: {
                position: 'top'
            }
        }
    }
});

// 3. Grafik Jumlah Transaksi (Bar)
const ctx3 = document.getElementById('transactionCountChart').getContext('2d');
new Chart(ctx3, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Transaksi Aplikasi',
                data: countAplikasiData,
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            },
            {
                label: 'Transaksi Manual',
                data: countManualData,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Tanggal'
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                },
                title: {
                    display: true,
                    text: 'Jumlah Transaksi'
                }
            }
        },
        plugins: {
            legend: {
                position: 'top'
            }
        }
    }
});

function changeMonth(month) {
    window.location.href = '?month=' + month;
}
</script>

</body>
</html>