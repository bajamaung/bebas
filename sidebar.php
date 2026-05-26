<?php
// includes/sidebar.php
// Pastikan auth.php sudah di-include sebelum file ini
if (!isset($current_user)) {
    $current_user = getCurrentUser($conn);
}

// Tentukan menu berdasarkan role
$menu_items = [];

if ($current_user['role'] == 'admin') {
    $menu_items = [
        ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'url' => 'index.php', 'route' => 'admin/index.php'],
        ['icon' => 'fas fa-users', 'label' => 'User', 'url' => 'user.php', 'route' => 'admin/user.php'],
        ['icon' => 'fas fa-boxes', 'label' => 'Alat', 'url' => 'alat.php', 'route' => 'admin/alat.php'],
        ['icon' => 'fas fa-tag', 'label' => 'Kategori', 'url' => 'kategori.php', 'route' => 'admin/kategori.php'],
        ['icon' => 'fas fa-clipboard-list', 'label' => 'Peminjaman', 'url' => 'peminjaman.php', 'route' => 'admin/peminjaman.php'],
        ['icon' => 'fas fa-undo', 'label' => 'Pengembalian', 'url' => 'pengembalian.php', 'route' => 'admin/pengembalian.php'],
        ['icon' => 'fas fa-history', 'label' => 'Log Aktivitas', 'url' => 'log.php', 'route' => 'admin/log.php'],
    ];
} elseif ($current_user['role'] == 'petugas') {
    $menu_items = [
        ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'url' => 'index.php', 'route' => 'petugas/index.php'],
        ['icon' => 'fas fa-check-circle', 'label' => 'Approval Peminjaman', 'url' => 'approval.php', 'route' => 'petugas/approval.php'],
        ['icon' => 'fas fa-box-open', 'label' => 'Pemantauan', 'url' => 'monitoring.php', 'route' => 'petugas/monitoring.php'],
        ['icon' => 'fas fa-file-pdf', 'label' => 'Laporan', 'url' => 'laporan.php', 'route' => 'petugas/laporan.php'],
    ];
} else { // peminjam
    $menu_items = [
        ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'url' => 'index.php', 'route' => 'peminjam/index.php'],
        ['icon' => 'fas fa-boxes', 'label' => 'Daftar Alat', 'url' => 'katalog.php', 'route' => 'peminjam/katalog.php'],
        ['icon' => 'fas fa-history', 'label' => 'Riwayat Peminjaman', 'url' => 'riwayat.php', 'route' => 'peminjam/riwayat.php'],
    ];
}

// Tentukan current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-boxes"></i>
        <span>Bangjo</span>
    </div>

    <div class="sidebar-menu">
        <?php foreach ($menu_items as $item): ?>
            <?php
            $is_active = ($item['url'] == $current_page) ? 'active' : '';
            ?>
            <a href="<?php echo BASE_URL . str_replace('admin/', '', str_replace('petugas/', '', str_replace('peminjam/', '', $item['route']))); ?>" class="sidebar-menu-item <?php echo $is_active; ?>">
                <i class="<?php echo $item['icon']; ?>"></i>
                <span><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>

        <a href="<?php echo BASE_URL; ?>logout.php" class="sidebar-menu-item logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<script>
    // Highlight active menu item
    document.addEventListener('DOMContentLoaded', function() {
        const current = '<?php echo $current_page; ?>';
        document.querySelectorAll('.sidebar-menu-item').forEach(item => {
            const href = item.getAttribute('href');
            if (href.includes(current)) {
                item.classList.add('active');
            }
        });
    });

    // Toggle sidebar on mobile
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.menu-toggle');
        if (sidebar && !sidebar.contains(event.target) && !toggle.contains(event.target)) {
            if (window.innerWidth < 768) {
                sidebar.classList.remove('show');
            }
        }
    });
</script>
