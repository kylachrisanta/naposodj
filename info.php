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
$success = "";

if (isset($_SESSION['info_flash_success'])) {
    $success = $_SESSION['info_flash_success'];
    unset($_SESSION['info_flash_success']);
}
if (isset($_SESSION['info_flash_error'])) {
    $error = $_SESSION['info_flash_error'];
    unset($_SESSION['info_flash_error']);
}

// Handle Registration Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_event'])) {
    $warta_id = (int)$_POST['warta_id'];
    $nama = $conn->real_escape_string($_POST['nama']);
    $email = $conn->real_escape_string($_POST['email']);
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    $metode_pembayaran = $conn->real_escape_string($_POST['metode_pembayaran']);
    $catatan = $conn->real_escape_string($_POST['catatan']);
    
    // Validate that this warta requires registration
    $warta_check = $conn->query("SELECT butuh_pendaftaran FROM warta WHERE id = $warta_id");
    if (!$warta_check || $warta_check->num_rows == 0) {
        $error = "Kegiatan tidak ditemukan.";
    } else {
        $warta_info = $warta_check->fetch_assoc();
        if (!$warta_info['butuh_pendaftaran']) {
            $error = "Kegiatan ini tidak membutuhkan pendaftaran.";
        }
    }
    
    // Check if already registered
    if (empty($error)) {
        $already_registered_res = $conn->query("SELECT id, status_pembayaran FROM pendaftaran_warta WHERE warta_id = $warta_id AND user_id = $user_id");
        if ($already_registered_res && $already_registered_res->num_rows > 0) {
            $existing_reg = $already_registered_res->fetch_assoc();
            if ($existing_reg['status_pembayaran'] != 'Ditolak') {
                $error = "Anda sudah terdaftar dalam kegiatan ini.";
            }
        }
    }
    
    // Handle File Upload if Non Tunai
    $bukti_pembayaran_filename = NULL;
    if (empty($error) && $metode_pembayaran == 'Non Tunai') {
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
        $status_pembayaran = ($metode_pembayaran == 'Tunai') ? 'Bayar di Tempat' : 'Menunggu Verifikasi';
        
        // If they had a rejected registration, delete it first
        $conn->query("DELETE FROM pendaftaran_warta WHERE warta_id = $warta_id AND user_id = $user_id AND status_pembayaran = 'Ditolak'");
        
        $stmt = $conn->prepare("INSERT INTO pendaftaran_warta (warta_id, user_id, nama_peserta, email_peserta, whatsapp_peserta, metode_pembayaran, bukti_pembayaran, catatan, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssssss", $warta_id, $user_id, $nama, $email, $whatsapp, $metode_pembayaran, $bukti_pembayaran_filename, $catatan, $status_pembayaran);
        
        if ($stmt->execute()) {
            $_SESSION['info_flash_success'] = "Pendaftaran berhasil! Terima kasih telah mendaftar.";
            header("Location: info.php");
            exit();
        } else {
            $error = "Terjadi kesalahan pada database saat menyimpan pendaftaran: " . $conn->error;
        }
    }
    
    $_SESSION['info_flash_error'] = $error;
    header("Location: info.php");
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<!-- Info Header -->
<section class="section bg-subtle" style="padding-top: 120px; padding-bottom: 40px;">
    <div class="container text-center">
        <h1 class="section-title">Informasi Utama</h1>
        <p class="section-subtitle">Jadwal kegiatan mingguan dan pengumuman bagi anggota Naposo HKBP Duren Jaya.</p>
    </div>
</section>

<!-- Alerts Section -->
<?php if (!empty($success)): ?>
    <div class="container" style="margin-top: 20px; margin-bottom: -10px;">
        <div style="color: #15803d; background: #dcfce7; padding: 15px 20px; border-radius: var(--radius-md); border: 1px solid #86efac; font-weight: 500; display: flex; align-items: center; gap: 10px; animation: fadeIn 0.4s ease;">
            <i class="fa-solid fa-circle-check" style="font-size: 1.15rem;"></i> <?= $success ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="container" style="margin-top: 20px; margin-bottom: -10px;">
        <div style="color: #b91c1c; background: #fee2e2; padding: 15px 20px; border-radius: var(--radius-md); border: 1px solid #fecaca; font-weight: 500; display: flex; align-items: center; gap: 10px; animation: fadeIn 0.4s ease;">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 1.15rem;"></i> <?= $error ?>
        </div>
    </div>
