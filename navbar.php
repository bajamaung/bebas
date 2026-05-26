<?php
// includes/navbar.php
// Pastikan auth.php dan getCurrentUser sudah tersedia
if (!isset($current_user)) {
    $current_user = getCurrentUser($conn);
}

$notification_count = 0;

// Hitung notifikasi pending untuk role tertentu
if ($current_user['role'] == 'petugas') {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM peminjaman WHERE status = 'pending'");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $notification_count = $result['count'];
} elseif ($current_user['role'] == 'peminjam') {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM peminjaman WHERE user_id = ? AND status IN ('pending', 'disetujui', 'sedang_dipinjam')");
    $stmt->bind_param("i", $current_user['id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $notification_count = $result['count'];
}

// Ambil inisial nama untuk avatar
$initials = strtoupper(substr($current_user['nama'], 0, 1));
?>

<div class="navbar">
    <div style="display: flex; align-items: center; gap: 15px;">
        <button class="menu-toggle" onclick="toggleSidebar()" style="display: none; background: none; border: none; cursor: pointer; font-size: 20px;">
            <i class="fas fa-bars"></i>
        </button>
        <div class="navbar-title">
            <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?>
        </div>
    </div>

    <div class="navbar-right">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Cari..." id="searchInput">
        </div>

        <div class="navbar-icons">
            <button class="icon-btn" onclick="toggleNotifications()">
                <i class="fas fa-bell"></i>
                <?php if ($notification_count > 0): ?>
                    <span class="badge-notification"><?php echo $notification_count; ?></span>
                <?php endif; ?>
            </button>

            <div class="user-profile" onclick="toggleUserMenu()">
                <div class="user-avatar"><?php echo $initials; ?></div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($current_user['nama']); ?></div>
                    <div class="user-role"><?php echo $current_user['role']; ?></div>
                </div>
                <i class="fas fa-chevron-down" style="color: var(--gray); font-size: 12px;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Notification Dropdown -->
<div id="notificationDropdown" style="display: none; position: absolute; top: 70px; right: 100px; background: white; border: 1px solid var(--border); border-radius: 8px; width: 300px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); z-index: 1001;">
    <div style="padding: 15px; border-bottom: 1px solid var(--border); font-weight: 600;">
        Notifikasi
    </div>
    <div id="notificationContent" style="max-height: 400px; overflow-y: auto;">
        <!-- Notifikasi akan dimuat di sini -->
    </div>
</div>

<!-- User Menu Dropdown -->
<div id="userMenuDropdown" style="display: none; position: absolute; top: 70px; right: 30px; background: white; border: 1px solid var(--border); border-radius: 8px; min-width: 200px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); z-index: 1001;">
    <a href="#" style="display: block; padding: 12px 15px; border-bottom: 1px solid var(--border); color: var(--secondary); text-decoration: none;">
        <i class="fas fa-user" style="margin-right: 8px;"></i> Profil
    </a>
    <a href="#" style="display: block; padding: 12px 15px; border-bottom: 1px solid var(--border); color: var(--secondary); text-decoration: none;">
        <i class="fas fa-cog" style="margin-right: 8px;"></i> Pengaturan
    </a>
    <a href="<?php echo BASE_URL; ?>logout.php" style="display: block; padding: 12px 15px; color: var(--danger); text-decoration: none;">
        <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Logout
    </a>
</div>

<style>
    .menu-toggle {
        display: none;
    }

    @media (max-width: 768px) {
        .menu-toggle {
            display: block !important;
        }

        .search-box {
            display: none !important;
        }
    }
</style>

<script>
    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        closeUserMenu();
    }

    function toggleUserMenu() {
        const dropdown = document.getElementById('userMenuDropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        closeNotifications();
    }

    function closeNotifications() {
        document.getElementById('notificationDropdown').style.display = 'none';
    }

    function closeUserMenu() {
        document.getElementById('userMenuDropdown').style.display = 'none';
    }

    // Close dropdown ketika click di luar
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.icon-btn') && !event.target.closest('#notificationDropdown')) {
            closeNotifications();
        }
        if (!event.target.closest('.user-profile') && !event.target.closest('#userMenuDropdown')) {
            closeUserMenu();
        }
    });

    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            performSearch(this.value);
        }
    });

    function performSearch(query) {
        // Implement search functionality di setiap halaman
        console.log('Searching for:', query);
    }
</script>
