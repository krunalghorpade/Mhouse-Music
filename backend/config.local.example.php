<?php
// backend/config.local.php
// Copy this file to 'config.local.php' and update with your local/environment specific credentials.
// This file should remain ignored by git.

$host = 'localhost';
$dbname = 'your_db_name';
$username = 'your_db_user';
$password = 'your_db_pass';

// SMTP Configuration (Optional - for PHPMailer)
$smtp_host = 'smtp.example.com';
$smtp_port = 587; // or 465
$smtp_user = 'your_email@example.com';
$smtp_pass = 'your_email_password';
$smtp_secure = 'tls'; // tls or ssl
?>