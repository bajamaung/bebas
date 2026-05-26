# 🌹 Aura & Co. Boutique Cafe — Finance Management System

Sistem manajemen keuangan berbasis web dengan tema **Luxury Rose Gold Gradient**.

---

## 📁 Struktur File

```
aura-finance/
├── index.php              ← Halaman utama (Dashboard, SPA)
├── api.php                ← JSON API untuk CRUD (POST)
├── api_filter.php         ← JSON API untuk filter tabel (GET)
├── setup.sql              ← Script SQL untuk inisialisasi database
├── config/
│   └── database.php       ← Konfigurasi koneksi PDO
└── includes/
    └── helpers.php        ← Fungsi-fungsi utama (CRUD, format, chart)
```

---

## 🚀 Cara Setup

### 1. Import Database
```sql
-- Di MySQL Workbench, phpMyAdmin, atau terminal:
SOURCE /path/to/aura-finance/setup.sql;
```

### 2. Konfigurasi Database
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');   // Host MySQL
define('DB_NAME', 'aura_finance'); // Nama database
define('DB_USER', 'root');         // Username MySQL
define('DB_PASS', '');             // Password MySQL
```

### 3. Jalankan dengan PHP
```bash
cd aura-finance/
php -S localhost:8080
# Buka http://localhost:8080
```

Atau letakkan di folder `htdocs` (XAMPP) / `www` (WAMP).

---

## ✨ Fitur

| Fitur | Status |
|-------|--------|
| Dashboard statistik hari ini | ✅ |
| Grafik 7 hari (Chart.js) | ✅ |
| Ringkasan bulan ini | ✅ |
| CRUD Penjualan | ✅ |
| CRUD Operasional | ✅ |
| Filter data by range tanggal | ✅ |
| Laporan 6 bulan + bar chart | ✅ |
| Validasi input angka positif | ✅ |
| Format mata uang Rupiah | ✅ |
| Toast notification | ✅ |
| Glassmorphism UI | ✅ |
| Responsive (mobile) | ✅ |

---

## 🎨 Tech Stack

- **Backend**: PHP 8.x Native (PDO + MySQLi-safe)
- **Database**: MySQL 5.7+ / MariaDB 10.x
- **CSS Framework**: Tailwind CSS (CDN)
- **Charts**: Chart.js 4.4.0
- **Fonts**: Cormorant Garamond + Jost (Google Fonts)
- **Architecture**: Modular PHP (Simple MVC pattern)

---

## 🔐 Keamanan

- PDO prepared statements (SQL injection safe)
- `htmlspecialchars()` pada semua output
- `strip_tags()` pada semua input
- Validasi server-side (angka positif, field required)
- Charset UTF-8MB4

---

*Dibuat untuk Aura & Co. Boutique Cafe © 2026*
