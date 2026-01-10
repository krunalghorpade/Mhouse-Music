<?php
/**
 * router.php
 * This script allows the PHP built-in web server to handle extension-less URLs
 * like /merch, /artists, etc. while still serving static assets correctly.
 */

$uri = $_SERVER['REQUEST_URI'];
$path = urldecode(parse_url($uri, PHP_URL_PATH));

// 1. If the path leads to an actual file (like .css, .js, .png), serve it as-is.
if ($path !== '/' && file_exists(__DIR__ . $path)) {
    return false;
}

// 2. If the path has no extension and a corresponding .php file exists, serve that .php file.
if (file_exists(__DIR__ . $path . '.php')) {
    // Preserve query parameters for the script
    require_once __DIR__ . $path . '.php';
    exit;
}

// 3. If it's the root or anything else not matched, let the server handle it normally (defaulting to index.php if it exists).
return false;
?>