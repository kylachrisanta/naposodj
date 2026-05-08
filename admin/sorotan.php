<?php
require_once '../includes/admin_header.php';
require_once '../includes/admin_sidebar.php';

$message = "";
if (isset($_SESSION['admin_flash'])) {
    $message = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
}
$upload_dir = '../assets/img/sorotan/';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Get file name first
    $res = $conn->query("SELECT file_media FROM sorotan WHERE id=$id");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $file_path = $upload_dir . $row['file_media'];
        if(file_exists($file_path) && is_file($file_path)) {
            unlink($file_path);
        }
        $conn->query("DELETE FROM sorotan WHERE id=$id");
        $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Sorotan berhasil dihapus beserta filenya.</div>";
        header("Location: sorotan.php");
        exit();
    }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $conn->real_escape_string($_POST['judul']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $tahun = (int)$_POST['tahun'];
    $tanggal_kegiatan = $conn->real_escape_string($_POST['tanggal_kegiatan']);
    $tipe_media = $conn->real_escape_string($_POST['tipe_media']);
    $divisi = $conn->real_escape_string($_POST['divisi']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    $file_media = "";
    $uploadOk = true;
    
    // Check if new file is uploaded
    if(isset($_FILES['file_media']) && $_FILES['file_media']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES["file_media"]["name"], PATHINFO_EXTENSION));
        $valid_extensions = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm'];
        
        if(!in_array($file_extension, $valid_extensions)) {
            $_SESSION['admin_flash'] = "<div style='color: #b91c1c; background: #fee2e2; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f87171;'>Format file tidak valid. Hanya JPG, PNG, WEBP, MP4, WEBM.</div>";
            $uploadOk = false;
        } else {
            // Check size (max 20MB)
            if ($_FILES["file_media"]["size"] > 20000000) {
                $_SESSION['admin_flash'] = "<div style='color: #b91c1c; background: #fee2e2; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f87171;'>Ukuran file terlalu besar (Maks 20MB).</div>";
                $uploadOk = false;
            } else {
                $file_media = time() . '_' . rand(100,999) . '.' . $file_extension;
                if(!move_uploaded_file($_FILES["file_media"]["tmp_name"], $upload_dir . $file_media)) {
                    $_SESSION['admin_flash'] = "<div style='color: #b91c1c; background: #fee2e2; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f87171;'>Gagal mengunggah file. Pastikan folder assets/img/sorotan/ ada dan writable.</div>";
                    $uploadOk = false;
                }
            }
        }
    } else {
        if($id == 0) { // If adding new, file is required
            $_SESSION['admin_flash'] = "<div style='color: #b91c1c; background: #fee2e2; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f87171;'>File media wajib diunggah untuk data baru.</div>";
            $uploadOk = false;
        }
    }
    
    if($uploadOk) {
        if ($id > 0) {
            // Edit
            if($file_media != "") { // if new file uploaded, delete old file and update file_media
                $old_res = $conn->query("SELECT file_media FROM sorotan WHERE id=$id");
                $old_file = $old_res->fetch_assoc()['file_media'];
                if(file_exists($upload_dir . $old_file) && is_file($upload_dir . $old_file)) unlink($upload_dir . $old_file);
                
                $sql = "UPDATE sorotan SET judul='$judul', deskripsi='$deskripsi', tahun=$tahun, tanggal_kegiatan='$tanggal_kegiatan', tipe_media='$tipe_media', file_media='$file_media', divisi='$divisi' WHERE id=$id";
            } else {
                $sql = "UPDATE sorotan SET judul='$judul', deskripsi='$deskripsi', tahun=$tahun, tanggal_kegiatan='$tanggal_kegiatan', tipe_media='$tipe_media', divisi='$divisi' WHERE id=$id";
            }
            if($conn->query($sql)) $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Sorotan berhasil diperbarui.</div>";
        } else {
            // Add
            $sql = "INSERT INTO sorotan (judul, deskripsi, tahun, tanggal_kegiatan, tipe_media, file_media, divisi) VALUES ('$judul', '$deskripsi', $tahun, '$tanggal_kegiatan', '$tipe_media', '$file_media', '$divisi')";
            if($conn->query($sql)) $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Sorotan berhasil ditambahkan.</div>";
        }
    }
    header("Location: sorotan.php");
    exit();
}

// Check Edit Mode
$edit_mode = false;
$edit_data = ['id' => '', 'judul' => '', 'deskripsi' => '', 'tahun' => date('Y'), 'tanggal_kegiatan' => date('Y-m-d'), 'tipe_media' => 'foto', 'file_media' => '', 'divisi' => ''];
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM sorotan WHERE id=$id");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}
?>

<div style="margin-bottom: 30px;">
    <h2>Kelola Sorotan</h2>
    <p style="color: var(--text-muted);">Unggah momen kebersamaan dan kegiatan besar tahunan Naposo.</p>
</div>

<?= $message ?>

