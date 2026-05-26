<?php
$page_title = 'Riwayat Peminjaman';
require '../config/database.php';
require '../includes/auth.php';

requireRole('peminjam');

$current_user = getCurrentUser($conn);

// Get status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$query = "
    SELECT p.*, 
           GROUP_CONCAT(CONCAT(a.nama_alat, ' (', dp.jumlah, ')') SEPARATOR ', ') as alat_list,
           COUNT(dp.id) as jumlah_alat
    FROM peminjaman p
    LEFT JOIN detail_peminjaman dp ON p.id = dp.peminjaman_id
    LEFT JOIN alat a ON dp.alat_id = a.id
    WHERE p.user_id = {$current_user['id']}
";

if (!empty($status_filter)) {
    $query .= " AND p.status = '" . $conn->real_escape_string($status_filter) . "'";
}

$query .= " GROUP BY p.id ORDER BY p.created_at DESC";

$peminjaman = $conn->query($query);

// Get status statistics
$pending_count = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE user_id = {$current_user['id']} AND status = 'pending'")->fetch_assoc()['count'];
$sedang_dipinjam_count = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE user_id = {$current_user['id']} AND status = 'sedang_dipinjam'")->fetch_assoc()['count'];
$sudah_dikembalikan_count = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE user_id = {$current_user['id']} AND status = 'sudah_dikembalikan'")->fetch_assoc()['count'];
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
                <span>Riwayat Peminjaman</span>
            </div>

            <div class="page-header">
                <h1>Riwayat Peminjaman</h1>
                <p>Lihat semua riwayat peminjaman Anda</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-3" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $pending_count; ?></div>
                    <div class="stat-card-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $sedang_dipinjam_count; ?></div>
                    <div class="stat-card-label">Sedang Dipinjam</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $sudah_dikembalikan_count; ?></div>
                    <div class="stat-card-label">Dikembalikan</div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div style="display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 1px solid var(--border); flex-wrap: wrap;">
                <a href="riwayat.php" class="btn <?php echo empty($status_filter) ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo empty($status_filter) ? 'var(--primary)' : 'transparent'; ?>;">
                    <i class="fas fa-list"></i> Semua
                </a>
                <a href="riwayat.php?status=pending" class="btn <?php echo ($status_filter == 'pending') ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo ($status_filter == 'pending') ? 'var(--primary)' : 'transparent'; ?>;">
                    <i class="fas fa-clock"></i> Pending
                </a>
                <a href="riwayat.php?status=sedang_dipinjam" class="btn <?php echo ($status_filter == 'sedang_dipinjam') ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo ($status_filter == 'sedang_dipinjam') ? 'var(--primary)' : 'transparent'; ?>;">
                    <i class="fas fa-box-open"></i> Sedang Dipinjam
                </a>
                <a href="riwayat.php?status=sudah_dikembalikan" class="btn <?php echo ($status_filter == 'sudah_dikembalikan') ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo ($status_filter == 'sudah_dikembalikan') ? 'var(--primary)' : 'transparent'; ?>;">
                    <i class="fas fa-check"></i> Dikembalikan
                </a>
            </div>

            <!-- List -->
            <?php if ($peminjaman->num_rows > 0): ?>
                <div class="grid grid-2">
                    <?php while ($row = $peminjaman->fetch_assoc()): ?>
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h3 style="font-size: 16px; margin-bottom: 5px;">
                                        <strong><?php echo htmlspecialchars($row['kode_peminjaman']); ?></strong>
                                    </h3>
                                    <p style="font-size: 12px; color: var(--gray); margin: 0;">
                                        <?php echo date('d M Y H:i', strtotime($row['created_at'])); ?>
                                    </p>
                                </div>
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
                                    $status_text = 'Dikembalikan';
                                } elseif ($row['status'] == 'ditolak') {
                                    $badge_class = 'badge-danger';
                                    $status_text = 'Ditolak';
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                            </div>

                            <div class="card-body">
                                <div style="margin-bottom: 15px;">
                                    <label style="font-size: 11px; color: var(--gray); text-transform: uppercase; font-weight: 600; margin-bottom: 5px; display: block;">Alat yang Dipinjam</label>
                                    <p style="font-size: 13px; color: var(--secondary); margin: 0;">
                                        <?php echo htmlspecialchars($row['alat_list']); ?>
                                    </p>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; font-size: 12px;">
                                    <div>
                                        <label style="color: var(--gray); display: block; margin-bottom: 3px;">Tanggal Pinjam</label>
                                        <strong><?php echo date('d M Y', strtotime($row['tanggal_pinjam'])); ?></strong>
                                    </div>
                                    <div>
                                        <label style="color: var(--gray); display: block; margin-bottom: 3px;">Deadline Kembali</label>
                                        <strong><?php echo date('d M Y', strtotime($row['deadline_kembali'])); ?></strong>
                                    </div>
                                </div>

                                <?php if ($row['status'] == 'sedang_dipinjam'): ?>
                                    <button class="btn btn-primary" style="width: 100%;">
                                        <i class="fas fa-undo"></i> Kembalikan Alat
                                    </button>
                                <?php elseif ($row['status'] == 'pending'): ?>
                                    <p style="font-size: 12px; color: var(--gray); padding: 10px; background: var(--light); border-radius: 6px; margin: 0;">
                                        <i class="fas fa-info-circle"></i> Menunggu approval dari petugas
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>Tidak ada riwayat peminjaman</h3>
                    <p>Anda belum pernah melakukan peminjaman alat</p>
                    <a href="katalog.php" class="btn btn-primary">Mulai Peminjaman</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
