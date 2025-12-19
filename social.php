<?php
require_once 'backend/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)");
            $stmt->execute([$email]);
            $message = "Subscribed successfully.";
        } catch (PDOException $e) {
            $message = "Already subscribed or error occurred.";
        }
    } else {
        $message = "Invalid email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-HOUSE MUSIC | Social</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .social-grid {
            margin-top: 4rem;
            display: grid;
            gap: 4rem;
        }

        @media(min-width: 900px) {
            .social-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .social-link-large {
            font-size: clamp(2rem, 6vw, 1rem);
            font-weight: 700;
            text-transform: uppercase;
            display: block;
            border-bottom: 2px solid var(--border-color);
            padding: 2rem 0;
            line-height: 1;
        }

        .social-link-large:hover {
            padding-left: 2rem;
            color: var(--accent-color);
            border-color: var(--accent-color);
            transition: all 0.3s ease;
        }

        .subscribe-box {
            background: #1a1a1a;
            padding: 3rem;
            text-align: center;
        }

        .subscribe-box input {
            padding: 1rem;
            width: 100%;
            max-width: 400px;
            margin-bottom: 1rem;
            background: transparent;
            border: 1px solid white;
            color: white;
            font-size: 1rem;
        }
    </style>
    <link rel="icon" type="image/png" href="assets/images/icon.png">
</head>

<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <div class="social-grid">
            <!-- Left: Links & Subscribe -->
            <div>
                <h1 class="page-title" style="margin-bottom: 2rem;">Connect</h1>

                <a href="https://instagram.com/mhouse.music" target="_blank" class="social-link-large">Instagram
                    <span>↗</span></a>
                <a href="https://soundcloud.com" target="_blank" class="social-link-large">SoundCloud <span>↗</span></a>
                <a href="https://youtube.com" target="_blank" class="social-link-large">YouTube <span>↗</span></a>
                <a href="mailto:contact@mhousemusic.com" class="social-link-large">Email <span>↗</span></a>

                <div class="subscribe-box" style="margin-top: 4rem;">
                    <h2 class="uppercase" style="margin-bottom: 1rem;">Join the List</h2>
                    <p style="margin-bottom: 2rem; color: var(--secondary-text);">Priority access to releases, merch,
                        and events.</p>

                    <?php if ($message): ?>
                        <p style="color: var(--accent-color); margin-bottom: 1rem;"><?php echo $message; ?></p>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="email" name="email" placeholder="YOUR@EMAIL.COM" required>
                        <br>
                        <button type="submit" class="btn" style="width: 100%; max-width: 400px;">Subscribe</button>
                    </form>
                </div>
            </div>

            <!-- Right: Instagram Embed -->
            <div>
                <h2 class="uppercase" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                    @mhouse.music</h2>
                <!-- Placeholder for Instagram Feed -->
                <div
                    style="background: #111; width: 100%; display: flex; justify-content: center; align-items: center; border: 1px solid #333; overflow: hidden;">
                    <div style="text-align: center; width: 100%;">
                        <!-- In prod, use a widget like SnapWidget or official Instagram Display API -->
                        <!-- Added invert filter to simulate dark mode (Note: this inverts images too, effective dark mode requires API) -->
                        <iframe src="https://www.instagram.com/mhouse.music/embed" width="100%" height="780"
                            frameborder="0" scrolling="yes" allowtransparency="true"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>

</html>