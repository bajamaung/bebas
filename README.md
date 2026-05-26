# Bangjo - Sistem Peminjaman Alat

Sistem peminjaman alat berbasis **PHP Native + MySQL** dengan desain **enterprise-level UI/UX** yang profesional, modern, clean, dan responsive. Sistem ini dirancang untuk mengelola peminjaman dan pengembalian alat dengan mudah dan efisien.

## 🎯 Fitur Utama

### 1. **Sistem Keamanan**
- Login aman dengan bcrypt password hashing
- Prepared statement untuk mencegah SQL Injection
- CSRF protection pada setiap form
- Session management yang aman
- Auto logout jika idle 30 menit
- Remember Me functionality

### 2. **Role-Based Access Control**
- **Admin**: Mengelola semua data (User, Alat, Kategori, Peminjaman, Pengembalian, Log)
- **Petugas**: Approval peminjaman, monitoring, laporan
- **Peminjam**: Melihat katalog alat, mengajukan peminjaman, melihat riwayat

### 3. **Dashboard & UI/UX**
- Dashboard enterprise-level untuk setiap role
- Sidebar modern dengan collapsible menu
- Navbar clean dengan search dan notifikasi
- Statistik realtime dengan card design premium
- Responsive design (mobile, tablet, desktop)
- Dark mode support (optional)
- Toast notifications & SweetAlert

### 4. **Fitur Peminjaman**
- Katalog alat dengan grid layout modern
- Form peminjaman dengan validasi
- Approval/Rejection system untuk petugas
- Status tracking realtime
- Deadline reminder

### 5. **Fitur Tambahan**
- Export data ke Excel & PDF
- Print functionality
- Search dan filter realtime
- Pagination AJAX
- Log aktivitas sistem
- QR Code support (siap diimplementasi)
- Chart.js untuk grafik statistik
- Responsive table dengan action buttons

## 📋 Requirement

- **PHP**: 7.4 atau lebih tinggi
- **MySQL**: 5.7 atau lebih tinggi
- **Web Server**: Apache (XAMPP)
- **Browser**: Modern browser (Chrome, Firefox, Safari, Edge)

## 🚀 Instalasi & Setup

### 1. **Download & Extract Project**
```bash
# Project sudah ada di: c:\xampp\htdocs\bangjo
# Atau copy ke folder htdocs
```

### 2. **Setup Database**

#### Cara 1: Menggunakan phpMyAdmin
1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Buat database baru dengan nama `bangjo_db`
3. Pilih database tersebut
4. Pergi ke tab "Import"
5. Pilih file `config/database.sql`
6. Klik "Go" untuk mengimport

#### Cara 2: Menggunakan Command Line
```bash
# Buka MySQL command line
mysql -u root -p

# Jalankan perintah berikut
CREATE DATABASE bangjo_db;
USE bangjo_db;
SOURCE c:\xampp\htdocs\bangjo\config\database.sql;
```

### 3. **Verifikasi Koneksi Database**

Edit file `config/database.php` jika diperlukan:
```php
define('DB_HOST', 'localhost');  // Host database
define('DB_USER', 'root');       // Username database
define('DB_PASS', '');           // Password database
define('DB_NAME', 'bangjo_db');  // Nama database
```

### 4. **Akses Aplikasi**

Buka browser dan akses:
```
http://localhost/bangjo/
```

Anda akan diarahkan ke halaman login.

## 🔐 Akun Demo

Sistem sudah menyediakan 3 akun demo untuk testing:

| Role | Username | Password | Deskripsi |
|------|----------|----------|-----------|
| Admin | `admin` | `admin123` | Akses penuh ke semua fitur |
| Petugas | `petugas` | `admin123` | Approval dan monitoring |
| Peminjam | `peminjam` | `admin123` | Mengajukan dan melihat peminjaman |

> ⚠️ **Catatan**: Untuk production, ubah password default ini!

## 📁 Struktur Folder

