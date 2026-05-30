<?php
require_once '../includes/admin_header.php';
require_once '../includes/admin_sidebar.php';

$message = "";
if (isset($_SESSION['admin_flash'])) {
    $message = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
}

// Handle Delete Kegiatan
if (isset($_GET['delete_kegiatan'])) {
    $id = (int)$_GET['delete_kegiatan'];
    $conn->query("DELETE FROM kegiatan WHERE id=$id");
    $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Kegiatan berhasil dihapus.</div>";
    header("Location: kegiatan_warta.php");
    exit();
}

// Handle Delete Warta
if (isset($_GET['delete_warta'])) {
    $id = (int)$_GET['delete_warta'];
    $res = $conn->query("SELECT gambar FROM warta WHERE id=$id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (!empty($row['gambar'])) {
            $file_path = '../assets/img/warta/' . $row['gambar'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    $conn->query("DELETE FROM warta WHERE id=$id");
    $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Warta berhasil dihapus.</div>";
    header("Location: kegiatan_warta.php");
    exit();
}

// Handle Add / Edit Kegiatan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_kegiatan'])) {
    $nama_kegiatan = $conn->real_escape_string($_POST['nama_kegiatan']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
    $tempat = $conn->real_escape_string($_POST['tempat']);
    $penanggung_jawab = $conn->real_escape_string($_POST['penanggung_jawab']);
    
    if (isset($_POST['id_kegiatan']) && $_POST['id_kegiatan'] != '') {
        $id = (int)$_POST['id_kegiatan'];
        $sql = "UPDATE kegiatan SET nama_kegiatan='$nama_kegiatan', tanggal='$tanggal', tempat='$tempat', penanggung_jawab='$penanggung_jawab' WHERE id=$id";
        if($conn->query($sql)) {
            $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Kegiatan berhasil diperbarui.</div>";
        }
    } else {
        $sql = "INSERT INTO kegiatan (nama_kegiatan, tanggal, tempat, penanggung_jawab) VALUES ('$nama_kegiatan', '$tanggal', '$tempat', '$penanggung_jawab')";
        if($conn->query($sql)) {
            $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Kegiatan berhasil ditambahkan.</div>";
        }
    }
    header("Location: kegiatan_warta.php");
    exit();
}

// Handle Add / Edit Warta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_warta'])) {
    $judul = $conn->real_escape_string($_POST['judul']);
    $isi_pengumuman = $conn->real_escape_string($_POST['isi_pengumuman']);
    $butuh_pendaftaran = isset($_POST['butuh_pendaftaran']) ? 1 : 0;
    $biaya = $butuh_pendaftaran ? (int)$_POST['biaya'] : 0;
    
    $error_warta = "";
    $gambar_filename = NULL;
    $has_new_image = isset($_FILES['gambar']) && $_FILES['gambar']['error'] != UPLOAD_ERR_NO_FILE;
    
    if ($has_new_image) {
        $file = $_FILES['gambar'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];
        
        if ($file_error !== UPLOAD_ERR_OK) {
            $error_warta = "Gagal mengunggah gambar. Kode error: $file_error";
        } else {
            $allowed_exts = ['jpg', 'jpeg', 'png'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_exts)) {
                $error_warta = "Format gambar tidak didukung. Harap unggah file JPG, JPEG, atau PNG.";
            } elseif ($file_size > 5 * 1024 * 1024) {
                $error_warta = "Ukuran gambar terlalu besar. Maksimal 5MB.";
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);
                $allowed_mimes = ['image/jpeg', 'image/png'];
                if (!in_array($mime_type, $allowed_mimes)) {
                    $error_warta = "Tipe file tidak valid. Harap unggah gambar JPG/JPEG atau PNG yang valid.";
                }
            }
            
            if (empty($error_warta)) {
                $target_dir = '../assets/img/warta/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                
                $new_filename = 'warta_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                $target_path = $target_dir . $new_filename;
                
                if (move_uploaded_file($file_tmp, $target_path)) {
                    $gambar_filename = $new_filename;
                } else {
                    $error_warta = "Gagal menyimpan file gambar.";
                }
            }
        }
    }
    
    if (!empty($error_warta)) {
        $_SESSION['admin_flash'] = "<div style='color: #b91c1c; background: #fee2e2; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fecaca;'><i class='fa-solid fa-circle-xmark'></i> " . $error_warta . "</div>";
        header("Location: kegiatan_warta.php");
        exit();
    }
    
    if (isset($_POST['id_warta']) && $_POST['id_warta'] != '') {
        $id = (int)$_POST['id_warta'];
        
        // Get existing warta image
        $old_img_res = $conn->query("SELECT gambar FROM warta WHERE id=$id");
        $old_img = ($old_img_res && $old_img_res->num_rows > 0) ? $old_img_res->fetch_assoc()['gambar'] : NULL;
        
        $hapus_gambar = isset($_POST['hapus_gambar']) ? 1 : 0;
        
        if ($has_new_image) {
            // Delete old file
            if (!empty($old_img)) {
                $old_file_path = '../assets/img/warta/' . $old_img;
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            $sql = "UPDATE warta SET judul='$judul', isi_pengumuman='$isi_pengumuman', butuh_pendaftaran=$butuh_pendaftaran, biaya=$biaya, gambar='$gambar_filename' WHERE id=$id";
        } elseif ($hapus_gambar) {
            // Delete old file
            if (!empty($old_img)) {
                $old_file_path = '../assets/img/warta/' . $old_img;
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            $sql = "UPDATE warta SET judul='$judul', isi_pengumuman='$isi_pengumuman', butuh_pendaftaran=$butuh_pendaftaran, biaya=$biaya, gambar=NULL WHERE id=$id";
        } else {
            $sql = "UPDATE warta SET judul='$judul', isi_pengumuman='$isi_pengumuman', butuh_pendaftaran=$butuh_pendaftaran, biaya=$biaya WHERE id=$id";
        }
        
        if($conn->query($sql)) {
            $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Warta berhasil diperbarui.</div>";
        }
    } else {
        $gambar_val = $gambar_filename ? "'$gambar_filename'" : "NULL";
        $sql = "INSERT INTO warta (judul, isi_pengumuman, butuh_pendaftaran, biaya, gambar) VALUES ('$judul', '$isi_pengumuman', $butuh_pendaftaran, $biaya, $gambar_val)";
        if($conn->query($sql)) {
            $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Warta berhasil ditambahkan.</div>";
        }
    }
    header("Location: kegiatan_warta.php");
    exit();
}

// Check Edit Mode Kegiatan
$edit_kegiatan_mode = false;
$edit_kegiatan_data = [
    'id' => '', 'nama_kegiatan' => '', 'tanggal' => '', 'tempat' => '', 'penanggung_jawab' => ''
];
if (isset($_GET['edit_kegiatan'])) {
    $id = (int)$_GET['edit_kegiatan'];
    $res = $conn->query("SELECT * FROM kegiatan WHERE id=$id");
    if ($res->num_rows > 0) {
        $edit_kegiatan_mode = true;
        $edit_kegiatan_data = $res->fetch_assoc();
        $edit_kegiatan_data['tanggal'] = date('Y-m-d\TH:i', strtotime($edit_kegiatan_data['tanggal']));
    }
}

// Check Edit Mode Warta
$edit_warta_mode = false;
$edit_warta_data = [
    'id' => '', 'judul' => '', 'isi_pengumuman' => '', 'butuh_pendaftaran' => 0, 'biaya' => 0, 'gambar' => NULL
];
if (isset($_GET['edit_warta'])) {
    $id = (int)$_GET['edit_warta'];
    $res = $conn->query("SELECT * FROM warta WHERE id=$id");
    if ($res->num_rows > 0) {
        $edit_warta_mode = true;
        $edit_warta_data = $res->fetch_assoc();
    }
}

// Handle Launch WhatsApp Notifications (Manual Custom)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_wa_custom'])) {
    $target_id = (int)$_POST['id_kegiatan'];
    $custom_message = $_POST['pesan_custom'];
    
    // Cek apakah waktu kegiatan sudah lewat (expired)
    $check_res = $conn->query("SELECT tanggal FROM kegiatan WHERE id = $target_id");
    if ($check_res->num_rows > 0) {
        $kegiatan = $check_res->fetch_assoc();
        $waktu_kegiatan = strtotime($kegiatan['tanggal']);
        $waktu_sekarang = time(); // Sudah berbasis Asia/Jakarta karena set di database.php

        if ($waktu_kegiatan < $waktu_sekarang) {
            $_SESSION['admin_flash'] = "<div style='color: #b91c1c; background: #fee2e2; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fecaca;'><i class='fa-solid fa-circle-exclamation'></i> Pengingat tidak dapat dikirim karena jadwal kegiatan telah lewat.</div>";
            header("Location: kegiatan_warta.php");
            exit();
        }
    }
    
    // Sertakan script pengiriman (script ini sudah mendukung $target_id dan $custom_message)
    ob_start();
    include '../cron/send_wa_notifications.php';
    ob_end_clean();
    
    $_SESSION['admin_flash'] = "<div style='color: #1d4ed8; background: #dbeafe; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #93c5fd;'><i class='fa-solid fa-paper-plane'></i> Notifikasi WhatsApp berhasil dikirim dengan pesan kustom.</div>";
    header("Location: kegiatan_warta.php");
    exit();
}
?>

