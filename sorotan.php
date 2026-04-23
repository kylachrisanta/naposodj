<?php
session_start();
// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
require_once 'config/database.php';
?>
<?php include 'includes/header.php'; ?>

<!-- Sorotan Header -->
<section class="section bg-subtle" style="padding-top: 120px; padding-bottom: 40px;">
    <div class="container text-center">
        <h1 class="section-title">Sorotan Kegiatan</h1>
        <p class="section-subtitle">Kilas balik dan memori kebersamaan Naposo dari tahun ke tahun.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid-3">
            <?php
            $result = $conn->query("SELECT * FROM sorotan ORDER BY tahun DESC, created_at DESC");
            if($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
            ?>
            <div class="card">
                <?php if($row['tipe_media'] == 'foto'): ?>
                    <img src="assets/img/sorotan/<?= htmlspecialchars($row['file_media']) ?>" alt="Sorotan" class="card-img" loading="lazy">
                <?php else: ?>
                    <video src="assets/img/sorotan/<?= htmlspecialchars($row['file_media']) ?>" class="card-img" style="background: black; object-fit: cover;" controls preload="metadata"></video>
                <?php endif; ?>
                
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span class="card-role" style="margin-bottom: 0;">Tahun <?= $row['tahun'] ?></span>
                        <span style="background: <?= $row['tipe_media'] == 'foto' ? 'rgba(37,99,235,0.1)' : 'rgba(245,158,11,0.1)' ?>; color: <?= $row['tipe_media'] == 'foto' ? 'var(--primary)' : 'var(--accent)' ?>; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">
                            <i class="fa-solid <?= $row['tipe_media'] == 'foto' ? 'fa-camera' : 'fa-video' ?>"></i> <?= ucfirst($row['tipe_media']) ?>
                        </span>
                    </div>
                    <h3 class="card-title"><?= htmlspecialchars($row['judul']) ?></h3>
                    <p class="card-text"><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: var(--text-muted); background: white; border-radius: var(--radius-md); border: 1px dashed var(--border-color);">
                <i class="fa-solid fa-camera-retro" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1;"></i><br>
                Belum ada dokumentasi sorotan yang diunggah Admin.
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
