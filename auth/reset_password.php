<?php
require_once dirname(__FILE__) . '/../includes/auth_middleware.php';
require '../config/database.php';

$error = "";

if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}

$user_id = $_SESSION['reset_user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Password dan Konfirmasi Password tidak cocok!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Hapus session reset dan OTP
        $stmt = $conn->prepare("UPDATE users SET password = ?, otp = NULL, otp_expired = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            unset($_SESSION['reset_whatsapp']);
            unset($_SESSION['reset_user_id']);
            
            $_SESSION['flash_success'] = "Password berhasil direset. Silakan login dengan password baru Anda.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Naposo HKBP Duren Jaya</title>
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
        <h2 style="font-family: var(--font-heading); margin-bottom: 5px; color: var(--text-main);">Password Baru</h2>
        <p style="color: var(--text-muted); margin-bottom: 30px;">Masukkan password baru Anda.</p>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Password Baru</label>
                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Konfirmasi Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required minlength="6">
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; border-radius: var(--radius-sm); padding: 12px;">Reset Password</button>
        </form>
    </div>
</body>
</html>
