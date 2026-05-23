<?php
require_once dirname(__FILE__) . '/../includes/auth_middleware.php';
require '../config/database.php';

$error = "";

if (!isset($_SESSION['reset_whatsapp'])) {
    header("Location: forgot_password.php");
    exit();
}

$whatsapp = $_SESSION['reset_whatsapp'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = $conn->real_escape_string($_POST['otp']);

    $stmt = $conn->prepare("SELECT id, otp_expired FROM users WHERE whatsapp = ? AND otp = ?");
    $stmt->bind_param("ss", $whatsapp, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (strtotime($user['otp_expired']) > time()) {
            $_SESSION['reset_user_id'] = $user['id'];
            header("Location: reset_password.php");
            exit();
        } else {
            $error = "Kode OTP sudah kedaluwarsa.";
        }
    } else {
        $error = "Kode OTP salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - Naposo HKBP Duren Jaya</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-box { background: white; padding: 40px; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); width: 100%; max-width: 400px; text-align: center; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: var(--font-body); text-align: center; font-size: 1.2rem; letter-spacing: 2px;}
        .form-control:focus { outline: none; border-color: var(--primary); }
        .alert { padding: 15px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.95rem; text-align: left;}
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #f87171; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="font-family: var(--font-heading); margin-bottom: 5px; color: var(--text-main);">Verifikasi OTP</h2>
        <p style="color: var(--text-muted); margin-bottom: 30px;">Masukkan 6 digit kode OTP yang telah dikirimkan ke WhatsApp Anda.</p>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <input type="text" name="otp" class="form-control" placeholder="123456" maxlength="6" required>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; border-radius: var(--radius-sm); padding: 12px;">Verifikasi</button>
        </form>
        
        <div style="margin-top: 20px; font-size: 0.95rem; color: var(--text-muted);">
            <a href="forgot_password.php" style="color: var(--primary); font-weight: 500;"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
</body>
</html>
