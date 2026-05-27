<?php
require_once '../includes/admin_header.php';
require_once '../includes/admin_sidebar.php';

// Get and validate warta_id
$warta_id = isset($_GET['warta_id']) ? (int)$_GET['warta_id'] : 0;
if (!$warta_id && isset($_POST['warta_id'])) {
    $warta_id = (int)$_POST['warta_id'];
}

if (!$warta_id) {
    header("Location: kegiatan_warta.php");
    exit();
}

// Fetch event (warta) details
$warta_res = $conn->query("SELECT * FROM warta WHERE id = $warta_id");
if ($warta_res->num_rows == 0) {
    header("Location: kegiatan_warta.php");
    exit();
}
$warta = $warta_res->fetch_assoc();

$message = "";
if (isset($_SESSION['admin_flash'])) {
    $message = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $pendaftaran_id = (int)$_POST['pendaftaran_id'];
    $status = $conn->real_escape_string($_POST['status']);
    
    $valid_statuses = ['Menunggu Verifikasi', 'Lunas', 'Ditolak', 'Bayar di Tempat'];
    if (in_array($status, $valid_statuses)) {
        $conn->query("UPDATE pendaftaran_warta SET status_pembayaran='$status' WHERE id=$pendaftaran_id");
        $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Status pembayaran berhasil diperbarui.</div>";
    }
    header("Location: pendaftar.php?warta_id=" . $warta_id);
    exit();
}