<?php endif; ?>

<!-- Jadwal Kegiatan -->
<section id="kegiatan" class="section" style="padding-top: 40px; padding-bottom: 40px;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
            <h2 style="font-family: var(--font-heading); font-size: 1.75rem;"><i class="fa-regular fa-calendar-check" style="color: var(--primary);"></i> Jadwal Kegiatan Seminggu</h2>
        </div>
        
        <div style="overflow-x: auto; background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); margin-top: 20px;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
                        <th style="padding: 15px 20px; font-weight: 600; color: var(--text-main);">No</th>
                        <th style="padding: 15px 20px; font-weight: 600; color: var(--text-main);">Nama Kegiatan</th>
                        <th style="padding: 15px 20px; font-weight: 600; color: var(--text-main);">Waktu</th>
                        <th style="padding: 15px 20px; font-weight: 600; color: var(--text-main);">Tempat</th>
                        <th style="padding: 15px 20px; font-weight: 600; color: var(--text-main);">Penanggung Jawab</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result_keg = $conn->query("SELECT * FROM kegiatan ORDER BY tanggal ASC");
                    $no = 1;
                    if($result_keg->num_rows > 0):
                        while($row_keg = $result_keg->fetch_assoc()):
                    ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 15px 20px; color: var(--text-muted);"><?= $no++ ?></td>
                        <td style="padding: 15px 20px; font-weight: 500;"><?= htmlspecialchars($row_keg['nama_kegiatan']) ?></td>
                        <td style="padding: 15px 20px; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($row_keg['tanggal'])) ?> WIB</td>
                        <td style="padding: 15px 20px; color: var(--text-muted);"><?= htmlspecialchars($row_keg['tempat']) ?></td>
                        <td style="padding: 15px 20px; color: var(--text-muted);"><?= htmlspecialchars($row_keg['penanggung_jawab']) ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted);">Belum ada jadwal kegiatan minggu ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Warta Pengumuman -->
