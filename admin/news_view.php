<?php
// admin/news_view.php

// Check for POST Max Size Limit Exceeded
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $max_size = ini_get('post_max_size');
    echo "<div style='color: white; background: var(--ios-red); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; text-align: center;'>
            <strong>Error:</strong> The uploaded file exceeds the server limit of $max_size.<br>
            Please upload a smaller file or update your PHP configuration.
          </div>";
}

// Handle Delete
if (isset($_POST['delete_news'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_msg'] = "News article deleted successfully.";
    if (ob_get_length())
        ob_end_clean();
    header("Location: ?view=news");
    exit;
}

// Handle Add/Edit
if (isset($_POST['save_news'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $published_date = $_POST['published_date'] ?: date('Y-m-d H:i:s');

    // Handle Image Upload
    $image_url = $_POST['current_image_url'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/uploads/";
        if (!file_exists($target_dir))
            mkdir($target_dir, 0777, true);

        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = "assets/uploads/" . $filename;
        } else {
            $_SESSION['flash_error'] = "Failed to upload image.";
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != 4) {
        $_SESSION['flash_error'] = "Image upload error: " . $_FILES['image']['error'];
    }

    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE news SET title=?, content=?, image_url=?, published_date=? WHERE id=?");
        $stmt->execute([$title, $content, $image_url, $published_date, $_POST['id']]);
        $_SESSION['flash_msg'] = "Article updated successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO news (title, content, image_url, published_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $image_url, $published_date]);
        $_SESSION['flash_msg'] = "New article published successfully.";
    }

    if (ob_get_length())
        ob_end_clean();
    header("Location: ?view=news");
    exit;
}

$stmt = $pdo->query("SELECT * FROM news ORDER BY published_date DESC");
$news_items = $stmt->fetchAll();

$editNews = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editNews = $stmt->fetch();
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Manage News</h1>
    <button class="ios-btn" onclick="document.getElementById('news-form').scrollIntoView({behavior: 'smooth'})">
        <ion-icon name="add-outline" style="vertical-align: middle;"></ion-icon> Add New
    </button>
</div>

<!-- List -->
<div class="ios-list" style="margin-bottom: 3rem;">
    <?php foreach ($news_items as $item): ?>
        <div class="ios-list-item">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <?php if ($item['image_url']): ?>
                    <img src="../<?php echo $item['image_url']; ?>"
                        style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                <?php endif; ?>
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($item['title']); ?></div>
                    <div style="font-size: 0.8rem; color: var(--ios-secondary);">
                        <?php echo date('M d, Y', strtotime($item['published_date'])); ?>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="?view=news&edit=<?php echo $item['id']; ?>" class="ios-btn-text">
                    <ion-icon name="create-outline" style="font-size: 1.5rem;"></ion-icon>
                </a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                    <input type="hidden" name="delete_news" value="1">
                    <button type="submit" style="background: none; border: none; color: var(--ios-red); cursor: pointer;">
                        <ion-icon name="trash-outline" style="font-size: 1.5rem;"></ion-icon>
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Form -->
<h2 id="news-form">
    <?php echo $editNews ? 'Edit Article' : 'Add New Article'; ?>
</h2>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="?view=news" enctype="multipart/form-data" onsubmit="showLoading()">
        <input type="hidden" name="id" value="<?php echo $editNews['id'] ?? ''; ?>">
        <input type="hidden" name="current_image_url" value="<?php echo $editNews['image_url'] ?? ''; ?>">

        <label>Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($editNews['title'] ?? ''); ?>" required
            placeholder="Article Title">

        <label>Content</label>
        <div
            style="background: rgba(255,255,255,0.05); padding: 0.5rem; border: 1px solid var(--ios-separator); border-bottom: none; border-radius: 8px 8px 0 0; display: flex; gap: 0.5rem;">
            <button type="button" onclick="insertTag('<b>', '</b>')" class="ios-btn-outline"
                style="padding: 0.2rem 0.6rem; font-weight: bold;">B</button>
            <button type="button" onclick="insertTag('<i>', '</i>')" class="ios-btn-outline"
                style="padding: 0.2rem 0.6rem; font-style: italic;">I</button>
            <button type="button" onclick="insertLink()" class="ios-btn-outline"
                style="padding: 0.2rem 0.6rem; text-decoration: underline;">Link</button>
        </div>
        <textarea id="newsContent" name="content" rows="10" required
            style="border-top-left-radius: 0; border-top-right-radius: 0;"
            placeholder="Article content..."><?php echo htmlspecialchars($editNews['content'] ?? ''); ?></textarea>

        <script>
            function insertTag(start, end) {
                const textarea = document.getElementById('newsContent');
                const selectionStart = textarea.selectionStart;
                const selectionEnd = textarea.selectionEnd;
                const text = textarea.value;

                const before = text.substring(0, selectionStart);
                const selection = text.substring(selectionStart, selectionEnd);
                const after = text.substring(selectionEnd);

                textarea.value = before + start + selection + end + after;
                textarea.focus();
                textarea.selectionStart = selectionStart + start.length;
                textarea.selectionEnd = selectionEnd + start.length;
            }

            function insertLink() {
                const url = prompt("Enter the URL:", "https://");
                if (url) {
                    insertTag('<a href="' + url + '" target="_blank">', '</a>');
                }
            }
        </script>

        <label>Published Date</label>
        <input type="text" name="published_date"
            value="<?php echo htmlspecialchars($editNews['published_date'] ?? date('Y-m-d H:i:s')); ?>"
            placeholder="YYYY-MM-DD HH:MM:SS">

        <label>Cover Image</label>
        <input type="file" name="image" accept="image/*">
        <?php if (!empty($editNews['image_url'])): ?>
            <p style="font-size: 0.8rem; color: var(--ios-secondary);">Current: <?php echo $editNews['image_url']; ?></p>
        <?php endif; ?>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" name="save_news" class="ios-btn">Save Article</button>
            <?php if ($editNews): ?>
                <a href="?view=news" class="ios-btn-outline"
                    style="padding: 0.6rem 1.2rem; border-radius: 20px; text-decoration: none;">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>