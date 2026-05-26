-- =========================================
-- Aura & Co. Boutique Cafe — Finance DB Setup
-- Run this once to initialize the database
-- =========================================

CREATE DATABASE IF NOT EXISTS aura_finance
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE aura_finance;

-- ─── Tabel Penjualan ──────────────────────
CREATE TABLE IF NOT EXISTS tabel_penjualan (
    id_penjualan    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tanggal         DATE          NOT NULL,
    nominal_penjualan DECIMAL(15,2) NOT NULL CHECK (nominal_penjualan > 0),
    keterangan      VARCHAR(255)  NOT NULL,
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── Tabel Operasional ────────────────────
CREATE TABLE IF NOT EXISTS tabel_operasional (
    id_operasional    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tanggal           DATE          NOT NULL,
    nominal_operasional DECIMAL(15,2) NOT NULL CHECK (nominal_operasional > 0),
    keterangan        VARCHAR(255)  NOT NULL,
    created_at        TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ─── Sample Data ──────────────────────────
INSERT INTO tabel_penjualan (tanggal, nominal_penjualan, keterangan) VALUES
(CURDATE(), 4500000, 'Penjualan Minuman & Makanan'),
(CURDATE(), 1200000, 'Paket Brunch Weekend'),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 3800000, 'Penjualan Hari Kemarin'),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), 5100000, 'Event Private Booking'),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 2900000, 'Reguler Weekday'),
(DATE_SUB(CURDATE(), INTERVAL 4 DAY), 4200000, 'Penjualan & Merchandise'),
(DATE_SUB(CURDATE(), INTERVAL 5 DAY), 3600000, 'Penjualan Harian'),
(DATE_SUB(CURDATE(), INTERVAL 6 DAY), 4800000, 'Special Promo'),
(DATE_FORMAT(CONCAT(YEAR(CURDATE()), '-', MONTH(CURDATE()), '-01'), '%Y-%m-%d'), 5500000, 'Awal Bulan Opening'),
(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-15'), 4100000, 'Bulan Lalu Mid'),
(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-20'), 3700000, 'Bulan Lalu Akhir'),
(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-10'), 6200000, 'Dua Bulan Lalu');

INSERT INTO tabel_operasional (tanggal, nominal_operasional, keterangan) VALUES
(CURDATE(), 800000, 'Bahan Baku Harian'),
(CURDATE(), 500000, 'Gaji Karyawan Harian'),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 950000, 'Operasional Kemarin'),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1200000, 'Listrik & Air Bulanan'),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 700000, 'Bahan Baku & Packaging'),
(DATE_SUB(CURDATE(), INTERVAL 4 DAY), 850000, 'Bahan Baku Harian'),
(DATE_SUB(CURDATE(), INTERVAL 5 DAY), 600000, 'Maintenance & Kebersihan'),
(DATE_SUB(CURDATE(), INTERVAL 6 DAY), 750000, 'Bahan Baku Premium'),
(DATE_FORMAT(CONCAT(YEAR(CURDATE()), '-', MONTH(CURDATE()), '-01'), '%Y-%m-%d'), 2500000, 'Sewa Tempat Bulanan'),
(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-15'), 900000, 'Bulan Lalu Operasional'),
(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-20'), 1100000, 'Bulan Lalu Akhir Ops'),
(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-10'), 1800000, 'Dua Bulan Lalu Ops');
