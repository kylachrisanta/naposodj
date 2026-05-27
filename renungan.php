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
// Fetch user details to prefill commenter name
$user_res = $conn->query("SELECT nama FROM users WHERE id = $user_id");
$user_data = $user_res ? $user_res->fetch_assoc() : ['nama' => ''];

$message = "";
$error = "";
$success = "";

if (isset($_SESSION['renungan_flash_success'])) {
    $success = $_SESSION['renungan_flash_success'];
    unset($_SESSION['renungan_flash_success']);
}
if (isset($_SESSION['renungan_flash_error'])) {
    $error = $_SESSION['renungan_flash_error'];
    unset($_SESSION['renungan_flash_error']);
}

// Handle Comment Posting
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    $renungan_id = (int)$_POST['renungan_id'];
    $isi_komentar = $conn->real_escape_string(trim($_POST['isi_komentar']));
    $nama_komentator = $conn->real_escape_string($user_data['nama']); // Prefilled and secure from logged-in user profile
    
    if (empty($isi_komentar)) {
        $error = "Komentar tidak boleh kosong.";
    } else {
        // Prepare query
        $stmt = $conn->prepare("INSERT INTO komentar_renungan (renungan_id, user_id, nama_komentator, isi_komentar, status_moderasi) VALUES (?, ?, ?, ?, 'Disetujui')");
        $stmt->bind_param("iiss", $renungan_id, $user_id, $nama_komentator, $isi_komentar);
        if ($stmt->execute()) {
            $_SESSION['renungan_flash_success'] = "Komentar berhasil diposting!";
        } else {
            $_SESSION['renungan_flash_error'] = "Terjadi kesalahan saat memposting komentar: " . $conn->error;
        }
    }
    header("Location: renungan.php?id=" . $renungan_id);
    exit();
}

$renungan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<?php include 'includes/header.php'; ?>

<!-- Renungan Header -->
<section class="section bg-subtle" style="padding-top: 120px; padding-bottom: 40px;">
    <div class="container text-center">
        <h1 class="section-title">Renungan Harian</h1>
        <p class="section-subtitle">Luangkan waktu sejenak untuk merenungkan firman Tuhan dan bertumbuh bersama dalam iman.</p>
    </div>
</section>

