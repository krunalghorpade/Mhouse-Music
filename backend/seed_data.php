<?php
require_once __DIR__ . '/db.php';

echo "Seeding database...\n";

// Clear existing data (optional, but good for idempotent seeding)
// $pdo->exec("DELETE FROM releases");
// $pdo->exec("DELETE FROM artists");
// $pdo->exec("DELETE FROM merch");
// $pdo->exec("DELETE FROM dj_sets");

// --- ARTISTS ---
$stmt = $pdo->query("SELECT COUNT(*) FROM artists");
if ($stmt->fetchColumn() > 0) {
    echo "Artists table not empty. Skipping artist seeding.\n";
} else {
    echo "Seeding Artists...\n";
    $artists = [
        ['name' => 'Kratex', 'bio' => 'Founder of M-House Music. Fusing Marathi folk with Deep House.', 'image_url' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?auto=format&fit=crop&w=800&q=80'],
        ['name' => 'Ajay-Atul (Remix)', 'bio' => 'Legendary duo, reimagined for the club.', 'image_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80'],
        ['name' => 'DJ Chetas', 'bio' => 'Bollywood mashup king.', 'image_url' => 'https://images.unsplash.com/photo-1493225255756-d9584f8606e9?auto=format&fit=crop&w=800&q=80'],
        ['name' => 'Nucleya', 'bio' => 'Bass Raja.', 'image_url' => 'https://images.unsplash.com/photo-1549834125-9ca0f8a5baa4?auto=format&fit=crop&w=800&q=80'],
    ];

    $stmt = $pdo->prepare("INSERT INTO artists (name, bio, image_url) VALUES (:name, :bio, :image_url)");
    foreach ($artists as $artist) {
        $stmt->execute($artist);
    }
}

// --- RELEASES ---
$stmt = $pdo->query("SELECT COUNT(*) FROM releases");
if ($stmt->fetchColumn() > 0) {
    echo "Releases table not empty. Skipping releases seeding.\n";
} else {
    echo "Seeding Releases...\n";
    $allArtists = $pdo->query("SELECT id, name FROM artists")->fetchAll();

    $releases = [
        ['title' => 'Zingaat (Deep House Edit)', 'artist_name' => 'Kratex', 'cover_url' => 'https://images.unsplash.com/photo-1614613535308-eb5fbd3d2c17?auto=format&fit=crop&w=800&q=80', 'release_date' => '2025-11-15', 'type' => 'Single'],
        ['title' => 'Apsara Aali (Club Mix)', 'artist_name' => 'Ajay-Atul (Remix)', 'cover_url' => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=800&q=80', 'release_date' => '2025-10-01', 'type' => 'Single'],
        ['title' => 'Marathi House Vol 1', 'artist_name' => 'Kratex', 'cover_url' => 'https://images.unsplash.com/photo-1514525253440-b393452de23e?auto=format&fit=crop&w=800&q=80', 'release_date' => '2025-08-20', 'type' => 'EP'],
        ['title' => 'Dolby Walya (Tech House)', 'artist_name' => 'Kratex', 'cover_url' => 'https://images.unsplash.com/photo-1496293455970-f8581aae0e3c?auto=format&fit=crop&w=800&q=80', 'release_date' => '2025-07-04', 'type' => 'Single'],
        ['title' => 'Bring It Back', 'artist_name' => 'Nucleya', 'cover_url' => 'https://images.unsplash.com/photo-1487215078519-e2b1352756fe?auto=format&fit=crop&w=800&q=80', 'release_date' => '2025-05-10', 'type' => 'Single'],
        ['title' => 'Mumbai Drift', 'artist_name' => 'DJ Chetas', 'cover_url' => 'https://images.unsplash.com/photo-1516280440614-6697288d5d38?auto=format&fit=crop&w=800&q=80', 'release_date' => '2025-01-01', 'type' => 'Album']
    ];

    // Updated Insert for Releases (without artist_id)
    $stmt = $pdo->prepare("INSERT INTO releases (title, cover_url, release_date, type, description) VALUES (:title, :cover_url, :release_date, :type, :description)");

    // Insert for Release_Artists
    $stmtLink = $pdo->prepare("INSERT INTO release_artists (release_id, artist_id) VALUES (?, ?)");

    foreach ($releases as $release) {
        $foundArtists = [];
        // Support multiple artists via comma in seed name e.g. "Kratex, Nucleya"
        $artistNames = explode(',', $release['artist_name']);

        foreach ($artistNames as $name) {
            $name = trim($name);
            foreach ($allArtists as $a) {
                if ($a['name'] === $name) {
                    $foundArtists[] = $a['id'];
                    break;
                }
            }
        }

        // Fallback
        if (empty($foundArtists))
            $foundArtists[] = $allArtists[0]['id'];

        // Add description to seed data if missing
        $desc = $release['description'] ?? "A masterpiece of fusion music, blending traditional beats with modern electronic landscapes.";

        $stmt->execute([
            ':title' => $release['title'],
            ':cover_url' => $release['cover_url'],
            ':release_date' => $release['release_date'],
            ':type' => $release['type'],
            ':description' => $desc
        ]);

        $releaseId = $pdo->lastInsertId();

        foreach ($foundArtists as $aId) {
            $stmtLink->execute([$releaseId, $aId]);
        }
    }
}

// --- MERCH ---
$stmt = $pdo->query("SELECT COUNT(*) FROM merch");
if ($stmt->fetchColumn() > 0) {
    echo "Merch table not empty. Skipping merch seeding.\n";
} else {
    echo "Seeding Merch...\n";
    $merch = [
        ['name' => 'M-HOUSE Signature Tee', 'price' => 2500.00, 'image_url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=800&q=80', 'description' => 'Heavyweight cotton oversized tee with puff print.'],
        ['name' => 'Vinyl Collection Vol. 1', 'price' => 3500.00, 'image_url' => 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?auto=format&fit=crop&w=800&q=80', 'description' => 'Limited edition transparent vinyl.'],
        ['name' => 'Rave Cap', 'price' => 1200.00, 'image_url' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?auto=format&fit=crop&w=800&q=80', 'description' => 'Embroidered logo dad hat.'],
        ['name' => 'Tote Bag', 'price' => 800.00, 'image_url' => 'https://images.unsplash.com/photo-1597484661643-2f6f33261563?auto=format&fit=crop&w=800&q=80', 'description' => 'Canvas tote for your records.']
    ];
    $stmt = $pdo->prepare("INSERT INTO merch (name, price, image_url, description) VALUES (:name, :price, :image_url, :description)");
    foreach ($merch as $item) {
        $stmt->execute($item);
    }
}

// --- DJ SETS REMOVED ---


echo "Seeding News...\n";
$news = [
    [
        'title' => 'The Evolution of M-House: A New Chapter Begins',
        'content' => "We are incredibly proud to unveil the next phase of M-House Music. What started as a small experiment in a Mumbai bedroom has grown into a movement that bridges the gap between traditional Maharashtrian folk and cutting-edge electronic music.\n\nOur new platform is not just a website; it's a hub for culture, sound, and innovation. We have streamlined our release schedule, upgraded our production facilities, and signed three new artists who define the future of the genre.\n\n\"This is just the beginning,\" says Kratex. \"We want to take the sound of the dhol, the tutari, and the lejim to the mainstages of Tomorrowland and Ultra.\"",
        'image_url' => 'https://images.unsplash.com/photo-1493225255756-d9584f8606e9?auto=format&fit=crop&w=800&q=80',
        'published_date' => '2025-12-01 10:00:00'
    ],
    [
        'title' => 'Tour Diary: Chaos and Magic in Pune',
        'content' => "Last night's gig at The High Spirits in Pune was nothing short of legendary. The energy of the crowd was palpable from the moment the first beat dropped. We debuted three new IDs, including a heavy-hitting remix of 'Jhingat' that nearly brought the roof down.\n\nCheck out the photo gallery below to see the madness. Special thanks to the crew for handling the sound issues during soundcheck like pros. We'll be back in Pune next month for the NH7 Weekender pre-party.",
        'image_url' => 'https://images.unsplash.com/photo-1501386761106-2c388191643c?auto=format&fit=crop&w=800&q=80',
        'published_date' => '2025-11-20 09:00:00'
    ],
    [
        'title' => 'Limited Edition: "Roots" Vinyl Collection',
        'content' => "For the audiophiles and the collectors, we present 'Roots'. This limited edition vinyl box set compiles the first 20 releases of M-House Music, remastered specifically for analog playback.\n\nThe box set includes:\n- 4x 180g Transparent Orange Vinyls\n- A 32-page booklet with liner notes and behind-the-scenes photos\n- An exclusive slipmat designed by local artist Aarav Patel.\n\nOnly 500 copies exist worldwide. Get yours in the Merch store now before they sell out.",
        'image_url' => 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?auto=format&fit=crop&w=800&q=80',
        'published_date' => '2025-11-05 14:00:00'
    ],
    [
        'title' => 'In Conversation: Nucleya on the M-House Sound',
        'content' => "We sat down with the 'Bass Raja' himself, Nucleya, to discuss the rising wave of regional electronic music in India.\n\nNucleya: \"What M-House is doing is essential. It's not just about adding a vocal sample; it's about understanding the rhythm structure of folk music and translating that into House and Techno. It's authentic.\"\n\nRead the full interview to hear his thoughts on collaboration, the importance of regional identity, and his upcoming track with Kratex.",
        'image_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80',
        'published_date' => '2025-10-15 11:00:00'
    ],
    [
        'title' => 'Beatport Chart Topper: "Zingaat (Deep Edit)"',
        'content' => "We did it! 'Zingaat (Deep House Edit)' has officially entered the Top 10 on the Beatport Deep House Charts, sitting comfortably at #7 alongside giants like Black Coffee and Solomun.\n\nThis is a massive milestone for Marathi electronic music. Thank you to everyone who streamed, purchased, and played the track. Your support proves that language is no barrier to a good groove.",
        'image_url' => 'https://images.unsplash.com/photo-1514525253440-b393452de23e?auto=format&fit=crop&w=800&q=80',
        'published_date' => '2025-11-18 16:20:00'
    ]
];
$stmt = $pdo->prepare("INSERT INTO news (title, content, image_url, published_date) VALUES (:title, :content, :image_url, :published_date)");
foreach ($news as $item) {
    $stmt->execute($item);
}


echo "Seeding complete.\n";
