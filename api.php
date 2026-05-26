<?php
// api.php — JSON API endpoint for CRUD operations

header('Content-Type: application/json');
require_once __DIR__ . '/includes/helpers.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {

        // ── PENJUALAN ─────────────────────────────────
        case 'add_penjualan':
            $tanggal    = sanitize($_POST['tanggal'] ?? '');
            $nominal    = (float)($_POST['nominal'] ?? 0);
            $keterangan = sanitize($_POST['keterangan'] ?? '');

            if (!$tanggal || $nominal <= 0 || !$keterangan) {
                echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi dengan nilai valid.']);
                exit;
            }
            $ok = insertPenjualan($tanggal, $nominal, $keterangan);
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Data penjualan berhasil disimpan.' : 'Gagal menyimpan data.']);
            break;

        case 'edit_penjualan':
            $id         = (int)($_POST['id'] ?? 0);
            $tanggal    = sanitize($_POST['tanggal'] ?? '');
            $nominal    = (float)($_POST['nominal'] ?? 0);
            $keterangan = sanitize($_POST['keterangan'] ?? '');

            if (!$id || !$tanggal || $nominal <= 0 || !$keterangan) {
                echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
                exit;
            }
            $ok = updatePenjualan($id, $tanggal, $nominal, $keterangan);
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Data berhasil diperbarui.' : 'Gagal memperbarui data.']);
            break;

        case 'delete_penjualan':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid.']); exit; }
            $ok = deletePenjualan($id);
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Data berhasil dihapus.' : 'Gagal menghapus data.']);
            break;

        case 'get_penjualan':
            $id   = (int)($_GET['id'] ?? 0);
            $data = getPenjualanById($id);
            echo json_encode($data ? ['success' => true, 'data' => $data] : ['success' => false, 'message' => 'Data tidak ditemukan.']);
            break;

        // ── OPERASIONAL ───────────────────────────────
        case 'add_operasional':
            $tanggal    = sanitize($_POST['tanggal'] ?? '');
            $nominal    = (float)($_POST['nominal'] ?? 0);
            $keterangan = sanitize($_POST['keterangan'] ?? '');

            if (!$tanggal || $nominal <= 0 || !$keterangan) {
                echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi dengan nilai valid.']);
                exit;
            }
            $ok = insertOperasional($tanggal, $nominal, $keterangan);
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Data operasional berhasil disimpan.' : 'Gagal menyimpan data.']);
            break;

        case 'edit_operasional':
            $id         = (int)($_POST['id'] ?? 0);
            $tanggal    = sanitize($_POST['tanggal'] ?? '');
            $nominal    = (float)($_POST['nominal'] ?? 0);
            $keterangan = sanitize($_POST['keterangan'] ?? '');

            if (!$id || !$tanggal || $nominal <= 0 || !$keterangan) {
                echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
                exit;
            }
            $ok = updateOperasional($id, $tanggal, $nominal, $keterangan);
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Data berhasil diperbarui.' : 'Gagal memperbarui data.']);
            break;

        case 'delete_operasional':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) { echo json_encode(['success' => false, 'message' => 'ID tidak valid.']); exit; }
            $ok = deleteOperasional($id);
            echo json_encode(['success' => $ok, 'message' => $ok ? 'Data berhasil dihapus.' : 'Gagal menghapus data.']);
            break;

        case 'get_operasional':
            $id   = (int)($_GET['id'] ?? 0);
            $data = getOperasionalById($id);
            echo json_encode($data ? ['success' => true, 'data' => $data] : ['success' => false, 'message' => 'Data tidak ditemukan.']);
            break;

        // ── CHART DATA ────────────────────────────────
        case 'chart_data':
            echo json_encode(['success' => true, 'data' => getChartData()]);
            break;

        case 'monthly_summary':
            echo json_encode(['success' => true, 'data' => getMonthlySummary(6)]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action tidak dikenali.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
