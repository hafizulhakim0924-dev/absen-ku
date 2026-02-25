<?php
// settings_content.php - Include this in index.php for settings tab
// This file contains the HTML content for settings tab

if (!isset($config) || !$config) {
    echo '<div class="alert alert-error">Error: Config tidak ditemukan</div>';
    return;
}

// Prepare data
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

// Load special schedules from separate file
$specialSchedulesFile = __DIR__ . '/datajadwalkhusus.json';
$specialSchedules = [];
if (file_exists($specialSchedulesFile)) {
    $content = file_get_contents($specialSchedulesFile);
    $specialSchedules = json_decode($content, true) ?: [];
} else {
    // Create empty file if not exists
    file_put_contents($specialSchedulesFile, '[]');
}
if (is_array($specialSchedules) && !empty($specialSchedules)) {
    usort($specialSchedules, function($a, $b) {
        $dateA = isset($a['start_date']) ? strtotime($a['start_date']) : 0;
        $dateB = isset($b['start_date']) ? strtotime($b['start_date']) : 0;
        return $dateA - $dateB;
    });
}
?>

<style>
:root { --primary: #1976D2; --primary-hover: #1565C0; --bg: #f0f4f8; --card: #fff; --border: #e2e8f0; --text: #1e293b; --text-muted: #64748b; }
.settings-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 12px;
    background: var(--bg);
    min-height: calc(100vh - 45px);
    position: relative;
    z-index: 1;
}
.settings-header {
    background: var(--card);
    padding: 12px 16px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    text-align: center;
    margin-bottom: 12px;
    border: 1px solid var(--border);
}
.settings-header h1 { color: var(--text); font-size: 15px; margin-bottom: 4px; }
.settings-header p { color: var(--text-muted); font-size: 12px; }
.settings-tabs-container {
    background: var(--card);
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    overflow: hidden;
    border: 1px solid var(--border);
}
.settings-tabs {
    display: flex;
    background: #f8fafc;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
}
.settings-tab {
    flex: 1;
    min-width: 120px;
    padding: 8px 14px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
    text-align: center;
    transition: all 0.2s;
}
.settings-tab:hover { background: #f1f5f9; color: var(--text); }
.settings-tab.active {
    background: var(--card);
    color: var(--primary);
    border-bottom: 2px solid var(--primary);
}
.settings-tab-content { padding: 14px; }
.settings-tab-pane { display: none; }
.settings-tab-pane.active { display: block; animation: fadeIn 0.2s; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.settings-form-group { margin-bottom: 12px; }
.settings-form-group label {
    display: block;
    margin-bottom: 4px;
    font-weight: 600;
    color: var(--text);
    font-size: 12px;
}
.settings-form-group input,
.settings-form-group select,
.settings-form-group textarea {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid var(--border);
    border-radius: 4px;
    font-size: 12px;
    transition: border-color 0.2s;
}
.settings-form-group input:focus,
.settings-form-group select:focus,
.settings-form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
}
.settings-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.settings-btn {
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
.settings-btn:hover { background: var(--primary-hover); }
.settings-btn-danger { background: #dc2626; }
.settings-btn-danger:hover { background: #b91c1c; }
.settings-btn-small { padding: 4px 10px; font-size: 11px; }
.settings-card {
    background: #f8fafc;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 12px;
    border: 1px solid var(--border);
}
.settings-card h3 { color: var(--text); margin-bottom: 10px; font-size: 13px; }
.settings-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    background: var(--card);
    border-radius: 4px;
    overflow: hidden;
    font-size: 11px;
}
.settings-table th {
    background: var(--primary);
    color: white;
    padding: 8px 10px;
    text-align: left;
    font-weight: 600;
    font-size: 11px;
}
.settings-table td { padding: 8px 10px; border-bottom: 1px solid var(--border); }
.settings-table tr:hover { background: #f8fafc; }
.settings-table tr:last-child td { border-bottom: none; }
.settings-table input[type="time"],
.settings-table input[type="text"],
.settings-table select {
    width: 100%;
    padding: 5px;
    border: 1px solid var(--border);
    border-radius: 4px;
    font-size: 11px;
}
.settings-table input:focus { outline: none; border-color: var(--primary); }
.settings-alert {
    padding: 10px 12px;
    margin-bottom: 12px;
    border-radius: 4px;
    font-weight: 500;
    font-size: 12px;
    animation: slideDown 0.2s;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
.settings-alert.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.settings-alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.settings-friday-schedule {
    background: #eff6ff;
    padding: 6px;
    border-radius: 4px;
    margin-top: 4px;
    border: 1px solid var(--primary);
}
.settings-friday-schedule label { font-size: 11px; color: var(--primary); font-weight: 600; }
.holiday-type {
    display: inline-block;
    padding: 3px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
}
.holiday-type.national { background: #dbeafe; color: var(--primary); }
.holiday-type.religious { background: #ffedd5; color: #c2410c; }
.table-empty { text-align: center; padding: 24px; color: var(--text-muted); font-size: 12px; }
</style>

<div class="settings-container">
    <div class="settings-header">
        <h1>⚙️ Pengaturan Sistem Absensi</h1>
        <p>Kelola konfigurasi jadwal kerja, hari libur, dan kebijakan absensi</p>
    </div>
    
    <div id="settingsAlertMessage"></div>
    
    <div class="settings-tabs-container">
        <div class="settings-tabs">
            <button class="settings-tab active" onclick="showSettingsTab('schedules', this)">📅 Jadwal Kerja</button>
            <button class="settings-tab" onclick="showSettingsTab('holidays', this)">🎉 Hari Libur</button>
            <button class="settings-tab" onclick="showSettingsTab('penalties', this)">💰 Kebijakan Denda</button>
        </div>
        
        <div class="settings-tab-content">
            <?php include __DIR__ . '/settings_full_content.php'; ?>
        </div>
    </div>
</div>

<script>
function showSettingsTab(tabName, clickedElement) {
    // Hide all tab panes
    document.querySelectorAll('.settings-tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab pane
    const tabPane = document.getElementById('settings-' + tabName);
    if (tabPane) {
        tabPane.classList.add('active');
    }
    
    // Add active class to clicked tab
    if (clickedElement) {
        clickedElement.classList.add('active');
    } else if (window.event && window.event.target) {
        window.event.target.classList.add('active');
    } else {
        // Find tab by text content or other method
        const tabs = document.querySelectorAll('.settings-tab');
        tabs.forEach(tab => {
            if (tab.textContent.includes(tabName === 'schedules' ? 'Jadwal Kerja' : 
                                         tabName === 'holidays' ? 'Hari Libur' : 
                                         'Kebijakan Denda')) {
                tab.classList.add('active');
            }
        });
    }
}

function showSettingsAlert(message, type) {
    const alertDiv = document.getElementById('settingsAlertMessage');
    alertDiv.className = 'settings-alert ' + type;
    alertDiv.textContent = message;
    alertDiv.style.display = 'block';
    
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        alertDiv.style.transition = 'opacity 0.5s';
        setTimeout(() => {
            alertDiv.style.display = 'none';
            alertDiv.style.opacity = '1';
        }, 500);
    }, 5000);
}

// Handle form submissions with AJAX
document.addEventListener('DOMContentLoaded', function() {
    // Schedule form
    const scheduleForm = document.getElementById('scheduleForm');
    if (scheduleForm) {
        scheduleForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'settings_action');
            formData.append('settings_action', 'update_schedule');
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                showSettingsAlert(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    setTimeout(() => location.reload(), 1500);
                }
            } catch (error) {
                showSettingsAlert('Error: ' + error.message, 'error');
            }
        });
    }
    
    // Holiday forms
    document.querySelectorAll('form[data-action="add_holiday"]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'settings_action');
            formData.append('settings_action', 'add_holiday');
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                showSettingsAlert(result.message, result.success ? 'success' : 'error');
                if (result.success && result.config) {
                    if (typeof window.updateConfigFromSettings === 'function') window.updateConfigFromSettings(result.config);
                    if (result.newHoliday) appendRowToHolidayTable(result.newHoliday, result.config);
                    if (typeof refreshResultsFromCurrentData === 'function') refreshResultsFromCurrentData();
                }
            } catch (error) {
                showSettingsAlert('Error: ' + error.message, 'error');
            }
        });
    });
    
    // Delete holiday - event delegation + update tanpa reload
    document.addEventListener('submit', async function(e) {
        if (!e.target || e.target.getAttribute('data-action') !== 'delete_holiday') return;
        e.preventDefault();
        if (!confirm('Yakin ingin menghapus hari libur ini?')) return;
        const formData = new FormData(e.target);
        formData.append('action', 'settings_action');
        formData.append('settings_action', 'delete_holiday');
        try {
            const response = await fetch('', { method: 'POST', body: formData });
            const result = await response.json();
            showSettingsAlert(result.message, result.success ? 'success' : 'error');
            if (result.success && result.config) {
                if (typeof window.updateConfigFromSettings === 'function') window.updateConfigFromSettings(result.config);
                if (result.deletedYear != null && result.deletedIndex != null)
                    removeRowFromHolidayTable(result.deletedYear, result.deletedIndex);
                if (typeof refreshResultsFromCurrentData === 'function') refreshResultsFromCurrentData();
            }
        } catch (err) {
            showSettingsAlert('Error: ' + err.message, 'error');
        }
    });
    
    function appendRowToHolidayTable(h, cfg) {
        const tbody = document.getElementById('holidays-table-body');
        if (!tbody) return;
        const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const d = new Date(h.date + 'T00:00:00');
        const dayName = dayNames[d.getDay()];
        let divText = 'Semua divisi';
        if (h.divisions && h.divisions.length) {
            const names = (h.divisions || []).map(function(dk) {
                return (cfg && cfg.division_schedules && cfg.division_schedules[dk]) ? (cfg.division_schedules[dk].name || dk) : dk;
            });
            divText = names.join(', ');
        }
        const typeClass = h.type === 'national' ? 'national' : 'religious';
        const typeLabel = h.type === 'national' ? '🇮🇩 Nasional' : '🕌 Keagamaan';
        const dateFormatted = h.date.split('-').reverse().join('/');
        const tr = document.createElement('tr');
        tr.innerHTML = '<td><strong>' + dateFormatted + '</strong></td><td>' + dayName + '</td><td>' + (h.name || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</td><td><span class="holiday-type ' + typeClass + '">' + typeLabel + '</span></td><td>' + (divText || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</td><td>' + (h.description || '-').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</td><td><form data-action="delete_holiday" style="display: inline;"><input type="hidden" name="year" value="' + h.year + '"><input type="hidden" name="index" value="' + h.index + '"><button type="submit" class="settings-btn settings-btn-danger settings-btn-small">🗑️ Hapus</button></form></td>';
        tbody.appendChild(tr);
    }
    function removeRowFromHolidayTable(year, index) {
        const tbody = document.getElementById('holidays-table-body');
        if (!tbody) return;
        const rows = tbody.querySelectorAll('tr');
        for (var i = 0; i < rows.length; i++) {
            var y = rows[i].querySelector('input[name="year"]');
            var idx = rows[i].querySelector('input[name="index"]');
            if (y && y.value === String(year) && idx && idx.value === String(index)) {
                rows[i].remove();
                return;
            }
        }
    }
    
    // Penalties form
    const penaltiesForm = document.getElementById('penaltiesForm');
    if (penaltiesForm) {
        penaltiesForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'settings_action');
            formData.append('settings_action', 'update_penalties');
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                showSettingsAlert(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    setTimeout(() => location.reload(), 1500);
                }
            } catch (error) {
                showSettingsAlert('Error: ' + error.message, 'error');
            }
        });
    }
    
    // Special schedule forms - use event delegation for dynamically added forms
    document.addEventListener('submit', async function(e) {
        const form = e.target;
        if (form && form.hasAttribute('data-action')) {
            const action = form.getAttribute('data-action');
            
            if (action === 'add_special_schedule') {
                e.preventDefault();
                const formData = new FormData(form);
                formData.append('action', 'settings_action');
                formData.append('settings_action', 'add_special_schedule');
                
                try {
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    showSettingsAlert(result.message, result.success ? 'success' : 'error');
                    if (result.success) {
                        // Reset form
                        form.reset();
                        // Reload page after short delay to show updated list, stay on settings tab
                        setTimeout(() => {
                            window.location.href = window.location.pathname + '?tab=settings';
                        }, 1000);
                    }
                } catch (error) {
                    showSettingsAlert('Error: ' + error.message, 'error');
                }
            } else if (action === 'delete_special_schedule') {
                e.preventDefault();
                if (!confirm('Yakin ingin menghapus jadwal khusus ini?')) return;
                
                const formData = new FormData(form);
                formData.append('action', 'settings_action');
                formData.append('settings_action', 'delete_special_schedule');
                
                try {
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    showSettingsAlert(result.message, result.success ? 'success' : 'error');
                    if (result.success) {
                        // Reload page after short delay to show updated list, stay on settings tab
                        setTimeout(() => {
                            window.location.href = window.location.pathname + '?tab=settings';
                        }, 1000);
                    }
                } catch (error) {
                    showSettingsAlert('Error: ' + error.message, 'error');
                }
            }
        }
    });
});
</script>

