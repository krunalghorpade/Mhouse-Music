<?php
require_once 'backend/db.php';
$stmt = $pdo->query("SELECT * FROM news ORDER BY published_date DESC");
$news_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-HOUSE MUSIC | News</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <h1 class="page-title">News</h1>

        <div class="grid">
            <?php foreach ($news_items as $item): ?>
                <div class="card">
                    <div class="card-image aspect-4-5">
                        <a href="news_item.php?id=<?php echo $item['id']; ?>">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($item['title']); ?>">
                        </a>
                    </div>
                    <div class="card-info" style="border: none;">
                        <div style="width: 100%;">
                            <div class="card-meta" style="text-align: left; margin-bottom: 0.5rem;">
                                <?php echo date('d M Y', strtotime($item['published_date'])); ?>
                            </div>
                            <div class="card-title" style="max-width: 100%; margin-bottom: 1rem;">
                                <a
                                    href="news_item.php?id=<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['title']); ?></a>
                            </div>
                            <p style="font-size: 0.9rem; color: var(--secondary-text); line-height: 1.6;">
                                <?php echo htmlspecialchars(substr(strip_tags($item['content']), 0, 150)) . '...'; ?>
                            </p>
                            <a href="news_item.php?id=<?php echo $item['id']; ?>"
                                style="display: inline-block; margin-top: 1rem; font-weight: 700; font-size: 0.8rem; border-bottom: 1px solid var(--accent-color);">READ
                                MORE</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>

</html>