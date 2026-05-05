<?php
require_once 'includes/auth_middleware.php';
check_auth();
require_once 'config/database.php';

// Fetch all years and their content counts
$years_query = "SELECT tahun, COUNT(*) as total FROM sorotan GROUP BY tahun ORDER BY tahun DESC";
$years_result = $conn->query($years_query);

// Fetch all content grouped by year for the Story Viewer
$all_content = [];
$content_query = "SELECT * FROM sorotan ORDER BY tahun DESC, tanggal_kegiatan ASC";
$content_result = $conn->query($content_query);
while($row = $content_result->fetch_assoc()) {
    $all_content[$row['tahun']][] = [
        'id' => $row['id'],
        'judul' => $row['judul'],
        'deskripsi' => $row['deskripsi'],
        'tanggal' => date('d M Y', strtotime($row['tanggal_kegiatan'])),
        'tipe' => $row['tipe_media'],
        'file' => 'assets/img/sorotan/' . $row['file_media']
    ];
}
?>
<?php include 'includes/header.php'; ?>

<style>
    :root {
        --ig-gradient: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    }

    .sorotan-page {
        padding-top: 120px;
        min-height: 80vh;
        background: #fafafa;
    }

    /* Highlight Circles */
    .highlights-container {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        justify-content: center;
        padding: 40px 0;
    }

    .highlight-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
        width: 120px;
        transition: transform 0.2s ease;
    }

    .highlight-item:hover {
        transform: scale(1.05);
    }

    .highlight-ring {
        width: 100px;
        height: 100px;
        padding: 3px;
        background: var(--ig-gradient);
        border-radius: 50%;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .highlight-inner {
        width: 100%;
        height: 100%;
        background: white;
        border-radius: 50%;
        padding: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .highlight-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        background: #eee;
    }

    .highlight-label {
        font-weight: 600;
        font-size: 0.95rem;
        color: #262626;
    }

    .highlight-count {
        font-size: 0.75rem;
        color: #8e8e8e;
    }

    /* Story Viewer Modal */
    #storyViewer {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    #storyViewer.active {
        display: flex;
        opacity: 1;
    }

    .story-container {
        position: relative;
        width: 100%;
        max-width: 450px;
        height: 90vh;
        background: #1a1a1a;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 0 50px rgba(0,0,0,0.5);
        transform: scale(0.9);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #storyViewer.active .story-container {
        transform: scale(1);
    }

    /* Progress Bars */
    .story-progress-container {
        position: absolute;
        top: 15px;
        left: 10px;
        right: 10px;
        display: flex;
        gap: 5px;
        z-index: 10;
    }

    .progress-bar {
        height: 2px;
        background: rgba(255, 255, 255, 0.3);
        flex-grow: 1;
        border-radius: 2px;
        overflow: hidden;
    }

    .progress-inner {
        height: 100%;
        background: white;
        width: 0%;
        transition: width linear;
    }

    /* Story Header */
    .story-header {
        position: absolute;
        top: 30px;
        left: 15px;
        right: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 10;
        color: white;
    }

    .story-user {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .story-year-badge {
        background: white;
        color: black;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .story-date {
        font-size: 0.85rem;
        opacity: 0.8;
    }

    .story-close {
        font-size: 1.5rem;
        cursor: pointer;
        padding: 5px;
    }

    /* Story Content */
    .story-content-area {
        flex-grow: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .story-media {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .story-nav {
        position: absolute;
        top: 0;
        height: 100%;
        width: 30%;
        z-index: 5;
        cursor: pointer;
    }

    .story-nav-prev { left: 0; }
    .story-nav-next { right: 0; width: 70%; }

    /* Story Footer (Info) */
    .story-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 40px 20px 30px;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        color: white;
        z-index: 10;
    }

    .story-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .story-desc {
        font-size: 0.9rem;
        opacity: 0.9;
        line-height: 1.4;
    }

    /* Controls Overlay */
    .story-controls {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 4rem;
        color: white;
        opacity: 0;
        transition: opacity 0.2s;
        pointer-events: none;
        z-index: 20;
    }

    @media (max-width: 500px) {
        .story-container {
            width: 100%;
            height: 100%;
            border-radius: 0;
            max-width: none;
        }
    }
</style>

<div class="sorotan-page">
    <div class="container">
        <div class="text-center" style="margin-bottom: 40px;">
            <h1 class="section-title">Sorotan Naposo</h1>
            <p class="section-subtitle">Klik lingkaran tahun untuk melihat rangkuman kegiatan dalam format cerita interaktif.</p>
        </div>

        <div class="highlights-container">
            <?php if($years_result->num_rows > 0): ?>
                <?php while($year = $years_result->fetch_assoc()): ?>
                    <?php 
                        // Find the first photo for thumbnail, or use a placeholder
                        $thumb = 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?auto=format&fit=crop&w=200&q=80';
                        foreach($all_content[$year['tahun']] as $item) {
                            if($item['tipe'] == 'foto') {
                                $thumb = $item['file'];
                                break;
                            }
                        }
                    ?>
                    <div class="highlight-item" onclick="openStory(<?= $year['tahun'] ?>)">
                        <div class="highlight-ring">
                            <div class="highlight-inner">
                                <img src="<?= $thumb ?>" class="highlight-thumb" alt="Thumbnail">
                            </div>
                        </div>
                        <span class="highlight-label"><?= $year['tahun'] ?></span>
                        <span class="highlight-count"><?= $year['total'] ?> Konten</span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center" style="padding: 100px 0; width: 100%;">
                    <i class="fa-solid fa-folder-open" style="font-size: 3rem; color: #ccc; margin-bottom: 20px;"></i>
                    <p style="color: #888;">Belum ada sorotan yang diunggah.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Story Viewer Modal -->
<div id="storyViewer">
    <div class="story-container">
        <!-- Progress Bars -->
        <div class="story-progress-container" id="progressBarContainer">
            <!-- Bars will be injected by JS -->
        </div>

        <!-- Header -->
        <div class="story-header">
            <div class="story-user">
                <span class="story-year-badge" id="storyYearBadge">2025</span>
                <span class="story-date" id="storyDate">24 Apr 2026</span>
            </div>
            <div class="story-close" onclick="closeStory()">
                <i class="fa-solid fa-xmark"></i>
            </div>
        </div>

        <!-- Media Area -->
        <div class="story-content-area" id="storyContentArea">
            <div class="story-nav story-nav-prev" onclick="prevContent()"></div>
            <div class="story-nav story-nav-next" onclick="nextContent()"></div>
            <div id="mediaPlaceholder" style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;">
                <!-- Media injected by JS -->
            </div>
        </div>

        <!-- Footer -->
        <div class="story-footer">
            <h3 class="story-title" id="storyTitle">Judul Kegiatan</h3>
            <p class="story-desc" id="storyDesc">Deskripsi singkat kegiatan...</p>
        </div>

        <!-- Pause/Play Indicator -->
        <div class="story-controls" id="playPauseIcon">
            <i class="fa-solid fa-pause"></i>
        </div>
    </div>
</div>

<script>
    const allContent = <?= json_stringify($all_content) ?>;
    let currentYearContent = [];
    let currentIndex = 0;
    let storyTimer;
    let isPaused = false;
    const STORY_DURATION = 5000; // 5 seconds for photos

    function openStory(year) {
        currentYearContent = allContent[year];
        currentIndex = 0;
        document.getElementById('storyYearBadge').innerText = year;
        
        // Setup Progress Bars
        const pbContainer = document.getElementById('progressBarContainer');
        pbContainer.innerHTML = '';
        currentYearContent.forEach((_, i) => {
            const bar = document.createElement('div');
            bar.className = 'progress-bar';
            bar.innerHTML = '<div class="progress-inner"></div>';
            pbContainer.appendChild(bar);
        });

        const viewer = document.getElementById('storyViewer');
        viewer.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scroll

        showContent(0);
    }

    function closeStory() {
        const viewer = document.getElementById('storyViewer');
        viewer.classList.remove('active');
        document.body.style.overflow = 'auto';
        clearTimeout(storyTimer);
        
        // Stop any playing video
        const media = document.querySelector('.story-media');
        if(media && media.tagName === 'VIDEO') media.pause();
    }

    function showContent(index) {
        if (index < 0 || index >= currentYearContent.length) {
            closeStory();
            return;
        }

        currentIndex = index;
        const item = currentYearContent[index];
        const mediaPlaceholder = document.getElementById('mediaPlaceholder');
        
        // Update Info
        document.getElementById('storyDate').innerText = item.tanggal;
        document.getElementById('storyTitle').innerText = item.judul;
        document.getElementById('storyDesc').innerText = item.deskripsi;

        // Clear timer
        clearTimeout(storyTimer);

        // Update Progress Bars State
        const bars = document.querySelectorAll('.progress-inner');
        bars.forEach((bar, i) => {
            bar.style.transition = 'none';
            if (i < index) bar.style.width = '100%';
            else bar.style.width = '0%';
        });

        // Inject Media
        if (item.tipe === 'foto') {
            mediaPlaceholder.innerHTML = `<img src="${item.file}" class="story-media" alt="Story">`;
            startTimer(STORY_DURATION);
        } else {
            mediaPlaceholder.innerHTML = `<video src="${item.file}" class="story-media" autoplay playsinline id="storyVideo"></video>`;
            const video = document.getElementById('storyVideo');
            video.onloadedmetadata = () => {
                startTimer(video.duration * 1000);
            };
            video.onended = nextContent;
        }
    }

    function startTimer(duration) {
        const bar = document.querySelectorAll('.progress-inner')[currentIndex];
        bar.style.transition = `width ${duration}ms linear`;
        
        // Request reflow to trigger transition
        void bar.offsetWidth;
        bar.style.width = '100%';

        storyTimer = setTimeout(nextContent, duration);
    }

    function nextContent() {
        if (currentIndex + 1 < currentYearContent.length) {
            showContent(currentIndex + 1);
        } else {
            closeStory();
        }
    }

    function prevContent() {
        if (currentIndex > 0) {
            showContent(currentIndex - 1);
        } else {
            showContent(0);
        }
    }

    // Pause/Play on hold
    const contentArea = document.getElementById('storyContentArea');
    let holdTimeout;

    contentArea.onmousedown = contentArea.ontouchstart = (e) => {
        holdTimeout = setTimeout(() => {
            isPaused = true;
            pauseStory();
        }, 200);
    };

    contentArea.onmouseup = contentArea.onmouseleave = contentArea.ontouchend = () => {
        clearTimeout(holdTimeout);
        if (isPaused) {
            isPaused = false;
            resumeStory();
        }
    };

    function pauseStory() {
        clearTimeout(storyTimer);
        const bar = document.querySelectorAll('.progress-inner')[currentIndex];
        const computedStyle = window.getComputedStyle(bar);
        const width = computedStyle.getPropertyValue('width');
        bar.style.transition = 'none';
        bar.style.width = width;
        
        const video = document.getElementById('storyVideo');
        if(video) video.pause();

        showPlayPauseIcon('pause');
    }

    function resumeStory() {
        const bar = document.querySelectorAll('.progress-inner')[currentIndex];
        const currentWidth = parseFloat(bar.style.width);
        const containerWidth = bar.parentElement.offsetWidth;
        const remainingPercent = 1 - (currentWidth / containerWidth);
        
        const item = currentYearContent[currentIndex];
        let remainingTime;

        if (item.tipe === 'foto') {
            remainingTime = STORY_DURATION * remainingPercent;
        } else {
            const video = document.getElementById('storyVideo');
            remainingTime = (video.duration - video.currentTime) * 1000;
            video.play();
        }

        bar.style.transition = `width ${remainingTime}ms linear`;
        bar.style.width = '100%';
        storyTimer = setTimeout(nextContent, remainingTime);

        showPlayPauseIcon('play');
    }

    function showPlayPauseIcon(type) {
        const icon = document.getElementById('playPauseIcon');
        icon.innerHTML = type === 'pause' ? '<i class="fa-solid fa-pause"></i>' : '<i class="fa-solid fa-play"></i>';
        icon.style.opacity = '0.5';
        setTimeout(() => icon.style.opacity = '0', 500);
    }

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        const viewer = document.getElementById('storyViewer');
        if (!viewer.classList.contains('active')) return;

        if (e.key === 'ArrowRight') nextContent();
        if (e.key === 'ArrowLeft') prevContent();
        if (e.key === 'Escape') closeStory();
    });
</script>

<?php 
function json_stringify($data) {
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
}
?>

<?php include 'includes/footer.php'; ?>
