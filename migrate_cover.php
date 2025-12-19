<?php
require_once 'backend/db.php';

try {
    $pdo->exec("ALTER TABLE cover_settings ADD COLUMN sub_text TEXT");
    echo "Added sub_text\n";
} catch (Exception $e) {
    echo "sub_text might exist\n";
}

try {
    $pdo->exec("ALTER TABLE cover_settings ADD COLUMN video_url TEXT");
    echo "Added video_url\n";
} catch (Exception $e) {
    echo "video_url might exist\n";
}

try {
    $pdo->exec("ALTER TABLE cover_settings ADD COLUMN highlight_text TEXT");
    echo "Added highlight_text\n";
} catch (Exception $e) {
    echo "highlight_text might exist\n";
}

echo "Migration complete.";
?>