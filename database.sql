-- Create Database
CREATE DATABASE IF NOT EXISTS `bangjo_db`;
USE `bangjo_db`;

-- Users Table
CREATE TABLE `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'petugas', 'peminjam') NOT NULL DEFAULT 'peminjam',
  `status` ENUM('aktif', 'nonaktif') NOT NULL DEFAULT 'aktif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kategori Table
CREATE TABLE `kategori` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nama_kategori` VARCHAR(100) NOT NULL UNIQUE,
  `deskripsi` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alat Table
CREATE TABLE `alat` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `kode_alat` VARCHAR(50) UNIQUE NOT NULL,
  `nama_alat` VARCHAR(100) NOT NULL,
  `kategori_id` INT NOT NULL,
  `stok` INT NOT NULL DEFAULT 0,
  `kondisi` ENUM('baik', 'kurang_baik', 'rusak') NOT NULL DEFAULT 'baik',
  `lokasi_rak` VARCHAR(100),
  `foto` VARCHAR(255),
  `qr_code` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Peminjaman Table
CREATE TABLE `peminjaman` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `kode_peminjaman` VARCHAR(50) UNIQUE NOT NULL,
  `user_id` INT NOT NULL,
  `tanggal_pinjam` DATE NOT NULL,
  `deadline_kembali` DATE NOT NULL,
  `status` ENUM('pending', 'disetujui', 'ditolak', 'sedang_dipinjam', 'sudah_dikembalikan') NOT NULL DEFAULT 'pending',
  `catatan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detail Peminjaman Table
CREATE TABLE `detail_peminjaman` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `peminjaman_id` INT NOT NULL,
  `alat_id` INT NOT NULL,
  `jumlah` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
  FOREIGN KEY (alat_id) REFERENCES alat(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pengembalian Table
CREATE TABLE `pengembalian` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `peminjaman_id` INT NOT NULL,
  `tanggal_kembali` DATE NOT NULL,
  `kondisi_alat` ENUM('baik', 'kurang_baik', 'rusak') NOT NULL,
  `denda` DECIMAL(10, 2) DEFAULT 0,
  `keterangan` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log Aktivitas Table
CREATE TABLE `log_aktivitas` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `aktivitas` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT,
  `tabel` VARCHAR(50),
  `record_id` INT,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Admin User (password: admin123)
INSERT INTO `users` (`nama`, `username`, `password`, `role`, `status`) VALUES
('Administrator', 'admin', '$2y$10$YIjlrBJSXzQr.h8YG7Uk4eKR8qKNp5wBccP1I9XXZL5gWQaYFZnRS', 'admin', 'aktif'),
('Petugas Sistem', 'petugas', '$2y$10$7h6Z3K9pL5mN2X1Q8w9Y0u7R4P5O2N3M8K9L0J1H2G3F4E5D6C7B', 'petugas', 'aktif'),
('Peminjam Test', 'peminjam', '$2y$10$Z9N2M3L8K7J6H5G4F3E2D1C0B9A8Z7Y6X5W4V3U2T1S0R9Q8P7', 'peminjam', 'aktif');

-- Create Indexes for better performance
CREATE INDEX idx_user_role ON users(role);
CREATE INDEX idx_user_status ON users(status);
CREATE INDEX idx_alat_kategori ON alat(kategori_id);
CREATE INDEX idx_peminjaman_user ON peminjaman(user_id);
CREATE INDEX idx_peminjaman_status ON peminjaman(status);
CREATE INDEX idx_detail_peminjaman ON detail_peminjaman(peminjaman_id);
CREATE INDEX idx_pengembalian_peminjaman ON pengembalian(peminjaman_id);
CREATE INDEX idx_log_aktivitas_user ON log_aktivitas(user_id);
CREATE INDEX idx_log_aktivitas_created ON log_aktivitas(created_at);
