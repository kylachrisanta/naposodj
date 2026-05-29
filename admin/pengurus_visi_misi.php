<?php
require_once '../includes/admin_header.php';
require_once '../includes/admin_sidebar.php';

$message = "";
if (isset($_SESSION['admin_flash'])) {
    $message = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
}
$upload_dir = '../assets/img/pengurus/';

// Handle Delete Pengurus
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
        $_SESSION['admin_flash'] = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Data pengurus berhasil dihapus.</div>";
        header("Location: pengurus_visi_misi.php");
        exit();
    }
}

// Handle Delete Program Kerja
if (isset($_GET['delete_proker'])) {
    $id = (int)$_GET['delete_proker'];
    $res = $conn->query("SELECT foto FROM program_kerja WHERE id=$id");
    if($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $file_path = '../assets/img/proker/' . $row['foto'];
        if($row['foto'] != '' && file_exists($file_path)) {
            unlink($file_path);
        }
        $conn->query("DELETE FROM program_kerja WHERE id=$id");
        $_SESSION['admin_flash'] = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Data program kerja berhasil dihapus.</div>";
        header("Location: pengurus_visi_misi.php");
        exit();
    }
}

// Handle Edit Program Kerja (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_edit_proker'])) {
    $id = (int)$_POST['proker_id'];
    $judul = $conn->real_escape_string($_POST['judul']);
    
    $foto = "";
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
        $foto = time() . '_proker_' . rand(100,999) . '.' . $ext;
        if(move_uploaded_file($_FILES["foto"]["tmp_name"], '../assets/img/proker/' . $foto)) {
            $old_res = $conn->query("SELECT foto FROM program_kerja WHERE id=$id");
            if($old_res->num_rows > 0) {
                $old_file = $old_res->fetch_assoc()['foto'];
                if($old_file != '' && file_exists('../assets/img/proker/' . $old_file)) unlink('../assets/img/proker/' . $old_file);
            }
        } else {
            $foto = "";
        }
    }

    if($foto != "") {
        $sql = "UPDATE program_kerja SET judul='$judul', foto='$foto' WHERE id=$id";
    } else {
        $sql = "UPDATE program_kerja SET judul='$judul' WHERE id=$id";
    }
    $conn->query($sql);
    $_SESSION['admin_flash'] = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Program kerja berhasil diperbarui.</div>";
    header("Location: pengurus_visi_misi.php");
    exit();
}

// Handle Add Program Kerja Terpisah (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_new_proker'])) {
    $judul = $conn->real_escape_string($_POST['judul']);
    $divisi = $conn->real_escape_string($_POST['divisi']);
    
    $foto = "";
    $uploadOk = true;
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
        $valid_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if(!in_array($ext, $valid_ext)) {
            $_SESSION['admin_flash'] = "<div class='alert alert-danger'>Format foto tidak valid. Gunakan JPG/PNG/WEBP.</div>";
            $uploadOk = false;
        } else {
            $foto = time() . '_proker_' . rand(100,999) . '.' . $ext;
            if(!move_uploaded_file($_FILES["foto"]["tmp_name"], '../assets/img/proker/' . $foto)) {
                $_SESSION['admin_flash'] = "<div class='alert alert-danger'>Gagal mengunggah foto.</div>";
                $uploadOk = false;
            }
        }
    } else {
        $_SESSION['admin_flash'] = "<div class='alert alert-danger'>Foto program kerja wajib diunggah.</div>";
        $uploadOk = false;
    }

    if($uploadOk) {
        $sql = "INSERT INTO program_kerja (divisi, judul, foto) VALUES ('$divisi', '$judul', '$foto')";
        $conn->query($sql);
        $_SESSION['admin_flash'] = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Program kerja berhasil ditambahkan secara terpisah.</div>";
    }
    header("Location: pengurus_visi_misi.php");
    exit();
}

