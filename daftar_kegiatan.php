<?php
session_start();
// Auth Check: Lempar pengguna ke login jika belum ada sesi user
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
// Mengambil config database
require_once 'config/database.php';

$user_id = (int)$_SESSION['user_id'];
// Fetch user details to prefill
$user_res = $conn->query("SELECT nama, email, whatsapp FROM users WHERE id = $user_id");
$user_data = $user_res ? $user_res->fetch_assoc() : ['nama' => '', 'email' => '', 'whatsapp' => ''];

$error = "";

$warta_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($warta_id <= 0) {
    $_SESSION['warta_flash_error'] = "Kegiatan tidak valid.";
    header("Location: warta.php");
    exit();
}

$warta_check = $conn->query("SELECT * FROM warta WHERE id = $warta_id");
if (!$warta_check || $warta_check->num_rows == 0) {
    $_SESSION['warta_flash_error'] = "Kegiatan tidak ditemukan.";
    header("Location: warta.php");
    exit();
}
$warta_info = $warta_check->fetch_assoc();

if (!$warta_info['butuh_pendaftaran']) {
    $_SESSION['warta_flash_error'] = "Kegiatan ini tidak membutuhkan pendaftaran.";
    header("Location: warta.php");
    exit();
}

// Check if already registered
$already_registered_res = $conn->query("SELECT id, status_pembayaran FROM pendaftaran_warta WHERE warta_id = $warta_id AND user_id = $user_id");
if ($already_registered_res && $already_registered_res->num_rows > 0) {
    $existing_reg = $already_registered_res->fetch_assoc();
    if ($existing_reg['status_pembayaran'] != 'Ditolak') {
        $_SESSION['warta_flash_error'] = "Anda sudah terdaftar dalam kegiatan ini.";
        header("Location: warta.php");
        exit();
    }
}

// Handle Registration Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_event'])) {
    $nama = $conn->real_escape_string($_POST['nama']);
    $email = $conn->real_escape_string($_POST['email']);
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    $biaya_kegiatan = (int)$warta_info['biaya'];
    $metode_pembayaran = ($biaya_kegiatan == 0) ? 'Tunai' : $conn->real_escape_string($_POST['metode_pembayaran'] ?? 'Tunai');
    $catatan = $conn->real_escape_string($_POST['catatan']);
    
    // Handle File Upload if Non Tunai
    $bukti_pembayaran_filename = NULL;
    if ($metode_pembayaran == 'Non Tunai') {
        if (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] == UPLOAD_ERR_NO_FILE) {
            $error = "Bukti pembayaran wajib diunggah untuk metode pembayaran Non Tunai.";
        } else {
            $file = $_FILES['bukti_pembayaran'];
            $file_name = $file['name'];
            $file_tmp = $file['tmp_name'];
            $file_size = $file['size'];
            $file_error = $file['error'];
            
            if ($file_error !== UPLOAD_ERR_OK) {
                $error = "Terjadi kesalahan saat mengunggah file. Kode error: $file_error";
            } else {
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (!in_array($file_ext, $allowed_exts)) {
                    $error = "Format file tidak didukung. Harap unggah file JPG, JPEG, PNG, atau WEBP.";
                } elseif ($file_size > 5 * 1024 * 1024) {
                    $error = "Ukuran file terlalu besar. Maksimal ukuran file adalah 5MB.";
                } else {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $file_tmp);
                    finfo_close($finfo);
                    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!in_array($mime_type, $allowed_mimes)) {
                        $error = "Tipe file tidak valid. Harap unggah gambar yang valid.";
                    }
                }
                
                if (empty($error)) {
                    $target_dir = 'assets/img/bukti_pembayaran/';
                    if (!is_dir($target_dir)) {
                        mkdir($target_dir, 0755, true);
                    }
                    
                    $new_filename = 'bukti_' . $warta_id . '_' . $user_id . '_' . time() . '.' . $file_ext;
                    $target_path = $target_dir . $new_filename;
                    
                    if (move_uploaded_file($file_tmp, $target_path)) {
                        $bukti_pembayaran_filename = $new_filename;
                    } else {
                        $error = "Gagal memindahkan file yang diunggah ke folder penyimpanan.";
                    }
                }
            }
        }
    }
    
    // Insert/Update into DB
    if (empty($error)) {
        $status_pembayaran = ($biaya_kegiatan == 0) ? 'Terdaftar' : (($metode_pembayaran == 'Tunai') ? 'Bayar di Tempat' : 'Menunggu Verifikasi');
        
        // If they had a rejected registration, delete it first
        $conn->query("DELETE FROM pendaftaran_warta WHERE warta_id = $warta_id AND user_id = $user_id AND status_pembayaran = 'Ditolak'");
        
        $stmt = $conn->prepare("INSERT INTO pendaftaran_warta (warta_id, user_id, nama_peserta, email_peserta, whatsapp_peserta, metode_pembayaran, bukti_pembayaran, catatan, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssssss", $warta_id, $user_id, $nama, $email, $whatsapp, $metode_pembayaran, $bukti_pembayaran_filename, $catatan, $status_pembayaran);
        
        if ($stmt->execute()) {
            $_SESSION['profil_flash_success'] = "Pendaftaran kegiatan '" . htmlspecialchars($warta_info['judul']) . "' berhasil! Terima kasih telah mendaftar.";
            header("Location: profil.php");
            exit();
        } else {
            $error = "Terjadi kesalahan pada database saat menyimpan pendaftaran: " . $conn->error;
        }
    }
}

