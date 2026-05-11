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
            <a href="#lokasi" class="btn-primary">Lihat Lokasi Kami</a>
        </div>
    </div>
</section>

<!-- Pendeta Section -->
<section class="section bg-subtle">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Pendeta</h2>
            <p class="section-subtitle">Hamba Tuhan yang melayani dan menggembalakan jemaat HKBP Duren Jaya.</p>
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
            <p class="section-subtitle">Periode 2023/2026.</p>
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
<!-- Divisi Section -->
<section class="section bg-subtle" id="divisi-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Divisi Pelayanan</h2>
            <p class="section-subtitle">Periode 2023/2026.</p>
        </div>
        
        <div class="grid-4">
            <?php
            // Grouping divisions manually or by distinct names in DB
            $divisi_list = ['Rohani', 'Padus & Musik', 'Humas', 'Olahraga'];
            $icons = [
                'Rohani' => 'fa-bible',
                'Padus & Musik' => 'fa-music',
                'Humas' => 'fa-users-rays',
                'Olahraga' => 'fa-volleyball'
            ];

            foreach ($divisi_list as $div):
            ?>
            <div class="card proker-card division-card" onclick="openDivisionModal('<?= $div ?>')" style="cursor: pointer;">
                <div class="proker-icon">
                    <i class="fa-solid <?= $icons[$div] ?>"></i>
                </div>
                <h3 class="card-title"><?= $div ?></h3>
                <p class="card-text" style="font-size: 0.85rem;">Klik untuk melihat pengurus dan program kerja.</p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
// Fetch Visi & Misi from DB
$res_settings = $conn->query("SELECT * FROM settings WHERE key_name IN ('visi', 'misi')");
$site_settings = [];
while($s_row = $res_settings->fetch_assoc()) {
    $site_settings[$s_row['key_name']] = $s_row['value_text'];
}
$visi_text = $site_settings['visi'] ?? 'Visi belum diatur.';
$misi_text = $site_settings['misi'] ?? 'Misi belum diatur.';
$misi_points = explode("\n", str_replace("\r", "", $misi_text));
?>
<!-- Visi & Misi Section -->
<section class="section">
    <div class="container">
        <div class="grid-2" style="align-items: center; gap: 60px;">
            <div class="visi-misi-content">
                <h2 class="section-title" style="text-align: left; margin-bottom: 30px;">Visi & Misi Kami</h2>
                <div style="margin-bottom: 25px;">
                    <h3 style="color: var(--primary); margin-bottom: 10px; font-size: 1.4rem;"><i class="fa-solid fa-eye"></i> Visi</h3>
                    <p style="font-size: 1.1rem; color: var(--text-main);"><?= nl2br(htmlspecialchars($visi_text)) ?></p>
                </div>
                <div style="margin-bottom: 40px;">
                    <h3 style="color: var(--primary); margin-bottom: 10px; font-size: 1.4rem;"><i class="fa-solid fa-bullseye"></i> Misi</h3>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach($misi_points as $point): if(trim($point) == '') continue; ?>
                        <li style="margin-bottom: 15px; display: flex; gap: 12px; align-items: flex-start;">
                            <i class="fa-solid fa-check-circle" style="color: var(--accent); margin-top: 6px; font-size: 1.1rem;"></i>
                            <span style="font-size: 1.05rem;"><?= htmlspecialchars(trim($point)) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="auth/register.php" class="btn-primary" style="background: var(--gradient-accent); padding: 18px 45px; font-size: 1.1rem; border-radius: 50px; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 10px 20px -5px rgba(244, 63, 94, 0.4);">
                    <i class="fa-solid fa-user-plus"></i> Gabung Sekarang
                </a>
            </div>
            <div class="visi-misi-image">
                <div style="position: relative;">
                    <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80" alt="Visi Misi" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); width: 100%; height: auto;">
                    <div style="position: absolute; z-index: -1; top: -20px; right: -20px; width: 100%; height: 100%; border: 4px solid var(--accent); border-radius: var(--radius-lg);"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Division Detail Modal -->
<div id="divisionModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeDivisionModal()">&times;</span>
        <div id="modal-body">
            <!-- Content injected by JS -->
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.6);
    backdrop-filter: blur(5px);
    padding-top: 60px;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 40px;
    border: none;
    width: 80%;
    max-width: 1000px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    position: relative;
    animation: slideIn 0.4s ease-out;
}

