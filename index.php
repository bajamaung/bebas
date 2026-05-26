<?php
// index.php — Dashboard Utama Nada & Cafe
require_once __DIR__ . '/includes/helpers.php';

$stats     = getStatistikHariIni();
$chartData = getChartData();
$monthly   = getMonthlySummary(6);
$today     = date('Y-m-d');
$page      = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Nada &amp; Cafe — Finance</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
  :root {
    --g1: #0D1F0F;
    --g2: #1A3320;
    --g3: #2A5232;
    --g4: #3A7048;
    --g5: #4E9060;
    --g-accent: #6BBF80;
    --g-light: #A8D5B5;

    --p1: #FF6B9D;
    --p2: #FF8FB3;
    --p3: #FFB3CC;
    --p-deep: #D94F82;

    --black: #080C09;
    --near-black: #0F1A11;
    --card-bg: rgba(15, 30, 18, 0.75);
    --card-border: rgba(107, 191, 128, 0.15);
    --card-border-pink: rgba(255, 107, 157, 0.2);

    --text-primary: #E8F5EC;
    --text-secondary: #8DB89A;
    --text-muted: #506858;

    --glow-green: 0 0 30px rgba(107,191,128,0.25);
    --glow-pink: 0 0 30px rgba(255,107,157,0.25);
    --shadow-deep: 0 24px 64px rgba(0,0,0,0.6);

    --glass: rgba(20, 40, 24, 0.6);
    --glass-border: rgba(107,191,128,0.12);
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--black);
    color: var(--text-primary);
    min-height: 100vh;
    overflow-x: hidden;
  }

  /* ── Animated Background ── */
  body::before {
    content: '';
    position: fixed; inset: 0; z-index: -2;
    background:
      radial-gradient(ellipse 70% 50% at 15% 10%, rgba(42,82,50,0.55) 0%, transparent 60%),
      radial-gradient(ellipse 50% 70% at 85% 90%, rgba(26,51,32,0.45) 0%, transparent 60%),
      radial-gradient(ellipse 40% 40% at 80% 15%, rgba(217,79,130,0.08) 0%, transparent 50%),
      radial-gradient(ellipse 60% 40% at 20% 85%, rgba(255,107,157,0.06) 0%, transparent 50%),
      var(--near-black);
  }

  /* Subtle noise texture */
  body::after {
    content: '';
    position: fixed; inset: 0; z-index: -1;
    opacity: 0.03;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
    pointer-events: none;
  }

  /* ── Typography ── */
  .font-display { font-family: 'Playfair Display', serif; }

  /* ── Sidebar ── */
  #sidebar {
    width: 272px; flex-shrink: 0;
    background: rgba(8, 18, 10, 0.92);
    backdrop-filter: blur(24px);
    border-right: 1px solid var(--glass-border);
    min-height: 100vh;
    position: sticky; top: 0;
    display: flex; flex-direction: column;
    box-shadow: 4px 0 40px rgba(0,0,0,0.4);
  }

  .logo-area {
    padding: 28px 24px 22px;
    border-bottom: 1px solid rgba(107,191,128,0.1);
    position: relative;
    overflow: hidden;
  }

  .logo-area::before {
    content: '';
    position: absolute; top: -20px; right: -20px;
    width: 100px; height: 100px;
    background: radial-gradient(circle, rgba(255,107,157,0.12), transparent 70%);
    border-radius: 50%;
  }

  .logo-icon {
    width: 44px; height: 44px; border-radius: 14px;
    background: linear-gradient(135deg, var(--g4), var(--p-deep));
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    box-shadow: 0 4px 20px rgba(107,191,128,0.3), 0 0 0 1px rgba(107,191,128,0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .logo-icon:hover {
    transform: rotate(-5deg) scale(1.05);
    box-shadow: 0 8px 30px rgba(255,107,157,0.4), 0 0 0 1px rgba(255,107,157,0.3);
  }

  .nav-section-label {
    padding: 16px 20px 6px;
    font-size: 9px; font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.14em;
  }

  .nav-item {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 16px; margin: 2px 10px; border-radius: 12px;
    color: var(--text-secondary); font-size: 13.5px; font-weight: 400;
    text-decoration: none;
    cursor: pointer; border: none; background: transparent;
    width: calc(100% - 20px); text-align: left;
    position: relative; overflow: hidden;
    transition: color 0.25s ease;
  }

  .nav-item::before {
    content: '';
    position: absolute; inset: 0; border-radius: 12px;
    background: linear-gradient(135deg, rgba(107,191,128,0.08), rgba(255,107,157,0.06));
    opacity: 0;
    transition: opacity 0.25s ease;
    transform: translateX(-100%);
    transition: opacity 0.25s ease, transform 0.35s ease;
  }

  .nav-item:hover::before { opacity: 1; transform: translateX(0); }
  .nav-item:hover {
    color: var(--text-primary);
    transform: translateX(4px);
    transition: color 0.2s, transform 0.25s cubic-bezier(0.34,1.56,0.64,1);
  }

  .nav-item.active {
    background: linear-gradient(135deg, rgba(107,191,128,0.15), rgba(255,107,157,0.1));
    color: var(--text-primary); font-weight: 500;
    border: 1px solid rgba(107,191,128,0.2);
    box-shadow: 0 4px 16px rgba(0,0,0,0.2), inset 0 1px 0 rgba(107,191,128,0.15);
  }

  .nav-item.active .nav-icon { filter: drop-shadow(0 0 6px rgba(107,191,128,0.6)); }

  .nav-icon {
    font-size: 17px; width: 22px; text-align: center;
    transition: transform 0.25s ease, filter 0.25s ease;
  }
  .nav-item:hover .nav-icon { transform: scale(1.15); }

  /* ── Glass Card ── */
  .glass-card {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--card-border);
    border-radius: 20px;
    box-shadow: var(--shadow-deep), inset 0 1px 0 rgba(107,191,128,0.08);
    position: relative;
    overflow: hidden;
    transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1),
                box-shadow 0.4s ease,
                border-color 0.4s ease;
  }
  .glass-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(107,191,128,0.3), rgba(255,107,157,0.2), transparent);
    opacity: 0;
    transition: opacity 0.4s ease;
  }
  .glass-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-deep), var(--glow-green), inset 0 1px 0 rgba(107,191,128,0.15);
    border-color: rgba(107,191,128,0.3);
  }
  .glass-card:hover::before { opacity: 1; }

  /* ── Stat Cards ── */
  .stat-card {
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border: 1px solid var(--card-border);
    border-radius: 22px; padding: 26px;
    position: relative; overflow: hidden;
    transition: all 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
    cursor: default;
  }

  .stat-card::after {
    content: '';
    position: absolute; inset: 0; border-radius: 22px;
    opacity: 0;
    transition: opacity 0.4s ease;
    pointer-events: none;
  }

  .stat-card:hover {
    transform: translateY(-8px) scale(1.01);
    border-color: rgba(107,191,128,0.35);
    box-shadow: var(--shadow-deep), var(--glow-green);
  }

  .stat-card.pink:hover {
    border-color: rgba(255,107,157,0.35);
    box-shadow: var(--shadow-deep), var(--glow-pink);
  }

  .stat-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(24px);
    transition: transform 0.5s ease, opacity 0.4s ease;
    pointer-events: none;
  }

  .stat-card:hover .stat-orb { transform: scale(1.4); opacity: 0.9; }

  .stat-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 11px; border-radius: 20px; font-size: 11px; font-weight: 500;
    letter-spacing: 0.03em;
  }

  /* ── Icon Box ── */
  .icon-box {
    width: 44px; height: 44px; border-radius: 13px;
    display: flex; align-items: center; justify-content: center; font-size: 19px;
    transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.35s ease;
  }
  .stat-card:hover .icon-box {
    transform: rotate(-8deg) scale(1.1);
    box-shadow: 0 8px 20px rgba(107,191,128,0.35);
  }
  .stat-card.pink:hover .icon-box {
    box-shadow: 0 8px 20px rgba(255,107,157,0.35);
  }

  /* ── Buttons ── */
  .btn-primary {
    background: linear-gradient(135deg, var(--g4) 0%, var(--g5) 50%, var(--g-accent) 100%);
    background-size: 200% 200%;
    color: #fff; border: none; border-radius: 13px;
    padding: 11px 24px; font-family: 'DM Sans', sans-serif;
    font-size: 13.5px; font-weight: 500; cursor: pointer;
    box-shadow: 0 4px 20px rgba(74,144,96,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
    transition: all 0.35s cubic-bezier(0.34,1.56,0.64,1);
    background-position: 100% 100%;
    position: relative; overflow: hidden;
    letter-spacing: 0.02em;
  }

  .btn-primary::before {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
    opacity: 0;
    transition: opacity 0.25s;
  }

  .btn-primary:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 10px 35px rgba(74,144,96,0.55), 0 0 20px rgba(107,191,128,0.3);
    background-position: 0% 0%;
  }
  .btn-primary:hover::before { opacity: 1; }
  .btn-primary:active { transform: translateY(-1px) scale(0.99); }

  .btn-pink {
    background: linear-gradient(135deg, var(--p-deep), var(--p1), var(--p2));
    background-size: 200% 200%;
    color: #fff; border: none; border-radius: 13px;
    padding: 11px 24px; font-family: 'DM Sans', sans-serif;
    font-size: 13.5px; font-weight: 500; cursor: pointer;
    box-shadow: 0 4px 20px rgba(217,79,130,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
    transition: all 0.35s cubic-bezier(0.34,1.56,0.64,1);
    background-position: 100% 100%;
    position: relative; overflow: hidden;
  }
  .btn-pink:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 10px 35px rgba(217,79,130,0.55), 0 0 20px rgba(255,107,157,0.3);
    background-position: 0% 0%;
  }
  .btn-pink:active { transform: translateY(-1px) scale(0.99); }

  .btn-ghost {
    background: rgba(107,191,128,0.08);
    color: var(--text-secondary);
    border: 1px solid rgba(107,191,128,0.2); border-radius: 13px;
    padding: 10px 20px; font-family: 'DM Sans', sans-serif;
    font-size: 13px; font-weight: 400; cursor: pointer;
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    position: relative; overflow: hidden;
  }
  .btn-ghost::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(107,191,128,0.1), rgba(255,107,157,0.08));
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  .btn-ghost:hover {
    color: var(--text-primary);
    border-color: rgba(107,191,128,0.4);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
  }
  .btn-ghost:hover::after { opacity: 1; }

  .btn-danger {
    background: rgba(192,57,43,0.1); color: #ff7979;
    border: 1px solid rgba(192,57,43,0.25); border-radius: 10px;
    padding: 6px 14px; font-size: 12px; cursor: pointer;
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    position: relative; overflow: hidden;
  }
  .btn-danger:hover {
    background: rgba(192,57,43,0.22);
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 6px 16px rgba(192,57,43,0.3);
    border-color: rgba(192,57,43,0.4);
  }

  .btn-edit {
    background: rgba(107,191,128,0.1); color: var(--g-accent);
    border: 1px solid rgba(107,191,128,0.25); border-radius: 10px;
    padding: 6px 14px; font-size: 12px; cursor: pointer;
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    position: relative; overflow: hidden;
  }
  .btn-edit:hover {
    background: rgba(107,191,128,0.2);
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 6px 16px rgba(107,191,128,0.25);
    border-color: rgba(107,191,128,0.45);
  }

  /* ── Form Inputs ── */
  .form-input {
    width: 100%; padding: 11px 16px;
    background: rgba(15,30,18,0.8); backdrop-filter: blur(8px);
    border: 1px solid var(--glass-border); border-radius: 12px;
    font-family: 'DM Sans', sans-serif; font-size: 14px;
    color: var(--text-primary);
    transition: all 0.3s ease; outline: none;
  }
  .form-input:focus {
    border-color: var(--g-accent);
    box-shadow: 0 0 0 3px rgba(107,191,128,0.12), 0 4px 12px rgba(0,0,0,0.3);
    background: rgba(20,40,24,0.9);
  }
  .form-input::placeholder { color: var(--text-muted); }
  .form-label {
    display: block; font-size: 11px; font-weight: 600;
    color: var(--text-muted); margin-bottom: 6px;
    text-transform: uppercase; letter-spacing: 0.09em;
  }

  /* ── Table ── */
  .data-table { width: 100%; border-collapse: separate; border-spacing: 0 5px; }
  .data-table thead th {
    padding: 10px 16px; text-align: left; font-size: 10px;
    font-weight: 600; color: var(--text-muted); text-transform: uppercase;
    letter-spacing: 0.09em; border-bottom: 1px solid var(--glass-border);
  }
  .data-table tbody tr {
    background: rgba(15,30,18,0.5);
    backdrop-filter: blur(8px);
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
  }
  .data-table tbody tr:hover {
    background: rgba(107,191,128,0.07);
    transform: scale(1.005) translateX(4px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
  }
  .data-table tbody td {
    padding: 13px 16px; font-size: 13.5px;
    border-top: 1px solid rgba(107,191,128,0.06);
    border-bottom: 1px solid rgba(107,191,128,0.06);
    transition: color 0.2s ease;
  }
  .data-table tbody tr:hover td { color: var(--text-primary); }
  .data-table tbody td:first-child { border-left: 1px solid rgba(107,191,128,0.06); border-radius: 11px 0 0 11px; }
  .data-table tbody td:last-child  { border-right: 1px solid rgba(107,191,128,0.06); border-radius: 0 11px 11px 0; }

  /* ── Modal ── */
  .modal-overlay {
    position: fixed; inset: 0; z-index: 50;
    background: rgba(8,12,9,0.75); backdrop-filter: blur(10px);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none; transition: opacity 0.35s ease;
  }
  .modal-overlay.open { opacity: 1; pointer-events: all; }
  .modal-box {
    background: rgba(10,20,12,0.97); backdrop-filter: blur(30px);
    border: 1px solid rgba(107,191,128,0.2); border-radius: 26px;
    padding: 34px; width: 500px; max-width: 92vw;
    box-shadow: var(--shadow-deep), var(--glow-green);
    transform: translateY(24px) scale(0.96);
    transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1);
    position: relative; overflow: hidden;
  }
  .modal-box::before {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(255,107,157,0.07), transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }
  .modal-overlay.open .modal-box { transform: translateY(0) scale(1); }

  /* ── Toast ── */
  #toast {
    position: fixed; bottom: 30px; right: 30px; z-index: 100;
    padding: 14px 22px; border-radius: 16px;
    font-size: 13.5px; font-weight: 500; min-width: 260px;
    transform: translateY(80px) scale(0.9); opacity: 0;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    pointer-events: none; letter-spacing: 0.02em;
  }
  #toast.show { transform: translateY(0) scale(1); opacity: 1; }
  #toast.success {
    background: linear-gradient(135deg, rgba(58,112,72,0.95), rgba(107,191,128,0.9));
    color: #fff;
    border: 1px solid rgba(107,191,128,0.4);
    box-shadow: var(--shadow-deep), var(--glow-green);
  }
  #toast.error {
    background: linear-gradient(135deg, rgba(180,50,80,0.95), rgba(220,80,100,0.9));
    color: white;
    border: 1px solid rgba(220,80,100,0.4);
    box-shadow: var(--shadow-deep), 0 0 30px rgba(220,80,100,0.25);
  }

  /* ── Section Transitions ── */
  .page-section { display: none; }
  .page-section.active { display: block; animation: pageFadeIn 0.5s ease both; }
  @keyframes pageFadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* ── Divider ── */
  .accent-divider {
    height: 2px; width: 52px;
    background: linear-gradient(90deg, var(--g-accent), var(--p1));
    border-radius: 2px; margin: 6px 0 20px;
    position: relative;
  }
  .accent-divider::after {
    content: '';
    position: absolute; right: -8px; top: -2px;
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--p1);
    box-shadow: 0 0 8px var(--p1);
  }

  /* ── Scrollbar ── */
  ::-webkit-scrollbar { width: 5px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: rgba(107,191,128,0.25); border-radius: 3px; }
  ::-webkit-scrollbar-thumb:hover { background: rgba(107,191,128,0.45); }

  /* ── Profit ── */
  .profit-positive { color: var(--g-accent); }
  .profit-negative { color: var(--p1); }

  /* ── Quick action cards ── */
  .quick-btn {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 20px; border-radius: 16px;
    font-family: 'DM Sans', sans-serif; font-size: 13.5px; font-weight: 500;
    cursor: pointer; border: none;
    transition: all 0.4s cubic-bezier(0.34,1.56,0.64,1);
    position: relative; overflow: hidden;
  }

  .quick-btn-green {
    background: linear-gradient(135deg, rgba(42,82,50,0.8), rgba(58,112,72,0.6));
    color: var(--g-accent);
    border: 1px solid rgba(107,191,128,0.2);
  }
  .quick-btn-green:hover {
    background: linear-gradient(135deg, rgba(42,82,50,1), rgba(78,144,96,0.8));
    color: #fff;
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 30px rgba(0,0,0,0.4), var(--glow-green);
    border-color: rgba(107,191,128,0.4);
  }

  .quick-btn-pink {
    background: linear-gradient(135deg, rgba(80,30,50,0.8), rgba(120,40,70,0.6));
    color: var(--p2);
    border: 1px solid rgba(255,107,157,0.2);
  }
  .quick-btn-pink:hover {
    background: linear-gradient(135deg, rgba(140,50,90,1), rgba(200,70,120,0.8));
    color: #fff;
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 30px rgba(0,0,0,0.4), var(--glow-pink);
    border-color: rgba(255,107,157,0.4);
  }

  .quick-btn-ghost {
    background: rgba(255,255,255,0.03);
    color: var(--text-secondary);
    border: 1px solid var(--glass-border);
  }
  .quick-btn-ghost:hover {
    background: rgba(107,191,128,0.08);
    color: var(--text-primary);
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 30px rgba(0,0,0,0.4);
    border-color: rgba(107,191,128,0.3);
  }

  /* ── Mobile ── */
  @media (max-width: 768px) {
    #sidebar { display: none; }
    #sidebar.mobile-open {
      display: flex; position: fixed; left: 0; top: 0;
      z-index: 40; height: 100vh;
      box-shadow: 8px 0 40px rgba(0,0,0,0.6);
    }
    .stat-value { font-size: 1.6rem !important; }
  }

  /* Chart containers */
  .chart-wrapper {
    position: relative;
    width: 100%;
  }

  /* Ripple effect on click */
  .ripple {
    position: absolute;
    border-radius: 50%;
    transform: scale(0);
    animation: ripple-anim 0.6s linear;
    background-color: rgba(107,191,128,0.25);
    pointer-events: none;
  }
  @keyframes ripple-anim {
    to { transform: scale(4); opacity: 0; }
  }