include 'includes/header.php';
?>

<div style="padding-top: 130px; padding-bottom: 80px; background: var(--bg-subtle); min-height: 100vh;">
    <div class="container" style="max-width: 700px;">
        
        <div style="margin-bottom: 20px;">
            <a href="warta.php" class="btn-secondary" style="padding: 10px 20px; font-size: 0.9rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Warta
            </a>
        </div>

        <?php if(!empty($error)): ?>
            <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 15px 20px; border-radius: var(--radius-md); margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 1.2rem;"></i>
                <span><?= $error ?></span>
            </div>
        <?php endif; ?>

        <div class="card" style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-md); overflow: hidden;">
            <div style="background: var(--gradient-primary); padding: 25px 30px; color: white;">
                <h2 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 8px; color: white;">Pendaftaran Kegiatan</h2>
                <h3 style="font-size: 1.1rem; font-weight: 500; opacity: 0.9; margin: 0; margin-bottom: 15px;"><?= htmlspecialchars($warta_info['judul']) ?></h3>
                <div style="background: rgba(255,255,255,0.15); padding: 10px 15px; border-radius: 6px; display: inline-flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 600; font-size: 1rem;">
                        <?= ($warta_info['biaya'] > 0) ? 'Biaya Pendaftaran: Rp ' . number_format($warta_info['biaya'], 0, ',', '.') : 'Gratis / Tidak Dipungut Biaya' ?>
                    </span>
                </div>
            </div>
            
            <form action="" method="POST" enctype="multipart/form-data" style="padding: 30px;">
                <input type="hidden" name="register_event" value="1">
                
                <h4 style="font-family: var(--font-heading); color: var(--text-main); margin-bottom: 20px; border-bottom: 2px solid var(--bg-subtle); padding-bottom: 10px;">Data Peserta</h4>
                
                <!-- Read-only Prefilled fields -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($user_data['nama']) ?>" required readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; background-color: #f8fafc; color: var(--text-muted); cursor: not-allowed; outline: none; font-size: 0.95rem;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; background-color: #f8fafc; color: var(--text-muted); cursor: not-allowed; outline: none; font-size: 0.95rem;">
                    </div>
                </div>
                
                <div style="margin-bottom: 30px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Nomor WhatsApp</label>
                    <input type="text" name="whatsapp" value="<?= htmlspecialchars($user_data['whatsapp'] ?? '') ?>" required readonly style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; background-color: #f8fafc; color: var(--text-muted); cursor: not-allowed; outline: none; font-size: 0.95rem;">
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 6px;"><i class="fa-solid fa-info-circle"></i> Data peserta diambil otomatis dari profil akun Anda.</p>
                </div>
                
                <h4 style="font-family: var(--font-heading); color: var(--text-main); margin-bottom: 20px; border-bottom: 2px solid var(--bg-subtle); padding-bottom: 10px;">Pembayaran & Keterangan</h4>
                
                <?php if ($warta_info['biaya'] > 0): ?>
                <!-- Payment Method Selector -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" onchange="togglePaymentSection()" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; font-size: 0.95rem; font-family: var(--font-body); cursor: pointer; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
                        <option value="Tunai">Tunai (Bayar di Tempat)</option>
                        <option value="Non Tunai">Non Tunai (QRIS)</option>
                    </select>
                </div>
                
                <!-- QRIS Section (Hidden by Default) -->
                <div id="qris_payment_section" style="display: none; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 25px; margin-bottom: 25px; animation: fadeIn 0.3s ease;">
                    <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 20px;">
                        <p style="font-size: 0.9rem; color: var(--text-main); font-weight: 500; text-align: center; margin-bottom: 15px;">
                            Silakan scan kode QRIS di bawah ini menggunakan aplikasi e-wallet atau mobile banking Anda:
                        </p>
                        <img src="assets/img/qris.png" alt="QRIS Naposo HKBP Duren Jaya" style="width: 200px; height: 200px; border-radius: 12px; box-shadow: var(--shadow-md); border: 4px solid white;">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Unggah Bukti Pembayaran <span style="color: var(--accent);">*</span></label>
                        <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept="image/jpeg,image/png,image/webp" onchange="previewImage(event)" style="width: 100%; padding: 10px 12px; border: 1px dashed var(--primary); border-radius: 8px; background: white; font-size: 0.9rem; outline: none; cursor: pointer;">
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 6px;">Mendukung format JPG, JPEG, PNG, WEBP. Maksimal ukuran file 5MB.</p>
                        
                        <!-- Image Preview Area -->
                        <div id="image_preview_container" style="display: none; margin-top: 15px; text-align: center; border: 1px solid var(--border-color); padding: 15px; border-radius: 8px; background: white;">
                            <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 10px; font-weight: 500;">Pratinjau Bukti Pembayaran:</span>
                            <img id="image_preview" src="" alt="Pratinjau" style="max-height: 200px; margin: 0 auto; border-radius: 6px; box-shadow: var(--shadow-sm);">
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Additional Notes (Optional) -->
                <div style="margin-bottom: 30px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Catatan Tambahan <span style="font-weight: 400; color: var(--text-muted); font-size: 0.8rem;">(Opsional)</span></label>
                    <textarea name="catatan" rows="3" placeholder="Tuliskan catatan khusus atau keterangan jika ada..." style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; font-size: 0.95rem; font-family: var(--font-body); resize: vertical; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'"></textarea>
                </div>
                
                <!-- Buttons -->
                <div style="display: flex; gap: 15px; justify-content: flex-end; border-top: 2px solid var(--bg-subtle); padding-top: 20px;">
                    <button type="submit" class="btn-primary" style="padding: 12px 30px; font-size: 1rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center; transform: none; box-shadow: var(--shadow-md);">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePaymentSection() {
    const method = document.getElementById('metode_pembayaran').value;
    const qrisSection = document.getElementById('qris_payment_section');
    const proofInput = document.getElementById('bukti_pembayaran');
    
    if (method === 'Non Tunai') {
        qrisSection.style.display = 'block';
        proofInput.required = true;
    } else {
        qrisSection.style.display = 'none';
        proofInput.required = false;
    }
}

function previewImage(event) {
    const input = event.target;
    const previewContainer = document.getElementById('image_preview_container');
    const previewImage = document.getElementById('image_preview');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate client side file size
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal ukuran file adalah 5MB.');
            input.value = '';
            previewContainer.style.display = 'none';
            previewImage.src = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewContainer.style.display = 'none';
        previewImage.src = '';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
