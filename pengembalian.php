<?php
$page_title = 'Pengembalian Alat';
require '../config/database.php';
require '../includes/auth.php';

requireRole('admin');

$current_user = getCurrentUser($conn);

$message = '';
$error = '';

// Get pengembalian
$pengembalian = $conn->query("
    SELECT p.*, u.nama, 
           GROUP_CONCAT(CONCAT(a.nama_alat, ' (', dp.jumlah, ')') SEPARATOR ', ') as alat_list
    FROM pengembalian p
    JOIN peminjaman pm ON p.peminjaman_id = pm.id
    JOIN users u ON pm.user_id = u.id
    LEFT JOIN detail_peminjaman dp ON pm.id = dp.peminjaman_id
    LEFT JOIN alat a ON dp.alat_id = a.id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");

// Get pending pengembalian
$pending_pengembalian = $conn->query("
    SELECT pm.*, u.nama, 
           GROUP_CONCAT(CONCAT(a.nama_alat) SEPARATOR ', ') as alat_list
    FROM peminjaman pm
    JOIN users u ON pm.user_id = u.id
    LEFT JOIN detail_peminjaman dp ON pm.id = dp.peminjaman_id
    LEFT JOIN alat a ON dp.alat_id = a.id
    WHERE pm.status = 'sedang_dipinjam'
    GROUP BY pm.id
    ORDER BY pm.deadline_kembali ASC
");
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
                <span>Pengembalian</span>
            </div>

            <div class="page-header">
                <h1>Data Pengembalian Alat</h1>
                <p>Kelola pengembalian dan verifikasi kondisi alat</p>
            </div>

            <!-- Pending Pengembalian -->
            <div class="card" style="margin-bottom: 30px;">
                <div class="card-header">
                    <h2>Peminjaman Menunggu Pengembalian</h2>
                </div>
                <div class="card-body">
                    <?php if ($pending_pengembalian->num_rows > 0): ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Kode Peminjaman</th>
                                        <th>Peminjam</th>
                                        <th>Alat</th>
                                        <th>Deadline Kembali</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $pending_pengembalian->fetch_assoc()): 
                                        $deadline = strtotime($row['deadline_kembali']);
                                        $now = time();
                                        $is_late = $now > $deadline;
                                    ?>
                                        <tr style="background: <?php echo $is_late ? '#fee2e2' : ''; ?>;">
                                            <td><strong><?php echo htmlspecialchars($row['kode_peminjaman']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($row['alat_list']); ?></td>
                                            <td>
                                                <?php echo date('d M Y', $deadline); ?>
                                                <?php if ($is_late): ?>
                                                    <br><span style="color: var(--danger); font-size: 11px; font-weight: 600;">
                                                        <i class="fas fa-exclamation-circle"></i> TERLAMBAT
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">Sedang Dipinjam</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" onclick="recordReturn(<?php echo $row['id']; ?>)">
                                                    <i class="fas fa-check"></i> Catat Pengembalian
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">✓</div>
                            <h3>Tidak ada peminjaman pending</h3>
                            <p>Semua alat yang dipinjam sudah dikembalikan</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Riwayat Pengembalian -->
            <div class="card">
                <div class="card-header">
                    <h2>Riwayat Pengembalian</h2>
                </div>
                <div class="card-body">
                    <?php if ($pengembalian->num_rows > 0): ?>
                        <div class="table-wrapper">
                            <table id="pengembalianTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal Kembali</th>
                                        <th>Peminjam</th>
                                        <th>Alat</th>
                                        <th>Kondisi</th>
                                        <th>Denda</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $pengembalian->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('d M Y', strtotime($row['tanggal_kembali'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($row['alat_list']); ?></td>
                                            <td>
                                                <?php
                                                $kondisi_badge = 'badge-success';
                                                $kondisi_text = 'Baik';
                                                if ($row['kondisi_alat'] == 'kurang_baik') {
                                                    $kondisi_badge = 'badge-warning';
                                                    $kondisi_text = 'Kurang Baik';
                                                } elseif ($row['kondisi_alat'] == 'rusak') {
                                                    $kondisi_badge = 'badge-danger';
                                                    $kondisi_text = 'Rusak';
                                                }
                                                ?>
                                                <span class="badge <?php echo $kondisi_badge; ?>"><?php echo $kondisi_text; ?></span>
                                            </td>
                                            <td><?php echo $row['denda'] > 0 ? 'Rp ' . number_format($row['denda'], 0, ',', '.') : '-'; ?></td>
                                            <td><?php echo htmlspecialchars($row['keterangan'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">📦</div>
                            <h3>Belum ada riwayat pengembalian</h3>
                            <p>Riwayat pengembalian akan muncul di sini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function recordReturn(peminjamanId) {
    Swal.fire({
        title: 'Catat Pengembalian',
        html: `
            <div style="text-align: left;">
                <div class="form-group">
                    <label>Tanggal Kembali</label>
                    <input type="date" id="tglKembali" value="${new Date().toISOString().split('T')[0]}" class="form-group">
                </div>
                <div class="form-group">
                    <label>Kondisi Alat</label>
                    <select id="kondisi" class="form-group">
                        <option value="baik">Baik</option>
                        <option value="kurang_baik">Kurang Baik</option>
                        <option value="rusak">Rusak</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Denda (Rp)</label>
                    <input type="number" id="denda" value="0" min="0" class="form-group">
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea id="keterangan" class="form-group" rows="3" placeholder="Catatan kondisi alat..."></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Simpan Pengembalian',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Record return untuk peminjaman:', peminjamanId);
            showToast('Pengembalian berhasil dicatat', 'success');
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
