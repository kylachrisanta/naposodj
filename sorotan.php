<?php
require_once 'includes/auth_middleware.php';
check_auth();
require_once 'config/database.php';

// Fetch all sorotan data grouped by year
$query = "SELECT * FROM sorotan ORDER BY tahun DESC, tanggal_kegiatan DESC";
$result = $conn->query($query);
$sorotan_by_year = [];
while($row = $result->fetch_assoc()) {
    $sorotan_by_year[$row['tahun']][] = $row;
}
?>
<?php include 'includes/header.php'; ?>

<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
    .sorotan-page {
        padding: 140px 0 80px;
        background: #f8fafc;
        min-height: 100vh;
        overflow-x: hidden;
    }

    .sorotan-header {
        text-align: center;
        margin-bottom: 60px;
    }

    .sorotan-header h1 {
        font-family: var(--font-heading);
        font-size: 3rem;
        color: #1e293b;
        margin-bottom: 10px;
        font-weight: 800;
        position: relative;
        display: inline-block;
        padding-bottom: 15px;
    }

    .sorotan-header h1::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 5px;
        background: var(--gradient-primary);
        border-radius: 4px;
    }

    .year-section {
        margin-bottom: 80px;
        position: relative;
    }

    .year-title {
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

    .year-title::before {
        content: '';
        width: 8px;
        height: 40px;
        background: var(--gradient-primary);
        border-radius: 4px;
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

    .sorotan-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
        max-width: 400px;
        border: 1px solid rgba(0,0,0,0.03);
    }

    .sorotan-card:hover {
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

    .sorotan-card:hover .card-media img {
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
        .sorotan-header h1 {
            font-size: 2rem;
        }
        .year-title {
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
    .search-container {
        max-width: 300px;
        margin: 30px auto 0;
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 12px 20px 12px 45px;
        border-radius: 999px;
        border: 2px solid #e2e8f0;
        background: white;
        font-family: var(--font-body);
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-sm);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1rem;
    }

    /* Animation for sections */
    .year-section {
        transition: opacity 0.4s ease, transform 0.4s ease;
    }

    .section-hidden {
        display: none;
        opacity: 0;
        transform: translateY(20px);
    }
</style>

<div class="sorotan-page">
    <div class="sorotan-header">
        <h1>Sorotan Naposo</h1>
        <p style="color: #64748b; font-size: 1.1rem;">Rekap kegiatan Naposo pertahun.</p>
        
        <div class="search-container">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="yearSearch" class="search-input" placeholder="Cari tahun (contoh: 2024)" maxlength="4">
        </div>
    </div>

    <!-- Empty search result state -->
    <div id="searchEmptyState" class="text-center" style="display: none; padding: 100px 0;">
        <i class="fa-solid fa-calendar-xmark" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 20px;"></i>
        <p style="color: #64748b;">Tidak ada sorotan di tahun tersebut.</p>
    </div>

    <?php if(empty($sorotan_by_year)): ?>
        <div class="text-center" style="width: 100%; padding: 100px 0;">
            <i class="fa-solid fa-camera-retro" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 20px;"></i>
            <p style="color: #64748b;">Belum ada dokumentasi sorotan.</p>
        </div>
    <?php else: ?>
        <?php foreach($sorotan_by_year as $tahun => $items): ?>
            <div class="year-section">
                <h2 class="year-title">Tahun <?= $tahun ?></h2>
                
                <div class="swiper yearSwiper" id="swiper-<?= $tahun ?>">
                    <div class="swiper-wrapper">
                        <?php foreach($items as $item): ?>
                            <div class="swiper-slide">
                                <div class="sorotan-card" onclick="openLightbox('<?= $item['file_media'] ?>', '<?= addslashes(htmlspecialchars($item['judul'])) ?>', '<?= addslashes(htmlspecialchars($item['deskripsi'])) ?>')" style="cursor: pointer;">
                                    <div class="card-media">
                                        <img src="assets/img/sorotan/<?= $item['file_media'] ?>" alt="<?= htmlspecialchars($item['judul']) ?>">
                                    </div>
                                    <div class="card-info">
                                        <h3><?= htmlspecialchars($item['judul']) ?></h3>
                                        <p><?= mb_strimwidth(htmlspecialchars($item['deskripsi']), 0, 100, "...") ?></p>
                                        <div class="card-meta">
                                            <span><?= date('d M Y', strtotime($item['tanggal_kegiatan'])) ?></span>
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
    document.querySelectorAll('.yearSwiper').forEach(el => {
        new Swiper(el, {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: false,
            centeredSlides: false,
            grabCursor: true,
            observer: true,
            observeParents: true,
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

    function openLightbox(file, title, desc) {
        const lightbox = document.getElementById('lightbox');
        const mediaContainer = document.getElementById('lightboxMedia');
        const titleEl = document.getElementById('lightboxTitle');
        const descEl = document.getElementById('lightboxDesc');

        mediaContainer.innerHTML = `<img src="assets/img/sorotan/${file}" alt="${title}">`;

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

    // Search Filtering Logic
    const searchInput = document.getElementById('yearSearch');
    const sections = document.querySelectorAll('.year-section');
    const emptyState = document.getElementById('searchEmptyState');
    const originalEmptyState = document.querySelector('.text-center:not(#searchEmptyState)');

    searchInput.addEventListener('input', function(e) {
        // Hanya izinkan angka
        this.value = this.value.replace(/[^0-9]/g, '');
        
        const query = this.value.trim();
        let foundAny = false;

        sections.forEach(section => {
            const yearTitle = section.querySelector('.year-title').textContent.toLowerCase();
            if (yearTitle.includes(query)) {
                section.style.display = 'block';
                setTimeout(() => {
                    section.style.opacity = '1';
                    section.style.transform = 'translateY(0)';
                }, 10);
                foundAny = true;
            } else {
                section.style.display = 'none';
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
            }
        });

        // Toggle empty states
        if (!foundAny && query !== '') {
            emptyState.style.display = 'block';
            if(originalEmptyState) originalEmptyState.style.display = 'none';
        } else {
            emptyState.style.display = 'none';
            if(originalEmptyState && sections.length === 0) originalEmptyState.style.display = 'block';
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
