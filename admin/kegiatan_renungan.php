<?php
require_once '../includes/admin_header.php';
require_once '../includes/admin_sidebar.php';

$message = "";
if (isset($_SESSION['admin_flash'])) {
    $message = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
}

// ----------------------------------------------------
// Section 1: Comment Moderation Handler
// ----------------------------------------------------
$manage_comments_id = isset($_GET['manage_comments']) ? (int)$_GET['manage_comments'] : 0;
if ($manage_comments_id > 0) {
    // Fetch renungan info
    $renungan_res = $conn->query("SELECT * FROM renungan WHERE id = $manage_comments_id");
    if ($renungan_res->num_rows == 0) {
        header("Location: kegiatan_renungan.php");
        exit();
    }
    $ren = $renungan_res->fetch_assoc();
    
    // Handle Comment Status Update
    if (isset($_GET['approve_comment'])) {
        $c_id = (int)$_GET['approve_comment'];
        $conn->query("UPDATE komentar_renungan SET status_moderasi='Disetujui' WHERE id=$c_id");
        $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Komentar berhasil disetujui.</div>";
        header("Location: kegiatan_renungan.php?manage_comments=" . $manage_comments_id);
        exit();
    }
    if (isset($_GET['reject_comment'])) {
        $c_id = (int)$_GET['reject_comment'];
        $conn->query("UPDATE komentar_renungan SET status_moderasi='Ditolak' WHERE id=$c_id");
        $_SESSION['admin_flash'] = "<div style='color: #b91c1c; background: #fee2e2; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fecaca;'><i class='fa-solid fa-circle-exclamation'></i> Komentar berhasil ditolak.</div>";
        header("Location: kegiatan_renungan.php?manage_comments=" . $manage_comments_id);
        exit();
    }
    if (isset($_GET['delete_comment'])) {
        $c_id = (int)$_GET['delete_comment'];
        $conn->query("DELETE FROM komentar_renungan WHERE id=$c_id");
        $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Komentar berhasil dihapus.</div>";
        header("Location: kegiatan_renungan.php?manage_comments=" . $manage_comments_id);
        exit();
    }
    
    // View Comments List for this Devotional
    ?>
    <div style="margin-bottom: 30px;">
        <a href="kegiatan_renungan.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; font-weight: 500; font-size: 0.9rem; margin-bottom: 10px; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Kelola Renungan
        </a>
        <h2>Moderasi Komentar</h2>
        <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 600; margin-top: 5px; color: var(--primary);">
            Renungan: <?= htmlspecialchars($ren['judul']) ?>
        </p>
    </div>
    
    <?= $message ?>
    
    <div style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden;">
        <h3 style="padding: 20px; background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); margin: 0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-comments" style="color: var(--primary);"></i> Daftar Komentar
        </h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); background: #f8fafc;">
                        <th style="padding: 15px 20px; width: 60px;">No</th>
                        <th style="padding: 15px 20px; width: 180px;">Komentator</th>
                        <th style="padding: 15px 20px;">Isi Komentar</th>
                        <th style="padding: 15px 20px; width: 140px; text-align: center;">Status</th>
                        <th style="padding: 15px 20px; width: 220px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $comments = $conn->query("SELECT * FROM komentar_renungan WHERE renungan_id = $manage_comments_id ORDER BY created_at DESC");
                    $no = 1;
                    while ($c_row = $comments->fetch_assoc()):
                    ?>
                    <tr style="border-bottom: 1px solid var(--border-color); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#fafafa'" onmouseout="this.style.backgroundColor='transparent'">
                        <td style="padding: 15px 20px; vertical-align: top;"><?= $no++ ?></td>
                        <td style="padding: 15px 20px; vertical-align: top;">
                            <strong style="color: var(--text-main); display: block;"><?= htmlspecialchars($c_row['nama_komentator']) ?></strong>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($c_row['created_at'])) ?></span>
                        </td>
                        <td style="padding: 15px 20px; vertical-align: top; max-width: 350px; overflow-wrap: break-word; white-space: normal; line-height: 1.5;">
                            <?= nl2br(htmlspecialchars($c_row['isi_komentar'])) ?>
                        </td>
                        <td style="padding: 15px 20px; vertical-align: top; text-align: center;">
                            <?php
                            $badge_bg = ''; $badge_fg = '';
                            if ($c_row['status_moderasi'] == 'Disetujui') {
                                $badge_bg = '#dcfce7'; $badge_fg = '#15803d';
                            } elseif ($c_row['status_moderasi'] == 'Menunggu') {
                                $badge_bg = '#fef3c7'; $badge_fg = '#d97706';
                            } else {
                                $badge_bg = '#fee2e2'; $badge_fg = '#b91c1c';
                            }
                            ?>
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 9999px; font-weight: 600; font-size: 0.8rem; background: <?= $badge_bg ?>; color: <?= $badge_fg ?>;">
                                <?= $c_row['status_moderasi'] ?>
                            </span>
                        </td>
                        <td style="padding: 15px 20px; vertical-align: top; text-align: center; white-space: nowrap;">
                            <?php if ($c_row['status_moderasi'] != 'Disetujui'): ?>
                                <a href="kegiatan_renungan.php?manage_comments=<?= $manage_comments_id ?>&approve_comment=<?= $c_row['id'] ?>" class="btn-primary" style="padding: 6px 12px; font-size: 0.75rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; background: #16a34a; margin-right: 5px; text-decoration: none;"><i class="fa-solid fa-circle-check"></i> Setujui</a>
                            <?php endif; ?>
                            <?php if ($c_row['status_moderasi'] != 'Ditolak'): ?>
                                <a href="kegiatan_renungan.php?manage_comments=<?= $manage_comments_id ?>&reject_comment=<?= $c_row['id'] ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.75rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; color: #dc2626; background: #fee2e2; margin-right: 5px; text-decoration: none;"><i class="fa-solid fa-circle-xmark"></i> Tolak</a>
                            <?php endif; ?>
                            <a href="kegiatan_renungan.php?manage_comments=<?= $manage_comments_id ?>&delete_comment=<?= $c_row['id'] ?>" onclick="return confirm('Hapus komentar ini?');" style="color: #b91c1c; padding: 6px 10px; display: inline-block;" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($comments->num_rows == 0): ?>
                    <tr>
                        <td colspan="5" style="padding: 30px; text-align: center; color: var(--text-muted);">
                            <i class="fa-regular fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 10px; color: #cbd5e1;"></i>
                            Belum ada komentar untuk renungan ini.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    require_once '../includes/admin_footer.php';
    exit();
}

