<?php
$page_title = 'Log Aktivitas';
require '../config/database.php';
require '../includes/auth.php';

requireRole('admin');

$current_user = getCurrentUser($conn);

// Get filter
$user_filter = isset($_GET['user']) ? $_GET['user'] : '';
$aktivitas_filter = isset($_GET['aktivitas']) ? $_GET['aktivitas'] : '';
$tabel_filter = isset($_GET['tabel']) ? $_GET['tabel'] : '';

// Build query
$query = "SELECT la.*, u.nama FROM log_aktivitas la JOIN users u ON la.user_id = u.id WHERE 1=1";

if (!empty($user_filter)) {
    $query .= " AND la.user_id = " . intval($user_filter);
}

if (!empty($aktivitas_filter)) {
    $query .= " AND la.aktivitas LIKE '%" . $conn->real_escape_string($aktivitas_filter) . "%'";
}

if (!empty($tabel_filter)) {
    $query .= " AND la.tabel = '" . $conn->real_escape_string($tabel_filter) . "'";
}

$query .= " ORDER BY la.created_at DESC LIMIT 500";

$logs = $conn->query($query);

// Get all users for filter
$users = $conn->query("SELECT id, nama FROM users ORDER BY nama ASC");

// Get unique aktivitas
$aktivitas_list = $conn->query("SELECT DISTINCT aktivitas FROM log_aktivitas ORDER BY aktivitas ASC");
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
                <span>Log Aktivitas</span>
            </div>

            <div class="page-header">
                <h1>Log Aktivitas Sistem</h1>
                <p>Pantau semua aktivitas pengguna dalam sistem</p>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <div class="filter-group">
                    <label>User</label>
                    <select id="userFilter">
                        <option value="">Semua User</option>
                        <?php while ($row = $users->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($user_filter == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['nama']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Aktivitas</label>
                    <select id="aktivitasFilter">
                        <option value="">Semua Aktivitas</option>
                        <?php while ($row = $aktivitas_list->fetch_assoc()): ?>
                            <option value="<?php echo $row['aktivitas']; ?>" <?php echo ($aktivitas_filter == $row['aktivitas']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['aktivitas']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Tabel</label>
                    <select id="tabelFilter">
                        <option value="">Semua Tabel</option>
                        <option value="users" <?php echo ($tabel_filter == 'users') ? 'selected' : ''; ?>>Users</option>
                        <option value="alat" <?php echo ($tabel_filter == 'alat') ? 'selected' : ''; ?>>Alat</option>
                        <option value="kategori" <?php echo ($tabel_filter == 'kategori') ? 'selected' : ''; ?>>Kategori</option>
                        <option value="peminjaman" <?php echo ($tabel_filter == 'peminjaman') ? 'selected' : ''; ?>>Peminjaman</option>
                        <option value="pengembalian" <?php echo ($tabel_filter == 'pengembalian') ? 'selected' : ''; ?>>Pengembalian</option>
                    </select>
                </div>

                <button class="btn btn-secondary" onclick="applyFilters()">Filter</button>
                <button class="btn btn-secondary" onclick="resetFilters()">Reset</button>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-header">
                    <h2>Daftar Aktivitas</h2>
                    <button class="btn btn-secondary btn-sm" onclick="exportToExcel('logTable', 'log_aktivitas')">
                        <i class="fas fa-download"></i> Export Excel
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($logs->num_rows > 0): ?>
                        <div class="table-wrapper">
                            <table id="logTable">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>User</th>
                                        <th>Aktivitas</th>
                                        <th>Tabel</th>
                                        <th>Record ID</th>
                                        <th>Deskripsi</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $logs->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo date('d M Y H:i:s', strtotime($row['created_at'])); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td>
                                                <?php
                                                $badge = 'badge-primary';
                                                if ($row['aktivitas'] == 'Hapus') $badge = 'badge-danger';
                                                if ($row['aktivitas'] == 'Edit') $badge = 'badge-warning';
                                                if ($row['aktivitas'] == 'Login') $badge = 'badge-success';
                                                if ($row['aktivitas'] == 'Logout') $badge = 'badge-primary';
                                                ?>
                                                <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($row['aktivitas']); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['tabel']); ?></td>
                                            <td><?php echo $row['record_id'] ?? '-'; ?></td>
                                            <td><?php echo htmlspecialchars(substr($row['deskripsi'] ?? '', 0, 50)); ?></td>
                                            <td><code style="font-size: 11px; background: var(--light); padding: 3px 8px; border-radius: 3px;"><?php echo htmlspecialchars($row['ip_address']); ?></code></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <h3>Tidak ada log aktivitas</h3>
                            <p>Tidak ada aktivitas yang cocok dengan filter yang dipilih</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const user = document.getElementById('userFilter').value;
    const aktivitas = document.getElementById('aktivitasFilter').value;
    const tabel = document.getElementById('tabelFilter').value;
    
    let url = 'log.php?';
    if (user) url += 'user=' + user + '&';
    if (aktivitas) url += 'aktivitas=' + encodeURIComponent(aktivitas) + '&';
    if (tabel) url += 'tabel=' + tabel;
    
    window.location.href = url;
}

function resetFilters() {
    window.location.href = 'log.php';
}
</script>

<?php include '../includes/footer.php'; ?>
