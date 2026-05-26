<?php
$page_title = 'Manajemen Kategori';
require '../config/database.php';
require '../includes/auth.php';

requireRole('admin');

$current_user = getCurrentUser($conn);

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'CSRF Token tidak valid!';
    } else {
        $nama_kategori = trim($_POST['nama_kategori']);
        $deskripsi = trim($_POST['deskripsi']);

        if (empty($nama_kategori)) {
            $error = 'Nama kategori harus diisi!';
        } else {
            if (isset($_POST['id']) && $_POST['id'] == 0) {
                // Add new
                $check = $conn->prepare("SELECT id FROM kategori WHERE nama_kategori = ?");
                $check->bind_param("s", $nama_kategori);
                $check->execute();
                
                if ($check->get_result()->num_rows > 0) {
                    $error = 'Kategori sudah ada!';
                } else {
                    $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)");
                    $stmt->bind_param("ss", $nama_kategori, $deskripsi);
                    
                    if ($stmt->execute()) {
                        logActivity($conn, $current_user['id'], 'Tambah Kategori', "Kategori $nama_kategori ditambahkan", 'kategori', $conn->insert_id);
                        $message = 'Kategori berhasil ditambahkan!';
                    } else {
                        $error = 'Gagal menambahkan kategori!';
                    }
                }
            } else {
                // Update
                $id = $_POST['id'];
                $stmt = $conn->prepare("UPDATE kategori SET nama_kategori = ?, deskripsi = ? WHERE id = ?");
                $stmt->bind_param("ssi", $nama_kategori, $deskripsi, $id);
                
                if ($stmt->execute()) {
                    logActivity($conn, $current_user['id'], 'Edit Kategori', "Kategori ID $id diupdate", 'kategori', $id);
                    $message = 'Kategori berhasil diupdate!';
                } else {
                    $error = 'Gagal mengupdate kategori!';
                }
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if kategori has alat
    $check = $conn->query("SELECT COUNT(*) as count FROM alat WHERE kategori_id = $id");
    $count = $check->fetch_assoc()['count'];
    
    if ($count > 0) {
        $error = 'Kategori tidak bisa dihapus karena masih memiliki alat!';
    } else {
        $stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity($conn, $current_user['id'], 'Hapus Kategori', "Kategori ID $id dihapus", 'kategori', $id);
            $message = 'Kategori berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus kategori!';
        }
    }
}

// Get all categories
$kategori = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Get single category for edit
$edit_kategori = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_kategori = $stmt->get_result()->fetch_assoc();
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
                <span>Kategori</span>
            </div>

            <div class="page-header">
                <h1>Manajemen Kategori</h1>
                <p>Kelola kategori alat peminjaman</p>
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
                        <h2><?php echo ($action == 'add') ? 'Tambah Kategori Baru' : 'Edit Kategori'; ?></h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="id" value="<?php echo ($edit_kategori) ? $edit_kategori['id'] : 0; ?>">

                            <div class="form-group">
                                <label for="nama_kategori">Nama Kategori</label>
                                <input type="text" id="nama_kategori" name="nama_kategori" value="<?php echo ($edit_kategori) ? htmlspecialchars($edit_kategori['nama_kategori']) : ''; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="deskripsi">Deskripsi</label>
                                <textarea id="deskripsi" name="deskripsi" rows="4"><?php echo ($edit_kategori) ? htmlspecialchars($edit_kategori['deskripsi']) : ''; ?></textarea>
                            </div>

                            <div style="display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo ($action == 'add') ? 'Tambah' : 'Update'; ?>
                                </button>
                                <a href="kategori.php" class="btn btn-secondary">
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
                    <h2>Daftar Kategori</h2>
                    <a href="kategori.php?action=add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </a>
                </div>
                <div class="card-body">
                    <div class="grid grid-3">
                        <?php while ($row = $kategori->fetch_assoc()): 
                            $alat_count = $conn->query("SELECT COUNT(*) as count FROM alat WHERE kategori_id = {$row['id']}")->fetch_assoc()['count'];
                        ?>
                            <div style="border: 1px solid var(--border); border-radius: 12px; padding: 20px; background: white;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                    <div style="flex: 1;">
                                        <h3 style="font-size: 16px; font-weight: 600; color: var(--secondary); margin-bottom: 5px;">
                                            <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                        </h3>
                                        <p style="font-size: 12px; color: var(--gray); line-height: 1.5;">
                                            <?php echo htmlspecialchars($row['deskripsi'] ?? 'Tidak ada deskripsi'); ?>
                                        </p>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 15px; border-top: 1px solid var(--border);">
                                    <span style="font-size: 12px; color: var(--gray);">
                                        <i class="fas fa-box"></i> <?php echo $alat_count; ?> Alat
                                    </span>
                                    <div class="action-buttons">
                                        <a href="kategori.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="deleteKategori(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteKategori(id) {
    confirmDelete('Kategori', () => {
        window.location.href = 'kategori.php?delete=' + id;
    });
}
</script>

<?php include '../includes/footer.php'; ?>
