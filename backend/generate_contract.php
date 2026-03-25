<?php
// backend/generate_contract.php
require_once 'db.php';

// Helper to sanitize filename
function sanitize_filename($filename)
{
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
}

// Helper to date string
function get_day_suffix($day)
{
    if (!in_array(($day % 100), array(11, 12, 13))) {
        switch ($day % 10) {
            case 1:
                return 'st';
            case 2:
                return 'nd';
            case 3:
                return 'rd';
        }
    }
    return 'th';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $track_name = $_POST['track_name'] ?? 'Untitled';
        $track_type = $_POST['track_type'] ?? 'Original';
        $track_version = $_POST['track_version'] ?? 'Original Mix';
        $artists_input = $_POST['artists'] ?? [];

        if (empty($artists_input)) {
            throw new Exception("No artists selected.");
        }

        $artist_ids = array_keys($artists_input);

        // Fetch Artist Details
        $placeholders = str_repeat('?,', count($artist_ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM artists WHERE id IN ($placeholders)");
        $stmt->execute($artist_ids);
        $artists_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $artists_data = [];
        foreach ($artists_db as $a) {
            $artists_data[$a['id']] = $a;
        }

        // Calculate Splits
        $num_artists = count($artists_input);

        // Label Share
        $label_share_mechanical = 50;
        $label_share_publishing = 70;

        // Artist Share Total
        $artist_total_mechanical = 50;
        $artist_total_publishing = 30;

        // Share Per Artist
        $artist_share_mechanical = round($artist_total_mechanical / $num_artists, 2);
        $artist_share_publishing = round($artist_total_publishing / $num_artists, 2);

        // Prepare Base Folders
        $base_contracts_dir = __DIR__ . '/../contracts/';
        // Create Release Specific Folder
        $release_folder_name = sanitize_filename($track_name);
        $target_dir = $base_contracts_dir . $release_folder_name . '/';

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $generated_files = [];

        foreach ($artists_input as $art_id => $details) {
            $artist_info = $artists_data[$art_id];
            $roles = isset($details['roles']) ? implode(' / ', $details['roles']) : 'Artist';

            // Prepare Variables
            $date_day = date('j');
            $date_suffix = get_day_suffix($date_day);
            $date_month = date('F');
            $date_year = date('Y');
            $full_date = $date_day . $date_suffix . " day of " . $date_month . " " . $date_year;

            $artist_legal_name = $artist_info['legal_name'] ?: $artist_info['name'];
            $artist_address = $artist_info['address'] ?: 'Address not provided';
            $artist_pan = $artist_info['pan_number'] ?: 'N/A';
            $artist_govt_id = $artist_info['govt_id_number'] ?: 'N/A';
            $artist_stage_name = $artist_info['name'];

            // Logo Path (absolute for local file access, relative/http for browser)
            // Ideally use base64 for portability in single html file
            $logo_path = __DIR__ . '/../assets/img/logo.png';
            $logo_base64 = '';
            if (file_exists($logo_path)) {
                $type = pathinfo($logo_path, PATHINFO_EXTENSION);
                $data = file_get_contents($logo_path);
                $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }

            // CONTENT GENERATION
            $html_content = "
            <html>
            <head>
                <style>
                    body { font-family: 'Times New Roman', serif; line-height: 1.4; color: #000; background: #fff; padding: 0; margin: 0; }
                    .contract-container { width: 100%; max-width: 800px; margin: 0 auto; padding: 40px; border: 1px solid #ddd; background: #fff; }
                    .logo-header { text-align: center; margin-bottom: 20px; }
                    .logo-header img { height: 80px; filter: invert(1); } /* Invert if logo is white on transparent */
                    h1 { text-align: center; text-transform: uppercase; font-size: 16pt; text-decoration: underline; margin-bottom: 30px; }
                    h3 { font-size: 11pt; text-transform: uppercase; margin-top: 20px; margin-bottom: 10px; text-decoration: underline; }
                    p { margin-bottom: 12px; text-align: justify; font-size: 10.5pt; }
                    .bold { font-weight: bold; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    td, th { border: 1px solid #000; padding: 10px; text-align: left; font-size: 10pt; }
                    
                    /* Print Optimizations */
                    @media print {
                        body { background: #fff; }
                        .contract-container { border: none; width: 100%; max-width: 100%; padding: 0; margin: 0; }
                        .logo-header img { filter: invert(1) !important; -webkit-filter: invert(1) !important; }
                    }
                </style>
            </head>
            <body>
                <div class='contract-container'>
                    <div class='logo-header'>
                         " . ($logo_base64 ? "<img src='$logo_base64' alt='M-House'>" : "<h2>M-HOUSE MUSIC</h2>") . "
                    </div>
                
                    <h1>ARTIST AGREEMENT</h1>

                    <p>This Artist Agreement (\"Agreement\") is made on this <strong>$full_date</strong></p>

                    <p><strong>BY AND BETWEEN</strong></p>

                    <p><strong>M-House Records</strong>, a child company of <strong>REKHA VIJAY CONSULTANCY PRIVATE LIMITED</strong>, having its registered office at Flat No. 1312, Crest Tower No. 3, Bolinj Nanbhat, Off Bolinj Sopara Road, Vasai-Virar City Palghar MH 401303 IN (GST: 27AAMCR5175J2ZR), (hereinafter referred to as the \"Label\").</p>

                    <p><strong>AND</strong></p>

                    <p><strong>$artist_legal_name</strong> an adult Indian citizen, residing at $artist_address.<br>
                    PAN: $artist_pan | ID No.: $artist_govt_id<br>
                    (Stage Name: <strong>$artist_stage_name</strong>)<br>
                    (hereinafter referred to as the \"Artist\" / \"you\" / \"your\").</p>

                    <p>The Label and the Artist are collectively referred to as the \"Parties\".</p>

                    <h3>1. SERVICES AND WORKS</h3>
                    <p>The Artist hereby acknowledges rendering their personal services as a <strong>$roles</strong> in connection with the recording and production by the Label of the master recording(s) detailed below (the \"Recording\"):</p>
                    <p><strong>Track Name:</strong> $track_name<br>
                    <strong>Type:</strong> $track_type<br>
                    <strong>Version:</strong> $track_version</p>
                    <p>It is hereby agreed that the results and proceeds arising out of the Artist's Services including the lyrics, composition and performance shall be collectively referred to as \"Works\".</p>

                    <h3>2. GRANT OF RIGHTS AND CO-OWNERSHIP</h3>
                    <p>You hereby expressly acknowledge and agree that your Services in respect of the Recording are collaborative in nature. You further acknowledge and agree that the Label (or its designees) is, and shall be deemed, the co-author of the Recordings and such other elements in and to the Song for all purposes and shall be the co-owner throughout the universe and in perpetuity of all the rights comprised in the copyright in the Recordings (expressly including the copyright in and to the “sound recordings”, and the Underlying Works), and any renewal or extension rights in connection therewith.</p>
                    <p>The Label shall have the unrestricted and unfettered right to edit, modify, alter, change and exploit the foregoing as the Label and/or its designees alone shall determine.</p>
                    <p>You hereby grant to the Label a perpetual, sub-licensable, irrevocable, exclusive (to the exclusion of you as well), unlimited, worldwide license in and to the Works.</p>

                    <h3>3. EXCLUSIVE EXPLOITATION RIGHTS</h3>
                    <p>The Label shall have the sole, exclusive (including to your exclusion), world-wide, perpetual rights of administration, exploitation, and collection with respect to one hundred percent (100%) of the rights, title and interest in and to the Underlying Works. This includes but is not limited to (i) copyright registration; (ii) publication, reproduction, synchronization; (iii) public performance and licensing; (iv) digital transmission and streaming.</p>

                    <h3>4. PAYMENT STRUCTURE</h3>
                    <p>In consideration of the rights granted herein, the Label shall pay the Artist shares of Net Revenue as follows:</p>
                    <p><strong>Mechanical / Master Royalties:</strong> $artist_share_mechanical% of the net revenue generated by the exploitation of the Recordings.</p>
                    <p><strong>Publishing Royalties:</strong> $artist_share_publishing% of the publishing income earned from the Works.</p>
                    <p>\"Net Revenue\" shall mean gross receipts actually received by the Label less applicable taxes, platform fees, direct marketing costs, and any \"Administration Fee\" retained by the Label for third-party collection.</p>

                    <h3>5. MORAL RIGHTS WAIVER & SECTION 19A</h3>
                    <p>You expressively acknowledge that the provisions of Section 19(4) and 19A of the Copyright Act shall not apply to this Agreement. You hereby irrevocably and unconditionally waive any and all “moral rights”, “performers’ rights”, and like rights that you have in the Works in any territory of the world.</p>

                    <h3>6. WARRANTIES AND INDEMNITY</h3>
                    <p>You represent and warrant that (i) you are competent to contract; (ii) you are the exclusive owner/controller of the rights granted; (iii) the Works are original and do not infringe any third-party rights. You agree to indemnify and hold the Label harmless against any damages, costs, or claims arising from any breach of these warranties.</p>
                    
                    <h3>7. TERMINATION</h3>
                    <p>The Label may terminate this Agreement in case of a material breach by the Artist not cured within 15 (fifteen) days of written notice. Rights assigned and revenue share accrued until termination shall survive termination.</p>

                    <h3>8. DISPUTE RESOLUTION & GOVERNING LAW</h3>
                    <p>This Agreement shall be exclusively governed and construed in accordance with the laws of the republic of India and the courts in Mumbai shall have exclusive jurisdiction in respect of any and all disputes arising hereunder.</p>

                    <h3>SIGNATURES</h3>
                    
                    <table style='border:none; margin-top:50px; width:100%'>
                    <tr>
                        <td style='border:none; width:50%; vertical-align:top;'>
                            <div style='border-top:1px solid #000; width:90%; padding-top:10px;'>
                            <strong>For REKHA VIJAY CONSULTANCY PVT LTD<br>(M-House Records)</strong><br><br><br>
                            Krunal Ghorpade<br>
                            (Authorized Signatory)
                            </div>
                        </td>
                        <td style='border:none; width:50%; vertical-align:top;'>
                            <div style='border-top:1px solid #000; width:90%; padding-top:10px;'>
                            <strong>Artist: $artist_legal_name</strong><br><br><br>
                            (Signature)
                            </div>
                        </td>
                    </tr>
                    </table>
                </div>
            </body>
            </html>
            ";

            // Filename
            $filename_base = "Contract_" . sanitize_filename($artist_stage_name) . "_" . sanitize_filename($track_name) . "_" . date('Ymd');

            // DOC file (HTML with headers for Word)
            $doc_content = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>" . $html_content . "</html>";
            file_put_contents($target_dir . $filename_base . ".doc", $doc_content);

            // PDF/Print View (HTML)
            file_put_contents($target_dir . $filename_base . ".html", $html_content);

            $generated_files[] = [
                'artist' => $artist_stage_name,
                'doc' => "contracts/" . $release_folder_name . "/" . $filename_base . ".doc",
                'html' => "contracts/" . $release_folder_name . "/" . $filename_base . ".html"
            ];
        }

        // Return JSON success
        echo json_encode(['status' => 'success', 'files' => $generated_files]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>