<section id="warta" class="section bg-subtle" style="padding-top: 40px;">
    <div class="container">
        <h2 style="font-family: var(--font-heading); font-size: 1.75rem; margin-bottom: 20px;"><i class="fa-solid fa-bullhorn" style="color: var(--accent);"></i> Warta</h2>
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php
            $result_warta = $conn->query("SELECT w.*, (SELECT COUNT(*) FROM pendaftaran_warta pw WHERE pw.warta_id = w.id AND pw.status_pembayaran != 'Ditolak') as total_pendaftar FROM warta w ORDER BY tanggal_posting DESC");
            $warta_colors = ['var(--primary)', 'var(--accent)', '#10b981', '#8b5cf6'];
            $color_idx = 0;
            
            if($result_warta->num_rows > 0):
                while($row_warta = $result_warta->fetch_assoc()):
                    // Rotasi warna border kiri untuk estetika
                    $border_color = $warta_colors[$color_idx % count($warta_colors)];
                    $color_idx++;
                    
                    // Check if current user is registered
                    $check_reg = $conn->query("SELECT status_pembayaran FROM pendaftaran_warta WHERE warta_id = {$row_warta['id']} AND user_id = $user_id");
                    $is_registered = false;
                    $reg_status = '';
                    if ($check_reg && $check_reg->num_rows > 0) {
                        $is_registered = true;
                        $reg_status_row = $check_reg->fetch_assoc();
                        $reg_status = $reg_status_row['status_pembayaran'];
                    }
            ?>
            <div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-left: 4px solid <?= $border_color ?>;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                    <div style="color: var(--text-muted); font-size: 0.875rem;"><i class="fa-regular fa-clock"></i> Diposting pada <?= date('d M Y', strtotime($row_warta['tanggal_posting'])) ?></div>
                    

                </div>
                
                <?php if (!empty($row_warta['gambar'])): ?>
                    <?php $warta_img_path = 'assets/img/warta/' . $row_warta['gambar']; ?>
                    <?php if (file_exists($warta_img_path)): ?>
                        <div style="margin-bottom: 20px; border-radius: var(--radius-sm); overflow: hidden; box-shadow: var(--shadow-sm); transition: transform 0.3s ease, box-shadow 0.3s ease;" class="warta-image-container" onclick="openLightbox('<?= $warta_img_path ?>')">
                            <img src="<?= $warta_img_path ?>" alt="<?= htmlspecialchars($row_warta['judul']) ?>" style="width: 100%; height: auto; max-height: 380px; object-fit: cover; transition: transform 0.5s ease;" class="warta-card-img">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <h3 style="font-family: var(--font-heading); font-size: 1.25rem; margin-bottom: 10px;"><?= htmlspecialchars($row_warta['judul']) ?></h3>
                <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 20px;"><?= nl2br(htmlspecialchars($row_warta['isi_pengumuman'])) ?></p>
                
                <?php
                // Fetch stats for this warta
                $reaksi_res = $conn->query("SELECT emoticon, COUNT(*) as count FROM warta_reaksi WHERE warta_id = {$row_warta['id']} GROUP BY emoticon");
                $stats = [];
                while($r = $reaksi_res->fetch_assoc()){
                    $stats[$r['emoticon']] = $r['count'];
                }
                
                // Check user's current reaction
                $user_reaksi_res = $conn->query("SELECT emoticon FROM warta_reaksi WHERE warta_id = {$row_warta['id']} AND user_id = $user_id");
                $user_reaction = ($user_reaksi_res && $user_reaksi_res->num_rows > 0) ? $user_reaksi_res->fetch_assoc()['emoticon'] : null;
                ?>
                <div class="reaction-container" data-warta-id="<?= $row_warta['id'] ?>" style="display: flex; align-items: center; gap: 10px; margin-bottom: <?= $row_warta['butuh_pendaftaran'] ? '15px' : '0' ?>; position: relative;">
                    <div class="reaction-trigger" style="position: relative; display: inline-block;">
                        <button class="btn-reaction-trigger" style="background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: 999px; padding: 6px 12px; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; gap: 6px; font-size: 0.9rem; transition: all 0.2s;" onmouseover="this.nextElementSibling.style.display='flex';" onmouseout="this.nextElementSibling.style.display='none';">
                            <i class="fa-regular fa-face-smile"></i> Suka
                        </button>
                        <div class="reaction-popover" style="display: none; position: absolute; bottom: 100%; left: 0; background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); padding: 8px 12px; gap: 8px; margin-bottom: 10px; z-index: 10;" onmouseover="this.style.display='flex';" onmouseout="this.style.display='none';">
                            <?php 
                            $emoticons = ['👍', '❤️', '🙏', '😂', '😮', '😢'];
                            foreach ($emoticons as $emoticon): 
                                $isActive = ($user_reaction === $emoticon) ? 'background: #f1f5f9; transform: scale(1.1);' : '';
                            ?>
                                <button class="emoticon-btn" onclick="sendReaction(<?= $row_warta['id'] ?>, '<?= $emoticon ?>')" style="background: transparent; border: none; font-size: 1.5rem; cursor: pointer; transition: transform 0.2s; padding: 4px; border-radius: 50%; <?= $isActive ?>" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='<?= $user_reaction === $emoticon ? 'scale(1.1)' : 'scale(1)' ?>'">
                                    <?= $emoticon ?>
                                </button>
                            <?php endforeach; ?>
                            <button class="emoticon-btn emoji-plus-btn" onclick="toggleEmojiPicker(<?= $row_warta['id'] ?>, event)" style="background: #f1f5f9; border: none; font-size: 1.2rem; cursor: pointer; transition: transform 0.2s; padding: 4px; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-left: 4px;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                <i class="fa-solid fa-plus" style="color: var(--text-muted);"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="reaction-stats" id="stats-<?= $row_warta['id'] ?>" style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <?php foreach($stats as $emo => $count): if($count > 0): ?>
                            <span class="reaction-badge" style="background: white; border: 1px solid var(--border-color); border-radius: 999px; padding: 2px 8px; font-size: 0.8rem; display: flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); <?= ($user_reaction === $emo) ? 'border-color: var(--primary); background: #f8fafc;' : '' ?>">
                                <?= $emo ?> <?= $count ?>
                            </span>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
                
                <?php if ($row_warta['butuh_pendaftaran']): ?>
                    <div style="border-top: 1px solid var(--border-color); padding-top: 15px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <?php if ($is_registered): ?>
                                <?php
                                $badge_bg = ''; $badge_fg = ''; $status_txt = '';
                                if ($reg_status == 'Lunas') {
                                    $badge_bg = '#dcfce7'; $badge_fg = '#15803d'; $status_txt = 'Terdaftar (Pembayaran Lunas)';
                                } elseif ($reg_status == 'Menunggu Verifikasi') {
                                    $badge_bg = '#fef3c7'; $badge_fg = '#d97706'; $status_txt = 'Terdaftar (Menunggu Verifikasi)';
                                } elseif ($reg_status == 'Bayar di Tempat') {
                                    $badge_bg = '#dbeafe'; $badge_fg = '#1d4ed8'; $status_txt = 'Terdaftar (Bayar di Tempat)';
                                } elseif ($reg_status == 'Ditolak') {
                                    $badge_bg = '#fee2e2'; $badge_fg = '#b91c1c'; $status_txt = 'Pendaftaran Ditolak';
                                }
                                ?>
                                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: <?= $badge_bg ?>; color: <?= $badge_fg ?>; border-radius: 6px; font-weight: 600; font-size: 0.9rem;">
                                    <i class="fa-solid fa-circle-check"></i> <?= $status_txt ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!$is_registered || $reg_status == 'Ditolak'): ?>
                            <button onclick='openRegisterModal(<?= json_encode($row_warta) ?>)' class="btn-primary" style="padding: 8px 20px; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; text-decoration: none;">
                                <i class="fa-solid fa-user-plus"></i> Daftar Kegiatan
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endwhile; else: ?>
            <div style="text-align: center; padding: 40px; color: var(--text-muted); background: white; border-radius: var(--radius-md);">Belum ada warta jemaat saat ini.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Modal Registration Form -->