```
bangjo/
├── config/
│   ├── database.php          # Konfigurasi database
│   └── database.sql          # Schema database (untuk import)
├── includes/
│   ├── auth.php              # Authentication & session management
│   ├── header.php            # HTML header & CSS
│   ├── sidebar.php           # Sidebar navigation
│   ├── navbar.php            # Top navbar
│   └── footer.php            # Footer & closing tags
├── assets/
│   ├── css/
│   │   └── style.css         # Custom CSS styles
│   ├── js/
│   │   └── main.js           # JavaScript functions
│   └── images/               # Image assets
├── admin/
│   ├── index.php             # Dashboard admin
│   ├── user.php              # Manajemen user
│   ├── alat.php              # Manajemen alat
│   ├── kategori.php          # Manajemen kategori
│   ├── peminjaman.php        # Data peminjaman
│   ├── pengembalian.php      # Data pengembalian
│   └── log.php               # Log aktivitas
├── petugas/
│   ├── index.php             # Dashboard petugas
│   ├── approval.php          # Approval peminjaman
│   ├── monitoring.php        # Monitoring peminjaman
│   └── laporan.php           # Laporan (siap diimplementasi)
├── peminjam/
│   ├── index.php             # Dashboard peminjam
│   ├── katalog.php           # Katalog alat
│   └── riwayat.php           # Riwayat peminjaman
├── uploads/
│   └── alat/                 # Folder upload foto alat
├── login.php                 # Halaman login
├── logout.php                # Logout handler
├── index.php                 # Redirect ke dashboard sesuai role
└── README.md                 # Dokumentasi (file ini)
```

## 🗄️ Database Schema

### Tabel-tabel yang dibuat:

1. **users** - Data user (admin, petugas, peminjam)
2. **kategori** - Kategori alat
3. **alat** - Data alat yang tersedia
4. **peminjaman** - Data peminjaman
5. **detail_peminjaman** - Detail item dalam setiap peminjaman
6. **pengembalian** - Data pengembalian alat
7. **log_aktivitas** - Log semua aktivitas sistem

## 🎨 UI/UX Design Features

### Color Scheme
- **Primary**: #2563eb (Blue)
- **Secondary**: #0f172a (Dark Navy)
- **Success**: #16a34a (Green)
- **Warning**: #f59e0b (Amber)
- **Danger**: #dc2626 (Red)
- **Background**: #f9fafb (Light Gray)

### Typography
- **Font**: Poppins (modern, clean)
- **Sizes**: Responsive dengan media queries

### Components
- Modern cards dengan shadow & hover effect
- Stat cards dengan icon & gradient
- Tables dengan sorting, searching, pagination
- Forms dengan validation & error handling
- Modals dengan smooth animation
- Breadcrumb navigation
- Status badges dengan warna berbeda
- Toast notifications
- SweetAlert confirmations
- Loading skeleton
- Empty state icons

## 🔄 Workflow Peminjaman

```
1. Peminjam Login ke Dashboard
   ↓
2. Cari & Lihat Katalog Alat
   ↓
3. Ajukan Peminjaman (Pilih Alat, Jumlah, Deadline)
   ↓
4. Petugas Menerima Notifikasi
   ↓
5. Petugas Approve/Reject Peminjaman
   ↓
6. Peminjam Lihat Status Peminjaman
   ↓
7. Peminjam Mengambil Alat (Status: Sedang Dipinjam)
   ↓
8. Peminjam Mengembalikan Alat
   ↓
9. Petugas Verifikasi Kondisi Alat
   ↓
10. Transaksi Selesai (Status: Sudah Dikembalikan)
```

## 👤 User Management (Admin)

### CRUD User
- **Create**: Tambah user baru dengan role (admin, petugas, peminjam)
- **Read**: Lihat daftar semua user dengan filter
- **Update**: Edit nama, role, dan status user
- **Delete**: Hapus user dari sistem