<!-- Alerts Section -->
<?php if (!empty($success)): ?>
    <div class="container" style="margin-top: 20px; margin-bottom: -10px;">
        <div style="color: #15803d; background: #dcfce7; padding: 15px 20px; border-radius: var(--radius-md); border: 1px solid #86efac; font-weight: 500; display: flex; align-items: center; gap: 10px; animation: fadeIn 0.4s ease;">
            <i class="fa-solid fa-circle-check" style="font-size: 1.15rem;"></i> <?= $success ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="container" style="margin-top: 20px; margin-bottom: -10px;">
        <div style="color: #b91c1c; background: #fee2e2; padding: 15px 20px; border-radius: var(--radius-md); border: 1px solid #fecaca; font-weight: 500; display: flex; align-items: center; gap: 10px; animation: fadeIn 0.4s ease;">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 1.15rem;"></i> <?= $error ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($renungan_id > 0): ?>
    <!-- Detail Renungan -->
    <?php
    $ren_res = $conn->query("SELECT * FROM renungan WHERE id = $renungan_id");
    if ($ren_res->num_rows == 0):
        echo "<script>window.location.href='renungan.php';</script>";
        exit();
    endif;
    $ren = $ren_res->fetch_assoc();
    ?>
    <section class="section" style="padding-top: 40px;">
        <div class="container" style="max-width: 900px;">
            <a href="renungan.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; font-weight: 600; font-size: 0.95rem; margin-bottom: 25px; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Renungan
            </a>
            
            <div style="background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); border: 1px solid var(--border-color); overflow: hidden; padding: 30px; margin-bottom: 45px;">
                <!-- Date, Author -->
                <div style="display: flex; gap: 15px; flex-wrap: wrap; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">
                    <span><i class="fa-regular fa-calendar" style="margin-right: 6px; color: var(--primary);"></i><?= date('d M Y, H:i', strtotime($ren['tanggal_posting'])) ?> WIB</span>
                    <span><i class="fa-regular fa-user" style="margin-right: 6px; color: var(--primary);"></i>Ditulis oleh: <strong><?= htmlspecialchars($ren['penulis']) ?></strong></span>
                </div>
                
                <!-- Title -->
                <h2 style="font-family: var(--font-heading); font-size: 2.2rem; line-height: 1.2; color: var(--text-main); margin-bottom: 20px;"><?= htmlspecialchars($ren['judul']) ?></h2>
                
                <!-- Image -->
                <?php if (!empty($ren['gambar'])): ?>
                    <?php $img_path = 'assets/img/renungan/' . $ren['gambar']; ?>
                    <?php if (file_exists($img_path)): ?>
                        <div style="margin-bottom: 30px; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); cursor: pointer;" class="ren-image-container" onclick="openLightbox('<?= $img_path ?>')">
                            <img src="<?= $img_path ?>" alt="Gambar Renungan" style="width: 100%; height: auto; max-height: 450px; object-fit: cover; transition: transform 0.5s ease;" class="ren-card-img">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Scripture Quote Block -->
                <div style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.05) 0%, rgba(124, 58, 237, 0.05) 100%); border-left: 5px solid var(--primary); padding: 20px 25px; border-radius: 0 var(--radius-md) var(--radius-md) 0; margin-bottom: 30px; box-shadow: var(--shadow-sm);">
                    <i class="fa-solid fa-quote-left" style="font-size: 1.8rem; color: var(--primary-light); opacity: 0.5; display: block; margin-bottom: 8px;"></i>
                    <p style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 600; font-style: italic; color: var(--primary-dark); line-height: 1.5; margin-bottom: 5px;">
                        "<?= htmlspecialchars($ren['ayat_alkitab']) ?>"
                    </p>
                    <span style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); display: block; text-align: right;">- Alkitab</span>
                </div>
                
                <!-- Content -->
                <div style="color: var(--text-main); font-size: 1.05rem; line-height: 1.8; text-align: justify; font-family: var(--font-body); white-space: pre-line;">
                    <?= htmlspecialchars($ren['isi_renungan']) ?>
                </div>
            </div>
            
            <!-- Comment Section -->
            <div style="background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); padding: 30px;">
                <h3 style="font-family: var(--font-heading); font-size: 1.45rem; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px solid var(--bg-subtle); padding-bottom: 15px;">
                    <i class="fa-regular fa-comments" style="color: var(--primary);"></i> Kolom Komentar Jemaat
                </h3>
                
                <!-- Comment list -->
                <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 35px;">
                    <?php
                    $comments = $conn->query("SELECT * FROM komentar_renungan WHERE renungan_id = $renungan_id AND status_moderasi = 'Disetujui' ORDER BY created_at ASC");
                    if ($comments->num_rows > 0):
                        while ($c = $comments->fetch_assoc()):
                    ?>
                    <div style="background: var(--bg-subtle); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color); position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-weight: 700; font-size: 0.95rem; color: var(--primary-dark); display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-circle-user" style="font-size: 1.1rem; color: var(--primary);"></i> <?= htmlspecialchars($c['nama_komentator']) ?>
                            </span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);"><i class="fa-regular fa-clock" style="margin-right: 4px;"></i><?= date('d M Y, H:i', strtotime($c['created_at'])) ?></span>
                        </div>
                        <p style="color: var(--text-main); font-size: 0.95rem; line-height: 1.5; white-space: pre-line; margin-left: 24px;"><?= htmlspecialchars($c['isi_komentar']) ?></p>
                    </div>
                    <?php endwhile; else: ?>
                    <div style="text-align: center; padding: 20px; color: var(--text-muted); font-style: italic; background: var(--bg-subtle); border-radius: var(--radius-md);">Belum ada komentar. Jadilah yang pertama memberikan refleksi rohani!</div>
                    <?php endif; ?>
                </div>
                
                <!-- Add comment Form -->
                <form action="renungan.php" method="POST" style="border-top: 1px solid var(--border-color); padding-top: 25px;">
                    <input type="hidden" name="renungan_id" value="<?= $renungan_id ?>">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Nama Anda</label>
                        <input type="text" value="<?= htmlspecialchars($user_data['nama']) ?>" readonly style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; background-color: #f8fafc; color: var(--text-muted); cursor: not-allowed; outline: none; font-size: 0.95rem;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);">Komentar / Refleksi Rohani</label>
                        <textarea name="isi_komentar" rows="4" required placeholder="Tuliskan komentar, tanggapan, atau kesaksian Anda mengenai renungan ini..." style="width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; font-size: 0.95rem; font-family: var(--font-body); resize: vertical; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'"></textarea>
                    </div>
                    
                    <div style="text-align: right;">
                        <button type="submit" name="submit_comment" class="btn-primary" style="padding: 10px 24px; font-size: 0.95rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; height: auto; cursor: pointer; transform: none; box-shadow: none;">
                            <i class="fa-regular fa-paper-plane"></i> Kirim Komentar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
