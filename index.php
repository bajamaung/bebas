<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/models/PenjualanModel.php';
require_once __DIR__ . '/models/OperasionalModel.php';

$penjualanModel   = new PenjualanModel();
$operasionalModel = new OperasionalModel();

// --- Filter ---
$filter_start = $_GET['filter_start'] ?? '';
$filter_end   = $_GET['filter_end']   ?? '';

// --- Summary Aggregation ---
$summaryP = $penjualanModel->getSummary($filter_start, $filter_end);
$summaryO = $operasionalModel->getSummary($filter_start, $filter_end);
$totalPenjualan   = (float)$summaryP['total_penjualan'];
$totalOperasional = (float)$summaryO['total_operasional'];
$totalKeuntungan  = $totalPenjualan - $totalOperasional;

// --- Today Summary ---
$todayP = $penjualanModel->getToday();
$todayO = $operasionalModel->getToday();
$todayPenjualan   = (float)$todayP['total'];
$todayOperasional = (float)$todayO['total'];
$todayKeuntungan  = $todayPenjualan - $todayOperasional;

// --- Chart Data ---
$chartPenjualan   = $penjualanModel->getChartData(7);
$chartOperasional = $operasionalModel->getChartData(7);

// Build chart dates (last 7 days)
$chartDates = [];
for ($i = 6; $i >= 0; $i--) {
    $chartDates[] = date('Y-m-d', strtotime("-{$i} days"));
}
$chartPMap = array_column($chartPenjualan, 'total', 'tanggal');
$chartOMap = array_column($chartOperasional, 'total', 'tanggal');
$chartLabels = [];
$chartPData  = [];
$chartOData  = [];
foreach ($chartDates as $d) {
    $parts = explode('-', $d);
    $months = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun',
               '07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
    $chartLabels[] = $parts[2] . ' ' . ($months[$parts[1]] ?? $parts[1]);
    $chartPData[]  = (float)($chartPMap[$d] ?? 0);
    $chartOData[]  = (float)($chartOMap[$d] ?? 0);
}

// --- Table Data ---
$penjualanList   = $penjualanModel->getAll($filter_start, $filter_end);
$operasionalList = $operasionalModel->getAll($filter_start, $filter_end);

// --- Edit State ---
$editPenjualan   = $_SESSION['edit_penjualan']   ?? null;
$editOperasional = $_SESSION['edit_operasional'] ?? null;
unset($_SESSION['edit_penjualan'], $_SESSION['edit_operasional']);

$profitPercent = $totalPenjualan > 0 ? round(($totalKeuntungan / $totalPenjualan) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Brewledger — Cafe Finance System</title>

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
/* ===== CSS VARIABLES ===== */
:root {
    --cafe-espresso:   #1C0A00;
    --cafe-dark:       #2D1505;
    --cafe-brown:      #6B3A2A;
    --cafe-mocha:      #8B5A3C;
    --cafe-caramel:    #C4843A;
    --cafe-gold:       #D4A853;
    --cafe-cream:      #F5ECD7;
    --cafe-latte:      #EDD9B8;
    --cafe-white:      #FDF8F0;
    --sidebar-width:   260px;
    --topbar-h:        70px;
    --radius-lg:       16px;
    --radius-md:       12px;
    --radius-sm:       8px;
    --shadow-card:     0 4px 24px rgba(28,10,0,0.10);
    --shadow-lg:       0 8px 40px rgba(28,10,0,0.16);
    --transition:      all 0.28s cubic-bezier(.4,0,.2,1);
}

/* ===== RESET & BASE ===== */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cafe-white);
    color: var(--cafe-espresso);
    min-height: 100vh;
    overflow-x: hidden;
}
h1,h2,h3,h4,h5 { font-family: 'Playfair Display', serif; }

