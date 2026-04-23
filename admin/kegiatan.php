<?php
require_once '../includes/admin_header.php';
require_once '../includes/admin_sidebar.php';

$message = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM kegiatan WHERE id=$id");
    $message = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Kegiatan berhasil dihapus.</div>";
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kegiatan = $conn->real_escape_string($_POST['nama_kegiatan']);
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
    $tempat = $conn->real_escape_string($_POST['tempat']);
    $penanggung_jawab = $conn->real_escape_string($_POST['penanggung_jawab']);
    
    if (isset($_POST['id']) && $_POST['id'] != '') {
        // Edit
        $id = (int)$_POST['id'];
        $sql = "UPDATE kegiatan SET nama_kegiatan='$nama_kegiatan', tanggal='$tanggal', tempat='$tempat', penanggung_jawab='$penanggung_jawab' WHERE id=$id";
        if($conn->query($sql)) {
            $message = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Kegiatan berhasil diperbarui.</div>";
        }
    } else {
        // Add
        $sql = "INSERT INTO kegiatan (nama_kegiatan, tanggal, tempat, penanggung_jawab) VALUES ('$nama_kegiatan', '$tanggal', '$tempat', '$penanggung_jawab')";
        if($conn->query($sql)) {
            $message = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Kegiatan berhasil ditambahkan.</div>";
        }
    }
}

// Check Edit Mode
$edit_mode = false;
$edit_data = [
    'id' => '', 'nama_kegiatan' => '', 'tanggal' => '', 'tempat' => '', 'penanggung_jawab' => ''
];
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM kegiatan WHERE id=$id");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
        // datetime-local expects format YYYY-MM-DDThh:mm
        $edit_data['tanggal'] = date('Y-m-d\TH:i', strtotime($edit_data['tanggal']));
    }
}
?>

<div style="margin-bottom: 30px;">
    <h2>Kelola Jadwal Kegiatan</h2>
    <p style="color: var(--text-muted);">Tambahkan, ubah, atau hapus jadwal kegiatan mingguan Naposo.</p>
</div>

<?= $message ?>

<!-- Form Section -->
<div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); margin-bottom: 30px;">
    <h3 style="margin-bottom: 20px;"><?= $edit_mode ? 'Ubah Kegiatan' : 'Tambah Kegiatan Baru' ?></h3>
    <form action="kegiatan.php" method="POST">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nama Kegiatan</label>
                <input type="text" name="nama_kegiatan" value="<?= htmlspecialchars($edit_data['nama_kegiatan']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Waktu (Tanggal & Jam)</label>
                <input type="datetime-local" name="tanggal" value="<?= $edit_data['tanggal'] ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Tempat</label>
                <input type="text" name="tempat" value="<?= htmlspecialchars($edit_data['tempat']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Penanggung Jawab</label>
                <input type="text" name="penanggung_jawab" value="<?= htmlspecialchars($edit_data['penanggung_jawab']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary"><?= $edit_mode ? 'Simpan Perubahan' : 'Simpan Kegiatan' ?></button>
            <?php if($edit_mode): ?>
                <a href="kegiatan.php" style="padding: 10px 20px; background: #e2e8f0; color: #334155; border-radius: 6px; font-weight: 500;">Batal Edit</a>
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
                <th style="padding: 15px 20px; width: 25%;">Nama Kegiatan</th>
                <th style="padding: 15px 20px; width: 20%;">Waktu</th>
                <th style="padding: 15px 20px; width: 20%;">Tempat</th>
                <th style="padding: 15px 20px; width: 20%;">Penanggung Jawab</th>
                <th style="padding: 15px 20px; width: 10%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM kegiatan ORDER BY tanggal DESC");
            $no = 1;
            while($row = $result->fetch_assoc()):
            ?>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 15px 20px; color: var(--text-muted);"><?= $no++ ?></td>
                <td style="padding: 15px 20px; font-weight: 500;"><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
                <td style="padding: 15px 20px; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($row['tanggal'])) ?> WIB</td>
                <td style="padding: 15px 20px; color: var(--text-muted);"><?= htmlspecialchars($row['tempat']) ?></td>
                <td style="padding: 15px 20px; color: var(--text-muted);"><?= htmlspecialchars($row['penanggung_jawab']) ?></td>
                <td style="padding: 15px 20px;">
                    <a href="kegiatan.php?edit=<?= $row['id'] ?>" style="color: var(--accent); margin-right: 15px;" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    <a href="kegiatan.php?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus jadwal ini?');" style="color: #b91c1c;" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if($result->num_rows == 0): ?>
            <tr>
                <td colspan="6" style="padding: 20px; text-align: center; color: var(--text-muted);">Belum ada jadwal kegiatan.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
