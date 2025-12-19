<?php
require_once 'backend/db.php';

try {
    $pdo->exec("ALTER TABLE merch ADD COLUMN category TEXT"); // using 'category' for men/women etc
    echo "Added category to merch\n";
} catch (Exception $e) {
    echo "category might exist\n";
}

try {
    $pdo->exec("ALTER TABLE merch ADD COLUMN link TEXT");
    echo "Added link to merch\n";
} catch (Exception $e) {
    echo "link might exist\n";
}

// Note: We can't easily drop columns in sqlite simply but we can just ignore 'price' and 'description' if not needed, or make them nullable in code logic.
// The user said "remove the price" from display, not necessarily drop column, but I will ignore it in admin.

echo "Merch migration complete.";
?>