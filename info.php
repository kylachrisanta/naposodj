<?php
session_start();
// Auth Check: Lempar pengguna ke login jika belum ada sesi user
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
// Mengambil config database
require_once 'config/database.php';

// Ambil status notifikasi user
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT wa_notification FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_status = $user_query->get_result()->fetch_assoc()['wa_notification'];
?>
<?php include 'includes/header.php'; ?>

<!-- Info Header -->
<section class="section bg-subtle" style="padding-top: 120px; padding-bottom: 40px;">
    <div class="container text-center">
        <h1 class="section-title">Informasi Utama</h1>
        <p class="section-subtitle">Jadwal kegiatan mingguan dan pengumuman bagi anggota Naposo HKBP Duren Jaya.</p>
    </div>
</section>

<!-- Jadwal Kegiatan -->
<section class="section">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
            <h2 style="font-family: var(--font-heading); font-size: 1.75rem;"><i class="fa-regular fa-calendar-check" style="color: var(--primary);"></i> Jadwal Kegiatan Seminggu</h2>
        </div>
        
        <!-- WhatsApp Notification Toggle -->
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
            <span style="font-size: 1.1rem; color: var(--text-main); font-weight: 500;">Notifikasi WhatsApp</span>
            <label class="switch">
                <input type="checkbox" id="waToggle" <?= $user_status === 'aktif' ? 'checked' : '' ?>>
                <span class="slider round"></span>
            </label>
        </div>
        
        <div style="overflow-x: auto; background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
                        <th style="padding: 15px 20px; font-weight: 600; color: var(--text-main);">No</th>
                        <th style="padding: 15px 20px; font-weight: 600; color: var(--text-main);">Nama Kegiatan</th>
                        <th style="padding: 15px 20px; font-weight: 600; color: var(--text-main);">Waktu</th>
                        <th style="padding: 15px 20px; font-weight: 600; color: var(--text-main);">Tempat</th>
                        <th style="padding: 15px 20px; font-weight: 600; color: var(--text-main);">Penanggung Jawab</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result_keg = $conn->query("SELECT * FROM kegiatan ORDER BY tanggal ASC");
                    $no = 1;
                    if($result_keg->num_rows > 0):
                        while($row_keg = $result_keg->fetch_assoc()):
                    ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 15px 20px; color: var(--text-muted);"><?= $no++ ?></td>
                        <td style="padding: 15px 20px; font-weight: 500;"><?= htmlspecialchars($row_keg['nama_kegiatan']) ?></td>
                        <td style="padding: 15px 20px; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($row_keg['tanggal'])) ?> WIB</td>
                        <td style="padding: 15px 20px; color: var(--text-muted);"><?= htmlspecialchars($row_keg['tempat']) ?></td>
                        <td style="padding: 15px 20px; color: var(--text-muted);"><?= htmlspecialchars($row_keg['penanggung_jawab']) ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: var(--text-muted);">Belum ada jadwal kegiatan minggu ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Warta Pengumuman -->
<section class="section bg-subtle">
    <div class="container">
        <h2 style="font-family: var(--font-heading); font-size: 1.75rem; margin-bottom: 20px;"><i class="fa-solid fa-bullhorn" style="color: var(--accent);"></i> Warta</h2>
        
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php
            $result_warta = $conn->query("SELECT * FROM warta ORDER BY tanggal_posting DESC");
            $warta_colors = ['var(--primary)', 'var(--accent)', '#10b981', '#8b5cf6'];
            $color_idx = 0;
            
            if($result_warta->num_rows > 0):
                while($row_warta = $result_warta->fetch_assoc()):
                    // Rotasi warna border kiri untuk estetika
                    $border_color = $warta_colors[$color_idx % count($warta_colors)];
                    $color_idx++;
            ?>
            <div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-left: 4px solid <?= $border_color ?>;">
                <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 10px;"><i class="fa-regular fa-clock"></i> Diposting pada <?= date('d M Y', strtotime($row_warta['tanggal_posting'])) ?></div>
                <h3 style="font-family: var(--font-heading); font-size: 1.25rem; margin-bottom: 10px;"><?= htmlspecialchars($row_warta['judul']) ?></h3>
                <p style="color: var(--text-muted); line-height: 1.6;"><?= nl2br(htmlspecialchars($row_warta['isi_pengumuman'])) ?></p>
            </div>
            <?php endwhile; else: ?>
            <div style="text-align: center; padding: 40px; color: var(--text-muted); background: white; border-radius: var(--radius-md);">Belum ada warta jemaat saat ini.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('waToggle').addEventListener('change', function() {
    const isChecked = this.checked;
    const status = isChecked ? 'aktif' : 'nonaktif';
    
    fetch('ajax/update_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ wa_notification: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            console.log('Notification status updated to ' + data.new_status);
        } else {
            alert('Gagal memperbarui status notifikasi: ' + data.message);
            this.checked = !isChecked; // Revert toggle on failure
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghubungi server.');
        this.checked = !isChecked; // Revert toggle on error
    });
});
</script>
