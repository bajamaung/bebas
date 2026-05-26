<?php
$page_title = 'Manajemen User';
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
        if ($action == 'add' || (isset($_POST['id']) && $_POST['id'] == 0)) {
            // Add new user
            $nama = trim($_POST['nama']);
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            $status = $_POST['status'];

            if (empty($nama) || empty($username) || empty($password)) {
                $error = 'Semua field harus diisi!';
            } else {
                // Check if username already exists
                $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $check->bind_param("s", $username);
                $check->execute();
                
                if ($check->get_result()->num_rows > 0) {
                    $error = 'Username sudah terdaftar!';
                } else {
                    $hashed_password = hashPassword($password);
                    $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role, status) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $nama, $username, $hashed_password, $role, $status);
                    
                    if ($stmt->execute()) {
                        logActivity($conn, $current_user['id'], 'Tambah User', "User $username berhasil ditambahkan", 'users', $conn->insert_id);
                        $message = 'User berhasil ditambahkan!';
                    } else {
                        $error = 'Gagal menambahkan user!';
                    }
                }
            }
        } elseif (isset($_POST['id']) && $_POST['id'] > 0) {
            // Update user
            $id = $_POST['id'];
            $nama = trim($_POST['nama']);
            $status = $_POST['status'];
            $password = $_POST['password'];

            if (empty($nama)) {
                $error = 'Nama harus diisi!';
            } else {
                if (!empty($password)) {
                    $hashed_password = hashPassword($password);
                    $stmt = $conn->prepare("UPDATE users SET nama = ?, status = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $nama, $status, $hashed_password, $id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET nama = ?, status = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $nama, $status, $id);
                }
                
                if ($stmt->execute()) {
                    logActivity($conn, $current_user['id'], 'Edit User', "User dengan ID $id berhasil diupdate", 'users', $id);
                    $message = 'User berhasil diupdate!';
                } else {
                    $error = 'Gagal mengupdate user!';
                }
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    if ($id == $current_user['id']) {
        $error = 'Anda tidak bisa menghapus akun sendiri!';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity($conn, $current_user['id'], 'Hapus User', "User dengan ID $id berhasil dihapus", 'users', $id);
            $message = 'User berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus user!';
        }
    }
}

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

// Get single user for edit
$edit_user = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
}

$csrf_token = generateCSRFToken();
?>

<?php include '../includes/header.php'; ?>

<div class="container-main">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        
        <div class="content">
            <!-- Breadcrumb -->
            <div class="breadcrumb-nav">
                <a href="index.php"><i class="fas fa-home"></i> Dashboard</a>
                <span><i class="fas fa-chevron-right"></i></span>
                <span>User</span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1>Manajemen User</h1>
                <p>Kelola akun user admin, petugas, dan peminjam</p>
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

            <!-- Form Add/Edit -->
            <?php if ($action == 'add' || $action == 'edit'): ?>
                <div class="card" style="margin-bottom: 30px;">
                    <div class="card-header">
                        <h2><?php echo ($action == 'add') ? 'Tambah User Baru' : 'Edit User'; ?></h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="id" value="<?php echo ($edit_user) ? $edit_user['id'] : 0; ?>">

                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label for="nama">Nama Lengkap</label>
                                    <input type="text" id="nama" name="nama" value="<?php echo ($edit_user) ? htmlspecialchars($edit_user['nama']) : ''; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" value="<?php echo ($edit_user) ? htmlspecialchars($edit_user['username']) : ''; ?>" <?php echo ($edit_user) ? 'readonly' : ''; ?> required>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password <?php echo ($edit_user) ? '(Kosongkan jika tidak ingin diubah)' : ''; ?></label>
                                    <input type="password" id="password" name="password" <?php echo (!$edit_user) ? 'required' : ''; ?>>
                                </div>

                                <?php if (!$edit_user): ?>
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <select id="role" name="role" required>
                                        <option value="">Pilih Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="petugas">Petugas</option>
                                        <option value="peminjam">Peminjam</option>
                                    </select>
                                </div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" required>
                                        <option value="aktif" <?php echo ($edit_user && $edit_user['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="nonaktif" <?php echo ($edit_user && $edit_user['status'] == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px; margin-top: 20px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo ($action == 'add') ? 'Tambah' : 'Update'; ?>
                                </button>
                                <a href="user.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Users List -->
            <div class="card">
                <div class="card-header">
                    <h2>Daftar User</h2>
                    <a href="user.php?action=add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah User
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <div class="filter-section">
                        <div class="filter-group">
                            <label>Cari</label>
                            <input type="text" id="searchInput" placeholder="Cari nama atau username...">
                        </div>
                        <div class="filter-group">
                            <label>Role</label>
                            <select id="roleFilter">
                                <option value="">Semua Role</option>
                                <option value="admin">Admin</option>
                                <option value="petugas">Petugas</option>
                                <option value="peminjam">Peminjam</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Status</label>
                            <select id="statusFilter">
                                <option value="">Semua Status</option>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <button class="btn btn-secondary" onclick="resetFilters()">Reset</button>
                    </div>

                    <!-- Table -->
                    <div class="table-wrapper">
                        <table id="userTable">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Terdaftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['nama']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td>
                                            <?php
                                            $role_badge = 'badge-primary';
                                            $role_text = ucfirst($row['role']);
                                            if ($row['role'] == 'admin') $role_badge = 'badge-danger';
                                            if ($row['role'] == 'petugas') $role_badge = 'badge-warning';
                                            ?>
                                            <span class="badge <?php echo $role_badge; ?>"><?php echo $role_text; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo ($row['status'] == 'aktif') ? 'badge-success' : 'badge-danger'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="user.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $row['id']; ?>)">
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
function deleteUser(id) {
    confirmDelete('User', () => {
        window.location.href = 'user.php?delete=' + id;
    });
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('roleFilter').value = '';
    document.getElementById('statusFilter').value = '';
    filterTable();
}

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const role = document.getElementById('roleFilter').value;
    const status = document.getElementById('statusFilter').value;
    const table = document.getElementById('userTable');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const roleCell = row.cells[2].textContent.toLowerCase();
        const statusCell = row.cells[3].textContent.toLowerCase();

        let match = true;
        if (search && !text.includes(search)) match = false;
        if (role && !roleCell.includes(role)) match = false;
        if (status && !statusCell.includes(status)) match = false;

        row.style.display = match ? '' : 'none';
    });
}

document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('roleFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);
</script>

<?php include '../includes/footer.php'; ?>
