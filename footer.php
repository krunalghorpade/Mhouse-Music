<?php
// Try to fetch artists for footer if not already available
if (!isset($footer_artists)) {
    try {
        // reuse existing $pdo if available, otherwise connect? 
        // usually included in pages which already require db, but let's check global $pdo
        if (isset($pdo)) {
            $footer_artists = $pdo->query("SELECT name, id FROM artists ORDER BY name ASC")->fetchAll();
        } else {
            $footer_artists = [];
        }
    } catch (Exception $e) {
        $footer_artists = [];
    }
}
?>
<footer
    style="padding: 4rem 2rem; border-top: 1px solid var(--border-color); margin-top: auto; background: var(--bg-color);">
    <div class="container"
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 3rem;">

        <!-- col 1: Brand -->
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <a href="index.php">
                <img src="assets/images/site-logo.svg" alt="M-HOUSE" style="height: 30px; width: auto;">
            </a>
            <div class="copyright" style="font-size: 0.8rem; color: var(--secondary-text);">
                &copy; <?php echo date('Y'); ?> M-House Music.<br>
                All Rights Reserved.
            </div>
        </div>

        <!-- col 2: Links -->
        <div style="display: flex; flex-direction: column; gap: 0.8rem;">
            <h4 style="font-size: 0.9rem; margin-bottom: 0.5rem; color: var(--secondary-text);">Menu</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 2rem;">
                <a href="releases.php" style="font-size: 0.9rem;">Music</a>
                <a href="artists.php" style="font-size: 0.9rem;">Artists</a>

                <a href="merch.php" style="font-size: 0.9rem;">Merch</a>
                <a href="demos.php" style="font-size: 0.9rem;">Send Demos</a>

                <a href="about.php" style="font-size: 0.9rem;">About</a>
            </div>
        </div>

        <!-- col 3: Connect -->
        <div style="display: flex; flex-direction: column; gap: 0.8rem;">
            <h4 style="font-size: 0.9rem; margin-bottom: 0.5rem; color: var(--secondary-text);">Connect</h4>
            <a href="mailto:contact@mhousemusic.com" style="font-size: 0.9rem;">Contact Us</a>
            <a href="https://instagram.com/mhouse.music" target="_blank" style="font-size: 0.9rem;">Instagram</a>
            <a href="https://youtube.com" target="_blank" style="font-size: 0.9rem;">YouTube</a>
            <a href="https://www.beatport.com" target="_blank" style="font-size: 0.9rem;">Beatport</a>
            <a href="https://kratex.in" target="_blank" style="font-size: 0.9rem;">Kratex&trade;</a>
        </div>

        <!-- col 4: Roster -->
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <h4 style="font-size: 0.9rem; margin-bottom: 0.5rem; color: var(--secondary-text);">Artists</h4>
            <div style="display: flex; flex-wrap: wrap; gap: 0.8rem 1rem;">
                <?php foreach ($footer_artists as $f_artist): ?>
                    <a href="artist.php?id=<?php echo $f_artist['id']; ?>"
                        style="font-size: 0.85rem; color: var(--secondary-text);"><?php echo htmlspecialchars($f_artist['name']); ?></a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</footer>

<!-- Scripts -->
<script src="assets/js/main.js"></script>