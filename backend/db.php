<?php
// backend/db.php

$db_dir = __DIR__ . '/../data';
$db_file = $db_dir . '/database.sqlite';

// Ensure data directory exists
if (!file_exists($db_dir)) {
    mkdir($db_dir, 0777, true);
}

try {
    // Create (connect to) SQLite database in file
    $pdo = new PDO("sqlite:" . $db_file);
    // Set errormode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create tables if they do not exist
    $commands = [
        "CREATE TABLE IF NOT EXISTS artists (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            bio TEXT,
            image_url TEXT,
            social_links TEXT, -- JSON string
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS releases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            cover_url TEXT,
            release_date DATE,
            platform_links TEXT, -- JSON string
            type TEXT, -- Single, Album, EP
            description TEXT, -- Added for track description
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS release_artists (
            release_id INTEGER,
            artist_id INTEGER,
            PRIMARY KEY (release_id, artist_id),
            FOREIGN KEY (release_id) REFERENCES releases(id) ON DELETE CASCADE,
            FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS enquiries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            message TEXT NOT NULL,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS merch (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            image_url TEXT,
            description TEXT,
            status TEXT DEFAULT 'available',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS news (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            content TEXT,
            image_url TEXT,
            published_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS demos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            artist_name TEXT NOT NULL,
            email TEXT NOT NULL,
            demo_url TEXT NOT NULL,
            track_name TEXT,
            instagram TEXT,
            phone TEXT,
            message TEXT,
            rating INTEGER DEFAULT 0,
            status TEXT DEFAULT 'not_heard',
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS subscribers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS page_views (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            page_url TEXT,
            ip_address TEXT,
            user_agent TEXT,
            visited_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS cover_settings (
            id INTEGER PRIMARY KEY CHECK (id = 1),
            tag_text TEXT,
            main_text TEXT,
            sub_text TEXT,
            highlight_text TEXT,
            button_label TEXT,
            button_link TEXT,
            image_url TEXT,
            video_url TEXT
        )"
    ];

    foreach ($commands as $command) {
        $pdo->exec($command);
    }

    // Seed default admin user if not exists (admin / admin123) - FOR DEV ONLY
    // In production, you'd want a registration script or manually add the hash.
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES ('admin', :pass)");
        $stmt->execute([':pass' => $password]);
    }

} catch (PDOException $e) {
    // For a production app you wouldn't echo this directly, but for dev it helps debugging
    die("Connection failed: " . $e->getMessage());
}
?>