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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">

        <div class="track-hero">
            <!-- Left: Artwork -->
            <div class="track-artwork">
                <img src="<?php echo htmlspecialchars($release['cover_url']); ?>" alt="Cover Art">
            </div>

            <!-- Right: Info -->
            <div class="track-info">
                <span class="track-type">
                    <?php echo htmlspecialchars($release['type']); ?>
                </span>

                <h1 class="track-title">
                    <?php echo htmlspecialchars($release['title']); ?>
                </h1>

                <!-- Artists -->
                <div class="track-artists">
                    <?php
                    $artistLinks = [];
                    foreach ($artists as $artist) {
                        $artistLinks[] = '<a href="artists.php?id=' . $artist['id'] . '" class="hover-underline">' . htmlspecialchars($artist['name']) . '</a>';
                    }
                    echo implode(', ', $artistLinks);
                    ?>
                </div>

                <!-- Description -->
                <?php if (!empty($release['description'])): ?>
                    <p style="margin-bottom: 3rem; font-size: 1.1rem; color: #ccc; line-height: 1.6; max-width: 600px;">
                        <?php echo nl2br(htmlspecialchars($release['description'])); ?>
                    </p>
                <?php endif; ?>

                <!-- Links (Minimal Buttons) -->
                <div class="track-links">
                    <?php if (!empty($links['spotify'])): ?>
                        <a href="<?php echo htmlspecialchars($links['spotify']); ?>" class="btn-minimal" target="_blank">
                            <i class="fa-brands fa-spotify"></i> Spotify
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($links['applemusic'])): ?>
                        <a href="<?php echo htmlspecialchars($links['applemusic']); ?>" class="btn-minimal" target="_blank">
                            <i class="fa-brands fa-apple"></i> Apple Music
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($links['youtube'])): ?>
                        <a href="<?php echo htmlspecialchars($links['youtube']); ?>" class="btn-minimal" target="_blank">
                            <i class="fa-brands fa-youtube"></i> YouTube
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($links['beatport'])): ?>
                        <a href="<?php echo htmlspecialchars($links['beatport']); ?>" class="btn-minimal" target="_blank">
                            <i class="fa-solid fa-music"></i> Beatport
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Embedded Player (YouTube) -->
        <?php if (!empty($links['youtube'])):
            $embedUrl = str_replace("watch?v=", "embed/", $links['youtube']);
            ?>
            <section style="margin-bottom: 6rem; max-width: 1000px; margin-left: auto; margin-right: auto;">
                <div
                    style="width: 100%; aspect-ratio: 16/9; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
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