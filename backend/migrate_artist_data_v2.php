<?php
// backend/migrate_artist_data_v2.php
require_once __DIR__ . '/db.php';

echo "Starting Artist Data Migration (v2)...\n";

try {
    // 1. Add new columns to artists table if they don't exist
    $columnsToAdd = [
        'legal_name' => "VARCHAR(255)",
        'address' => "TEXT",
        'pan_number' => "VARCHAR(50)",
        'govt_id_number' => "VARCHAR(100)"
    ];

    foreach ($columnsToAdd as $col => $type) {
        try {
            $pdo->exec("ALTER TABLE artists ADD COLUMN $col $type");
            echo "Added '$col' column to 'artists'.\n";
        } catch (PDOException $e) {
            // Check if error is because column exists
            echo "Column '$col' likely already exists or error: " . $e->getMessage() . "\n";
        }
    }

    // 2. Migrate Data from artist_submissions to artists
    // Match by email or some unique identifier if possible, but here we might rely on manual association 
    // OR just update existing artists if we can link them.
    // However, the prompt implies we need this data for *contract generation*. 
    // Since `artist_submissions` contains the source of truth for these details, 
    // and `artists` table was previously just for display (name, bio, image),
    // we should try to update `artists` records where the name matches `stage_name` or `full_name` from submissions.

    $submissions = $pdo->query("SELECT * FROM artist_submissions WHERE status = 'approved'")->fetchAll();

    $updatedCount = 0;
    foreach ($submissions as $sub) {
        // Try to find matching artist
        // Priority: Match by Name (since that's what we have)
        $artistName = !empty($sub['stage_name']) ? $sub['stage_name'] : $sub['full_name'];

        $stmt = $pdo->prepare("SELECT id FROM artists WHERE name = ?");
        $stmt->execute([$artistName]);
        $artist = $stmt->fetch();

        if ($artist) {
            // Update the artist record with missing details
            $updateSql = "UPDATE artists SET 
                legal_name = :legal_name,
                address = :address,
                pan_number = :pan_number,
                govt_id_number = :govt_id_number
                WHERE id = :id";

            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([
                ':legal_name' => $sub['full_name'],
                ':address' => $sub['address'],
                ':pan_number' => $sub['pan_number'],
                ':govt_id_number' => $sub['govt_id_number'],
                ':id' => $artist['id']
            ]);
            $updatedCount++;
        }
    }

    echo "Updated $updatedCount artist records with legal details from submissions.\n";
    echo "Migration Complete.\n";

} catch (PDOException $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
?>