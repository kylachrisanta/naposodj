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
// Fetch user details to prefill
$user_res = $conn->query("SELECT nama, email, whatsapp FROM users WHERE id = $user_id");
$user_data = $user_res ? $user_res->fetch_assoc() : ['nama' => '', 'email' => '', 'whatsapp' => ''];

$error = "";
$success = "";

if (isset($_SESSION['warta_flash_success'])) {
    $success = $_SESSION['warta_flash_success'];
    unset($_SESSION['warta_flash_success']);
}
if (isset($_SESSION['warta_flash_error'])) {
    $error = $_SESSION['warta_flash_error'];
    unset($_SESSION['warta_flash_error']);
}


?>
<?php include 'includes/header.php'; ?>

<!-- Warta Header -->
<section class="section bg-subtle" style="padding-top: 120px; padding-bottom: 40px;">
    <div class="container text-center">
        <h1 class="section-title">Warta</h1>
        <p class="section-subtitle">Informasi, pengumuman, dan kegiatan yang dipublikasikan.</p>
    </div>
</section>


<!-- Warta Pengumuman -->
<section id="warta" class="section bg-subtle" style="padding-top: 20px; padding-bottom: 60px;">
    <div class="container">
        
        <!-- Alerts Section -->
        <?php if (!empty($success)): ?>
            <div style="margin-bottom: 25px; color: #15803d; background: #dcfce7; padding: 16px 20px; border-radius: var(--radius-md); border: 1px solid #86efac; font-weight: 500; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm); animation: fadeIn 0.4s ease;" id="alert-success">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-circle-check" style="font-size: 1.25rem;"></i> <?= $success ?>
                </div>
                <button onclick="document.getElementById('alert-success').style.display='none'" style="background: transparent; border: none; color: #15803d; cursor: pointer; font-size: 1.1rem; padding: 4px; display: flex; align-items: center; justify-content: center; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div style="margin-bottom: 25px; color: #b91c1c; background: #fee2e2; padding: 16px 20px; border-radius: var(--radius-md); border: 1px solid #fecaca; font-weight: 500; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm); animation: fadeIn 0.4s ease;" id="alert-error">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-circle-exclamation" style="font-size: 1.25rem;"></i> <?= $error ?>
                </div>
                <button onclick="document.getElementById('alert-error').style.display='none'" style="background: transparent; border: none; color: #b91c1c; cursor: pointer; font-size: 1.1rem; padding: 4px; display: flex; align-items: center; justify-content: center; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        <?php endif; ?>
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php
            $result_warta = $conn->query("SELECT w.*, (SELECT COUNT(*) FROM pendaftaran_warta pw WHERE pw.warta_id = w.id AND pw.status_pembayaran != 'Ditolak') as total_pendaftar FROM warta w ORDER BY tanggal_posting DESC");
            $warta_colors = ['var(--primary)', 'var(--accent)', '#10b981', '#8b5cf6'];
            $color_idx = 0;
            
            if($result_warta->num_rows > 0):
                while($row_warta = $result_warta->fetch_assoc()):
                    // Rotasi warna border kiri untuk estetika
                    $border_color = $warta_colors[$color_idx % count($warta_colors)];
                    $color_idx++;
                    
                    // Check if current user is registered
                    $check_reg = $conn->query("SELECT status_pembayaran FROM pendaftaran_warta WHERE warta_id = {$row_warta['id']} AND user_id = $user_id");
                    $is_registered = false;
                    $reg_status = '';
                    if ($check_reg && $check_reg->num_rows > 0) {
                        $is_registered = true;
                        $reg_status_row = $check_reg->fetch_assoc();
                        $reg_status = $reg_status_row['status_pembayaran'];
                    }
            ?>
            <div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border-left: 4px solid <?= $border_color ?>;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                    <div style="color: var(--text-muted); font-size: 0.875rem;"><i class="fa-regular fa-clock"></i> Diposting pada <?= date('d M Y', strtotime($row_warta['tanggal_posting'])) ?></div>
                    
                </div>
                
                <?php if (!empty($row_warta['gambar'])): ?>
                    <?php $warta_img_path = 'assets/img/warta/' . $row_warta['gambar']; ?>
                    <?php if (file_exists($warta_img_path)): ?>
                        <div style="margin-bottom: 20px; border-radius: var(--radius-sm); overflow: hidden; box-shadow: var(--shadow-sm); transition: transform 0.3s ease, box-shadow 0.3s ease;" class="warta-image-container" onclick="openLightbox('<?= $warta_img_path ?>')">
                            <img src="<?= $warta_img_path ?>" alt="<?= htmlspecialchars($row_warta['judul']) ?>" style="width: 100%; height: auto; max-height: 380px; object-fit: cover; transition: transform 0.5s ease;" class="warta-card-img">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <h3 style="font-family: var(--font-heading); font-size: 1.25rem; margin-bottom: 10px;"><?= htmlspecialchars($row_warta['judul']) ?></h3>
                <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 20px;"><?= nl2br(htmlspecialchars($row_warta['isi_pengumuman'])) ?></p>
                
                <?php
                // Fetch stats for this warta
                $reaksi_res = $conn->query("SELECT emoticon, COUNT(*) as count FROM warta_reaksi WHERE warta_id = {$row_warta['id']} GROUP BY emoticon");
                $stats = [];
                while($r = $reaksi_res->fetch_assoc()){
                    $stats[$r['emoticon']] = $r['count'];
                }
                
                // Check user's current reaction
                $user_reaksi_res = $conn->query("SELECT emoticon FROM warta_reaksi WHERE warta_id = {$row_warta['id']} AND user_id = $user_id");
                $user_reaction = ($user_reaksi_res && $user_reaksi_res->num_rows > 0) ? $user_reaksi_res->fetch_assoc()['emoticon'] : null;
                ?>
                <div class="reaction-container" data-warta-id="<?= $row_warta['id'] ?>" style="display: flex; align-items: center; gap: 10px; margin-bottom: <?= $row_warta['butuh_pendaftaran'] ? '15px' : '0' ?>; position: relative;">
                    <div class="reaction-trigger" style="position: relative; display: inline-block;">
                        <button class="btn-reaction-trigger" style="background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: 999px; padding: 6px 12px; cursor: pointer; color: var(--text-muted); display: flex; align-items: center; gap: 6px; font-size: 0.9rem; transition: all 0.2s;" onmouseover="this.nextElementSibling.style.display='flex';" onmouseout="this.nextElementSibling.style.display='none';">
                            <i class="fa-regular fa-face-smile"></i> Suka
                        </button>
                        <div class="reaction-popover" style="display: none; position: absolute; bottom: 100%; left: 0; background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); padding: 8px 12px; gap: 8px; margin-bottom: 10px; z-index: 10;" onmouseover="this.style.display='flex';" onmouseout="this.style.display='none';">
                            <?php 
                            $emoticons = ['👍', '❤️', '🙏', '😂', '😮', '😢'];
                            foreach ($emoticons as $emoticon): 
                                $isActive = ($user_reaction === $emoticon) ? 'background: #f1f5f9; transform: scale(1.1);' : '';
                            ?>
                                <button class="emoticon-btn" onclick="sendReaction(<?= $row_warta['id'] ?>, '<?= $emoticon ?>')" style="background: transparent; border: none; font-size: 1.5rem; cursor: pointer; transition: transform 0.2s; padding: 4px; border-radius: 50%; <?= $isActive ?>" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='<?= $user_reaction === $emoticon ? 'scale(1.1)' : 'scale(1)' ?>'">
                                    <?= $emoticon ?>
                                </button>
                            <?php endforeach; ?>
                            <button class="emoticon-btn emoji-plus-btn" onclick="toggleEmojiPicker(<?= $row_warta['id'] ?>, event)" style="background: #f1f5f9; border: none; font-size: 1.2rem; cursor: pointer; transition: transform 0.2s; padding: 4px; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; margin-left: 4px;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                <i class="fa-solid fa-plus" style="color: var(--text-muted);"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="reaction-stats" id="stats-<?= $row_warta['id'] ?>" style="display: flex; gap: 6px; flex-wrap: wrap;">
                        <?php foreach($stats as $emo => $count): if($count > 0): ?>
                            <span class="reaction-badge" style="background: white; border: 1px solid var(--border-color); border-radius: 999px; padding: 2px 8px; font-size: 0.8rem; display: flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); <?= ($user_reaction === $emo) ? 'border-color: var(--primary); background: #f8fafc;' : '' ?>">
                                <?= $emo ?> <?= $count ?>
                            </span>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
                
                <?php if ($row_warta['butuh_pendaftaran']): ?>
                    <div style="border-top: 1px solid var(--border-color); padding-top: 15px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <?php if ($is_registered): ?>
                                <?php
                                $badge_bg = ''; $badge_fg = ''; $status_txt = '';
                                if ($reg_status == 'Lunas') {
                                    $badge_bg = '#dcfce7'; $badge_fg = '#15803d'; $status_txt = 'Terdaftar (Pembayaran Lunas)';
                                } elseif ($reg_status == 'Menunggu Verifikasi') {
                                    $badge_bg = '#fef3c7'; $badge_fg = '#d97706'; $status_txt = 'Terdaftar (Menunggu Verifikasi)';
                                } elseif ($reg_status == 'Bayar di Tempat') {
                                    $badge_bg = '#dbeafe'; $badge_fg = '#1d4ed8'; $status_txt = 'Terdaftar (Bayar di Tempat)';
                                } elseif ($reg_status == 'Terdaftar') {
                                    $badge_bg = '#dcfce7'; $badge_fg = '#15803d'; $status_txt = 'Terdaftar';
                                } elseif ($reg_status == 'Ditolak') {
                                    $badge_bg = '#fee2e2'; $badge_fg = '#b91c1c'; $status_txt = 'Pendaftaran Ditolak';
                                }
                                ?>
                                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: <?= $badge_bg ?>; color: <?= $badge_fg ?>; border-radius: 6px; font-weight: 600; font-size: 0.9rem;">
                                    <i class="fa-solid fa-circle-check"></i> <?= $status_txt ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!$is_registered || $reg_status == 'Ditolak'): ?>
                            <a href="daftar_kegiatan.php?id=<?= $row_warta['id'] ?>" class="btn-primary" style="padding: 8px 20px; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; text-decoration: none;">
                                <i class="fa-solid fa-user-plus"></i> Daftar Kegiatan
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endwhile; else: ?>
            <div style="text-align: center; padding: 40px; color: var(--text-muted); background: white; border-radius: var(--radius-md);">Belum ada warta jemaat saat ini.</div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Global Emoji Picker -->
