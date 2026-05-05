<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';
?>
<?php include 'includes/header.php'; ?>

<!-- Jejak Header -->
<section class="section bg-subtle" style="padding-top: 120px; padding-bottom: 40px;">
    <div class="container text-center">
        <h1 class="section-title">Jejak Kami</h1>
        <p class="section-subtitle">Rekam jejak, prestasi, dan partisipasi Naposo HKBP Duren Jaya dalam berbagai kegiatan pelayanan maupun perlombaan.</p>
    </div>
</section>

<!-- Prestasi Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title text-center" style="font-size: 2rem;"><i class="fa-solid fa-trophy" style="color: var(--accent);"></i> Prestasi</h2>
        <div class="grid-2" style="margin-top: 40px;">
            <?php
            $res_prestasi = $conn->query("SELECT * FROM jejak WHERE kategori='Prestasi' ORDER BY tahun DESC, created_at DESC");
            if($res_prestasi->num_rows > 0):
                while($row = $res_prestasi->fetch_assoc()):
            ?>
            <div class="card">
                <?php if($row['tipe_media'] == 'foto'): ?>
                    <img src="assets/img/jejak/<?= htmlspecialchars($row['file_media']) ?>" alt="Prestasi" class="card-img" loading="lazy" style="height: 400px; object-fit: cover;">
                <?php else: ?>
                    <video src="assets/img/jejak/<?= htmlspecialchars($row['file_media']) ?>" class="card-img" style="background: black; object-fit: cover; height: 400px;" controls preload="metadata"></video>
                <?php endif; ?>
                <div class="card-body">
                    <div class="card-role">Tahun <?= $row['tahun'] ?></div>
                    <h3 class="card-title"><?= htmlspecialchars($row['judul']) ?></h3>
                    <p class="card-text"><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted); background: white; border-radius: var(--radius-md); border: 1px dashed var(--border-color);">Belum ada rekam jejak prestasi yang diunggah.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Partisipasi Section -->
<section class="section bg-subtle">
    <div class="container">
        <h2 class="section-title text-center" style="font-size: 2rem;"><i class="fa-solid fa-handshake-angle" style="color: var(--primary);"></i> Partisipasi</h2>
        <div class="grid-2" style="margin-top: 40px;">
            <?php
            $res_partisipasi = $conn->query("SELECT * FROM jejak WHERE kategori='Partisipasi' ORDER BY tahun DESC, created_at DESC");
            if($res_partisipasi->num_rows > 0):
                while($row = $res_partisipasi->fetch_assoc()):
            ?>
            <div class="card">
                <?php if($row['tipe_media'] == 'foto'): ?>
                    <img src="assets/img/jejak/<?= htmlspecialchars($row['file_media']) ?>" alt="Partisipasi" class="card-img" loading="lazy" style="height: 400px; object-fit: cover;">
                <?php else: ?>
                    <video src="assets/img/jejak/<?= htmlspecialchars($row['file_media']) ?>" class="card-img" style="background: black; object-fit: cover; height: 400px;" controls preload="metadata"></video>
                <?php endif; ?>
                <div class="card-body">
                    <div class="card-role">Tahun <?= $row['tahun'] ?></div>
                    <h3 class="card-title"><?= htmlspecialchars($row['judul']) ?></h3>
                    <p class="card-text"><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted); background: white; border-radius: var(--radius-md); border: 1px dashed var(--border-color);">Belum ada rekam jejak partisipasi yang diunggah.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
