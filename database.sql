-- ============================================
-- Cafe Finance Management System
-- Database: cafe_finance
-- ============================================

CREATE DATABASE IF NOT EXISTS cafe_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cafe_finance;

-- Tabel Penjualan (Pemasukan)
CREATE TABLE IF NOT EXISTS penjualan (
    id_penjualan INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE NOT NULL,
    nominal_penjualan DECIMAL(15,2) NOT NULL CHECK (nominal_penjualan > 0),
    keterangan VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Operasional (Pengeluaran)
CREATE TABLE IF NOT EXISTS operasional (
    id_operasional INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE NOT NULL,
    nominal_operasional DECIMAL(15,2) NOT NULL CHECK (nominal_operasional > 0),
    keterangan VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sample Data Penjualan
INSERT INTO penjualan (tanggal, nominal_penjualan, keterangan) VALUES
(CURDATE(), 1850000, 'Penjualan kopi & minuman pagi'),
(CURDATE(), 2300000, 'Penjualan siang + makanan'),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 3100000, 'Penjualan weekend'),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), 2750000, 'Penjualan reguler'),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 1900000, 'Penjualan weekday'),
(DATE_SUB(CURDATE(), INTERVAL 4 DAY), 2200000, 'Event live music'),
(DATE_SUB(CURDATE(), INTERVAL 5 DAY), 2050000, 'Penjualan normal'),
(DATE_SUB(CURDATE(), INTERVAL 6 DAY), 3400000, 'Weekend special menu');

-- Sample Data Operasional
INSERT INTO operasional (tanggal, nominal_operasional, keterangan) VALUES
(CURDATE(), 650000, 'Bahan baku kopi & susu'),
(CURDATE(), 300000, 'Gaji karyawan harian'),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 850000, 'Bahan baku + listrik'),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), 720000, 'Operasional harian'),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 580000, 'Bahan baku minuman'),
(DATE_SUB(CURDATE(), INTERVAL 4 DAY), 900000, 'Sound system + bahan baku'),
(DATE_SUB(CURDATE(), INTERVAL 5 DAY), 670000, 'Operasional reguler'),
(DATE_SUB(CURDATE(), INTERVAL 6 DAY), 780000, 'Bahan baku weekend');