<div style="margin-bottom: 30px;">
    <h2>Kelola Kegiatan & Warta</h2>
    <p style="color: var(--text-muted);">Kelola jadwal kegiatan mingguan dan bagikan pengumuman warta kepada Naposo.</p>
</div>

<?= $message ?>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin-bottom: 30px;">
    <!-- Form Kegiatan -->
    <div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;"><i class="fa-regular fa-calendar-check" style="color: var(--primary);"></i> <?= $edit_kegiatan_mode ? 'Ubah Kegiatan' : 'Tambah Kegiatan Baru' ?></h3>
        <form action="kegiatan_warta.php" method="POST">
            <input type="hidden" name="id_kegiatan" value="<?= $edit_kegiatan_data['id'] ?>">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nama Kegiatan</label>
                <input type="text" name="nama_kegiatan" value="<?= htmlspecialchars($edit_kegiatan_data['nama_kegiatan']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Waktu (Tanggal & Jam)</label>
                <input type="datetime-local" name="tanggal" value="<?= $edit_kegiatan_data['tanggal'] ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Tempat</label>
                <input type="text" name="tempat" value="<?= htmlspecialchars($edit_kegiatan_data['tempat']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Penanggung Jawab</label>
                <input type="text" name="penanggung_jawab" value="<?= htmlspecialchars($edit_kegiatan_data['penanggung_jawab']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="submit_kegiatan" class="btn-primary"><?= $edit_kegiatan_mode ? 'Simpan Perubahan' : 'Simpan Kegiatan' ?></button>
                <?php if($edit_kegiatan_mode): ?>
                    <a href="kegiatan_warta.php" style="padding: 10px 20px; background: #e2e8f0; color: #334155; border-radius: 6px; font-weight: 500;">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Form Warta -->
    <div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;"><i class="fa-solid fa-bullhorn" style="color: var(--primary);"></i> <?= $edit_warta_mode ? 'Ubah Warta' : 'Tambah Warta Baru' ?></h3>
        <form action="kegiatan_warta.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_warta" value="<?= $edit_warta_data['id'] ?>">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Judul Pengumuman</label>
                <input type="text" name="judul" value="<?= htmlspecialchars($edit_warta_data['judul']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Isi Pengumuman</label>
                <textarea name="isi_pengumuman" rows="9" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: var(--font-body);"><?= htmlspecialchars($edit_warta_data['isi_pengumuman']) ?></textarea>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Gambar Pendukung <span style="font-weight: 400; color: var(--text-muted); font-size: 0.8rem;">(Opsional)</span></label>
                <input type="file" name="gambar" id="gambar_warta" accept="image/jpeg,image/png" onchange="previewWartaImage(event)" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 6px; outline: none; background: white; font-size: 0.9rem;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Mendukung format JPG, JPEG, PNG. Maksimal ukuran file 5MB.</p>
                
                <!-- Client-side image preview -->
                <div id="warta_preview_container" style="display: none; margin-top: 12px; border: 1px dashed var(--border-color); padding: 10px; border-radius: 6px; text-align: center; background: #f8fafc;">
                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-bottom: 6px;">Pratinjau Gambar Baru:</span>
                    <img id="warta_preview" src="" alt="Pratinjau Gambar" style="max-height: 150px; border-radius: 6px; box-shadow: var(--shadow-sm); max-width: 100%; object-fit: contain;">
                </div>
            </div>

            <?php if ($edit_warta_mode && !empty($edit_warta_data['gambar'])): ?>
            <div style="margin-bottom: 15px; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; display: flex; align-items: center; gap: 15px;">
                <div style="width: 60px; height: 60px; border-radius: 6px; overflow: hidden; border: 1px solid var(--border-color);">
                    <img src="../assets/img/warta/<?= htmlspecialchars($edit_warta_data['gambar']) ?>" alt="Gambar Aktif" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="flex: 1;">
                    <span style="font-size: 0.85rem; color: var(--text-muted); display: block; font-weight: 500;">Gambar Aktif saat ini</span>
                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                        <input type="checkbox" name="hapus_gambar" id="hapus_gambar" value="1" style="width: 15px; height: 15px; cursor: pointer;">
                        <label for="hapus_gambar" style="font-size: 0.85rem; color: #b91c1c; font-weight: 600; cursor: pointer;">Hapus Gambar Aktif</label>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="butuh_pendaftaran" id="butuh_pendaftaran" onchange="toggleBiayaField()" value="1" <?= ($edit_warta_data['butuh_pendaftaran'] ?? 0) == 1 ? 'checked' : '' ?> style="width: 18px; height: 18px; cursor: pointer;">
                <label for="butuh_pendaftaran" style="font-weight: 500; cursor: pointer; color: var(--text-main);">Butuh Pendaftaran Peserta</label>
            </div>
            
            <div id="biaya_field" style="margin-bottom: 20px; display: <?= ($edit_warta_data['butuh_pendaftaran'] ?? 0) == 1 ? 'block' : 'none' ?>; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid var(--border-color);">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Biaya Pendaftaran (Rp)</label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 600; color: var(--text-muted);">Rp</span>
                    <input type="number" name="biaya" value="<?= htmlspecialchars($edit_warta_data['biaya'] ?? 0) ?>" min="0" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 6px;">Biarkan 0 atau kosongkan jika kegiatan ini gratis.</p>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="submit_warta" class="btn-primary"><?= $edit_warta_mode ? 'Simpan Perubahan' : 'Sebarkan Warta' ?></button>
                <?php if($edit_warta_mode): ?>
                    <a href="kegiatan_warta.php" style="padding: 10px 20px; background: #e2e8f0; color: #334155; border-radius: 6px; font-weight: 500;">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
    <!-- Table Kegiatan -->
    <div style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden; display: flex; flex-direction: column;">
        <h3 style="padding: 20px; background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); margin: 0;">Daftar Kegiatan</h3>
        <div style="overflow-x: auto; flex: 1;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color);">
                        <th style="padding: 15px 20px;">Kegiatan</th>
                        <th style="padding: 15px 20px;">Waktu & Tempat</th>
                        <th style="padding: 15px 20px; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM kegiatan ORDER BY tanggal DESC");
                    while($row = $result->fetch_assoc()):
                        $is_expired = strtotime($row['tanggal']) < time();
                    ?>
                    <tr style="border-bottom: 1px solid var(--border-color); <?= $is_expired ? 'background: #fafafa;' : '' ?>">
                        <td style="padding: 15px 20px; vertical-align: top;">
                            <strong style="display: block; margin-bottom: 4px;">
                                <?= htmlspecialchars($row['nama_kegiatan']) ?>
                                <?php if($is_expired): ?>
                                    <span style="font-size: 0.7rem; background: #f1f5f9; color: #64748b; padding: 2px 6px; border-radius: 4px; margin-left: 5px; font-weight: 600; text-transform: uppercase;">Selesai</span>
                                <?php endif; ?>
                            </strong>
                            <span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fa-solid fa-user-tie" style="margin-right:4px;"></i><?= htmlspecialchars($row['penanggung_jawab']) ?></span>
                        </td>
                        <td style="padding: 15px 20px; color: var(--text-muted); font-size: 0.9rem; vertical-align: top;">
                            <div style="margin-bottom: 4px;"><i class="fa-regular fa-clock" style="margin-right:4px;"></i><?= date('d M Y, H:i', strtotime($row['tanggal'])) ?> WIB</div>
                            <div><i class="fa-solid fa-location-dot" style="margin-right:4px;"></i><?= htmlspecialchars($row['tempat']) ?></div>
                        </td>
                        <td style="padding: 15px 20px; vertical-align: top; white-space: nowrap;">
                            <?php if ($is_expired): ?>
                                <button onclick="alert('Pengingat tidak dapat dikirim karena jadwal kegiatan telah lewat.')" style="color: #94a3b8; margin-right: 15px; background: none; border: none; cursor: pointer; padding: 0; font-size: inherit; font-family: inherit;" title="Jadwal telah lewat">
                                    <i class="fa-solid fa-paper-plane"></i> Luncurkan
                                </button>
                            <?php else: ?>
                                <button onclick='openWAModal(<?= json_encode($row) ?>)' style="color: #059669; margin-right: 15px; background: none; border: none; cursor: pointer; padding: 0; font-size: inherit; font-family: inherit;" title="Luncurkan WA">
                                    <i class="fa-solid fa-paper-plane"></i> Luncurkan
                                </button>
                            <?php endif; ?>
                            <a href="kegiatan_warta.php?edit_kegiatan=<?= $row['id'] ?>" style="color: var(--accent); margin-right: 15px;" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a href="kegiatan_warta.php?delete_kegiatan=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus jadwal ini?');" style="color: #b91c1c;" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($result->num_rows == 0): ?>
                    <tr>
                        <td colspan="3" style="padding: 20px; text-align: center; color: var(--text-muted);">Belum ada jadwal kegiatan.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table Warta -->
    <div style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden; display: flex; flex-direction: column;">
        <h3 style="padding: 20px; background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); margin: 0;">Daftar Warta</h3>
        <div style="overflow-x: auto; flex: 1;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color);">
                        <th style="padding: 15px 20px;">Judul</th>
                        <th style="padding: 15px 20px;">Tanggal Posting</th>
                        <th style="padding: 15px 20px; width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT *, (SELECT COUNT(*) FROM pendaftaran_warta WHERE warta_id = warta.id) as total_pendaftar FROM warta ORDER BY tanggal_posting DESC");
                    while($row = $result->fetch_assoc()):
                    ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 15px 20px; vertical-align: top;">
                            <strong style="display: block; margin-bottom: 4px;"><?= htmlspecialchars($row['judul']) ?></strong>
                            <span style="font-size: 0.85rem; color: var(--text-muted);"><?= mb_strimwidth(htmlspecialchars($row['isi_pengumuman']), 0, 80, "...") ?></span>
                            <?php if ($row['butuh_pendaftaran']): ?>
                                <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span style="font-size: 0.75rem; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 9999px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-user-check"></i> Pendaftaran Aktif
                                    </span>
                                    <a href="pendaftar.php?warta_id=<?= $row['id'] ?>" style="font-size: 0.8rem; color: var(--primary); font-weight: 600; text-decoration: underline;" title="Kelola Peserta">
                                        (<?= $row['total_pendaftar'] ?> Pendaftar) Kelola Peserta
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php
                            $reaksi_stats = $conn->query("SELECT emoticon, COUNT(*) as count FROM warta_reaksi WHERE warta_id = {$row['id']} GROUP BY emoticon");
                            if ($reaksi_stats && $reaksi_stats->num_rows > 0):
                            ?>
                                <div style="margin-top: 10px; display: flex; gap: 6px; flex-wrap: wrap;">
                                <?php while($r = $reaksi_stats->fetch_assoc()): ?>
                                    <span title="Total Reaksi <?= $r['emoticon'] ?>" style="font-size: 0.75rem; background: #f8fafc; border: 1px solid var(--border-color); color: var(--text-muted); padding: 2px 6px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 4px;">
                                        <?= $r['emoticon'] ?> <?= $r['count'] ?>
                                    </span>
                                <?php endwhile; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 15px 20px; color: var(--text-muted); font-size: 0.9rem; vertical-align: top;">
                            <?= date('d M Y, H:i', strtotime($row['tanggal_posting'])) ?>
                        </td>
                        <td style="padding: 15px 20px; vertical-align: top; white-space: nowrap;">
                            <a href="kegiatan_warta.php?edit_warta=<?= $row['id'] ?>" style="color: var(--accent); margin-right: 15px;" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a href="kegiatan_warta.php?delete_warta=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus warta ini?');" style="color: #b91c1c;" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($result->num_rows == 0): ?>
                    <tr>
                        <td colspan="3" style="padding: 20px; text-align: center; color: var(--text-muted);">Belum ada warta/pengumuman.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- WhatsApp Custom Message Modal -->