</style>
</head>
<body>

<!-- Toast Notification -->
<div id="toast"></div>

<!-- Mobile backdrop -->
<div id="mobile-backdrop" class="fixed inset-0 z-30" style="background:rgba(0,0,0,0.5); display:none;" onclick="closeSidebar()"></div>

<div class="flex" style="min-height:100vh;">

  <!-- ══ SIDEBAR ══ -->
  <aside id="sidebar">
    <!-- Logo -->
    <div class="logo-area">
      <div class="flex items-center gap-3 mb-1">
        <div class="logo-icon">🌿</div>
        <div>
          <div class="font-display text-xl font-semibold" style="color:var(--text-primary); line-height:1.1;">Nada &amp; Cafe</div>
          <div style="font-size:9px; color:var(--text-muted); letter-spacing:0.15em; text-transform:uppercase;">Finance Dashboard</div>
        </div>
      </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 py-4 overflow-y-auto">
      <div class="nav-section-label">Menu Utama</div>

      <button class="nav-item active" id="nav-dashboard" onclick="showPage('dashboard')">
        <span class="nav-icon">📊</span> Dashboard
      </button>
      <button class="nav-item" id="nav-penjualan" onclick="showPage('penjualan')">
        <span class="nav-icon">💰</span> Penjualan
      </button>
      <button class="nav-item" id="nav-operasional" onclick="showPage('operasional')">
        <span class="nav-icon">📋</span> Operasional
      </button>
      <button class="nav-item" id="nav-laporan" onclick="showPage('laporan')">
        <span class="nav-icon">📈</span> Laporan &amp; Analitik
      </button>

      <div class="nav-section-label" style="margin-top:8px;">Lainnya</div>
      <button class="nav-item" id="nav-settings" onclick="showPage('settings')">
        <span class="nav-icon">⚙️</span> Pengaturan
      </button>
    </nav>

    <!-- Bottom -->
    <div style="padding:18px 20px; border-top:1px solid var(--glass-border);">
      <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.1em; margin-bottom:4px;">Hari ini</div>
      <div style="font-size:13px; font-weight:500; color:var(--text-secondary);">
        <?= date('l, d F Y') ?>
      </div>
      <div style="margin-top:12px; height:3px; border-radius:3px; background:linear-gradient(90deg, var(--g4), var(--p1)); box-shadow: 0 0 8px rgba(107,191,128,0.4);"></div>
    </div>
  </aside>

  <!-- ══ MAIN CONTENT ══ -->
  <main class="flex-1 overflow-y-auto" style="min-width:0;">

    <!-- Top Bar -->
    <header style="padding:18px 32px; background:rgba(8,12,9,0.85); backdrop-filter:blur(20px); border-bottom:1px solid var(--glass-border); position:sticky; top:0; z-index:20; display:flex; align-items:center; justify-content:space-between;">
      <div class="flex items-center gap-4">
        <button style="display:none;" class="btn-ghost" id="mobile-menu-btn" onclick="toggleSidebar()" style="padding:9px 13px; font-size:18px;">☰</button>
        <div>
          <div id="page-title" class="font-display text-2xl font-semibold" style="color:var(--text-primary); letter-spacing:-0.01em;">Dashboard</div>
          <div id="page-sub" style="font-size:12px; color:var(--text-muted); margin-top:1px;">Ringkasan keuangan hari ini</div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <button class="btn-primary" onclick="showPage('penjualan'); setTimeout(()=>openModal('modal-add-penjualan'),100)">
          + Tambah Data
        </button>
      </div>
    </header>

    <div style="padding:28px 32px;">

      <!-- ════════════ DASHBOARD PAGE ════════════ -->
      <section id="page-dashboard" class="page-section active">

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">

          <!-- Penjualan -->
          <div class="stat-card">
            <div class="stat-orb" style="width:130px; height:130px; top:-40px; right:-40px; background:radial-gradient(circle, rgba(107,191,128,0.2), transparent 70%);"></div>
            <div class="flex items-start justify-between mb-5">
              <div>
                <p style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.1em;">Total Penjualan</p>
                <p style="font-size:11px; color:var(--text-secondary); margin-top:3px;">Hari Ini</p>
              </div>
              <div class="icon-box" style="background:linear-gradient(135deg,rgba(42,82,50,0.8),rgba(107,191,128,0.3)); border:1px solid rgba(107,191,128,0.2);">☕</div>
            </div>
            <div class="font-display stat-value" style="font-size:2rem; font-weight:700; color:var(--text-primary); margin-bottom:12px; letter-spacing:-0.02em;">
              <?= formatRupiah($stats['penjualan']) ?>
            </div>
            <span class="stat-badge" style="background:rgba(107,191,128,0.12); color:var(--g-accent); border:1px solid rgba(107,191,128,0.2);">
              ↑ Pemasukan
            </span>
          </div>

          <!-- Operasional -->
          <div class="stat-card pink">
            <div class="stat-orb" style="width:130px; height:130px; top:-40px; right:-40px; background:radial-gradient(circle, rgba(255,107,157,0.18), transparent 70%);"></div>
            <div class="flex items-start justify-between mb-5">
              <div>
                <p style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.1em;">Total Pengeluaran</p>
                <p style="font-size:11px; color:var(--text-secondary); margin-top:3px;">Operasional Hari Ini</p>
              </div>
              <div class="icon-box" style="background:linear-gradient(135deg,rgba(80,30,50,0.8),rgba(255,107,157,0.25)); border:1px solid rgba(255,107,157,0.2);">🧾</div>
            </div>
            <div class="font-display stat-value" style="font-size:2rem; font-weight:700; color:var(--text-primary); margin-bottom:12px; letter-spacing:-0.02em;">
              <?= formatRupiah($stats['operasional']) ?>
            </div>
            <span class="stat-badge" style="background:rgba(255,107,157,0.1); color:var(--p1); border:1px solid rgba(255,107,157,0.2);">
              ↓ Pengeluaran
            </span>
          </div>

          <!-- Profit -->
          <div class="stat-card" style="background:linear-gradient(135deg, rgba(20,45,25,0.9), rgba(40,70,45,0.7));">
            <div class="stat-orb" style="width:160px; height:160px; top:-50px; right:-50px; background:radial-gradient(circle, rgba(107,191,128,0.25), rgba(255,107,157,0.1) 60%, transparent 80%);"></div>
            <div class="flex items-start justify-between mb-5">
              <div>
                <p style="font-size:10px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.1em;">Profit Bersih</p>
                <p style="font-size:11px; color:var(--text-secondary); margin-top:3px;">Penjualan − Operasional</p>
              </div>
              <div class="icon-box" style="background:linear-gradient(135deg,var(--g3),var(--p-deep)); border:1px solid rgba(107,191,128,0.3); box-shadow:0 4px 16px rgba(107,191,128,0.25);">✨</div>
            </div>
            <div class="font-display stat-value <?= $stats['profit'] >= 0 ? 'profit-positive' : 'profit-negative' ?>" style="font-size:2rem; font-weight:700; margin-bottom:12px; letter-spacing:-0.02em;">
              <?= formatRupiah($stats['profit']) ?>
            </div>
            <span class="stat-badge" style="background:rgba(255,255,255,0.07); color:var(--text-secondary); border:1px solid rgba(255,255,255,0.1);">
              <?= $stats['profit'] >= 0 ? '🌟 Profit' : '⚠️ Rugi' ?> Hari Ini
            </span>
          </div>
        </div>

        <!-- Chart & Bulan -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">
          <!-- Chart 7 Hari -->
          <div class="glass-card lg:col-span-2" style="padding:26px;">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="font-display text-xl font-semibold" style="color:var(--text-primary); letter-spacing:-0.01em;">Performa 7 Hari</h3>
                <div class="accent-divider"></div>
              </div>
              <div style="font-size:11px; color:var(--text-muted); background:rgba(107,191,128,0.08); padding:5px 12px; border-radius:20px; border:1px solid var(--glass-border);">Pemasukan vs Pengeluaran</div>
            </div>
            <div class="chart-wrapper">
              <canvas id="chart-weekly"></canvas>
            </div>
          </div>

          <!-- Ringkasan Bulan Ini -->
          <div class="glass-card" style="padding:26px;">
            <h3 class="font-display text-xl font-semibold" style="color:var(--text-primary); letter-spacing:-0.01em;">Bulan Ini</h3>
            <div class="accent-divider"></div>
            <?php
              $thisMonth = date('m'); $thisYear = date('Y');
              $pdo = getConnection();
              $sp = $pdo->prepare("SELECT COALESCE(SUM(nominal_penjualan),0) FROM tabel_penjualan WHERE MONTH(tanggal)=? AND YEAR(tanggal)=?");
              $sp->execute([$thisMonth, $thisYear]);
              $mP = (float)$sp->fetchColumn();
              $so = $pdo->prepare("SELECT COALESCE(SUM(nominal_operasional),0) FROM tabel_operasional WHERE MONTH(tanggal)=? AND YEAR(tanggal)=?");
              $so->execute([$thisMonth, $thisYear]);
              $mO = (float)$so->fetchColumn();
              $mProfit = $mP - $mO;
            ?>
            <div>
              <div style="padding:14px 0; border-bottom:1px solid var(--glass-border);">
                <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.09em; margin-bottom:6px;">Penjualan</div>
                <div class="font-display text-xl font-semibold profit-positive"><?= formatRupiah($mP) ?></div>
              </div>
              <div style="padding:14px 0; border-bottom:1px solid var(--glass-border);">
                <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.09em; margin-bottom:6px;">Operasional</div>
                <div class="font-display text-xl font-semibold profit-negative"><?= formatRupiah($mO) ?></div>
              </div>
              <div style="padding:14px 0;">
                <div style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.09em; margin-bottom:6px;">Net Profit</div>
                <div class="font-display text-2xl font-bold <?= $mProfit >= 0 ? 'profit-positive' : 'profit-negative' ?>" style="text-shadow:<?= $mProfit >= 0 ? '0 0 20px rgba(107,191,128,0.4)' : '0 0 20px rgba(255,107,157,0.4)' ?>">
                  <?= formatRupiah($mProfit) ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="glass-card" style="padding:24px;">
          <h3 class="font-display text-lg font-semibold" style="color:var(--text-primary); margin-bottom:16px; letter-spacing:-0.01em;">Aksi Cepat</h3>
          <div class="flex flex-wrap gap-3">
            <button class="quick-btn quick-btn-green" onclick="addRipple(event); showPage('penjualan'); setTimeout(()=>openModal('modal-add-penjualan'),100)">
              <span style="font-size:16px;">💰</span> Tambah Penjualan
            </button>
            <button class="quick-btn quick-btn-pink" onclick="addRipple(event); showPage('operasional'); setTimeout(()=>openModal('modal-add-operasional'),100)">
              <span style="font-size:16px;">🧾</span> Tambah Operasional
            </button>
            <button class="quick-btn quick-btn-ghost" onclick="addRipple(event); showPage('laporan')">
              <span style="font-size:16px;">📈</span> Lihat Laporan
            </button>
          </div>
        </div>

      </section>

      <!-- ════════════ PENJUALAN PAGE ════════════ -->
      <section id="page-penjualan" class="page-section">

        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="font-display text-2xl font-semibold" style="color:var(--text-primary); letter-spacing:-0.01em;">Data Penjualan</h2>
            <div class="accent-divider"></div>
          </div>
          <button class="btn-primary" onclick="openModal('modal-add-penjualan')">+ Tambah Penjualan</button>
        </div>

        <div class="glass-card" style="padding:20px; margin-bottom:20px;">
          <div class="flex flex-wrap gap-4 items-end">
            <div>
              <label class="form-label">Dari Tanggal</label>
              <input type="date" id="filter-p-from" class="form-input" style="width:165px;" value="<?= date('Y-m-01') ?>"/>
            </div>
            <div>
              <label class="form-label">Sampai Tanggal</label>
              <input type="date" id="filter-p-to" class="form-input" style="width:165px;" value="<?= $today ?>"/>
            </div>
            <button class="btn-primary" onclick="loadPenjualan()">Filter</button>
            <button class="btn-ghost" onclick="resetFilterP()">Reset</button>
          </div>
        </div>

        <div class="glass-card" style="padding:18px 24px; margin-bottom:20px; display:flex; gap:36px; flex-wrap:wrap; border-color:rgba(107,191,128,0.2);">
          <div>
            <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.09em;">Total Periode</span><br>
            <span id="sum-p-total" class="font-display text-xl font-semibold profit-positive">Rp 0</span>
          </div>
          <div style="width:1px; background:var(--glass-border);"></div>
          <div>
            <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.09em;">Jumlah Transaksi</span><br>
            <span id="sum-p-count" class="font-display text-xl font-semibold" style="color:var(--text-primary);">0</span>
          </div>
        </div>

        <div class="glass-card" style="padding:20px; overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr><th>No</th><th>Tanggal</th><th>Nominal</th><th>Keterangan</th><th>Aksi</th></tr>
            </thead>
            <tbody id="tbody-penjualan">
              <tr><td colspan="5" style="text-align:center; padding:28px; color:var(--text-muted);">Loading data...</td></tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ════════════ OPERASIONAL PAGE ════════════ -->
      <section id="page-operasional" class="page-section">

        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="font-display text-2xl font-semibold" style="color:var(--text-primary); letter-spacing:-0.01em;">Data Operasional</h2>
            <div class="accent-divider"></div>
          </div>
          <button class="btn-pink" onclick="openModal('modal-add-operasional')">+ Tambah Operasional</button>
        </div>

        <div class="glass-card" style="padding:20px; margin-bottom:20px;">
          <div class="flex flex-wrap gap-4 items-end">
            <div>
              <label class="form-label">Dari Tanggal</label>
              <input type="date" id="filter-o-from" class="form-input" style="width:165px;" value="<?= date('Y-m-01') ?>"/>
            </div>
            <div>
              <label class="form-label">Sampai Tanggal</label>
              <input type="date" id="filter-o-to" class="form-input" style="width:165px;" value="<?= $today ?>"/>
            </div>
            <button class="btn-pink" onclick="loadOperasional()">Filter</button>
            <button class="btn-ghost" onclick="resetFilterO()">Reset</button>
          </div>
        </div>

        <div class="glass-card" style="padding:18px 24px; margin-bottom:20px; display:flex; gap:36px; flex-wrap:wrap; border-color:rgba(255,107,157,0.2);">
          <div>
            <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.09em;">Total Periode</span><br>
            <span id="sum-o-total" class="font-display text-xl font-semibold profit-negative">Rp 0</span>
          </div>
          <div style="width:1px; background:var(--glass-border);"></div>
          <div>
            <span style="font-size:10px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.09em;">Jumlah Transaksi</span><br>
            <span id="sum-o-count" class="font-display text-xl font-semibold" style="color:var(--text-primary);">0</span>
          </div>
        </div>

        <div class="glass-card" style="padding:20px; overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr><th>No</th><th>Tanggal</th><th>Nominal</th><th>Keterangan</th><th>Aksi</th></tr>
            </thead>
            <tbody id="tbody-operasional">
              <tr><td colspan="5" style="text-align:center; padding:28px; color:var(--text-muted);">Loading data...</td></tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- ════════════ LAPORAN PAGE ════════════ -->
      <section id="page-laporan" class="page-section">
        <div class="mb-6">
          <h2 class="font-display text-2xl font-semibold" style="color:var(--text-primary); letter-spacing:-0.01em;">Laporan &amp; Analitik</h2>
          <div class="accent-divider"></div>
        </div>

        <div class="glass-card" style="padding:28px; margin-bottom:20px;">
          <h3 class="font-display text-xl font-semibold" style="color:var(--text-primary); margin-bottom:4px; letter-spacing:-0.01em;">Performa 6 Bulan Terakhir</h3>
          <p style="font-size:12.5px; color:var(--text-muted); margin-bottom:22px;">Perbandingan penjualan, operasional, dan profit bersih</p>
          <div class="chart-wrapper">
            <canvas id="chart-monthly"></canvas>
          </div>
        </div>

        <div class="glass-card" style="padding:26px;">
          <h3 class="font-display text-xl font-semibold" style="color:var(--text-primary); margin-bottom:18px; letter-spacing:-0.01em;">Ringkasan Bulanan</h3>
          <div style="overflow-x:auto;">
            <table class="data-table">
              <thead>
                <tr><th>Bulan</th><th>Penjualan</th><th>Operasional</th><th>Profit</th><th>Margin</th></tr>
              </thead>
              <tbody>
                <?php foreach ($monthly as $row): ?>
                <tr>
                  <td style="font-weight:500; color:var(--text-primary);"><?= htmlspecialchars($row['label']) ?></td>
                  <td class="profit-positive"><?= formatRupiah($row['penjualan']) ?></td>
                  <td class="profit-negative"><?= formatRupiah($row['operasional']) ?></td>
                  <td class="<?= $row['profit'] >= 0 ? 'profit-positive' : 'profit-negative' ?>" style="font-weight:600;">
                    <?= formatRupiah($row['profit']) ?>
                  </td>
                  <td>
                    <?php $margin = $row['penjualan'] > 0 ? round(($row['profit'] / $row['penjualan']) * 100, 1) : 0; ?>
                    <span class="stat-badge" style="background:<?= $margin >= 0 ? 'rgba(107,191,128,0.12)' : 'rgba(255,107,157,0.12)' ?>; color:<?= $margin >= 0 ? 'var(--g-accent)' : 'var(--p1)' ?>; border:1px solid <?= $margin >= 0 ? 'rgba(107,191,128,0.25)' : 'rgba(255,107,157,0.25)' ?>">
                      <?= $margin ?>%
                    </span>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ════════════ SETTINGS PAGE ════════════ -->
      <section id="page-settings" class="page-section">
        <div class="mb-6">
          <h2 class="font-display text-2xl font-semibold" style="color:var(--text-primary); letter-spacing:-0.01em;">Pengaturan</h2>
          <div class="accent-divider"></div>
        </div>
        <div class="glass-card" style="padding:30px; max-width:600px;">
          <h3 class="font-display text-lg font-semibold" style="margin-bottom:10px; color:var(--text-primary);">Informasi Aplikasi</h3>
          <div style="font-size:13.5px; color:var(--text-secondary); line-height:2.0;">
            <p><strong style="color:var(--text-muted);">Nama Bisnis:</strong> Nada &amp; Cafe</p>
            <p><strong style="color:var(--text-muted);">Versi Sistem:</strong> 1.0.0</p>
            <p><strong style="color:var(--text-muted);">Database:</strong> aura_finance (MySQL)</p>
            <p><strong style="color:var(--text-muted);">Framework:</strong> PHP Native + Tailwind CSS + Chart.js</p>
            <p><strong style="color:var(--text-muted);">Mata Uang:</strong> IDR (Rupiah)</p>
          </div>
          <div style="margin-top:22px; padding:16px 20px; background:rgba(107,191,128,0.06); border-radius:14px; border:1px solid rgba(107,191,128,0.15);">
            <p style="font-size:12px; color:var(--text-muted);">💡 Untuk setup database, jalankan file <code style="color:var(--g-accent); background:rgba(107,191,128,0.1); padding:1px 6px; border-radius:4px;">setup.sql</code> di MySQL Anda, lalu sesuaikan kredensial di <code style="color:var(--p2); background:rgba(255,107,157,0.08); padding:1px 6px; border-radius:4px;">config/database.php</code>.</p>
          </div>
        </div>
      </section>

    </div>
  </main>
