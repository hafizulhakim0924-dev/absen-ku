<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewer Data Absensi JSON - 2025</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .upload-section {
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
        }
        .upload-box {
            border: 3px dashed #667eea;
            border-radius: 15px;
            padding: 60px 40px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .upload-box:hover {
            border-color: #764ba2;
            background: #f8f9fa;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }
        .upload-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .upload-box h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        .upload-box p {
            color: #666;
            font-size: 14px;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 5px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .content-section {
            padding: 30px 40px;
            display: none;
        }
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .info-card h3 {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
            font-weight: 500;
        }
        .info-card .value {
            font-size: 32px;
            font-weight: 700;
        }
        .search-filter {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .search-controls {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-input, .filter-select {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            min-width: 200px;
            transition: all 0.3s ease;
        }
        .search-input:focus, .filter-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .data-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .data-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .data-table tbody tr {
            transition: all 0.2s ease;
        }
        .data-table tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.01);
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .penalty-amount {
            font-weight: 700;
            font-size: 14px;
        }
        .penalty-positive { color: #28a745; }
        .penalty-negative { color: #dc3545; }
        .detail-view {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
        }
        .day-entry {
            background: white;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .pagination button {
            padding: 8px 15px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .pagination button:hover:not(:disabled) {
            background: #667eea;
            color: white;
        }
        .pagination button.active {
            background: #667eea;
            color: white;
        }
        .pagination button:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        .export-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin: 20px 0;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            z-index: 10000;
            animation: slideIn 0.3s ease;
            max-width: 400px;
        }
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .notification.success { border-left: 5px solid #28a745; }
        .notification.error { border-left: 5px solid #dc3545; }
        .notification.info { border-left: 5px solid #17a2b8; }
        .summary-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .summary-card h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        .summary-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .summary-item label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-item .value {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
        .detail-toggle {
            background: #667eea;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 600;
            margin-top: 8px;
        }
        .detail-toggle:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Viewer Data Absensi JSON</h1>
            <p>Buka dan analisis file data absensi yang telah disimpan sebelumnya</p>
        </div>

        <div class="upload-section" id="uploadSection">
            <div class="upload-box" onclick="document.getElementById('fileInput').click()">
                <div class="upload-icon">📁</div>
                <h2>Pilih File JSON</h2>
                <p>Klik di sini atau drag & drop file JSON data absensi Anda</p>
                <input type="file" id="fileInput" accept=".json" style="display: none;">
            </div>
            <div style="margin-top: 20px;">
                <p style="color: #666; font-size: 13px;">
                    File JSON yang dapat dibuka: <strong>Absensi_[Bulan]_[Tahun]_Data.json</strong>
                </p>
            </div>
        </div>

        <div class="content-section" id="contentSection">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: #333;">Data Absensi: <span id="periodTitle"></span></h2>
                <button class="btn btn-secondary" onclick="resetView()">🔄 Buka File Lain</button>
            </div>

            <div class="info-cards">
                <div class="info-card">
                    <h3>Total Karyawan</h3>
                    <div class="value" id="totalEmployees">-</div>
                </div>
                <div class="info-card">
                    <h3>Total Record</h3>
                    <div class="value" id="totalRecords">-</div>
                </div>
                <div class="info-card">
                    <h3>File Diproses</h3>
                    <div class="value" id="filesProcessed">-</div>
                </div>
                <div class="info-card">
                    <h3>Total Denda</h3>
                    <div class="value" id="totalPenalties">-</div>
                </div>
            </div>

            <div class="export-section">
                <h3 style="margin-bottom: 15px; color: #333;">📥 Export Data</h3>
                <button class="btn btn-success" onclick="exportToExcel('summary')">📊 Export Ringkasan</button>
                <button class="btn btn-success" onclick="exportToExcel('detailed')">📋 Export Detail Lengkap</button>
                <button class="btn btn-success" onclick="exportToExcel('filtered')" id="exportFilteredBtn" style="display:none;">🔍 Export Data Terfilter</button>
            </div>

            <div class="search-filter">
                <div class="search-controls">
                    <input type="text" id="searchInput" class="search-input" placeholder="🔍 Cari ID, Nama, atau Divisi...">
                    <select id="divisionFilter" class="filter-select">
                        <option value="">Semua Divisi</option>
                    </select>
                    <select id="penaltyFilter" class="filter-select">
                        <option value="">Semua Status</option>
                        <option value="with-penalty">Ada Denda</option>
                        <option value="no-penalty">Tanpa Denda</option>
                    </select>
                    <button class="btn btn-secondary" onclick="clearFilters()">Reset Filter</button>
                </div>
                <div style="margin-top: 15px;">
                    <span id="searchResults" style="color: #666; font-weight: 600;"></span>
                </div>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="80">ID</th>
                            <th width="180">Nama</th>
                            <th width="150">Divisi</th>
                            <th width="100" style="text-align: center;">Hari Hadir</th>
                            <th width="120" style="text-align: center;">Total Denda</th>
                            <th width="100" style="text-align: center;">Status</th>
                            <th width="120" style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                    </tbody>
                </table>
            </div>

            <div class="pagination" id="pagination"></div>
        </div>
    </div>

    <script>
        let jsonData = null;
        let allEmployees = [];
        let filteredEmployees = [];
        let currentPage = 1;
        const rowsPerPage = 15;

        document.getElementById('fileInput').addEventListener('change', handleFileSelect);

        // Drag and drop support
        const uploadBox = document.querySelector('.upload-box');
        uploadBox.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadBox.style.borderColor = '#764ba2';
            uploadBox.style.background = '#f8f9fa';
        });
        uploadBox.addEventListener('dragleave', () => {
            uploadBox.style.borderColor = '#667eea';
            uploadBox.style.background = 'white';
        });
        uploadBox.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadBox.style.borderColor = '#667eea';
            uploadBox.style.background = 'white';
            const file = e.dataTransfer.files[0];
            if (file && file.type === 'application/json') {
                loadJSONFile(file);
            } else {
                showNotification('File harus berformat JSON!', 'error');
            }
        });

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                loadJSONFile(file);
            }
        }

        function loadJSONFile(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    jsonData = JSON.parse(e.target.result);
                    if (!jsonData.version || !jsonData.processedData) {
                        throw new Error('Format JSON tidak valid');
                    }
                    displayData();
                    showNotification(`✅ Data ${jsonData.monthName} ${jsonData.year} berhasil dimuat!`, 'success');
                } catch (error) {
                    showNotification('❌ Error: ' + error.message, 'error');
                }
            };
            reader.readAsText(file);
        }

        function displayData() {
            document.getElementById('uploadSection').style.display = 'none';
            document.getElementById('contentSection').style.display = 'block';

            // Set title
            document.getElementById('periodTitle').textContent = `${jsonData.monthName} ${jsonData.year}`;

            // Set statistics
            document.getElementById('totalEmployees').textContent = jsonData.stats.totalEmployees;
            document.getElementById('totalRecords').textContent = jsonData.stats.totalRecords.toLocaleString('id-ID');
            document.getElementById('filesProcessed').textContent = jsonData.stats.filesProcessed;

            // Calculate total penalties
            let totalPenalties = 0;
            allEmployees = [];
            
            Object.keys(jsonData.processedData.employees).sort((a, b) => parseInt(a) - parseInt(b)).forEach(id => {
                const emp = jsonData.processedData.employees[id];
                const empData = jsonData.originalEmployeesArray.find(e => e.id === id);
                if (empData) {
                    allEmployees.push(empData);
                    totalPenalties += empData.totalPenalty;
                }
            });

            document.getElementById('totalPenalties').textContent = formatCurrency(totalPenalties);

            // Setup filters
            setupFilters();

            // Display table
            filteredEmployees = [...allEmployees];
            displayTable();
        }

        function setupFilters() {
            const divisionFilter = document.getElementById('divisionFilter');
            const divisions = new Set();
            allEmployees.forEach(emp => divisions.add(emp.divisi));
            
            divisionFilter.innerHTML = '<option value="">Semua Divisi</option>';
            Array.from(divisions).sort().forEach(div => {
                const option = document.createElement('option');
                option.value = div;
                option.textContent = div;
                divisionFilter.appendChild(option);
            });

            // Event listeners
            document.getElementById('searchInput').addEventListener('input', applyFilters);
            document.getElementById('divisionFilter').addEventListener('change', applyFilters);
            document.getElementById('penaltyFilter').addEventListener('change', applyFilters);
        }

        function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const divisionFilter = document.getElementById('divisionFilter').value;
            const penaltyFilter = document.getElementById('penaltyFilter').value;

            filteredEmployees = allEmployees.filter(emp => {
                const matchSearch = !searchTerm || 
                    emp.id.toLowerCase().includes(searchTerm) ||
                    emp.nama.toLowerCase().includes(searchTerm) ||
                    emp.divisi.toLowerCase().includes(searchTerm);
                
                const matchDivision = !divisionFilter || emp.divisi === divisionFilter;
                
                let matchPenalty = true;
                if (penaltyFilter === 'with-penalty') {
                    matchPenalty = emp.totalPenalty > 0;
                } else if (penaltyFilter === 'no-penalty') {
                    matchPenalty = emp.totalPenalty === 0;
                }

                return matchSearch && matchDivision && matchPenalty;
            });

            currentPage = 1;
            displayTable();
            updateSearchResults();

            const exportFilteredBtn = document.getElementById('exportFilteredBtn');
            if (filteredEmployees.length < allEmployees.length) {
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
            const resultsText = filteredEmployees.length === allEmployees.length
                ? `Menampilkan semua ${allEmployees.length} karyawan`
                : `Menampilkan ${filteredEmployees.length} dari ${allEmployees.length} karyawan`;
            document.getElementById('searchResults').textContent = resultsText;
        }

        function displayTable() {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';

            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const pageData = filteredEmployees.slice(startIndex, endIndex);

            pageData.forEach(emp => {
                const tr = document.createElement('tr');
                const statusBadge = emp.totalPenalty === 0 
                    ? '<span class="badge badge-success">✓ Baik</span>'
                    : '<span class="badge badge-danger">⚠ Ada Denda</span>';
                
                const penaltyClass = emp.totalPenalty === 0 ? 'penalty-positive' : 'penalty-negative';

                tr.innerHTML = `
                    <td><strong>${emp.id}</strong></td>
                    <td>${emp.nama}</td>
                    <td>${emp.divisi}</td>
                    <td style="text-align: center;"><strong>${emp.daysPresent}</strong> hari</td>
                    <td style="text-align: center;">
                        <span class="penalty-amount ${penaltyClass}">${formatCurrency(emp.totalPenalty)}</span>
                    </td>
                    <td style="text-align: center;">${statusBadge}</td>
                    <td style="text-align: center;">
                        <button class="detail-toggle" onclick="showDetail('${emp.id}')">📋 Detail</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            updatePagination();
            updateSearchResults();
        }

        function updatePagination() {
            const totalPages = Math.ceil(filteredEmployees.length / rowsPerPage);
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.style.display = 'none';
                return;
            }

            pagination.style.display = 'flex';
            pagination.innerHTML = '';

            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.textContent = '« Prev';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => changePage(currentPage - 1);
            pagination.appendChild(prevBtn);

            // Page numbers
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, currentPage + 2);

            if (startPage > 1) {
                const btn = document.createElement('button');
                btn.textContent = '1';
                btn.onclick = () => changePage(1);
                pagination.appendChild(btn);
                if (startPage > 2) {
                    const dots = document.createElement('span');
                    dots.textContent = '...';
                    dots.style.padding = '8px';
                    pagination.appendChild(dots);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.onclick = () => changePage(i);
                if (i === currentPage) btn.classList.add('active');
                pagination.appendChild(btn);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const dots = document.createElement('span');
                    dots.textContent = '...';
                    dots.style.padding = '8px';
                    pagination.appendChild(dots);
                }
                const btn = document.createElement('button');
                btn.textContent = totalPages;
                btn.onclick = () => changePage(totalPages);
                pagination.appendChild(btn);
            }

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.textContent = 'Next »';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => changePage(currentPage + 1);
            pagination.appendChild(nextBtn);
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredEmployees.length / rowsPerPage);
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            displayTable();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function showDetail(employeeId) {
            const emp = filteredEmployees.find(e => e.id === employeeId);
            if (!emp) return;

            const modal = document.createElement('div');
            modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;';
            
            modal.innerHTML = `
                <div style="background: white; border-radius: 15px; max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 30px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h2 style="color: #667eea; margin: 0;">Detail Absensi</h2>
                        <button onclick="this.closest('div[style*=fixed]').remove()" style="background: #dc3545; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-weight: 600;">✕ Tutup</button>
                    </div>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">ID Karyawan</label>
                                <strong style="font-size: 18px; color: #333;">${emp.id}</strong>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Nama</label>
                                <strong style="font-size: 18px; color: #333;">${emp.nama}</strong>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Divisi</label>
                                <strong style="font-size: 18px; color: #333;">${emp.divisi}</strong>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Hari Hadir</label>
                                <strong style="font-size: 18px; color: #333;">${emp.daysPresent} hari</strong>
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #666; display: block; margin-bottom: 5px;">Total Denda</label>
                                <strong style="font-size: 18px; color: ${emp.totalPenalty > 0 ? '#dc3545' : '#28a745'};">${formatCurrency(emp.totalPenalty)}</strong>
                            </div>
                        </div>
                    </div>
                    <div style="background: white; border: 2px solid #e0e0e0; border-radius: 10px; padding: 20px;">
                        <h3 style="color: #333; margin-bottom: 15px;">📅 Rincian Kehadiran</h3>
                        <div style="max-height: 400px; overflow-y: auto;">
                            ${emp.attendanceDetail}
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });
        }

        function formatCurrency(amount) {
            return `Rp ${amount.toLocaleString('id-ID')}`;
        }

        function exportToExcel(type) {
            if (!jsonData) {
                showNotification('❌ Tidak ada data untuk diexport!', 'error');
                return;
            }

            const dataToExport = type === 'filtered' ? filteredEmployees : allEmployees;
            const filename = `${jsonData.monthName}_${jsonData.year}_${type}_${new Date().getTime()}.xlsx`;
            
            let worksheetData = [];
            
            if (type === 'summary' || type === 'filtered') {
                worksheetData.push(['ID', 'Nama', 'Divisi', 'Hari Hadir', 'Total Denda', 'Status']);
                
                dataToExport.forEach(emp => {
                    const status = emp.totalPenalty === 0 ? 'Baik' : 'Ada Denda';
                    worksheetData.push([
                        emp.id,
                        emp.nama,
                        emp.divisi,
                        emp.daysPresent,
                        emp.totalPenalty,
                        status
                    ]);
                });
            } else if (type === 'detailed') {
                worksheetData.push(['ID', 'Nama', 'Divisi', 'Tanggal', 'Hari', 'Jam Datang', 'Jam Pulang', 'Denda', 'Keterangan']);
                
                dataToExport.forEach(emp => {
                    const employeeData = jsonData.processedData.employees[emp.id];
                    if (employeeData && employeeData.attendanceByDate) {
                        const dates = Object.keys(employeeData.attendanceByDate).sort((a, b) => parseInt(a) - parseInt(b));
                        
                        dates.forEach(date => {
                            const attendance = employeeData.attendanceByDate[date];
                            let jamDatang = '';
                            let jamPulang = '';
                            let denda = 0;
                            let keterangan = '';
                            
                            attendance.forEach(entry => {
                                const time = entry.time;
                                const hour = parseInt(time.split(':')[0] || time.substring(0, 2));
                                
                                if (hour < 12) {
                                    jamDatang = time;
                                } else {
                                    jamPulang = time;
                                }
                            });
                            
                            const dayInfo = attendance[0]?.dayInfo;
                            const dayName = dayInfo?.dayName || '';
                            
                            worksheetData.push([
                                emp.id,
                                emp.nama,
                                emp.divisi,
                                date,
                                dayName,
                                jamDatang,
                                jamPulang,
                                denda,
                                keterangan
                            ]);
                        });
                    }
                });
            }
            
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(worksheetData);
            
            // Auto width columns
            const colWidths = worksheetData[0].map((_, i) => {
                const maxLength = Math.max(...worksheetData.map(row => 
                    row[i] ? row[i].toString().length : 0
                ));
                return { wch: Math.min(Math.max(maxLength + 2, 10), 30) };
            });
            ws['!cols'] = colWidths;
            
            XLSX.utils.book_append_sheet(wb, ws, type === 'detailed' ? 'Detail' : 'Ringkasan');
            XLSX.writeFile(wb, filename);
            
            showNotification('✅ Data berhasil diexport ke Excel!', 'success');
        }

        function resetView() {
            jsonData = null;
            allEmployees = [];
            filteredEmployees = [];
            currentPage = 1;
            
            document.getElementById('uploadSection').style.display = 'block';
            document.getElementById('contentSection').style.display = 'none';
            document.getElementById('fileInput').value = '';
            
            showNotification('📂 Silakan pilih file JSON lainnya', 'info');
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="font-size: 24px;">
                        ${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}
                    </div>
                    <div>
                        <strong style="display: block; margin-bottom: 5px;">
                            ${type === 'success' ? 'Berhasil!' : type === 'error' ? 'Error!' : 'Informasi'}
                        </strong>
                        <p style="margin: 0; font-size: 14px; color: #666;">${message}</p>
                    </div>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transition = 'opacity 0.5s, transform 0.5s';
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(400px)';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }

        // Auto load if JSON file is in URL hash
        window.addEventListener('load', () => {
            const hash = window.location.hash.substring(1);
            if (hash) {
                fetch(hash)
                    .then(response => response.json())
                    .then(data => {
                        jsonData = data;
                        displayData();
                        showNotification('✅ Data otomatis dimuat!', 'success');
                    })
                    .catch(() => {});
            }
        });
    </script>
</body>
</html>