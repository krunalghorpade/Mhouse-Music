<?php
$is_homepage = true;
require_once 'backend/db.php';

// 1. Cover Settings (Video Hero)
try {
    $cover = $pdo->query("SELECT * FROM cover_settings WHERE id = 1")->fetch();
} catch (Exception $e) {
    $cover = null;
}

// Prepare Hero Data
$hero_video = !empty($cover['video_url']) ? $cover['video_url'] : 'assets/videos/website.mov';

// Static Hero Text
$hero_main_text = 'M-House Music is a record label modernizing <span class="highlight">Marathi</span> music in electronic vibes';
$hero_sub_text = '#marathivaajlachpahije';

// 2. Releases (Latest 4)
$sql = "SELECT r.*, GROUP_CONCAT(a.name SEPARATOR ', ') as artist_names 
        FROM releases r 
        LEFT JOIN release_artists ra ON r.id = ra.release_id 
        LEFT JOIN artists a ON ra.artist_id = a.id 
        GROUP BY r.id 
        ORDER BY r.release_date DESC 
        LIMIT 4";
$releases = $pdo->query($sql)->fetchAll();

// 3. Merch (4)
$stmt = $pdo->query("SELECT * FROM merch WHERE status = 'available' ORDER BY created_at DESC LIMIT 4");
$merch = $stmt->fetchAll();

// 4. News (4)
$stmt = $pdo->query("SELECT * FROM news ORDER BY published_date DESC LIMIT 4");
$news = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-HOUSE MUSIC | Kratex</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 3rem;
            margin-top: 6rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .section-header h2 {
            font-size: clamp(2rem, 5vw, 2rem);
        }

        .view-all {
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
        }
    </style>
    <link rel="icon" type="image/png" href="assets/images/icon.png">
</head>

<body>

    <?php include 'header.php'; ?>

    <!-- Video Hero Section -->
    <section class="video-hero">
        <video autoplay muted loop playsinline id="heroVideo">
            <source src="<?php echo htmlspecialchars($hero_video); ?>" type="video/mp4">
            <!-- Fallback for mov if needed, browsers mostly support mp4 now or quicktime -->
            <source src="<?php echo htmlspecialchars($hero_video); ?>" type="video/quicktime">
        </video>
        <div class="video-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title"><?php echo $hero_main_text; // Output raw html for highlight span 
            ?></h1>
            <?php if (!empty($hero_sub_text)): ?>
                <p class="hero-subtitle" style="font-size: 1.2rem; margin-top: 1rem; color: #ccc; letter-spacing: 2px;">
                    <?php echo htmlspecialchars($hero_sub_text); ?>
                </p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Main Content (Reset top margin for homepage flow) -->
    <main class="container" style="margin-top: 0 !important;">

        <!-- 2. Releases -->
        <section>
            <div class="section-header">
                <h2>Releases</h2>
                <a href="releases.php" class="view-all">View All →</a>
            </div>
            <div class="grid">
                <?php foreach ($releases as $release): ?>
                    <a href="release.php?id=<?php echo $release['id']; ?>" class="card">
                        <div class="card-image">
                            <img src="<?php echo htmlspecialchars($release['cover_url']); ?>">
                        </div>
                        <div class="card-info" style="display: block;">
                            <div class="card-title"><?php echo htmlspecialchars($release['title']); ?></div>
                            <div class="artist-name">
                                <?php echo htmlspecialchars($release['artist_names'] ?: 'M-House Music'); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 3. Merch -->
        <section>
            <div class="section-header">
                <h2>Merch</h2>
                <a href="merch.php" class="view-all">Shop All →</a>
            </div>
            <div class="grid">
                <?php foreach ($merch as $item): ?>
                    <a href="<?php echo htmlspecialchars($item['link'] ?? '#'); ?>" target="_blank" class="card">
                        <div class="card-image">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="card-info" style="display: block;">
                            <div class="card-title"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div
                                style="font-size: 0.9rem; color: var(--secondary-text); margin-top: 0.2rem; text-transform: uppercase;">
                                <?php echo htmlspecialchars($item['category'] ?? 'Unisex'); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 4. News -->
        <section>
            <div class="section-header">
                <h2>News</h2>
                <a href="news.php" class="view-all">Read All →</a>
            </div>
            <div class="grid">
                <?php foreach ($news as $item): ?>
                    <div class="card">
                        <div class="card-image aspect-4-5">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>">
                        </div>
                        <div class="card-info" style="display: block;">
                            <div class="card-meta" style="text-align: left; margin-bottom: 0.5rem;">
                                <?php echo date('d M', strtotime($item['published_date'])); ?>
                            </div>
                            <div class="card-title"><?php echo htmlspecialchars($item['title']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>



        <!-- 6. Demo Submission -->
        <!-- 6. Demo Submission -->
        <section style="margin-top: 6rem; padding: 2rem 0; text-align: center;">
            <div style="border-top: 1px solid var(--border-color); width: 50px; margin: 0 auto 2rem auto;"></div>
            <p
                style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; color: var(--secondary-text); margin-bottom: 1rem;">
                Got unreleased music?
            </p>
            <a href="demos.php" class="btn"
                style="background: transparent; border: 1px solid var(--border-color); color: var(--text-color); margin-top: 0; padding: 0.8rem 2rem;">
                Send Demos
            </a>
        </section>

    </main>

    <?php include 'footer.php'; ?>
</body>

</html>