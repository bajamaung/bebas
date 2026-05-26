<?php
$page_title = 'Laporan';
require '../config/database.php';
require '../includes/auth.php';

requireRole('petugas');

$current_user = getCurrentUser($conn);

// Get date range for filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
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
                <span>Laporan</span>
            </div>

            <div class="page-header">
                <h1>Laporan Peminjaman</h1>
                <p>Generate laporan peminjaman dalam format PDF atau Excel</p>
            </div>

            <!-- Filter -->
            <div class="card" style="margin-bottom: 30px;">
                <div class="card-header">
                    <h2>Filter Laporan</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
                        <div class="form-group" style="margin: 0;">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="laporan.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Laporan Options -->
            <div class="grid grid-3" style="margin-bottom: 30px;">
                <div class="card" style="cursor: pointer; text-align: center;" onclick="generatePDF()">
                    <div class="card-body">
                        <div style="font-size: 40px; color: #dc2626; margin-bottom: 15px;">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <h3 style="margin-bottom: 5px;">Laporan PDF</h3>
                        <p style="color: var(--gray); font-size: 13px;">Download laporan dalam format PDF</p>
                    </div>
                </div>

                <div class="card" style="cursor: pointer; text-align: center;" onclick="generateExcel()">
                    <div class="card-body">
                        <div style="font-size: 40px; color: #16a34a; margin-bottom: 15px;">
                            <i class="fas fa-file-excel"></i>
                        </div>
                        <h3 style="margin-bottom: 5px;">Laporan Excel</h3>
                        <p style="color: var(--gray); font-size: 13px;">Download laporan dalam format Excel</p>
                    </div>
                </div>

                <div class="card" style="cursor: pointer; text-align: center;" onclick="printReport()">
                    <div class="card-body">
                        <div style="font-size: 40px; color: #2563eb; margin-bottom: 15px;">
                            <i class="fas fa-print"></i>
                        </div>
                        <h3 style="margin-bottom: 5px;">Cetak Laporan</h3>
                        <p style="color: var(--gray); font-size: 13px;">Cetak laporan langsung</p>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="grid grid-4" style="margin-bottom: 30px;">
                <?php
                $total_peminjaman = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE DATE(tanggal_pinjam) BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['count'];
                $total_disetujui = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE DATE(tanggal_pinjam) BETWEEN '$start_date' AND '$end_date' AND status IN ('disetujui', 'sedang_dipinjam', 'sudah_dikembalikan')")->fetch_assoc()['count'];
                $total_ditolak = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE DATE(tanggal_pinjam) BETWEEN '$start_date' AND '$end_date' AND status = 'ditolak'")->fetch_assoc()['count'];
                $total_terlambat = $conn->query("SELECT COUNT(*) as count FROM pengembalian WHERE DATE(tanggal_kembali) BETWEEN '$start_date' AND '$end_date' AND denda > 0")->fetch_assoc()['count'];
                ?>

                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $total_peminjaman; ?></div>
                    <div class="stat-card-label">Total Peminjaman</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $total_disetujui; ?></div>
                    <div class="stat-card-label">Disetujui</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $total_ditolak; ?></div>
                    <div class="stat-card-label">Ditolak</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-value"><?php echo $total_terlambat; ?></div>
                    <div class="stat-card-label">Terlambat</div>
                </div>
            </div>

            <!-- Sample Report Table -->
            <div class="card">
                <div class="card-header">
                    <h2>Preview Data</h2>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal Pinjam</th>
                                    <th>Kode Peminjaman</th>
                                    <th>Peminjam</th>
                                    <th>Status</th>
                                    <th>Deadline</th>
                                    <th>Kondisi Return</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $report = $conn->query("
                                    SELECT p.*, u.nama,
                                           COALESCE(r.kondisi_alat, 'Belum Dikembalikan') as kondisi_alat,
                                           COALESCE(r.denda, 0) as denda
                                    FROM peminjaman p
                                    JOIN users u ON p.user_id = u.id
                                    LEFT JOIN pengembalian r ON p.id = r.peminjaman_id
                                    WHERE DATE(p.tanggal_pinjam) BETWEEN '$start_date' AND '$end_date'
                                    ORDER BY p.tanggal_pinjam DESC
                                    LIMIT 10
                                ");

                                while ($row = $report->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?php echo date('d M Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['kode_peminjaman']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
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
                                                $status_text = 'Dikembalikan';
                                            } elseif ($row['status'] == 'ditolak') {
                                                $badge_class = 'badge-danger';
                                                $status_text = 'Ditolak';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($row['deadline_kembali'])); ?></td>
                                        <td><?php echo htmlspecialchars($row['kondisi_alat']); ?></td>
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
function generatePDF() {
    const startDate = new URLSearchParams(window.location.search).get('start_date') || '<?php echo $start_date; ?>';
    const endDate = new URLSearchParams(window.location.search).get('end_date') || '<?php echo $end_date; ?>';
    console.log('Generate PDF:', startDate, endDate);
    showToast('PDF akan didownload...', 'info');
    // Implementasi PDF export dengan library seperti jsPDF atau TCPDF
}

function generateExcel() {
    const startDate = new URLSearchParams(window.location.search).get('start_date') || '<?php echo $start_date; ?>';
    const endDate = new URLSearchParams(window.location.search).get('end_date') || '<?php echo $end_date; ?>';
    console.log('Generate Excel:', startDate, endDate);
    showToast('Excel akan didownload...', 'info');
    // Implementasi Excel export dengan library seperti PhpSpreadsheet
}

function printReport() {
    window.print();
}
</script>

<?php include '../includes/footer.php'; ?>
