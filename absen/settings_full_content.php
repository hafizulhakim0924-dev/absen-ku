<?php
// settings_full_content.php - Full settings content for index.php
// This replaces the include statements in settings_content.php

if (!isset($config) || !$config) {
    echo '<div class="settings-alert settings-alert-error">Error: Config tidak ditemukan</div>';
    return;
}

// Prepare data (already done in index.php, but ensure it exists)
$allHolidays = $allHolidays ?? [];
$specialSchedules = $specialSchedules ?? [];
?>

<!-- Tab Jadwal Kerja -->
<div id="settings-schedules" class="settings-tab-pane active">
    <form id="scheduleForm">
        <div class="settings-card">
            <h3>⏰ Jadwal Default</h3>
            <div class="settings-form-row">
                <div class="settings-form-group">
                    <label>Jam Masuk:</label>
                    <input type="time" name="default_start_time" value="<?php echo $config['default_work_schedule']['start_time'] ?? '08:00'; ?>" required>
                </div>
                <div class="settings-form-group">
                    <label>Jam Pulang:</label>
                    <input type="time" name="default_end_time" value="<?php echo $config['default_work_schedule']['end_time'] ?? '16:00'; ?>" required>
                </div>
            </div>
        </div>
        
        <div class="settings-card">
            <h3>🏢 Jadwal per Divisi</h3>
            <table class="settings-table">
                <thead>
                    <tr>
                        <th style="width: 150px;">Divisi</th>
                        <th style="width: 120px;">Jam Masuk</th>
                        <th style="width: 120px;">Jam Pulang</th>
                        <th style="width: 200px;">Jadwal Khusus Jumat</th>
                        <th>Jadwal Khusus (Periode Tertentu)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($config['division_schedules'] as $key => $division): 
                        // Get special schedules for this division
                        $divisionSpecialSchedules = array_filter($specialSchedules, function($schedule) use ($key) {
                            return $schedule['division'] === $key;
                        });
                    ?>
                        <tr>
                            <td style="font-weight: 600; color: #333;">
                                <?php echo htmlspecialchars($division['name']); ?><br>
                                <small style="color: #666; font-weight: normal;">(<?php echo $key; ?>)</small>
                            </td>
                            <td>
                                <input type="time" name="division_start_<?php echo $key; ?>" value="<?php echo $division['start_time'] ?? '08:00'; ?>" required>
                            </td>
                            <td>
                                <input type="time" name="division_end_<?php echo $key; ?>" value="<?php echo $division['end_time'] ?? '16:00'; ?>" required>
                            </td>
                            <td>
                                <div class="settings-friday-schedule">
                                    <label>Jumat:</label>
                                    <div class="settings-form-row">
                                        <div class="settings-form-group" style="margin-bottom: 0;">
                                            <input type="time" name="friday_start_<?php echo $key; ?>" value="<?php echo $division['friday_schedule']['start_time'] ?? $division['start_time'] ?? '08:00'; ?>" placeholder="Masuk">
                                        </div>
                                        <div class="settings-form-group" style="margin-bottom: 0;">
                                            <input type="time" name="friday_end_<?php echo $key; ?>" value="<?php echo $division['friday_schedule']['end_time'] ?? $division['end_time'] ?? '16:00'; ?>" placeholder="Pulang">
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($divisionSpecialSchedules)): ?>
                                    <div style="font-size: 12px;">
                                        <?php foreach ($divisionSpecialSchedules as $idx => $schedule): 
                                            // Find index in full specialSchedules array
                                            $fullIndex = false;
                                            foreach ($specialSchedules as $i => $s) {
                                                if ($s['start_date'] === $schedule['start_date'] && 
                                                    $s['end_date'] === $schedule['end_date'] && 
                                                    $s['division'] === $schedule['division']) {
                                                    $fullIndex = $i;
                                                    break;
                                                }
                                            }
                                            if ($fullIndex === false) continue;
                                        ?>
                                            <div style="background: #e3f2fd; padding: 5px; margin: 3px 0; border-radius: 4px; border-left: 3px solid #2196f3;">
                                                <strong><?php echo date('d/m/Y', strtotime($schedule['start_date'])); ?> - <?php echo date('d/m/Y', strtotime($schedule['end_date'])); ?></strong><br>
                                                <small><?php echo $schedule['start_time']; ?> - <?php echo $schedule['end_time']; ?></small>
                                                <?php if ($schedule['description']): ?>
                                                    <br><small style="color: #666;"><?php echo htmlspecialchars($schedule['description']); ?></small>
                                                <?php endif; ?>
                                                <form data-action="delete_special_schedule" style="display: inline; margin-left: 5px;" onsubmit="return confirm('Yakin ingin menghapus jadwal khusus ini?');">
                                                    <input type="hidden" name="index" value="<?php echo $fullIndex; ?>">
                                                    <button type="submit" class="settings-btn settings-btn-danger settings-btn-small" style="padding: 2px 6px; font-size: 10px;">🗑️</button>
                                                </form>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #999; font-size: 12px;">Tidak ada jadwal khusus</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <button type="submit" class="settings-btn">💾 Simpan Semua Jadwal</button>
    </form>
    
    <!-- Jadwal Khusus untuk Tanggal Tertentu -->
    <div class="settings-card" style="margin-top: 30px;">
        <h3>📆 Jadwal Khusus untuk Tanggal Tertentu</h3>
        <p style="color: #666; margin-bottom: 15px; font-size: 14px;">
            Atur jadwal khusus untuk periode tertentu (misal: Januari tgl 5-28, jam masuk X sampai Y untuk divisi tertentu)
        </p>
        
        <form data-action="add_special_schedule">
            <div class="settings-form-row">
                <div class="settings-form-group">
                    <label>Tanggal Mulai:</label>
                    <input type="date" name="start_date" required>
                </div>
                <div class="settings-form-group">
                    <label>Tanggal Akhir:</label>
                    <input type="date" name="end_date" required>
                </div>
            </div>
            <div class="settings-form-row">
                <div class="settings-form-group">
                    <label>Divisi:</label>
                    <select name="division" required>
                        <option value="">Pilih Divisi</option>
                        <?php foreach ($config['division_schedules'] as $key => $division): ?>
                            <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($division['name']); ?> (<?php echo $key; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="settings-form-group">
                    <label>Keterangan (Opsional):</label>
                    <input type="text" name="special_description" placeholder="Contoh: Masa Ujian, Libur Semester, dll">
                </div>
            </div>
            <div class="settings-form-row">
                <div class="settings-form-group">
                    <label>Jam Masuk:</label>
                    <input type="time" name="special_start_time" required>
                </div>
                <div class="settings-form-group">
                    <label>Jam Pulang:</label>
                    <input type="time" name="special_end_time" required>
                </div>
            </div>
            <button type="submit" class="settings-btn">➕ Tambah Jadwal Khusus</button>
        </form>
    </div>
    
    <!-- Daftar Jadwal Khusus -->
    <div class="settings-card" style="margin-top: 20px;">
        <h3>📋 Daftar Jadwal Khusus</h3>
        <?php if (!empty($specialSchedules)): ?>
            <table class="settings-table">
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
                                <form data-action="delete_special_schedule" style="display: inline;">
                                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                                    <button type="submit" class="settings-btn settings-btn-danger settings-btn-small">🗑️ Hapus</button>
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
<div id="settings-holidays" class="settings-tab-pane">
    <div class="settings-card">
        <h3>➕ Tambah Hari Libur Baru</h3>
        <form data-action="add_holiday">
            <div class="settings-form-row">
                <div class="settings-form-group">
                    <label>Tanggal:</label>
                    <input type="date" name="holiday_date" required>
                </div>
                <div class="settings-form-group">
                    <label>Nama Hari Libur:</label>
                    <input type="text" name="holiday_name" placeholder="Contoh: Hari Raya Idul Fitri" required>
                </div>
            </div>
            <div class="settings-form-row">
                <div class="settings-form-group">
                    <label>Tipe:</label>
                    <select name="holiday_type" required>
                        <option value="national">🇮🇩 Nasional</option>
                        <option value="religious">🕌 Keagamaan</option>
                    </select>
                </div>
                <div class="settings-form-group">
                    <label>Deskripsi (Opsional):</label>
                    <input type="text" name="holiday_description" placeholder="Keterangan tambahan">
                </div>
            </div>
            <div class="settings-form-row">
                <div class="settings-form-group" style="flex: 1;">
                    <label>Divisi terdampak:</label>
                    <select name="holiday_divisions[]" multiple style="min-height: 100px;">
                        <option value="all">👥 Semua Divisi</option>
                        <?php if (!empty($config['division_schedules'])): ?>
                            <?php foreach ($config['division_schedules'] as $divKey => $div): ?>
                                <option value="<?php echo htmlspecialchars($divKey); ?>"><?php echo htmlspecialchars($div['name'] ?? $divKey); ?> (<?php echo htmlspecialchars($divKey); ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small style="color: #666;">Kosongkan atau pilih "Semua Divisi" = libur untuk semua. Pilih satu/beberapa divisi = libur hanya untuk divisi tersebut.</small>
                </div>
            </div>
            <button type="submit" class="settings-btn">➕ Tambah Hari Libur</button>
        </form>
    </div>
    
    <div class="settings-card">
        <h3>📋 Daftar Semua Hari Libur</h3>
        <?php if (!empty($allHolidays)): ?>
            <table class="settings-table">
                <thead>
                    <tr>
                        <th style="width: 120px;">Tanggal</th>
                        <th style="width: 80px;">Hari</th>
                        <th>Nama Hari Libur</th>
                        <th style="width: 120px;">Tipe</th>
                        <th style="width: 180px;">Divisi Terdampak</th>
                        <th>Deskripsi</th>
                        <th style="width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="holidays-table-body">
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
                            <td>
                                <?php
                                $divs = $holiday['divisions'] ?? [];
                                if (empty($divs)) {
                                    echo 'Semua divisi';
                                } else {
                                    $names = [];
                                    foreach ($divs as $dk) {
                                        $names[] = isset($config['division_schedules'][$dk]) ? ($config['division_schedules'][$dk]['name'] ?? $dk) : $dk;
                                    }
                                    echo htmlspecialchars(implode(', ', $names));
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($holiday['description'] ?: '-'); ?></td>
                            <td>
                                <form data-action="delete_holiday" style="display: inline;">
                                    <input type="hidden" name="year" value="<?php echo $holiday['year']; ?>">
                                    <input type="hidden" name="index" value="<?php echo $holiday['index']; ?>">
                                    <button type="submit" class="settings-btn settings-btn-danger settings-btn-small">🗑️ Hapus</button>
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
<div id="settings-penalties" class="settings-tab-pane">
    <form id="penaltiesForm">
        <div class="settings-card">
            <h3>⏰ Kebijakan Keterlambatan</h3>
            <div class="settings-form-group">
                <label>Masa Tenggang (menit):</label>
                <input type="number" name="late_grace_period" value="<?php echo $config['late_policy']['grace_period_minutes']; ?>" required>
                <small style="color: #666; display: block; margin-top: 5px;">Karyawan tidak dikenakan denda jika terlambat dalam masa tenggang ini.</small>
            </div>
            <div style="background: #f8f9fa; border-radius: 6px; padding: 15px; margin-top: 15px;">
                <strong style="display: block; margin-bottom: 15px;">Tingkat Denda Keterlambatan:</strong>
                <?php foreach ($config['late_policy']['levels'] as $index => $level): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 10px; background: white; border-radius: 4px;">
                        <div style="flex: 1;">
                            <strong><?php echo $level['name']; ?></strong><br>
                            <small style="color: #666;"><?php echo $level['min_minutes']; ?>-<?php echo $level['max_minutes']; ?> menit</small>
                        </div>
                        <div>
                            <input type="number" name="late_penalty_<?php echo $index; ?>" value="<?php echo $level['penalty_amount']; ?>" style="width: 150px; padding: 8px; border: 2px solid #e0e0e0; border-radius: 4px;" required>
                            <span style="margin-left: 5px; color: #666;">Rp</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="settings-card">
            <h3>🏃 Kebijakan Pulang Awal</h3>
            <div class="settings-form-group">
                <label>Masa Tenggang (menit):</label>
                <input type="number" name="early_grace_period" value="<?php echo $config['early_departure_policy']['grace_period_minutes']; ?>" required>
                <small style="color: #666; display: block; margin-top: 5px;">Karyawan tidak dikenakan denda jika pulang lebih awal dalam masa tenggang ini.</small>
            </div>
            <div style="background: #f8f9fa; border-radius: 6px; padding: 15px; margin-top: 15px;">
                <strong style="display: block; margin-bottom: 15px;">Tingkat Denda Pulang Awal:</strong>
                <?php foreach ($config['early_departure_policy']['levels'] as $index => $level): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 10px; background: white; border-radius: 4px;">
                        <div style="flex: 1;">
                            <strong><?php echo $level['name']; ?></strong><br>
                            <small style="color: #666;"><?php echo $level['min_minutes']; ?>-<?php echo $level['max_minutes']; ?> menit sebelum jam pulang</small>
                        </div>
                        <div>
                            <input type="number" name="early_penalty_<?php echo $index; ?>" value="<?php echo $level['penalty_amount']; ?>" style="width: 150px; padding: 8px; border: 2px solid #e0e0e0; border-radius: 4px;" required>
                            <span style="margin-left: 5px; color: #666;">Rp</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="settings-card">
            <h3>❌ Kebijakan Absen</h3>
            <div class="settings-form-row">
                <div class="settings-form-group">
                    <label>Denda Absen Sehari Penuh (Rp):</label>
                    <input type="number" name="full_day_penalty" value="<?php echo $config['absence_policy']['full_day_penalty']; ?>" required>
                </div>
                <div class="settings-form-group">
                    <label>Denda Absen Setengah Hari (Rp):</label>
                    <input type="number" name="half_day_penalty" value="<?php echo $config['absence_policy']['half_day_penalty']; ?>" required>
                </div>
            </div>
        </div>
        
        <button type="submit" class="settings-btn">💾 Simpan Semua Kebijakan</button>
    </form>
</div>