<div id="waModal" class="modal-overlay" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; backdrop-filter: blur(4px);">
    <div style="background: white; max-width: 600px; width: 90%; margin: 50px auto; border-radius: var(--radius-md); padding: 30px; position: relative; box-shadow: var(--shadow-lg);">
        <span onclick="closeWAModal()" style="position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</span>
        <h3 style="margin-bottom: 20px; border-bottom: 2px solid var(--bg-subtle); padding-bottom: 10px; color: var(--primary);">Kirim Pengingat WhatsApp</h3>
        
        <form action="kegiatan_warta.php" method="POST">
            <input type="hidden" name="id_kegiatan" id="wa_id_kegiatan">
            
            <div style="background: #f8fafc; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid var(--border-color);">
                <div style="margin-bottom: 5px;"><strong>Kegiatan:</strong> <span id="wa_display_nama"></span></div>
                <div style="margin-bottom: 5px;"><strong>Waktu:</strong> <span id="wa_display_waktu"></span></div>
                <div><strong>Tempat:</strong> <span id="wa_display_tempat"></span></div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Isi Pesan WhatsApp</label>
                <textarea name="pesan_custom" id="wa_pesan_custom" rows="12" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 6px; font-family: var(--font-body); font-size: 0.95rem; line-height: 1.5;"></textarea>
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 8px;">
                    <i class="fa-solid fa-circle-info"></i> Gunakan <b>{nama}</b> untuk memanggil nama penerima secara otomatis.
                </p>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeWAModal()" style="padding: 10px 20px; background: #e2e8f0; color: #334155; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">Batal</button>
                <button type="submit" name="submit_wa_custom" class="btn-primary" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Notifikasi Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openWAModal(kegiatan) {
    document.getElementById('wa_id_kegiatan').value = kegiatan.id;
    document.getElementById('wa_display_nama').innerText = kegiatan.nama_kegiatan;
    
    const date = new Date(kegiatan.tanggal);
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    const tgl = date.toLocaleDateString('id-ID', options);
    const jam = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
    
    document.getElementById('wa_display_waktu').innerText = tgl + ', ' + jam + ' WIB';
    document.getElementById('wa_display_tempat').innerText = kegiatan.tempat;

    let template = `Halo *{nama}*! 

Ada kegiatan seru nih di Naposo HKBP Duren Jaya! Mari kita luangkan waktu untuk berkumpul bersama.

📌 *Kegiatan:* ${kegiatan.nama_kegiatan}
📅 *Tanggal:* ${tgl}
⏰ *Waktu:* ${jam} WIB
📍 *Tempat:* ${kegiatan.tempat}

Mari kita persiapkan hati dan diri untuk hadir tepat waktu. Sampai jumpa di lokasi! Tuhan Yesus memberkati! 🙏✨`;

    document.getElementById('wa_pesan_custom').value = template;
    document.getElementById('waModal').style.display = 'block';
}

function closeWAModal() {
    document.getElementById('waModal').style.display = 'none';
}

function previewWartaImage(event) {
    const input = event.target;
    const previewContainer = document.getElementById('warta_preview_container');
    const previewImage = document.getElementById('warta_preview');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
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
function toggleBiayaField() {
    const isChecked = document.getElementById('butuh_pendaftaran').checked;
    document.getElementById('biaya_field').style.display = isChecked ? 'block' : 'none';
}
</script>

<style>
.modal-overlay { display: flex; align-items: flex-start; justify-content: center; }
</style>

<?php require_once '../includes/admin_footer.php'; ?>
