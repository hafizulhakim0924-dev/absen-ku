# Konfigurasi Sistem Absensi

## File Konfigurasi

Sistem ini menggunakan beberapa file konfigurasi untuk mengatur koneksi database dan pengaturan aplikasi:

### 1. `db_config.php`
File ini berisi konfigurasi koneksi database. **PENTING**: Jangan commit file ini ke repository publik!

**Konfigurasi yang perlu disesuaikan:**
- `DB_HOST`: Host database (biasanya 'localhost')
- `DB_USER`: Username database
- `DB_PASS`: Password database
- `DB_NAME`: Nama database

### 2. `app_config.php`
File ini berisi konfigurasi aplikasi secara keseluruhan, termasuk:
- Base URL aplikasi
- Path direktori
- Pengaturan upload
- Konfigurasi session

**Konfigurasi yang perlu disesuaikan:**
- `BASE_URL`: URL dasar aplikasi
  - Development: `http://localhost/absen-ku/absen`
  - Production: `https://ypi-khairaummah.sch.id/absen`

### 3. `config.json`
File ini berisi konfigurasi bisnis aplikasi:
- Jadwal kerja per divisi
- Kebijakan denda
- Hari libur
- Aturan absensi

File ini dapat dikonfigurasi melalui halaman **Pengaturan** di aplikasi.

## Cara Setup

### Langkah 1: Setup Database
1. Buat database MySQL/MariaDB
2. Copy `config.example.php` dan buat `db_config.php`
3. Edit `db_config.php` dan sesuaikan kredensial database

### Langkah 2: Setup Aplikasi
1. Pastikan `app_config.php` sudah ada
2. Edit `BASE_URL` sesuai environment (development/production)
3. Pastikan folder `upload/` memiliki permission write (755 atau 777)

### Langkah 3: Setup Konfigurasi Bisnis
1. Buka aplikasi melalui `utama.php`
2. Masuk ke menu **Pengaturan**
3. Konfigurasi:
   - Jadwal kerja per divisi
   - Kebijakan denda
   - Hari libur

## Struktur Menu

Aplikasi memiliki menu berikut:

1. **Halaman Utama** (`index.php`)
   - Upload dan proses data absensi
   - Perhitungan denda otomatis

2. **Edit Data** (`edit.php`)
   - Kelola data absensi yang sudah tersimpan
   - Edit dan hapus data

3. **Pengaturan** (`settings.php`)
   - Konfigurasi jadwal kerja
   - Kelola hari libur
   - Atur kebijakan denda

4. **Izin & Cuti** (`izin.php`)
   - Kelola data izin karyawan
   - Input dan edit izin

5. **Jadwal Kerja** (`work_schedule.php`)
   - Atur jadwal kerja per divisi
   - Konfigurasi hari kerja

## Keyboard Shortcuts

- `Alt + M`: Toggle menu sidebar
- `Alt + 1`: Halaman Utama
- `Alt + 2`: Edit Data
- `Alt + 3`: Pengaturan
- `Alt + 4`: Izin & Cuti
- `Alt + 5`: Jadwal Kerja
- `ESC`: Tutup sidebar

## Troubleshooting

### Error: "Database connection failed"
- Pastikan kredensial database di `db_config.php` benar
- Pastikan database server berjalan
- Cek permission user database

### Error: "Config file not found"
- Pastikan file `config.json` ada di folder `absen/`
- Pastikan permission file dapat dibaca (644)

### Error: "Cannot write to upload directory"
- Pastikan folder `upload/` ada
- Set permission folder ke 755 atau 777
- Pastikan web server memiliki akses write

### Menu tidak muncul atau halaman tidak load
- Cek `BASE_URL` di `app_config.php` sesuai dengan URL aktual
- Pastikan semua file PHP ada di folder yang benar
- Cek console browser untuk error JavaScript

## Support

Jika mengalami masalah, periksa:
1. Error log PHP (biasanya di `error_log`)
2. Console browser (F12)
3. Log web server (Apache/Nginx)

