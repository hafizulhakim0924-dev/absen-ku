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
.settings-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: calc(100vh - 60px);
    position: relative;
    z-index: 1;
}

.settings-header {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    text-align: center;
    margin-bottom: 20px;
}

.settings-header h1 {
    color: #333;
    font-size: 2em;
    margin-bottom: 5px;
}

.settings-header p {
    color: #666;
}

.settings-tabs-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.settings-tabs {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    flex-wrap: wrap;
}

.settings-tab {
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

.settings-tab:hover {
    background: #e9ecef;
}

.settings-tab.active {
    background: white;
    color: #667eea;
    border-bottom: 3px solid #667eea;
}

.settings-tab-content {
    padding: 30px;
}

.settings-tab-pane {
    display: none;
}

.settings-tab-pane.active {
    display: block;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.settings-form-group {
    margin-bottom: 20px;
}

.settings-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.settings-form-group input,
.settings-form-group select,
.settings-form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.settings-form-group input:focus,
.settings-form-group select:focus,
.settings-form-group textarea:focus {
    outline: none;
    border-color: #667eea;
}

.settings-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.settings-btn {
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

.settings-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.settings-btn-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
}

.settings-btn-small {
    padding: 6px 12px;
    font-size: 12px;
}

.settings-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.settings-card h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 1.2em;
}

.settings-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.settings-table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
}

.settings-table td {
    padding: 12px;
    border-bottom: 1px solid #e0e0e0;
}

.settings-table tr:hover {
    background: #f8f9fa;
}

.settings-table tr:last-child td {
    border-bottom: none;
}

.settings-table input[type="time"],
.settings-table input[type="text"],
.settings-table select {
    width: 100%;
    padding: 8px;
    border: 2px solid #e0e0e0;
    border-radius: 4px;
    font-size: 14px;
}

.settings-table input:focus {
    outline: none;
    border-color: #667eea;
}

.settings-alert {
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

.settings-alert.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.settings-alert.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.settings-friday-schedule {
    background: #fff3cd;
    padding: 8px;
    border-radius: 4px;
    margin-top: 5px;
}

.settings-friday-schedule label {
    font-size: 12px;
    color: #856404;
    font-weight: 600;
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
                if (result.success) {
                    setTimeout(() => location.reload(), 1500);
                }
            } catch (error) {
                showSettingsAlert('Error: ' + error.message, 'error');
            }
        });
    });
    
    // Delete holiday forms
    document.querySelectorAll('form[data-action="delete_holiday"]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!confirm('Yakin ingin menghapus hari libur ini?')) return;
            
            const formData = new FormData(this);
            formData.append('action', 'settings_action');
            formData.append('settings_action', 'delete_holiday');
            
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
    });
    
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

