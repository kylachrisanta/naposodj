<?php
require_once '../includes/admin_header.php';
require_once '../includes/admin_sidebar.php';

$message = "";
$upload_dir = '../assets/img/pengurus/';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $conn->query("SELECT foto FROM pengurus WHERE id=$id");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $file_path = $upload_dir . $row['foto'];
        if($row['foto'] != '' && file_exists($file_path)) {
            unlink($file_path);
        }
        $conn->query("DELETE FROM pengurus WHERE id=$id");
        $message = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Data pengurus berhasil dihapus.</div>";
    }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $conn->real_escape_string($_POST['nama']);
    $jabatan = $conn->real_escape_string($_POST['jabatan']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $kategori = $conn->real_escape_string($_POST['kategori']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    $foto = "";
    $uploadOk = true;
    
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
        $valid_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if(!in_array($ext, $valid_ext)) {
            $message = "<div class='alert alert-danger'>Format foto tidak valid. Gunakan JPG/PNG/WEBP.</div>";
            $uploadOk = false;
        } else {
            $foto = time() . '_' . rand(100,999) . '.' . $ext;
            if(!move_uploaded_file($_FILES["foto"]["tmp_name"], $upload_dir . $foto)) {
                $message = "<div class='alert alert-danger'>Gagal mengunggah foto. Pastikan folder pengurus/ writable.</div>";
                $uploadOk = false;
            }
        }
    }

    if($uploadOk) {
        if ($id > 0) {
            // Edit
            if($foto != "") {
                $old_res = $conn->query("SELECT foto FROM pengurus WHERE id=$id");
                $old_row = $old_res->fetch_assoc();
                if($old_row['foto'] != '' && file_exists($upload_dir . $old_row['foto'])) {
                    unlink($upload_dir . $old_row['foto']);
                }
                $sql = "UPDATE pengurus SET nama='$nama', jabatan='$jabatan', deskripsi='$deskripsi', foto='$foto', kategori='$kategori' WHERE id=$id";
            } else {
                $sql = "UPDATE pengurus SET nama='$nama', jabatan='$jabatan', deskripsi='$deskripsi', kategori='$kategori' WHERE id=$id";
            }
            if($conn->query($sql)) {
                $message = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Data pengurus berhasil diperbarui.</div>";
            }
        } else {
            // Add
            $sql = "INSERT INTO pengurus (nama, jabatan, deskripsi, foto, kategori) VALUES ('$nama', '$jabatan', '$deskripsi', '$foto', '$kategori')";
            if($conn->query($sql)) {
                $message = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Data pengurus berhasil ditambahkan.</div>";
            }
        }
    }
}

// Check Edit Mode
$edit_mode = false;
$edit_data = ['id' => '', 'nama' => '', 'jabatan' => '', 'deskripsi' => '', 'kategori' => 'BPI', 'foto' => ''];
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM pengurus WHERE id=$id");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}
?>

<div style="margin-bottom: 30px;">
    <h2>Kelola Pengurus</h2>
    <p style="color: var(--text-muted);">Manajemen profil Pendeta, BPI, dan Ketua Divisi.</p>
</div>

<?= $message ?>

<!-- Form Section -->
<div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); margin-bottom: 30px;">
    <h3 style="margin-bottom: 20px;"><?= $edit_mode ? 'Ubah Data Pengurus' : 'Tambah Pengurus Baru' ?></h3>
    <form action="pengurus.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div>
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($edit_data['nama']) ?>" required class="form-control">
            </div>
            <div>
                <label>Jabatan</label>
                <input type="text" name="jabatan" value="<?= htmlspecialchars($edit_data['jabatan']) ?>" required class="form-control" placeholder="Contoh: Pendeta Resort / Ketua Naposo">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div>
                <label>Kategori</label>
                <select name="kategori" class="form-control">
                    <option value="Pendeta" <?= $edit_data['kategori'] == 'Pendeta' ? 'selected' : '' ?>>Pendeta</option>
                    <option value="BPI" <?= $edit_data['kategori'] == 'BPI' ? 'selected' : '' ?>>Badan Pengurus Inti (BPI)</option>
                    <option value="Divisi" <?= $edit_data['kategori'] == 'Divisi' ? 'selected' : '' ?>>Divisi / Program Kerja</option>
                </select>
            </div>
            <div>
                <label>Foto Profil <?= $edit_mode ? '(Kosongkan jika tak ingin ganti)' : '' ?></label>
                <input type="file" name="foto" class="form-control">
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label>Deskripsi Singkat / Visi</label>
            <textarea name="deskripsi" rows="3" class="form-control" placeholder="Tuliskan deskripsi singkat atau kutipan..."><?= htmlspecialchars($edit_data['deskripsi']) ?></textarea>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary"><?= $edit_mode ? 'Simpan Perubahan' : 'Tambah Pengurus' ?></button>
            <?php if($edit_mode): ?>
                <a href="pengurus.php" class="btn-secondary">Batal Edit</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Table Section -->
<div style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
                <th style="padding: 15px 20px; width: 10%;">Foto</th>
                <th style="padding: 15px 20px; width: 25%;">Nama</th>
                <th style="padding: 15px 20px; width: 20%;">Jabatan</th>
                <th style="padding: 15px 20px; width: 15%;">Kategori</th>
                <th style="padding: 15px 20px; width: 20%;">Deskripsi</th>
                <th style="padding: 15px 20px; width: 10%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM pengurus ORDER BY kategori DESC, id ASC");
            while($row = $result->fetch_assoc()):
            ?>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 10px 20px;">
                    <?php if($row['foto']): ?>
                        <img src="../assets/img/pengurus/<?= $row['foto'] ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color);">
                    <?php else: ?>
                        <div style="width: 50px; height: 50px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    <?php endif; ?>
                </td>
                <td style="padding: 15px 20px; font-weight: 500;"><?= htmlspecialchars($row['nama']) ?></td>
                <td style="padding: 15px 20px; color: var(--text-main);"><?= htmlspecialchars($row['jabatan']) ?></td>
                <td style="padding: 15px 20px;">
                    <span class="badge" style="background: <?= ($row['kategori'] == 'Pendeta' ? '#fef3c7' : ($row['kategori'] == 'BPI' ? '#dbeafe' : '#f1f5f9')) ?>; color: <?= ($row['kategori'] == 'Pendeta' ? '#92400e' : ($row['kategori'] == 'BPI' ? '#1e40af' : '#475569')) ?>;">
                        <?= $row['kategori'] ?>
                    </span>
                </td>
                <td style="padding: 15px 20px; color: var(--text-muted); font-size: 0.85rem;">
                    <?= mb_strimwidth(htmlspecialchars($row['deskripsi']), 0, 40, "...") ?>
                </td>
                <td style="padding: 15px 20px;">
                    <a href="pengurus.php?edit=<?= $row['id'] ?>" class="text-primary" style="margin-right: 15px;" title="Edit"><i class="fa-solid fa-edit"></i></a>
                    <a href="pengurus.php?delete=<?= $row['id'] ?>" class="text-danger" onclick="return confirm('Yakin ingin menghapus data pengurus ini?')" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if($result->num_rows == 0): ?>
            <tr>
                <td colspan="6" style="padding: 20px; text-align: center; color: var(--text-muted);">Belum ada data pengurus.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
