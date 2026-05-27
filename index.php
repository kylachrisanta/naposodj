<?php 
require_once 'config/database.php';
include 'includes/header.php'; 
?>
<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <h1 class="hero-title">Naposo HKBP Duren Jaya</h1>
        <p class="hero-subtitle">Membangun persekutuan yang berakar dalam Kristus dan berbuah bagi sesama. Mari bertumbuh bersama dalam iman, pengharapan, dan kasih.</p>
    </div>
</section>

<!-- Welcome Marquee Section -->
<div class="sh-marquee-container" id="heroMarquee" onclick="this.classList.toggle('paused')" title="Klik untuk berhenti / lanjutkan">
    <div class="sh-marquee-track">
        <?php 
        $items = ['SELAMAT DATANG DI NAPOSO HKBP DUREN JAYA!'];
        $all = array_fill(0, 10, $items[0]);
        foreach ($all as $item): 
        ?>
            <span class="sh-marquee-item"><?= htmlspecialchars($item) ?></span>
            <span class="sh-marquee-bullet" style="color: var(--accent); font-size: 1.3rem; margin: 0 1.5rem; flex-shrink: 0;">•</span>
        <?php endforeach; ?>
    </div>
</div>

<!-- Galeri Kegiatan Beranda Section -->
<section class="section bg-subtle" id="galeri-kegiatan" style="padding-bottom: 80px; overflow-x: hidden;">
    <div class="container">
        <div class="text-center" style="margin-bottom: 40px;">
            <h2 class="section-title">Kegiatan Kami</h2>
            <p class="section-subtitle" style="margin-bottom: 0;">Berikut adalah beberapa keseruan Naposo HKBP Duren Jaya! :)</p>
        </div>
    </div>
    
    <!-- Swiper Carousel outside container for full-width edge-to-edge layout -->
    <div class="swiper berandaSwiper" style="width: 100%; padding: 20px 50px 60px !important;">
        <div class="swiper-wrapper">
            <?php 
            $res_fotos = $conn->query("SELECT * FROM beranda_foto ORDER BY id DESC");
            if($res_fotos->num_rows > 0):
                while($row_foto = $res_fotos->fetch_assoc()):
            ?>
            <div class="swiper-slide" style="height: auto; display: flex; justify-content: center;">
                <div class="card" style="width: 100%; max-width: 380px; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-md); transition: all 0.4s; border: 1px solid var(--border-color); background: white;">
                    <div style="width: 100%; height: 260px; overflow: hidden; position: relative;">
                        <img src="assets/img/beranda/<?= htmlspecialchars($row_foto['file_foto']) ?>" alt="<?= htmlspecialchars($row_foto['caption']) ?>" class="card-img" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                    </div>
                    <div class="card-body" style="padding: 25px; text-align: left;">
                        <h3 class="card-title" style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 0; line-height: 1.4;"><?= htmlspecialchars($row_foto['caption']) ?></h3>
                    </div>
                </div>
            </div>
            <?php 
                endwhile; 
            else: 
            ?>
            <div style="width: 100%; text-align: center; color: var(--text-muted); padding: 40px;">Belum ada foto kegiatan di Beranda.</div>
            <?php endif; ?>
        </div>
        
        <!-- Navigation Buttons -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        
        <!-- Pagination -->
        <div class="swiper-pagination"></div>
    </div>
</section>

<!-- Activities Marquee Section -->
<section class="activity-marquee-section">
    <!-- Row 1: Leftwards -->
    <div class="act-marquee-container" title="Klik untuk berhenti / lanjutkan">
        <div class="act-marquee-track track-left">
            <?php 
            $activities = [
                ['name' => 'KEBAKTIAN PADANG', 'class' => 'badge-indigo'],
                ['name' => 'RET-RET', 'class' => 'badge-rose'],
                ['name' => 'BADMINTON', 'class' => 'badge-emerald'],
                ['name' => 'BASKET', 'class' => 'badge-amber'],
                ['name' => 'FUTSAL', 'class' => 'badge-sky'],
                ['name' => 'LATIHAN KOOR', 'class' => 'badge-purple'],
                ['name' => 'PENDALAMAN ALKITAB', 'class' => 'badge-indigo'],
                ['name' => 'NONTON BIOSKOP', 'class' => 'badge-rose'],
                ['name' => 'NATAL', 'class' => 'badge-emerald']
            ];
            
            // Repeat array 4 times for width coverage
            $row1_items = array_merge($activities, $activities, $activities, $activities);
            foreach ($row1_items as $item): 
            ?>
                <span class="act-badge <?= $item['class'] ?>"><?= htmlspecialchars($item['name']) ?></span>
            <?php endforeach; ?>
            <?php foreach ($row1_items as $item): ?>
                <span class="act-badge <?= $item['class'] ?>"><?= htmlspecialchars($item['name']) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Row 2: Rightwards -->
    <div class="act-marquee-container" title="Klik untuk berhenti / lanjutkan">
        <div class="act-marquee-track track-right">
            <?php 
            $activities_rev = array_reverse($activities);
            $row2_items = array_merge($activities_rev, $activities_rev, $activities_rev, $activities_rev);
            foreach ($row2_items as $item): 
            ?>
                <span class="act-badge <?= $item['class'] ?>"><?= htmlspecialchars($item['name']) ?></span>
            <?php endforeach; ?>
            <?php foreach ($row2_items as $item): ?>
                <span class="act-badge <?= $item['class'] ?>"><?= htmlspecialchars($item['name']) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Selamat Datang Section -->
<section class="section" id="selamat-datang">
    <div class="container" style="max-width: 800px; text-align: center;">
        <div class="welcome-content">
            <h2 class="section-title" style="margin-bottom: 30px;">Syalom!</h2>
            <p style="font-size: 1.15rem; color: var(--text-main); margin-bottom: 20px; line-height: 1.8; font-weight: 500;">
                Yuk, bergabung dengan persekutuan Naposo HKBP Duren Jaya.
            </p>
            <p style="color: var(--text-muted); font-size: 1.1rem; line-height: 1.8; margin-bottom: 0;">
                Di sini kita bisa seru-seruan bareng, menambah teman, ikut berbagai kegiatan, dan bertumbuh bersama di dalam kasih Tuhan. Bersama kita belajar, melayani, dan menciptakan banyak momen yang menyenangkan.
            </p>
        </div>
    </div>
</section>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new Swiper('.berandaSwiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: false,
            grabCursor: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
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
            }
        });

        // Click-to-pause event listener for activities marquee
        const marqueeContainers = document.querySelectorAll('.act-marquee-container');
        marqueeContainers.forEach(container => {
            container.addEventListener('click', () => {
                container.classList.toggle('paused');
            });
        });
    });
</script>
<?php include 'includes/footer.php'; ?>
