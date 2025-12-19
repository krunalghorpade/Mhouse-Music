<?php
// backend/db.php

// Database Configuration
$host = 'localhost';
$dbname = 'kratexin_mhousemusic';
$username = 'kratexin_kratexin';
$password = 'adminpass123sql';

// Override with local configuration if available
if (file_exists(__DIR__ . '/config.local.php')) {
    include __DIR__ . '/config.local.php';
}

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set errormode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create tables if they do not exist (MySQL Compatible)
    $commands = [
        "CREATE TABLE IF NOT EXISTS artists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            bio TEXT,
            image_url VARCHAR(255),
            social_links TEXT, -- JSON string
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS releases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            cover_url VARCHAR(255),
            release_date DATE,
            platform_links TEXT, -- JSON string
            type VARCHAR(50), -- Single, Album, EP
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS release_artists (
            release_id INT,
            artist_id INT,
            PRIMARY KEY (release_id, artist_id),
            FOREIGN KEY (release_id) REFERENCES releases(id) ON DELETE CASCADE,
            FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS enquiries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS merch (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            category VARCHAR(50),
            link VARCHAR(255),
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            image_url VARCHAR(255),
            description TEXT,
            status VARCHAR(50) DEFAULT 'available',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",

        "CREATE TABLE IF NOT EXISTS news (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            image_url VARCHAR(255),
            published_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS demos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            artist_name VARCHAR(255) NOT NULL,
            email VARCHAR(150) NOT NULL,
            demo_url VARCHAR(255) NOT NULL,
            track_name VARCHAR(255),
            instagram VARCHAR(100),
            phone VARCHAR(50),
            message TEXT,
            rating INT DEFAULT 0,
            status VARCHAR(50) DEFAULT 'not_heard',
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(150) NOT NULL UNIQUE,
            subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS page_views (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_url VARCHAR(255),
            ip_address VARCHAR(50),
            user_agent VARCHAR(255),
            visited_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS cover_settings (
            id INT PRIMARY KEY,
            tag_text VARCHAR(255),
            main_text TEXT,
            sub_text VARCHAR(255),
            highlight_text VARCHAR(255),
            button_label VARCHAR(50),
            button_link VARCHAR(255),
            image_url VARCHAR(255),
            video_url VARCHAR(255)
        )"
    ];

    foreach ($commands as $command) {
        $pdo->exec($command);
    }

    // Seed default admin user if not exists (admin / admin123)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES ('admin', :pass)");
        $stmt->execute([':pass' => $password]);
    }

    // Seed default cover setting if not exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM cover_settings WHERE id = 1");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO cover_settings (id, main_text) VALUES (1, 'M-HOUSE MUSIC')");
    }

} catch (PDOException $e) {
    // For a production app you wouldn't echo this directly, but for dev it helps debugging
    // die("Connection failed: " . $e->getMessage());
    // Fallback or silent fail might be better in prod, but echoing for debug provided per user request
    die("DB Connection failed: " . $e->getMessage());
}
?>