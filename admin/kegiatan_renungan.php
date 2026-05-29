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
    
    if (isset($_POST['id_renungan']) && $_POST['id_renungan'] != '') {
        $id = (int)$_POST['id_renungan'];
        $sql = "UPDATE renungan SET judul='$judul', ayat_alkitab='$ayat_alkitab', isi_renungan='$isi_renungan', penulis='$penulis', tanggal_posting='$tanggal_posting' WHERE id=$id";
        
        if ($conn->query($sql)) {
            $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Renungan berhasil diperbarui.</div>";
        }
    } else {
        $sql = "INSERT INTO renungan (judul, ayat_alkitab, isi_renungan, penulis, tanggal_posting) VALUES ('$judul', '$ayat_alkitab', '$isi_renungan', '$penulis', '$tanggal_posting')";
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
    'id' => '', 'judul' => '', 'ayat_alkitab' => '', 'isi_renungan' => '', 'penulis' => '', 'tanggal_posting' => ''
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
        
        <form action="kegiatan_renungan.php" method="POST">
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



<?php require_once '../includes/admin_footer.php'; ?>
