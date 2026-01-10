<?php include_once 'backend/track_visit.php'; ?>
<div id="loader">
    <img src="assets/images/site-logo.svg" alt="Loading..." class="loader-logo">
</div>

<header class="<?php echo (isset($is_homepage) && $is_homepage) ? 'transparent' : ''; ?>">
    <a href="index" class="brand">
        <img src="assets/images/site-logo.svg" alt="M-House Music" class="logo-img">
    </a>

    <!-- Desktop Nav -->
    <nav class="desktop-nav">
        <!-- Reordered -->
        <a href="merch">Merch</a>
        <a href="releases">Music</a>

        <a href="artists">Artists</a>
        <a href="social">Social</a>
        <a href="demos">Send Demos</a> <!-- Updated -->
        <a href="https://kratex.in" target="_blank">Kratex&trade;</a>
        <a href="about">About</a>
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
    <button class="close-menu-btn">&times;</button>
    <a href="merch">Merch</a>
    <a href="releases">Music</a>

    <a href="artists">Artists</a>
    <a href="social">Social</a>
    <a href="demos">Send Demos</a>
    <a href="https://kratex.in" target="_blank">Kratex&trade;</a>
    <a href="about">About</a>
</div>