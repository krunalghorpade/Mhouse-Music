<?php
// backend/mailer.php

function sendEmail($to, $subject, $message)
{
    // For local development, we'll confirm successful "sending" and log it.
    // In production, this would use more robust headers or a library like PHPMailer/SwiftMailer.

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: M-House Music <noreply@mhousemusic.com>' . "\r\n";

    // Attempt to send
    $mailSent = mail($to, $subject, $message, $headers);

    // Log for debugging (useful for localhost where mail() might not be configured)
    $logEntry = "--- EMAIL LOG ---\n";
    $logEntry .= "Time: " . date('Y-m-d H:i:s') . "\n";
    $logEntry .= "To: $to\n";
    $logEntry .= "Subject: $subject\n";
    $logEntry .= "Status: " . ($mailSent ? "Sent/Queued" : "Failed (Check server config)") . "\n";
    $logEntry .= "Body Preview:\n" . substr(strip_tags($message), 0, 200) . "...\n";
    $logEntry .= "-----------------\n\n";

    // Append to a log file in backend/
    file_put_contents(__DIR__ . '/email_log.txt', $logEntry, FILE_APPEND);

    return $mailSent;
}
?>