@keyframes slideIn {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.close-modal {
    color: #aaa;
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 35px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.close-modal:hover {
    color: var(--text-main);
}

.modal-section-title {
    font-size: 1.8rem;
    color: var(--text-main);
    margin-bottom: 40px;
    border-bottom: 3px solid var(--primary);
    display: block;
    width: fit-content;
    margin-left: auto;
    margin-right: auto;
    padding-bottom: 5px;
    text-align: center;
}

.modal-content h3 {
    text-align: center;
    margin-bottom: 25px;
    font-weight: 700;
}

.member-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, 220px);
    justify-content: center;
    gap: 20px;
    margin-bottom: 40px;
}

.member-item {
    text-align: center;
    background: var(--bg-subtle);
    padding: 0;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.member-photo {
    width: 100%;
    height: 220px;
    object-fit: cover;
    margin-bottom: 0;
    border: none;
    box-shadow: none;
    border-radius: 0;
}

.member-info {
    padding: 15px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.proker-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fit, 320px);
    justify-content: center;
    gap: 20px;
}

.proker-item {
    position: relative;
    border-radius: var(--radius-md);
    overflow: hidden;
    height: 200px;
}

.proker-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: 0.5s;
}

.proker-item:hover img {
    transform: scale(1.1);
}

.proker-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    color: white;
    padding: 15px;
    font-size: 0.9rem;
}
</style>

<script>
// Data structure for divisions
const divisionData = {
    <?php
    foreach ($divisi_list as $div) {
        echo "'$div': {";
        
        // Fetch members
        echo "members: [";
        $stmt = $conn->prepare("SELECT * FROM pengurus WHERE kategori = 'Divisi' AND divisi = ? ORDER BY urutan ASC");
        $stmt->bind_param("s", $div);
        $stmt->execute();
        $res_members = $stmt->get_result();
        while($m = $res_members->fetch_assoc()) {
            $photo = $m['foto'] ? 'assets/img/pengurus/' . $m['foto'] : 'https://ui-avatars.com/api/?name=' . urlencode($m['nama']) . '&background=random';
            echo "{nama: '" . addslashes($m['nama']) . "', jabatan: '" . addslashes($m['jabatan']) . "', deskripsi: '" . addslashes($m['deskripsi']) . "', foto: '$photo'},";
        }
        echo "],";
        
        // Fetch program kerja from sorotan
        echo "programs: [";
        $stmt_p = $conn->prepare("SELECT * FROM sorotan WHERE divisi = ? ORDER BY tanggal_kegiatan DESC");
        $stmt_p->bind_param("s", $div);
        $stmt_p->execute();
        $res_proker = $stmt_p->get_result();
        while($p = $res_proker->fetch_assoc()) {
            echo "{judul: '" . addslashes($p['judul']) . "', foto: 'assets/img/sorotan/" . $p['file_media'] . "'},";
        }
        echo "]";
        
        echo "},";
    }
    ?>
};

function openDivisionModal(divName) {
    const data = divisionData[divName];
    if (!data) return;

    let html = `<h2 class="modal-section-title">Divisi ${divName}</h2>`;
    
    // Render Members
    html += `<h3>Pengurus Divisi</h3>`;
    if (data.members.length > 0) {
        html += `<div class="member-grid">`;
        data.members.forEach(m => {
            html += `
                <div class="member-item">
                    <img src="${m.foto}" class="member-photo" alt="${m.nama}">
                    <div class="member-info">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 1.1rem;">${m.nama}</div>
                        <div style="font-size: 0.85rem; color: var(--primary); font-weight: 600; margin-bottom: 8px;">${m.jabatan}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">${m.deskripsi}</div>
                    </div>
                </div>
            `;
        });
        html += `</div>`;
    } else {
        html += `<p style="color: #666; margin-bottom: 30px;">Belum ada data pengurus untuk divisi ini.</p>`;
    }

    // Render Programs
    html += `<h3>Program Kerja & Dokumentasi</h3>`;
    if (data.programs.length > 0) {
        html += `<div class="proker-gallery">`;
        data.programs.forEach(p => {
            html += `
                <div class="proker-item">
                    <img src="${p.foto}" alt="${p.judul}">
                    <div class="proker-overlay">${p.judul}</div>
                </div>
            `;
        });
        html += `</div>`;
    } else {
        html += `<p style="color: #666;">Belum ada data program kerja untuk divisi ini.</p>`;
    }

    document.getElementById('modal-body').innerHTML = html;
    document.getElementById('divisionModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scroll
}

function closeDivisionModal() {
    document.getElementById('divisionModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close on click outside
window.onclick = function(event) {
    const modal = document.getElementById('divisionModal');
    if (event.target == modal) {
        closeDivisionModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