</div>

<!-- ══ MODALS ══ -->

<!-- Add Penjualan -->
<div class="modal-overlay" id="modal-add-penjualan">
  <div class="modal-box">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="font-display text-2xl font-semibold" style="color:var(--text-primary);">Tambah Penjualan</h3>
        <div class="accent-divider" style="margin-bottom:0;"></div>
      </div>
      <button onclick="closeModal('modal-add-penjualan')" style="font-size:20px; background:rgba(107,191,128,0.1); border:1px solid var(--glass-border); border-radius:10px; width:36px; height:36px; cursor:pointer; color:var(--text-muted); display:flex; align-items:center; justify-content:center; transition:all 0.25s;" onmouseover="this.style.background='rgba(255,107,157,0.15)'; this.style.color='var(--p1)'" onmouseout="this.style.background='rgba(107,191,128,0.1)'; this.style.color='var(--text-muted)'">✕</button>
    </div>
    <div style="display:flex; flex-direction:column; gap:18px;">
      <div>
        <label class="form-label">Tanggal *</label>
        <input type="date" id="ap-tanggal" class="form-input" value="<?= $today ?>"/>
      </div>
      <div>
        <label class="form-label">Nominal Penjualan (Rp) *</label>
        <input type="number" id="ap-nominal" class="form-input" placeholder="Contoh: 1500000" min="1"/>
      </div>
      <div>
        <label class="form-label">Keterangan *</label>
        <input type="text" id="ap-keterangan" class="form-input" placeholder="Contoh: Penjualan minuman harian"/>
      </div>
      <div class="flex gap-3 justify-end" style="margin-top:4px;">
        <button class="btn-ghost" onclick="closeModal('modal-add-penjualan')">Batal</button>
        <button class="btn-primary" onclick="submitAddPenjualan()">Simpan Data</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Penjualan -->
