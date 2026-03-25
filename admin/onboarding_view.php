<?php
// admin/onboarding_view.php
require_once '../backend/mailer.php';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action'];
    $email = $_POST['email'];
    $name = $_POST['full_name'];

    // Fetch submission details for data transfer
    $stmt = $pdo->prepare("SELECT * FROM artist_submissions WHERE id = ?");
    $stmt->execute([$id]);
    $submission = $stmt->fetch();

    if ($submission) {
        if ($action === 'approve') {
            // 1. Create Artist in main table
            // Use Stage Name if provided, else Full Name
            $artistName = !empty($submission['stage_name']) ? $submission['stage_name'] : $submission['full_name'];

            $stmtInsert = $pdo->prepare("INSERT INTO artists (name, legal_name, address, pan_number, govt_id_number, bio, image_url, social_links) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([
                $artistName,
                $submission['full_name'], // legal_name
                $submission['address'],
                $submission['pan_number'],
                $submission['govt_id_number'],
                $submission['bio'],
                $submission['image_url'], // Use the uploaded profile image
                $submission['social_links']
            ]);

            // 2. Update Status
            $stmtUpdate = $pdo->prepare("UPDATE artist_submissions SET status = 'approved' WHERE id = ?");
            $stmtUpdate->execute([$id]);

            // 3. Send Email
            $subject = "Welcome to M-House Music!";
            $message = "
                <h1>Welcome, $name!</h1>
                <p>We are thrilled to inform you that your artist application has been approved.</p>
                <p>You are now officially part of the M-House Music collective. Our team will contact you shortly regarding contracts and next steps.</p>
                <br>
                <p>Cheers,<br>M-House Team</p>
            ";
            sendEmail($email, $subject, $message);

            $_SESSION['flash_msg'] = "Artist approved and notified.";

        } elseif ($action === 'reject') {
            // 1. Update Status
            $stmtUpdate = $pdo->prepare("UPDATE artist_submissions SET status = 'rejected' WHERE id = ?");
            $stmtUpdate->execute([$id]);

            // 2. Send Email
            $subject = "Update on your M-House Music Application";
            $message = "
                <p>Dear $name,</p>
                <p>Thank you for your interest in M-House Music. After careful review, we have decided not to proceed with your application at this time.</p>
                <p>If you have any questions, please feel free to reach out to us at contact@mhousemusic.com.</p>
                <br>
                <p>Best,<br>M-House Team</p>
            ";
            sendEmail($email, $subject, $message);

            $_SESSION['flash_msg'] = "Application rejected and applicant notified.";
        }
    }

    // Redirect to list
    header("Location: ?view=onboarding");
    exit;
}

// Fetch Pending Submissions
$stmt = $pdo->query("SELECT * FROM artist_submissions WHERE status = 'pending' ORDER BY created_at DESC");
$submissions = $stmt->fetchAll();

// Detail View Logic
$detailId = $_GET['id'] ?? null;
$detailSubmission = null;
if ($detailId) {
    $stmt = $pdo->prepare("SELECT * FROM artist_submissions WHERE id = ?");
    $stmt->execute([$detailId]);
    $detailSubmission = $stmt->fetch();
}
?>

<h1>Artist Onboarding</h1>

