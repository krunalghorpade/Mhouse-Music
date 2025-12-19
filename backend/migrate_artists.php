<?php
// backend/migrate_artists.php
require_once __DIR__ . '/db.php';

echo "Starting migration...\n";

try {
    // 1. Add description column to releases
    try {
        $pdo->exec("ALTER TABLE releases ADD COLUMN description TEXT");
        echo "Added 'description' column to 'releases'.\n";
    } catch (PDOException $e) {
        echo "Column 'description' likely already exists or error: " . $e->getMessage() . "\n";
    }

    // 2. Create release_artists table
    $pdo->exec("CREATE TABLE IF NOT EXISTS release_artists (
        release_id INTEGER,
        artist_id INTEGER,
        PRIMARY KEY (release_id, artist_id),
        FOREIGN KEY (release_id) REFERENCES releases(id) ON DELETE CASCADE,
        FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
    )");
    echo "Created (or ensured) 'release_artists' table.\n";

    // 3. Migrate Data
    // Check if artist_id column exists in releases by trying to select it
    // SQLite doesn't easily let us check column existence without PRAGMA, but select is safe enough
    try {
        $releases = $pdo->query("SELECT id, artist_id FROM releases WHERE artist_id IS NOT NULL")->fetchAll();
        $count = 0;
        foreach ($releases as $rel) {
            // Insert into join table if not exists
            $stmt = $pdo->prepare("INSERT IGNORE INTO release_artists (release_id, artist_id) VALUES (?, ?)");
            $stmt->execute([$rel['id'], $rel['artist_id']]);
            $count++;
        }
        echo "Migrated $count artist associations to 'release_artists'.\n";
    } catch (PDOException $e) {
        echo "Could not migrate data (column artist_id might be missing or other error): " . $e->getMessage() . "\n";
    }

    // 4. Drop dj_sets
    $pdo->exec("DROP TABLE IF EXISTS dj_sets");
    echo "Dropped 'dj_sets' table.\n";

    echo "Migration Complete.\n";

} catch (PDOException $e) {
    echo "Fatal Migration Error: " . $e->getMessage() . "\n";
}
?>