// Handle Add / Edit Pengurus
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_pengurus'])) {
    $nama = $conn->real_escape_string($_POST['nama']);
    $jabatan = $conn->real_escape_string($_POST['jabatan']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    $kategori = $conn->real_escape_string($_POST['kategori']);
    $divisi = $conn->real_escape_string($_POST['divisi']);
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    $foto = "";
    $uploadOk = true;
    
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
        $valid_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if(!in_array($ext, $valid_ext)) {
            $_SESSION['admin_flash'] = "<div class='alert alert-danger'>Format foto tidak valid. Gunakan JPG/PNG/WEBP.</div>";
            $uploadOk = false;
        } else {
            $foto = time() . '_' . rand(100,999) . '.' . $ext;
            if(!move_uploaded_file($_FILES["foto"]["tmp_name"], $upload_dir . $foto)) {
                $_SESSION['admin_flash'] = "<div class='alert alert-danger'>Gagal mengunggah foto. Pastikan folder pengurus/ writable.</div>";
                $uploadOk = false;
            }
        }
    }

    if($uploadOk) {
        if ($id > 0) {
            // Edit Pengurus
            if($foto != "") {
                $old_res = $conn->query("SELECT foto FROM pengurus WHERE id=$id");
                $old_row = $old_res->fetch_assoc();
                if($old_row['foto'] != '' && file_exists($upload_dir . $old_row['foto'])) {
                    unlink($upload_dir . $old_row['foto']);
                }
                $sql = "UPDATE pengurus SET nama='$nama', jabatan='$jabatan', deskripsi='$deskripsi', foto='$foto', kategori='$kategori', divisi='$divisi' WHERE id=$id";
            } else {
                $sql = "UPDATE pengurus SET nama='$nama', jabatan='$jabatan', deskripsi='$deskripsi', kategori='$kategori', divisi='$divisi' WHERE id=$id";
            }
            if($conn->query($sql)) {
                $_SESSION['admin_flash'] = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Data pengurus berhasil diperbarui.</div>";
            }
        } else {
            // Add Pengurus
            $sql = "INSERT INTO pengurus (nama, jabatan, deskripsi, foto, kategori, divisi) VALUES ('$nama', '$jabatan', '$deskripsi', '$foto', '$kategori', '$divisi')";
            if($conn->query($sql)) {
                $_SESSION['admin_flash'] = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Data pengurus berhasil ditambahkan.</div>";
            }
        }

    }
    header("Location: pengurus_visi_misi.php");
    exit();
}

// Handle Update Visi Misi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_visimisi'])) {
    $visi = $conn->real_escape_string($_POST['visi']);
    $misi = $conn->real_escape_string($_POST['misi']);
    
    $conn->query("UPDATE settings SET value_text = '$visi' WHERE key_name = 'visi'");
    $conn->query("UPDATE settings SET value_text = '$misi' WHERE key_name = 'misi'");
    
    $_SESSION['admin_flash'] = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Visi & Misi berhasil diperbarui.</div>";
    header("Location: pengurus_visi_misi.php");
    exit();
}

// Fetch current Visi Misi data
$res = $conn->query("SELECT * FROM settings");
$settings = [];
while($row = $res->fetch_assoc()) {
    $settings[$row['key_name']] = $row['value_text'];
}

// Check Edit Mode Pengurus
$edit_mode = false;
$edit_data = ['id' => '', 'nama' => '', 'jabatan' => '', 'deskripsi' => '', 'kategori' => 'BPI', 'foto' => '', 'divisi' => ''];
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM pengurus WHERE id=$id");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}

// Check Edit Mode Program Kerja
$edit_proker_mode = false;
$edit_proker_data = [];
if (isset($_GET['edit_proker'])) {
    $id = (int)$_GET['edit_proker'];
    $res = $conn->query("SELECT * FROM program_kerja WHERE id=$id");
    if ($res->num_rows > 0) {
        $edit_proker_mode = true;
        $edit_proker_data = $res->fetch_assoc();
    }
}
?>

<div style="margin-bottom: 30px;">
    <h2>Kelola Pengurus dan Visi & Misi</h2>
    <p style="color: var(--text-muted);">Manajemen profil Pendeta, BPI, Ketua Divisi, serta Visi dan Misi organisasi.</p>
</div>

<?= $message ?>

