<?php
// backend/track_visit.php
require_once __DIR__ . '/db.php';

// Simple visit tracking
try {
    $url = $_SERVER['REQUEST_URI'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    // Optional: Ignore admin pages or assets
    if (strpos($url, '/admin') === false && strpos($url, 'assets/') === false) {
        $stmt = $pdo->prepare("INSERT INTO page_views (page_url, ip_address, user_agent) VALUES (?, ?, ?)");
        $stmt->execute([$url, $ip, $ua]);
    }
} catch (Exception $e) {
    // Silently fail logging to not disrupt user flow
}
?>