<div class="modal-overlay" id="modal-edit-penjualan">
  <div class="modal-box">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="font-display text-2xl font-semibold" style="color:var(--text-primary);">Edit Penjualan</h3>
        <div class="accent-divider" style="margin-bottom:0;"></div>
      </div>
      <button onclick="closeModal('modal-edit-penjualan')" style="font-size:20px; background:rgba(107,191,128,0.1); border:1px solid var(--glass-border); border-radius:10px; width:36px; height:36px; cursor:pointer; color:var(--text-muted); display:flex; align-items:center; justify-content:center; transition:all 0.25s;" onmouseover="this.style.background='rgba(255,107,157,0.15)'; this.style.color='var(--p1)'" onmouseout="this.style.background='rgba(107,191,128,0.1)'; this.style.color='var(--text-muted)'">✕</button>
    </div>
    <input type="hidden" id="ep-id"/>
    <div style="display:flex; flex-direction:column; gap:18px;">
      <div><label class="form-label">Tanggal *</label><input type="date" id="ep-tanggal" class="form-input"/></div>
      <div><label class="form-label">Nominal Penjualan (Rp) *</label><input type="number" id="ep-nominal" class="form-input" min="1"/></div>
      <div><label class="form-label">Keterangan *</label><input type="text" id="ep-keterangan" class="form-input"/></div>
      <div class="flex gap-3 justify-end" style="margin-top:4px;">
        <button class="btn-ghost" onclick="closeModal('modal-edit-penjualan')">Batal</button>
        <button class="btn-primary" onclick="submitEditPenjualan()">Update Data</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Operasional -->
