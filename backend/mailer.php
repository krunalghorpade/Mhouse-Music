<?php
// backend/mailer.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Load PHPMailer locally
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

function sendEmail($to, $subject, $message)
{
    // 1. Load Local Config for SMTP Credentials
    $smtp_host = null;
    $smtp_user = null;
    $smtp_pass = null;
    $smtp_port = 587;
    $smtp_secure = 'tls';

    if (file_exists(__DIR__ . '/config.local.php')) {
        include __DIR__ . '/config.local.php';
    }

    $mail = new PHPMailer(true);
    $statusLog = "";

    try {
        // 2. Configure SMTP if credentials exist
        if (!empty($smtp_host) && !empty($smtp_user)) {
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_user;
            $mail->Password = $smtp_pass;
            $mail->SMTPSecure = $smtp_secure; // tls or ssl
            $mail->Port = $smtp_port;
        } else {
            // Fallback to PHP mail() if no SMTP config found
            // PHPMailer uses mail() by default if not set to SMTP, but let's be explicit?
            // Actually, if we don't call isSMTP(), it uses mail().
            // But we prefer SMTP on live servers.
        }

        // 3. Sender & Recipient
        // Use a generic sender that likely works, or the one from config
        $senderEmail = !empty($smtp_user) ? $smtp_user : 'noreply@mhousemusic.com';

        $mail->setFrom($senderEmail, 'M-House Music');
        $mail->addAddress($to);
        $mail->addReplyTo('contact@mhousemusic.com', 'M-House Music');

        // 4. Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        $statusLog = "Sent successfully via " . (!empty($smtp_host) ? "SMTP" : "mail()");
        $success = true;

    } catch (Exception $e) {
        $statusLog = "Failed. Error: {$mail->ErrorInfo}";
        $success = false;
    }

    // 5. Logging
    $logEntry = "--- EMAIL LOG ---\n";
    $logEntry .= "Time: " . date('Y-m-d H:i:s') . "\n";
    $logEntry .= "To: $to\n";
    $logEntry .= "Subject: $subject\n";
    $logEntry .= "Status: $statusLog\n";
    $logEntry .= "-----------------\n\n";

    file_put_contents(__DIR__ . '/email_log.txt', $logEntry, FILE_APPEND);

    return $success;
}
?>