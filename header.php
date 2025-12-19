<?php include_once 'backend/track_visit.php'; ?>
<div id="loader">
    <img src="assets/images/site-logo.svg" alt="Loading..." class="loader-logo">
</div>

<header class="<?php echo (isset($is_homepage) && $is_homepage) ? 'transparent' : ''; ?>">
    <a href="index.php" class="brand">
        <img src="assets/images/site-logo.svg" alt="M-House Music" class="logo-img">
    </a>

    <!-- Desktop Nav -->
    <nav class="desktop-nav">
        <!-- Reordered -->
        <a href="merch.php">Merch</a>
        <a href="releases.php">Music</a>
        <a href="news.php">News</a>

        <a href="artists.php">Artists</a>
        <a href="social.php">Social</a>
        <a href="demos.php">Demos</a> <!-- Updated -->
        <a href="https://kratex.in" target="_blank">Kratex&trade;</a>
        <a href="about.php">About</a>
    </nav>

    <!-- Mobile Nav Toggle -->
    <div class="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>
</header>

<!-- Mobile Nav Overlay -->
<div class="mobile-nav">
    <a href="merch.php">Merch</a>
    <a href="releases.php">Music</a>
    <a href="news.php">News</a>

    <a href="artists.php">Artists</a>
    <a href="social.php">Social</a>
    <a href="demos.php">Demos</a>
    <a href="https://kratex.in" target="_blank">Kratex&trade;</a>
    <a href="about.php">About</a>
</div>

<script src="assets/js/main.js"></script>