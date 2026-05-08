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
    
    if (isset($_POST['id_warta']) && $_POST['id_warta'] != '') {
        $id = (int)$_POST['id_warta'];
        $sql = "UPDATE warta SET judul='$judul', isi_pengumuman='$isi_pengumuman' WHERE id=$id";
        if($conn->query($sql)) {
            $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Warta berhasil diperbarui.</div>";
        }
    } else {
        $sql = "INSERT INTO warta (judul, isi_pengumuman) VALUES ('$judul', '$isi_pengumuman')";
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
    'id' => '', 'judul' => '', 'isi_pengumuman' => ''
];
if (isset($_GET['edit_warta'])) {
    $id = (int)$_GET['edit_warta'];
    $res = $conn->query("SELECT * FROM warta WHERE id=$id");
    if ($res->num_rows > 0) {
        $edit_warta_mode = true;
        $edit_warta_data = $res->fetch_assoc();
    }
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
        <form action="kegiatan_warta.php" method="POST">
            <input type="hidden" name="id_warta" value="<?= $edit_warta_data['id'] ?>">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Judul Pengumuman</label>
                <input type="text" name="judul" value="<?= htmlspecialchars($edit_warta_data['judul']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Isi Pengumuman</label>
                <textarea name="isi_pengumuman" rows="9" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: var(--font-body);"><?= htmlspecialchars($edit_warta_data['isi_pengumuman']) ?></textarea>
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
                    ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 15px 20px; vertical-align: top;">
                            <strong style="display: block; margin-bottom: 4px;"><?= htmlspecialchars($row['nama_kegiatan']) ?></strong>
                            <span style="font-size: 0.85rem; color: var(--text-muted);"><i class="fa-solid fa-user-tie" style="margin-right:4px;"></i><?= htmlspecialchars($row['penanggung_jawab']) ?></span>
                        </td>
                        <td style="padding: 15px 20px; color: var(--text-muted); font-size: 0.9rem; vertical-align: top;">
                            <div style="margin-bottom: 4px;"><i class="fa-regular fa-clock" style="margin-right:4px;"></i><?= date('d M Y, H:i', strtotime($row['tanggal'])) ?> WIB</div>
                            <div><i class="fa-solid fa-location-dot" style="margin-right:4px;"></i><?= htmlspecialchars($row['tempat']) ?></div>
                        </td>
                        <td style="padding: 15px 20px; vertical-align: top; white-space: nowrap;">
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
                    $result = $conn->query("SELECT * FROM warta ORDER BY tanggal_posting DESC");
                    while($row = $result->fetch_assoc()):
                    ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 15px 20px; vertical-align: top;">
                            <strong style="display: block; margin-bottom: 4px;"><?= htmlspecialchars($row['judul']) ?></strong>
                            <span style="font-size: 0.85rem; color: var(--text-muted);"><?= mb_strimwidth(htmlspecialchars($row['isi_pengumuman']), 0, 80, "...") ?></span>
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

<?php require_once '../includes/admin_footer.php'; ?>
