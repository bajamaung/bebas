<?php
$page_title = 'Dashboard Admin';
require '../config/database.php';
require '../includes/auth.php';

// Require Admin role
requireRole('admin');

$current_user = getCurrentUser($conn);

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin'")->fetch_assoc()['count'];
$total_alat = $conn->query("SELECT COUNT(*) as count FROM alat")->fetch_assoc()['count'];
$total_kategori = $conn->query("SELECT COUNT(*) as count FROM kategori")->fetch_assoc()['count'];
$total_peminjaman_aktif = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE status IN ('sedang_dipinjam')")->fetch_assoc()['count'];

// Get today's returns
$today = date('Y-m-d');
$total_pengembalian_hari_ini = $conn->query("SELECT COUNT(*) as count FROM pengembalian WHERE DATE(tanggal_kembali) = '$today'")->fetch_assoc()['count'];

// Get recent activities
$recent_activities = $conn->query("
    SELECT * FROM log_aktivitas 
    ORDER BY created_at DESC 
    LIMIT 10
");
?>

<?php include '../includes/header.php'; ?>

<div class="container-main">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        
        <div class="content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Selamat Datang, <?php echo htmlspecialchars($current_user['nama']); ?>! 👋</h1>
                <p>Kelola sistem peminjaman alat dengan mudah</p>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-4">
                <div class="stat-card">
                    <div class="stat-card-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-card-value"><?php echo $total_users; ?></div>
                    <div class="stat-card-label">Total User</div>
                    <div class="stat-card-desc">
                        <i class="fas fa-arrow-up" style="color: var(--success);"></i>
                        <span>Pengguna aktif sistem</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon success">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-card-value"><?php echo $total_alat; ?></div>
                    <div class="stat-card-label">Total Alat</div>
                    <div class="stat-card-desc">
                        <i class="fas fa-arrow-up" style="color: var(--success);"></i>
                        <span>Alat dalam sistem</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon warning">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="stat-card-value"><?php echo $total_kategori; ?></div>
                    <div class="stat-card-label">Total Kategori</div>
                    <div class="stat-card-desc">
                        <i class="fas fa-arrow-up" style="color: var(--success);"></i>
                        <span>Kategori alat</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon danger">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-card-value"><?php echo $total_peminjaman_aktif; ?></div>
                    <div class="stat-card-label">Peminjaman Aktif</div>
                    <div class="stat-card-desc">
                        <i class="fas fa-arrow-up" style="color: var(--success);"></i>
                        <span>Sedang dipinjam</span>
                    </div>
                </div>
            </div>

            <!-- Charts and Recent Activities Row -->
            <div class="grid grid-2">
                <!-- Chart -->
                <div class="card">
                    <div class="card-header">
                        <h2>Statistik Peminjaman (30 Hari Terakhir)</h2>
                        <span style="font-size: 12px; color: var(--gray);">Periode bulan ini</span>
                    </div>
                    <div class="card-body" style="position: relative; height: 300px;">
                        <canvas id="chartPeminjaman"></canvas>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="card">
                    <div class="card-header">
                        <h2>Aktivitas Terbaru</h2>
                        <a href="log.php" style="font-size: 12px; color: var(--primary);">Lihat semua</a>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php
                            $count = 0;
                            while ($activity = $recent_activities->fetch_assoc() && $count < 8):
                                $count++;
                                $user = $conn->query("SELECT nama FROM users WHERE id = " . $activity['user_id'])->fetch_assoc();
                            ?>
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <div class="timeline-date"><?php echo date('d M Y H:i', strtotime($activity['created_at'])); ?></div>
                                    <div class="timeline-desc">
                                        <strong><?php echo htmlspecialchars($user['nama']); ?></strong><br>
                                        <span style="color: var(--gray);"><?php echo htmlspecialchars($activity['aktivitas']); ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card" style="margin-top: 30px;">
                <div class="card-header">
                    <h2>Aksi Cepat</h2>
                </div>
                <div class="card-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                    <a href="user.php" class="btn btn-primary" style="justify-content: center;">
                        <i class="fas fa-plus"></i> Tambah User
                    </a>
                    <a href="alat.php" class="btn btn-primary" style="justify-content: center;">
                        <i class="fas fa-plus"></i> Tambah Alat
                    </a>
                    <a href="kategori.php" class="btn btn-primary" style="justify-content: center;">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </a>
                    <a href="peminjaman.php" class="btn btn-primary" style="justify-content: center;">
                        <i class="fas fa-clipboard-list"></i> Lihat Peminjaman
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Chart for Peminjaman Statistics
const ctx = document.getElementById('chartPeminjaman').getContext('2d');
const chartPeminjaman = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Hari 1', 'Hari 5', 'Hari 10', 'Hari 15', 'Hari 20', 'Hari 25', 'Hari 30'],
        datasets: [{
            label: 'Peminjaman',
            data: [12, 19, 15, 25, 22, 30, 28],
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#2563eb',
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#6b7280'
                },
                grid: {
                    color: 'rgba(229, 231, 235, 0.3)'
                }
            },
            x: {
                ticks: {
                    color: '#6b7280'
                },
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