<div style="display: grid; grid-template-columns: 1fr; gap: 30px; margin-bottom: 30px;">
    
    <!-- Bagian Visi Misi -->
    <div style="background: white; padding: 30px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 20px;"><i class="fa-solid fa-lightbulb" style="color: var(--primary);"></i> Kelola Visi & Misi</h3>
        <form action="pengurus_visi_misi.php" method="POST">
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 1.1rem; color: var(--primary);">
                    <i class="fa-solid fa-eye"></i> Visi Organisasi
                </label>
                <textarea name="visi" rows="3" class="form-control" style="font-size: 1rem; padding: 15px;" required><?= htmlspecialchars($settings['visi'] ?? '') ?></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 1.1rem; color: var(--primary);">
                    <i class="fa-solid fa-bullseye"></i> Misi Organisasi
                </label>
                <textarea name="misi" rows="5" class="form-control" style="font-size: 1rem; padding: 15px;" required><?= htmlspecialchars($settings['misi'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: 15px; align-items: center;">
                <button type="submit" name="submit_visimisi" class="btn-primary" style="padding: 10px 25px;">
                    <i class="fa-solid fa-save"></i> Simpan Visi & Misi
                </button>
            </div>
        </form>
    </div>

    <!-- Bagian Form Pengurus -->
    <div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 20px;"><i class="fa-solid fa-user-plus" style="color: var(--primary);"></i> <?= $edit_mode ? 'Ubah Data Pengurus' : 'Tambah Pengurus Baru' ?></h3>
        <form action="pengurus_visi_misi.php" method="POST" enctype="multipart/form-data">
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
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                <div>
                    <label>Kategori</label>
                    <select name="kategori" class="form-control" onchange="toggleDivisiField(this.value)">
                        <option value="Pendeta" <?= $edit_data['kategori'] == 'Pendeta' ? 'selected' : '' ?>>Pendeta</option>
                        <option value="BPI" <?= $edit_data['kategori'] == 'BPI' ? 'selected' : '' ?>>Badan Pengurus Inti (BPI)</option>
                        <option value="Divisi" <?= $edit_data['kategori'] == 'Divisi' ? 'selected' : '' ?>>Divisi / Program Kerja</option>
                    </select>
                </div>
                <div id="divisi-field" style="display: <?= $edit_data['kategori'] == 'Divisi' ? 'block' : 'none' ?>;">
                    <label>Nama Divisi</label>
                    <select name="divisi" class="form-control">
                        <option value="">-- Pilih Divisi --</option>
                        <option value="Rohani" <?= $edit_data['divisi'] == 'Rohani' ? 'selected' : '' ?>>Rohani</option>
                        <option value="Padus & Musik" <?= $edit_data['divisi'] == 'Padus & Musik' ? 'selected' : '' ?>>Padus & Musik</option>
                        <option value="Humas" <?= $edit_data['divisi'] == 'Humas' ? 'selected' : '' ?>>Humas</option>
                        <option value="Olahraga" <?= $edit_data['divisi'] == 'Olahraga' ? 'selected' : '' ?>>Olahraga</option>
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
                <button type="submit" name="submit_pengurus" class="btn-primary"><?= $edit_mode ? 'Simpan Perubahan' : 'Tambah Pengurus' ?></button>
                <?php if($edit_mode): ?>
                    <a href="pengurus_visi_misi.php" class="btn-secondary">Batal Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <?php if($edit_proker_mode): ?>
    <!-- Bagian Form Edit Program Kerja -->
    <div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 20px; color: var(--primary);"><i class="fa-solid fa-edit"></i> Ubah Data Program Kerja</h3>
        <form action="pengurus_visi_misi.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="proker_id" value="<?= $edit_proker_data['id'] ?>">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                <div>
                    <label>Judul Program Kerja</label>
                    <input type="text" name="judul" value="<?= htmlspecialchars($edit_proker_data['judul']) ?>" required class="form-control">
                </div>
                <div>
                    <label>Divisi</label>
                    <input type="text" value="<?= htmlspecialchars($edit_proker_data['divisi']) ?>" disabled class="form-control">
                </div>
            </div>
            <div style="margin-bottom: 20px;">
                <label>Foto Dokumentasi (Opsional, kosongkan jika tidak ingin ganti)</label>
                <input type="file" name="foto" class="form-control">
                <small style="display: block; margin-top: 5px; color: var(--primary);">Foto saat ini: <?= htmlspecialchars($edit_proker_data['foto']) ?></small>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="submit_edit_proker" class="btn-primary">Simpan Perubahan</button>
                <a href="pengurus_visi_misi.php" class="btn-secondary">Batal Edit</a>
            </div>
        </form>
    </div>
    <?php endif; ?>
    
    <!-- Bagian Form Tambah Program Kerja Mandiri -->
    <div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
        <h3 style="margin-bottom: 20px;"><i class="fa-solid fa-folder-plus" style="color: var(--primary);"></i> Tambah Program Kerja Baru (Tanpa Tambah Pengurus)</h3>
        <form action="pengurus_visi_misi.php" method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                <div>
                    <label>Judul Program Kerja</label>
                    <input type="text" name="judul" required class="form-control" placeholder="Contoh: Donor Darah 2024">
                </div>
                <div>
                    <label>Pilih Divisi Terkait</label>
                    <select name="divisi" required class="form-control">
                        <option value="">-- Pilih Divisi --</option>
                        <option value="Rohani">Rohani</option>
                        <option value="Padus & Musik">Padus & Musik</option>
                        <option value="Humas">Humas</option>
                        <option value="Olahraga">Olahraga</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 20px;">
                <label>Foto Dokumentasi Program Kerja</label>
                <input type="file" name="foto" required class="form-control">
                <small style="color: var(--text-muted); display: block; margin-top: 10px;">Program kerja ini akan otomatis masuk ke divisi terkait di halaman Tentang Kami.</small>
            </div>
            <button type="submit" name="submit_new_proker" class="btn-primary">Tambah Program Kerja</button>
        </form>
    </div>

</div>

<!-- Table Section Pengurus -->
<div style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden;">
    <h3 style="padding: 20px; background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); margin: 0;"><i class="fa-solid fa-users" style="color: var(--primary);"></i> Daftar Pengurus</h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color);">
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
                        <?php if($row['kategori'] == 'Divisi'): ?>
                            <div style="font-size: 0.75rem; margin-top: 5px; color: var(--primary); font-weight: 600;"><?= $row['divisi'] ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px 20px; color: var(--text-muted); font-size: 0.85rem;">
                        <?= mb_strimwidth(htmlspecialchars($row['deskripsi']), 0, 40, "...") ?>
                    </td>
                    <td style="padding: 15px 20px;">
                        <a href="pengurus_visi_misi.php?edit=<?= $row['id'] ?>" class="text-primary" style="margin-right: 15px;" title="Edit"><i class="fa-solid fa-edit"></i></a>
                        <a href="pengurus_visi_misi.php?delete=<?= $row['id'] ?>" class="text-danger" onclick="return confirm('Yakin ingin menghapus data pengurus ini?')" title="Hapus"><i class="fa-solid fa-trash"></i></a>
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
</div>

<!-- Table Section Program Kerja -->
<div style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden; margin-top: 30px;">
    <h3 style="padding: 20px; background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); margin: 0;"><i class="fa-solid fa-image" style="color: var(--primary);"></i> Daftar Program Kerja Divisi</h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color);">
                    <th style="padding: 15px 20px; width: 10%;">Foto</th>
                    <th style="padding: 15px 20px; width: 40%;">Judul Program</th>
                    <th style="padding: 15px 20px; width: 25%;">Divisi</th>
                    <th style="padding: 15px 20px; width: 15%;">Tgl Dibuat</th>
                    <th style="padding: 15px 20px; width: 10%;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result_proker = $conn->query("SELECT * FROM program_kerja ORDER BY created_at DESC");
                while($row = $result_proker->fetch_assoc()):
                ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 10px 20px;">
                        <img src="../assets/img/proker/<?= $row['foto'] ?>" style="width: 60px; height: 45px; border-radius: 4px; object-fit: cover; border: 1px solid var(--border-color);">
                    </td>
                    <td style="padding: 15px 20px; font-weight: 500;"><?= htmlspecialchars($row['judul']) ?></td>
                    <td style="padding: 15px 20px;">
                        <span class="badge" style="background: #e0e7ff; color: #4338ca;"><?= htmlspecialchars($row['divisi']) ?></span>
                    </td>
                    <td style="padding: 15px 20px; color: var(--text-muted); font-size: 0.85rem;">
                        <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                    </td>
                    <td style="padding: 15px 20px;">
                        <a href="pengurus_visi_misi.php?edit_proker=<?= $row['id'] ?>" class="text-primary" style="margin-right: 15px;" title="Edit"><i class="fa-solid fa-edit"></i></a>
                        <a href="pengurus_visi_misi.php?delete_proker=<?= $row['id'] ?>" class="text-danger" onclick="return confirm('Yakin ingin menghapus data program kerja ini?')" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($result_proker->num_rows == 0): ?>
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted);">Belum ada data program kerja divisi.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleDivisiField(val) {
    const isDivisi = (val === 'Divisi');
    document.getElementById('divisi-field').style.display = isDivisi ? 'block' : 'none';
}
</script>

<?php require_once '../includes/admin_footer.php'; ?>