/* ===== SIDEBAR ===== */
.sidebar {
    position: fixed;
    top: 0; left: 0;
    width: var(--sidebar-width);
    height: 100vh;
    background: var(--cafe-espresso);
    background-image: linear-gradient(160deg, #2D1505 0%, #1C0A00 100%);
    z-index: 1000;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: var(--transition);
}
.sidebar::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 220px; height: 220px;
    background: radial-gradient(circle, rgba(196,132,58,0.18) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.sidebar-brand {
    padding: 28px 24px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
}
.brand-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}
.brand-icon {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, var(--cafe-caramel), var(--cafe-gold));
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    box-shadow: 0 4px 14px rgba(196,132,58,0.35);
    flex-shrink: 0;
}
.brand-text { line-height: 1.1; }
.brand-name {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--cafe-cream);
    letter-spacing: .5px;
}
.brand-sub {
    font-size: 10px;
    color: rgba(245,236,215,0.5);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 500;
}
.sidebar-nav { padding: 20px 0; flex: 1; }
.nav-label {
    font-size: 9px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(245,236,215,0.35);
    padding: 0 24px 8px;
    margin-top: 12px;
}
.nav-item { list-style: none; padding: 2px 12px; }
.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 16px;
    border-radius: var(--radius-sm);
    color: rgba(245,236,215,0.65);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}
.nav-link::before {
    content: '';
    position: absolute;
    left: 0; top: 50%;
    transform: translateY(-50%);
    width: 3px; height: 0;
    background: var(--cafe-gold);
    border-radius: 0 3px 3px 0;
    transition: height .25s ease;
}
.nav-link:hover, .nav-link.active {
    background: rgba(196,132,58,0.15);
    color: var(--cafe-cream);
}
.nav-link:hover::before, .nav-link.active::before { height: 60%; }
.nav-link.active { color: var(--cafe-gold); font-weight: 600; }
.nav-icon { font-size: 17px; width: 20px; text-align: center; flex-shrink: 0; }
.sidebar-footer {
    padding: 16px 24px;
    border-top: 1px solid rgba(255,255,255,0.07);
}
.sidebar-date {
    font-size: 11px;
    color: rgba(245,236,215,0.4);
    text-align: center;
    letter-spacing: .5px;
}

/* ===== MAIN CONTENT ===== */
.main-content {
    margin-left: var(--sidebar-width);
    min-height: 100vh;
    transition: var(--transition);
}

/* ===== TOPBAR ===== */
.topbar {
    height: var(--topbar-h);
    background: rgba(253,248,240,0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(107,58,42,0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 32px;
    position: sticky;
    top: 0;
    z-index: 100;
}
.topbar-left { display: flex; align-items: center; gap: 14px; }
.menu-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
    color: var(--cafe-brown);
    padding: 4px;
    line-height: 1;
}
.topbar-title {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 600;
    color: var(--cafe-espresso);
}
.topbar-right { display: flex; align-items: center; gap: 12px; }
.today-badge {
    background: var(--cafe-latte);
    color: var(--cafe-brown);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .3px;
}
.avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--cafe-caramel), var(--cafe-gold));
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(196,132,58,0.3);
    transition: var(--transition);
}
.avatar:hover { transform: scale(1.08); }

/* ===== PAGE CONTENT ===== */
.page-content { padding: 28px 32px 40px; }

