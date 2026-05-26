<?php
require_once __DIR__ . '/../models/PenjualanModel.php';

class PenjualanController {
    private PenjualanModel $model;

    public function __construct() {
        $this->model = new PenjualanModel();
    }

    public function handleRequest(): void {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'create':
                $this->create();
                break;
            case 'edit':
                $this->edit();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
                header('Location: index.php');
                exit;
        }
    }

    private function validate(array $data): array {
        $errors = [];
        if (empty($data['tanggal'])) $errors[] = 'Tanggal wajib diisi.';
        if (empty($data['nominal_penjualan']) || !is_numeric($data['nominal_penjualan']) || $data['nominal_penjualan'] <= 0) {
            $errors[] = 'Nominal penjualan harus berupa angka positif.';
        }
        return $errors;
    }

    private function create(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: index.php#penjualan');
            exit;
        }
        $nominal = (float) str_replace(['.', ','], ['', '.'], $_POST['nominal_penjualan']);
        if ($this->model->create($_POST['tanggal'], $nominal, trim($_POST['keterangan'] ?? ''))) {
            $_SESSION['success'] = 'Data penjualan berhasil ditambahkan!';
        } else {
            $_SESSION['error'] = 'Gagal menambahkan data penjualan.';
        }
        header('Location: index.php#penjualan');
        exit;
    }

    private function edit(): void {
        $id = (int)($_GET['id'] ?? 0);
        $data = $this->model->getById($id);
        if (!$data) { $_SESSION['error'] = 'Data tidak ditemukan.'; header('Location: index.php'); exit; }
        $_SESSION['edit_penjualan'] = $data;
        header('Location: index.php?edit_penjualan=' . $id . '#penjualan');
        exit;
    }

    private function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
        $id = (int)($_POST['id'] ?? 0);
        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: index.php#penjualan');
            exit;
        }
        $nominal = (float) str_replace(['.', ','], ['', '.'], $_POST['nominal_penjualan']);
        if ($this->model->update($id, $_POST['tanggal'], $nominal, trim($_POST['keterangan'] ?? ''))) {
            $_SESSION['success'] = 'Data penjualan berhasil diperbarui!';
        } else {
            $_SESSION['error'] = 'Gagal memperbarui data.';
        }
        unset($_SESSION['edit_penjualan']);
        header('Location: index.php#penjualan');
        exit;
    }

    private function delete(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->model->delete($id)) {
            $_SESSION['success'] = 'Data penjualan berhasil dihapus.';
        } else {
            $_SESSION['error'] = 'Gagal menghapus data.';
        }
        header('Location: index.php#penjualan');
        exit;
    }
}
