<?php
require_once dirname(__FILE__) . '/../includes/auth_middleware.php';

// Halaman login dapat diakses kapan saja untuk login peran lain
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Naposo HKBP Duren Jaya</title>
    <!-- Menambahkan CDN Font Awesome & Font Google agar tampilannya sama -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { background: white; padding: 40px; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); width: 100%; max-width: 400px; text-align: center; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: var(--font-body); }
        .form-control:focus { outline: none; border-color: var(--primary); }
        .alert { padding: 15px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.95rem; text-align: left;}
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #f87171; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="font-family: var(--font-heading); margin-bottom: 5px; color: var(--text-main);">Masuk Akun</h2>
        <p style="color: var(--text-muted); margin-bottom: 30px;">Silakan login untuk mengakses Info & Sorotan Naposo.</p>
        
        <?php if(isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['flash_error'] ?></div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= $_SESSION['flash_success'] ?></div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <form action="process_login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Contoh: user@gmail.com" required>
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password..." required>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; border-radius: var(--radius-sm); padding: 12px;">Login</button>
        </form>
        
        <div style="margin-top: 20px; font-size: 0.95rem; color: var(--text-muted);">
            Belum punya akun? <a href="register.php" style="color: var(--primary); font-weight: 500;">Daftar di sini</a>
        </div>
        <div style="margin-top: 20px; font-size: 0.9rem;">
            <a href="../index.php" style="color: var(--text-muted);"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