/* ===== STATS CARDS ===== */
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 28px; }
.stat-card {
    background: #fff;
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-card);
    position: relative;
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid rgba(107,58,42,0.06);
}
.stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); }
.stat-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}
.stat-card.pemasukan::after  { background: linear-gradient(90deg, #52b788, #74c69d); }
.stat-card.pengeluaran::after { background: linear-gradient(90deg, #e07a5f, #f2ac99); }
.stat-card.keuntungan::after  { background: linear-gradient(90deg, var(--cafe-caramel), var(--cafe-gold)); }
.stat-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(28,10,0,0.45);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.stat-dot { width: 6px; height: 6px; border-radius: 50%; }
.stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--cafe-espresso);
    margin-bottom: 8px;
    letter-spacing: -.5px;
}
.stat-sub { font-size: 12px; color: rgba(28,10,0,0.4); }
.stat-icon {
    position: absolute;
    top: 20px; right: 20px;
    font-size: 32px;
    opacity: .12;
}
.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 10px;
    margin-top: 4px;
}
.stat-badge.up   { background: #d8f3dc; color: #2d6a4f; }
.stat-badge.down { background: #ffe5d9; color: #9d0208; }
.stat-badge.neutral { background: var(--cafe-latte); color: var(--cafe-brown); }

/* ===== FILTER BAR ===== */
.filter-bar {
    background: #fff;
    border-radius: var(--radius-lg);
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-card);
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    border: 1px solid rgba(107,58,42,0.06);
}
.filter-bar label {
    font-size: 12px;
    font-weight: 600;
    color: var(--cafe-brown);
    text-transform: uppercase;
    letter-spacing: 1px;
    white-space: nowrap;
}
.filter-input {
    height: 38px;
    border: 1.5px solid var(--cafe-latte);
    border-radius: var(--radius-sm);
    padding: 0 12px;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    color: var(--cafe-espresso);
    background: var(--cafe-white);
    transition: var(--transition);
}
.filter-input:focus {
    outline: none;
    border-color: var(--cafe-caramel);
    box-shadow: 0 0 0 3px rgba(196,132,58,0.12);
}
.btn-filter {
    height: 38px;
    padding: 0 20px;
    background: linear-gradient(135deg, var(--cafe-caramel), var(--cafe-gold));
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: var(--transition);
    letter-spacing: .3px;
}
.btn-filter:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(196,132,58,0.35); }
.btn-reset {
    height: 38px;
    padding: 0 16px;
    background: transparent;
    color: var(--cafe-brown);
    border: 1.5px solid var(--cafe-latte);
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.btn-reset:hover { border-color: var(--cafe-caramel); color: var(--cafe-caramel); }

/* ===== CHART CARD ===== */
.chart-card {
    background: #fff;
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-card);
    margin-bottom: 28px;
    border: 1px solid rgba(107,58,42,0.06);
}
.card-header-custom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}
.card-title-custom {
    font-family: 'Playfair Display', serif;
    font-size: 17px;
    font-weight: 600;
    color: var(--cafe-espresso);
}
.chart-legend {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}
.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: rgba(28,10,0,0.6);
    font-weight: 500;
}
.legend-dot {
    width: 10px; height: 10px;
    border-radius: 3px;
}

