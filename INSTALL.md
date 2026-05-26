# 📖 PANDUAN INSTALASI BANGJO SISTEM PEMINJAMAN ALAT

Dokumentasi lengkap untuk install dan setup Bangjo di komputer Anda.

## 📋 Daftar Isi

1. [Requirement](#requirement)
2. [Step-by-Step Installation](#step-by-step-installation)
3. [Verifikasi Instalasi](#verifikasi-instalasi)
4. [Troubleshooting](#troubleshooting)
5. [Post-Installation](#post-installation)

---

## ✅ Requirement

Sebelum memulai, pastikan sudah memiliki:

### Software yang Diperlukan:
- **XAMPP** (versi terbaru) - Download dari [apachefriends.org](https://www.apachefriends.org)
- **Text Editor/IDE** - VS Code, Sublime Text, atau PhpStorm (optional)
- **Git** (optional) - Untuk clone repository

### Sistem Requirements:
- **Windows/Mac/Linux** dengan XAMPP terinstall
- **PHP** 7.4 atau lebih tinggi
- **MySQL** 5.7 atau lebih tinggi
- **Minimum RAM**: 2GB
- **Disk Space**: 100MB

---

## 🚀 Step-by-Step Installation

### **STEP 1: Prepare Directory**

1. **Buka File Explorer / Windows Explorer**
   ```
   Navigasi ke: C:\xampp\htdocs
   ```

2. **Lokasi Project**
   ```
   Path lengkap: C:\xampp\htdocs\bangjo
   ```

   > Jika folder `bangjo` belum ada, pastikan project sudah dicopy ke lokasi ini.

---

### **STEP 2: Start XAMPP Services**

1. **Buka XAMPP Control Panel**
   ```
   Lokasi: C:\xampp\xampp-control.exe
   ```

2. **Start Services**
   - Klik **"Start"** untuk **Apache**
   - Klik **"Start"** untuk **MySQL**
   
   Status harus menunjukkan **"Running"** (warna hijau)

   ```
   Expected Output:
   [Apache] 16:30:45  Apache started [PID: 1234]
   [MySQL]  16:30:47  MySQL started [PID: 1235]
   ```

3. **Verifikasi**
   - Buka browser: `http://localhost`
   - Harus menampilkan XAMPP homepage

---

### **STEP 3: Create Database**

#### **Method A: PhpMyAdmin (GUI)**

1. **Buka PhpMyAdmin**
   ```
   URL: http://localhost/phpmyadmin
   ```

2. **Buat Database Baru**
   - Klik tab **"Databases"**
   - Isi field **"Database name"** dengan: `bangjo_db`
   - Charset: `utf8mb4_unicode_ci`
   - Klik **"Create"**

3. **Import Schema**
   - Pilih database **"bangjo_db"**
   - Klik tab **"Import"**
   - Klik **"Choose File"**
   - Navigate ke: `C:\xampp\htdocs\bangjo\config\database.sql`
   - Klik **"Go"**
   
   Wait untuk process selesai (1-5 detik)

#### **Method B: Command Line (CLI)**

```bash
# Buka Command Prompt
# Navigate ke folder xampp
cd C:\xampp\mysql\bin

# Login ke MySQL
mysql -u root -p

# Jika diminta password, tekan Enter (default kosong)

# Salin-paste commands berikut:
CREATE DATABASE bangjo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bangjo_db;
SOURCE C:\xampp\htdocs\bangjo\config\database.sql;

# Tunggu hingga selesai
# Type: exit
exit
```

---

### **STEP 4: Verify Database**

1. **Kembali ke PhpMyAdmin**
   ```
   http://localhost/phpmyadmin
   ```

2. **Cek Tabel**
   - Pilih database **"bangjo_db"**
   - Seharusnya ada 7 tabel:
     - users
     - kategori
     - alat
     - peminjaman
     - detail_peminjaman
     - pengembalian
     - log_aktivitas

3. **Cek Data Default**
   - Klik tabel **"users"**
   - Harus ada 3 user (admin, petugas, peminjam)

---

### **STEP 5: Access Application**

1. **Buka Browser**
   ```
   URL: http://localhost/bangjo
   ```

2. **Expected Result**
   - Akan redirect ke halaman login: `http://localhost/bangjo/login.php`
   - Halaman login menampilkan:
     - Logo Bangjo
     - Form username & password
     - Tombol Login
     - Informasi akun demo

---

### **STEP 6: First Login**

1. **Login dengan Akun Admin**
   ```
   Username: admin
   Password: admin123
   ```

2. **Expected Behavior**
   - Redirect ke Dashboard Admin
   - Menampilkan statistik dan data
   - Session aktif

3. **Test Features**
   - Navigasi sidebar
   - Check menu items
   - Verify data loading

---

## ✅ Verifikasi Instalasi

### Checklist Verifikasi:

- [ ] XAMPP Apache berjalan (status: Running)
- [ ] XAMPP MySQL berjalan (status: Running)
- [ ] Database `bangjo_db` sudah dibuat
- [ ] 7 tabel sudah ter-import
- [ ] 3 akun default sudah ada
- [ ] Aplikasi bisa diakses di `http://localhost/bangjo`
- [ ] Login berhasil dengan akun admin
- [ ] Dashboard menampilkan data dengan baik

### Test Semua Fitur:

```
Akun Admin:
- [ ] Login & Logout
- [ ] Dashboard load dengan data
- [ ] CRUD User works
- [ ] CRUD Alat works
- [ ] CRUD Kategori works
- [ ] View Peminjaman
- [ ] View Log Aktivitas

Akun Petugas:
- [ ] Login & Logout
- [ ] Dashboard menampilkan approval requests
- [ ] Approve/Reject peminjaman
- [ ] Monitoring alat
- [ ] View laporan

Akun Peminjam:
- [ ] Login & Logout
- [ ] View katalog alat
- [ ] Ajukan peminjaman
- [ ] View riwayat peminjaman
```

---

## 🔧 Configuration Options

### Database Configuration

File: `config/database.php`

```php
// Default configuration (sesuai dengan XAMPP)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');              // Password kosong (default XAMPP)
define('DB_NAME', 'bangjo_db');
```

### Timezone Configuration

File: `config/database.php`

```php
// Timezone Indonesia
date_default_timezone_set('Asia/Jakarta');
```

### Base URL Configuration

File: `includes/auth.php`

```php
// Gunakan untuk development
define('BASE_URL', 'http://localhost/bangjo/');

// Atau untuk custom domain
// define('BASE_URL', 'https://bangjo.example.com/');
```

---

## 🐛 Troubleshooting

### Problem 1: "Connection refused"

**Error Message:**
```
Connection failed: Connection refused
```

**Penyebab:**
- MySQL belum di-start
- Port MySQL sudah terpakai

**Solusi:**
```bash
# 1. Buka XAMPP Control Panel
# 2. Pastikan MySQL status "Running"
# 3. Jika tidak bisa di-start, check port:
netstat -ano | findstr :3306

# 4. Jika ada konflip, change MySQL port di XAMPP config
```

---

### Problem 2: "Unknown database 'bangjo_db'"

**Error Message:**
```
Unknown database 'bangjo_db'
```

**Penyebab:**
- Database belum dibuat
- Nama database salah
- Import schema tidak lengkap

**Solusi:**
```
1. Buka PhpMyAdmin: http://localhost/phpmyadmin
2. Check apakah database 'bangjo_db' sudah ada
3. Jika belum, buat baru
4. Import file config/database.sql
```

---

### Problem 3: "No such file or directory"

**Error Message:**
```
No such file or directory: C:\xampp\htdocs\bangjo\...
```

**Penyebab:**
- File tidak ada di folder yang benar
- Path salah atau folder belum dicopy

**Solusi:**
```
1. Verify project di: C:\xampp\htdocs\bangjo
2. Check semua file sudah ada (login.php, index.php, etc)
3. Verify folder structure:
   - config/
   - includes/
   - assets/
   - admin/
   - petugas/
   - peminjam/
```

---

### Problem 4: "Password incorrect"

**Error Message:**
```
Password salah!
```

**Penyebab:**
- Password salah
- User tidak aktif
- User belum ter-import dari database

**Solusi:**
```
Akun default:
- Username: admin    Password: admin123
- Username: petugas  Password: admin123
- Username: peminjam Password: admin123

Jika masih gagal:
1. Check tabel users di PhpMyAdmin
2. Verify ada 3 user dengan role berbeda
3. Reset password dari database jika perlu
```

---

### Problem 5: Page looks weird / CSS tidak load

**Error Message:**
```
Halaman login tidak ada styling
```

**Penyebab:**
- CSS file tidak ter-load
- Asset path salah
- Browser cache

**Solusi:**
```
1. Clear browser cache (Ctrl + Shift + Delete)
2. Reload page (Ctrl + F5)
3. Check file ada di: assets/css/style.css
4. Verify path di HTML benar
```

---

## 🔒 Post-Installation Security

### 1. Change Default Passwords

**IMPORTANT: Untuk Production**

```sql
-- Change admin password
UPDATE users SET password = PASSWORD_HASH_BARU WHERE username = 'admin';

-- Change petugas password
UPDATE users SET password = PASSWORD_HASH_BARU WHERE username = 'petugas';

-- Change peminjam password
UPDATE users SET password = PASSWORD_HASH_BARU WHERE username = 'peminjam';
```

Gunakan online bcrypt hash generator untuk generate password baru.

### 2. Set MySQL Password

```bash
# Default MySQL tidak punya password
# Set password baru:
cd C:\xampp\mysql\bin
mysql -u root
ALTER USER 'root'@'localhost' IDENTIFIED BY 'newpassword';
FLUSH PRIVILEGES;
EXIT;

# Update config/database.php
define('DB_PASS', 'newpassword');
```

### 3. Protect Sensitive Files

Create `.htaccess` di root folder:
```apache
# Protect config files
<Files "*.sql">
    Order allow,deny
    Deny from all
</Files>

<Files "config.php">
    Order allow,deny
    Deny from all
</Files>
```

### 4. Set Proper Permissions

```bash
# Set folder permissions (Windows)
# Klik kanan folder -> Properties -> Security -> Edit
# Berikan Full Control ke SYSTEM dan Users

# Linux/Mac:
chmod 755 uploads/
chmod 644 config/database.php
```

---

## 📚 Next Steps

Setelah instalasi berhasil:

1. **Explore Admin Features**
   - Tambah user baru
   - Tambah kategori alat
   - Tambah alat
   - Test CRUD operations

2. **Explore Petugas Features**
   - Login dengan akun petugas
   - Coba approval peminjaman
   - Monitor alat

3. **Explore Peminjam Features**
   - Login dengan akun peminjam
   - Lihat katalog alat
   - Ajukan peminjaman

4. **Customize**
   - Edit warna/theme di CSS
   - Add logo Anda di `assets/images/`
   - Ubah nama aplikasi di header

5. **Deploy**
   - Setup di server produksi
   - Configure domain
   - Setup SSL/HTTPS

---

## 📞 Support

Jika ada masalah:

1. Check log file browser (F12 → Console)
2. Check PHP error log di XAMPP
3. Verify database connection
4. Review README.md untuk dokumentasi lengkap

---

**Installation Complete! ✨**

Selamat! Aplikasi Bangjo sudah siap digunakan.

Login dengan salah satu akun demo dan mulai explore fitur-fiturnya.

---

**Last Updated:** 2026
**Version:** 1.0.0
