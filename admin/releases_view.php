<?php
// admin/releases_view.php

// Check for POST Max Size Limit Exceeded
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $max_size = ini_get('post_max_size');
    echo "<div style='color: white; background: var(--ios-red); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; text-align: center;'>
            <strong>Error:</strong> The uploaded file exceeds the server limit of $max_size.<br>
            Please upload a smaller file or update your PHP configuration.
          </div>";
}

// Handle Delete
if (isset($_POST['delete_release'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM releases WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_msg'] = "Release deleted successfully.";
    if (ob_get_length())
        ob_end_clean();
    header("Location: ?view=releases");
    exit;
}

// Handle Save Cover Settings
if (isset($_POST['save_cover'])) {
    $main_text = $_POST['main_text'];
    $sub_text = $_POST['sub_text'];
    $highlight_text = $_POST['highlight_text'];
    // Keeping these as they might be useful or legacy, treating tag_text/button as optional
    $tag_text = $_POST['tag_text'] ?? '';
    $button_label = $_POST['button_label'] ?? '';
    $button_link = $_POST['button_link'] ?? '';

    // Handle Image (Legacy/Poster)
    $image_url = $_POST['current_cover_image'] ?? '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
        $target_dir = "../assets/uploads/";
        if (!file_exists($target_dir))
            mkdir($target_dir, 0777, true);

        // Sanitize filename
        $raw_name = basename($_FILES["cover_image"]["name"]);
        $clean_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $raw_name);
        $filename = time() . "_cover_" . $clean_name;
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
            $image_url = "assets/uploads/" . $filename;
        } else {
            $_SESSION['flash_error'] = "Failed to upload cover image.";
        }
    }

    // Handle Video
    $video_url = $_POST['current_video_url'] ?? '';
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $target_dir = "../assets/uploads/";
        if (!file_exists($target_dir))
            mkdir($target_dir, 0777, true);

        // Sanitize filename
        $raw_name = basename($_FILES["video_file"]["name"]);
        $clean_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $raw_name);
        $filename = time() . "_video_" . $clean_name;
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_file)) {
            $video_url = "assets/uploads/" . $filename;
        } else {
            $_SESSION['flash_error'] = "Failed to upload video file.";
        }
    }

    // Insert or Update (ID is always 1)
    $stmt = $pdo->prepare("INSERT OR REPLACE INTO cover_settings (id, tag_text, main_text, sub_text, highlight_text, button_label, button_link, image_url, video_url) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$tag_text, $main_text, $sub_text, $highlight_text, $button_label, $button_link, $image_url, $video_url]);

    if (!isset($_SESSION['flash_error'])) {
        $_SESSION['flash_msg'] = "Video Hero settings updated successfully.";
    }

    if (ob_get_length())
        ob_end_clean();
    header("Location: ?view=releases");
    exit;
}

// Handle Add/Edit
if (isset($_POST['save_release'])) {
    $title = $_POST['title'];
    $artist_ids = $_POST['artist_ids'] ?? []; // Array
    $type = $_POST['type'];
    $release_date = $_POST['release_date'];
    $description = $_POST['description'] ?? '';

    // Handle Image
    $cover_url = $_POST['current_cover_url'] ?? '';
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $target_dir = "../assets/uploads/";
        if (!file_exists($target_dir))
            mkdir($target_dir, 0777, true);

        // Sanitize filename
        $raw_name = basename($_FILES["cover"]["name"]);
        $clean_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $raw_name);
        $filename = time() . "_rel_" . $clean_name;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["cover"]["tmp_name"], $target_file)) {
            $cover_url = "assets/uploads/" . $filename;
        } else {
            $_SESSION['flash_error'] = "Failed to upload cover art.";
        }
    } elseif (isset($_FILES['cover']) && $_FILES['cover']['error'] != 4) {
        $_SESSION['flash_error'] = "Cover upload error: " . $_FILES['cover']['error'];
    }

    $links = [];
    if (!empty($_POST['spotify']))
        $links['spotify'] = $_POST['spotify'];
    if (!empty($_POST['applemusic']))
        $links['applemusic'] = $_POST['applemusic'];
    if (!empty($_POST['beatport']))
        $links['beatport'] = $_POST['beatport'];
    if (!empty($_POST['youtube']))
        $links['youtube'] = $_POST['youtube'];
    if (!empty($_POST['soundcloud']))
        $links['soundcloud'] = $_POST['soundcloud'];
    $platform_links = json_encode($links);

    $release_id = $_POST['id'];

    if (!empty($release_id)) {
        // Update Release Info
        $stmt = $pdo->prepare("UPDATE releases SET title=?, type=?, release_date=?, cover_url=?, platform_links=?, description=? WHERE id=?");
        $stmt->execute([$title, $type, $release_date, $cover_url, $platform_links, $description, $release_id]);
        $_SESSION['flash_msg'] = "Release updated successfully.";
    } else {
        // Insert Release Info
        $stmt = $pdo->prepare("INSERT INTO releases (title, type, release_date, cover_url, platform_links, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $type, $release_date, $cover_url, $platform_links, $description]);
        $release_id = $pdo->lastInsertId();
        $_SESSION['flash_msg'] = "New release added successfully.";
    }

    // Handle Artists (Many-to-Many)
    // 1. Clear existing
    $pdo->prepare("DELETE FROM release_artists WHERE release_id = ?")->execute([$release_id]);

    // 2. Insert new
    if (!empty($artist_ids)) {
        $stmtMap = $pdo->prepare("INSERT INTO release_artists (release_id, artist_id) VALUES (?, ?)");
        foreach ($artist_ids as $aid) {
            $stmtMap->execute([$release_id, $aid]);
        }
    }

    if (ob_get_length())
        ob_end_clean();
    header("Location: ?view=releases");
    exit;
}