/* ===== DATA TABLES SECTION ===== */
.tables-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 28px;
}
.table-card {
    background: #fff;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    border: 1px solid rgba(107,58,42,0.06);
}
.table-card-header {
    padding: 20px 24px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.table-section-title {
    font-family: 'Playfair Display', serif;
    font-size: 16px;
    font-weight: 600;
    color: var(--cafe-espresso);
    display: flex;
    align-items: center;
    gap: 8px;
}
.table-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 12px;
    font-family: 'DM Sans', sans-serif;
}
.badge-green { background: #d8f3dc; color: #2d6a4f; }
.badge-red   { background: #ffe5d9; color: #9d0208; }
.table-scroll { overflow-x: auto; max-height: 360px; overflow-y: auto; }
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.data-table thead th {
    background: var(--cafe-cream);
    color: var(--cafe-brown);
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 11px 16px;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 1;
}
.data-table tbody td {
    padding: 11px 16px;
    border-bottom: 1px solid rgba(107,58,42,0.06);
    color: var(--cafe-espresso);
    vertical-align: middle;
}
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover { background: rgba(245,236,215,0.4); }
.amount-cell { font-weight: 600; font-size: 13px; white-space: nowrap; }
.amount-green { color: #2d6a4f; }
.amount-red   { color: #9d0208; }
.desc-cell {
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: rgba(28,10,0,0.55);
    font-size: 12px;
}
.btn-action {
    padding: 4px 10px;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    line-height: 1.5;
}
.btn-edit { background: rgba(196,132,58,0.12); color: var(--cafe-brown); }
.btn-edit:hover { background: var(--cafe-caramel); color: #fff; }
.btn-del  { background: rgba(224,122,95,0.12); color: #c1440e; }
.btn-del:hover { background: #e07a5f; color: #fff; }
.empty-row td { text-align: center; padding: 28px; color: rgba(28,10,0,0.3); font-size: 13px; }

/* ===== FORM SECTION ===== */
.forms-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}
.form-card {
    background: #fff;
    border-radius: var(--radius-lg);
    padding: 28px;
    box-shadow: var(--shadow-card);
    border: 1px solid rgba(107,58,42,0.06);
}
.form-card-title {
    font-family: 'Playfair Display', serif;
    font-size: 17px;
    font-weight: 600;
    color: var(--cafe-espresso);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-icon {
    width: 34px; height: 34px;
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.form-icon.green { background: #d8f3dc; }
.form-icon.red   { background: #ffe5d9; }
.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--cafe-brown);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 6px;
}
.form-control-custom {
    width: 100%;
    height: 42px;
    border: 1.5px solid var(--cafe-latte);
    border-radius: var(--radius-sm);
    padding: 0 14px;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    color: var(--cafe-espresso);
    background: var(--cafe-white);
    transition: var(--transition);
}
textarea.form-control-custom {
    height: auto;
    padding: 10px 14px;
    resize: none;
}
.form-control-custom:focus {
    outline: none;
    border-color: var(--cafe-caramel);
    box-shadow: 0 0 0 3px rgba(196,132,58,0.12);
}
.form-control-custom::placeholder { color: rgba(28,10,0,0.3); }
.btn-submit {
    width: 100%;
    height: 44px;
    border: none;
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: var(--transition);
    letter-spacing: .4px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 8px;
}
.btn-submit.green {
    background: linear-gradient(135deg, #52b788, #40916c);
    color: #fff;
}
.btn-submit.red {
    background: linear-gradient(135deg, #e07a5f, #c1440e);
    color: #fff;
}
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.15); }
.btn-cancel {
    width: 100%;
    height: 38px;
    background: transparent;
    border: 1.5px solid var(--cafe-latte);
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    color: var(--cafe-brown);
    transition: var(--transition);
    margin-top: 6px;
    text-align: center;
    display: block;
    line-height: 36px;
    text-decoration: none;
}
.btn-cancel:hover { border-color: var(--cafe-caramel); color: var(--cafe-caramel); }
.form-editing-indicator {
    background: rgba(196,132,58,0.1);
    border: 1.5px solid rgba(196,132,58,0.3);
    border-radius: var(--radius-sm);
    padding: 10px 14px;
    font-size: 12px;
    color: var(--cafe-caramel);
    font-weight: 600;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ===== SUMMARY TOTAL ROW ===== */
.summary-row {
    background: linear-gradient(135deg, var(--cafe-espresso), var(--cafe-dark));
    border-radius: var(--radius-lg);
    padding: 22px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-lg);
}
.summary-item { text-align: center; }
.summary-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(245,236,215,0.5);
    margin-bottom: 4px;
    font-weight: 600;
}
.summary-value {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--cafe-cream);
}
.summary-divider { width: 1px; height: 44px; background: rgba(255,255,255,0.1); }
.summary-profit { color: var(--cafe-gold); }

/* ===== ALERTS ===== */
.custom-alert {
    border: none;
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    position: fixed;
    top: 84px;
    right: 24px;
    z-index: 9999;
    min-width: 280px;
    max-width: 400px;
    animation: slideIn .35s cubic-bezier(.4,0,.2,1);
}
.alert-success { background: #d8f3dc; color: #2d6a4f; }
.alert-danger  { background: #ffe5d9; color: #9d0208; }
.alert-icon { font-size: 16px; flex-shrink: 0; }
@keyframes slideIn {
    from { transform: translateX(120%); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}

/* ===== MOBILE OVERLAY ===== */
.sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 999;
    backdrop-filter: blur(2px);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1200px) {
    .tables-grid, .forms-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: repeat(3,1fr); }
}
@media (max-width: 900px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    .sidebar.open {
        transform: translateX(0);
        box-shadow: var(--shadow-lg);
    }
    .sidebar-overlay.show { display: block; }
    .main-content { margin-left: 0; }
    .menu-toggle { display: block; }
    .page-content { padding: 20px 16px 32px; }
    .topbar { padding: 0 16px; }
    .stats-grid { grid-template-columns: 1fr; }
    .summary-row { gap: 12px; }
    .summary-divider { display: none; }
    .filter-bar { gap: 10px; }
    .topbar-title { font-size: 17px; }
    .stat-value { font-size: 22px; }
}

/* ===== SCROLLBAR ===== */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: var(--cafe-cream); }
::-webkit-scrollbar-thumb { background: var(--cafe-mocha); border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: var(--cafe-brown); }

/* ===== SECTION ANCHOR OFFSET ===== */
.section-anchor { scroll-margin-top: 88px; }
</style>
</head>
<body>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <a href="index.php" class="brand-logo">
            <div class="brand-icon">☕</div>
            <div class="brand-text">
                <div class="brand-name">Jawa Prime</div>
                <div class="brand-sub">Cafe Finance</div>
            </div>
        </a>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <ul style="list-style:none;padding:0">
            <li class="nav-item">
                <a href="index.php" class="nav-link active">
                    <span class="nav-icon">📊</span> Dashboard
                </a>
            </li>
        </ul>
        <div class="nav-label">Transaksi</div>
        <ul style="list-style:none;padding:0">
            <li class="nav-item">
                <a href="#penjualan" class="nav-link">
                    <span class="nav-icon">💰</span> Penjualan
                </a>
            </li>
            <li class="nav-item">
                <a href="#operasional" class="nav-link">
                    <span class="nav-icon">🧾</span> Operasional
                </a>
            </li>
        </ul>
        <div class="nav-label">Laporan</div>
        <ul style="list-style:none;padding:0">
            <li class="nav-item">
                <a href="#chart-section" class="nav-link">
                    <span class="nav-icon">📈</span> Grafik
                </a>
            </li>
            <li class="nav-item">
                <a href="#summary-section" class="nav-link">
                    <span class="nav-icon">🧮</span> Ringkasan
                </a>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <div class="sidebar-date">📅 <?= date('d F Y') ?></div>
    </div>
</aside>

<!-- ===== MAIN CONTENT ===== -->
<main class="main-content">
    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
            <span class="topbar-title">Dashboard Keuangan</span>
        </div>
        <div class="topbar-right">
            <span class="today-badge">📅 <?= date('d M Y') ?></span>
            <div class="avatar" title="Admin">👤</div>
        </div>
    </div>

    <!-- Page Content -->
    <div class="page-content">

        <!-- Flash Messages -->
        <?= flashMessage('success') ?>
        <?= flashMessage('error') ?>

        <!-- ===== STATS CARDS (TODAY) ===== -->
        <div class="stats-grid">
            <div class="stat-card pemasukan">
                <span class="stat-icon">💰</span>
                <div class="stat-label">
                    <span class="stat-dot" style="background:#52b788"></span>
                    Penjualan Hari Ini
                </div>
                <div class="stat-value"><?= formatRupiah($todayPenjualan) ?></div>
                <div class="stat-sub">Total pendapatan hari ini</div>
                <?php if ($todayPenjualan > 0): ?>
                <span class="stat-badge up">▲ Aktif</span>
                <?php else: ?>
                <span class="stat-badge neutral">— Belum ada data</span>
                <?php endif; ?>
            </div>
            <div class="stat-card pengeluaran">
                <span class="stat-icon">🧾</span>
                <div class="stat-label">
                    <span class="stat-dot" style="background:#e07a5f"></span>
                    Operasional Hari Ini
                </div>
                <div class="stat-value"><?= formatRupiah($todayOperasional) ?></div>
                <div class="stat-sub">Total pengeluaran hari ini</div>
                <?php if ($todayOperasional > 0): ?>
                <span class="stat-badge down">▼ Ada pengeluaran</span>
                <?php else: ?>
                <span class="stat-badge neutral">— Belum ada data</span>
                <?php endif; ?>
            </div>
            <div class="stat-card keuntungan">
                <span class="stat-icon">✨</span>
                <div class="stat-label">
                    <span class="stat-dot" style="background:var(--cafe-caramel)"></span>
                    Keuntungan Hari Ini
                </div>
                <div class="stat-value" style="<?= $todayKeuntungan >= 0 ? 'color:var(--cafe-caramel)' : 'color:#9d0208' ?>">
                    <?= formatRupiah($todayKeuntungan) ?>
                </div>
                <div class="stat-sub">Penjualan − Operasional</div>
                <?php if ($todayKeuntungan > 0): ?>
                <span class="stat-badge up">▲ Profit</span>
                <?php elseif ($todayKeuntungan < 0): ?>
                <span class="stat-badge down">▼ Rugi</span>
                <?php else: ?>
                <span class="stat-badge neutral">— Break Even</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== FILTER BAR ===== -->
        <form class="filter-bar" method="GET" action="index.php">
            <label>🔍 Filter Periode:</label>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <input type="date" name="filter_start" value="<?= h($filter_start) ?>" class="filter-input" placeholder="Dari tanggal">
                <span style="color:var(--cafe-brown);font-weight:500;font-size:13px">s/d</span>
                <input type="date" name="filter_end" value="<?= h($filter_end) ?>" class="filter-input" placeholder="Sampai tanggal">
            </div>
            <button type="submit" class="btn-filter">Terapkan Filter</button>
            <?php if ($filter_start || $filter_end): ?>
            <a href="index.php" class="btn-reset">✕ Reset</a>
            <?php endif; ?>
            <?php if ($filter_start && $filter_end): ?>
            <span style="font-size:12px;color:rgba(28,10,0,0.45);margin-left:4px">
                Menampilkan: <?= formatDate($filter_start) ?> — <?= formatDate($filter_end) ?>
            </span>
            <?php endif; ?>
        </form>

        <!-- ===== SUMMARY AGGREGATION ===== -->
        <div class="summary-row" id="summary-section">
            <div class="summary-item">
                <div class="summary-label">Total Penjualan</div>
                <div class="summary-value"><?= formatRupiah($totalPenjualan) ?></div>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-item">
                <div class="summary-label">Total Operasional</div>
                <div class="summary-value"><?= formatRupiah($totalOperasional) ?></div>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-item">
                <div class="summary-label">Total Keuntungan</div>
                <div class="summary-value summary-profit"><?= formatRupiah($totalKeuntungan) ?></div>
            </div>
            <div class="summary-divider"></div>
            <div class="summary-item">
                <div class="summary-label">Margin Profit</div>
                <div class="summary-value summary-profit"><?= $profitPercent ?>%</div>
            </div>
        </div>

        <!-- ===== CHART ===== -->
        <div class="chart-card" id="chart-section">
            <div class="card-header-custom">
                <div class="card-title-custom">📈 Grafik Pemasukan vs Pengeluaran (7 Hari)</div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#52b788"></div>
                        Penjualan
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#e07a5f"></div>
                        Operasional
                    </div>
                </div>
            </div>
            <canvas id="financeChart" height="90"></canvas>
        </div>

        <!-- ===== DATA TABLES ===== -->
        <div class="tables-grid">
            <!-- Tabel Penjualan -->
            <div class="table-card" id="penjualan">
                <div class="table-card-header section-anchor">
                    <div class="table-section-title">
                        💰 Riwayat Penjualan
                        <span class="table-badge badge-green"><?= count($penjualanList) ?> data</span>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($penjualanList)): ?>
                            <tr class="empty-row"><td colspan="5">📭 Belum ada data penjualan</td></tr>
                        <?php else: ?>
                            <?php foreach ($penjualanList as $i => $row): ?>
                            <tr>
                                <td style="color:rgba(28,10,0,0.35);font-size:11px"><?= $i+1 ?></td>
                                <td style="white-space:nowrap;font-size:12px"><?= formatDate($row['tanggal']) ?></td>
                                <td class="amount-cell amount-green"><?= formatRupiah((float)$row['nominal_penjualan']) ?></td>
                                <td class="desc-cell" title="<?= h($row['keterangan']) ?>"><?= h($row['keterangan'] ?: '—') ?></td>
                                <td style="white-space:nowrap">
                                    <a href="penjualan.php?action=edit&id=<?= $row['id_penjualan'] ?>" class="btn-action btn-edit">✏️</a>
                                    <a href="penjualan.php?action=delete&id=<?= $row['id_penjualan'] ?>"
                                       class="btn-action btn-del"
                                       onclick="return confirm('Hapus data ini?')">🗑️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabel Operasional -->
            <div class="table-card" id="operasional">
                <div class="table-card-header section-anchor">
                    <div class="table-section-title">
                        🧾 Riwayat Operasional
                        <span class="table-badge badge-red"><?= count($operasionalList) ?> data</span>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Nominal</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($operasionalList)): ?>
                            <tr class="empty-row"><td colspan="5">📭 Belum ada data operasional</td></tr>
                        <?php else: ?>
                            <?php foreach ($operasionalList as $i => $row): ?>
                            <tr>
                                <td style="color:rgba(28,10,0,0.35);font-size:11px"><?= $i+1 ?></td>
                                <td style="white-space:nowrap;font-size:12px"><?= formatDate($row['tanggal']) ?></td>
                                <td class="amount-cell amount-red"><?= formatRupiah((float)$row['nominal_operasional']) ?></td>
                                <td class="desc-cell" title="<?= h($row['keterangan']) ?>"><?= h($row['keterangan'] ?: '—') ?></td>
                                <td style="white-space:nowrap">
                                    <a href="operasional.php?action=edit&id=<?= $row['id_operasional'] ?>" class="btn-action btn-edit">✏️</a>
                                    <a href="operasional.php?action=delete&id=<?= $row['id_operasional'] ?>"
                                       class="btn-action btn-del"
                                       onclick="return confirm('Hapus data ini?')">🗑️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===== INPUT FORMS ===== -->
        <div class="forms-grid">
            <!-- Form Penjualan -->
            <div class="form-card">
                <div class="form-card-title">
                    <span class="form-icon green">💰</span>
                    <?= $editPenjualan ? 'Edit Data Penjualan' : 'Tambah Penjualan' ?>
                </div>

                <?php if ($editPenjualan): ?>
                <div class="form-editing-indicator">
                    ✏️ Mode Edit — ID #<?= $editPenjualan['id_penjualan'] ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="penjualan.php?action=<?= $editPenjualan ? 'update' : 'create' ?>"
                      onsubmit="return validateForm(this, 'nominal_penjualan')">
                    <?php if ($editPenjualan): ?>
                    <input type="hidden" name="id" value="<?= $editPenjualan['id_penjualan'] ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Tanggal *</label>
                        <input type="date" name="tanggal"
                               value="<?= $editPenjualan ? h($editPenjualan['tanggal']) : date('Y-m-d') ?>"
                               class="form-control-custom" required max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Nominal Penjualan (Rp) *</label>
                        <input type="number" name="nominal_penjualan" step="100" min="1"
                               value="<?= $editPenjualan ? h($editPenjualan['nominal_penjualan']) : '' ?>"
                               class="form-control-custom" placeholder="Contoh: 1500000" required>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="2" class="form-control-custom"
                                  placeholder="Deskripsi singkat penjualan..."><?= $editPenjualan ? h($editPenjualan['keterangan']) : '' ?></textarea>
                    </div>
                    <button type="submit" class="btn-submit green">
                        <?= $editPenjualan ? '💾 Simpan Perubahan' : '+ Tambah Penjualan' ?>
                    </button>
                    <?php if ($editPenjualan): ?>
                    <a href="index.php#penjualan" class="btn-cancel">✕ Batal</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Form Operasional -->
            <div class="form-card">
                <div class="form-card-title">
                    <span class="form-icon red">🧾</span>
                    <?= $editOperasional ? 'Edit Data Operasional' : 'Tambah Operasional' ?>
                </div>

                <?php if ($editOperasional): ?>
                <div class="form-editing-indicator">
                    ✏️ Mode Edit — ID #<?= $editOperasional['id_operasional'] ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="operasional.php?action=<?= $editOperasional ? 'update' : 'create' ?>"
                      onsubmit="return validateForm(this, 'nominal_operasional')">
                    <?php if ($editOperasional): ?>
                    <input type="hidden" name="id" value="<?= $editOperasional['id_operasional'] ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label>Tanggal *</label>
                        <input type="date" name="tanggal"
                               value="<?= $editOperasional ? h($editOperasional['tanggal']) : date('Y-m-d') ?>"
                               class="form-control-custom" required max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Nominal Operasional (Rp) *</label>
                        <input type="number" name="nominal_operasional" step="100" min="1"
                               value="<?= $editOperasional ? h($editOperasional['nominal_operasional']) : '' ?>"
                               class="form-control-custom" placeholder="Contoh: 500000" required>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="2" class="form-control-custom"
                                  placeholder="Deskripsi pengeluaran..."><?= $editOperasional ? h($editOperasional['keterangan']) : '' ?></textarea>
                    </div>
                    <button type="submit" class="btn-submit red">
                        <?= $editOperasional ? '💾 Simpan Perubahan' : '+ Tambah Operasional' ?>
                    </button>
                    <?php if ($editOperasional): ?>
                    <a href="index.php#operasional" class="btn-cancel">✕ Batal</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

    </div><!-- /page-content -->
</main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ===== SIDEBAR TOGGLE =====
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}

// ===== FORM VALIDATION =====
function validateForm(form, nominalField) {
    const nominal = parseFloat(form[nominalField].value);
    if (isNaN(nominal) || nominal <= 0) {
        alert('Nominal harus berupa angka positif lebih dari 0!');
        form[nominalField].focus();
        return false;
    }
    const tanggal = form.tanggal.value;
    if (!tanggal) {
        alert('Tanggal wajib diisi!');
        return false;
    }
    return true;
}

// ===== AUTO DISMISS ALERTS =====
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.custom-alert');
    alerts.forEach(a => {
        setTimeout(() => {
            a.style.transition = 'opacity .5s ease, transform .5s ease';
            a.style.opacity = '0';
            a.style.transform = 'translateX(120%)';
            setTimeout(() => a.remove(), 500);
        }, 4500);
    });
});

// ===== CHART.JS =====
const ctx = document.getElementById('financeChart').getContext('2d');

const chartLabels = <?= json_encode($chartLabels) ?>;
const chartPData  = <?= json_encode($chartPData)  ?>;
const chartOData  = <?= json_encode($chartOData)  ?>;

const gradientGreen = ctx.createLinearGradient(0, 0, 0, 300);
gradientGreen.addColorStop(0, 'rgba(82,183,136,0.35)');
gradientGreen.addColorStop(1, 'rgba(82,183,136,0.00)');

const gradientRed = ctx.createLinearGradient(0, 0, 0, 300);
gradientRed.addColorStop(0, 'rgba(224,122,95,0.30)');
gradientRed.addColorStop(1, 'rgba(224,122,95,0.00)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [
            {
                label: 'Penjualan',
                data: chartPData,
                borderColor: '#52b788',
                backgroundColor: gradientGreen,
                borderWidth: 2.5,
                pointBackgroundColor: '#52b788',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.42
            },
            {
                label: 'Operasional',
                data: chartOData,
                borderColor: '#e07a5f',
                backgroundColor: gradientRed,
                borderWidth: 2.5,
                pointBackgroundColor: '#e07a5f',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.42
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1C0A00',
                titleColor: '#F5ECD7',
                bodyColor: 'rgba(245,236,215,0.75)',
                padding: 12,
                cornerRadius: 10,
                callbacks: {
                    label: ctx => {
                        const v = ctx.parsed.y;
                        return '  ' + ctx.dataset.label + ': Rp ' + v.toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { color: 'rgba(107,58,42,0.07)' },
                ticks: { color: 'rgba(28,10,0,0.5)', font: { size: 11, family: 'DM Sans' } }
            },
            y: {
                grid: { color: 'rgba(107,58,42,0.07)' },
                ticks: {
                    color: 'rgba(28,10,0,0.5)',
                    font: { size: 11, family: 'DM Sans' },
                    callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt'
                },
                beginAtZero: true
            }
        }
    }
});

// ===== SMOOTH SCROLL FOR SIDEBAR LINKS =====
document.querySelectorAll('.nav-link[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const target = document.querySelector(link.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        // Close sidebar on mobile
        if (window.innerWidth < 768) toggleSidebar();
    });
});
</script>
</body>
</html>
