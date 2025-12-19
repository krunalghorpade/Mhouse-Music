<?php
require_once __DIR__ . '/db.php';

try {
    $pdo->exec("ALTER TABLE demos ADD COLUMN status TEXT DEFAULT 'not_heard'");
    echo "Migration successful: Added 'status' column to 'demos' table.\n";
} catch (PDOException $e) {
    echo "Migration failed (or column already exists): " . $e->getMessage() . "\n";
}
?>