<div class="modal-overlay" id="modal-add-operasional">
  <div class="modal-box">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="font-display text-2xl font-semibold" style="color:var(--text-primary);">Tambah Operasional</h3>
        <div class="accent-divider" style="margin-bottom:0;"></div>
      </div>
      <button onclick="closeModal('modal-add-operasional')" style="font-size:20px; background:rgba(107,191,128,0.1); border:1px solid var(--glass-border); border-radius:10px; width:36px; height:36px; cursor:pointer; color:var(--text-muted); display:flex; align-items:center; justify-content:center; transition:all 0.25s;" onmouseover="this.style.background='rgba(255,107,157,0.15)'; this.style.color='var(--p1)'" onmouseout="this.style.background='rgba(107,191,128,0.1)'; this.style.color='var(--text-muted)'">✕</button>
    </div>
    <div style="display:flex; flex-direction:column; gap:18px;">
      <div><label class="form-label">Tanggal *</label><input type="date" id="ao-tanggal" class="form-input" value="<?= $today ?>"/></div>
      <div><label class="form-label">Nominal Pengeluaran (Rp) *</label><input type="number" id="ao-nominal" class="form-input" placeholder="Contoh: 500000" min="1"/></div>
      <div><label class="form-label">Keterangan *</label><input type="text" id="ao-keterangan" class="form-input" placeholder="Contoh: Bahan baku harian"/></div>
      <div class="flex gap-3 justify-end" style="margin-top:4px;">
        <button class="btn-ghost" onclick="closeModal('modal-add-operasional')">Batal</button>
        <button class="btn-pink" onclick="submitAddOperasional()">Simpan Data</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Operasional -->
