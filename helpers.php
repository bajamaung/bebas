<?php
// includes/helpers.php

require_once __DIR__ . '/../config/database.php';

/**
 * Format angka ke format Rupiah
 */
function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Sanitize input string
 */
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Ambil statistik hari ini
 */
function getStatistikHariIni(): array {
    $pdo = getConnection();
    $today = date('Y-m-d');

    $stmtP = $pdo->prepare("SELECT COALESCE(SUM(nominal_penjualan), 0) AS total FROM tabel_penjualan WHERE tanggal = ?");
    $stmtP->execute([$today]);
    $penjualan = (float)$stmtP->fetchColumn();

    $stmtO = $pdo->prepare("SELECT COALESCE(SUM(nominal_operasional), 0) AS total FROM tabel_operasional WHERE tanggal = ?");
    $stmtO->execute([$today]);
    $operasional = (float)$stmtO->fetchColumn();

    return [
        'penjualan'   => $penjualan,
        'operasional' => $operasional,
        'profit'      => $penjualan - $operasional,
    ];
}

/**
 * Ambil data chart 7 hari terakhir
 */
function getChartData(): array {
    $pdo = getConnection();
    $labels = $penjualanData = $operasionalData = [];

    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = date('d M', strtotime($date));

        $stmtP = $pdo->prepare("SELECT COALESCE(SUM(nominal_penjualan), 0) FROM tabel_penjualan WHERE tanggal = ?");
        $stmtP->execute([$date]);
        $penjualanData[] = (float)$stmtP->fetchColumn();

        $stmtO = $pdo->prepare("SELECT COALESCE(SUM(nominal_operasional), 0) FROM tabel_operasional WHERE tanggal = ?");
        $stmtO->execute([$date]);
        $operasionalData[] = (float)$stmtO->fetchColumn();
    }

    return compact('labels', 'penjualanData', 'operasionalData');
}

/**
 * Ambil data dengan filter tanggal
 */
function getPenjualanFiltered(string $from, string $to): array {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM tabel_penjualan WHERE tanggal BETWEEN ? AND ? ORDER BY tanggal DESC, id_penjualan DESC");
    $stmt->execute([$from, $to]);
    return $stmt->fetchAll();
}

function getOperasionalFiltered(string $from, string $to): array {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM tabel_operasional WHERE tanggal BETWEEN ? AND ? ORDER BY tanggal DESC, id_operasional DESC");
    $stmt->execute([$from, $to]);
    return $stmt->fetchAll();
}

/**
 * CRUD Penjualan
 */
function insertPenjualan(string $tanggal, float $nominal, string $keterangan): bool {
    $pdo = getConnection();
    $stmt = $pdo->prepare("INSERT INTO tabel_penjualan (tanggal, nominal_penjualan, keterangan) VALUES (?, ?, ?)");
    return $stmt->execute([$tanggal, $nominal, $keterangan]);
}

function updatePenjualan(int $id, string $tanggal, float $nominal, string $keterangan): bool {
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE tabel_penjualan SET tanggal=?, nominal_penjualan=?, keterangan=? WHERE id_penjualan=?");
    return $stmt->execute([$tanggal, $nominal, $keterangan, $id]);
}

function deletePenjualan(int $id): bool {
    $pdo = getConnection();
    $stmt = $pdo->prepare("DELETE FROM tabel_penjualan WHERE id_penjualan=?");
    return $stmt->execute([$id]);
}

function getPenjualanById(int $id): ?array {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM tabel_penjualan WHERE id_penjualan=?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * CRUD Operasional
 */
function insertOperasional(string $tanggal, float $nominal, string $keterangan): bool {
    $pdo = getConnection();
    $stmt = $pdo->prepare("INSERT INTO tabel_operasional (tanggal, nominal_operasional, keterangan) VALUES (?, ?, ?)");
    return $stmt->execute([$tanggal, $nominal, $keterangan]);
}

function updateOperasional(int $id, string $tanggal, float $nominal, string $keterangan): bool {
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE tabel_operasional SET tanggal=?, nominal_operasional=?, keterangan=? WHERE id_operasional=?");
    return $stmt->execute([$tanggal, $nominal, $keterangan, $id]);
}

function deleteOperasional(int $id): bool {
    $pdo = getConnection();
    $stmt = $pdo->prepare("DELETE FROM tabel_operasional WHERE id_operasional=?");
    return $stmt->execute([$id]);
}

function getOperasionalById(int $id): ?array {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM tabel_operasional WHERE id_operasional=?");
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Summary per bulan untuk laporan
 */
function getMonthlySummary(int $months = 6): array {
    $pdo = getConnection();
    $data = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $year  = date('Y', strtotime("-{$i} months"));
        $month = date('m', strtotime("-{$i} months"));
        $label = date('M Y', strtotime("-{$i} months"));

        $stmtP = $pdo->prepare("SELECT COALESCE(SUM(nominal_penjualan),0) FROM tabel_penjualan WHERE YEAR(tanggal)=? AND MONTH(tanggal)=?");
        $stmtP->execute([$year, $month]);
        $p = (float)$stmtP->fetchColumn();

        $stmtO = $pdo->prepare("SELECT COALESCE(SUM(nominal_operasional),0) FROM tabel_operasional WHERE YEAR(tanggal)=? AND MONTH(tanggal)=?");
        $stmtO->execute([$year, $month]);
        $o = (float)$stmtO->fetchColumn();

        $data[] = ['label' => $label, 'penjualan' => $p, 'operasional' => $o, 'profit' => $p - $o];
    }
    return $data;
}
