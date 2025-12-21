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

// 2. Releases (Latest 8)
$sql = "SELECT r.*, GROUP_CONCAT(a.name SEPARATOR ', ') as artist_names 
        FROM releases r 
        LEFT JOIN release_artists ra ON r.id = ra.release_id 
        LEFT JOIN artists a ON ra.artist_id = a.id 
        GROUP BY r.id 
        ORDER BY r.release_date DESC 
        LIMIT 8";
$releases = $pdo->query($sql)->fetchAll();

// 3. Merch (4)
$stmt = $pdo->query("SELECT * FROM merch WHERE status = 'available' ORDER BY created_at DESC LIMIT 4");
$merch = $stmt->fetchAll();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <?php include 'header.php'; ?>

    <!-- Video Hero Section -->
    <section class="video-hero">
        <video autoplay muted loop playsinline id="heroVideo">
            <source src="<?php echo htmlspecialchars($hero_video); ?>" type="video/mp4">
            <source src="<?php echo htmlspecialchars($hero_video); ?>" type="video/quicktime">
        </video>
        <div class="video-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title" id="glitchText"
                data-original="<?php echo htmlspecialchars(strip_tags($hero_main_text)); ?>">
                <?php echo $hero_main_text; ?>
            </h1>
            <?php if (!empty($hero_sub_text)): ?>
                <p class="hero-subtitle" style="font-size: 1.2rem; margin-top: 1rem; color: #ccc; letter-spacing: 2px;">
                    <?php echo htmlspecialchars($hero_sub_text); ?>
                </p>
            <?php endif; ?>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const textElement = document.getElementById('glitchText');
            // Store the HTML version to keep the span if possible, or just raw text. 
            // The user wants specific text for the glitch.
            const originalHTML = textElement.innerHTML;
            const marathiText = "M-House Music हे एक रेकॉर्ड लेबल आहे जे मराठी संगीताला आधुनिक इलेक्ट्रॉनिक वाइब्समध्ये (Electronic Vibes) रूपांतरित करत आहे";

            setInterval(() => {
                // 1. Trigger Glitch / Change to Marathi
                textElement.style.opacity = 0;
                setTimeout(() => {
                    textElement.innerText = marathiText;
                    textElement.style.opacity = 1;

                    // 2. Wait 2 seconds, then revert
                    setTimeout(() => {
                        textElement.style.opacity = 0;
                        setTimeout(() => {
                            textElement.innerHTML = originalHTML;
                            textElement.style.opacity = 1;
                        }, 100); // short fade
                    }, 2000);

                }, 100);
            }, 9000);
            // User: "every 7 seconds for 2 seconds". 
            // Value: 7000ms interval. 
            // But if I set interval to 7000, it will happen every 7s.

        });
    </script>

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


        <!-- Subscribe Block -->
        <section
            style="margin-top: 6rem; padding: 4rem 2rem; background: #111; text-align: center; border-radius: 8px;">
            <h2 style="font-size: clamp(1.5rem, 3vw, 2.5rem); margin-bottom: 1rem;">Join the Movement</h2>
            <p style="color: var(--secondary-text); margin-bottom: 2rem;">Subscribe for the latest releases, merch
                drops, and exclusive events.</p>
            <form action="#" style="max-width: 500px; margin: 0 auto; display: flex; gap: 1rem; flex-wrap: wrap;">
                <input type="email" placeholder="YOUR@EMAIL.COM" required
                    style="flex: 1; min-width: 250px; padding: 1rem; background: #000; border: 1px solid #333; color: #fff; outline: none; text-transform: uppercase;">
                <button type="submit"
                    style="padding: 1rem 2rem; background: #fff; color: #000; border: none; font-weight: 700; text-transform: uppercase; cursor: pointer;">Subscribe</button>
            </form>
        </section>

        <!-- 6. Demo Submission -->
        <section style="margin-top: 4rem; padding: 1rem 0; text-align: center;">
            <p
                style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--secondary-text); margin-bottom: 0.5rem;">
                Got unreleased music?
            </p>
            <a href="demos.php"
                style="text-decoration: underline; font-size: 0.8rem; text-transform: uppercase; color: var(--text-color);">
                Send Demos
            </a>
        </section>

    </main>

    <?php include 'footer.php'; ?>
</body>

</html>