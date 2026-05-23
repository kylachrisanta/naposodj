<?php
require_once dirname(__FILE__) . '/../includes/auth_middleware.php';
require '../config/database.php';
require '../config/whatsapp.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    
    // Normalisasi nomor WA
    $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp); 
    if (substr($whatsapp, 0, 1) === '0') {
        $whatsapp = '62' . substr($whatsapp, 1);
    } elseif (substr($whatsapp, 0, 1) === '8') {
        $whatsapp = '62' . $whatsapp;
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE whatsapp = ?");
    $stmt->bind_param("s", $whatsapp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        
        $otp = rand(100000, 999999);
        $expired = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $update_stmt = $conn->prepare("UPDATE users SET otp = ?, otp_expired = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $otp, $expired, $user_id);
        $update_stmt->execute();

        // Kirim via Fonnte
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => FONNTE_API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $whatsapp,
                'message' => "Kode OTP Reset Password Anda adalah: *$otp*\n\nKode ini berlaku selama 15 menit. Jangan berikan kode ini kepada siapapun.",
                'delay' => '2',
                'countryCode' => '62',
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . FONNTE_TOKEN
            ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        $_SESSION['reset_whatsapp'] = $whatsapp;
        header("Location: verify_otp.php");
        exit();
    } else {
        $error = "Nomor WhatsApp tidak terdaftar di sistem.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Naposo HKBP Duren Jaya</title>
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
        <h2 style="font-family: var(--font-heading); margin-bottom: 5px; color: var(--text-main);">Lupa Password</h2>
        <p style="color: var(--text-muted); margin-bottom: 30px;">Masukkan nomor WhatsApp Anda yang terdaftar untuk menerima OTP.</p>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nomor WhatsApp</label>
                <input type="text" name="whatsapp" class="form-control" placeholder="Contoh: 628123456789" required>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; border-radius: var(--radius-sm); padding: 12px;">Kirim OTP</button>
        </form>
        
        <div style="margin-top: 20px; font-size: 0.95rem; color: var(--text-muted);">
            <a href="login.php" style="color: var(--primary); font-weight: 500;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Login</a>
        </div>
    </div>
</body>
</html>
