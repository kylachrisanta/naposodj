<?php 
require_once '../includes/admin_header.php'; 
require_once '../includes/admin_sidebar.php'; 

// Fetch statistik dari database
$count_pengurus = $conn->query("SELECT COUNT(id) AS total FROM pengurus")->fetch_assoc()['total'];
$count_kegiatan = $conn->query("SELECT COUNT(id) AS total FROM kegiatan")->fetch_assoc()['total'];
$count_warta    = $conn->query("SELECT COUNT(id) AS total FROM warta")->fetch_assoc()['total'];
$count_users    = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc()['total'];
?>

<div style="margin-bottom: 30px;">
    <h2>Dashboard</h2>
    <p style="color: var(--text-muted);">Selamat datang di Panel Manajemen Naposo HKBP Duren Jaya.</p>
</div>

<!-- Statistik Cards -->
<div class="dash-grid">
    <div class="dash-card">
        <div class="dash-icon bg-blue">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="dash-info">
            <h4>Total Pengurus</h4>
            <h2><?= $count_pengurus ?></h2>
        </div>
    </div>
    
    <div class="dash-card">
        <div class="dash-icon bg-green">
            <i class="fa-regular fa-calendar-check"></i>
        </div>
        <div class="dash-info">
            <h4>Jadwal Kegiatan</h4>
            <h2><?= $count_kegiatan ?></h2>
        </div>
    </div>
    
    <div class="dash-card">
        <div class="dash-icon bg-amber">
            <i class="fa-solid fa-bullhorn"></i>
        </div>
        <div class="dash-info">
            <h4>Warta Jemaat</h4>
            <h2><?= $count_warta ?></h2>
        </div>
    </div>
    
    <div class="dash-card">
        <div class="dash-icon bg-purple">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="dash-info">
            <h4>Akun Terdaftar</h4>
            <h2><?= $count_users ?></h2>
        </div>
    </div>
</div>

<!-- Welcome Banner / Extra Info -->
<div style="background: white; padding: 30px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 30px;">
    <div>
        <h3 style="font-size: 1.5rem; margin-bottom: 10px;">Kelola Website Anda</h3>
        <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 20px;">Gunakan menu navigasi di sebelah kiri untuk menambah, mengubah, atau menghapus konten pada website pengunjung. Segala perubahan yang Anda lakukan di sini akan secara otomatis (*real-time*) diperbarui di halaman publik.</p>
        <a href="../index.php" target="_blank" class="btn-primary"><i class="fa-solid fa-globe"></i> Lihat Website Pengunjung</a>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
