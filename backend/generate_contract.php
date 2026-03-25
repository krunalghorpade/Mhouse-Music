<?php
if (function_exists('opcache_reset')) { opcache_reset(); }
// backend/generate_contract.php
require_once 'db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Helper to sanitize filename
function sanitize_filename($filename)
{
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
}

// Helper for date string
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
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, true);

        if (!$input) {
            // Fallback for form data if needed, but UI uses fetch JSON
            throw new Exception("Invalid JSON payload.");
        }

        $release_name = $input['release_name'] ?? 'Untitled Release';
        $release_type = $input['release_type'] ?? 'Single';
        $tracks = $input['tracks'] ?? [];

        if (empty($tracks)) {
            throw new Exception("No tracks provided.");
        }

        // Collect all artist IDs to fetch them efficiently
        $artist_ids = [];
        foreach ($tracks as $track) {
            foreach ($track['artists'] as $artist) {
                if (!empty($artist['id']) && !in_array($artist['id'], $artist_ids)) {
                    $artist_ids[] = $artist['id'];
                }
            }
        }

        if (empty($artist_ids)) {
            throw new Exception("No artists provided in the tracks.");
        }

        // Fetch Artist Details
        $placeholders = str_repeat('?,', count($artist_ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM artists WHERE id IN ($placeholders)");
        $stmt->execute($artist_ids);
        $artists_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $artists_data = [];
        foreach ($artists_db as $a) {
            $artists_data[$a['id']] = $a;
        }

        // Prepare Directories
        $base_contracts_dir = __DIR__ . '/../contracts/';
        $release_folder_name = sanitize_filename($release_name);
        $target_dir = $base_contracts_dir . $release_folder_name . '/';

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $generated_files = [];

        // Logo configuration for Dompdf
        $logo_path = __DIR__ . '/../assets/img/logo.png';
        $logo_base64 = '';
        if (file_exists($logo_path)) {
            $type = pathinfo($logo_path, PATHINFO_EXTENSION);
            $data = file_get_contents($logo_path);
            $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $date_day = date('j');
        $date_suffix = get_day_suffix($date_day);
        $date_month = date('F');
        $date_year = date('Y');
        $full_date = $date_day . $date_suffix . " day of " . $date_month . " " . $date_year;

        // Init Dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        foreach ($tracks as $tIndex => $track) {
            $track_name = $track['name'] ?: 'Untitled Track';
            $num_artists = count($track['artists']);
            
            if ($num_artists === 0) continue;

            // Royalty split logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic logic 
            // Royalty split logic: total artist pool is 50% for either mechanical and publishing
            // Then divided by amount of artists for the track
            $artist_share_mechanical = round(50 / $num_artists, 2);
            $artist_share_publishing = round(50 / $num_artists, 2);

            foreach ($track['artists'] as $aIndex => $artistInput) {
                if (empty($artistInput['id'])) continue;
                $art_id = $artistInput['id'];
                
                // If the artist somehow doesn't exist in DB, skip to prevent errors
                if (!isset($artists_data[$art_id])) continue;
                $artist_info = $artists_data[$art_id];

                $roles = !empty($artistInput['roles']) ? implode(' / ', $artistInput['roles']) : 'Artist';
                $isMainStr = !empty($artistInput['isMain']) ? " (Main Artist)" : "";

                $artist_legal_name = !empty($artist_info['legal_name']) ? $artist_info['legal_name'] : $artist_info['name'];
                $artist_address = !empty($artist_info['address']) ? $artist_info['address'] : 'Address not provided';
                $artist_pan = !empty($artist_info['pan_number']) ? $artist_info['pan_number'] : 'N/A';
                $artist_govt_id = !empty($artist_info['govt_id_number']) ? $artist_info['govt_id_number'] : 'N/A';
                $artist_stage_name = $artist_info['name'];

                $html_content = "
                <html>
                <head>
                    <style>
                        body { font-family: 'Helvetica', 'Arial', sans-serif; line-height: 1.5; color: #000; background: #fff; padding: 20px; font-size: 10pt; }
                        h1 { text-align: center; text-transform: uppercase; font-size: 16pt; text-decoration: underline; margin-bottom: 25px; }
                        h2 { text-align: center; font-size: 14pt; margin-bottom: 5px;}
                        h3 { font-size: 11pt; text-transform: uppercase; margin-top: 20px; margin-bottom: 10px; text-decoration: underline; }
                        p { margin-bottom: 12px; text-align: justify; }
                        .bold { font-weight: bold; }
                        .logo-container { text-align: center; margin-bottom: 10px; }
                        .logo-container img { height: 60px; filter: grayscale(100%) invert(100%); }
                        .signatures { margin-top: 50px; width: 100%; border-collapse: collapse; }
                        .signatures td { padding-top: 50px; border-top: 1px solid #000; width: 45%; vertical-align: top; }
                    </style>
                </head>
                <body>
                    <div class='logo-container'>
                        " . ($logo_base64 ? "<img src='$logo_base64' alt='M-House'>" : "<h2>M-HOUSE MUSIC</h2>") . "
                    </div>
                
                    <h1>ARTIST AGREEMENT</h1>

                    <p>This Artist Agreement (\"Agreement\") is made on this <strong>$full_date</strong></p>

                    <p><strong>BY AND BETWEEN</strong></p>

                    <p><strong>M-House Records</strong>, a child company of <strong>REKHA VIJAY CONSULTANCY PVT LTD</strong>, having its registered office at Flat No. 1312, Crest Tower No. 3, Bolinj Nanbhat, Off Bolinj Sopara Road, Vasai-Virar City Palghar MH 401303 IN (GST: 27AAMCR5175J2ZR), represented by Director: Krunal Vijay Ghorpade (hereinafter referred to as the \"Label\").</p>

                    <p><strong>AND</strong></p>

                    <p><strong>$artist_legal_name</strong> an adult Indian citizen, residing at $artist_address.<br>
                    Aadhar/ID No.: $artist_govt_id | PAN: $artist_pan<br>
                    (Stage Name: <strong>$artist_stage_name$isMainStr</strong>)<br>
                    (hereinafter referred to as the \"Artist\").</p>

                    <p>The Label and the Artist are collectively referred to as the \"Parties\".</p>

                    <p><strong>Release:</strong> $release_name ($release_type)</p>

                    <h3>1. DEFINITIONS</h3>
                    <p>\"Works\" shall mean the lyrics, composition, vocals, recordings, and performances created by the Artist under this Agreement for the track \"<strong>$track_name</strong>\" ({$track['type']}, {$track['version']}).</p>
                    <p>\"Recordings\" shall mean the sound recording(s) and/or music video(s) produced under this Agreement.</p>
                    <p>\"Territory\" shall mean the world.</p>
                    <p>\"Term\" shall mean in perpetuity from the Effective Date unless otherwise terminated by mutual consent.</p>

                    <h3>2. GRANT OF RIGHTS</h3>
                    <p>The Artist grants M-House Records the exclusive right to produce, distribute, promote, and exploit the Recordings in any manner (including but not limited to physical sales, digital streaming, live performances, broadcasting, and synchronization).</p>

                    <h3>3. PAYMENT STRUCTURE</h3>
                    <p>The Label shall pay the Artist their share of the net revenue generated by the exploitation of the Recordings. \"Net Revenue\" shall mean gross receipts actually received by the Label after a 20% deduction by the distributor (Nirvana Digital), applicable taxes, platform fees, and direct marketing costs.</p>
                    <p>The royalty pool for the track is 50% of mechanical rights and 70% of publishing rights. From this pool, the Label retains 50%, and the remaining 50% is shared equally among all artists on the track. Therefore, for this track with $num_artists artist(s), your specific share is <strong>$artist_share_mechanical%</strong> of the mechanical rights and <strong>$artist_share_publishing%</strong> of the publishing rights.</p>
                    <p>Payment shall be made to the Artist within 45 days of receipt of revenue by the Label, along with a statement of accounts.</p>

                    <h3>4. CREDITS</h3>
                    <p>The Artist shall be credited as: <strong>$roles</strong> (as applicable).</p>

                    <h3>5. WARRANTIES AND INDEMNITY</h3>
                    <p>The Artist represents that the Works are original and do not infringe the rights of any third party. The Artist shall indemnify and hold harmless the Label against any such claims.</p>

                    <h3>6. TERMINATION</h3>
                    <p>The Label may terminate this Agreement in case of a material breach by the Artist not cured within 15 (fifteen) days of written notice. Rights assigned and revenue share accrued until termination shall survive termination.</p>

                    <h3>7. CONFIDENTIALITY</h3>
                    <p>Both parties agree to keep the terms of this Agreement and any business information confidential.</p>

                    <h3>8. DISPUTE RESOLUTION & GOVERNING LAW</h3>
                    <p>Any disputes shall be resolved by arbitration in Mumbai under the Arbitration and Conciliation Act, 1996. The courts in Mumbai shall have exclusive jurisdiction.</p>

                    <h3>9. MISCELLANEOUS</h3>
                    <p>Stamp duty and applicable charges shall be borne by the Label.</p>
                    <p>This Agreement may be executed electronically in counterparts.</p>
                    <p>Standard clauses of Force Majeure, Severability, Entire Agreement, and Independent Contractor shall apply.</p>

                    <table class='signatures'>
                        <tr>
                            <td>
                                <strong>For M-House Records (Label)</strong><br><br><br>
                                Signature: _________________________<br>
                                Name: Krunal Vijay Ghorpade<br>
                                Designation: Director
                            </td>
                            <td style='border-top:none; width: 10%;'></td>
                            <td>
                                <strong>Artist</strong><br><br><br>
                                Signature: _________________________<br>
                                Name: $artist_legal_name<br>
                                (Stage Name: $artist_stage_name)
                            </td>
                        </tr>
                    </table>
                </body>
                </html>
                ";

                // Filename
                $filename_base = "Contract_" . sanitize_filename($artist_stage_name) . "_" . sanitize_filename($track_name) . "_" . date('Ymd');
                $pdf_path = $target_dir . $filename_base . ".pdf";

                // Generate PDF using Dompdf
                $dompdf = new Dompdf($options);
                $dompdf->loadHtml($html_content);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                
                // Output and save
                $output = $dompdf->output();
                file_put_contents($pdf_path, $output);

                $generated_files[] = [
                    'artist' => $artist_stage_name,
                    'path' => "/contracts/" . $release_folder_name . "/" . $filename_base . ".pdf",
                    'name' => $filename_base . ".pdf"
                ];
            }
        }

        echo json_encode(['status' => 'success', 'files' => $generated_files]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>