<?php
require_once '../includes/admin_header.php';
require_once '../includes/admin_sidebar.php';

$message = "";
if (isset($_SESSION['admin_flash'])) {
    $message = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
}

// Handle Delete
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = (int)$_GET['delete'];
    $type = $_GET['type'];
    $table = ($type == 'sorotan') ? 'sorotan' : 'jejak';
    $upload_dir = ($type == 'sorotan') ? '../assets/img/sorotan/' : '../assets/img/jejak/';
    
    $res = $conn->query("SELECT file_media FROM $table WHERE id=$id");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $file_path = $upload_dir . $row['file_media'];
        if(file_exists($file_path) && is_file($file_path)) {
            unlink($file_path);
        }
        $conn->query("DELETE FROM $table WHERE id=$id");
        $_SESSION['admin_flash'] = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Data " . ucfirst($type) . " berhasil dihapus beserta filenya.</div>";
        header("Location: dokumentasi.php?tab=$type");
        exit();
    }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['type'])) {
    $type = $_POST['type'];
    $table = ($type == 'sorotan') ? 'sorotan' : 'jejak';
    $upload_dir = ($type == 'sorotan') ? '../assets/img/sorotan/' : '../assets/img/jejak/';
    
    $judul = $conn->real_escape_string($_POST['judul']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $tahun = (int)$_POST['tahun'];
    $tipe_media = $conn->real_escape_string($_POST['tipe_media']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Fields specific to type
    $tanggal_kegiatan = isset($_POST['tanggal_kegiatan']) ? $conn->real_escape_string($_POST['tanggal_kegiatan']) : date('Y-m-d');
    $divisi = isset($_POST['divisi']) ? $conn->real_escape_string($_POST['divisi']) : '';
    $kategori = isset($_POST['kategori']) ? $conn->real_escape_string($_POST['kategori']) : 'Partisipasi';

    $file_media = "";
    $uploadOk = true;
    
    if(isset($_FILES['file_media']) && $_FILES['file_media']['error'] == 0) {
        $file_extension = strtolower(pathinfo($_FILES["file_media"]["name"], PATHINFO_EXTENSION));
        $valid_extensions = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm'];
        
        if(!in_array($file_extension, $valid_extensions)) {
            $_SESSION['admin_flash'] = "<div class='alert alert-danger'>Format file tidak valid.</div>";
            $uploadOk = false;
        } else if ($_FILES["file_media"]["size"] > 20000000) {
            $_SESSION['admin_flash'] = "<div class='alert alert-danger'>Ukuran file terlalu besar (Maks 20MB).</div>";
            $uploadOk = false;
        } else {
            $file_media = time() . '_' . rand(100,999) . '.' . $file_extension;
            if(!move_uploaded_file($_FILES["file_media"]["tmp_name"], $upload_dir . $file_media)) {
                $_SESSION['admin_flash'] = "<div class='alert alert-danger'>Gagal mengunggah file.</div>";
                $uploadOk = false;
            }
        }
    } else if($id == 0) {
        $_SESSION['admin_flash'] = "<div class='alert alert-danger'>File media wajib diunggah.</div>";
        $uploadOk = false;
    }
    
    if($uploadOk) {
        if ($id > 0) {
            // Edit logic
            if($file_media != "") {
                $old_res = $conn->query("SELECT file_media FROM $table WHERE id=$id");
                $old_file = $old_res->fetch_assoc()['file_media'];
                if(file_exists($upload_dir . $old_file) && is_file($upload_dir . $old_file)) unlink($upload_dir . $old_file);
                
                if($type == 'sorotan') {
                    $sql = "UPDATE sorotan SET judul='$judul', deskripsi='$deskripsi', tahun=$tahun, tanggal_kegiatan='$tanggal_kegiatan', tipe_media='$tipe_media', file_media='$file_media', divisi='$divisi' WHERE id=$id";
                } else {
                    $sql = "UPDATE jejak SET judul='$judul', deskripsi='$deskripsi', tahun=$tahun, kategori='$kategori', tipe_media='$tipe_media', file_media='$file_media' WHERE id=$id";
                }
            } else {
                if($type == 'sorotan') {
                    $sql = "UPDATE sorotan SET judul='$judul', deskripsi='$deskripsi', tahun=$tahun, tanggal_kegiatan='$tanggal_kegiatan', tipe_media='$tipe_media', divisi='$divisi' WHERE id=$id";
                } else {
                    $sql = "UPDATE jejak SET judul='$judul', deskripsi='$deskripsi', tahun=$tahun, kategori='$kategori', tipe_media='$tipe_media' WHERE id=$id";
                }
            }
            if($conn->query($sql)) $_SESSION['admin_flash'] = "<div class='alert alert-success'>Data " . ucfirst($type) . " berhasil diperbarui.</div>";
        } else {
            // Add logic
            if($type == 'sorotan') {
                $sql = "INSERT INTO sorotan (judul, deskripsi, tahun, tanggal_kegiatan, tipe_media, file_media, divisi) VALUES ('$judul', '$deskripsi', $tahun, '$tanggal_kegiatan', '$tipe_media', '$file_media', '$divisi')";
            } else {
                $sql = "INSERT INTO jejak (kategori, judul, deskripsi, tahun, tipe_media, file_media) VALUES ('$kategori', '$judul', '$deskripsi', $tahun, '$tipe_media', '$file_media')";
            }
            if($conn->query($sql)) $_SESSION['admin_flash'] = "<div class='alert alert-success'>Data " . ucfirst($type) . " berhasil ditambahkan.</div>";
        }
    }
    header("Location: dokumentasi.php?tab=$type");
    exit();
}

// Tab handling
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'sorotan';
$edit_mode = false;
$edit_data = [];

if (isset($_GET['edit']) && isset($_GET['type'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_type = $_GET['type'];
    $table = ($edit_type == 'sorotan') ? 'sorotan' : 'jejak';
    $res = $conn->query("SELECT * FROM $table WHERE id=$edit_id");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
        $active_tab = $edit_type;
    }
}
?>

<div style="margin-bottom: 30px;">
    <h2>Kelola Dokumentasi</h2>
    <p style="color: var(--text-muted);">Kelola Sorotan dan Rekam Jejak (Prestasi/Partisipasi) Naposo dalam satu tempat.</p>
</div>

<!-- Tab Navigation -->
<div style="display: flex; gap: 10px; margin-bottom: 25px; border-bottom: 2px solid var(--border-color);">
    <a href="dokumentasi.php?tab=sorotan" style="padding: 12px 25px; font-weight: 600; color: <?= $active_tab == 'sorotan' ? 'var(--primary)' : 'var(--text-muted)' ?>; border-bottom: 3px solid <?= $active_tab == 'sorotan' ? 'var(--primary)' : 'transparent' ?>; margin-bottom: -2px; transition: 0.3s;">
        <i class="fa-solid fa-camera-retro"></i> Sorotan Kegiatan
    </a>
    <a href="dokumentasi.php?tab=jejak" style="padding: 12px 25px; font-weight: 600; color: <?= $active_tab == 'jejak' ? 'var(--primary)' : 'var(--text-muted)' ?>; border-bottom: 3px solid <?= $active_tab == 'jejak' ? 'var(--primary)' : 'transparent' ?>; margin-bottom: -2px; transition: 0.3s;">
        <i class="fa-solid fa-trophy"></i> Jejak (Prestasi & Partisipasi)
    </a>
</div>

<?= $message ?>

<!-- Form Section -->
<div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); margin-bottom: 40px;">
    <h3 style="margin-bottom: 20px;"><?= $edit_mode ? 'Ubah Data' : 'Tambah Data Baru' ?> (<?= ucfirst($active_tab) ?>)</h3>
    <form action="dokumentasi.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="type" value="<?= $active_tab ?>">
        <input type="hidden" name="id" value="<?= $edit_mode ? $edit_data['id'] : '' ?>">
        
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label>Judul Dokumentasi</label>
                <input type="text" name="judul" value="<?= $edit_mode ? htmlspecialchars($edit_data['judul']) : '' ?>" required class="form-control">
            </div>
            <div>
                <label>Tahun</label>
                <input type="number" name="tahun" value="<?= $edit_mode ? $edit_data['tahun'] : date('Y') ?>" required min="2000" max="<?= date('Y')+1 ?>" class="form-control">
            </div>
            
            <?php if($active_tab == 'sorotan'): ?>
            <div>
                <label>Tanggal Kegiatan</label>
                <input type="date" name="tanggal_kegiatan" value="<?= $edit_mode ? $edit_data['tanggal_kegiatan'] : date('Y-m-d') ?>" required class="form-control">
            </div>
            <?php else: ?>
            <div>
                <label>Kategori Jejak</label>
                <select name="kategori" class="form-control">
                    <option value="Prestasi" <?= ($edit_mode && $edit_data['kategori'] == 'Prestasi') ? 'selected' : '' ?>>Prestasi</option>
                    <option value="Partisipasi" <?= ($edit_mode && $edit_data['kategori'] == 'Partisipasi') ? 'selected' : '' ?>>Partisipasi</option>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 20px;">
            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="3" required class="form-control"><?= $edit_mode ? htmlspecialchars($edit_data['deskripsi']) : '' ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px; align-items: start;">
            <div>
                <label>Tipe Media</label>
                <select name="tipe_media" class="form-control">
                    <option value="foto" <?= ($edit_mode && $edit_data['tipe_media'] == 'foto') ? 'selected' : '' ?>>Foto (Gambar)</option>
                    <option value="video" <?= ($edit_mode && $edit_data['tipe_media'] == 'video') ? 'selected' : '' ?>>Video</option>
                </select>
            </div>
            
            <?php if($active_tab == 'sorotan'): ?>
            <div>
                <label>Link Divisi (Opsional)</label>
                <select name="divisi" class="form-control">
                    <option value="">-- Bukan Program Divisi --</option>
                    <?php 
                    $divs = ['Rohani', 'Padus & Musik', 'Humas', 'Olahraga & Seni'];
                    foreach($divs as $d) {
                        $sel = ($edit_mode && $edit_data['divisi'] == $d) ? 'selected' : '';
                        echo "<option value='$d' $sel>$d</option>";
                    }
                    ?>
                </select>
            </div>
            <?php endif; ?>

            <div>
                <label>Upload File <?= $edit_mode ? '(Opsional)' : '' ?></label>
                <input type="file" name="file_media" accept="image/*,video/*" <?= $edit_mode ? '' : 'required' ?> class="form-control" style="padding: 7px;">
                <?php if($edit_mode): ?>
                    <small class="text-primary">File: <?= $edit_data['file_media'] ?></small>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary"><?= $edit_mode ? 'Simpan Perubahan' : 'Tambah Data' ?></button>
            <?php if($edit_mode): ?>
                <a href="dokumentasi.php?tab=<?= $active_tab ?>" class="btn-secondary">Batal Edit</a>
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
                <th style="padding: 15px 20px; width: 30%;">Judul</th>
                <th style="padding: 15px 20px; width: 15%;"><?= $active_tab == 'sorotan' ? 'Tanggal' : 'Kategori' ?></th>
                <th style="padding: 15px 20px; width: 15%;">Tipe & Tahun</th>
                <th style="padding: 15px 20px; width: 10%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $table = ($active_tab == 'sorotan') ? 'sorotan' : 'jejak';
            $order = ($active_tab == 'sorotan') ? 'tahun DESC, tanggal_kegiatan DESC' : 'kategori ASC, tahun DESC';
            $result = $conn->query("SELECT * FROM $table ORDER BY $order");
            $no = 1;
            while($row = $result->fetch_assoc()):
                $img_path = ($active_tab == 'sorotan') ? '../assets/img/sorotan/' : '../assets/img/jejak/';
            ?>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 15px 20px; color: var(--text-muted);"><?= $no++ ?></td>
                <td style="padding: 15px 20px;">
                    <?php if($row['tipe_media'] == 'foto'): ?>
                        <img src="<?= $img_path . htmlspecialchars($row['file_media']) ?>" alt="preview" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                    <?php else: ?>
                        <div style="width: 80px; height: 60px; background: #334155; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white;"><i class="fa-solid fa-play"></i></div>
                    <?php endif; ?>
                </td>
                <td style="padding: 15px 20px;">
                    <div style="font-weight: 600;"><?= htmlspecialchars($row['judul']) ?></div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);"><?= mb_strimwidth(htmlspecialchars($row['deskripsi']), 0, 50, "...") ?></div>
                </td>
                <td style="padding: 15px 20px;">
                    <?php if($active_tab == 'sorotan'): ?>
                        <?= date('d/m/Y', strtotime($row['tanggal_kegiatan'])) ?>
                        <?php if($row['divisi']): ?>
                            <div class="badge" style="background: #e0e7ff; color: #4338ca; margin-top: 5px; display: inline-block;"><?= $row['divisi'] ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge" style="background: <?= $row['kategori'] == 'Prestasi' ? '#fef08a' : '#bfdbfe' ?>; color: <?= $row['kategori'] == 'Prestasi' ? '#854d0e' : '#1e40af' ?>;">
                            <?= $row['kategori'] ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td style="padding: 15px 20px;">
                    <div style="font-weight: 600;"><?= $row['tahun'] ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;"><i class="fa-solid <?= $row['tipe_media'] == 'foto' ? 'fa-camera' : 'fa-video' ?>"></i> <?= $row['tipe_media'] ?></div>
                </td>
                <td style="padding: 15px 20px;">
                    <a href="dokumentasi.php?edit=<?= $row['id'] ?>&type=<?= $active_tab ?>" style="color: var(--primary); margin-right: 15px;" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    <a href="dokumentasi.php?delete=<?= $row['id'] ?>&type=<?= $active_tab ?>" onclick="return confirm('Yakin ingin menghapus data ini?');" style="color: #ef4444;" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if($result->num_rows == 0): ?>
            <tr>
                <td colspan="6" style="padding: 40px; text-align: center; color: var(--text-muted);">Belum ada data dokumentasi.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