<div id="registerModal" style="display:none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; backdrop-filter: blur(8px); justify-content: center; align-items: center; padding: 20px; overflow-y: auto;">
    <div style="background: white; max-width: 600px; width: 100%; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); overflow: hidden; animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); margin: auto;">
        
        <!-- Header -->
        <div style="background: var(--gradient-primary); padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; color: white;">
            <div>
                <h3 style="font-family: var(--font-heading); font-size: 1.35rem; margin: 0; color: white;">Form Pendaftaran Kegiatan</h3>
                <span id="modal_event_title" style="font-size: 0.85rem; opacity: 0.9; font-weight: 500; display: block; margin-top: 4px;"></span>
            </div>
            <button onclick="closeRegisterModal()" style="background: rgba(255,255,255,0.15); border: none; border-radius: 50%; width: 32px; height: 32px; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <!-- Form Body -->
        <form action="info.php" method="POST" enctype="multipart/form-data" style="padding: 25px; margin: 0;">
            <input type="hidden" name="register_event" value="1">
            <input type="hidden" name="warta_id" id="modal_warta_id">
            
            <!-- Read-only Prefilled fields -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user_data['nama']) ?>" required readonly style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background-color: #f8fafc; color: var(--text-muted); cursor: not-allowed; outline: none; font-size: 0.95rem;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required readonly style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background-color: #f8fafc; color: var(--text-muted); cursor: not-allowed; outline: none; font-size: 0.95rem;">
                </div>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Nomor WhatsApp</label>
                <input type="text" name="whatsapp" value="<?= htmlspecialchars($user_data['whatsapp'] ?? '') ?>" required readonly style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background-color: #f8fafc; color: var(--text-muted); cursor: not-allowed; outline: none; font-size: 0.95rem;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">WhatsApp otomatis disesuaikan dengan nomor akun Anda.</p>
            </div>
            
            <!-- Payment Method Selector -->
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Metode Pembayaran</label>
                <select name="metode_pembayaran" id="metode_pembayaran" onchange="togglePaymentSection()" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; font-size: 0.95rem; font-family: var(--font-body); cursor: pointer; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'">
                    <option value="Tunai">Tunai (Bayar di Tempat)</option>
                    <option value="Non Tunai">Non Tunai (QRIS)</option>
                </select>
            </div>
            
            <!-- QRIS Section (Hidden by Default) -->
            <div id="qris_payment_section" style="display: none; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; margin-bottom: 15px; animation: fadeIn 0.3s ease;">
                <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 15px;">
                    <p style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500; text-align: center; margin-bottom: 10px;">
                        Silakan scan kode QRIS di bawah ini menggunakan aplikasi e-wallet atau mobile banking Anda:
                    </p>
                    <img src="assets/img/qris.png" alt="QRIS Naposo HKBP Duren Jaya" style="width: 180px; height: 180px; border-radius: 8px; box-shadow: var(--shadow-sm); border: 4px solid white;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Unggah Bukti Pembayaran <span style="color: var(--accent);">*</span></label>
                    <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept="image/jpeg,image/png,image/webp" onchange="previewImage(event)" style="width: 100%; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: 6px; background: white; font-size: 0.85rem; outline: none;">
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Mendukung format JPG, JPEG, PNG, WEBP. Maksimal ukuran file 5MB.</p>
                    
                    <!-- Image Preview Area -->
                    <div id="image_preview_container" style="display: none; margin-top: 10px; text-align: center; border: 1px dashed var(--border-color); padding: 10px; border-radius: 6px; background: white;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-bottom: 6px;">Pratinjau Bukti:</span>
                        <img id="image_preview" src="" alt="Pratinjau" style="max-height: 150px; margin: 0 auto; border-radius: 4px; box-shadow: var(--shadow-sm);">
                    </div>
                </div>
            </div>
            
            <!-- Additional Notes (Optional) -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Catatan Tambahan <span style="font-weight: 400; color: var(--text-muted); font-size: 0.8rem;">(Opsional)</span></label>
                <textarea name="catatan" rows="3" placeholder="Tuliskan catatan khusus atau keterangan jika ada..." style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; font-size: 0.95rem; font-family: var(--font-body); resize: vertical; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'"></textarea>
            </div>
            
            <!-- Buttons -->
            <div style="display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid var(--border-color); padding-top: 15px;">
                <button type="button" onclick="closeRegisterModal()" class="btn-secondary" style="padding: 10px 20px; font-size: 0.9rem; border-radius: 8px; height: auto;">Batal</button>
                <button type="submit" class="btn-primary" style="padding: 10px 24px; font-size: 0.9rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; height: auto; transform: none; box-shadow: none;">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Pendaftaran
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Global Emoji Picker -->
<div id="globalEmojiPickerContainer" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10001; box-shadow: var(--shadow-lg); border-radius: 8px;">
    <emoji-picker></emoji-picker>