<?php else: ?>
    <!-- List Renungan -->
    <section class="section" style="padding-top: 40px;">
        <div class="container">
            <div style="display: flex; flex-direction: column; gap: 40px; max-width: 800px; margin: 0 auto;">
                <?php
                $list = $conn->query("SELECT * FROM renungan ORDER BY tanggal_posting DESC");
                if ($list->num_rows > 0):
                    while ($row = $list->fetch_assoc()):
                ?>
                <div class="card" style="background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden; padding: 30px; display: flex; flex-direction: column; gap: 20px;">
                    <!-- Date, Author -->
                    <div style="display: flex; gap: 15px; flex-wrap: wrap; color: var(--text-muted); font-size: 0.85rem;">
                        <span><i class="fa-regular fa-calendar" style="margin-right: 6px; color: var(--primary);"></i><?= date('d M Y, H:i', strtotime($row['tanggal_posting'])) ?> WIB</span>
                        <span><i class="fa-regular fa-user" style="margin-right: 6px; color: var(--primary);"></i>Oleh: <strong><?= htmlspecialchars($row['penulis']) ?></strong></span>
                    </div>

                    <!-- Title -->
                    <h3 style="font-family: var(--font-heading); font-size: 1.8rem; line-height: 1.2; color: var(--text-main); margin: 0;">
                        <a href="renungan.php?id=<?= $row['id'] ?>" style="color: inherit; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'">
                            <?= htmlspecialchars($row['judul']) ?>
                        </a>
                    </h3>

                    <!-- Image -->
                    <?php if (!empty($row['gambar'])): ?>
                        <?php $img_path = 'assets/img/renungan/' . $row['gambar']; ?>
                        <?php if (file_exists($img_path)): ?>
                            <div style="border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); cursor: pointer;" class="ren-image-container" onclick="openLightbox('<?= $img_path ?>')">
                                <img src="<?= $img_path ?>" alt="Gambar Renungan" style="width: 100%; height: auto; max-height: 400px; object-fit: cover; transition: transform 0.5s ease;" class="ren-card-img">
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Scripture Quote Block -->
                    <div style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.05) 0%, rgba(124, 58, 237, 0.05) 100%); border-left: 5px solid var(--primary); padding: 15px 20px; border-radius: 0 var(--radius-md) var(--radius-md) 0; box-shadow: var(--shadow-sm);">
                        <p style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 600; font-style: italic; color: var(--primary-dark); line-height: 1.5; margin: 0;">
                            "<?= htmlspecialchars($row['ayat_alkitab']) ?>"
                        </p>
                    </div>

                    <!-- Content (Full) -->
                    <div style="color: var(--text-main); font-size: 1.02rem; line-height: 1.8; text-align: justify; font-family: var(--font-body); white-space: pre-line; margin: 0;">
                        <?= htmlspecialchars($row['isi_renungan']) ?>
                    </div>

                    <!-- Footer / Comment link -->
                    <div style="border-top: 1px solid var(--border-color); padding-top: 15px; display: flex; justify-content: flex-end;">
                        <?php
                        $comments_count_res = $conn->query("SELECT COUNT(id) AS total FROM komentar_renungan WHERE renungan_id = " . $row['id'] . " AND status_moderasi = 'Disetujui'");
                        $comments_count = $comments_count_res ? $comments_count_res->fetch_assoc()['total'] : 0;
                        ?>
                        <a href="renungan.php?id=<?= $row['id'] ?>" class="btn-secondary" style="padding: 8px 18px; font-size: 0.85rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fa-regular fa-comments"></i> Tanggapan Jemaat (<?= $comments_count ?>)
                        </a>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div style="text-align: center; padding: 40px; color: var(--text-muted); background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">Belum ada renungan rohani saat ini.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Lightbox Modal for Renungan Image Preview -->
<div id="imageLightbox" onclick="closeLightbox(event)" style="display:none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.95); z-index: 10000; backdrop-filter: blur(8px); justify-content: center; align-items: center; padding: 20px; cursor: zoom-out;">
    <div style="position: relative; max-width: 90%; max-height: 90%; cursor: default; display: flex; justify-content: center; align-items: center; animation: fadeIn 0.3s ease;" onclick="event.stopPropagation()">
        <!-- Close Button -->
        <button onclick="closeLightbox(event)" style="position: absolute; top: -50px; right: 0; background: rgba(255,255,255,0.15); border: none; border-radius: 50%; width: 40px; height: 40px; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 1.25rem;" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1.05)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='scale(1)'">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <!-- Preview Image -->
        <img id="lightboxImage" src="" alt="Pratinjau Gambar" style="max-width: 100%; max-height: 85vh; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); display: block; border: 4px solid white; object-fit: contain;">
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
.ren-image-container {
    overflow: hidden;
    position: relative;
}
.ren-image-container:hover .ren-card-img {
    transform: scale(1.015);
}
</style>

<?php include 'includes/footer.php'; ?>
