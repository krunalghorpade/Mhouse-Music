<?php
require_once 'backend/db.php';
require_once 'backend/mailer.php';

$pageTitle = "Artist Onboarding | M-House Music";
$step = isset($_POST['step']) ? $_POST['step'] : 'form';
$errors = [];
$data = [];

// Handle File Upload Helper
function handleUpload($fileInputName, $prefix = '')
{
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
        $target_dir = "assets/uploads/";
        if (!file_exists($target_dir))
            mkdir($target_dir, 0777, true);

        $raw_name = basename($_FILES[$fileInputName]["name"]);
        $clean_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $raw_name);
        $filename = $prefix . time() . "_" . $clean_name;
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $target_file)) {
            return "assets/uploads/" . $filename;
        }
    }
    return null;
}

// Process Form/Preview
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for Post Size Violation (File too large)
    if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $errors[] = "The selected files are too large. Please upload smaller files (Max " . (ini_get('upload_max_filesize') ?: '2M') . ").";
    }

    // Collect Data
    $data = [
        'full_name' => $_POST['full_name'] ?? '',
        'stage_name' => $_POST['stage_name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'country' => $_POST['country'] ?? '',
        'address' => $_POST['address'] ?? '',
        'pan_number' => $_POST['pan_number'] ?? '',
        'govt_id_number' => $_POST['govt_id_number'] ?? '',
        'bio' => $_POST['bio'] ?? '',
        'instagram' => $_POST['instagram'] ?? '',
        'spotify' => $_POST['spotify'] ?? '',
        'govt_id_path' => $_POST['govt_id_current'] ?? '',
        'image_url' => $_POST['image_url_current'] ?? ''
    ];

    // Handle Uploads (Only if new files are uploaded)
    if (!empty($_FILES['govt_id']['name'])) {
        $path = handleUpload('govt_id', 'proof_');
        if ($path)
            $data['govt_id_path'] = $path;
    }
    if (!empty($_FILES['profile_image']['name'])) {
        $path = handleUpload('profile_image', 'artist_');
        if ($path)
            $data['image_url'] = $path;
    }

    // Step Transition
    if ($step === 'preview') {
        // Validate
        if (empty($data['full_name']))
            $errors[] = "Full Name is required.";
        if (empty($data['email']))
            $errors[] = "Email is required.";
        if (empty($data['phone']))
            $errors[] = "Phone Number is required.";
        if (empty($data['country']))
            $errors[] = "Country is required.";
        if (empty($data['govt_id_path']))
            $errors[] = "Government ID file is required.";
        if (empty($data['govt_id_number']))
            $errors[] = "Government ID Number is required.";

        // Indian Citizen PAN Check
        if (strtolower(trim($data['country'])) === 'india' && empty($data['pan_number'])) {
            $errors[] = "PAN Number is required for Indian citizens.";
        }

        if (empty($errors)) {
            $step = 'confirm_view'; // Show Preview
        } else {
            $step = 'form'; // Back to form with errors
        }
    } elseif ($step === 'submit') {
        // Save to DB
        try {
            $socials = json_encode([
                'instagram' => $data['instagram'],
                'spotify' => $data['spotify']
            ]);

            $stmt = $pdo->prepare("INSERT INTO artist_submissions 
                (full_name, stage_name, email, phone, country, address, pan_number, govt_id_number, govt_id_path, social_links, bio, image_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $data['full_name'],
                $data['stage_name'],
                $data['email'],
                $data['phone'],
                $data['country'],
                $data['address'],
                $data['pan_number'],
                $data['govt_id_number'],
                $data['govt_id_path'],
                $socials,
                $data['bio'],
                $data['image_url']
            ]);

            // Send Confirmation Email
            $subject = "Application Received - M-House Music";
            $message = "
                <h1>Application Received</h1>
                <p>Hi " . htmlspecialchars($data['full_name']) . ",</p>
                <p>Thanks for applying to join the M-House Music collective. We have received your dossier.</p>
                <p>Our team will review your submission and get back to you shortly.</p>
                <br>
                <p>Best,<br>M-House Team</p>
            ";
            sendEmail($data['email'], $subject, $message);

            $step = 'success';
        } catch (Exception $e) {
            $errors[] = "Database Error: " . $e->getMessage();
            $step = 'form';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="admin/admin.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <style>
        :root {
            --accent-color: #f95738;
            --bg-secondary: #f4f4f4;
            --border-color: #000;
        }

        body {
            background: #ffffff;
            color: #000000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }

        .form-section {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 0;
            padding: 2.5rem;
            margin-bottom: 2rem;
        }

        .form-section h3 {
            margin-top: 0;
            margin-bottom: 2rem;
            font-size: 1.4rem;
            font-weight: 700;
            color: #000;
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-section h3 ion-icon {
            color: var(--accent-color);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .ios-input-group label {
            display: block;
            margin-bottom: 0.6rem;
            color: #666;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .ios-input-group input,
        .ios-input-group textarea,
        .ios-input-group select {
            width: 100%;
            padding: 14px 16px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 0;
            color: #000;
            margin-bottom: 1.5rem;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .ios-input-group input:focus,
        .ios-input-group textarea:focus {
            border-color: var(--accent-color);
            background: #fff;
            outline: none;
            box-shadow: none;
        }

        /* Profile Upload UI */
        .photo-upload-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
            text-align: center;
        }

        .photo-card {
            width: 220px;
            height: 220px;
            border-radius: 0;
            border: 1px solid #000;
            background: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .photo-card:hover {
            border-color: var(--accent-color);
            background: #fff;
        }

        .photo-card.has-image {
            border-style: solid;
            border-color: #000;
        }

        .photo-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .photo-card .upload-placeholder {
            text-align: center;
            color: #999;
        }

        .photo-card:hover .upload-placeholder {
            color: var(--accent-color);
        }

        .photo-card .upload-placeholder ion-icon {
            font-size: 3.5rem;
            margin-bottom: 0.5rem;
        }

        .photo-card .remove-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #000;
            color: white;
            border: none;
            border-radius: 0;
            width: 32px;
            height: 32px;
            display: none;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 10;
        }

        .photo-card.has-image .remove-btn {
            display: flex;
        }

        .photo-card.has-image img {
            display: block;
        }

        .photo-card.has-image .upload-placeholder {
            display: none;
        }

        .progress-box {
            width: 100%;
            max-width: 220px;
            margin-top: 15px;
            display: none;
        }

        .progress-track {
            width: 100%;
            height: 4px;
            background: #eee;
            border-radius: 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            background: var(--accent-color);
            transition: width 0.3s ease;
        }

        .preview-group {
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 0;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .preview-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .preview-row:last-child {
            border-bottom: none;
        }

        .preview-label {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .preview-value {
            font-weight: 700;
            text-align: right;
            color: #000;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-card {
            background: #ffffff;
            padding: 3.5rem 2rem;
            border: 2px solid #000;
            border-radius: 0;
            max-width: 450px;
            text-align: center;
        }

        .ios-btn {
            background: #000000;
            color: #fff;
            border: none;
            padding: 18px 32px;
            border-radius: 0;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .ios-btn:hover {
            background: var(--accent-color);
        }

        .ios-btn-outline {
            background: transparent;
            color: #000;
            border: 2px solid #000;
            padding: 16px 32px;
            border-radius: 0;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .ios-btn-outline:hover {
            background: #000;
            color: #fff;
        }

        /* Dossier Elements */
        .dossier-container {
            padding: 4rem;
            border: 4px solid #000;
        }

        .dossier-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 3rem;
            border-bottom: 4px solid #000;
            padding-bottom: 2rem;
        }

        .dossier-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
        }

        .dossier-photo {
            width: 150px;
            height: 150px;
            border: 2px solid #000;
            padding: 5px;
            background: #fff;
        }

        .dossier-actions {
            display: flex;
            gap: 1.5rem;
        }

        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 1rem;
            }

            .form-section {
                padding: 1.5rem;
            }

            /* Dossier Mobile */
            .dossier-container {
                padding: 1.5rem;
                border: 2px solid #000;
            }

            .dossier-header {
                flex-direction: column-reverse;
                gap: 2rem;
                align-items: center;
                text-align: center;
                padding-bottom: 1.5rem;
                margin-bottom: 2rem;
            }

            .dossier-header h2 {
                font-size: 2rem !important;
            }

            .dossier-grid {
                grid-template-columns: 1fr;
                gap: 2.5rem;
            }

            .dossier-photo {
                width: 120px;
                height: 120px;
            }

            .preview-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .preview-value {
                text-align: left;
                font-size: 0.9rem;
            }

            .dossier-actions {
                flex-direction: column-reverse;
            }

            .ios-btn,
            .ios-btn-outline {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div style="text-align: center; margin-bottom: 4rem; padding-top: 2rem;">
            <img src="assets/img/logo.png" alt="M-House" style="height: 60px; margin-bottom: 2rem; filter: invert(1);">
            <h1
                style="font-size: 4rem; margin-bottom: 0.5rem; font-weight: 900; letter-spacing: -2px; color: #000; text-transform: uppercase; line-height: 0.9;">
                Artist<br>Onboarding</h1>
            <p
                style="color: #666; font-size: 1.1rem; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; margin-top: 1.5rem;">
                Join the M-House Collective.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div
                style="background: #fff; border: 2px solid #ff3b30; padding: 1.5rem; border-radius: 0; margin-bottom: 2.5rem; color: #ff3b30;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <ion-icon name="alert-circle" style="font-size: 1.5rem;"></ion-icon>
                    <strong style="font-size: 1.1rem; text-transform: uppercase;">Incomplete Submission</strong>
                </div>
                <?php foreach ($errors as $err)
                    echo "<div style='font-size: 0.95rem; margin-left: 32px;'>• $err</div>"; ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 'form' || $step === 'edit'): ?>
            <form method="POST" enctype="multipart/form-data" class="ios-input-group" id="onboardingForm">
                <input type="hidden" name="step" value="preview">
                <input type="hidden" name="govt_id_current"
                    value="<?php echo htmlspecialchars($data['govt_id_path'] ?? ''); ?>">
                <input type="hidden" name="image_url_current"
                    value="<?php echo htmlspecialchars($data['image_url'] ?? ''); ?>">

                <div class="form-section">
                    <h3><ion-icon name="person-outline"></ion-icon> Personal Information</h3>
                    <div class="grid-2">
                        <div>
                            <label>Full Name (as on Govt ID) *</label>
                            <input type="text" name="full_name"
                                value="<?php echo htmlspecialchars($data['full_name'] ?? ''); ?>" required
                                placeholder="John Doe">
                        </div>
                        <div>
                            <label>Artist / Stage Name</label>
                            <input type="text" name="stage_name"
                                value="<?php echo htmlspecialchars($data['stage_name'] ?? ''); ?>" placeholder="Stage Name">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label>Email ID *</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
                                required placeholder="artist@mhouse.com">
                        </div>
                        <div>
                            <label>Phone (with Country Code) *</label>
                            <input type="text" name="phone" placeholder="+91 98765 43210"
                                value="<?php echo htmlspecialchars($data['phone'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label>Country *</label>
                            <input type="text" name="country"
                                value="<?php echo htmlspecialchars($data['country'] ?? ''); ?>" required
                                placeholder="India">
                        </div>
                        <div>
                            <label>Residential Address *</label>
                            <textarea name="address" rows="1" required
                                placeholder="Complete residential address"><?php echo htmlspecialchars($data['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3><ion-icon name="shield-checkmark-outline"></ion-icon> Identity & Verification</h3>
                    <div class="grid-2">
                        <div>
                            <label>Govt ID / Aadhar Number *</label>
                            <input type="text" name="govt_id_number"
                                value="<?php echo htmlspecialchars($data['govt_id_number'] ?? ''); ?>" required
                                placeholder="ID / Aadhar Card Number">
                        </div>
                        <div>
                            <label>PAN Number (India Only)</label>
                            <input type="text" name="pan_number"
                                value="<?php echo htmlspecialchars($data['pan_number'] ?? ''); ?>"
                                placeholder="PAN Card Details">
                        </div>
                    </div>

                    <label>Upload Govt ID / Aadhar Card * (Image/PDF)</label>
                    <input type="file" name="govt_id" id="govt_id_input" onchange="validateFileSize(this)" <?php echo empty($data['govt_id_path']) ? 'required' : ''; ?>
                        style="border-style: dashed; padding: 2rem; background: #f9f9f9; text-align: center; border: 1px solid #000;">
                    <?php if (!empty($data['govt_id_path'])): ?>
                        <p
                            style="font-size:0.85rem; margin-top:-0.5rem; color:var(--accent-color); font-weight: 700; text-transform: uppercase;">
                            ✓ Document Uploaded Successfully</p>
                    <?php endif; ?>
                </div>

                <div class="form-section">
                    <h3><ion-icon name="musical-notes-outline"></ion-icon> Artist Profile</h3>

                    <div class="photo-upload-container">
                        <label style="margin-bottom: 1.5rem;">Profile / Press Shot (1:1 Ratio) *</label>
                        <div class="photo-card <?php echo !empty($data['image_url']) ? 'has-image' : ''; ?>" id="photoCard">
                            <button type="button" class="remove-btn" id="removeBtn"><ion-icon
                                    name="close"></ion-icon></button>
                            <img src="<?php echo !empty($data['image_url']) ? $data['image_url'] : ''; ?>"
                                id="photoPreview">
                            <div class="upload-placeholder">
                                <ion-icon name="camera-outline"></ion-icon>
                                <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase;">Select Photo
                                </div>
                            </div>
                        </div>
                        <div class="progress-box" id="progressBox">
                            <div style="font-size: 0.75rem; color: #666; margin-bottom: 5px; text-transform: uppercase;"
                                id="progressText">Uploading...</div>
                            <div class="progress-track">
                                <div class="progress-fill" id="progressFill"></div>
                            </div>
                        </div>
                        <input type="file" name="profile_image" id="profile_input" accept="image/*" style="display: none;"
                            onchange="validateFileSize(this)">
                    </div>

                    <label>Artist Bio</label>
                    <textarea name="bio" rows="4"
                        placeholder="Briefly describe your musical style and journey..."><?php echo htmlspecialchars($data['bio'] ?? ''); ?></textarea>

                    <div class="grid-2">
                        <div>
                            <label>Instagram Link</label>
                            <input type="url" name="instagram"
                                value="<?php echo htmlspecialchars($data['instagram'] ?? ''); ?>"
                                placeholder="https://instagram.com/...">
                        </div>
                        <div>
                            <label>Spotify Link</label>
                            <input type="url" name="spotify" value="<?php echo htmlspecialchars($data['spotify'] ?? ''); ?>"
                                placeholder="https://open.spotify.com/artist/...">
                        </div>
                    </div>
                </div>

                <div style="padding: 1rem 0 4rem;">
                    <button type="submit" class="ios-btn" style="width: 100%;">Preview Submission</button>
                    <p
                        style="text-align: center; color: #999; font-size: 0.8rem; margin-top: 1.5rem; text-transform: uppercase; letter-spacing: 1px;">
                        This form is only for mhouse website data collection, dont worry your data wont be shared with
                        anyone, thats our guarantee</p>
                </div>
            </form>

            <script>
                function validateFileSize(input) {
                    const MAX_MB = 15;
                    if (input.files && input.files[0]) {
                        if ((input.files[0].size / 1024 / 1024) > MAX_MB) {
                            alert(`File too large. Maximum allowed size is ${MAX_MB}MB.`);
                            input.value = ''; // Clear the input
                        }
                    }
                }

                const photoCard = document.getElementById('photoCard');
                const profileInput = document.getElementById('profile_input');
                const photoPreview = document.getElementById('photoPreview');
                const removeBtn = document.getElementById('removeBtn');
                const progressBox = document.getElementById('progressBox');
                const progressFill = document.getElementById('progressFill');
                const progressText = document.getElementById('progressText');

                photoCard.addEventListener('click', (e) => {
                    if (e.target === removeBtn || removeBtn.contains(e.target)) return;
                    profileInput.click();
                });

                profileInput.addEventListener('change', function () {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();

                        // Simulate Progress for UX
                        progressBox.style.display = 'block';
                        let progress = 0;
                        const interval = setInterval(() => {
                            progress += Math.random() * 30;
                            if (progress >= 100) progress = 100;
                            progressFill.style.width = progress + '%';
                            progressText.innerText = 'Uploading... ' + Math.round(progress) + '%';

                            if (progress >= 100) {
                                clearInterval(interval);
                                setTimeout(() => {
                                    progressBox.style.display = 'none';
                                    progressFill.style.width = '0%';
                                }, 800);
                            }
                        }, 100);

                        reader.onload = function (e) {
                            photoPreview.src = e.target.result;
                            photoCard.classList.add('has-image');
                        }
                        reader.readAsDataURL(file);
                    }
                });

                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    profileInput.value = '';
                    photoPreview.src = '';
                    photoCard.classList.remove('has-image');
                });
            </script>

        <?php elseif ($step === 'confirm_view'): ?>
            <div class="form-section dossier-container">
                <div class="dossier-header">
                    <div>
                        <h2
                            style="margin: 0; font-weight: 900; text-transform: uppercase; letter-spacing: -2px; font-size: 3rem; line-height: 0.85;">
                            APPLICATION<br>DOSSIER</h2>
                        <p
                            style="margin: 1rem 0 0; color: var(--accent-color); font-weight: 800; text-transform: uppercase; letter-spacing: 3px; font-size: 0.9rem;">
                            M-HOUSE MUSIC COLLECTIVE / OFFICIAL</p>
                    </div>
                    <?php if (!empty($data['image_url'])): ?>
                        <div class="dossier-photo">
                            <img src="<?php echo $data['image_url']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php else: ?>
                        <div class="dossier-photo"
                            style="display: flex; align-items: center; justify-content: center; background: #f9f9f9; color: #ccc;">
                            <ion-icon name="person-outline" style="font-size: 3rem;"></ion-icon>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="dossier-grid">
                    <div>
                        <h4
                            style="text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2rem; border-bottom: 2px solid #000; padding-bottom: 0.5rem; font-size: 1rem; font-weight: 900;">
                            SECTION 01: IDENTITY</h4>
                        <div class="preview-row" style="border-bottom: 1px solid #eee;"><span class="preview-label">LEGAL
                                NAME</span> <span
                                class="preview-value"><?php echo htmlspecialchars($data['full_name']); ?></span></div>
                        <div class="preview-row" style="border-bottom: 1px solid #eee;"><span class="preview-label">STAGE
                                ALIAS</span> <span
                                class="preview-value"><?php echo htmlspecialchars($data['stage_name'] ?: 'N/A'); ?></span>
                        </div>
                        <div class="preview-row" style="border-bottom: 1px solid #eee;"><span class="preview-label">GOVT ID
                                No.</span> <span
                                class="preview-value"><?php echo htmlspecialchars($data['govt_id_number']); ?></span></div>
                        <div class="preview-row" style="border-bottom: 1px solid #eee;"><span class="preview-label">TAX /
                                PAN ID</span> <span
                                class="preview-value"><?php echo htmlspecialchars($data['pan_number'] ?: 'NOT DISCLOSED'); ?></span>
                        </div>
                    </div>
                    <div>
                        <h4
                            style="text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2rem; border-bottom: 2px solid #000; padding-bottom: 0.5rem; font-size: 1rem; font-weight: 900;">
                            SECTION 02: CONTACT</h4>
                        <div class="preview-row" style="border-bottom: 1px solid #eee;"><span class="preview-label">E-MAIL
                                ADDRESS</span> <span
                                class="preview-value"><?php echo htmlspecialchars($data['email']); ?></span></div>
                        <div class="preview-row" style="border-bottom: 1px solid #eee;"><span class="preview-label">MOBILE
                                PHONE</span> <span
                                class="preview-value"><?php echo htmlspecialchars($data['phone']); ?></span></div>
                        <div class="preview-row" style="border-bottom: 1px solid #eee;"><span
                                class="preview-label">CITIZENSHIP</span> <span
                                class="preview-value"><?php echo htmlspecialchars($data['country']); ?></span></div>
                        <div class="preview-row" style="border-bottom: 1px solid #eee;"><span class="preview-label">BASE
                                ADDRESS</span> <span class="preview-value"
                                style="font-size: 0.8rem; font-weight: 500;"><?php echo htmlspecialchars($data['address']); ?></span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 4rem;">
                    <h4
                        style="text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2rem; border-bottom: 2px solid #000; padding-bottom: 0.5rem; font-size: 1rem; font-weight: 900;">
                        SECTION 03: ARTISTIC PROFILE</h4>
                    <div class="dossier-grid">
                        <div>
                            <div class="preview-row" style="border-bottom: 1px solid #eee;"><span
                                    class="preview-label">INSTAGRAM</span> <span class="preview-value"
                                    style="font-size: 0.8rem;"><?php echo htmlspecialchars($data['instagram'] ?: 'LINK NOT PROVIDED'); ?></span>
                            </div>
                            <div class="preview-row" style="border-bottom: 1px solid #eee;"><span
                                    class="preview-label">SPOTIFY</span> <span class="preview-value"
                                    style="font-size: 0.8rem;"><?php echo htmlspecialchars($data['spotify'] ?: 'LINK NOT PROVIDED'); ?></span>
                            </div>
                        </div>
                        <div style="background: #fdfdfd; padding: 1.5rem; border: 1px solid #eee;">
                            <p style="margin: 0; font-size: 0.9rem; line-height: 1.6; color: #444;"><strong
                                    style="color: #000; text-transform: uppercase; font-size: 0.75rem; display: block; margin-bottom: 10px; border-bottom: 1px solid #000; padding-bottom: 3px; width: fit-content;">STATEMENT
                                    /
                                    BIOGRAPHY</strong><?php echo nl2br(htmlspecialchars($data['bio'] ?: 'No biography statement provided for this application.')); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 5rem; border-top: 2px solid #000; padding-top: 3rem; position: relative;">
                    <div
                        style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: #fff; padding: 0 20px; text-transform: uppercase; letter-spacing: 5px; font-weight: 900; font-size: 0.8rem;">
                        DECLARATION</div>
                    <p
                        style="font-size: 0.75rem; color: #000; text-transform: uppercase; letter-spacing: 1px; text-align: center; margin-bottom: 3rem; font-weight: 600;">
                        I, the undersigned, confirm that the information provided in this dossier is complete, accurate, and
                        represents my professional profile truthfully.</p>

                    <form method="POST" class="dossier-actions">
                        <?php foreach ($data as $k => $v): ?>
                            <?php if (!is_array($v)): ?>
                                <input type="hidden" name="<?php echo $k; ?>" value="<?php echo htmlspecialchars($v); ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <!-- Explicitly pass current file paths -->
                        <input type="hidden" name="govt_id_current"
                            value="<?php echo htmlspecialchars($data['govt_id_path']); ?>">
                        <input type="hidden" name="image_url_current"
                            value="<?php echo htmlspecialchars($data['image_url']); ?>">

                        <button type="submit" name="step" value="form" class="ios-btn-outline"
                            style="flex: 1; height: 65px; border-width: 2px;">REVISE APPLICATION</button>
                        <button type="submit" name="step" value="submit" class="ios-btn"
                            style="flex: 2; height: 65px;">SUBMIT TO COLLECTIVE</button>
                    </form>
                </div>
            </div>

        <?php elseif ($step === 'success'): ?>
            <div class="modal-overlay">
                <div class="modal-card">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem;">⚡</div>
                    <h2
                        style="margin-bottom: 1rem; font-size: 2.5rem; font-weight: 900; letter-spacing: -1px; text-transform: uppercase;">
                        Application Sent</h2>
                    <p style="color: #666; margin-bottom: 2.5rem; line-height: 1.6; font-size: 1.1rem; font-weight: 500;">
                        Your artist profile is now under review. We'll send an update to
                        <strong><?php echo htmlspecialchars($data['email']); ?></strong> once approved.
                    </p>
                    <a href="index" class="ios-btn" style="text-decoration: none; display: block; width: 100%;">Close</a>
                </div>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>