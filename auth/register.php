<?php
require_once dirname(__FILE__) . '/../includes/auth_middleware.php';
require '../config/database.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $nama = $conn->real_escape_string($_POST['nama']);
    $nama_panggilan = $conn->real_escape_string(trim($_POST['nama_panggilan']));
    $jenis_kelamin = $conn->real_escape_string($_POST['jenis_kelamin']);
    $tempat_lahir = $conn->real_escape_string($_POST['tempat_lahir']);
    $tanggal_lahir = $conn->real_escape_string($_POST['tanggal_lahir']);
    $alamat = $conn->real_escape_string($_POST['alamat']);
    $wijk = $conn->real_escape_string($_POST['wijk']);
    $angkatan_sidi = $conn->real_escape_string($_POST['angkatan_sidi']);
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    // Normalisasi nomor WA ke format 628... (Indonesia)
    $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp); 
    if (substr($whatsapp, 0, 1) === '0') {
        $whatsapp = '62' . substr($whatsapp, 1);
    } elseif (substr($whatsapp, 0, 1) === '8') {
        $whatsapp = '62' . $whatsapp;
    }
    
    $wa_notification = $conn->real_escape_string($_POST['wa_notification']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Hitung umur
    $birthdate = new DateTime($tanggal_lahir);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;

    // Validasi form di backend
    if (empty($nama_panggilan)) {
        $error = "Nama Panggilan wajib diisi!";
    } elseif (strlen($nama_panggilan) > 50) {
        $error = "Nama Panggilan maksimal 50 karakter!";
    } elseif (!in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'])) {
        $error = "Pilihlah Jenis Kelamin yang valid!";
    } elseif ($age < 18 || $age > 32) {
        $error = "Pendaftaran hanya diperbolehkan untuk pengguna berusia 18–32 tahun.";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan Konfirmasi Password tidak cocok!";
    } else {
        // Cek email apakah sudah terdaftar
        $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check_email->num_rows > 0) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain atau login.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = "pengunjung";

            $sql = "INSERT INTO users (nama, nama_panggilan, jenis_kelamin, tempat_lahir, tanggal_lahir, alamat, wijk, angkatan_sidi, whatsapp, wa_notification, email, password, role) 
                    VALUES ('$nama', '$nama_panggilan', '$jenis_kelamin', '$tempat_lahir', '$tanggal_lahir', '$alamat', '$wijk', '$angkatan_sidi', '$whatsapp', '$wa_notification', '$email', '$hashed_password', '$role')";

            if ($conn->query($sql) === TRUE) {
                // Set flash message session
                $_SESSION['flash_success'] = "Pendaftaran berhasil! Silakan login dengan akun Anda.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Terjadi kesalahan: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Naposo HKBP Duren Jaya</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 40px 20px;}
        .login-box { background: white; padding: 40px; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); width: 100%; max-width: 600px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-family: var(--font-body); }
        .form-control:focus { outline: none; border-color: var(--primary); }
        .grid-2-form { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .alert { padding: 15px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.95rem; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #f87171; }
        @media (max-width: 600px) { .grid-2-form { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="login-box">
        <div style="text-align: center;">
            <h2 style="font-family: var(--font-heading); margin-bottom: 5px; color: var(--text-main);">Daftar Akun Baru</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px;">Bergabunglah bersama keluarga besar Naposo Duren Jaya.</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>
        
        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
            <div class="grid-2-form">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nama Panggilan</label>
                    <input type="text" name="nama_panggilan" class="form-control" required maxlength="50" placeholder="Contoh: Budi" value="<?= isset($_POST['nama_panggilan']) ? htmlspecialchars($_POST['nama_panggilan']) : '' ?>">
                </div>
            </div>

            <div class="grid-2-form">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" required value="<?= isset($_POST['tempat_lahir']) ? htmlspecialchars($_POST['tempat_lahir']) : '' ?>">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" required 
                           min="<?= date('Y-m-d', strtotime('-32 years')) ?>" 
                           max="<?= date('Y-m-d', strtotime('-18 years')) ?>" 
                           value="<?= isset($_POST['tanggal_lahir']) ? htmlspecialchars($_POST['tanggal_lahir']) : '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Alamat Lengkap</label>
                <textarea name="alamat" class="form-control" rows="3" required><?= isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : '' ?></textarea>
            </div>

            <div class="grid-2-form">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Wijk (Sektor)</label>
                    <input type="text" name="wijk" class="form-control" placeholder="Contoh: 1" required value="<?= isset($_POST['wijk']) ? htmlspecialchars($_POST['wijk']) : '' ?>">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Tahun Angkatan Sidi</label>
                    <input type="text" name="angkatan_sidi" class="form-control" placeholder="Contoh: 2018" required value="<?= isset($_POST['angkatan_sidi']) ? htmlspecialchars($_POST['angkatan_sidi']) : '' ?>">
                </div>
            </div>

            <div class="grid-2-form">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nomor WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" placeholder="Contoh: 628123456789" required value="<?= isset($_POST['whatsapp']) ? htmlspecialchars($_POST['whatsapp']) : '' ?>">
                </div>
            </div>

            <div class="grid-2-form">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control" required>
                        <option value="" disabled <?= !isset($_POST['jenis_kelamin']) ? 'selected' : '' ?>>Pilih Jenis Kelamin</option>
                        <option value="Laki-laki" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Persetujuan Notifikasi WA</label>
                    <select name="wa_notification" class="form-control" required>
                        <option value="aktif" <?= (isset($_POST['wa_notification']) && $_POST['wa_notification'] == 'aktif') ? 'selected' : '' ?>>Aktifkan Notifikasi WA</option>
                        <option value="nonaktif" <?= (isset($_POST['wa_notification']) && $_POST['wa_notification'] == 'nonaktif') ? 'selected' : '' ?>>Nonaktifkan Notifikasi WA</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Password</label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Ulangi Password</label>
                <input type="password" name="confirm_password" class="form-control" required minlength="6">
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; border-radius: var(--radius-sm); margin-top: 10px; padding: 15px;">Daftar Sekarang</button>
        </form>
        
        <div style="margin-top: 25px; text-align: center; font-size: 0.95rem; color: var(--text-muted);">
            Sudah punya akun? <a href="login.php" style="color: var(--primary); font-weight: 500;">Login di sini</a>
        </div>
        <div style="margin-top: 20px; text-align: center; font-size: 0.9rem;">
            <a href="../index.php" style="color: var(--text-muted);"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
        </div>
    </div>
    <script>
        const dateInput = document.getElementById('tanggal_lahir');
        
        function validateAge() {
            if (!dateInput.value) return;
            
            const birthdate = new Date(dateInput.value);
            const today = new Date();
            let age = today.getFullYear() - birthdate.getFullYear();
            const m = today.getMonth() - birthdate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthdate.getDate())) {
                age--;
            }
            
            if (age < 18 || age > 32) {
                dateInput.setCustomValidity('Pendaftaran hanya diperbolehkan untuk pengguna berusia 18–32 tahun.');
            } else {
                dateInput.setCustomValidity('');
            }
        }
        
        dateInput.addEventListener('input', validateAge);
        window.addEventListener('load', validateAge);
    </script>
</body>
</html>