// Handle delete participant
if (isset($_GET['delete_pendaftaran'])) {
    $pendaftaran_id = (int)$_GET['delete_pendaftaran'];
    // Delete payment proof if it exists
    $res = $conn->query("SELECT bukti_pembayaran FROM pendaftaran_warta WHERE id=$pendaftaran_id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (!empty($row['bukti_pembayaran'])) {
            $file_path = '../assets/img/bukti_pembayaran/' . $row['bukti_pembayaran'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    $conn->query("DELETE FROM pendaftaran_warta WHERE id=$pendaftaran_id");
    $_SESSION['admin_flash'] = "<div style='color: #15803d; background: #dcfce7; padding: 10px 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #86efac;'><i class='fa-solid fa-circle-check'></i> Peserta berhasil dihapus.</div>";
    header("Location: pendaftar.php?warta_id=" . $warta_id);
    exit();
}

// Count registrations by status
$stats = [
    'total' => 0,
    'lunas' => 0,
    'menunggu' => 0,
    'bayar_di_tempat' => 0,
    'ditolak' => 0
];
$stat_res = $conn->query("SELECT status_pembayaran, COUNT(*) as count FROM pendaftaran_warta WHERE warta_id = $warta_id GROUP BY status_pembayaran");
while ($stat_row = $stat_res->fetch_assoc()) {
    $status = $stat_row['status_pembayaran'];
    $count = (int)$stat_row['count'];
    $stats['total'] += $count;
    if ($status == 'Lunas') $stats['lunas'] = $count;
    elseif ($status == 'Menunggu Verifikasi') $stats['menunggu'] = $count;
    elseif ($status == 'Bayar di Tempat') $stats['bayar_di_tempat'] = $count;
    elseif ($status == 'Ditolak') $stats['ditolak'] = $count;
}
?>

<div style="margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
    <div>
        <a href="kegiatan_warta.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; font-weight: 500; font-size: 0.9rem; margin-bottom: 10px; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Warta & Kegiatan
        </a>
        <h2>Daftar Peserta Kegiatan</h2>
        <p style="color: var(--text-muted); font-size: 1.1rem; font-weight: 600; margin-top: 5px; color: var(--primary);">
            <?= htmlspecialchars($warta['judul']) ?>
        </p>
    </div>
</div>

<?= $message ?>

<!-- Status Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: white; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
        <span style="font-size: 1.8rem; font-weight: 700; color: var(--text-main);"><?= $stats['total'] ?></span>
        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500; margin-top: 5px;">Total Pendaftar</span>
    </div>
    <div style="background: white; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
        <span style="font-size: 1.8rem; font-weight: 700; color: #16a34a;"><?= $stats['lunas'] ?></span>
        <span style="font-size: 0.85rem; color: #16a34a; font-weight: 600; margin-top: 5px; background: #dcfce7; padding: 2px 8px; border-radius: 9999px;">Lunas</span>
    </div>
    <div style="background: white; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
        <span style="font-size: 1.8rem; font-weight: 700; color: #d97706;"><?= $stats['menunggu'] ?></span>
        <span style="font-size: 0.85rem; color: #d97706; font-weight: 600; margin-top: 5px; background: #fef3c7; padding: 2px 8px; border-radius: 9999px;">Menunggu Verifikasi</span>
    </div>
    <div style="background: white; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
        <span style="font-size: 1.8rem; font-weight: 700; color: #2563eb;"><?= $stats['bayar_di_tempat'] ?></span>
        <span style="font-size: 0.85rem; color: #2563eb; font-weight: 600; margin-top: 5px; background: #dbeafe; padding: 2px 8px; border-radius: 9999px;">Bayar di Tempat</span>
    </div>
</div>

<!-- Participant List Table -->
<div style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden; display: flex; flex-direction: column;">
    <h3 style="padding: 20px; background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); margin: 0; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-users" style="color: var(--primary);"></i> Daftar Peserta
    </h3>
    <div style="overflow-x: auto; flex: 1;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 800px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); background: #f8fafc;">
                    <th style="padding: 15px 20px; font-weight: 600;">No</th>
                    <th style="padding: 15px 20px; font-weight: 600;">Peserta</th>
                    <th style="padding: 15px 20px; font-weight: 600;">Kontak</th>
                    <th style="padding: 15px 20px; font-weight: 600;">Pembayaran</th>
                    <th style="padding: 15px 20px; font-weight: 600;">Bukti</th>
                    <th style="padding: 15px 20px; font-weight: 600;">Catatan</th>
                    <th style="padding: 15px 20px; font-weight: 600; width: 220px;">Status</th>
                    <th style="padding: 15px 20px; font-weight: 600; width: 80px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $participants = $conn->query("SELECT * FROM pendaftaran_warta WHERE warta_id = $warta_id ORDER BY created_at DESC");
                $no = 1;
                while ($row = $participants->fetch_assoc()):
                ?>
                <tr style="border-bottom: 1px solid var(--border-color); transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#fafafa'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 15px 20px; vertical-align: middle;"><?= $no++ ?></td>
                    <td style="padding: 15px 20px; vertical-align: middle;">
                        <strong style="color: var(--text-main); display: block;"><?= htmlspecialchars($row['nama_peserta']) ?></strong>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Terdaftar: <?= date('d M Y, H:i', strtotime($row['created_at'])) ?></span>
                    </td>
                    <td style="padding: 15px 20px; vertical-align: middle;">
                        <div style="font-size: 0.9rem; color: var(--text-main); display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                            <i class="fa-regular fa-envelope" style="color: var(--text-muted); width: 14px;"></i> <?= htmlspecialchars($row['email_peserta']) ?>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                            <i class="fa-brands fa-whatsapp" style="color: #25d366; width: 14px;"></i> <?= htmlspecialchars($row['whatsapp_peserta']) ?>
                        </div>
                    </td>
                    <td style="padding: 15px 20px; vertical-align: middle;">
                        <span style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);">
                            <?= $row['metode_pembayaran'] ?>
                        </span>
                    </td>
                    <td style="padding: 15px 20px; vertical-align: middle;">
                        <?php if ($row['metode_pembayaran'] == 'Non Tunai' && !empty($row['bukti_pembayaran'])): ?>
                            <?php $proof_url = '../assets/img/bukti_pembayaran/' . $row['bukti_pembayaran']; ?>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <a href="javascript:void(0)" onclick="openLightbox('<?= $proof_url ?>')" style="display: block; width: 45px; height: 45px; border-radius: 4px; overflow: hidden; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="Klik untuk memperbesar">
                                    <img src="<?= $proof_url ?>" alt="Bukti" style="width: 100%; height: 100%; object-fit: cover;">
                                </a>
                                <a href="<?= $proof_url ?>" download style="font-size: 0.8rem; color: var(--primary); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;" title="Unduh Bukti">
                                    <i class="fa-solid fa-download"></i> Unduh
                                </a>
                            </div>
                        <?php else: ?>
                            <span style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">Tidak Ada (Tunai)</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px 20px; vertical-align: middle; max-width: 200px;">
                        <div style="font-size: 0.85rem; color: var(--text-muted); overflow-wrap: break-word;">
                            <?= !empty($row['catatan']) ? nl2br(htmlspecialchars($row['catatan'])) : '-' ?>
                        </div>
                    </td>
                    <td style="padding: 15px 20px; vertical-align: middle;">
                        <form action="pendaftar.php" method="POST" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                            <input type="hidden" name="warta_id" value="<?= $warta_id ?>">
                            <input type="hidden" name="pendaftaran_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="update_status" value="1">
                            
                            <?php
                            $badge_color = '';
                            $badge_bg = '';
                            if ($row['status_pembayaran'] == 'Lunas') {
                                $badge_color = '#16a34a'; $badge_bg = '#dcfce7';
                            } elseif ($row['status_pembayaran'] == 'Menunggu Verifikasi') {
                                $badge_color = '#d97706'; $badge_bg = '#fef3c7';
                            } elseif ($row['status_pembayaran'] == 'Bayar di Tempat') {
                                $badge_color = '#2563eb'; $badge_bg = '#dbeafe';
                            } elseif ($row['status_pembayaran'] == 'Ditolak') {
                                $badge_color = '#dc2626'; $badge_bg = '#fee2e2';
                            }
                            ?>
                            
                            <select name="status" onchange="this.form.submit()" style="padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; border: 1px solid var(--border-color); color: <?= $badge_color ?>; background: <?= $badge_bg ?>; cursor: pointer; outline: none;">
                                <option value="Menunggu Verifikasi" style="color: #d97706; background: white;" <?= $row['status_pembayaran'] == 'Menunggu Verifikasi' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                                <option value="Lunas" style="color: #16a34a; background: white;" <?= $row['status_pembayaran'] == 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                                <option value="Bayar di Tempat" style="color: #2563eb; background: white;" <?= $row['status_pembayaran'] == 'Bayar di Tempat' ? 'selected' : '' ?>>Bayar di Tempat</option>
                                <option value="Ditolak" style="color: #dc2626; background: white;" <?= $row['status_pembayaran'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </form>
                    </td>
                    <td style="padding: 15px 20px; vertical-align: middle; text-align: center;">
                        <a href="pendaftar.php?warta_id=<?= $warta_id ?>&delete_pendaftaran=<?= $row['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pendaftaran peserta ini? Tindakan ini tidak dapat dibatalkan.');" style="color: #dc2626; padding: 6px 10px; border-radius: 6px; font-size: 0.9rem; transition: background-color 0.2s; display: inline-block;" onmouseover="this.style.backgroundColor='#fee2e2'" onmouseout="this.style.backgroundColor='transparent'" title="Hapus Pendaftar">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($participants->num_rows == 0): ?>
                <tr>
                    <td colspan="8" style="padding: 30px; text-align: center; color: var(--text-muted);">
                        <i class="fa-regular fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 10px; color: #cbd5e1;"></i>
                        Belum ada peserta yang mendaftar untuk kegiatan ini.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Lightbox Modal for Payment Proof Preview -->
<div id="imageLightbox" onclick="closeLightbox(event)" style="display:none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.95); z-index: 10000; backdrop-filter: blur(8px); justify-content: center; align-items: center; padding: 20px; cursor: zoom-out;">
    <div style="position: relative; max-width: 90%; max-height: 90%; cursor: default; display: flex; justify-content: center; align-items: center; animation: fadeIn 0.3s ease;" onclick="event.stopPropagation()">
        <!-- Close Button -->
        <button onclick="closeLightbox(event)" style="position: absolute; top: -50px; right: 0; background: rgba(255,255,255,0.15); border: none; border-radius: 50%; width: 40px; height: 40px; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 1.25rem;" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='scale(1)'">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <!-- Preview Image -->
        <img id="lightboxImage" src="" alt="Pratinjau Bukti Pembayaran" style="max-width: 100%; max-height: 85vh; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); display: block; border: 4px solid white; object-fit: contain;">
    </div>
</div>

<script>
function openLightbox(imageSrc) {
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImg = document.getElementById('lightboxImage');
    lightboxImg.src = imageSrc;
    lightbox.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // prevent background scrolling
}

function closeLightbox(event) {
    const lightbox = document.getElementById('imageLightbox');
    lightbox.style.display = 'none';
    document.body.style.overflow = 'auto'; // restore scrolling
}

// Close lightbox on Escape
window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLightbox();
    }
});
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<?php require_once '../includes/admin_footer.php'; ?>