<div class="modal-overlay" id="modal-edit-operasional">
  <div class="modal-box">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="font-display text-2xl font-semibold" style="color:var(--text-primary);">Edit Operasional</h3>
        <div class="accent-divider" style="margin-bottom:0;"></div>
      </div>
      <button onclick="closeModal('modal-edit-operasional')" style="font-size:20px; background:rgba(107,191,128,0.1); border:1px solid var(--glass-border); border-radius:10px; width:36px; height:36px; cursor:pointer; color:var(--text-muted); display:flex; align-items:center; justify-content:center; transition:all 0.25s;" onmouseover="this.style.background='rgba(255,107,157,0.15)'; this.style.color='var(--p1)'" onmouseout="this.style.background='rgba(107,191,128,0.1)'; this.style.color='var(--text-muted)'">✕</button>
    </div>
    <input type="hidden" id="eo-id"/>
    <div style="display:flex; flex-direction:column; gap:18px;">
      <div><label class="form-label">Tanggal *</label><input type="date" id="eo-tanggal" class="form-input"/></div>
      <div><label class="form-label">Nominal Pengeluaran (Rp) *</label><input type="number" id="eo-nominal" class="form-input" min="1"/></div>
      <div><label class="form-label">Keterangan *</label><input type="text" id="eo-keterangan" class="form-input"/></div>
      <div class="flex gap-3 justify-end" style="margin-top:4px;">
        <button class="btn-ghost" onclick="closeModal('modal-edit-operasional')">Batal</button>
        <button class="btn-pink" onclick="submitEditOperasional()">Update Data</button>
      </div>
    </div>
  </div>
</div>

<!-- ══ JavaScript ══ -->
<script>
const API   = 'api.php';
const TODAY = '<?= $today ?>';

// Chart.js global dark theme defaults
Chart.defaults.color = '#8DB89A';
Chart.defaults.borderColor = 'rgba(107,191,128,0.1)';
Chart.defaults.font.family = 'DM Sans';

// ─── Ripple Effect ────────────────────────────────────────────
function addRipple(e) {
  const btn = e.currentTarget;
  const circle = document.createElement('span');
  const diameter = Math.max(btn.clientWidth, btn.clientHeight);
  const radius = diameter / 2;
  const rect = btn.getBoundingClientRect();
  circle.style.width = circle.style.height = `${diameter}px`;
  circle.style.left = `${e.clientX - rect.left - radius}px`;
  circle.style.top  = `${e.clientY - rect.top - radius}px`;
  circle.classList.add('ripple');
  btn.style.position = 'relative';
  btn.appendChild(circle);
  circle.addEventListener('animationend', () => circle.remove());
}

// ─── Navigation ───────────────────────────────────────────────
const pageTitles = {
  dashboard:   ['Dashboard', 'Ringkasan keuangan hari ini'],
  penjualan:   ['Data Penjualan', 'Kelola catatan penjualan harian'],
  operasional: ['Data Operasional', 'Kelola catatan pengeluaran'],
  laporan:     ['Laporan & Analitik', 'Visualisasi performa keuangan'],
  settings:    ['Pengaturan', 'Konfigurasi sistem'],
};

function showPage(name) {
  document.querySelectorAll('.page-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('page-' + name)?.classList.add('active');
  document.getElementById('nav-' + name)?.classList.add('active');
  const [title, sub] = pageTitles[name] || [name, ''];
  document.getElementById('page-title').textContent = title;
  document.getElementById('page-sub').textContent = sub;
  if (name === 'penjualan')   loadPenjualan();
  if (name === 'operasional') loadOperasional();
  if (name === 'laporan')     initMonthlyChart();
  closeSidebar();
}

// ─── Mobile Sidebar ───────────────────────────────────────────
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  const bd = document.getElementById('mobile-backdrop');
  sb.classList.toggle('mobile-open');
  bd.style.display = sb.classList.contains('mobile-open') ? 'block' : 'none';
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('mobile-open');
  document.getElementById('mobile-backdrop').style.display = 'none';
}

