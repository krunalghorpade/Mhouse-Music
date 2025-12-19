<?php
require_once 'backend/db.php';
$stmt = $pdo->query("SELECT * FROM artists");
$artists = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-HOUSE MUSIC | Artists</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Specific override for this page if needed, but style.css handles .artist-list-item */
    </style>
    <link rel="icon" type="image/png" href="assets/images/icon.png">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <h1 class="page-title">Artists</h1>

        <div class="artist-list">
            <?php foreach ($artists as $artist): ?>
                <a href="artist.php?id=<?php echo $artist['id']; ?>" class="artist-list-item">
                    <img src="<?php echo htmlspecialchars($artist['image_url']); ?>"
                        alt="<?php echo htmlspecialchars($artist['name']); ?>" class="artist-list-image">
                    <span class="artist-list-name"><?php echo htmlspecialchars($artist['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>

</html>