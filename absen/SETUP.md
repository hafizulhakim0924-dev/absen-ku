# Panduan Setup Konfigurasi Sistem Absensi

## ✅ File Konfigurasi yang Telah Dibuat

### 1. **db_config.php** 
File konfigurasi database dengan fungsi helper untuk koneksi.
- **Lokasi**: `absen/db_config.php`
- **Fungsi**: Mengatur koneksi database MySQL/MariaDB
- **Konfigurasi yang perlu disesuaikan**:
  ```php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'ypikhair_admin');
  define('DB_PASS', 'hakim123123123');
  define('DB_NAME', 'ypikhair_datautama');
  ```

### 2. **app_config.php**
File konfigurasi aplikasi utama.
- **Lokasi**: `absen/app_config.php`
- **Fungsi**: Mengatur path, URL, dan pengaturan aplikasi
- **Konfigurasi yang perlu disesuaikan**:
  ```php
  define('BASE_URL', 'https://ypi-khairaummah.sch.id/absen');
  // atau untuk development:
  // define('BASE_URL', 'http://localhost/absen-ku/absen');
  ```

### 3. **init.php**
File helper untuk inisialisasi di setiap halaman.
- **Lokasi**: `absen/init.php`
- **Fungsi**: Memuat semua konfigurasi dengan satu include
- **Usage**: `require_once __DIR__ . '/init.php';`

### 4. **config.example.php**
Template konfigurasi sebagai referensi.
- **Lokasi**: `absen/config.example.php`
- **Fungsi**: Template untuk setup awal

## 📋 Menu yang Telah Dikonfigurasi

File `utama.php` telah diperbarui dengan menu lengkap:

1. **🏠 Halaman Utama** (`index.php`)
   - Proses & Upload Data Absensi
   - Shortcut: `Alt + 1`

2. **✏️ Edit Data** (`edit.php`)
   - Kelola & Edit Data Tersimpan
   - Shortcut: `Alt + 2`

3. **⚙️ Pengaturan** (`settings.php`)
   - Konfigurasi Sistem & Kebijakan
   - Shortcut: `Alt + 3`

4. **📝 Izin & Cuti** (`izin.php`)
   - Kelola Data Izin Karyawan
   - Shortcut: `Alt + 4`

5. **📅 Jadwal Kerja** (`work_schedule.php`)
   - Atur Jadwal Kerja Divisi
   - Shortcut: `Alt + 5`

## 🔧 Langkah Setup

### Step 1: Konfigurasi Database
1. Buka file `absen/db_config.php`
2. Sesuaikan kredensial database:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'username_anda');
   define('DB_PASS', 'password_anda');
   define('DB_NAME', 'nama_database');
   ```

### Step 2: Konfigurasi URL
1. Buka file `absen/app_config.php`
2. Sesuaikan `BASE_URL` sesuai environment:
   - **Development**: `http://localhost/absen-ku/absen`
   - **Production**: `https://ypi-khairaummah.sch.id/absen`

### Step 3: Pastikan Folder Upload
1. Pastikan folder `absen/upload/` ada
2. Set permission folder ke **755** atau **777**
3. Folder akan dibuat otomatis jika tidak ada (melalui `app_config.php`)

### Step 4: Konfigurasi Bisnis
1. Buka aplikasi melalui `utama.php`
2. Klik menu **⚙️ Pengaturan**
3. Konfigurasi:
   - Jadwal kerja per divisi
   - Hari libur
   - Kebijakan denda

## 🎯 Fitur Konfigurasi

### ✅ Yang Sudah Terkoneksi
- ✅ Menu navigasi lengkap dengan 5 halaman
- ✅ URL routing otomatis
- ✅ Keyboard shortcuts untuk semua menu
- ✅ Responsive design (mobile & desktop)
- ✅ Loading indicator
- ✅ Browser history support (back/forward button)

### 🔗 Koneksi Antar Halaman
Semua halaman dapat diakses melalui:
- Menu sidebar (klik menu item)
- Keyboard shortcuts (`Alt + 1-5`)
- URL parameter (`?page=index`, `?page=settings`, dll)

## 📝 Catatan Penting

1. **Security**: 
   - Jangan commit `db_config.php` ke repository publik
   - Set `display_errors = 0` di production
   - Gunakan HTTPS di production

2. **File Permission**:
   - Folder `upload/`: 755 atau 777
   - File `config.json`: 644
   - File PHP: 644

3. **Database**:
   - Pastikan user database memiliki permission yang cukup
   - Backup database secara berkala

## 🐛 Troubleshooting

### Menu tidak muncul
- Cek console browser (F12) untuk error JavaScript
- Pastikan semua file PHP ada di folder yang benar

### Halaman tidak load di iframe
- Cek `BASE_URL` di `app_config.php`
- Pastikan URL sesuai dengan domain aktual
- Cek CORS jika menggunakan domain berbeda

### Database connection error
- Verifikasi kredensial di `db_config.php`
- Pastikan MySQL/MariaDB service berjalan
- Cek permission user database

## 📚 Dokumentasi Lengkap

Lihat `README_CONFIG.md` untuk dokumentasi lengkap tentang konfigurasi.

## ✨ Status Konfigurasi

✅ Database configuration ready  
✅ Application configuration ready  
✅ Menu navigation configured  
✅ URL routing configured  
✅ Helper functions ready  
✅ Documentation complete  

**Sistem siap digunakan!** 🎉