<?php if (!$detailSubmission): ?>
    <!-- LIST VIEW -->
    <?php if (empty($submissions)): ?>
        <div style="text-align: center; color: var(--ios-secondary); margin-top: 4rem;">
            <ion-icon name="folder-open-outline" style="font-size: 3rem;"></ion-icon>
            <p>No pending applications.</p>
        </div>
    <?php else: ?>
        <div class="ios-list">
            <?php foreach ($submissions as $sub): ?>
                <div class="ios-list-item">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <img src="<?php echo !empty($sub['image_url']) ? '/' . ltrim($sub['image_url'], '/') : '/assets/images/icon.png'; ?>"
                            style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 1px solid var(--ios-separator);">
                        <div>
                            <div style="font-weight: 700; font-size: 1.1rem; color: var(--ios-text);">
                                <?php echo htmlspecialchars($sub['stage_name'] ?: $sub['full_name']); ?>
                            </div>
                            <div
                                style="font-size: 0.85rem; color: var(--ios-secondary); text-transform: uppercase; letter-spacing: 0.5px;">
                                <?php echo htmlspecialchars($sub['full_name']); ?>
                                •
                                <?php echo date('M d, Y', strtotime($sub['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="?view=onboarding&id=<?php echo $sub['id']; ?>" class="ios-btn">Review</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- DETAIL VIEW -->
    <?php
    $socials = json_decode($detailSubmission['social_links'], true);
    ?>
    <div style="margin-bottom: 2rem;">
        <a href="?view=onboarding"
            style="color: var(--ios-blue); text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
            <ion-icon name="chevron-back-outline"></ion-icon> Back to List
        </a>
    </div>

    <div class="card" style="max-width: 800px;">
        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">

            <div style="flex: 1; min-width: 300px;">
                <div style="text-align: center; margin-bottom: 2rem; border-bottom: 1px solid #eee; padding-bottom: 2rem;">
                    <img src="<?php echo !empty($detailSubmission['image_url']) ? '/' . ltrim($detailSubmission['image_url'], '/') : '/assets/images/icon.png'; ?>"
                        style="width: 200px; height: 200px; border-radius: 15px; object-fit: cover; background: #f0f0f0; border: 2px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <h2 style="margin: 1rem 0 0.5rem; font-size: 2rem;">
                        <?php echo htmlspecialchars($detailSubmission['stage_name'] ?: 'No Stage Name'); ?>
                    </h2>
                    <p
                        style="color: var(--ios-secondary); font-weight: 500; text-transform: uppercase; letter-spacing: 1px;">
                        <?php echo htmlspecialchars($detailSubmission['full_name']); ?>
                    </p>
                </div>

                <div class="detail-row"><strong>Full Name:</strong>
                    <?php echo htmlspecialchars($detailSubmission['full_name']); ?>
                </div>
                <div class="detail-row"><strong>Stage Name:</strong>
                    <?php echo htmlspecialchars($detailSubmission['stage_name']); ?>
                </div>
                <div class="detail-row"><strong>Bio:</strong>
                    <p style="font-size: 0.9rem; color: #ccc;">
                        <?php echo nl2br(htmlspecialchars($detailSubmission['bio'])); ?>
                    </p>
                </div>

                <h3>Contact & Location</h3>
                <div class="detail-row"><strong>Email:</strong>
                    <?php echo htmlspecialchars($detailSubmission['email']); ?>
                </div>
                <div class="detail-row"><strong>Phone:</strong>
                    <?php echo htmlspecialchars($detailSubmission['phone']); ?>
                </div>
                <div class="detail-row"><strong>Country:</strong>
                    <?php echo htmlspecialchars($detailSubmission['country']); ?>
                </div>
                <div class="detail-row"><strong>Address:</strong>
                    <?php echo htmlspecialchars($detailSubmission['address']); ?>
                </div>

                <h3>Links</h3>
                <div class="detail-row"><strong>Instagram:</strong> <a
                        href="<?php echo htmlspecialchars($socials['instagram']); ?>" target="_blank"
                        style="color: var(--ios-blue);">
                        <?php echo htmlspecialchars($socials['instagram']); ?>
                    </a></div>
                <div class="detail-row"><strong>Spotify:</strong> <a
                        href="<?php echo htmlspecialchars($socials['spotify']); ?>" target="_blank"
                        style="color: var(--ios-blue);">
                        <?php echo htmlspecialchars($socials['spotify']); ?>
                    </a></div>
            </div>

            <div style="flex: 1; min-width: 300px; border-left: 1px solid #333; padding-left: 2rem;">
                <h2 style="margin-top: 0;">Legal & ID</h2>
                <div class="detail-row"><strong>Govt ID / Aadhar Number:</strong>
                    <?php echo htmlspecialchars($detailSubmission['govt_id_number']); ?>
                </div>
                <div class="detail-row"><strong>PAN Number:</strong>
                    <?php echo htmlspecialchars($detailSubmission['pan_number']); ?>
                </div>

                <h3 style="margin-top: 1rem;">Government ID Document</h3>
                <div
                    style="margin-bottom: 2rem; background: #f9f9f9; padding: 1.5rem; border-radius: 12px; border: 1px solid #eee;">
                    <?php
                    $ext = pathinfo($detailSubmission['govt_id_path'], PATHINFO_EXTENSION);
                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])):
                        ?>
                        <img src="<?php echo '/' . ltrim($detailSubmission['govt_id_path'], '/'); ?>"
                            style="max-width: 100%; border-radius: 8px; border: 1px solid #ccc; cursor: pointer; display: block;"
                            onclick="window.open(this.src)">
                        <p style="font-size: 0.75rem; color: #888; margin-top: 10px; text-align: center;">Click image to enlarge
                        </p>
                    <?php else: ?>
                        <a href="<?php echo '/' . ltrim($detailSubmission['govt_id_path'], '/'); ?>" target="_blank"
                            class="ios-btn-outline"
                            style="width: 100%; text-align: center; padding: 2rem; display: flex; flex-direction: column; gap: 10px; align-items: center; border-style: dashed;">
                            <ion-icon name="document-text-outline" style="font-size: 3rem;"></ion-icon>
                            <span>Open Document (<?php echo strtoupper($ext); ?>)</span>
                        </a>
                    <?php endif; ?>
                </div>

                <div style="border-top: 1px solid #333; padding-top: 2rem;">
                    <h3>Actions</h3>
                    <div style="display: flex; gap: 1rem;">
                        <form method="POST" onsubmit="return confirm('Reject this application?');" style="flex:1;">
                            <input type="hidden" name="id" value="<?php echo $detailSubmission['id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="email" value="<?php echo $detailSubmission['email']; ?>">
                            <input type="hidden" name="full_name" value="<?php echo $detailSubmission['full_name']; ?>">
                            <button type="submit" class="ios-btn"
                                style="width: 100%; background: var(--ios-red);">Reject</button>
                        </form>

                        <form method="POST"
                            onsubmit="return confirm('Approve this artist? Use Stage Name for public profile.');"
                            style="flex:1;">
                            <input type="hidden" name="id" value="<?php echo $detailSubmission['id']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="email" value="<?php echo $detailSubmission['email']; ?>">
                            <input type="hidden" name="full_name" value="<?php echo $detailSubmission['full_name']; ?>">
                            <button type="submit" class="ios-btn"
                                style="width: 100%; background: var(--ios-green);">Approve</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .detail-row {
            margin-bottom: 0.8rem;
            border-bottom: 1px solid #222;
            padding-bottom: 0.5rem;
        }
    </style>
<?php endif; ?>