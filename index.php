<?php 
require_once 'config/database.php';
include 'includes/header.php'; 
?>
<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
/* Elegant Animated Hero Title */
.hero-title {
    min-height: 1.2em; /* Mencegah layout lompat saat animasi */
    white-space: nowrap; /* Memaksa teks sejajar 1 baris */
    display: flex;
    justify-content: center;
    gap: 15px; /* Spasi antar kata */
}
.hero-title .animated-word {
    display: inline-block;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeSequence 8s infinite ease-in-out;
}

@keyframes fadeSequence {
    0%, 5% {
        opacity: 0;
        transform: translateY(20px);
        text-shadow: none;
    }
    15% {
        opacity: 1;
        transform: translateY(0);
        text-shadow: 0 0 20px rgba(255, 255, 255, 0.9), 0 0 40px var(--primary-light, #818cf8);
        color: #ffffff;
    }
    25%, 80% {
        opacity: 1;
        transform: translateY(0);
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.4);
        color: #f8fafc;
    }
    90%, 100% {
        opacity: 0;
        transform: translateY(-15px);
        text-shadow: none;
    }
}
@media (max-width: 1024px) {
    .hero-title { gap: 12px; }
}
@media (max-width: 768px) {
    .hero-title { gap: 8px; font-size: 2.2rem; }
}
@media (max-width: 480px) {
    .hero-title { gap: 6px; font-size: 1.6rem; }
}

/* Animated Subtitle */
.hero-subtitle {
    font-size: 2.2rem; /* Diperbesar namun tetap di bawah ukuran judul */
    font-family: var(--font-heading);
    font-weight: 500;
    letter-spacing: 2px;
    margin-bottom: 40px;
    display: flex;
    justify-content: center;
    flex-wrap: wrap; /* Bisa turun ke bawah jika layar kekecilan */
    gap: 12px;
    color: #e2e8f0;
}
.hero-subtitle .sub-word {
    display: inline-block;
    opacity: 0;
    animation: fadeSequence 8s infinite ease-in-out;
}
@media (max-width: 1024px) {
    .hero-subtitle { font-size: 1.8rem; }
}
@media (max-width: 768px) {
    .hero-subtitle { font-size: 1.4rem; gap: 8px; letter-spacing: 1px; }
}
@media (max-width: 480px) {
    .hero-subtitle { font-size: 1.1rem; gap: 5px; }
}
</style>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <h1 class="hero-title" id="animatedHeroTitle">Naposo HKBP Duren Jaya</h1>
        <p class="hero-subtitle" id="animatedSubtitle">Bertumbuh, Bersaudara, Melayani</p>
    </div>
</section>

<!-- Welcome Marquee Section -->
<div class="sh-marquee-container" id="heroMarquee" onclick="this.classList.toggle('paused')" title="Klik untuk berhenti / lanjutkan">
    <div class="sh-marquee-track">
        <?php 
        $items = ['SELAMAT DATANG DI NAPOSO HKBP DUREN JAYA!'];
        $all = array_fill(0, 12, $items[0]);
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
            $res_fotos = $conn->query("SELECT * FROM foto_beranda ORDER BY id DESC");
            if($res_fotos->num_rows > 0):
                while($row_foto = $res_fotos->fetch_assoc()):
            ?>
            <div class="swiper-slide" style="height: auto; display: flex; justify-content: center;">
                <div class="card" style="width: 100%; max-width: 380px; border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-md); transition: all 0.4s; border: 1px solid var(--border-color); background: white;">
                    <div style="width: 100%; height: 260px; overflow: hidden; position: relative;">
                        <img src="assets/img/beranda/<?= htmlspecialchars($row_foto['file_media']) ?>" alt="<?= htmlspecialchars($row_foto['judul']) ?>" class="card-img" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                    </div>
                    <div class="card-body" style="padding: 25px; text-align: left;">
                        <h3 class="card-title" style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 0; line-height: 1.4;"><?= htmlspecialchars($row_foto['judul']) ?></h3>
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
        // Animasi Hero Title
        const titleElement = document.getElementById('animatedHeroTitle');
        if (titleElement) {
            const text = titleElement.textContent.trim();
            const words = text.split(' ');
            titleElement.innerHTML = ''; // Kosongkan teks asli
            
            words.forEach((word, index) => {
                const span = document.createElement('span');
                span.textContent = word;
                span.className = 'animated-word';
                // Memberikan delay bertahap untuk setiap kata (0.4 detik)
                span.style.animationDelay = `${index * 0.4}s`;
                titleElement.appendChild(span);
            });
        }

        // Animasi Hero Subtitle
        const subtitleElement = document.getElementById('animatedSubtitle');
        if (subtitleElement) {
            const subText = subtitleElement.textContent.trim();
            const subWords = subText.split(' ');
            subtitleElement.innerHTML = ''; 
            
            subWords.forEach((word, index) => {
                const span = document.createElement('span');
                span.textContent = word;
                span.className = 'sub-word';
                // Delay dimulai setelah kata terakhir title (sekitar 1.6s)
                span.style.animationDelay = `${1.6 + (index * 0.4)}s`;
                subtitleElement.appendChild(span);
            });
        }

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