// Mobile menu button visibility
if (window.innerWidth <= 768) {
  document.getElementById('mobile-menu-btn').style.display = '';
}
window.addEventListener('resize', () => {
  const btn = document.getElementById('mobile-menu-btn');
  btn.style.display = window.innerWidth <= 768 ? '' : 'none';
});

// ─── Modal ────────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});

// ─── Toast ────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = (type === 'success' ? '✓ ' : '✕ ') + msg;
  t.className = 'show ' + type;
  setTimeout(() => t.className = '', 3800);
}

// ─── Format Rupiah (JS) ───────────────────────────────────────
function fmtRp(n) {
  return 'Rp ' + Math.abs(parseFloat(n) || 0).toLocaleString('id-ID', {maximumFractionDigits:0});
}

// ─── Load Penjualan Table ─────────────────────────────────────
async function loadPenjualan() {
  const from = document.getElementById('filter-p-from').value;
  const to   = document.getElementById('filter-p-to').value;
  const res  = await fetch(`api_filter.php?type=penjualan&from=${from}&to=${to}`);
  const data = await res.json();
  const tbody = document.getElementById('tbody-penjualan');
  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:28px; color:var(--text-muted);">Tidak ada data pada periode ini.</td></tr>';
    document.getElementById('sum-p-total').textContent = 'Rp 0';
    document.getElementById('sum-p-count').textContent = '0';
    return;
  }
  let total = 0;
  tbody.innerHTML = data.map((row, i) => {
    total += parseFloat(row.nominal_penjualan);
    return `<tr>
      <td style="color:var(--text-muted); font-size:12px;">${i+1}</td>
      <td style="color:var(--text-secondary);">${row.tanggal}</td>
      <td class="profit-positive" style="font-weight:600;">${fmtRp(row.nominal_penjualan)}</td>
      <td style="color:var(--text-secondary);">${row.keterangan}</td>
      <td><div style="display:flex; gap:6px;">
        <button class="btn-edit" onclick="editPenjualan(${row.id_penjualan})">Edit</button>
        <button class="btn-danger" onclick="deletePenjualanConfirm(${row.id_penjualan})">Hapus</button>
      </div></td>
    </tr>`;
  }).join('');
  document.getElementById('sum-p-total').textContent = fmtRp(total);
  document.getElementById('sum-p-count').textContent = data.length + ' transaksi';
}

function resetFilterP() {
  document.getElementById('filter-p-from').value = new Date().toISOString().slice(0,7) + '-01';
  document.getElementById('filter-p-to').value   = TODAY;
  loadPenjualan();
}

// ─── Load Operasional Table ───────────────────────────────────
async function loadOperasional() {
  const from = document.getElementById('filter-o-from').value;
  const to   = document.getElementById('filter-o-to').value;
  const res  = await fetch(`api_filter.php?type=operasional&from=${from}&to=${to}`);
  const data = await res.json();
  const tbody = document.getElementById('tbody-operasional');
  if (!data.length) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:28px; color:var(--text-muted);">Tidak ada data pada periode ini.</td></tr>';
    document.getElementById('sum-o-total').textContent = 'Rp 0';
    document.getElementById('sum-o-count').textContent = '0';
    return;
  }
  let total = 0;
  tbody.innerHTML = data.map((row, i) => {
    total += parseFloat(row.nominal_operasional);
    return `<tr>
      <td style="color:var(--text-muted); font-size:12px;">${i+1}</td>
      <td style="color:var(--text-secondary);">${row.tanggal}</td>
      <td class="profit-negative" style="font-weight:600;">${fmtRp(row.nominal_operasional)}</td>
      <td style="color:var(--text-secondary);">${row.keterangan}</td>
      <td><div style="display:flex; gap:6px;">
        <button class="btn-edit" onclick="editOperasional(${row.id_operasional})">Edit</button>
        <button class="btn-danger" onclick="deleteOperasionalConfirm(${row.id_operasional})">Hapus</button>
      </div></td>
    </tr>`;
  }).join('');
  document.getElementById('sum-o-total').textContent = fmtRp(total);
  document.getElementById('sum-o-count').textContent = data.length + ' transaksi';
}

function resetFilterO() {
  document.getElementById('filter-o-from').value = new Date().toISOString().slice(0,7) + '-01';
  document.getElementById('filter-o-to').value   = TODAY;
  loadOperasional();
}

// ─── CRUD Penjualan ───────────────────────────────────────────
async function submitAddPenjualan() {
  const tanggal = document.getElementById('ap-tanggal').value;
  const nominal = document.getElementById('ap-nominal').value;
  const keterangan = document.getElementById('ap-keterangan').value.trim();
  if (!tanggal || !nominal || nominal <= 0 || !keterangan) { showToast('Semua field wajib diisi dengan nilai valid!', 'error'); return; }
  const fd = new FormData();
  fd.append('action','add_penjualan'); fd.append('tanggal',tanggal); fd.append('nominal',nominal); fd.append('keterangan',keterangan);
  const res = await (await fetch(API, { method:'POST', body:fd })).json();
  showToast(res.message, res.success ? 'success' : 'error');
  if (res.success) { closeModal('modal-add-penjualan'); loadPenjualan(); document.getElementById('ap-nominal').value=''; document.getElementById('ap-keterangan').value=''; }
}

async function editPenjualan(id) {
  const res = await (await fetch(`${API}?action=get_penjualan&id=${id}`)).json();
  if (!res.success) { showToast('Data tidak ditemukan', 'error'); return; }
  const d = res.data;
  document.getElementById('ep-id').value        = d.id_penjualan;
  document.getElementById('ep-tanggal').value   = d.tanggal;
  document.getElementById('ep-nominal').value   = d.nominal_penjualan;
  document.getElementById('ep-keterangan').value= d.keterangan;
  openModal('modal-edit-penjualan');
}

async function submitEditPenjualan() {
  const id = document.getElementById('ep-id').value;
  const tanggal = document.getElementById('ep-tanggal').value;
  const nominal = document.getElementById('ep-nominal').value;
  const keterangan = document.getElementById('ep-keterangan').value.trim();
  if (!nominal || nominal <= 0 || !keterangan) { showToast('Data tidak valid!', 'error'); return; }
  const fd = new FormData();
  fd.append('action','edit_penjualan'); fd.append('id',id); fd.append('tanggal',tanggal); fd.append('nominal',nominal); fd.append('keterangan',keterangan);
  const res = await (await fetch(API, { method:'POST', body:fd })).json();
  showToast(res.message, res.success ? 'success' : 'error');
  if (res.success) { closeModal('modal-edit-penjualan'); loadPenjualan(); }
}

async function deletePenjualanConfirm(id) {
  if (!confirm('Yakin ingin menghapus data penjualan ini?')) return;
  const fd = new FormData(); fd.append('action','delete_penjualan'); fd.append('id',id);
  const res = await (await fetch(API, { method:'POST', body:fd })).json();
  showToast(res.message, res.success ? 'success' : 'error');
  if (res.success) loadPenjualan();
}

// ─── CRUD Operasional ─────────────────────────────────────────
async function submitAddOperasional() {
  const tanggal = document.getElementById('ao-tanggal').value;
  const nominal = document.getElementById('ao-nominal').value;
  const keterangan = document.getElementById('ao-keterangan').value.trim();
  if (!tanggal || !nominal || nominal <= 0 || !keterangan) { showToast('Semua field wajib diisi dengan nilai valid!', 'error'); return; }
  const fd = new FormData();
  fd.append('action','add_operasional'); fd.append('tanggal',tanggal); fd.append('nominal',nominal); fd.append('keterangan',keterangan);
  const res = await (await fetch(API, { method:'POST', body:fd })).json();
  showToast(res.message, res.success ? 'success' : 'error');
  if (res.success) { closeModal('modal-add-operasional'); loadOperasional(); document.getElementById('ao-nominal').value=''; document.getElementById('ao-keterangan').value=''; }
}