### Fitur Security
- Password dienkripsi dengan bcrypt
- Username harus unik
- Status aktif/nonaktif untuk mengontrol akses
- Change password functionality

## 📊 Dashboard Admin

Menampilkan:
- Total Users
- Total Alat
- Total Kategori
- Peminjaman Aktif
- Pengembalian Hari Ini
- Chart statistik 30 hari terakhir
- Timeline aktivitas terbaru
- Quick action buttons

## 📊 Dashboard Petugas

Menampilkan:
- Permintaan Pending (count)
- Alat Sedang Dipinjam (count)
- Pengembalian Terlambat (count)
- Daftar request pending dengan approve/reject buttons
- Chart peminjaman bulanan

## 📊 Dashboard Peminjam

Menampilkan:
- Peminjaman Aktif (count)
- Alat Tersedia (count)
- Total Peminjaman (count)
- Alat Populer dengan grid
- Riwayat Peminjaman Terbaru
- Quick links ke katalog

## 🔧 Konfigurasi Tambahan

### Mengubah Timeout Idle Logout
Edit di `assets/js/main.js`:
```javascript
let idleWait = 30 * 60 * 1000; // 30 menit (dalam milliseconds)
```

### Mengubah Base URL
Edit di `includes/auth.php`:
```php
define('BASE_URL', 'http://localhost/bangjo/');
```

## 📝 API Endpoints Siap Diimplementasi

### untuk AJAX requests:
- `/api/search-alat.php` - Search alat realtime
- `/api/get-notifications.php` - Get notifikasi
- `/api/approve-peminjaman.php` - Approve peminjaman
- `/api/reject-peminjaman.php` - Reject peminjaman
- `/api/export-excel.php` - Export ke Excel
- `/api/export-pdf.php` - Export ke PDF

## 🐛 Troubleshooting

### Masalah: Database connection error
**Solusi**:
- Pastikan MySQL running
- Check `config/database.php` settings
- Verify database `bangjo_db` sudah dibuat

### Masalah: Login gagal
**Solusi**:
- Pastikan password benar
- Cek status user (aktif/nonaktif)
- Clear browser cache dan cookies

### Masalah: Session expired
**Solusi**:
- Login kembali
- Ubah idle wait time di `assets/js/main.js` jika terlalu singkat
- Check server timezone di `config/database.php`

### Masalah: File upload error
**Solusi**:
- Create folder `uploads/alat` dengan permission 755
- Check PHP `upload_max_filesize` di `php.ini`

## 📈 Development & Extension

Aplikasi ini siap untuk dikembangkan dengan:

1. **API REST**
   - Buat folder `/api` untuk endpoints
   - Return JSON responses
   - Implement proper error handling

2. **Fitur Tambahan**
   - QR Code generation & scanning
   - Email notifications
   - SMS alerts
   - Denda otomatis untuk keterlambatan
   - Maintenance tracking untuk alat
   - User ratings & reviews

3. **Database Optimization**
   - Add more indexes
   - Implement caching
   - Archive old data

4. **Security**
   - Two-factor authentication (2FA)
   - IP whitelist
   - API token authentication
   - Rate limiting

## 🎓 Suitability

Aplikasi ini **perfect untuk**:
- ✅ Project mahasiswa/sekolah
- ✅ Portfolio development
- ✅ Presentasi di kampus/kantor
- ✅ Learning PHP Native & MySQL
- ✅ Production use dengan minor modifications

## 📞 Support & Documentation

Dokumentasi lengkap tersedia di:
- Struktur folder yang jelas
- Comment dalam setiap file
- Standardized coding style
- Clean & readable code

## 📄 License

Sistem ini dibuat untuk keperluan edukasi dan dapat digunakan secara gratis.

## ✨ Created By

**Bangjo Sistem Peminjaman Alat**
- Designed for professional use
- Built with modern technologies
- Ready for enterprise deployment

---

**Happy coding! 🚀**

Jika ada pertanyaan atau bug, silakan review kode dan lakukan debugging sesuai kebutuhan.
