<?php
session_start();
// Auth Check: Lempar pengguna ke login jika belum ada sesi user
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}
// Mengambil config database
require_once 'config/database.php';

$user_id = (int)$_SESSION['user_id'];
?>
<?php include 'includes/header.php'; ?>

<!-- Kegiatan Header -->
<section class="section bg-subtle" style="padding-top: 120px; padding-bottom: 40px;">
    <div class="container text-center">
        <h1 class="section-title">Kegiatan</h1>
        <p class="section-subtitle">Jadwal kegiatan dalam seminggu untuk Naposo HKBP Duren Jaya.</p>
    </div>
</section>

<!-- Jadwal Kegiatan -->
<section id="kegiatan" class="section" style="padding-top: 40px; padding-bottom: 60px;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="font-family: var(--font-heading); font-size: 1.75rem;"><i class="fa-regular fa-calendar-check" style="color: var(--primary);"></i> Jadwal Kegiatan Terdekat</h2>
        </div>
        
        <div class="timeline-container">
            <div class="timeline">
                <?php
                $result_keg = $conn->query("SELECT * FROM kegiatan WHERE tanggal >= CURDATE() ORDER BY tanggal ASC");
                if($result_keg->num_rows > 0):
                    $no = 1;
                    while($row_keg = $result_keg->fetch_assoc()):
                ?>
                <div class="timeline-item">
                    <div class="timeline-badge"><?= $no++ ?></div>
                    <div class="timeline-content">
                        <h3 class="kegiatan-title"><?= htmlspecialchars($row_keg['nama_kegiatan']) ?></h3>
                        <div class="kegiatan-meta">
                            <span><i class="fa-regular fa-clock"></i> <?= date('d M Y, H:i', strtotime($row_keg['tanggal'])) ?> WIB</span>
                            <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($row_keg['tempat']) ?></span>
                            <span><i class="fa-solid fa-user"></i> PIC: <?= htmlspecialchars($row_keg['penanggung_jawab']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div style="text-align: center; padding: 40px; color: var(--text-muted); background: white; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                    <i class="fa-regular fa-calendar-xmark" style="font-size: 3rem; color: var(--border-color); margin-bottom: 15px; display: block;"></i>
                    Belum ada jadwal kegiatan terdekat saat ini.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.timeline-container {
    max-width: 800px;
    margin: 0 auto;
}
.timeline {
    position: relative;
    padding: 20px 0;
}
.timeline::after {
    content: '';
    position: absolute;
    width: 4px;
    background-color: #e2e8f0;
    top: 0;
    bottom: 0;
    left: 28px;
    margin-left: -2px;
    border-radius: 4px;
}
.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 80px;
}
.timeline-item:last-child {
    margin-bottom: 0;
}
.timeline-badge {
    position: absolute;
    width: 36px;
    height: 36px;
    background-color: white;
    border: 3px solid var(--primary);
    color: var(--primary);
    font-weight: bold;
    border-radius: 50%;
    left: 10px;
    top: 0;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 0 0 0 4px var(--bg-subtle);
}
.timeline-content {
    background: white;
    padding: 25px;
    border-radius: var(--radius-md);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    border: 1px solid var(--border-color);
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}
.timeline-content:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
    border-color: var(--primary-light);
}
.kegiatan-title {
    font-family: var(--font-heading);
    font-size: 1.3rem;
    color: var(--text-main);
    margin-bottom: 15px;
    margin-top: 0;
}
.kegiatan-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    color: var(--text-muted);
    font-size: 0.9rem;
}
.kegiatan-meta span {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    padding: 8px 14px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
.kegiatan-meta i {
    color: var(--primary);
}
@media (max-width: 768px) {
    .timeline::after {
        left: 20px;
    }
    .timeline-item {
        padding-left: 60px;
    }
    .timeline-badge {
        width: 30px;
        height: 30px;
        font-size: 0.85rem;
        left: 5px;
        top: 3px;
    }
    .timeline-content {
        padding: 20px;
    }
    .kegiatan-meta {
        flex-direction: column;
        gap: 8px;
    }
    .kegiatan-meta span {
        display: flex;
        width: 100%;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