async function editOperasional(id) {
  const res = await (await fetch(`${API}?action=get_operasional&id=${id}`)).json();
  if (!res.success) { showToast('Data tidak ditemukan', 'error'); return; }
  const d = res.data;
  document.getElementById('eo-id').value        = d.id_operasional;
  document.getElementById('eo-tanggal').value   = d.tanggal;
  document.getElementById('eo-nominal').value   = d.nominal_operasional;
  document.getElementById('eo-keterangan').value= d.keterangan;
  openModal('modal-edit-operasional');
}

async function submitEditOperasional() {
  const id = document.getElementById('eo-id').value;
  const tanggal = document.getElementById('eo-tanggal').value;
  const nominal = document.getElementById('eo-nominal').value;
  const keterangan = document.getElementById('eo-keterangan').value.trim();
  if (!nominal || nominal <= 0 || !keterangan) { showToast('Data tidak valid!', 'error'); return; }
  const fd = new FormData();
  fd.append('action','edit_operasional'); fd.append('id',id); fd.append('tanggal',tanggal); fd.append('nominal',nominal); fd.append('keterangan',keterangan);
  const res = await (await fetch(API, { method:'POST', body:fd })).json();
  showToast(res.message, res.success ? 'success' : 'error');
  if (res.success) { closeModal('modal-edit-operasional'); loadOperasional(); }
}

async function deleteOperasionalConfirm(id) {
  if (!confirm('Yakin ingin menghapus data operasional ini?')) return;
  const fd = new FormData(); fd.append('action','delete_operasional'); fd.append('id',id);
  const res = await (await fetch(API, { method:'POST', body:fd })).json();
  showToast(res.message, res.success ? 'success' : 'error');
  if (res.success) loadOperasional();
}

// ─── Chart.js — Shared plugin helpers ─────────────────────────
function darkTooltip() {
  return {
    backgroundColor: 'rgba(8,14,10,0.95)',
    titleColor: '#E8F5EC',
    bodyColor: '#8DB89A',
    borderColor: 'rgba(107,191,128,0.3)',
    borderWidth: 1,
    padding: 12,
    cornerRadius: 12,
    titleFont: { family: 'Playfair Display', size: 14 },
    bodyFont: { family: 'DM Sans', size: 12 },
    callbacks: {
      label: ctx => '  ' + ctx.dataset.label + ': Rp ' + Math.abs(ctx.raw).toLocaleString('id-ID')
    }
  };
}

function darkLegend() {
  return {
    labels: {
      font: { family: 'DM Sans', size: 12 },
      color: '#8DB89A',
      boxWidth: 12,
      boxHeight: 12,
      borderRadius: 3,
      padding: 20
    }
  };
}

function darkScales() {
  return {
    x: {
      grid: { color: 'rgba(107,191,128,0.06)', drawBorder: false },
      ticks: { color: '#506858', font: { family: 'DM Sans', size: 11 } }
    },
    y: {
      grid: { color: 'rgba(107,191,128,0.06)', drawBorder: false },
      ticks: {
        color: '#506858',
        font: { family: 'DM Sans', size: 11 },
        callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt'
      }
    }
  };
}

// ─── Chart.js Weekly ─────────────────────────────────────────
(function() {
  const chartData = <?= json_encode($chartData) ?>;
  const canvas = document.getElementById('chart-weekly');
  const ctx = canvas.getContext('2d');

  const gradG = ctx.createLinearGradient(0,0,0,280);
  gradG.addColorStop(0, 'rgba(107,191,128,0.35)');
  gradG.addColorStop(0.7, 'rgba(107,191,128,0.05)');
  gradG.addColorStop(1, 'rgba(107,191,128,0)');

  const gradP = ctx.createLinearGradient(0,0,0,280);
  gradP.addColorStop(0, 'rgba(255,107,157,0.3)');
  gradP.addColorStop(0.7, 'rgba(255,107,157,0.05)');
  gradP.addColorStop(1, 'rgba(255,107,157,0)');

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: chartData.labels,
      datasets: [
        {
          label: 'Penjualan',
          data: chartData.penjualanData,
          borderColor: '#6BBF80',
          backgroundColor: gradG,
          borderWidth: 2.5,
          pointBackgroundColor: '#6BBF80',
          pointBorderColor: '#0D1F0F',
          pointBorderWidth: 2.5,
          pointRadius: 5,
          pointHoverRadius: 8,
          pointHoverBackgroundColor: '#6BBF80',
          pointHoverBorderColor: '#fff',
          pointHoverBorderWidth: 2,
          tension: 0.45, fill: true
        },
        {
          label: 'Operasional',
          data: chartData.operasionalData,
          borderColor: '#FF6B9D',
          backgroundColor: gradP,
          borderWidth: 2.5,
          pointBackgroundColor: '#FF6B9D',
          pointBorderColor: '#0D1F0F',
          pointBorderWidth: 2.5,
          pointRadius: 5,
          pointHoverRadius: 8,
          pointHoverBackgroundColor: '#FF6B9D',
          pointHoverBorderColor: '#fff',
          pointHoverBorderWidth: 2,
          tension: 0.45, fill: true
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: window.innerWidth < 768 ? 1.4 : 2.2,
      interaction: { mode: 'index', intersect: false },
      animation: {
        duration: 1200,
        easing: 'easeInOutQuart'
      },
      plugins: {
        legend: darkLegend(),
        tooltip: darkTooltip()
      },
      scales: darkScales()
    }
  });

  // Update aspect ratio on resize
  window.addEventListener('resize', () => {
    // Chart.js handles responsive resize automatically
  });
})();

// ─── Monthly Chart ────────────────────────────────────────────
let monthlyChartInited = false;
function initMonthlyChart() {
  if (monthlyChartInited) return;
  monthlyChartInited = true;

  const monthly = <?= json_encode($monthly) ?>;
  const labels  = monthly.map(r => r.label);
  const pData   = monthly.map(r => r.penjualan);
  const oData   = monthly.map(r => r.operasional);
  const profitD = monthly.map(r => r.profit);

  const ctx = document.getElementById('chart-monthly').getContext('2d');

  const profitGrad = ctx.createLinearGradient(0,0,0,200);
  profitGrad.addColorStop(0, 'rgba(201,112,90,0.2)');
  profitGrad.addColorStop(1, 'rgba(201,112,90,0)');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Penjualan',
          data: pData,
          backgroundColor: 'rgba(107,191,128,0.6)',
          hoverBackgroundColor: 'rgba(107,191,128,0.85)',
          borderColor: 'rgba(107,191,128,0.9)',
          borderWidth: 1,
          borderRadius: 8,
          borderSkipped: false
        },
        {
          label: 'Operasional',
          data: oData,
          backgroundColor: 'rgba(255,107,157,0.5)',
          hoverBackgroundColor: 'rgba(255,107,157,0.75)',
          borderColor: 'rgba(255,107,157,0.8)',
          borderWidth: 1,
          borderRadius: 8,
          borderSkipped: false
        },
        {
          label: 'Profit',
          data: profitD,
          type: 'line',
          borderColor: '#C9705A',
          backgroundColor: profitGrad,
          borderWidth: 2.5,
          pointRadius: 6,
          pointHoverRadius: 9,
          pointBackgroundColor: '#C9705A',
          pointBorderColor: '#0D1F0F',
          pointBorderWidth: 2,
          pointHoverBackgroundColor: '#C9705A',
          pointHoverBorderColor: '#fff',
          tension: 0.45, fill: true,
          yAxisID: 'y'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: window.innerWidth < 768 ? 1.3 : 2.2,
      interaction: { mode: 'index', intersect: false },
      animation: {
        duration: 1400,
        easing: 'easeInOutQuart',
        delay: (ctx) => ctx.dataIndex * 80
      },
      plugins: {
        legend: darkLegend(),
        tooltip: darkTooltip()
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#506858', font: { family: 'DM Sans', size: 11 } }
        },
        y: {
          grid: { color: 'rgba(107,191,128,0.06)' },
          ticks: {
            color: '#506858',
            font: { family: 'DM Sans', size: 11 },
            callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt'
          }
        }
      }
    }
  });
}
</script>
</body>
</html>