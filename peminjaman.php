<?php
$page_title = 'Data Peminjaman';
require '../config/database.php';
require '../includes/auth.php';

requireRole('admin');

$current_user = getCurrentUser($conn);

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "
    SELECT p.*, u.nama, u.username,
           GROUP_CONCAT(CONCAT(a.nama_alat, ' (', dp.jumlah, ')') SEPARATOR ', ') as alat_list
    FROM peminjaman p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN detail_peminjaman dp ON p.id = dp.peminjaman_id
    LEFT JOIN alat a ON dp.alat_id = a.id
";

if (!empty($status_filter)) {
    $query .= " WHERE p.status = '" . $conn->real_escape_string($status_filter) . "'";
}

$query .= " GROUP BY p.id ORDER BY p.created_at DESC";

$peminjaman = $conn->query($query);

// Get status statistics
$pending_count = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE status = 'pending'")->fetch_assoc()['count'];
$disetujui_count = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE status = 'disetujui'")->fetch_assoc()['count'];
$sedang_dipinjam_count = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE status = 'sedang_dipinjam'")->fetch_assoc()['count'];
$sudah_dikembalikan_count = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE status = 'sudah_dikembalikan'")->fetch_assoc()['count'];
$ditolak_count = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE status = 'ditolak'")->fetch_assoc()['count'];
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
                <span>Peminjaman</span>
            </div>

            <div class="page-header">
                <h1>Data Peminjaman</h1>
                <p>Kelola semua data peminjaman alat</p>
            </div>

            <!-- Status Cards -->
            <div class="grid grid-5" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $pending_count; ?></div>
                    <div class="stat-card-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $disetujui_count; ?></div>
                    <div class="stat-card-label">Disetujui</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $sedang_dipinjam_count; ?></div>
                    <div class="stat-card-label">Sedang Dipinjam</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $sudah_dikembalikan_count; ?></div>
                    <div class="stat-card-label">Dikembalikan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $ditolak_count; ?></div>
                    <div class="stat-card-label">Ditolak</div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div style="display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 1px solid var(--border); flex-wrap: wrap;">
                <a href="peminjaman.php" class="btn <?php echo empty($status_filter) ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo empty($status_filter) ? 'var(--primary)' : 'transparent'; ?>;">
                    <i class="fas fa-list"></i> Semua
                </a>
                <a href="peminjaman.php?status=pending" class="btn <?php echo ($status_filter == 'pending') ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo ($status_filter == 'pending') ? 'var(--primary)' : 'transparent'; ?>;">
                    <i class="fas fa-clock"></i> Pending
                </a>
                <a href="peminjaman.php?status=sedang_dipinjam" class="btn <?php echo ($status_filter == 'sedang_dipinjam') ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo ($status_filter == 'sedang_dipinjam') ? 'var(--primary)' : 'transparent'; ?>;">
                    <i class="fas fa-box-open"></i> Sedang Dipinjam
                </a>
                <a href="peminjaman.php?status=sudah_dikembalikan" class="btn <?php echo ($status_filter == 'sudah_dikembalikan') ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo ($status_filter == 'sudah_dikembalikan') ? 'var(--primary)' : 'transparent'; ?>;">
                    <i class="fas fa-check"></i> Dikembalikan
                </a>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-header">
                    <h2>Daftar Peminjaman</h2>
                    <button class="btn btn-secondary btn-sm" onclick="exportToExcel('peminjamanTable', 'peminjaman')">
                        <i class="fas fa-download"></i> Export Excel
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($peminjaman->num_rows > 0): ?>
                        <div class="table-wrapper">
                            <table id="peminjamanTable">
                                <thead>
                                    <tr>
                                        <th>Kode Peminjaman</th>
                                        <th>Peminjam</th>
                                        <th>Alat</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Deadline</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $peminjaman->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($row['kode_peminjaman']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($row['alat_list']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                            <td><?php echo date('d M Y', strtotime($row['deadline_kembali'])); ?></td>
                                            <td>
                                                <?php
                                                $badge_class = 'badge-primary';
                                                $status_text = 'Pending';
                                                
                                                if ($row['status'] == 'disetujui') {
                                                    $badge_class = 'badge-success';
                                                    $status_text = 'Disetujui';
                                                } elseif ($row['status'] == 'sedang_dipinjam') {
                                                    $badge_class = 'badge-warning';
                                                    $status_text = 'Sedang Dipinjam';
                                                } elseif ($row['status'] == 'sudah_dikembalikan') {
                                                    $badge_class = 'badge-success';
                                                    $status_text = 'Sudah Dikembalikan';
                                                } elseif ($row['status'] == 'ditolak') {
                                                    $badge_class = 'badge-danger';
                                                    $status_text = 'Ditolak';
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="viewDetail(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-eye"></i> Detail
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <h3>Tidak ada data</h3>
                            <p>Tidak ada peminjaman dengan status yang dipilih</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewDetail(id) {
    console.log('View detail peminjaman:', id);
    // Implementasi detail view di sini
}
</script>

<?php include '../includes/footer.php'; ?>
