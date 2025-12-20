<?php
// admin/merch_view.php

// Handle Delete
if (isset($_POST['delete_merch'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM merch WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_msg'] = "Merch item deleted.";
    if (ob_get_length())
        ob_end_clean();
    header("Location: ?view=merch");
    exit;
}

// Handle Add/Edit
if (isset($_POST['save_merch'])) {
    $name = $_POST['name'];
    $category = $_POST['category']; // Men, Women, Unisex, Kids, Accessory
    $link = $_POST['link'];
    // Price and Description might be ignored or set to defaults since user wants to remove "price" display, 
    // but DB requires non-null or defaults. 'price' is DECIMAL NOT NULL in original schema.
    // We will set a dummy price or 0 if not provided, since display hides it.
    $price = 0;

    // Handle Image Upload
    $image_url = $_POST['current_image_url'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/uploads/";
        if (!file_exists($target_dir))
            mkdir($target_dir, 0777, true);

        // Sanitize filename
        $raw_name = basename($_FILES["image"]["name"]);
        $clean_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $raw_name);
        $filename = time() . "_merch_" . $clean_name;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = "assets/uploads/" . $filename;
        }
    }

    $id = $_POST['id'] ?? '';

    if (!empty($id)) {
        // Update
        $stmt = $pdo->prepare("UPDATE merch SET name=?, category=?, link=?, image_url=?, price=0 WHERE id=?");
        $stmt->execute([$name, $category, $link, $image_url, $id]);
        $_SESSION['flash_msg'] = "Merch updated successfully.";
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO merch (name, category, link, image_url, price) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$name, $category, $link, $image_url]);
        $_SESSION['flash_msg'] = "New merch item added successfully.";
    }

    if (ob_get_length())
        ob_end_clean();
    header("Location: ?view=merch");
    exit;
}

// Fetch Merch
$merch_items = $pdo->query("SELECT * FROM merch ORDER BY created_at DESC")->fetchAll();

$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM merch WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editItem = $stmt->fetch();
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Manage Shop (Merch)</h1>
    <button class="ios-btn" onclick="document.getElementById('merch-form').scrollIntoView({behavior: 'smooth'})">
        <ion-icon name="add-outline" style="vertical-align: middle;"></ion-icon> Add New
    </button>
</div>

<!-- List -->
<div class="ios-list" style="margin-bottom: 3rem;">
    <?php foreach ($merch_items as $item): ?>
        <div class="ios-list-item">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <img src="../<?php echo htmlspecialchars($item['image_url']); ?>"
                    style="width: 50px; height: 50px; border-radius: 4px; object-fit: cover; background: #333;">
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div style="font-size: 0.8rem; color: var(--ios-secondary);">
                        <?php echo htmlspecialchars($item['category'] ?? ''); ?>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="?view=merch&edit=<?php echo $item['id']; ?>" class="ios-btn-text">
                    <ion-icon name="create-outline" style="font-size: 1.5rem;"></ion-icon>
                </a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                    <input type="hidden" name="delete_merch" value="1">
                    <button type="submit" style="background: none; border: none; color: var(--ios-red); cursor: pointer;">
                        <ion-icon name="trash-outline" style="font-size: 1.5rem;"></ion-icon>
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($merch_items)): ?>
        <p style="color: var(--ios-secondary); text-align: center; padding: 2rem;">No items in shop.</p>
    <?php endif; ?>
</div>

<!-- Form -->
<h2 id="merch-form"><?php echo $editItem ? 'Edit Item' : 'Add New Item'; ?></h2>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="?view=merch" enctype="multipart/form-data" onsubmit="showLoading()">
        <input type="hidden" name="id" value="<?php echo $editItem['id'] ?? ''; ?>">
        <input type="hidden" name="current_image_url" value="<?php echo $editItem['image_url'] ?? ''; ?>">

        <label>Title</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($editItem['name'] ?? ''); ?>" required
            placeholder="T-Shirt Name">

        <label>For (Category)</label>
        <select name="category" required>
            <option value="Unisex" <?php echo ($editItem && $editItem['category'] == 'Unisex') ? 'selected' : ''; ?>>
                Unisex</option>
            <option value="Men" <?php echo ($editItem && $editItem['category'] == 'Men') ? 'selected' : ''; ?>>Men
            </option>
            <option value="Women" <?php echo ($editItem && $editItem['category'] == 'Women') ? 'selected' : ''; ?>>Women
            </option>
            <option value="Kids" <?php echo ($editItem && $editItem['category'] == 'Kids') ? 'selected' : ''; ?>>Kids
            </option>
            <option value="Accessory" <?php echo ($editItem && $editItem['category'] == 'Accessory') ? 'selected' : ''; ?>>Accessory</option>
        </select>

        <label>Link (URL)</label>
        <input type="text" name="link" value="<?php echo htmlspecialchars($editItem['link'] ?? ''); ?>" required
            placeholder="https://store.com/item">

        <label>Image</label>
        <input type="file" name="image" accept="image/*">
        <?php if (!empty($editItem['image_url'])): ?>
            <div style="margin-top: 0.5rem;">
                <img src="../<?php echo htmlspecialchars($editItem['image_url']); ?>"
                    style="height: 100px; border-radius: 8px;">
            </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; display: flex; gap: 1rem;">
            <button type="submit" name="save_merch" class="ios-btn">Save Item</button>
            <?php if ($editItem): ?>
                <a href="?view=merch" class="ios-btn-outline"
                    style="padding: 0.6rem 1.2rem; border-radius: 20px; text-decoration: none;">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>