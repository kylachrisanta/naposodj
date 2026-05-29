<?php 
require_once dirname(__FILE__) . '/auth_middleware.php';
// Helper untuk mendeteksi halaman aktif agar navbar diberi highlight
$current_page = basename($_SERVER['PHP_SELF']);

// Mengecek apakah user sudah login (pengunjung atau admin)
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naposo HKBP Duren Jaya</title>
    <!-- Stylesheet Utama -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <!-- Font Awesome untuk Ikon (via CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-greeting { margin-right: 15px; font-weight: 500; color: var(--text-main); font-size: 0.95rem; }
        @media (max-width: 768px) {
            .user-greeting { display: none; }
        }
    </style>
</head>
<body>

<header class="glass-panel">
    <div class="container" style="max-width: 95%;">
        <nav class="navbar">
            <a href="index.php" class="logo">
                <img src="assets/img/beranda/logo_naposo.png" alt="Logo" style="height: 35px; width: auto; object-fit: contain;">
                Naposo HKBP Duren Jaya
            </a>
            <ul class="nav-links">
                <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Beranda</a></li>
                <li class="dropdown">
                    <a href="tentang.php" class="<?= ($current_page == 'tentang.php') ? 'active' : '' ?> dropdown-toggle">
                        Tentang Kami <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; margin-left: 4px;"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="tentang.php#pendeta">Pendeta</a></li>
                        <li><a href="tentang.php#bpi">BPI</a></li>
                        <li><a href="tentang.php#divisi">Divisi</a></li>
                        <li><a href="tentang.php#visi-misi">Visi & Misi</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="jejak.php" class="<?= ($current_page == 'jejak.php') ? 'active' : '' ?> dropdown-toggle">
                        Jejak <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; margin-left: 4px;"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="jejak.php#prestasi">Prestasi</a></li>
                        <li><a href="jejak.php#partisipasi">Partisipasi</a></li>
                    </ul>
                </li>
                
                <?php if ($is_logged_in): ?>
                <li><a href="sorotan.php" class="<?= ($current_page == 'sorotan.php') ? 'active' : '' ?>">Sorotan</a></li>
                <li><a href="renungan.php" class="<?= ($current_page == 'renungan.php') ? 'active' : '' ?>">Renungan</a></li>
                <li class="dropdown">
                    <a href="info.php" class="<?= ($current_page == 'info.php') ? 'active' : '' ?> dropdown-toggle">
                        Info <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; margin-left: 4px;"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="info.php#kegiatan">Kegiatan</a></li>
                        <li><a href="info.php#warta">Warta</a></li>
                    </ul>
                </li>
                <li><a href="profil.php" class="<?= ($current_page == 'profil.php') ? 'active' : '' ?>">Profil Saya</a></li>
                <?php endif; ?>
            </ul>
            <div class="nav-actions">
                <?php if ($is_logged_in): ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="user-greeting">Hi, <strong><?= htmlspecialchars($_SESSION['user_nama_panggilan'] ?? $_SESSION['user_nama'] ?? 'Teman') ?></strong>!</span>
                    <?php endif; ?>
                    <a href="auth/logout.php?role=<?= isset($_SESSION['admin_id']) ? 'admin' : 'user' ?>" class="btn-primary" style="background: var(--text-muted); display: inline-flex; align-items: center; gap: 8px;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;"><i class="fa-solid fa-user"></i> Login</a>
                    <a href="auth/register.php" class="btn-primary" style="display: inline-flex; align-items: center; gap: 8px;"><i class="fa-solid fa-user-plus"></i> Daftar</a>
                <?php endif; ?>
                
                <!-- Mobile Toggle -->
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </nav>
    </div>
</header>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header">
        <a href="index.php" class="logo">
            <img src="assets/img/beranda/logo_naposo.png" alt="Logo" style="height: 35px; width: auto; object-fit: contain;">
            Naposo HKBP
        </a>
        <button id="closeMenu"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <ul class="mobile-nav-links">
        <?php if ($is_logged_in && isset($_SESSION['user_id'])): ?>
            <li style="padding: 10px 15px; border-bottom: 1px solid var(--border-color); margin-bottom: 15px; font-weight: 600; font-family: var(--font-heading); font-size: 1.15rem; color: var(--primary);">
                <i class="fa-regular fa-face-smile" style="margin-right: 8px;"></i>Halo, <?= htmlspecialchars($_SESSION['user_nama_panggilan'] ?? $_SESSION['user_nama'] ?? 'Teman') ?>!
            </li>
        <?php endif; ?>
        <li><a href="index.php">Beranda</a></li>
        <li><a href="tentang.php">Tentang Kami</a></li>
        <li style="padding-left: 20px; margin-top: -10px; margin-bottom: -5px;"><a href="tentang.php#pendeta" style="font-size: 1.1rem; font-weight: 500; color: var(--text-muted);"><i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; margin-right: 6px; color: var(--primary-light);"></i> Pendeta</a></li>
        <li style="padding-left: 20px; margin-top: -10px; margin-bottom: -5px;"><a href="tentang.php#bpi" style="font-size: 1.1rem; font-weight: 500; color: var(--text-muted);"><i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; margin-right: 6px; color: var(--primary-light);"></i> BPI</a></li>
        <li style="padding-left: 20px; margin-top: -10px; margin-bottom: -5px;"><a href="tentang.php#divisi" style="font-size: 1.1rem; font-weight: 500; color: var(--text-muted);"><i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; margin-right: 6px; color: var(--primary-light);"></i> Divisi</a></li>
        <li style="padding-left: 20px; margin-top: -10px; margin-bottom: -5px;"><a href="tentang.php#visi-misi" style="font-size: 1.1rem; font-weight: 500; color: var(--text-muted);"><i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; margin-right: 6px; color: var(--primary-light);"></i> Visi & Misi</a></li>
        <li><a href="jejak.php">Jejak</a></li>
        <li style="padding-left: 20px; margin-top: -10px; margin-bottom: -5px;"><a href="jejak.php#prestasi" style="font-size: 1.1rem; font-weight: 500; color: var(--text-muted);"><i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; margin-right: 6px; color: var(--primary-light);"></i> Prestasi</a></li>
        <li style="padding-left: 20px; margin-top: -10px; margin-bottom: -5px;"><a href="jejak.php#partisipasi" style="font-size: 1.1rem; font-weight: 500; color: var(--text-muted);"><i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; margin-right: 6px; color: var(--primary-light);"></i> Partisipasi</a></li>
        <?php if ($is_logged_in): ?>
            <li><a href="sorotan.php">Sorotan</a></li>
            <li><a href="renungan.php">Renungan</a></li>
            <li><a href="info.php">Info</a></li>
            <li style="padding-left: 20px; margin-top: -10px; margin-bottom: -5px;"><a href="info.php#kegiatan" style="font-size: 1.1rem; font-weight: 500; color: var(--text-muted);"><i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; margin-right: 6px; color: var(--primary-light);"></i> Kegiatan</a></li>
            <li style="padding-left: 20px; margin-top: -10px; margin-bottom: -5px;"><a href="info.php#warta" style="font-size: 1.1rem; font-weight: 500; color: var(--text-muted);"><i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; margin-right: 6px; color: var(--primary-light);"></i> Warta</a></li>
            <li><a href="profil.php">Profil Saya</a></li>
        <?php endif; ?>
        <li style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
            <?php if ($is_logged_in): ?>
                <a href="auth/logout.php?role=<?= isset($_SESSION['admin_id']) ? 'admin' : 'user' ?>" class="btn-primary" style="width: 100%; text-align: center; background: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; gap: 8px;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            <?php else: ?>
                <a href="auth/login.php" class="btn-secondary" style="width: 100%; text-align: center; display: inline-flex; align-items: center; justify-content: center; gap: 8px;"><i class="fa-solid fa-user"></i> Login</a>
                <a href="auth/register.php" class="btn-primary" style="width: 100%; text-align: center; display: inline-flex; align-items: center; justify-content: center; gap: 8px;"><i class="fa-solid fa-user-plus"></i> Daftar</a>
            <?php endif; ?>
        </li>
    </ul>
</div>

<script>
    const mobileToggle = document.getElementById('mobileToggle');
    const closeMenu = document.getElementById('closeMenu');
    const mobileMenu = document.getElementById('mobileMenu');

    mobileToggle.addEventListener('click', () => {
        mobileMenu.classList.add('active');
        document.body.style.overflow = 'hidden';
    });

    closeMenu.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
        document.body.style.overflow = 'auto';
    });

    // Close mobile menu when any link is clicked (useful for anchor links on same page)
    const mobileLinks = document.querySelectorAll('.mobile-nav-links a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    });
</script>
