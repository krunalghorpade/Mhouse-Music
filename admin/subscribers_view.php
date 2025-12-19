<?php
// admin/subscribers_view.php

// Handle Bulk Email Sending
$msg = '';
$error = '';

if (isset($_POST['send_email'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($subject) || empty($message)) {
        $error = "Subject and Message are required.";
    } else {
        // Fetch all subscriber emails
        $stmt = $pdo->query("SELECT email FROM subscribers");
        $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($emails) > 0) {
            $to = implode(',', $emails); // This exposes all emails to each other in 'To'. Bcc is better.

            // In a real world scenario, you would loop and send individually or use a service like SendGrid/AWS SES AND use BCC.
            // For this PHP 'mail' demo, we'll iterate.

            // IMPORTANT: Change this to your cPanel email address
            $from_email = 'noreply@mhousemusic.com';

            $headers = "From: " . $from_email . "\r\n";
            $headers .= "Reply-To: " . $from_email . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $success_count = 0;

            // Send Loop
            foreach ($emails as $email) {
                // Determine if we are on localhost or live server
                if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1') {
                    // Simulation for localhost
                    $success_count++;
                } else {
                    // Actual send for live server
                    if (mail($email, $subject, $message, $headers)) {
                        $success_count++;
                    }
                }
            }

            $msg = "Campaign processing complete. Sent to $success_count subscribers.";
        } else {
            $error = "No subscribers found to send to.";
        }
    }
}

// Fetch Subscribers
$subscribers = $pdo->query("SELECT * FROM subscribers ORDER BY subscribed_at DESC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Subscribers</h1>
    <div style="color: var(--ios-secondary);">
        Total: <?php echo count($subscribers); ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">

    <!-- List -->
    <div>
        <h2 style="margin-top: 0;">Subscriber List</h2>
        <div class="ios-list">
            <?php if (count($subscribers) === 0): ?>
                <div class="ios-list-item" style="color: var(--ios-secondary);">No subscribers yet.</div>
            <?php else: ?>
                <?php foreach ($subscribers as $sub): ?>
                    <div class="ios-list-item">
                        <div>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($sub['email']); ?></div>
                        </div>
                        <div style="font-size: 0.9rem; color: var(--ios-secondary);">
                            <?php echo date('M d, Y', strtotime($sub['subscribed_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bulk Email Form -->
    <div>
        <h2 style="margin-top: 0;">Send Bulk Email</h2>
        <div class="card">
            <?php if ($msg): ?>
                <div
                    style="background: rgba(52, 199, 89, 0.1); color: var(--ios-green); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div
                    style="background: rgba(255, 59, 48, 0.1); color: var(--ios-red); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label>Subject</label>
                <input type="text" name="subject" placeholder="Newsletter Subject" required>

                <label>Message</label>
                <textarea name="message" rows="8" placeholder="Write your update here..." required></textarea>

                <button type="submit" name="send_email" class="ios-btn" style="width: 100%;">
                    <ion-icon name="paper-plane-outline" style="vertical-align: middle; margin-right: 5px;"></ion-icon>
                    Send to All
                </button>
                <p style="font-size: 0.8rem; color: var(--ios-secondary); margin-top: 1rem; text-align: center;">
                    This will effectively send an email to all <?php echo count($subscribers); ?> active subscribers.
                </p>
            </form>
        </div>
    </div>

</div>

<!-- Responsive Fix for Grid -->
<style>
    @media (max-width: 900px) {
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>