// Fetch Data
// Fetch Data
// Group concat for display
$releases = $pdo->query("SELECT r.*, GROUP_CONCAT(a.name, ', ') as artist_names 
                         FROM releases r 
                         LEFT JOIN release_artists ra ON r.id = ra.release_id 
                         LEFT JOIN artists a ON ra.artist_id = a.id 
                         GROUP BY r.id 
                         ORDER BY r.release_date DESC")->fetchAll();

$artists = $pdo->query("SELECT id, name FROM artists ORDER BY name")->fetchAll();

$editRelease = null;
$selectedArtists = [];

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM releases WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editRelease = $stmt->fetch();

    // Fetch related artists
    if ($editRelease) {
        $stmt = $pdo->prepare("SELECT artist_id FROM release_artists WHERE release_id = ?");
        $stmt->execute([$editRelease['id']]);
        $selectedArtists = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
$links = $editRelease ? json_decode($editRelease['platform_links'], true) : [];

// Fetch Cover Settings
// Fetch Cover Settings
$cover = $pdo->query("SELECT * FROM cover_settings WHERE id = 1")->fetch();

// If no custom cover set, show what is currently live (Latest Release)
if (!$cover) {
    $stmt = $pdo->query("SELECT * FROM releases ORDER BY release_date DESC LIMIT 1");
    $latest = $stmt->fetch();

    if ($latest) {
        $cover = [
            'tag_text' => 'Latest Drop',
            'main_text' => $latest['title'],
            'button_label' => 'Stream Now',
            'button_link' => 'releases.php',
            'image_url' => $latest['cover_url']
        ];
    }
}
?>

<!-- Cover Change Section -->
<!-- Cover Change Section (Video Hero) -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-bottom: 1rem;">Video Hero Settings</h2>
    <form method="POST" action="?view=releases" enctype="multipart/form-data" onsubmit="showLoading()">
        <input type="hidden" name="current_cover_image" value="<?php echo $cover['image_url'] ?? ''; ?>">
        <input type="hidden" name="current_video_url" value="<?php echo $cover['video_url'] ?? ''; ?>">

        <label>Hero Text (Main Title)</label>
        <textarea name="main_text" rows="2"
            placeholder="Main Headline"><?php echo htmlspecialchars($cover['main_text'] ?? ''); ?></textarea>

        <label>Word(s) to Highlight (Will be orange)</label>
        <input type="text" name="highlight_text" value="<?php echo htmlspecialchars($cover['highlight_text'] ?? ''); ?>"
            placeholder="e.g. Marathi">

        <label>Sub Text (Hashtag/Subtitle)</label>
        <input type="text" name="sub_text" value="<?php echo htmlspecialchars($cover['sub_text'] ?? ''); ?>"
            placeholder="e.g. #marathivaajlachpahije">

        <label>Video File (Max 128MB)</label>
        <input type="file" name="video_file" accept="video/*">
        <?php if (!empty($cover['video_url'])): ?>
            <div style="margin-top: 0.5rem; color: var(--ios-blue); font-size: 0.8rem;">
                Current Video: <?php echo basename($cover['video_url']); ?>
            </div>
        <?php endif; ?>

        <div style="margin-top: 1rem; border-top: 1px solid var(--ios-separator); padding-top: 1rem;">
            <h3 style="font-size: 1rem; color: var(--ios-secondary); margin-bottom: 0.5rem;">Legacy / Fallback Options
            </h3>
            <label>Cover Image (Poster for Video)</label>
            <input type="file" name="cover_image" accept="image/*">
        </div>

        <button type="submit" name="save_cover" class="ios-btn" style="margin-top: 1rem;">Update Video Hero</button>
    </form>
</div>


<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Manage Releases</h1>
    <button class="ios-btn" onclick="document.getElementById('release-form').scrollIntoView({behavior: 'smooth'})">
        <ion-icon name="add-outline" style="vertical-align: middle;"></ion-icon> Add New
    </button>
</div>

<!-- List -->
<div class="ios-list" style="margin-bottom: 3rem;">
    <?php foreach ($releases as $rel): ?>
        <div class="ios-list-item">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <img src="../<?php echo $rel['cover_url']; ?>"
                    style="width: 50px; height: 50px; border-radius: 4px; object-fit: cover;">
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($rel['title']); ?></div>
                    <div style="font-size: 0.8rem; color: var(--ios-secondary);">
                        <?php echo htmlspecialchars($rel['artist_names'] ?: 'No Artist'); ?> • <?php echo $rel['type']; ?>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="?view=releases&edit=<?php echo $rel['id']; ?>" class="ios-btn-text">
                    <ion-icon name="create-outline" style="font-size: 1.5rem;"></ion-icon>
                </a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                    <input type="hidden" name="id" value="<?php echo $rel['id']; ?>">
                    <input type="hidden" name="delete_release" value="1">
                    <button type="submit" style="background: none; border: none; color: var(--ios-red); cursor: pointer;">
                        <ion-icon name="trash-outline" style="font-size: 1.5rem;"></ion-icon>
                    </button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Form -->
<h2 id="release-form">
    <?php echo $editRelease ? 'Edit Release' : 'Add New Release'; ?>
</h2>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="?view=releases" enctype="multipart/form-data" onsubmit="showLoading()">
        <input type="hidden" name="id" value="<?php echo $editRelease['id'] ?? ''; ?>">
        <input type="hidden" name="current_cover_url" value="<?php echo $editRelease['cover_url'] ?? ''; ?>">

        <label>Title</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($editRelease['title'] ?? ''); ?>" required
            placeholder="Track Title">

        <label>Artists (Hold Ctrl/Cmd to select multiple)</label>
        <select name="artist_ids[]" multiple style="min-height: 100px;">
            <?php foreach ($artists as $artist): ?>
                <option value="<?php echo $artist['id']; ?>" <?php echo in_array($artist['id'], $selectedArtists) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($artist['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Description</label>
        <textarea name="description" rows="3"
            placeholder="Track Description"><?php echo htmlspecialchars($editRelease['description'] ?? ''); ?></textarea>

        <label>Type</label>
        <select name="type">
            <option value="Single" <?php echo ($editRelease && $editRelease['type'] == 'Single') ? 'selected' : ''; ?>>
                Single</option>
            <option value="EP" <?php echo ($editRelease && $editRelease['type'] == 'EP') ? 'selected' : ''; ?>>EP</option>
            <option value="Album" <?php echo ($editRelease && $editRelease['type'] == 'Album') ? 'selected' : ''; ?>>Album
            </option>
        </select>

        <label>Release Date</label>
        <input type="date" name="release_date"
            value="<?php echo htmlspecialchars($editRelease['release_date'] ?? date('Y-m-d')); ?>">

        <label>Cover Art</label>
        <input type="file" name="cover" accept="image/*">
        <?php if (!empty($editRelease['cover_url'])): ?>
            <p style="font-size: 0.8rem; color: var(--ios-secondary);">Current: <?php echo $editRelease['cover_url']; ?></p>
        <?php endif; ?>

        <h3 style="font-size: 1.1rem; margin-top: 1rem;">Platform Links</h3>
        <input type="text" name="spotify" value="<?php echo htmlspecialchars($links['spotify'] ?? ''); ?>"
            placeholder="Spotify URL">
        <input type="text" name="applemusic" value="<?php echo htmlspecialchars($links['applemusic'] ?? ''); ?>"
            placeholder="Apple Music URL">
        <input type="text" name="beatport" value="<?php echo htmlspecialchars($links['beatport'] ?? ''); ?>"
            placeholder="Beatport URL">
        <input type="text" name="youtube" value="<?php echo htmlspecialchars($links['youtube'] ?? ''); ?>"
            placeholder="YouTube URL">
        <input type="text" name="soundcloud" value="<?php echo htmlspecialchars($links['soundcloud'] ?? ''); ?>"
            placeholder="SoundCloud URL">

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" name="save_release" class="ios-btn">Save Release</button>
            <?php if ($editRelease): ?>
                <a href="?view=releases" class="ios-btn-outline"
                    style="padding: 0.6rem 1.2rem; border-radius: 20px; text-decoration: none;">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>