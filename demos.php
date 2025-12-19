<?php
require_once 'backend/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artist_name = trim($_POST['artist_name']);
    $email = trim($_POST['email']);
    $demo_url = trim($_POST['demo_url']);
    $track_name = trim($_POST['track_name']);
    $instagram = trim($_POST['instagram']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    if ($artist_name && $email && $demo_url) {
        try {
            $stmt = $pdo->prepare("INSERT INTO demos (artist_name, email, demo_url, track_name, instagram, phone, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$artist_name, $email, $demo_url, $track_name, $instagram, $phone, $message]);
            $success = "Demo submitted! We'll allow 1-2 weeks for review.";
        } catch (PDOException $e) {
            $error = "Error submitting demo: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-HOUSE | Submit Demo</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-group {
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        input,
        textarea {
            width: 100%;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            font-family: inherit;
            font-size: 1rem;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--accent-color);
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <h1 class="page-title">Send Demos</h1>

        <div style="max-width: 600px; margin: 0 auto;">
            <p style="font-size: 1.2rem; margin-bottom: 3rem; color: var(--secondary-text);">
                We are always looking for the next big sound in Marathi House. Send us your best unsigned tracks.
            </p>

            <?php if ($success): ?>
                <div
                    style="padding: 1rem; background: rgba(0, 255, 149, 0.1); border: 1px solid #00FF95; color: #00FF95; margin-bottom: 2rem;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div
                    style="padding: 1rem; background: rgba(255, 0, 0, 0.1); border: 1px solid red; color: red; margin-bottom: 2rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Artist Name *</label>
                    <input type="text" name="artist_name" required>
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>SoundCloud / Dropbox Link *</label>
                    <input type="url" name="demo_url" placeholder="https://" required>
                </div>

                <div class="form-group">
                    <label>Track Name / Album Title</label>
                    <input type="text" name="track_name">
                </div>

                <div class="form-row">
                    <div>
                        <label>Instagram ID</label>
                        <input type="text" name="instagram" placeholder="@user">
                    </div>
                    <div>
                        <label>Phone Number</label>
                        <input type="tel" name="phone">
                    </div>
                </div>

                <div class="form-group">
                    <label>Message / Bio</label>
                    <textarea name="message" rows="5"></textarea>
                </div>

                <button type="submit" class="btn"
                    style="width: 100%; border: none; cursor: pointer; background: var(--accent-color); color: white;">Submit
                    Demo</button>
            </form>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>

</html>