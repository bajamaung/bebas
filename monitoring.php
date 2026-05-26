<?php
$page_title = 'Monitoring Peminjaman';
require '../config/database.php';
require '../includes/auth.php';

requireRole('petugas');

$current_user = getCurrentUser($conn);

// Get all active peminjaman
$sedang_dipinjam = $conn->query("
    SELECT pm.*, u.nama,
           GROUP_CONCAT(CONCAT(a.nama_alat, ' (', dp.jumlah, ')') SEPARATOR ', ') as alat_list
    FROM peminjaman pm
    JOIN users u ON pm.user_id = u.id
    LEFT JOIN detail_peminjaman dp ON pm.id = dp.peminjaman_id
    LEFT JOIN alat a ON dp.alat_id = a.id
    WHERE pm.status = 'sedang_dipinjam'
    GROUP BY pm.id
    ORDER BY pm.deadline_kembali ASC
");

// Get terlambat
$terlambat = $conn->query("
    SELECT pm.*, u.nama,
           GROUP_CONCAT(CONCAT(a.nama_alat) SEPARATOR ', ') as alat_list
    FROM peminjaman pm
    JOIN users u ON pm.user_id = u.id
    LEFT JOIN detail_peminjaman dp ON pm.id = dp.peminjaman_id
    LEFT JOIN alat a ON dp.alat_id = a.id
    WHERE pm.status = 'sedang_dipinjam' AND pm.deadline_kembali < DATE(NOW())
    GROUP BY pm.id
    ORDER BY pm.deadline_kembali ASC
");

$terlambat_count = $terlambat->num_rows;
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
                <span>Monitoring</span>
            </div>

            <div class="page-header">
                <h1>Monitoring Peminjaman</h1>
                <p>Pantau status alat yang sedang dipinjam</p>
            </div>

            <!-- Alert Terlambat -->
            <?php if ($terlambat_count > 0): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong><?php echo $terlambat_count; ?> Peminjaman Terlambat!</strong>
                    Ada alat yang belum dikembalikan sesuai deadline. Segera hubungi peminjam.
                </div>
            <?php endif; ?>

            <!-- Filter Tabs -->
            <div style="display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 1px solid var(--border);">
                <a href="monitoring.php?filter=all" class="btn btn-primary" style="border-radius: 0; border-bottom: 3px solid var(--primary);">
                    <i class="fas fa-list"></i> Semua Peminjaman
                </a>
                <?php if ($terlambat_count > 0): ?>
                    <a href="monitoring.php?filter=late" class="btn btn-danger" style="border-radius: 0;">
                        <i class="fas fa-exclamation-circle"></i> Terlambat (<?php echo $terlambat_count; ?>)
                    </a>
                <?php endif; ?>
            </div>

            <!-- Grid View -->
            <?php if ($sedang_dipinjam->num_rows > 0): ?>
                <div class="grid grid-2" style="margin-bottom: 30px;">
                    <?php 
                    $sedang_dipinjam->data_seek(0);
                    while ($row = $sedang_dipinjam->fetch_assoc()):
                        $deadline = strtotime($row['deadline_kembali']);
                        $now = time();
                        $is_late = $now > $deadline;
                        $days_left = ceil(($deadline - $now) / (24 * 60 * 60));
                    ?>
                        <div class="card" style="border-left: 4px solid <?php echo $is_late ? 'var(--danger)' : 'var(--primary)'; ?>;">
                            <div class="card-header">
                                <div>
                                    <h3 style="font-size: 16px; margin-bottom: 5px;">
                                        <strong><?php echo htmlspecialchars($row['kode_peminjaman']); ?></strong>
                                    </h3>
                                    <p style="font-size: 12px; color: var(--gray); margin: 0;">
                                        <?php echo htmlspecialchars($row['nama']); ?>
                                    </p>
                                </div>
                                <span class="badge badge-warning">Sedang Dipinjam</span>
                            </div>

                            <div class="card-body">
                                <div style="margin-bottom: 15px;">
                                    <label style="font-size: 11px; color: var(--gray); text-transform: uppercase; font-weight: 600; margin-bottom: 5px; display: block;">Alat</label>
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
                                        <strong style="color: <?php echo $is_late ? 'var(--danger)' : 'var(--success)'; ?>;">
                                            <?php echo date('d M Y', $deadline); ?>
                                        </strong>
                                    </div>
                                </div>

                                <?php if ($is_late): ?>
                                    <div style="background: #fee2e2; border-left: 4px solid var(--danger); padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                                        <p style="font-size: 12px; color: var(--danger); font-weight: 600; margin: 0;">
                                            <i class="fas fa-exclamation-circle"></i> TERLAMBAT <?php echo abs($days_left); ?> HARI!
                                        </p>
                                    </div>
                                <?php else: ?>
                                    <div style="background: #dcfce7; border-left: 4px solid var(--success); padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                                        <p style="font-size: 12px; color: var(--success); font-weight: 600; margin: 0;">
                                            <i class="fas fa-check-circle"></i> Sisa <?php echo $days_left; ?> hari
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-primary" onclick="contactPeminjam(<?php echo $row['user_id']; ?>)">
                                        <i class="fas fa-phone"></i> Hubungi
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="markAsReturned(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-check"></i> Sudah Kembali
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">✓</div>
                    <h3>Tidak ada alat yang sedang dipinjam</h3>
                    <p>Semua alat telah dikembalikan</p>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <h2>Statistik</h2>
                </div>
                <div class="card-body">
                    <div class="grid grid-3">
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo $sedang_dipinjam->num_rows; ?></div>
                            <div class="stat-card-label">Total Sedang Dipinjam</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo $terlambat_count; ?></div>
                            <div class="stat-card-label">Terlambat</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-card-value"><?php echo $sedang_dipinjam->num_rows - $terlambat_count; ?></div>
                            <div class="stat-card-label">Tepat Waktu</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function contactPeminjam(userId) {
    Swal.fire({
        title: 'Hubungi Peminjam',
        text: 'Fitur hubungi peminjam akan ditambahkan. Untuk saat ini, hubungi secara manual.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function markAsReturned(peminjamanId) {
    Swal.fire({
        title: 'Konfirmasi Pengembalian',
        text: 'Alat sudah dikembalikan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        confirmButtonText: 'Ya, Sudah Dikembalikan'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Mark as returned:', peminjamanId);
            showToast('Status peminjaman berhasil diupdate', 'success');
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