<div id="globalEmojiPickerContainer" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10001; box-shadow: var(--shadow-lg); border-radius: 8px;">
    <emoji-picker></emoji-picker>
</div>

<!-- Lightbox Modal for Warta Image Preview -->
<div id="imageLightbox" onclick="closeLightbox(event)" style="display:none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.95); z-index: 10000; backdrop-filter: blur(8px); justify-content: center; align-items: center; padding: 20px; cursor: zoom-out;">
    <div style="position: relative; max-width: 90%; max-height: 90%; cursor: default; display: flex; justify-content: center; align-items: center; animation: fadeIn 0.3s ease;" onclick="event.stopPropagation()">
        <!-- Close Button -->
        <button onclick="closeLightbox(event)" style="position: absolute; top: -50px; right: 0; background: rgba(255,255,255,0.15); border: none; border-radius: 50%; width: 40px; height: 40px; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 1.25rem;" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='scale(1)'">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <!-- Preview Image -->
        <img id="lightboxImage" src="" alt="Pratinjau Gambar Warta" style="max-width: 100%; max-height: 85vh; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); display: block; border: 4px solid white; object-fit: contain;">
    </div>
</div>

<script>
function openLightbox(imageSrc) {
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImg = document.getElementById('lightboxImage');
    lightboxImg.src = imageSrc;
    lightbox.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeLightbox(event) {
    const lightbox = document.getElementById('imageLightbox');
    lightbox.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modals on Escape
window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLightbox();
    }
});

