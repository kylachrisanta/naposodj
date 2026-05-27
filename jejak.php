<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'config/database.php';

// Fetch all jejak data grouped by category
$query = "SELECT * FROM jejak ORDER BY kategori ASC, tahun DESC, created_at DESC";
$result = $conn->query($query);
$jejak_by_category = [];
while($row = $result->fetch_assoc()) {
    $jejak_by_category[$row['kategori']][] = $row;
}
?>
<?php include 'includes/header.php'; ?>

<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
    .jejak-page {
        padding: 140px 0 80px;
        background: #f8fafc;
        min-height: 100vh;
        overflow-x: hidden;
    }

    .jejak-header {
        text-align: center;
        margin-bottom: 60px;
    }

    .jejak-header h1 {
        font-family: var(--font-heading);
        font-size: 3rem;
        color: #1e293b;
        margin-bottom: 10px;
        font-weight: 800;
    }

    .category-section {
        margin-bottom: 80px;
        position: relative;
    }

    .category-title {
        font-family: var(--font-heading);
        font-size: 2.2rem;
        color: #0f172a;
        margin-bottom: 30px;
        padding-left: 50px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .category-title::before {
        content: '';
        width: 8px;
        height: 40px;
        background: var(--gradient-primary);
        border-radius: 4px;
    }

    .category-section:nth-child(odd) .category-title::before {
        background: var(--gradient-accent);
    }

    /* Swiper custom styles */
    .swiper {
        width: 100%;
        padding: 20px 50px 80px !important; 
    }

    .swiper-slide {
        height: auto;
        display: flex;
        justify-content: center;
    }

    .jejak-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
        max-width: 400px;
        border: 1px solid rgba(0,0,0,0.03);
    }

    .jejak-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }

    .card-media {
        width: 100%;
        height: 300px;
        overflow: hidden;
        position: relative;
    }

    .card-media img, .card-media video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .jejak-card:hover .card-media img {
        transform: scale(1.08);
    }

    .card-info {
        padding: 25px;
    }

    .card-info h3 {
        font-family: var(--font-heading);
        font-size: 1.4rem;
        color: #0f172a;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .card-info p {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .card-meta {
        margin-top: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary);
    }

    /* Navigation Arrows */
    .swiper-button-next, .swiper-button-prev {
        width: 50px;
        height: 50px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        color: #0f172a !important;
        transition: all 0.3s;
        z-index: 10;
    }

    .swiper-button-next:after, .swiper-button-prev:after {
        font-size: 1.2rem !important;
        font-weight: 900;
    }

    .swiper-button-next:hover, .swiper-button-prev:hover {
        background: var(--primary);
        color: white !important;
        transform: scale(1.1);
    }

    /* Pagination */
    .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
        background: #cbd5e1;
        opacity: 1;
        transition: all 0.3s;
    }

    .swiper-pagination-bullet-active {
        width: 30px;
        border-radius: 5px;
        background: #0f172a;
    }

    /* Video Play Icon */
    .video-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(5px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .swiper {
            padding: 20px 20px 60px !important;
        }
        .swiper-button-next, .swiper-button-prev {
            display: none;
        }
        .jejak-header h1 {
            font-size: 2rem;
        }
        .category-title {
            padding-left: 20px;
            font-size: 1.8rem;
        }
    }

    /* Lightbox Modal */
    .lightbox {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        padding: 40px;
    }

    .lightbox.active {
        display: flex;
    }

    .lightbox-content {
        max-width: 100%;
        max-height: 100%;
        position: relative;
    }

    .lightbox-content img, .lightbox-content video {
        max-width: 100%;
        max-height: 90vh;
        border-radius: 8px;
    }

    .lightbox-close {
        position: absolute;
        top: -40px;
        right: 0;
        color: white;
        font-size: 2rem;
        cursor: pointer;
    }

    .lightbox-info {
        color: white;
        margin-top: 20px;
        text-align: center;
    }

    .lightbox-info h3 {
        color: white;
        font-size: 1.5rem;
        margin-bottom: 10px;
    }
</style>

