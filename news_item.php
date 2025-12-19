<?php
require_once 'backend/db.php';

if (!isset($_GET['id'])) {
    header("Location: news.php");
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    header("Location: news.php");
    exit;
}

// Fetch suggested news (2 items, excluding current)
$stmt = $pdo->prepare("SELECT * FROM news WHERE id != ? ORDER BY published_date DESC LIMIT 2");
$stmt->execute([$id]);
$suggested = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-HOUSE | <?php echo htmlspecialchars($news['title']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <!-- Article Header -->
        <article style="max-width: 800px; margin: 0 auto; margin-top: 4rem;">
            <div style="margin-bottom: 2rem;">
                <span class="uppercase" style="color: var(--accent-color); font-weight: 700;">News</span>
                <span
                    style="color: var(--secondary-text); margin-left: 1rem;"><?php echo date('d M Y', strtotime($news['published_date'])); ?></span>
            </div>

            <h1 style="font-size: clamp(2.5rem, 5vw, 4rem); margin-bottom: 2rem;">
                <?php echo htmlspecialchars($news['title']); ?>
            </h1>

            <div style="width: 100%; aspect-ratio: 4/5; overflow: hidden; margin-bottom: 3rem;">
                <img src="<?php echo htmlspecialchars($news['image_url']); ?>"
                    alt="<?php echo htmlspecialchars($news['title']); ?>"
                    style="width: 100%; height: 100%; object-fit: cover;">
            </div>

            <div class="news-article-content" style="font-size: 1.5rem; line-height: 1.8; color: #ddd;">
                <?php echo nl2br($news['content']); ?>
            </div>
        </article>

        <!-- Suggested News -->
        <section style="margin-top: 8rem; border-top: 1px solid var(--border-color); padding-top: 4rem;">
            <h2 class="uppercase" style="margin-bottom: 3rem;">More News</h2>
            <div class="grid">
                <?php foreach ($suggested as $item): ?>
                    <div class="card">
                        <div class="card-image aspect-4-5">
                            <a href="news_item.php?id=<?php echo $item['id']; ?>">
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>">
                            </a>
                        </div>
                        <div class="card-info" style="display: block;">
                            <div class="card-meta" style="text-align: left; margin-bottom: 0.5rem;">
                                <?php echo date('d M', strtotime($item['published_date'])); ?>
                            </div>
                            <div class="card-title">
                                <a
                                    href="news_item.php?id=<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['title']); ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <?php include 'footer.php'; ?>
</body>

</html>