// ----------------------------------------------------
// Section 2: Devotional (Renungan) Add/Edit/Delete Logic
// ----------------------------------------------------

// Handle Delete Renungan
if (isset($_GET['delete_renungan'])) {
    $id = (int)$_GET['delete_renungan'];
    
    // Delete supporting image if exists
    $res = $conn->query("SELECT gambar FROM renungan WHERE id=$id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (!empty($row['gambar'])) {
            $file_path = '../assets/img/renungan/' . $row['gambar'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    
    $conn->query("DELETE FROM renungan WHERE id=$id");
    $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Renungan berhasil dihapus.</div>";
    header("Location: kegiatan_renungan.php");
    exit();
}

// Handle Add / Edit Renungan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_renungan'])) {
    $judul = $conn->real_escape_string($_POST['judul']);
    $ayat_alkitab = $conn->real_escape_string($_POST['ayat_alkitab']);
    $isi_renungan = $conn->real_escape_string($_POST['isi_renungan']);
    $penulis = $conn->real_escape_string($_POST['penulis']);
    $tanggal_posting = $conn->real_escape_string($_POST['tanggal_posting']);
    
    $error_renungan = "";
    $gambar_filename = NULL;
    $has_new_image = isset($_FILES['gambar']) && $_FILES['gambar']['error'] != UPLOAD_ERR_NO_FILE;
    
    if ($has_new_image) {
        $file = $_FILES['gambar'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];
        
        if ($file_error !== UPLOAD_ERR_OK) {
            $error_renungan = "Gagal mengunggah gambar. Kode error: $file_error";
        } else {
            $allowed_exts = ['jpg', 'jpeg', 'png'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_exts)) {
                $error_renungan = "Format gambar tidak didukung. Harap unggah file JPG, JPEG, atau PNG.";
            } elseif ($file_size > 5 * 1024 * 1024) {
                $error_renungan = "Ukuran gambar terlalu besar. Maksimal 5MB.";
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_tmp);
                finfo_close($finfo);
                $allowed_mimes = ['image/jpeg', 'image/png'];
                if (!in_array($mime_type, $allowed_mimes)) {
                    $error_renungan = "Tipe file tidak valid. Harap unggah gambar JPG/JPEG atau PNG yang valid.";
                }
            }
            
            if (empty($error_renungan)) {
                $target_dir = '../assets/img/renungan/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                
                $new_filename = 'renungan_' . time() . '_' . rand(100, 999) . '.' . $file_ext;
                $target_path = $target_dir . $new_filename;
                
                if (move_uploaded_file($file_tmp, $target_path)) {
                    $gambar_filename = $new_filename;
                } else {
                    $error_renungan = "Gagal menyimpan file gambar.";
                }
            }
        }
    }
    
    if (!empty($error_renungan)) {
        $_SESSION['admin_flash'] = "<div style='color: #b91c1c; background: #fee2e2; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fecaca;'><i class='fa-solid fa-circle-xmark'></i> " . $error_renungan . "</div>";
        header("Location: kegiatan_renungan.php");
        exit();
    }
    
    if (isset($_POST['id_renungan']) && $_POST['id_renungan'] != '') {
        $id = (int)$_POST['id_renungan'];
        
        // Get existing image
        $old_img_res = $conn->query("SELECT gambar FROM renungan WHERE id=$id");
        $old_img = ($old_img_res && $old_img_res->num_rows > 0) ? $old_img_res->fetch_assoc()['gambar'] : NULL;
        
        $hapus_gambar = isset($_POST['hapus_gambar']) ? 1 : 0;
        
        if ($has_new_image) {
            // Delete old file
            if (!empty($old_img)) {
                $old_file_path = '../assets/img/renungan/' . $old_img;
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            $sql = "UPDATE renungan SET judul='$judul', ayat_alkitab='$ayat_alkitab', isi_renungan='$isi_renungan', penulis='$penulis', tanggal_posting='$tanggal_posting', gambar='$gambar_filename' WHERE id=$id";
        } elseif ($hapus_gambar) {
            // Delete old file
            if (!empty($old_img)) {
                $old_file_path = '../assets/img/renungan/' . $old_img;
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            $sql = "UPDATE renungan SET judul='$judul', ayat_alkitab='$ayat_alkitab', isi_renungan='$isi_renungan', penulis='$penulis', tanggal_posting='$tanggal_posting', gambar=NULL WHERE id=$id";
        } else {
            $sql = "UPDATE renungan SET judul='$judul', ayat_alkitab='$ayat_alkitab', isi_renungan='$isi_renungan', penulis='$penulis', tanggal_posting='$tanggal_posting' WHERE id=$id";
        }
        
        if ($conn->query($sql)) {
            $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Renungan berhasil diperbarui.</div>";
        }
    } else {
        $gambar_val = $gambar_filename ? "'$gambar_filename'" : "NULL";
        $sql = "INSERT INTO renungan (judul, ayat_alkitab, isi_renungan, penulis, tanggal_posting, gambar) VALUES ('$judul', '$ayat_alkitab', '$isi_renungan', '$penulis', '$tanggal_posting', $gambar_val)";
        if ($conn->query($sql)) {
            $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Renungan berhasil ditambahkan.</div>";
        }
    }
    header("Location: kegiatan_renungan.php");
    exit();
}

// Edit Mode Check
$edit_mode = false;
$edit_data = [
    'id' => '', 'judul' => '', 'ayat_alkitab' => '', 'isi_renungan' => '', 'penulis' => '', 'tanggal_posting' => '', 'gambar' => NULL
];
if (isset($_GET['edit_renungan'])) {
    $id = (int)$_GET['edit_renungan'];
    $res = $conn->query("SELECT * FROM renungan WHERE id=$id");
    if ($res && $res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
        $edit_data['tanggal_posting'] = date('Y-m-d\TH:i', strtotime($edit_data['tanggal_posting']));
    }
}
?>

<div style="margin-bottom: 30px;">
    <h2>Kelola Renungan</h2>
    <p style="color: var(--text-muted);">Kelola ayat Alkitab, renungan rohani mingguan, dan komentar dari jemaat.</p>
</div>

<?= $message ?>

<div style="display: grid; grid-template-columns: 1fr; gap: 30px; margin-bottom: 40px;">
    <!-- Form Renungan -->
    <div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;"><i class="fa-solid fa-book-open-reader" style="color: var(--primary);"></i> <?= $edit_mode ? 'Ubah Renungan' : 'Tambah Renungan Baru' ?></h3>
        
        <form action="kegiatan_renungan.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_renungan" value="<?= $edit_data['id'] ?>">
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Judul Renungan</label>
                    <input type="text" name="judul" value="<?= htmlspecialchars($edit_data['judul']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Ayat Alkitab (cth: Yohanes 3:16)</label>
                    <input type="text" name="ayat_alkitab" value="<?= htmlspecialchars($edit_data['ayat_alkitab']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Penulis / Pelayan</label>
                    <input type="text" name="penulis" value="<?= htmlspecialchars($edit_data['penulis']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Waktu Posting</label>
                    <input type="datetime-local" name="tanggal_posting" value="<?= !empty($edit_data['tanggal_posting']) ? $edit_data['tanggal_posting'] : date('Y-m-d\TH:i') ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Isi Renungan</label>
                <textarea name="isi_renungan" rows="12" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: var(--font-body); line-height: 1.6;"><?= htmlspecialchars($edit_data['isi_renungan']) ?></textarea>
            </div>
            
            <div style="margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; align-items: flex-start;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Gambar Pendukung <span style="font-weight: 400; color: var(--text-muted); font-size: 0.8rem;">(Opsional)</span></label>
                    <input type="file" name="gambar" id="gambar_renungan" accept="image/jpeg,image/png" onchange="previewRenunganImage(event)" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 6px; outline: none; background: white; font-size: 0.9rem; cursor: pointer;">
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Mendukung JPG, JPEG, PNG. Maksimal ukuran file 5MB.</p>
                </div>
                
                <div>
                    <!-- Client-side preview -->
                    <div id="preview_container" style="display: none; border: 1px dashed var(--border-color); padding: 10px; border-radius: 6px; text-align: center; background: #f8fafc;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-bottom: 6px;">Pratinjau Gambar Baru:</span>
                        <img id="image_preview" src="" alt="Pratinjau" style="max-height: 120px; border-radius: 6px; box-shadow: var(--shadow-sm); max-width: 100%; object-fit: contain;">
                    </div>
                    
                    <?php if ($edit_mode && !empty($edit_data['gambar'])): ?>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; display: flex; align-items: center; gap: 15px;">
                        <div style="width: 50px; height: 50px; border-radius: 6px; overflow: hidden; border: 1px solid var(--border-color);">
                            <img src="../assets/img/renungan/<?= htmlspecialchars($edit_data['gambar']) ?>" alt="Gambar Aktif" style="width: 100%; height: 100%; object-fit: cover;">
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
                </div>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="submit_renungan" class="btn-primary"><?= $edit_mode ? 'Simpan Perubahan' : 'Bagikan Renungan' ?></button>
                <?php if($edit_mode): ?>
                    <a href="kegiatan_renungan.php" style="padding: 10px 20px; background: #e2e8f0; color: #334155; border-radius: 6px; font-weight: 500; text-decoration: none;">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- List Renungan -->
<div style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden;">
    <h3 style="padding: 20px; background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); margin: 0; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-list" style="color: var(--primary);"></i> Daftar Renungan
    </h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); background: #f8fafc;">
                    <th style="padding: 15px 20px; width: 80px;">Gambar</th>
                    <th style="padding: 15px 20px;">Judul & Ayat</th>
                    <th style="padding: 15px 20px; width: 180px;">Penulis & Tanggal</th>
                    <th style="padding: 15px 20px; width: 140px; text-align: center;">Komentar</th>
                    <th style="padding: 15px 20px; width: 160px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $renungans = $conn->query("SELECT r.*, 
                    (SELECT COUNT(*) FROM komentar_renungan WHERE renungan_id = r.id) as total_komentar,
                    (SELECT COUNT(*) FROM komentar_renungan WHERE renungan_id = r.id AND status_moderasi = 'Menunggu') as komentar_pending
                    FROM renungan r ORDER BY tanggal_posting DESC");
                while ($row = $renungans->fetch_assoc()):
                ?>
                <tr style="border-bottom: 1px solid var(--border-color); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#fafafa'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 15px 20px; vertical-align: middle;">
                        <?php if (!empty($row['gambar'])): ?>
                            <div style="width: 50px; height: 50px; border-radius: 4px; overflow: hidden; border: 1px solid var(--border-color);">
                                <img src="../assets/img/renungan/<?= htmlspecialchars($row['gambar']) ?>" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div style="width: 50px; height: 50px; border-radius: 4px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8;" title="Tidak ada gambar">
                                <i class="fa-solid fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px 20px; vertical-align: middle;">
                        <strong style="color: var(--text-main); display: block; font-size: 1.05rem;"><?= htmlspecialchars($row['judul']) ?></strong>
                        <span style="font-size: 0.85rem; color: var(--primary); font-weight: 600;"><i class="fa-solid fa-quote-left" style="font-size: 0.7rem; margin-right: 4px;"></i><?= htmlspecialchars($row['ayat_alkitab']) ?></span>
                    </td>
                    <td style="padding: 15px 20px; vertical-align: middle; color: var(--text-muted); font-size: 0.9rem;">
                        <div style="font-weight: 500; color: var(--text-main); margin-bottom: 2px;"><i class="fa-solid fa-user-pen" style="margin-right: 4px;"></i><?= htmlspecialchars($row['penulis']) ?></div>
                        <div><i class="fa-regular fa-clock" style="margin-right: 4px;"></i><?= date('d M Y, H:i', strtotime($row['tanggal_posting'])) ?></div>
                    </td>
                    <td style="padding: 15px 20px; vertical-align: middle; text-align: center;">
                        <a href="kegiatan_renungan.php?manage_comments=<?= $row['id'] ?>" style="font-weight: 600; font-size: 0.9rem; color: var(--primary); text-decoration: underline;" title="Kelola Komentar">
                            <?= $row['total_komentar'] ?> Komentar
                        </a>
                        <?php if ($row['komentar_pending'] > 0): ?>
                            <div style="margin-top: 4px;"><span style="background: #fef3c7; color: #d97706; font-size: 0.75rem; padding: 1px 6px; border-radius: 4px; font-weight: bold;">(<?= $row['komentar_pending'] ?> Pending)</span></div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px 20px; vertical-align: middle; text-align: center; white-space: nowrap;">
                        <a href="kegiatan_renungan.php?edit_renungan=<?= $row['id'] ?>" style="color: var(--accent); margin-right: 20px;" title="Ubah"><i class="fa-solid fa-pen-to-square"></i></a>
                        <a href="kegiatan_renungan.php?delete_renungan=<?= $row['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus renungan ini secara permanen?');" style="color: #b91c1c;" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($renungans->num_rows == 0): ?>
                <tr>
                    <td colspan="5" style="padding: 30px; text-align: center; color: var(--text-muted);">
                        Belum ada renungan rohani yang diposting.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function previewRenunganImage(event) {
    const input = event.target;
    const previewContainer = document.getElementById('preview_container');
    const previewImage = document.getElementById('image_preview');
    
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
</script>

<?php require_once '../includes/admin_footer.php'; ?>
