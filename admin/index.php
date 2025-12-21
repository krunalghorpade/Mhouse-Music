<?php
ob_start();
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../backend/db.php';

$error = '';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php"); // Redirect to self (dashboard)
        exit;
    } else {
        $error = "Invalid credentials";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Check Auth
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M-House Admin</title>
    <!-- iOS Theme -->
    <link rel="stylesheet" href="/admin/admin.css">
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <?php if (!$isLoggedIn): ?>
        <!-- LOGIN VIEW -->
        <div style="display: flex; justify-content: center; align-items: center; height: 100vh;">
            <div class="card" style="width: 100%; max-width: 400px;">
                <h1 style="text-align: center; margin-bottom: 2rem;">Admin Login</h1>

                <?php if ($error): ?>
                    <div style="color: var(--ios-red); text-align: center; margin-bottom: 1rem;"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="login" class="ios-btn" style="width: 100%;">Login</button>
                </form>
            </div>
        </div>
    <?php else: ?>

        <div class="admin-container">
            <!-- Sidebar -->
            <nav class="sidebar">
                <div class="brand">
                    Admin Panel
                    <button id="mobile-close-btn" class="ios-btn-text"
                        style="float: right; font-size: 1.5rem; display: none;">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>

                <a href="?view=dashboard"
                    class="nav-item <?php echo (!isset($_GET['view']) || $_GET['view'] == 'dashboard') ? 'active' : ''; ?>">
                    <ion-icon name="grid-outline"></ion-icon> Dashboard
                </a>
                <a href="?view=artists" class="nav-item <?php echo ($_GET['view'] ?? '') == 'artists' ? 'active' : ''; ?>">
                    <ion-icon name="people-outline"></ion-icon> Artists
                </a>
                <a href="?view=releases"
                    class="nav-item <?php echo ($_GET['view'] ?? '') == 'releases' ? 'active' : ''; ?>">
                    <ion-icon name="musical-notes-outline"></ion-icon> Releases
                </a>
                <a href="?view=merch" class="nav-item <?php echo ($_GET['view'] ?? '') == 'merch' ? 'active' : ''; ?>">
                    <ion-icon name="shirt-outline"></ion-icon> Shop
                </a>

                <div style="height: 1px; background: var(--ios-separator); margin: 0.5rem 0;"></div>

                <a href="?view=demos" class="nav-item <?php echo ($_GET['view'] ?? '') == 'demos' ? 'active' : ''; ?>">
                    <ion-icon name="headset-outline"></ion-icon> Send Demos
                </a>
                <a href="?view=subscribers"
                    class="nav-item <?php echo ($_GET['view'] ?? '') == 'subscribers' ? 'active' : ''; ?>">
                    <ion-icon name="mail-outline"></ion-icon> Subscribers
                </a>
                <a href="?view=stats" class="nav-item <?php echo ($_GET['view'] ?? '') == 'stats' ? 'active' : ''; ?>">
                    <ion-icon name="stats-chart-outline"></ion-icon> Stats
                </a>

                <div style="flex:1"></div>

                <a href="../index.php" target="_blank" class="nav-item">
                    <ion-icon name="home-outline"></ion-icon> Live Site
                </a>
                <a href="?logout=1" class="nav-item" style="color: var(--ios-red);">
                    <ion-icon name="log-out-outline"></ion-icon> Logout
                </a>
            </nav>

            <!-- Loading Overlay -->
            <div id="loading-overlay" class="loading-overlay">
                <div class="spinner"></div>
                <div style="font-weight: 600; font-size: 1.1rem; color: var(--ios-text);">Saving Changes...</div>
                <div style="font-size: 0.9rem; color: var(--ios-secondary); margin-top: 5px;">Please wait while we upload
                    your files.</div>
            </div>

            <!-- Main Content -->
            <main class="main-content">
                <button id="mobile-menu-btn" class="ios-btn-text"
                    style="font-size: 2rem; margin-bottom: 1rem; display: none; min-width: 40px; min-height: 40px; cursor: pointer; z-index: 1000;">
                    <ion-icon name="menu-outline"></ion-icon>
                </button>

                <!-- Flash Messages -->
                <?php if (isset($_SESSION['flash_msg'])): ?>
                    <div
                        style="background: var(--ios-green); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); animation: slideDown 0.3s ease-out;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <ion-icon name="checkmark-circle" style="font-size: 1.2rem;"></ion-icon>
                            <strong>Success:</strong> <?php echo $_SESSION['flash_msg']; ?>
                        </div>
                    </div>
                    <?php unset($_SESSION['flash_msg']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['flash_error'])): ?>
                    <div
                        style="background: var(--ios-red); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); animation: slideDown 0.3s ease-out;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <ion-icon name="alert-circle" style="font-size: 1.2rem;"></ion-icon>
                            <strong>Error:</strong> <?php echo $_SESSION['flash_error']; ?>
                        </div>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>
                <?php
                $view = $_GET['view'] ?? 'dashboard';

                if ($view === 'dashboard') {
                    // Dashboard Stats
                    $views = $pdo->query("SELECT COUNT(*) FROM page_views")->fetchColumn();

                    // 1. Unheard demos
                    $unheard_demos = $pdo->query("SELECT COUNT(*) FROM demos WHERE status = 'not_heard'")->fetchColumn();

                    // 2. Total releases
                    $total_releases = $pdo->query("SELECT COUNT(*) FROM releases")->fetchColumn();

                    // 4. Total artists
                    $total_artists = $pdo->query("SELECT COUNT(*) FROM artists")->fetchColumn();

                    // 5. Total subscribers
                    $total_subscribers = $pdo->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
                    $this_month_subs = $pdo->query("SELECT COUNT(*) FROM subscribers WHERE YEAR(subscribed_at) = YEAR(CURRENT_DATE()) AND MONTH(subscribed_at) = MONTH(CURRENT_DATE())")->fetchColumn();

                    $recent_demos = $pdo->query("SELECT * FROM demos ORDER BY submitted_at DESC LIMIT 5")->fetchAll();
                    ?>
                    <h1 style="margin-bottom: 2rem;">Overview</h1>

                    <div class="card-grid">
                        <!-- Unheard Demos -->
                        <div class="card">
                            <div class="card-title">Unheard Demos</div>
                            <div class="card-value" style="color: var(--ios-blue);">
                                <?php echo number_format($unheard_demos); ?>
                            </div>
                        </div>

                        <!-- Total Releases -->
                        <div class="card">
                            <div class="card-title">Total Releases</div>
                            <div class="card-value"><?php echo number_format($total_releases); ?></div>
                        </div>

                        <!-- Total Artists -->
                        <div class="card">
                            <div class="card-title">Total Artists</div>
                            <div class="card-value"><?php echo number_format($total_artists); ?></div>
                        </div>

                        <!-- Subscribers -->
                        <div class="card">
                            <div class="card-title">Total Subscribers</div>
                            <div class="card-value"><?php echo number_format($total_subscribers); ?></div>
                            <div style="font-size: 0.8rem; color: var(--ios-secondary); margin-top: 0.5rem;">
                                +<?php echo number_format($this_month_subs); ?> this month
                            </div>
                        </div>

                        <!-- Page Views -->
                        <div class="card">
                            <div class="card-title">Total Page Views</div>
                            <div class="card-value"><?php echo number_format($views); ?></div>
                        </div>
                    </div>

                    <h2>Recent Demos</h2>
                    <div class="ios-list">
                        <?php foreach ($recent_demos as $demo): ?>
                            <div class="ios-list-item">
                                <div>
                                    <div style="font-weight: 600;"><?php echo htmlspecialchars($demo['artist_name']); ?></div>
                                    <div style="font-size: 0.9rem; color: var(--ios-secondary);">
                                        <?php echo htmlspecialchars($demo['track_name']); ?>
                                    </div>
                                </div>
                                <div>
                                    <a href="?view=demos&search=<?php echo urlencode($demo['artist_name']); ?>"
                                        class="ios-btn-text">View</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php
                } elseif ($view === 'artists') {
                    include 'artists_view.php';
                } elseif ($view === 'releases') {
                    include 'releases_view.php';
                } elseif ($view === 'merch') {
                    include 'merch_view.php';
                } elseif ($view === 'demos') {
                    include 'demos_view.php';
                } elseif ($view === 'subscribers') {
                    include 'subscribers_view.php';
                } elseif ($view === 'stats') {
                    include 'stats_view.php';
                }
                ?>
            </main>
        </div>

    <?php endif; ?>

    <!-- Autosave Logic -->
    <script>
        // Simple draft autosave simulation
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('input', (e) => {
                if (e.target.name) {
                    const status = document.getElementById('autosave-status');
                    if (status) {
                        status.style.display = 'block';
                        status.textContent = 'Saving...';
                        setTimeout(() => {
                            status.textContent = 'Saved to draft';
                            setTimeout(() => status.style.display = 'none', 2000);
                        }, 500);
                    }
                }
            });
        });
    </script>
    <div id="autosave-status"
        style="position: fixed; bottom: 20px; right: 20px; background: rgba(0,0,0,0.8); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.8rem; display: none; z-index: 9999;">
    </div>

    <!-- Mobile Menu Logic -->
    <script>
        function showLoading() {
            document.getElementById('loading-overlay').style.display = 'flex';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileCloseBtn = document.getElementById('mobile-close-btn');
            const sidebar = document.querySelector('.sidebar');

            if (mobileMenuBtn && sidebar) {
                mobileMenuBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    sidebar.classList.add('active');
                });
            }

            if (mobileCloseBtn && sidebar) {
                mobileCloseBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    sidebar.classList.remove('active');
                });
            }
        });
    </script>
</body>

</html>