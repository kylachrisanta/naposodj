<aside class="admin-sidebar">
    <div class="sidebar-header">
        <i class="fa-solid fa-church" style="color: #60a5fa;"></i>
        <span>Naposo Admin</span>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="kegiatan_warta.php" class="<?= ($current_page == 'kegiatan_warta.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days"></i> Kegiatan & Warta
            </a>
        </li>
        <li>
            <a href="sorotan.php" class="<?= ($current_page == 'sorotan.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-camera-retro"></i> Kelola Sorotan
            </a>
        </li>
        <li>
            <a href="jejak.php" class="<?= ($current_page == 'jejak.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-trophy"></i> Kelola Jejak
            </a>
        </li>
        <li>
            <a href="user.php" class="<?= ($current_page == 'user.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-user-shield"></i> Kelola Akun
            </a>
        </li>
        <li>
            <a href="pengurus_visi_misi.php" class="<?= ($current_page == 'pengurus_visi_misi.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-users-gear"></i> Pengurus & Visi Misi
            </a>
        </li>
    </ul>
</aside>

<main class="admin-main">
    <header class="admin-topbar">
        <div class="topbar-title">
            Panel Manajemen
        </div>
        <div class="topbar-right">
            <div class="admin-profile">
                <i class="fa-solid fa-circle-user" style="font-size: 24px; color: var(--primary);"></i>
                <span>Halo, <?= htmlspecialchars($_SESSION['admin_nama'] ?? '') ?></span>
            </div>
            <a href="../auth/logout.php?role=admin" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </header>
    
    <div class="admin-content">
