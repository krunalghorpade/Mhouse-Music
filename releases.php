<?php
require_once 'backend/db.php';
$stmt = $pdo->query("SELECT r.*, GROUP_CONCAT(a.name, '||') as artist_names, GROUP_CONCAT(a.id, '||') as artist_ids 
                     FROM releases r 
                     LEFT JOIN release_artists ra ON r.id = ra.release_id 
                     LEFT JOIN artists a ON ra.artist_id = a.id 
                     GROUP BY r.id 
                     ORDER BY r.release_date DESC");
$releases = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-HOUSE MUSIC | Releases</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/icon.png">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <h1 class="page-title">Music</h1>

        <!-- Playlists Section -->
        <section style="margin-bottom: 4rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border-color);">
            <h2 class="uppercase" style="margin-bottom: 2rem;">Playlists</h2>
            <div class="grid">
                <a href="#" class="card" style="text-align: center;">
                    <div class="card-image"><img
                            src="https://images.unsplash.com/photo-1611339555312-e607c8352fd7?auto=format&fit=crop&w=400&q=80"
                            style="opacity: 0.8;"></div>
                    <div class="card-title" style="margin-top: 1rem; max-width: 100%;">Spotify Selection</div>
                </a>
                <a href="#" class="card" style="text-align: center;">
                    <div class="card-image"><img
                            src="https://images.unsplash.com/photo-1605371924599-2d0365da1ae0?auto=format&fit=crop&w=400&q=80"
                            style="opacity: 0.8;"></div>
                    <div class="card-title" style="margin-top: 1rem; max-width: 100%;">Apple Music</div>
                </a>
                <a href="#" class="card" style="text-align: center;">
                    <div class="card-image"><img
                            src="https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=400&q=80"
                            style="opacity: 0.8;"></div>
                    <div class="card-title" style="margin-top: 1rem; max-width: 100%;">YouTube Mix</div>
                </a>
                <a href="#" class="card" style="text-align: center;">
                    <div class="card-image"><img src="assets/images/site-logo.svg"
                            style="opacity: 0.8; padding: 20px; background: #222;"></div>
                    <div class="card-title" style="margin-top: 1rem; max-width: 100%;">Nustavibes Radio</div>
                </a>
            </div>
        </section>

        <h2 class="uppercase" style="margin-bottom: 2rem;">Catalog</h2>
        <div class="grid">
            <?php foreach ($releases as $release): ?>
                <a href="release.php?id=<?php echo $release['id']; ?>" class="card">
                    <div class="card-image">
                        <img src="<?php echo htmlspecialchars($release['cover_url']); ?>"
                            alt="<?php echo htmlspecialchars($release['title']); ?>">
                    </div>
                    <div class="card-info" style="display: block;">
                        <div class="card-title">
                            <?php echo htmlspecialchars($release['title']); ?>
                        </div>
                        <div class="artist-name">
                            <?php
                            // Parse and display artists
                            if ($release['artist_ids']) {
                                $names = explode('||', $release['artist_names']);
                                echo implode(', ', $names);
                            } else {
                                echo 'M-House';
                            }
                            ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>

</html>