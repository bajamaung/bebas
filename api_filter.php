<?php
// api_filter.php — Returns filtered JSON data for tables

header('Content-Type: application/json');
require_once __DIR__ . '/includes/helpers.php';

$type = $_GET['type'] ?? '';
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');

// Basic date validation
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   $to   = date('Y-m-d');

try {
    if ($type === 'penjualan') {
        echo json_encode(getPenjualanFiltered($from, $to));
    } elseif ($type === 'operasional') {
        echo json_encode(getOperasionalFiltered($from, $to));
    } else {
        echo json_encode([]);
    }
} catch (Exception $e) {
    echo json_encode([]);
}
