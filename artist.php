<?php
require_once 'backend/db.php';

if (!isset($_GET['id'])) {
    header("Location: artists.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM artists WHERE id = ?");
$stmt->execute([$id]);
$artist = $stmt->fetch();

if (!$artist) {
    header("Location: artists.php");
    exit;
}

// Fetch Artist's Releases
$stmt = $pdo->prepare("SELECT * FROM releases WHERE artist_id = ? ORDER BY release_date DESC");
$stmt->execute([$id]);
$releases = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-HOUSE | <?php echo htmlspecialchars($artist['name']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <div class="artist-profile">
            <!-- Left: Image -->
            <div class="artist-profile-image">
                <img src="<?php echo htmlspecialchars($artist['image_url']); ?>"
                    alt="<?php echo htmlspecialchars($artist['name']); ?>">
            </div>

            <!-- Right: Details -->
            <div class="artist-details">
                <h1 style="font-size: clamp(3rem, 6vw, 5rem); margin-bottom: 2rem; line-height: 1;">
                    <?php echo htmlspecialchars($artist['name']); ?>
                </h1>

                <div class="artist-bio-section"
                    style="font-size: 1.1rem; color: var(--secondary-text); margin-bottom: 2rem; white-space: pre-wrap;">
                    <?php echo htmlspecialchars($artist['bio']); ?>
                </div>

                <?php
                $socials = json_decode($artist['social_links'], true) ?? [];
                if (!empty($socials)):
                    ?>
                    <div style="display: flex; gap: 1rem; margin-bottom: 3rem; flex-wrap: wrap;">
                        <?php foreach ($socials as $platform => $url):
                            if ($platform == 'soundcloud')
                                continue; // Skip SoundCloud
                    
                            $icon = 'fa-solid fa-link'; // Default
                            if ($platform == 'instagram')
                                $icon = 'fa-brands fa-instagram';
                            if ($platform == 'spotify')
                                $icon = 'fa-brands fa-spotify';
                            ?>
                            <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" class="btn-minimal">
                                <i class="<?php echo $icon; ?>"></i> <?php echo ucfirst($platform); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Discography -->
        <?php if ($releases): ?>
            <section>
                <h2 class="uppercase"
                    style="margin-bottom: 3rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                    Discography</h2>
                <div class="grid">
                    <?php foreach ($releases as $release): ?>
                        <div class="card">
                            <div class="card-image">
                                <a href="release.php?id=<?php echo $release['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($release['cover_url']); ?>">
                                </a>
                            </div>
                            <div class="card-info">
                                <div class="card-title">
                                    <a
                                        href="release.php?id=<?php echo $release['id']; ?>"><?php echo htmlspecialchars($release['title']); ?></a>
                                </div>
                                <div class="card-meta">
                                    <?php echo date('Y', strtotime($release['release_date'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

    </main>

    <?php include 'footer.php'; ?>
</body>

</html>