<div class="jejak-page">
    <div class="jejak-header">
        <h1>Jejak Naposo</h1>
        <p style="color: #64748b; font-size: 1.1rem;">Rekam jejak perjalanan Naposo HKBP Duren Jaya dalam melayani dan berkarya.</p>
    </div>

    <?php if(empty($jejak_by_category)): ?>
        <div class="text-center" style="width: 100%; padding: 100px 0;">
            <i class="fa-solid fa-folder-open" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 20px;"></i>
            <p style="color: #64748b;">Belum ada rekam jejak yang diunggah.</p>
        </div>
    <?php else: ?>
        <?php foreach($jejak_by_category as $kategori => $items): ?>
            <div class="category-section" id="<?= strtolower($kategori) ?>">
                <div class="sh-marquee-container" onclick="this.classList.toggle('paused')" title="Klik untuk berhenti / lanjutkan" style="margin-bottom: 35px; border-left: none; border-right: none;">
                    <div class="sh-marquee-track">
                        <?php 
                        $cat_upper = strtoupper($kategori);
                        $all_items = array_fill(0, 15, $cat_upper);
                        foreach ($all_items as $item): 
                        ?>
                            <span class="sh-marquee-item"><?= htmlspecialchars($item) ?></span>
                            <span class="sh-marquee-bullet" style="color: var(--accent); font-size: 1.3rem; margin: 0 1.5rem; flex-shrink: 0;">•</span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="swiper categorySwiper">
                    <div class="swiper-wrapper">
                        <?php foreach($items as $item): ?>
                            <div class="swiper-slide">
                                <div class="jejak-card" onclick="openLightbox('<?= $item['file_media'] ?>', '<?= $item['tipe_media'] ?>', '<?= addslashes(htmlspecialchars($item['judul'])) ?>', '<?= addslashes(htmlspecialchars($item['deskripsi'])) ?>')" style="cursor: pointer;">
                                    <div class="card-media">
                                        <?php if($item['tipe_media'] == 'foto'): ?>
                                            <img src="assets/img/jejak/<?= $item['file_media'] ?>" alt="<?= htmlspecialchars($item['judul']) ?>">
                                        <?php else: ?>
                                            <video src="assets/img/jejak/<?= $item['file_media'] ?>"></video>
                                            <div class="video-overlay"><i class="fa-solid fa-play"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-info">
                                        <h3><?= htmlspecialchars($item['judul']) ?></h3>
                                        <p><?= mb_strimwidth(htmlspecialchars($item['deskripsi']), 0, 100, "...") ?></p>
                                        <div class="card-meta">
                                            <span>Tahun <?= $item['tahun'] ?></span>
                                            <span>•</span>
                                            <span><?= $kategori ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Add Pagination -->
                    <div class="swiper-pagination"></div>
                    
                    <!-- Add Navigation -->
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <div id="lightboxMedia"></div>
        <div class="lightbox-info">
            <h3 id="lightboxTitle"></h3>
            <p id="lightboxDesc"></p>
        </div>
    </div>
</div>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- Initialize Swipers -->
<script>
    document.querySelectorAll('.categorySwiper').forEach(el => {
        new Swiper(el, {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: false,
            centeredSlides: false,
            grabCursor: true,
            pagination: {
                el: el.querySelector('.swiper-pagination'),
                clickable: true,
            },
            navigation: {
                nextEl: el.querySelector('.swiper-button-next'),
                prevEl: el.querySelector('.swiper-button-prev'),
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
                1440: {
                    slidesPerView: 4,
                },
            },
        });
    });

    function openLightbox(file, type, title, desc) {
        const lightbox = document.getElementById('lightbox');
        const mediaContainer = document.getElementById('lightboxMedia');
        const titleEl = document.getElementById('lightboxTitle');
        const descEl = document.getElementById('lightboxDesc');

        mediaContainer.innerHTML = '';
        if (type === 'foto') {
            mediaContainer.innerHTML = `<img src="assets/img/jejak/${file}" alt="${title}">`;
        } else {
            mediaContainer.innerHTML = `<video src="assets/img/jejak/${file}" controls autoplay></video>`;
        }

        titleEl.innerText = title;
        descEl.innerText = desc;
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        const lightbox = document.getElementById('lightbox');
        lightbox.classList.remove('active');
        document.body.style.overflow = 'auto';
        document.getElementById('lightboxMedia').innerHTML = '';
    }
</script>

<?php include 'includes/footer.php'; ?>