<!-- Form Section -->
<div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); margin-bottom: 30px;">
    <h3 style="margin-bottom: 20px;"><?= $edit_mode ? 'Ubah Sorotan' : 'Tambah Sorotan Baru' ?></h3>
    <form action="sorotan.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Judul Sorotan</label>
                <input type="text" name="judul" value="<?= htmlspecialchars($edit_data['judul']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Tahun Kegiatan</label>
                <input type="number" name="tahun" value="<?= $edit_data['tahun'] ?>" required min="2000" max="<?= date('Y')+1 ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Tanggal Kegiatan</label>
                <input type="date" name="tanggal_kegiatan" value="<?= $edit_data['tanggal_kegiatan'] ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Deskripsi</label>
            <textarea name="deskripsi" rows="3" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: var(--font-body);"><?= htmlspecialchars($edit_data['deskripsi']) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px; align-items: start;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Tipe Media</label>
                <select name="tipe_media" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: var(--font-body);">
                    <option value="foto" <?= $edit_data['tipe_media'] == 'foto' ? 'selected' : '' ?>>Foto (Gambar)</option>
                    <option value="video" <?= $edit_data['tipe_media'] == 'video' ? 'selected' : '' ?>>Video</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Link ke Divisi (Opsional)</label>
                <select name="divisi" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: var(--font-body);">
                    <option value="">-- Bukan Program Divisi --</option>
                    <option value="Rohani" <?= $edit_data['divisi'] == 'Rohani' ? 'selected' : '' ?>>Rohani</option>
                    <option value="Padus & Musik" <?= $edit_data['divisi'] == 'Padus & Musik' ? 'selected' : '' ?>>Padus & Musik</option>
                    <option value="Humas" <?= $edit_data['divisi'] == 'Humas' ? 'selected' : '' ?>>Humas</option>
                    <option value="Olahraga & Seni" <?= $edit_data['divisi'] == 'Olahraga & Seni' ? 'selected' : '' ?>>Olahraga & Seni</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Unggah File <?= $edit_mode ? '(Biarkan kosong jika tak ingin ubah)' : '' ?></label>
                <input type="file" name="file_media" accept="image/*,video/*" <?= $edit_mode ? '' : 'required' ?> style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-subtle);">
                <small style="color: var(--text-muted); display: block; margin-top: 5px;">Format: JPG, PNG, WEBP, MP4 (Maks 20MB)</small>
                <?php if($edit_mode && $edit_data['file_media'] != ''): ?>
                    <div style="margin-top: 10px; font-size: 0.9rem; color: var(--primary);">File saat ini: <?= htmlspecialchars($edit_data['file_media']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary"><?= $edit_mode ? 'Simpan Perubahan' : 'Unggah Sorotan' ?></button>
            <?php if($edit_mode): ?>
                <a href="sorotan.php" style="padding: 10px 20px; background: #e2e8f0; color: #334155; border-radius: 6px; font-weight: 500;">Batal Edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Table Section -->
<div style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
                <th style="padding: 15px 20px; width: 5%;">No</th>
                <th style="padding: 15px 20px; width: 15%;">Preview</th>
                <th style="padding: 15px 20px; width: 25%;">Judul</th>
                <th style="padding: 15px 20px; width: 30%;">Deskripsi</th>
                <th style="padding: 15px 20px; width: 15%;">Tipe & Tahun</th>
                <th style="padding: 15px 20px; width: 10%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM sorotan ORDER BY tahun DESC, created_at DESC");
            $no = 1;
            while($row = $result->fetch_assoc()):
            ?>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 15px 20px; color: var(--text-muted);"><?= $no++ ?></td>
                <td style="padding: 15px 20px;">
                    <?php if($row['tipe_media'] == 'foto'): ?>
                        <img src="../assets/img/sorotan/<?= htmlspecialchars($row['file_media']) ?>" alt="preview" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                    <?php else: ?>
                        <div style="width: 80px; height: 60px; background: #334155; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white;"><i class="fa-solid fa-play"></i></div>
                    <?php endif; ?>
                </td>
                <td style="padding: 15px 20px; font-weight: 500;"><?= htmlspecialchars($row['judul']) ?></td>
                <td style="padding: 15px 20px; color: var(--text-muted); font-size: 0.9rem;">
                    <?= mb_strimwidth(htmlspecialchars($row['deskripsi']), 0, 50, "...") ?>
                </td>
                <td style="padding: 15px 20px;">
                    <div style="font-weight: 600; color: var(--text-main);"><?= $row['tahun'] ?></div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);"><?= date('d/m/Y', strtotime($row['tanggal_kegiatan'])) ?></div>
                    <?php if($row['divisi']): ?>
                        <div style="font-size: 0.75rem; color: var(--primary); font-weight: 700;"><?= $row['divisi'] ?></div>
                    <?php endif; ?>
                    <div style="font-size: 0.8rem; color: <?= $row['tipe_media'] == 'foto' ? 'var(--primary)' : 'var(--accent)' ?>; text-transform: uppercase;"><i class="fa-solid <?= $row['tipe_media'] == 'foto' ? 'fa-camera' : 'fa-video' ?>"></i> <?= $row['tipe_media'] ?></div>
                </td>
                <td style="padding: 15px 20px;">
                    <a href="sorotan.php?edit=<?= $row['id'] ?>" style="color: var(--accent); margin-right: 15px;" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    <a href="sorotan.php?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus media ini? File juga akan terhapus permanen.');" style="color: #b91c1c;" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if($result->num_rows == 0): ?>
            <tr>
                <td colspan="6" style="padding: 20px; text-align: center; color: var(--text-muted);">Belum ada data sorotan.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
