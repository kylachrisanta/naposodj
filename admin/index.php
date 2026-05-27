<?php 
require_once '../includes/admin_header.php'; 
require_once '../includes/admin_sidebar.php'; 

// Fetch statistik dari database
$count_pengurus = $conn->query("SELECT COUNT(id) AS total FROM pengurus")->fetch_assoc()['total'];
$count_kegiatan = $conn->query("SELECT COUNT(id) AS total FROM kegiatan")->fetch_assoc()['total'];
$count_warta    = $conn->query("SELECT COUNT(id) AS total FROM warta")->fetch_assoc()['total'];
$count_users    = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc()['total'];

// Fetch statistik gender
$count_male = $conn->query("SELECT COUNT(id) AS total FROM users WHERE jenis_kelamin = 'Laki-laki'")->fetch_assoc()['total'];
$count_female = $conn->query("SELECT COUNT(id) AS total FROM users WHERE jenis_kelamin = 'Perempuan'")->fetch_assoc()['total'];
$total_genders = $count_male + $count_female;
$pct_male = $total_genders > 0 ? round(($count_male / $total_genders) * 100, 1) : 0;
$pct_female = $total_genders > 0 ? round(($count_female / $total_genders) * 100, 1) : 0;
?>

<div style="margin-bottom: 30px;">
    <h2>Dashboard</h2>
    <p style="color: var(--text-muted);">Selamat datang di Panel Manajemen Naposo HKBP Duren Jaya.</p>
</div>

<!-- Statistik Cards -->
<div class="dash-grid">
    <div class="dash-card">
        <div class="dash-icon bg-blue">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="dash-info">
            <h4>Total Pengurus</h4>
            <h2><?= $count_pengurus ?></h2>
        </div>
    </div>
    
    <div class="dash-card">
        <div class="dash-icon bg-green">
            <i class="fa-regular fa-calendar-check"></i>
        </div>
        <div class="dash-info">
            <h4>Jadwal Kegiatan</h4>
            <h2><?= $count_kegiatan ?></h2>
        </div>
    </div>
    
    <div class="dash-card">
        <div class="dash-icon bg-amber">
            <i class="fa-solid fa-bullhorn"></i>
        </div>
        <div class="dash-info">
            <h4>Warta Jemaat</h4>
            <h2><?= $count_warta ?></h2>
        </div>
    </div>
    
    <div class="dash-card">
        <div class="dash-icon bg-purple">
            <i class="fa-solid fa-user-check"></i>
        </div>
        <div class="dash-info">
            <h4>Akun Terdaftar</h4>
            <h2><?= $count_users ?></h2>
        </div>
    </div>
</div>

<style>
    .demografi-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        align-items: stretch;
    }
    @media (max-width: 768px) {
        .demografi-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Seksi Demografi Pengguna -->
<div style="margin-top: 40px; margin-bottom: 20px;">
    <h3 style="font-family: var(--font-heading); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 1.3rem;">
        <i class="fa-solid fa-venus-mars" style="color: var(--primary);"></i> Demografi Pengguna
    </h3>
    
    <div class="demografi-container">
        <!-- Left: Chart & Breakdown -->
        <div style="background: white; padding: 25px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h4 style="font-family: var(--font-heading); font-size: 1.05rem; color: var(--text-main); margin-bottom: 8px;">Persentase Jenis Kelamin</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 25px;">Perbandingan persentase jumlah pengguna laki-laki dan perempuan yang terdaftar.</p>
            </div>
            
            <div>
                <!-- Bar Chart -->
                <div style="height: 24px; width: 100%; background: #e2e8f0; border-radius: 12px; overflow: hidden; display: flex; box-shadow: inset 0 2px 4px rgba(0,0,0,0.06); margin-bottom: 20px;">
                    <?php if ($total_genders > 0): ?>
                        <div style="width: <?= $pct_male ?>%; background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%); height: 100%; transition: width 0.5s ease;" title="Laki-laki: <?= $pct_male ?>%"></div>
                        <div style="width: <?= $pct_female ?>%; background: linear-gradient(90deg, #ec4899 0%, #f472b6 100%); height: 100%; transition: width 0.5s ease;" title="Perempuan: <?= $pct_female ?>%"></div>
                    <?php else: ?>
                        <div style="width: 100%; background: #cbd5e1; height: 100%; text-align: center; color: #64748b; font-size: 0.8rem; line-height: 24px;">Belum ada data jenis kelamin</div>
                    <?php endif; ?>
                </div>
                
                <!-- Legend -->
                <div style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: flex-start; font-size: 0.9rem;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #3b82f6;"></span>
                        <span style="font-weight: 500; color: var(--text-main);">Laki-laki: <?= $count_male ?> (<?= $pct_male ?>%)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: #ec4899;"></span>
                        <span style="font-weight: 500; color: var(--text-main);">Perempuan: <?= $count_female ?> (<?= $pct_female ?>%)</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right: Stats cards vertical grid -->
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <div style="background: white; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 15px;">
                <div style="width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: white; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                    <i class="fa-solid fa-mars"></i>
                </div>
                <div>
                    <h4 style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Laki-laki</h4>
                    <h2 style="font-size: 1.5rem; margin-top: 2px; color: #0f172a; font-family: var(--font-heading);"><?= $count_male ?> <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: normal;">org</span></h2>
                </div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 15px;">
                <div style="width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: white; background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);">
                    <i class="fa-solid fa-venus"></i>
                </div>
                <div>
                    <h4 style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Perempuan</h4>
                    <h2 style="font-size: 1.5rem; margin-top: 2px; color: #0f172a; font-family: var(--font-heading);"><?= $count_female ?> <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: normal;">org</span></h2>
                </div>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 15px;">
                <div style="width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: white; background: var(--gradient-primary);">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <h4 style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">Total Pengguna</h4>
                    <h2 style="font-size: 1.5rem; margin-top: 2px; color: #0f172a; font-family: var(--font-heading);"><?= $count_users ?> <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: normal;">org</span></h2>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/admin_footer.php'; ?>
