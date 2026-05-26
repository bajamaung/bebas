# ☕ Brewledger — Cafe Finance Management System

Sistem manajemen keuangan kafe berbasis PHP Native + MySQL + Bootstrap.

---

## 📁 Struktur Folder

```
cafe_finance/
├── index.php                  # Dashboard utama
├── penjualan.php              # Router penjualan (CRUD)
├── operasional.php            # Router operasional (CRUD)
├── database.sql               # Script SQL database
├── config/
│   ├── database.php           # Konfigurasi & koneksi PDO
│   └── helpers.php            # Fungsi bantu (formatRupiah, dll)
├── models/
│   ├── PenjualanModel.php     # Model data penjualan
│   └── OperasionalModel.php   # Model data operasional
└── controllers/
    ├── PenjualanController.php  # Logic CRUD penjualan
    └── OperasionalController.php # Logic CRUD operasional
```

---

## 🚀 Cara Setup

### 1. Persyaratan
- PHP >= 8.1
- MySQL / MariaDB
- Web server: XAMPP / Laragon / WAMP

### 2. Import Database
Buka phpMyAdmin atau MySQL CLI, lalu jalankan:
```sql
source /path/to/cafe_finance/database.sql;
```

Atau paste isi `database.sql` di tab SQL phpMyAdmin.

### 3. Konfigurasi Database
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // username MySQL kamu
define('DB_PASS', '');          // password MySQL kamu
define('DB_NAME', 'cafe_finance');
```

### 4. Jalankan
Taruh folder `cafe_finance/` di:
- XAMPP: `C:/xampp/htdocs/cafe_finance/`
- Laragon: `C:/laragon/www/cafe_finance/`

Akses di browser: `http://localhost/cafe_finance/`

---

## ✨ Fitur

| Fitur | Keterangan |
|-------|-----------|
| 📊 Dashboard | Statistik harian real-time |
| 💰 Penjualan CRUD | Tambah, edit, hapus data pemasukan |
| 🧾 Operasional CRUD | Tambah, edit, hapus data pengeluaran |
| 📈 Grafik | Chart 7 hari pemasukan vs pengeluaran |
| 🔍 Filter | Filter data berdasarkan rentang tanggal |
| 🧮 Agregasi SQL | SUM total penjualan, operasional, keuntungan |
| 📱 Responsive | Tampilan mobile-friendly dengan sidebar toggle |
| 💱 Format Rupiah | Semua nominal dalam format Rp |

---

## 🔒 Keamanan
- Menggunakan PDO prepared statements (anti SQL Injection)
- Input HTML di-escape dengan `htmlspecialchars()`
- Validasi angka positif di server & client
- CSRF sederhana melalui session

---

## 🎨 Tech Stack
- **Backend**: PHP 8.1+ Native (MVC Pattern)
- **Database**: MySQL / MariaDB
- **Frontend**: Bootstrap 5.3 + Custom CSS
- **Chart**: Chart.js 4
- **Font**: Playfair Display + DM Sans
