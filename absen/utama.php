<?php
// Load configuration
require_once __DIR__ . '/app_config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Karyawan - YPI Khairaummah</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            height: 100vh;
            background: #f5f5f5;
        }

        .container {
            display: flex;
            height: 100vh;
            position: relative;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            position: fixed;
            left: -280px;
            top: 0;
            height: 100vh;
            transition: left 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.3);
        }

        .sidebar.open {
            left: 0;
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            color: #bdc3c7;
        }

        .menu-list {
            list-style: none;
            padding: 20px 0;
        }

        .menu-item {
            padding: 0;
            margin: 5px 10px;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.1);
        }

        .menu-item.active {
            background: linear-gradient(90deg, #3498db 0%, #2980b9 100%);
            box-shadow: 0 2px 8px rgba(52,152,219,0.4);
        }

        .menu-item a .icon {
            font-size: 24px;
            margin-right: 15px;
            width: 30px;
            text-align: center;
        }

        .menu-item a .text {
            flex: 1;
        }

        .menu-item a .label {
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
        }

        .menu-item a .desc {
            font-size: 11px;
            color: rgba(255,255,255,0.7);
        }

        /* Toggle Button */
        .toggle-btn {
            position: fixed;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(90deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            padding: 15px 8px;
            cursor: pointer;
            z-index: 999;
            border-radius: 0 8px 8px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.3);
            transition: all 0.3s;
            font-size: 20px;
            line-height: 1;
        }

        .toggle-btn:hover {
            background: linear-gradient(90deg, #2980b9 0%, #2471a3 100%);
            padding-right: 12px;
        }

        .toggle-btn.sidebar-open {
            left: 280px;
        }

        /* Content Area */
        .content {
            flex: 1;
            transition: margin-left 0.3s ease;
            height: 100vh;
            position: relative;
        }

        .content.sidebar-open {
            margin-left: 280px;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: white;
        }

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 998;
        }

        .overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Loading */
        .loading-screen {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 100;
            transition: opacity 0.3s;
        }

        .loading-screen.hide {
            opacity: 0;
            pointer-events: none;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #ecf0f1;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Top Bar (visible on mobile) */
        .top-bar {
            display: none;
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .top-bar h1 {
            font-size: 16px;
        }

        .mobile-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .top-bar {
                display: flex;
            }

            .toggle-btn {
                display: none;
            }

            .sidebar {
                width: 250px;
                left: -250px;
            }

            .sidebar.open {
                left: 0;
            }

            .toggle-btn.sidebar-open {
                left: 250px;
            }

            .content.sidebar-open {
                margin-left: 0;
            }
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            background: #e74c3c;
            color: white;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 10px;
        }

        .badge.new {
            background: #27ae60;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <h1>📋 Sistem Absensi</h1>
        <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>
    </div>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>📋 Sistem Absensi</h2>
                <p>YPI Khairaummah School</p>
            </div>
            <ul class="menu-list">
                <li class="menu-item active" id="menu-index">
                    <a onclick="loadPage('index')">
                        <span class="icon">🏠</span>
                        <span class="text">
                            <span class="label">Halaman Utama</span>
                            <span class="desc">Proses & Upload Data Absensi</span>
                        </span>
                    </a>
                </li>
                <li class="menu-item" id="menu-edit">
                    <a onclick="loadPage('edit')">
                        <span class="icon">✏️</span>
                        <span class="text">
                            <span class="label">Edit Data</span>
                            <span class="desc">Kelola & Edit Data Tersimpan</span>
                        </span>
                    </a>
                </li>
                <li class="menu-item" id="menu-settings">
                    <a onclick="loadPage('settings')">
                        <span class="icon">⚙️</span>
                        <span class="text">
                            <span class="label">Pengaturan</span>
                            <span class="desc">Konfigurasi Sistem & Kebijakan</span>
                        </span>
                    </a>
                </li>
                <li class="menu-item" id="menu-izin">
                    <a onclick="loadPage('izin')">
                        <span class="icon">📝</span>
                        <span class="text">
                            <span class="label">Izin & Cuti</span>
                            <span class="desc">Kelola Data Izin Karyawan</span>
                        </span>
                    </a>
                </li>
                <li class="menu-item" id="menu-work_schedule">
                    <a onclick="loadPage('work_schedule')">
                        <span class="icon">📅</span>
                        <span class="text">
                            <span class="label">Jadwal Kerja</span>
                            <span class="desc">Atur Jadwal Kerja Divisi</span>
                        </span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Toggle Button -->
        <button class="toggle-btn" id="toggleBtn" onclick="toggleSidebar()">
            ☰
        </button>

        <!-- Overlay -->
        <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

        <!-- Content Area -->
        <div class="content" id="content">
            <div class="loading-screen" id="loadingScreen">
                <div class="spinner"></div>
                <div class="loading-text">Memuat halaman...</div>
            </div>
            <iframe id="contentFrame" src="<?php 
                $page = isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'index';
                $pageMap = ['index' => 'index.php', 'edit' => 'edit.php', 'settings' => 'settings.php', 'izin' => 'izin.php', 'work_schedule' => 'work_schedule.php'];
                echo isset($pageMap[$page]) ? $pageMap[$page] : 'index.php';
            ?>"></iframe>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const content = document.getElementById('content');
        const overlay = document.getElementById('overlay');
        const contentFrame = document.getElementById('contentFrame');
        const loadingScreen = document.getElementById('loadingScreen');

        let sidebarOpen = false;
        let currentPage = 'index';

        // Toggle Sidebar
        function toggleSidebar() {
            sidebarOpen = !sidebarOpen;
            
            if (sidebarOpen) {
                sidebar.classList.add('open');
                toggleBtn.classList.add('sidebar-open');
                content.classList.add('sidebar-open');
                overlay.classList.add('show');
                toggleBtn.innerHTML = '✕';
            } else {
                closeSidebar();
            }
        }

        function closeSidebar() {
            sidebarOpen = false;
            sidebar.classList.remove('open');
            toggleBtn.classList.remove('sidebar-open');
            content.classList.remove('sidebar-open');
            overlay.classList.remove('show');
            toggleBtn.innerHTML = '☰';
        }

        // Konfigurasi URL Base (sesuaikan dengan environment Anda)
        const BASE_URL = window.location.origin + window.location.pathname.replace('utama.php', '');
        
        // Mapping halaman ke file
        const pageMap = {
            'index': 'index.php',
            'edit': 'edit.php',
            'settings': 'settings.php',
            'izin': 'izin.php',
            'work_schedule': 'work_schedule.php'
        };

        // Load Page
        function loadPage(page) {
            if (page === currentPage) {
                closeSidebar();
                return;
            }

            currentPage = page;

            // Update active menu
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            const menuItem = document.getElementById('menu-' + page);
            if (menuItem) {
                menuItem.classList.add('active');
            }

            // Show loading
            loadingScreen.classList.remove('hide');

            // Load iframe
            const filename = pageMap[page] || 'index.php';
            const url = BASE_URL + filename;
            contentFrame.src = url;

            // Update URL tanpa reload
            const newUrl = window.location.pathname + '?page=' + page;
            window.history.pushState({page: page}, '', newUrl);

            // Close sidebar on mobile
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        }
        
        // Handle browser back/forward buttons
        window.addEventListener('popstate', function(event) {
            const page = new URLSearchParams(window.location.search).get('page') || 'index';
            if (page !== currentPage) {
                currentPage = page;
                loadPage(page);
            }
        });
        
        // Initialize page from URL
        const urlParams = new URLSearchParams(window.location.search);
        const initialPage = urlParams.get('page') || 'index';
        if (initialPage !== 'index') {
            setTimeout(() => loadPage(initialPage), 100);
        }

        // Hide loading when iframe loads
        contentFrame.addEventListener('load', function() {
            setTimeout(() => {
                loadingScreen.classList.add('hide');
            }, 300);
        });

        // Close sidebar when clicking outside on mobile
        overlay.addEventListener('click', closeSidebar);

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && sidebarOpen) {
                // Don't auto-close on desktop
            } else if (window.innerWidth <= 768) {
                // Auto-close on mobile when resizing
                closeSidebar();
            }
        });

        // Keyboard shortcut
        document.addEventListener('keydown', function(e) {
            // Alt + M to toggle menu
            if (e.altKey && e.key === 'm') {
                e.preventDefault();
                toggleSidebar();
            }
            // Alt + 1 for Index
            if (e.altKey && e.key === '1') {
                e.preventDefault();
                loadPage('index');
            }
            // Alt + 2 for Edit
            if (e.altKey && e.key === '2') {
                e.preventDefault();
                loadPage('edit');
            }
            // Alt + 3 for Settings
            if (e.altKey && e.key === '3') {
                e.preventDefault();
                loadPage('settings');
            }
            // Alt + 4 for Izin
            if (e.altKey && e.key === '4') {
                e.preventDefault();
                loadPage('izin');
            }
            // Alt + 5 for Work Schedule
            if (e.altKey && e.key === '5') {
                e.preventDefault();
                loadPage('work_schedule');
            }
            // ESC to close sidebar
            if (e.key === 'Escape' && sidebarOpen) {
                closeSidebar();
            }
        });

        // Initial load
        window.addEventListener('load', function() {
            setTimeout(() => {
                loadingScreen.classList.add('hide');
            }, 500);
        });

        // Prevent double-click text selection on menu
        document.querySelectorAll('.menu-item a').forEach(item => {
            item.addEventListener('mousedown', function(e) {
                e.preventDefault();
            });
        });
    </script>
</body>
</html>