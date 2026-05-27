<?php
require_once 'includes/auth_middleware.php';
require_once 'config/database.php';

// Proteksi halaman - wajib login
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    $_SESSION['flash_error'] = "Silakan login terlebih dahulu untuk mengakses profil.";
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'];
$is_admin = isset($_SESSION['admin_id']);

$error = "";
$success = "";

// Ambil data user saat ini
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

if (!$user_data) {
    die("Pengguna tidak ditemukan.");
}

// Proses form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $nama = sanitize($_POST['nama']);
    $nama_panggilan = sanitize($_POST['nama_panggilan']);
    $jenis_kelamin = sanitize($_POST['jenis_kelamin']);
    $tempat_lahir = sanitize($_POST['tempat_lahir']);
    $tanggal_lahir = sanitize($_POST['tanggal_lahir']);
    $alamat = sanitize($_POST['alamat']);
    $wijk = sanitize($_POST['wijk']);
    $angkatan_sidi = sanitize($_POST['angkatan_sidi']);
    $email = sanitize($_POST['email']);
    $whatsapp = sanitize($_POST['whatsapp']);
    $wa_notification = sanitize($_POST['wa_notification'] ?? 'aktif');
    
    // Normalisasi WhatsApp
    $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp); 
    if (substr($whatsapp, 0, 1) === '0') {
        $whatsapp = '62' . substr($whatsapp, 1);
    } elseif (substr($whatsapp, 0, 1) === '8') {
        $whatsapp = '62' . $whatsapp;
    }

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi email unik (tidak boleh sama dengan email user lain)
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_email->bind_param("si", $email, $user_id);
    $check_email->execute();
    $email_result = $check_email->get_result();

    if ($email_result->num_rows > 0) {
        $error = "Email sudah digunakan oleh pengguna lain.";
    } elseif (empty($nama_panggilan)) {
        $error = "Nama Panggilan wajib diisi!";
    } elseif (strlen($nama_panggilan) > 50) {
        $error = "Nama Panggilan maksimal 50 karakter!";
    } elseif (!in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'])) {
        $error = "Jenis Kelamin tidak valid!";
    } else {
        // Handle upload foto profil
        $foto_filename = $user_data['foto_profil']; // Default menggunakan foto lama
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto_profil']['tmp_name'];
            $file_name = $_FILES['foto_profil']['name'];
            $file_size = $_FILES['foto_profil']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file_ext, $allowed_exts)) {
                $error = "Format file foto tidak didukung. Harap upload format JPG, JPEG, PNG, atau GIF.";
            } elseif ($file_size > 2 * 1024 * 1024) { // Max 2MB
                $error = "Ukuran file foto terlalu besar. Maksimal adalah 2MB.";
            } else {
                // Buat direktori upload jika belum ada
                $upload_dir = 'assets/img/profil/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Buat nama file unik
                $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
                $dest_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file_tmp, $dest_path)) {
                    // Hapus foto profil lama jika ada dan bukan default
                    if ($user_data['foto_profil'] && file_exists($upload_dir . $user_data['foto_profil'])) {
                        unlink($upload_dir . $user_data['foto_profil']);
                    }
                    $foto_filename = $new_filename;
                } else {
                    $error = "Gagal mengunggah foto profil.";
                }
            }
        }

        // Jika tidak ada error sejauh ini, lanjutkan update
        if (empty($error)) {
            // Update password jika diisi
            if (!empty($password)) {
                if ($password !== $confirm_password) {
                    $error = "Password baru dan konfirmasi password tidak cocok!";
                } elseif (strlen($password) < 6) {
                    $error = "Password minimal terdiri dari 6 karakter.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE users SET nama = ?, nama_panggilan = ?, jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?, alamat = ?, wijk = ?, angkatan_sidi = ?, whatsapp = ?, wa_notification = ?, email = ?, password = ?, foto_profil = ? WHERE id = ?");
                    $update_stmt->bind_param("sssssssssssssi", $nama, $nama_panggilan, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $alamat, $wijk, $angkatan_sidi, $whatsapp, $wa_notification, $email, $hashed_password, $foto_filename, $user_id);
                    if ($update_stmt->execute()) {
                        $success = "Profil dan password Anda berhasil diperbarui!";
                    } else {
                        $error = "Gagal memperbarui database: " . $conn->error;
                    }
                }
            } else {
                // Update tanpa ganti password
                $update_stmt = $conn->prepare("UPDATE users SET nama = ?, nama_panggilan = ?, jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?, alamat = ?, wijk = ?, angkatan_sidi = ?, whatsapp = ?, wa_notification = ?, email = ?, foto_profil = ? WHERE id = ?");
                $update_stmt->bind_param("ssssssssssssi", $nama, $nama_panggilan, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $alamat, $wijk, $angkatan_sidi, $whatsapp, $wa_notification, $email, $foto_filename, $user_id);
                if ($update_stmt->execute()) {
                    $success = "Profil Anda berhasil diperbarui!";
                } else {
                    $error = "Gagal memperbarui database: " . $conn->error;
                }
            }

            // Refresh data user terbaru setelah update
            if (empty($error)) {
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user_data = $stmt->get_result()->fetch_assoc();
                
                // Update nama di session jika berubah
                if ($is_admin) {
                    $_SESSION['admin_nama'] = $user_data['nama'];
                } else {
                    $_SESSION['user_nama'] = $user_data['nama'];
                    $_SESSION['user_nama_panggilan'] = $user_data['nama_panggilan'];
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div style="padding-top: 130px; padding-bottom: 80px; background: var(--bg-subtle); min-height: 100vh;">
    <div class="container" style="max-width: 900px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <h1 class="section-title">Profil Saya</h1>
            <p class="section-subtitle" style="margin-bottom: 0;">Kelola data diri, kontak, dan keamanan akun Anda di sini.</p>
        </div>

        <?php if($success): ?>
            <div style="background: #dcfce7; border: 1px solid #bbf7d0; color: #15803d; padding: 15px 20px; border-radius: var(--radius-md); margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-check" style="font-size: 1.2rem;"></i>
                <span><?= $success ?></span>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 15px 20px; border-radius: var(--radius-md); margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 1.2rem;"></i>
                <span><?= $error ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= get_csrf_token() ?>">
            
            <div style="display: grid; grid-template-columns: 280px 1fr; gap: 40px; align-items: start;">
                
                <!-- Kiri: Foto Profil -->
                <div class="card" style="padding: 30px; text-align: center; background: white;">
                    <div style="position: relative; width: 150px; height: 150px; margin: 0 auto 20px; border-radius: 50%; overflow: hidden; border: 4px solid var(--primary-light); box-shadow: var(--shadow-md);">
                        <?php 
                        $avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($user_data['nama']) . '&background=4f46e5&color=fff&size=150';
                        if ($user_data['foto_profil'] && file_exists('assets/img/profil/' . $user_data['foto_profil'])) {
                            $avatar_url = 'assets/img/profil/' . $user_data['foto_profil'];
                        }
                        ?>
                        <img id="profile-preview" src="<?= $avatar_url ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    
                    <h3 style="font-size: 1.25rem; color: var(--text-main); margin-bottom: 5px;"><?= htmlspecialchars($user_data['nama']) ?></h3>
                    <p style="font-size: 0.85rem; color: var(--primary); font-weight: 600; text-transform: uppercase; margin-bottom: 20px;">
                        <?= htmlspecialchars($user_data['role']) ?>
                    </p>
                    
                    <label class="btn-primary" style="padding: 10px 20px; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center; border-radius: var(--radius-sm);">
                        <i class="fa-solid fa-camera"></i> Ganti Foto
                        <input type="file" name="foto_profil" id="foto_profil_input" accept="image/*" style="display: none;" onchange="previewImage(this)">
                    </label>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px;">Format: JPG, PNG, GIF. Max: 2MB.</p>
                </div>

                <!-- Kanan: Form Data Profil -->
                <div style="display: flex; flex-direction: column; gap: 30px;">
                    
                    <!-- Section 1: Data Diri -->
                    <div class="card" style="padding: 35px; background: white;">
                        <h3 style="font-family: var(--font-heading); color: var(--text-main); font-size: 1.3rem; margin-bottom: 25px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-id-card" style="color: var(--primary);"></i> Data Diri
                        </h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control-profile" required value="<?= htmlspecialchars($user_data['nama']) ?>">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Nama Panggilan</label>
                                <input type="text" name="nama_panggilan" class="form-control-profile" required maxlength="50" value="<?= htmlspecialchars($user_data['nama_panggilan'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" class="form-control-profile" required value="<?= htmlspecialchars($user_data['tempat_lahir']) ?>">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" class="form-control-profile" required value="<?= htmlspecialchars($user_data['tanggal_lahir']) ?>">
                            </div>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control-profile" rows="3" required style="resize: vertical; min-height: 80px;"><?= htmlspecialchars($user_data['alamat']) ?></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Wijk (Sektor)</label>
                                <input type="text" name="wijk" class="form-control-profile" required value="<?= htmlspecialchars($user_data['wijk']) ?>">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Angkatan Sidi</label>
                                <input type="text" name="angkatan_sidi" class="form-control-profile" required value="<?= htmlspecialchars($user_data['angkatan_sidi']) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Kontak & Akun -->
                    <div class="card" style="padding: 35px; background: white;">
                        <h3 style="font-family: var(--font-heading); color: var(--text-main); font-size: 1.3rem; margin-bottom: 25px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-envelope-open-text" style="color: var(--primary);"></i> Kontak & Akun
                        </h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Email</label>
                                <input type="email" name="email" class="form-control-profile" required value="<?= htmlspecialchars($user_data['email']) ?>">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Nomor WhatsApp</label>
                                <input type="text" name="whatsapp" class="form-control-profile" required placeholder="Contoh: 628123456789" value="<?= htmlspecialchars($user_data['whatsapp'] ?? '') ?>">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-control-profile" required>
                                    <option value="" disabled <?= is_null($user_data['jenis_kelamin']) ? 'selected' : '' ?>>Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki" <?= ($user_data['jenis_kelamin'] == 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="Perempuan" <?= ($user_data['jenis_kelamin'] == 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Notifikasi WhatsApp</label>
                                <select name="wa_notification" class="form-control-profile">
                                    <option value="aktif" <?= ($user_data['wa_notification'] == 'aktif') ? 'selected' : '' ?>>Aktifkan Notifikasi</option>
                                    <option value="nonaktif" <?= ($user_data['wa_notification'] == 'nonaktif') ? 'selected' : '' ?>>Nonaktifkan Notifikasi</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Keamanan -->
                    <div class="card" style="padding: 35px; background: white;">
                        <h3 style="font-family: var(--font-heading); color: var(--text-main); font-size: 1.3rem; margin-bottom: 25px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> Ubah Password
                        </h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">Kosongkan form di bawah ini jika Anda tidak ingin mengubah password saat ini.</p>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Password Baru</label>
                                <input type="password" name="password" class="form-control-profile" placeholder="Minimal 6 karakter" minlength="6">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); font-size: 0.9rem;">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="form-control-profile" placeholder="Ulangi password baru" minlength="6">
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div style="display: flex; gap: 15px; justify-content: flex-end;">
                        <a href="index.php" class="btn-secondary" style="padding: 15px 30px; border-radius: var(--radius-sm); font-size: 1rem;">Batal</a>
                        <button type="submit" class="btn-primary" style="padding: 15px 40px; border-radius: var(--radius-sm); font-size: 1rem; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                        </button>
                    </div>

                </div>

            </div>
        </form>
    </div>
</div>

<style>
.form-control-profile {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-family: var(--font-body);
    font-size: 0.95rem;
    color: var(--text-main);
    background: #f8fafc;
    transition: all 0.3s;
}

.form-control-profile:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
}

select.form-control-profile {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 16px;
    padding-right: 40px;
}

@media (max-width: 768px) {
    form > div {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
    }
    
    .form-control-profile {
        padding: 10px 14px;
    }
}
</style>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
