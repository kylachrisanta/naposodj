<?php
require_once '../includes/admin_header.php';
require_once '../includes/admin_sidebar.php';

$message = "";

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $visi = $conn->real_escape_string($_POST['visi']);
    $misi = $conn->real_escape_string($_POST['misi']);
    
    $conn->query("UPDATE settings SET value_text = '$visi' WHERE key_name = 'visi'");
    $conn->query("UPDATE settings SET value_text = '$misi' WHERE key_name = 'misi'");
    
    $message = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Visi & Misi berhasil diperbarui.</div>";
}

// Fetch current data
$res = $conn->query("SELECT * FROM settings");
$settings = [];
while($row = $res->fetch_assoc()) {
    $settings[$row['key_name']] = $row['value_text'];
}
?>

<div style="margin-bottom: 30px;">
    <h2>Kelola Visi & Misi</h2>
    <p style="color: var(--text-muted);">Sesuaikan visi dan misi organisasi yang tampil di halaman depan.</p>
</div>

<?= $message ?>

<div style="background: white; padding: 30px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
    <form action="visi_misi.php" method="POST">
        <div style="margin-bottom: 25px;">
            <label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 1.1rem; color: var(--primary);">
                <i class="fa-solid fa-eye"></i> Visi Organisasi
            </label>
            <textarea name="visi" rows="4" class="form-control" style="font-size: 1rem; padding: 15px;" required><?= htmlspecialchars($settings['visi'] ?? '') ?></textarea>
            <small style="color: var(--text-muted);">Visi adalah tujuan jangka panjang atau impian besar organisasi.</small>
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: block; font-weight: 600; margin-bottom: 10px; font-size: 1.1rem; color: var(--primary);">
                <i class="fa-solid fa-bullseye"></i> Misi Organisasi
            </label>
            <textarea name="misi" rows="8" class="form-control" style="font-size: 1rem; padding: 15px;" required><?= htmlspecialchars($settings['misi'] ?? '') ?></textarea>
            <small style="color: var(--text-muted);">Masukkan poin-poin misi. Pisahkan setiap poin dengan baris baru (Enter).</small>
        </div>

        <div style="display: flex; gap: 15px; align-items: center;">
            <button type="submit" class="btn-primary" style="padding: 12px 30px;">
                <i class="fa-solid fa-save"></i> Simpan Perubahan
            </button>
            <span style="color: var(--text-muted); font-size: 0.9rem;">Perubahan akan langsung tampil di halaman 'Tentang Kami'.</span>
        </div>
    </form>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