</div>

<!-- Lightbox Modal for Warta Image Preview -->
<div id="imageLightbox" onclick="closeLightbox(event)" style="display:none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.95); z-index: 10000; backdrop-filter: blur(8px); justify-content: center; align-items: center; padding: 20px; cursor: zoom-out;">
    <div style="position: relative; max-width: 90%; max-height: 90%; cursor: default; display: flex; justify-content: center; align-items: center; animation: fadeIn 0.3s ease;" onclick="event.stopPropagation()">
        <!-- Close Button -->
        <button onclick="closeLightbox(event)" style="position: absolute; top: -50px; right: 0; background: rgba(255,255,255,0.15); border: none; border-radius: 50%; width: 40px; height: 40px; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 1.25rem;" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='scale(1)'">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <!-- Preview Image -->
        <img id="lightboxImage" src="" alt="Pratinjau Gambar Warta" style="max-width: 100%; max-height: 85vh; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); display: block; border: 4px solid white; object-fit: contain;">
    </div>
</div>

<script>
function openRegisterModal(warta) {
    document.getElementById('modal_warta_id').value = warta.id;
    document.getElementById('modal_event_title').innerText = warta.judul;
    
    // Reset form values & preview
    document.getElementById('metode_pembayaran').value = 'Tunai';
    document.getElementById('bukti_pembayaran').value = '';
    document.getElementById('bukti_pembayaran').required = false;
    document.getElementById('image_preview_container').style.display = 'none';
    document.getElementById('image_preview').src = '';
    document.getElementById('qris_payment_section').style.display = 'none';
    
    document.getElementById('registerModal').style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Disable page scrolling
}