// Warta Reaction AJAX
function sendReaction(wartaId, emoticon) {
    fetch('ajax/warta_reaction.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ warta_id: wartaId, emoticon: emoticon })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const statsContainer = document.getElementById('stats-' + wartaId);
            statsContainer.innerHTML = '';
            
            for (const [emo, count] of Object.entries(data.stats)) {
                if (count > 0) {
                    const isActive = (data.user_reaction === emo);
                    const style = isActive ? 'border-color: var(--primary); background: #f8fafc;' : '';
                    statsContainer.innerHTML += `
                        <span class="reaction-badge" style="background: white; border: 1px solid var(--border-color); border-radius: 999px; padding: 2px 8px; font-size: 0.8rem; display: flex; align-items: center; gap: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); ${style}">
                            ${emo} ${count}
                        </span>
                    `;
                }
            }
            
            const popover = document.querySelector(`.reaction-container[data-warta-id="${wartaId}"] .reaction-popover`);
            if (popover) {
                const btns = popover.querySelectorAll('.emoticon-btn:not(.emoji-plus-btn)');
                btns.forEach(btn => {
                    const isBtnActive = (btn.innerText.trim() === data.user_reaction);
                    if (isBtnActive) {
                        btn.style.background = '#f1f5f9';
                        btn.style.transform = 'scale(1.1)';
                        btn.onmouseout = function() { this.style.transform='scale(1.1)'; };
                    } else {
                        btn.style.background = 'transparent';
                        btn.style.transform = 'scale(1)';
                        btn.onmouseout = function() { this.style.transform='scale(1)'; };
                    }
                });
            }
        }
    })
    .catch(error => console.error('Fetch error:', error));
}

let currentWartaIdForPicker = null;

function toggleEmojiPicker(wartaId, event) {
    event.stopPropagation();
    currentWartaIdForPicker = wartaId;
    
    const pickerContainer = document.getElementById('globalEmojiPickerContainer');
    if (pickerContainer.style.display === 'block') {
        pickerContainer.style.display = 'none';
        return;
    }
    pickerContainer.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', () => {
    const picker = document.querySelector('emoji-picker');
    if (picker) {
        picker.addEventListener('emoji-click', event => {
            if (currentWartaIdForPicker !== null) {
                sendReaction(currentWartaIdForPicker, event.detail.unicode);
                document.getElementById('globalEmojiPickerContainer').style.display = 'none';
            }
        });
    }

    document.addEventListener('click', (e) => {
        const pickerContainer = document.getElementById('globalEmojiPickerContainer');
        if (pickerContainer && pickerContainer.style.display === 'block' && !pickerContainer.contains(e.target) && !e.target.closest('.emoji-plus-btn')) {
            pickerContainer.style.display = 'none';
        }
    });
});
</script>

<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<style>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.warta-image-container {
    overflow: hidden;
    position: relative;
    cursor: pointer;
}
.warta-image-container:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.warta-image-container:hover .warta-card-img {
    transform: scale(1.02);
}
</style>

<?php include 'includes/footer.php'; ?>
