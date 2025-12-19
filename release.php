<?php
require_once 'backend/db.php';

if (!isset($_GET['id'])) {
    header("Location: releases.php");
    exit;
}

$id = $_GET['id'];

// Fetch Release Info
$stmt = $pdo->prepare("SELECT * FROM releases WHERE id = ?");
$stmt->execute([$id]);
$release = $stmt->fetch();

if (!$release) {
    header("Location: releases.php");
    exit;
}

// Fetch Artists
$stmtArtists = $pdo->prepare("SELECT a.id, a.name FROM artists a JOIN release_artists ra ON a.id = ra.artist_id WHERE ra.release_id = ?");
$stmtArtists->execute([$id]);
$artists = $stmtArtists->fetchAll();

// Fallback if no artists linked (legacy data safety)
if (empty($artists) && !empty($release['artist_name'])) {
    // This part is tricky if artist_name column is gone or empty. 
    // Ideally we rely on IDs. If empty, maybe show "M-House Artist"
}

$links = json_decode($release['platform_links'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-HOUSE | <?php echo htmlspecialchars($release['title']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/icon.png">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <div class="hero-split" style="margin-top: 4rem;">
            <!-- Artwork -->
            <div class="hero-image" style="background: transparent;">
                <img src="<?php echo htmlspecialchars($release['cover_url']); ?>" alt="Cover Art"
                    style="box-shadow: 0 10px 40px rgba(0,0,0,0.5);">
            </div>

            <!-- Details -->
            <div class="hero-text">
                <span class="uppercase" style="color: var(--accent-color); font-weight: 700; letter-spacing: 2px;">
                    <?php echo htmlspecialchars($release['type']); ?>
                </span>
                <h1 style="font-size: clamp(3rem, 6vw, 6rem); margin-top: 1rem; margin-bottom: 0.5rem;">
                    <?php echo htmlspecialchars($release['title']); ?>
                </h1>

                <!-- Artists -->
                <h2 style="font-size: 2rem; color: var(--secondary-text); margin-bottom: 2rem;">
                    <?php
                    $artistLinks = [];
                    foreach ($artists as $artist) {
                        $artistLinks[] = '<a href="artists.php?id=' . $artist['id'] . '" class="hover-underline">' . htmlspecialchars($artist['name']) . '</a>';
                    }
                    echo implode(', ', $artistLinks);
                    ?>
                </h2>

                <!-- Description -->
                <?php if (!empty($release['description'])): ?>
                    <p style="padding-left: 0; border-left: none; margin-bottom: 2rem; font-size: 1.1rem; color: #ccc;">
                        <?php echo nl2br(htmlspecialchars($release['description'])); ?>
                    </p>
                <?php endif; ?>

                <!-- Links -->
                <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    <?php if (!empty($links['spotify'])): ?>
                        <a href="<?php echo htmlspecialchars($links['spotify']); ?>" class="btn"
                            style="background: #1DB954; color: black; margin-top: 0; border: none;">Spotify</a>
                    <?php endif; ?>

                    <?php if (!empty($links['applemusic'])): ?>
                        <a href="<?php echo htmlspecialchars($links['applemusic']); ?>" class="btn"
                            style="background: #FC3C44; color: white; margin-top: 0; border: none;">Apple Music</a>
                    <?php endif; ?>

                    <?php if (!empty($links['beatport'])): ?>
                        <a href="<?php echo htmlspecialchars($links['beatport']); ?>" class="btn"
                            style="background: #02FF95; color: black; margin-top: 0; border: none;">Beatport</a>
                    <?php endif; ?>

                    <?php if (!empty($links['soundcloud'])): ?>
                        <a href="<?php echo htmlspecialchars($links['soundcloud']); ?>" class="btn"
                            style="background: #FF5500; color: white; margin-top: 0; border: none;">SoundCloud</a>
                    <?php endif; ?>

                    <?php if (!empty($links['youtube'])): ?>
                        <a href="<?php echo htmlspecialchars($links['youtube']); ?>" class="btn"
                            style="background: #FF0000; color: white; margin-top: 0; border: none;">YouTube</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Embedded Player (YouTube/Soundcloud) -->
        <?php if (!empty($links['youtube'])):
            // Simple logic to convert watch URL to embed
            $embedUrl = str_replace("watch?v=", "embed/", $links['youtube']);
            ?>
            <section style="margin-top: 6rem;">
                <div style="width: 100%; aspect-ratio: 16/9;">
                    <iframe width="100%" height="100%" src="<?php echo htmlspecialchars($embedUrl); ?>"
                        title="YouTube video player" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen></iframe>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <?php include 'footer.php'; ?>
</body>

</html>