<?php 
require_once 'config/database.php';
include 'includes/header.php'; 
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <h1 class="hero-title">Naposo HKBP Duren Jaya</h1>
        <p class="hero-subtitle">Membangun persekutuan yang berakar dalam Kristus dan berbuah bagi sesama. Mari bertumbuh bersama dalam iman, pengharapan, dan kasih.</p>
        <div class="hero-buttons">
            <a href="info.php" class="btn-primary">Lihat Jadwal Ibadah</a>
            <a href="sejarah.php" class="btn-outline">Kenali Sejarah Kami</a>
        </div>
    </div>
</section>

<!-- Pendeta Section -->
<section class="section bg-subtle">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Pendeta Kami</h2>
            <p class="section-subtitle">Mengenal lebih dekat hamba Tuhan yang melayani dan menggembalakan jemaat HKBP Duren Jaya.</p>
        </div>
        
        <div class="grid-2">
            <?php
            $res_pendeta = $conn->query("SELECT * FROM pengurus WHERE kategori = 'Pendeta' ORDER BY id ASC");
            if($res_pendeta->num_rows > 0):
                while($row = $res_pendeta->fetch_assoc()):
            ?>
            <div class="card">
                <?php if($row['foto']): ?>
                    <img src="assets/img/pengurus/<?= $row['foto'] ?>" alt="<?= htmlspecialchars($row['nama']) ?>" class="card-img" style="object-fit: cover; height: 350px;">
                <?php else: ?>
                    <div class="card-img" style="height: 350px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                        <i class="fa-solid fa-user-tie fa-5x"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h3 class="card-title"><?= htmlspecialchars($row['nama']) ?></h3>
                    <div class="card-role"><?= htmlspecialchars($row['jabatan']) ?></div>
                    <p class="card-text"><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></p>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 40px; background: white; border-radius: var(--radius-md);">Data profil pendeta belum tersedia di database.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- BPI Section -->
<section class="section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Badan Pengurus Inti (BPI)</h2>
            <p class="section-subtitle">Pemuda-pemudi yang dipercaya untuk mengkoordinir pergerakan pelayanan Naposo HKBP Duren Jaya periode ini.</p>
        </div>
        
        <div class="grid-3">
            <?php
            $res_bpi = $conn->query("SELECT * FROM pengurus WHERE kategori = 'BPI' ORDER BY id ASC");
            if($res_bpi->num_rows > 0):
                while($row = $res_bpi->fetch_assoc()):
            ?>
            <div class="card">
                <?php if($row['foto']): ?>
                    <img src="assets/img/pengurus/<?= $row['foto'] ?>" alt="<?= htmlspecialchars($row['nama']) ?>" class="card-img" style="object-fit: cover; height: 300px;">
                <?php else: ?>
                    <div class="card-img" style="height: 300px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                        <i class="fa-solid fa-user fa-5x"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body text-center">
                    <h3 class="card-title"><?= htmlspecialchars($row['nama']) ?></h3>
                    <div class="card-role"><?= htmlspecialchars($row['jabatan']) ?></div>
                    <?php if($row['deskripsi']): ?>
                        <p class="card-text" style="font-size: 0.85rem; margin-top: 10px;"><?= htmlspecialchars($row['deskripsi']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 40px; background: var(--bg-subtle); border-radius: var(--radius-md);">Data BPI belum tersedia di database.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Program Kerja & Divisi Section -->
<section class="section bg-subtle">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Bidang Pelayanan & Ketua Divisi</h2>
            <p class="section-subtitle">Setiap pemuda bebas berekspresi dan melayani melalui berbagai divisi sesuai dengan talenta yang Tuhan berikan.</p>
        </div>
        
        <div class="grid-4">
            <?php
            $res_divisi = $conn->query("SELECT * FROM pengurus WHERE kategori = 'Divisi' ORDER BY id ASC");
            if($res_divisi->num_rows > 0):
                while($row = $res_divisi->fetch_assoc()):
            ?>
            <div class="card proker-card">
                <div class="proker-icon" style="overflow: hidden; border-radius: 50%; width: 80px; height: 80px; margin: 0 auto 15px; border: 3px solid white; box-shadow: var(--shadow-sm);">
                    <?php if($row['foto']): ?>
                        <img src="assets/img/pengurus/<?= $row['foto'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white;">
                            <i class="fa-solid fa-users-gear" style="font-size: 1.5rem;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h3 class="card-title" style="font-size: 1.1rem;"><?= htmlspecialchars($row['jabatan']) ?></h3>
                <div style="font-weight: 600; color: var(--primary); font-size: 0.9rem; margin-bottom: 5px;"><?= htmlspecialchars($row['nama']) ?></div>
                <p class="card-text" style="font-size: 0.85rem;"><?= htmlspecialchars($row['deskripsi']) ?></p>
            </div>
            <?php endwhile; else: ?>
            <!-- Fallback Static jika belum ada data divisi di DB -->
            <div class="card proker-card">
                <div class="proker-icon"><i class="fa-solid fa-bible"></i></div>
                <h3 class="card-title">Divisi Rohani</h3>
                <p class="card-text">Mengadakan Penelaahan Alkitab (PA), Retret, dan membina kerohanian Naposo.</p>
            </div>
            <div class="card proker-card">
                <div class="proker-icon"><i class="fa-solid fa-users-rays"></i></div>
                <h3 class="card-title">Divisi Humas</h3>
                <p class="card-text">Mengelola informasi, media sosial, dan menjalin hubungan eksternal.</p>
            </div>
            <div class="card proker-card">
                <div class="proker-icon"><i class="fa-solid fa-music"></i></div>
                <h3 class="card-title">Padus & Musik</h3>
                <p class="card-text">Mengkoordinir pelayanan paduan suara, pemusik (band), dan kantoria.</p>
            </div>
            <div class="card proker-card">
                <div class="proker-icon"><i class="fa-solid fa-volleyball"></i></div>
                <h3 class="card-title">Olahraga & Seni</h3>
                <p class="card-text">Melatih fisik dan kebersamaan melalui olahraga badminton, futsal, dan seni.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
