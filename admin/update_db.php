<?php
// admin/update_db.php
require_once '../backend/db.php';

echo "<h1>Database Updater</h1>";

$columns = [
    'legal_name' => 'VARCHAR(255) NULL',
    'address' => 'TEXT NULL',
    'pan_number' => 'VARCHAR(100) NULL',
    'govt_id_number' => 'VARCHAR(100) NULL'
];

foreach ($columns as $column => $definition) {
    try {
        $check = $pdo->query("SHOW COLUMNS FROM `artists` LIKE '$column'");
        if ($check->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `artists` ADD COLUMN `$column` $definition");
            echo "<p style='color:green;'>Successfully added column: <b>$column</b></p>";
        } else {
            echo "<p style='color:gray;'>Column already exists: <b>$column</b></p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Error adding $column: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Update Complete!</h3>";
echo "<p><a href='index.php?view=onboarding'>Go back to Onboarding</a></p>";
?>
