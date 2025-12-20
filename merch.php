<?php
require_once 'backend/db.php';
$stmt = $pdo->query("SELECT * FROM merch WHERE status = 'available' ORDER BY created_at DESC");
$merch = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-HOUSE MUSIC | Merch</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/icon.png">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <h1 class="page-title">Shop</h1>

        <?php if (empty($merch)): ?>
            <p style="color: var(--secondary-text); margin-top: 2rem;">No merch items available at the moment.</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($merch as $item): ?>
                    <a href="<?php echo htmlspecialchars($item['link'] ?? '#'); ?>" target="_blank" class="card">
                        <div class="card-image">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="card-info">
                            <div class="card-title"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="card-subtitle">
                                <?php echo htmlspecialchars($item['category'] ?? 'Unisex'); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>

</html>