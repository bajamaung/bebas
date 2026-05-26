<?php
$page_title = 'Approval Peminjaman';
require '../config/database.php';
require '../includes/auth.php';

requireRole('petugas');

$current_user = getCurrentUser($conn);

$message = '';
$error = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'CSRF Token tidak valid!';
    } else {
        $peminjaman_id = $_POST['peminjaman_id'];
        $action = $_POST['action'];

        if ($action == 'approve') {
            $status = 'disetujui';
            $stmt = $conn->prepare("UPDATE peminjaman SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $peminjaman_id);
            
            if ($stmt->execute()) {
                logActivity($conn, $current_user['id'], 'Approve Peminjaman', "Peminjaman ID $peminjaman_id disetujui", 'peminjaman', $peminjaman_id);
                $message = 'Peminjaman berhasil disetujui!';
            } else {
                $error = 'Gagal menyetujui peminjaman!';
            }
        } elseif ($action == 'reject') {
            $status = 'ditolak';
            $catatan = $_POST['catatan'] ?? '';
            $stmt = $conn->prepare("UPDATE peminjaman SET status = ?, catatan = ? WHERE id = ?");
            $stmt->bind_param("ssi", $status, $catatan, $peminjaman_id);
            
            if ($stmt->execute()) {
                logActivity($conn, $current_user['id'], 'Reject Peminjaman', "Peminjaman ID $peminjaman_id ditolak", 'peminjaman', $peminjaman_id);
                $message = 'Peminjaman berhasil ditolak!';
            } else {
                $error = 'Gagal menolak peminjaman!';
            }
        }
    }
}

// Get pending peminjaman
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'pending';
$pending = $conn->query("
    SELECT p.*, u.nama, u.username,
           GROUP_CONCAT(CONCAT(a.nama_alat, ' (', dp.jumlah, ')') SEPARATOR ', ') as alat_list
    FROM peminjaman p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN detail_peminjaman dp ON p.id = dp.peminjaman_id
    LEFT JOIN alat a ON dp.alat_id = a.id
    WHERE p.status = '$filter_status'
    GROUP BY p.id
    ORDER BY p.created_at DESC
");

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
                <span>Approval Peminjaman</span>
            </div>

            <div class="page-header">
                <h1>Approval Peminjaman</h1>
                <p>Proses dan kelola permintaan peminjaman alat</p>
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

            <!-- Status Tabs -->
            <div style="display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 1px solid var(--border);">
                <a href="approval.php?status=pending" class="btn <?php echo ($filter_status == 'pending') ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo ($filter_status == 'pending') ? 'var(--primary)' : 'transparent'; ?>;">
                    <i class="fas fa-clock"></i> Pending
                </a>
                <a href="approval.php?status=disetujui" class="btn <?php echo ($filter_status == 'disetujui') ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo ($filter_status == 'disetujui') ? 'var(--primary)' : 'transparent'; ?>;">
                    <i class="fas fa-check"></i> Disetujui
                </a>
                <a href="approval.php?status=ditolak" class="btn <?php echo ($filter_status == 'ditolak') ? 'btn-primary' : 'btn-secondary'; ?>" style="border-radius: 0; border-bottom: 3px solid <?php echo ($filter_status == 'ditolak') ? 'var(--primary)' : 'transparent'; ?>;">
                    <i class="fas fa-times"></i> Ditolak
                </a>
            </div>

            <!-- List -->
            <?php if ($pending->num_rows > 0): ?>
                <div class="grid grid-2">
                    <?php while ($row = $pending->fetch_assoc()): ?>
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <h3 style="font-size: 16px; margin-bottom: 5px;">
                                        <strong><?php echo htmlspecialchars($row['kode_peminjaman']); ?></strong>
                                    </h3>
                                    <p style="font-size: 12px; color: var(--gray); margin: 0;">
                                        <?php echo htmlspecialchars($row['nama']); ?> (@<?php echo htmlspecialchars($row['username']); ?>)
                                    </p>
                                </div>
                                <span class="badge badge-warning"><?php echo ucfirst($row['status']); ?></span>
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

                                <?php if ($filter_status == 'pending'): ?>
                                    <form method="POST" action="" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="peminjaman_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Setujui
                                        </button>
                                        
                                        <button type="button" class="btn btn-danger btn-sm" onclick="openRejectForm(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-times"></i> Tolak
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($filter_status == 'ditolak' && $row['catatan']): ?>
                                    <div style="background: #fee2e2; border-left: 4px solid var(--danger); padding: 10px; border-radius: 4px; margin-top: 10px;">
                                        <label style="font-size: 11px; color: var(--danger); font-weight: 600; display: block; margin-bottom: 5px;">Alasan Penolakan</label>
                                        <p style="font-size: 12px; color: #7f1d1d; margin: 0;">
                                            <?php echo htmlspecialchars($row['catatan']); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">✓</div>
                    <h3>Tidak ada peminjaman <?php echo $filter_status; ?></h3>
                    <p>Semua peminjaman dengan status <?php echo $filter_status; ?> sudah diproses</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Reject Form Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <span class="modal-close" onclick="closeRejectForm()">&times;</span>
        
        <div class="modal-header">
            <h2>Tolak Peminjaman</h2>
        </div>

        <form id="rejectForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="peminjaman_id" id="rejectPeminjamanId">
            <input type="hidden" name="action" value="reject">
            
            <div class="form-group">
                <label for="catatan">Alasan Penolakan</label>
                <textarea id="catatan" name="catatan" rows="4" placeholder="Masukkan alasan penolakan..." required></textarea>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-times"></i> Tolak
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeRejectForm()">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectForm(peminjamanId) {
    document.getElementById('rejectPeminjamanId').value = peminjamanId;
    document.getElementById('rejectModal').classList.add('show');
}

function closeRejectForm() {
    document.getElementById('rejectModal').classList.remove('show');
}

document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectForm();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
