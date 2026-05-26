<?php
$page_title = 'Katalog Alat';
require '../config/database.php';
require '../includes/auth.php';

requireRole('peminjam');

$current_user = getCurrentUser($conn);

$message = '';
$error = '';

// Handle peminjaman submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'CSRF Token tidak valid!';
    } else {
        $alat_ids = $_POST['alat_id'] ?? [];
        $jumlah = $_POST['jumlah'] ?? [];
        $tanggal_pinjam = $_POST['tanggal_pinjam'];
        $deadline_kembali = $_POST['deadline_kembali'];

        if (empty($alat_ids) || empty($tanggal_pinjam) || empty($deadline_kembali)) {
            $error = 'Pilih alat dan isi tanggal pinjam dan deadline!';
        } else {
            // Generate kode peminjaman
            $prefix = 'PJM';
            $date = date('Ymd');
            $count = $conn->query("SELECT COUNT(*) as count FROM peminjaman WHERE kode_peminjaman LIKE '{$prefix}{$date}%'")->fetch_assoc()['count'];
            $kode_peminjaman = $prefix . $date . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            // Insert peminjaman
            $stmt = $conn->prepare("INSERT INTO peminjaman (kode_peminjaman, user_id, tanggal_pinjam, deadline_kembali, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param("siss", $kode_peminjaman, $current_user['id'], $tanggal_pinjam, $deadline_kembali);

            if ($stmt->execute()) {
                $peminjaman_id = $conn->insert_id;

                // Insert detail peminjaman
                foreach ($alat_ids as $key => $alat_id) {
                    $qty = $jumlah[$key] ?? 1;
                    $detail_stmt = $conn->prepare("INSERT INTO detail_peminjaman (peminjaman_id, alat_id, jumlah) VALUES (?, ?, ?)");
                    $detail_stmt->bind_param("iii", $peminjaman_id, $alat_id, $qty);
                    $detail_stmt->execute();
                }

                logActivity($conn, $current_user['id'], 'Ajukan Peminjaman', "Peminjaman $kode_peminjaman diajukan", 'peminjaman', $peminjaman_id);
                $message = 'Permintaan peminjaman berhasil diajukan! Silahkan tunggu approval dari petugas.';
            } else {
                $error = 'Gagal mengajukan peminjaman!';
            }
        }
    }
}

// Get filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Build query
$query = "SELECT a.*, k.nama_kategori FROM alat a JOIN kategori k ON a.kategori_id = k.id WHERE a.stok > 0 AND a.kondisi = 'baik'";
if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $query .= " AND (a.nama_alat LIKE '%$search_safe%' OR a.kode_alat LIKE '%$search_safe%')";
}
if (!empty($kategori_filter)) {
    $query .= " AND a.kategori_id = " . intval($kategori_filter);
}
$query .= " ORDER BY a.nama_alat ASC";

$alat = $conn->query($query);

// Get all kategori for filter
$kategori = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

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
                <span>Katalog Alat</span>
            </div>

            <div class="page-header">
                <h1>Katalog Alat Peminjaman 📦</h1>
                <p>Jelajahi dan ajukan permintaan peminjaman alat</p>
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

            <!-- Search & Filter -->
            <div class="filter-section" style="margin-bottom: 30px;">
                <div class="filter-group" style="flex: 2;">
                    <label>Cari Alat</label>
                    <input type="text" id="searchInput" placeholder="Cari nama atau kode alat..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <label>Kategori</label>
                    <select id="kategoriFilter">
                        <option value="">Semua Kategori</option>
                        <?php while ($row = $kategori->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>" <?php echo ($kategori_filter == $row['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['nama_kategori']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button class="btn btn-primary" onclick="searchAlat()">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>

            <!-- Alat Grid -->
            <?php if ($alat->num_rows > 0): ?>
                <div class="grid grid-3">
                    <?php while ($row = $alat->fetch_assoc()): ?>
                        <div style="border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: all 0.3s ease; cursor: pointer;" onclick="openAlatDetail(<?php echo $row['id']; ?>)">
                            <!-- Image Placeholder -->
                            <div style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 50px; position: relative;">
                                <i class="fas fa-box"></i>
                                <span style="position: absolute; top: 10px; right: 10px; background: var(--success); color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Stok: <?php echo $row['stok']; ?></span>
                            </div>

                            <!-- Info -->
                            <div style="padding: 20px;">
                                <h3 style="font-size: 16px; font-weight: 600; color: var(--secondary); margin-bottom: 5px; line-height: 1.4;">
                                    <?php echo htmlspecialchars($row['nama_alat']); ?>
                                </h3>
                                <p style="font-size: 13px; color: var(--gray); margin-bottom: 15px;">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                </p>

                                <div style="display: flex; gap: 8px; margin-bottom: 15px; flex-wrap: wrap;">
                                    <span class="badge badge-primary"><?php echo $row['kode_alat']; ?></span>
                                    <?php
                                    $kondisi_badge = 'badge-success';
                                    $kondisi_text = 'Baik';
                                    if ($row['kondisi'] == 'kurang_baik') {
                                        $kondisi_badge = 'badge-warning';
                                        $kondisi_text = 'Kurang Baik';
                                    }
                                    ?>
                                    <span class="badge <?php echo $kondisi_badge; ?>"><?php echo $kondisi_text; ?></span>
                                </div>

                                <button class="btn btn-primary" style="width: 100%; justify-content: center;" onclick="openPeminjamanForm(<?php echo $row['id']; ?>, event)">
                                    <i class="fas fa-plus"></i> Ajukan Peminjaman
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <h3>Alat tidak ditemukan</h3>
                    <p>Cobalah ubah pencarian atau filter Anda</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Peminjaman Form Modal -->
<div id="peminjamanModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <span class="modal-close" onclick="closePeminjamanForm()">&times;</span>
        
        <div class="modal-header">
            <h2>Ajukan Peminjaman</h2>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div id="alatContainer"></div>

            <div class="form-group">
                <label for="tanggal_pinjam">Tanggal Pinjam</label>
                <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" min="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="deadline_kembali">Deadline Kembali</label>
                <input type="date" id="deadline_kembali" name="deadline_kembali" required>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Ajukan
                </button>
                <button type="button" class="btn btn-secondary" onclick="closePeminjamanForm()">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function searchAlat() {
    const search = document.getElementById('searchInput').value;
    const kategori = document.getElementById('kategoriFilter').value;
    let url = 'katalog.php?';
    if (search) url += 'search=' + encodeURIComponent(search) + '&';
    if (kategori) url += 'kategori=' + kategori;
    window.location.href = url;
}

function openPeminjamanForm(alatId, event) {
    event.stopPropagation();
    
    // Prepare alat input
    const container = document.getElementById('alatContainer');
    container.innerHTML = `
        <input type="hidden" name="alat_id[]" value="${alatId}">
        <div class="form-group">
            <label>Jumlah</label>
            <input type="number" name="jumlah[]" value="1" min="1" required>
        </div>
    `;

    document.getElementById('peminjamanModal').classList.add('show');
}

function closePeminjamanForm() {
    document.getElementById('peminjamanModal').classList.remove('show');
}

// Set min deadline kembali
document.getElementById('tanggal_pinjam')?.addEventListener('change', function() {
    const minDeadline = new Date(this.value);
    minDeadline.setDate(minDeadline.getDate() + 1);
    document.getElementById('deadline_kembali').min = minDeadline.toISOString().split('T')[0];
});

// Close modal when clicking outside
document.getElementById('peminjamanModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePeminjamanForm();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
