<?php
$page_title = 'Manajemen Alat';
require '../config/database.php';
require '../includes/auth.php';

requireRole('admin');

$current_user = getCurrentUser($conn);

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';
$error = '';

// Function to generate unique kode_alat
function generateKodeAlat($conn) {
    $prefix = 'ALT';
    $date = date('Ymd');
    $count = $conn->query("SELECT COUNT(*) as count FROM alat WHERE kode_alat LIKE '{$prefix}{$date}%'")->fetch_assoc()['count'];
    return $prefix . $date . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'CSRF Token tidak valid!';
    } else {
        $nama_alat = trim($_POST['nama_alat']);
        $kategori_id = $_POST['kategori_id'];
        $stok = $_POST['stok'];
        $kondisi = $_POST['kondisi'];
        $lokasi_rak = trim($_POST['lokasi_rak']);

        if (empty($nama_alat) || empty($kategori_id) || empty($stok)) {
            $error = 'Nama alat, kategori, dan stok harus diisi!';
        } else {
            if (isset($_POST['id']) && $_POST['id'] == 0) {
                // Add new
                $kode_alat = generateKodeAlat($conn);
                
                $stmt = $conn->prepare("INSERT INTO alat (kode_alat, nama_alat, kategori_id, stok, kondisi, lokasi_rak) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiiss", $kode_alat, $nama_alat, $kategori_id, $stok, $kondisi, $lokasi_rak);
                
                if ($stmt->execute()) {
                    logActivity($conn, $current_user['id'], 'Tambah Alat', "Alat $nama_alat ditambahkan", 'alat', $conn->insert_id);
                    $message = 'Alat berhasil ditambahkan!';
                } else {
                    $error = 'Gagal menambahkan alat!';
                }
            } else {
                // Update
                $id = $_POST['id'];
                $stmt = $conn->prepare("UPDATE alat SET nama_alat = ?, kategori_id = ?, stok = ?, kondisi = ?, lokasi_rak = ? WHERE id = ?");
                $stmt->bind_param("siissi", $nama_alat, $kategori_id, $stok, $kondisi, $lokasi_rak, $id);
                
                if ($stmt->execute()) {
                    logActivity($conn, $current_user['id'], 'Edit Alat', "Alat ID $id diupdate", 'alat', $id);
                    $message = 'Alat berhasil diupdate!';
                } else {
                    $error = 'Gagal mengupdate alat!';
                }
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM alat WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        logActivity($conn, $current_user['id'], 'Hapus Alat', "Alat ID $id dihapus", 'alat', $id);
        $message = 'Alat berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus alat!';
    }
}

// Get all alat
$alat = $conn->query("
    SELECT a.*, k.nama_kategori 
    FROM alat a 
    JOIN kategori k ON a.kategori_id = k.id 
    ORDER BY a.nama_alat ASC
");

// Get all kategori
$kategori = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Get single alat for edit
$edit_alat = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM alat WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_alat = $stmt->get_result()->fetch_assoc();
}

$csrf_token = generateCSRFToken();
?>

<?php include '../includes/header.php'; ?>

<div class="container-main">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        
        <div class="content">
            <div class="breadcrumb-nav">
                <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>Alat</span>
            </div>

            <div class="page-header">
                <h1>Manajemen Alat</h1>
                <p>Kelola data alat peminjaman</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <?php if ($action == 'add' || $action == 'edit'): ?>
                <div class="card" style="margin-bottom: 30px;">
                    <div class="card-header">
                        <h2><?php echo ($action == 'add') ? 'Tambah Alat Baru' : 'Edit Alat'; ?></h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="id" value="<?php echo ($edit_alat) ? $edit_alat['id'] : 0; ?>">

                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label for="nama_alat">Nama Alat</label>
                                    <input type="text" id="nama_alat" name="nama_alat" value="<?php echo ($edit_alat) ? htmlspecialchars($edit_alat['nama_alat']) : ''; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="kategori_id">Kategori</label>
                                    <select id="kategori_id" name="kategori_id" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php
                                        // Reset pointer untuk kategori
                                        $kategori->data_seek(0);
                                        while ($row = $kategori->fetch_assoc()):
                                        ?>
                                            <option value="<?php echo $row['id']; ?>" <?php echo ($edit_alat && $edit_alat['kategori_id'] == $row['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="stok">Stok</label>
                                    <input type="number" id="stok" name="stok" min="0" value="<?php echo ($edit_alat) ? $edit_alat['stok'] : '0'; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="kondisi">Kondisi</label>
                                    <select id="kondisi" name="kondisi" required>
                                        <option value="baik" <?php echo ($edit_alat && $edit_alat['kondisi'] == 'baik') ? 'selected' : ''; ?>>Baik</option>
                                        <option value="kurang_baik" <?php echo ($edit_alat && $edit_alat['kondisi'] == 'kurang_baik') ? 'selected' : ''; ?>>Kurang Baik</option>
                                        <option value="rusak" <?php echo ($edit_alat && $edit_alat['kondisi'] == 'rusak') ? 'selected' : ''; ?>>Rusak</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="lokasi_rak">Lokasi Rak</label>
                                    <input type="text" id="lokasi_rak" name="lokasi_rak" placeholder="Contoh: Rak A1, Lemari B2" value="<?php echo ($edit_alat) ? htmlspecialchars($edit_alat['lokasi_rak']) : ''; ?>">
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px; margin-top: 20px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo ($action == 'add') ? 'Tambah' : 'Update'; ?>
                                </button>
                                <a href="alat.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- List -->
            <div class="card">
                <div class="card-header">
                    <h2>Daftar Alat</h2>
                    <a href="alat.php?action=add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Alat
                    </a>
                </div>
                <div class="card-body">
                    <div class="filter-section">
                        <div class="filter-group">
                            <label>Cari</label>
                            <input type="text" id="searchInput" placeholder="Cari nama atau kode alat...">
                        </div>
                        <button class="btn btn-secondary" onclick="resetFilters()">Reset</button>
                    </div>

                    <div class="table-wrapper">
                        <table id="alatTable">
                            <thead>
                                <tr>
                                    <th>Kode Alat</th>
                                    <th>Nama Alat</th>
                                    <th>Kategori</th>
                                    <th>Stok</th>
                                    <th>Kondisi</th>
                                    <th>Lokasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $alat->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['kode_alat']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['nama_alat']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                                        <td>
                                            <span class="badge <?php echo ($row['stok'] > 0) ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo $row['stok']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $kondisi_badge = 'badge-success';
                                            $kondisi_text = 'Baik';
                                            if ($row['kondisi'] == 'kurang_baik') {
                                                $kondisi_badge = 'badge-warning';
                                                $kondisi_text = 'Kurang Baik';
                                            } elseif ($row['kondisi'] == 'rusak') {
                                                $kondisi_badge = 'badge-danger';
                                                $kondisi_text = 'Rusak';
                                            }
                                            ?>
                                            <span class="badge <?php echo $kondisi_badge; ?>"><?php echo $kondisi_text; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['lokasi_rak'] ?? '-'); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="alat.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="deleteAlat(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteAlat(id) {
    confirmDelete('Alat', () => {
        window.location.href = 'alat.php?delete=' + id;
    });
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    filterTable();
}

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const table = document.getElementById('alatTable');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
}

document.getElementById('searchInput').addEventListener('keyup', filterTable);
</script>

<?php include '../includes/footer.php'; ?>
