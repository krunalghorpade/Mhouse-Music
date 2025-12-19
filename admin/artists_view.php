<?php
// admin/artists_view.php

// Check for POST Max Size Limit Exceeded
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $max_size = ini_get('post_max_size');
    echo "<div style='color: white; background: var(--ios-red); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; text-align: center;'>
            <strong>Error:</strong> The uploaded file exceeds the server limit of $max_size.<br>
            Please upload a smaller file or update your PHP configuration.
          </div>";
}

// Handle Delete
if (isset($_POST['delete_artist'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM artists WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_msg'] = "Artist deleted successfully.";
    if (ob_get_length())
        ob_end_clean();
    header("Location: ?view=artists");
    exit;
}

// Handle Add/Edit
if (isset($_POST['save_artist'])) {
    $name = $_POST['name'];
    $bio = $_POST['bio'];

    // Handle Image Upload
    $image_url = $_POST['current_image_url'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/uploads/";
        if (!file_exists($target_dir))
            mkdir($target_dir, 0777, true);

        // Sanitize filename to prevent DB errors with special chars
        $raw_name = basename($_FILES["image"]["name"]);
        $clean_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $raw_name);
        $filename = time() . "_" . $clean_name;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = "assets/uploads/" . $filename;
        } else {
            $_SESSION['flash_error'] = "Failed to upload image. Check permissions.";
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] != 4) {
        // Error other than "no file uploaded" (4)
        if ($_FILES['image']['error'] == 1) {
            $max = ini_get('upload_max_filesize');
            $_SESSION['flash_error'] = "File too large. Your server limit is $max.";
        } else {
            $_SESSION['flash_error'] = "Image upload error code: " . $_FILES['image']['error'];
        }
    }

    $socials = [];
    if (!empty($_POST['instagram']))
        $socials['instagram'] = $_POST['instagram'];
    if (!empty($_POST['spotify']))
        $socials['spotify'] = $_POST['spotify'];
    $social_links = json_encode($socials);

    if (!empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE artists SET name=?, bio=?, image_url=?, social_links=? WHERE id=?");
        $stmt->execute([$name, $bio, $image_url, $social_links, $_POST['id']]);
        $_SESSION['flash_msg'] = "Artist updated successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO artists (name, bio, image_url, social_links) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $bio, $image_url, $social_links]);
        $_SESSION['flash_msg'] = "New artist added successfully.";
    }

    if (ob_get_length())
        ob_end_clean();
    header("Location: ?view=artists");
    exit;
}

$stmt = $pdo->query("SELECT * FROM artists ORDER BY id DESC");
$artists = $stmt->fetchAll();

$editArtist = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM artists WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editArtist = $stmt->fetch();
}
$socials = $editArtist ? json_decode($editArtist['social_links'], true) : [];
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Manage Artists</h1>
    <button class="ios-btn" onclick="document.getElementById('artist-form').scrollIntoView({behavior: 'smooth'})">
        <ion-icon name="add-outline" style="vertical-align: middle;"></ion-icon> Add New
    </button>
</div>

<!-- List -->
<div class="ios-list" style="margin-bottom: 3rem;">
    <?php foreach ($artists as $artist): ?>
        <div class="ios-list-item">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <img src="../<?php echo $artist['image_url'] ?: 'assets/img/placeholder.png'; ?>"
                    style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                <div>
                    <div style="font-weight: 600; font-size: 1.1rem;"><?php echo htmlspecialchars($artist['name']); ?></div>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="?view=artists&edit=<?php echo $artist['id']; ?>" class="ios-btn-text">
                    <ion-icon name="create-outline" style="font-size: 1.5rem;"></ion-icon>
                </a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                    <input type="hidden" name="id" value="<?php echo $artist['id']; ?>">
                    <input type="hidden" name="delete_artist" value="1">
                    <button type="submit" style="background: none; border: none; color: var(--ios-red); cursor: pointer;">
                        <ion-icon name="trash-outline" style="font-size: 1.5rem;"></ion-icon>
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Form -->
<h2 id="artist-form">
    <?php echo $editArtist ? 'Edit Artist' : 'Add New Artist'; ?>
</h2>

<div class="card" style="max-width: 600px;">
    <!-- Explicit action to ?view=artists to prevent sticking in edit mode on refresh/post -->
    <form method="POST" action="?view=artists" enctype="multipart/form-data" onsubmit="showLoading()">
        <input type="hidden" name="id" value="<?php echo $editArtist['id'] ?? ''; ?>">
        <input type="hidden" name="current_image_url" value="<?php echo $editArtist['image_url'] ?? ''; ?>">

        <label>Artist Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($editArtist['name'] ?? ''); ?>" required
            placeholder="Name">

        <label>Bio</label>
        <textarea name="bio" rows="3"
            placeholder="Biography"><?php echo htmlspecialchars($editArtist['bio'] ?? ''); ?></textarea>

        <label>Profile Image</label>
        <input type="file" name="image" accept="image/*">
        <?php if (!empty($editArtist['image_url'])): ?>
            <p style="font-size: 0.8rem; color: var(--ios-secondary);">Current: <?php echo $editArtist['image_url']; ?></p>
        <?php endif; ?>

        <h3 style="font-size: 1.1rem; margin-top: 1rem;">Social Links</h3>
        <input type="text" name="instagram" value="<?php echo htmlspecialchars($socials['instagram'] ?? ''); ?>"
            placeholder="Instagram URL">
        <input type="text" name="spotify" value="<?php echo htmlspecialchars($socials['spotify'] ?? ''); ?>"
            placeholder="Spotify URL">

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" name="save_artist" class="ios-btn">Save Artist</button>
            <?php if ($editArtist): ?>
                <a href="?view=artists" class="ios-btn-outline"
                    style="padding: 0.6rem 1.2rem; border-radius: 20px; text-decoration: none;">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>