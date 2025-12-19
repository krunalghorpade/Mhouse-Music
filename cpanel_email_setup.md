# How to Configure cPanel Email for M-House Music

Since you are using cPanel, you have two main options for sending emails:
1.  **PHP `mail()` Function** (Easiest, works out-of-the-box on most cPanel hosts).
2.  **SMTP via PHPMailer** (Most reliable, prevents emails going to Spam).

## Step 1: Create an Email Account in cPanel
1.  Log in to your **cPanel**.
2.  Go to **Email Accounts**.
3.  Click **+ Create**.
4.  Username: `noreply` (or `admin`, `newsletter`).
5.  Domain: `mhousemusic.com` (Select your specific domain).
6.  Password: Set a strong password.
7.  Click **Create**.

## Step 2: Get Configuration Details
Once created, find the email account in the list and click **Connect Devices**. Note down the following (usually in the "Mail Client Manual Settings" box):
*   **Outgoing Server**: `mail.yourdomain.com` (e.g., `mail.mhousemusic.com`)
*   **SMTP Port**: `465` (SSL) or `587` (TLS)
*   **Username**: Full email address (e.g., `noreply@mhousemusic.com`)
*   **Password**: The password you just set.

---

## Step 3: Configure Your Application

### Option A: Using the default PHP `mail()` (No external libraries)
Most cPanel servers allow the standard PHP `mail()` function to work **IF** the "From" address matches the domain the script is hosted on.

**Action Required:**
1.  Open `admin/subscribers_view.php`.
2.  Ensure the `$from_email` variable matches exactly the email you created in Step 1.

### Option B: Using SMTP (Recommended for Deliverability)
If your emails go to spam, you need to use SMTP. This requires a library like **PHPMailer**.

1.  **Download PHPMailer**:
    *   If you have Composer: `composer require phpmailer/phpmailer`
    *   Manual: Download the zip from [GitHub](https://github.com/PHPMailer/PHPMailer), extract it, and copy the `src` folder to `backend/PHPMailer`.

2.  **Update Code**:
    You would replace the `mail()` function in `admin/subscribers_view.php` with:
    ```php
    use PHPMailer\PHPMailer\PHPMailer;
    require '../backend/PHPMailer/src/PHPMailer.php';
    require '../backend/PHPMailer/src/SMTP.php';
    
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'mail.mhousemusic.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'noreply@mhousemusic.com';
    $mail->Password = 'your_password';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    
    $mail->setFrom('noreply@mhousemusic.com', 'M-House Music');
    $mail->addAddress($subscriber_email);
    // ... setup content and send
    ```
