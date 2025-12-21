<?php
require_once 'backend/db.php';
$stmt = $pdo->query("SELECT r.*, GROUP_CONCAT(a.name SEPARATOR '||') as artist_names, GROUP_CONCAT(a.id SEPARATOR '||') as artist_ids 
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <h1 class="page-title">Music</h1>


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