function closeRegisterModal() {
    document.getElementById('registerModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // Re-enable page scrolling
}

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

function openLightbox(imageSrc) {
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImg = document.getElementById('lightboxImage');
    lightboxImg.src = imageSrc;
    lightbox.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeLightbox(event) {
    const lightbox = document.getElementById('imageLightbox');
    lightbox.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modals on Escape
window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeRegisterModal();
        closeLightbox();
    }
});

// Warta Reaction AJAX
function sendReaction(wartaId, emoticon) {
    fetch('ajax/warta_reaction.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ warta_id: wartaId, emoticon: emoticon })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const statsContainer = document.getElementById('stats-' + wartaId);
            statsContainer.innerHTML = '';
            
            for (const [emo, count] of Object.entries(data.stats)) {
                if (count > 0) {
                    const isActive = (data.user_reaction === emo);
                    const style = isActive ? 'border-color: var(--primary); background: #f8fafc;' : '';
                    statsContainer.innerHTML += `
                        <span class="reaction-badge" style="background: white; border: 1px solid var(--border-color); border-radius: 999px; padding: 2px 8px; font-size: 0.8rem; display: flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); ${style}">
                            ${emo} ${count}
                        </span>
                    `;
                }
            }
            
            const popover = document.querySelector(`.reaction-container[data-warta-id="${wartaId}"] .reaction-popover`);
            if (popover) {
                const btns = popover.querySelectorAll('.emoticon-btn:not(.emoji-plus-btn)');
                btns.forEach(btn => {
                    const isBtnActive = (btn.innerText.trim() === data.user_reaction);
                    if (isBtnActive) {
                        btn.style.background = '#f1f5f9';
                        btn.style.transform = 'scale(1.1)';
                        btn.onmouseout = function() { this.style.transform='scale(1.1)'; };
                    } else {
                        btn.style.background = 'transparent';
                        btn.style.transform = 'scale(1)';
                        btn.onmouseout = function() { this.style.transform='scale(1)'; };
                    }
                });
            }
        }
    })
    .catch(error => console.error('Fetch error:', error));
}

let currentWartaIdForPicker = null;

function toggleEmojiPicker(wartaId, event) {
    event.stopPropagation();
    currentWartaIdForPicker = wartaId;
    
    const pickerContainer = document.getElementById('globalEmojiPickerContainer');
    if (pickerContainer.style.display === 'block') {
        pickerContainer.style.display = 'none';
        return;
    }
    pickerContainer.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', () => {
    const picker = document.querySelector('emoji-picker');
    if (picker) {
        picker.addEventListener('emoji-click', event => {
            if (currentWartaIdForPicker !== null) {
                sendReaction(currentWartaIdForPicker, event.detail.unicode);
                document.getElementById('globalEmojiPickerContainer').style.display = 'none';
            }
        });
    }

    document.addEventListener('click', (e) => {
        const pickerContainer = document.getElementById('globalEmojiPickerContainer');
        if (pickerContainer && pickerContainer.style.display === 'block' && !pickerContainer.contains(e.target) && !e.target.closest('.emoji-plus-btn')) {
            pickerContainer.style.display = 'none';
        }
    });
});
</script>

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<style>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.warta-image-container {
    overflow: hidden;
    position: relative;
    cursor: pointer;
}
.warta-image-container:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.warta-image-container:hover .warta-card-img {
    transform: scale(1.02);
}
</style>

<?php include 'includes/footer.php'; ?>
