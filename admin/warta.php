<?php
require_once '../includes/admin_header.php';
require_once '../includes/admin_sidebar.php';

$message = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM warta WHERE id=$id");
    $message = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Warta berhasil dihapus.</div>";
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $conn->real_escape_string($_POST['judul']);
    $isi_pengumuman = $conn->real_escape_string($_POST['isi_pengumuman']);
    
    if (isset($_POST['id']) && $_POST['id'] != '') {
        // Edit
        $id = (int)$_POST['id'];
        $sql = "UPDATE warta SET judul='$judul', isi_pengumuman='$isi_pengumuman' WHERE id=$id";
        if($conn->query($sql)) {
            $message = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Warta berhasil diubah.</div>";
        }
    } else {
        // Add
        $sql = "INSERT INTO warta (judul, isi_pengumuman) VALUES ('$judul', '$isi_pengumuman')";
        if($conn->query($sql)) {
            $message = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Warta berhasil ditambahkan.</div>";
        }
    }
}

// Check Edit Mode
$edit_mode = false;
$edit_data = [
    'id' => '', 'judul' => '', 'isi_pengumuman' => ''
];
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM warta WHERE id=$id");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}
?>

<div style="margin-bottom: 30px;">
    <h2>Kelola Warta Jemaat</h2>
    <p style="color: var(--text-muted);">Bagikan pengumuman atau informasi penting kepada Naposo.</p>
</div>

<?= $message ?>

<!-- Form Section -->
<div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); margin-bottom: 30px;">
    <h3 style="margin-bottom: 20px;"><?= $edit_mode ? 'Ubah Warta' : 'Tambah Warta Baru' ?></h3>
    <form action="warta.php" method="POST">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Judul Pengumuman</label>
            <input type="text" name="judul" value="<?= htmlspecialchars($edit_data['judul']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
        </div>
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Isi Pengumuman</label>
            <textarea name="isi_pengumuman" rows="6" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; font-family: var(--font-body);"><?= htmlspecialchars($edit_data['isi_pengumuman']) ?></textarea>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary"><?= $edit_mode ? 'Simpan Perubahan' : 'Sebarkan Warta' ?></button>
            <?php if($edit_mode): ?>
                <a href="warta.php" style="padding: 10px 20px; background: #e2e8f0; color: #334155; border-radius: 6px; font-weight: 500;">Batal Edit</a>
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
                <th style="padding: 15px 20px; width: 25%;">Judul</th>
                <th style="padding: 15px 20px; width: 45%;">Isi Singkat</th>
                <th style="padding: 15px 20px; width: 15%;">Tanggal</th>
                <th style="padding: 15px 20px; width: 10%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM warta ORDER BY tanggal_posting DESC");
            $no = 1;
            while($row = $result->fetch_assoc()):
            ?>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 15px 20px; color: var(--text-muted);"><?= $no++ ?></td>
                <td style="padding: 15px 20px; font-weight: 500;"><?= htmlspecialchars($row['judul']) ?></td>
                <td style="padding: 15px 20px; color: var(--text-muted);">
                    <?= mb_strimwidth(htmlspecialchars($row['isi_pengumuman']), 0, 70, "...") ?>
                </td>
                <td style="padding: 15px 20px; color: var(--text-muted); font-size: 0.9rem;"><?= date('d M Y, H:i', strtotime($row['tanggal_posting'])) ?></td>
                <td style="padding: 15px 20px;">
                    <a href="warta.php?edit=<?= $row['id'] ?>" style="color: var(--accent); margin-right: 15px;" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    <a href="warta.php?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus warta ini?');" style="color: #b91c1c;" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if($result->num_rows == 0): ?>
            <tr>
                <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted);">Belum ada warta/pengumuman.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
