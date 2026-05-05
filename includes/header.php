<?php 
require_once dirname(__FILE__) . '/auth_middleware.php';
// Helper untuk mendeteksi halaman aktif agar navbar diberi highlight
$current_page = basename($_SERVER['PHP_SELF']);

// Mengecek apakah user sudah login
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naposo HKBP Duren Jaya</title>
    <!-- Stylesheet Utama -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome untuk Ikon (via CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="glass-panel">
    <div class="container" style="max-width: 95%;">
        <nav class="navbar">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-church" style="color: var(--primary);"></i>
                Naposo HKBP Duren Jaya
            </a>
            <ul class="nav-links">
                <li><a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Tentang Kami</a></li>
                
                <?php if ($is_logged_in): ?>
                <li><a href="sorotan.php" class="<?= ($current_page == 'sorotan.php') ? 'active' : '' ?>">Sorotan</a></li>
                <li><a href="info.php" class="<?= ($current_page == 'info.php') ? 'active' : '' ?>">Info</a></li>
                <?php endif; ?>
                
                <li><a href="jejak.php" class="<?= ($current_page == 'jejak.php') ? 'active' : '' ?>">Jejak</a></li>
                <li><a href="sejarah.php" class="<?= ($current_page == 'sejarah.php') ? 'active' : '' ?>">Sejarah</a></li>
            </ul>
            <div class="nav-actions">
                <?php if ($is_logged_in): ?>
                    <a href="auth/logout.php" class="btn-primary" style="background: var(--text-muted);"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn-primary"><i class="fa-solid fa-user"></i> Login</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>
