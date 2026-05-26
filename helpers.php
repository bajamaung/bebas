<?php

function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate(string $date): string {
    $months = [
        '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
        '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
        '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
    ];
    $parts = explode('-', $date);
    return $parts[2] . ' ' . ($months[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
}

function flashMessage(string $type): string {
    if (!isset($_SESSION[$type])) return '';
    $msg = $_SESSION[$type];
    unset($_SESSION[$type]);
    $class = $type === 'success' ? 'alert-success' : 'alert-danger';
    $icon  = $type === 'success' ? '✓' : '✗';
    return "<div class='alert {$class} alert-dismissible fade show custom-alert' role='alert'>
                <span class='alert-icon'>{$icon}</span> {$msg}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
