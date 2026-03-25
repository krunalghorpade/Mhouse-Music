<?php
// admin/contracts_view.php

// Fetch Artists for the select dropdown
$artists = $pdo->query("SELECT id, name FROM artists ORDER BY name ASC")->fetchAll();

// List existing contracts (Group by Release/Folder)
$releases = [];
$contract_dir = __DIR__ . '/../contracts/';

if (file_exists($contract_dir)) {
    $items = scandir($contract_dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === '.DS_Store') continue;
        
        $path = $contract_dir . $item;
        
        if (is_dir($path)) {
            // It's a release folder
            $folder_files = [];
            $sub_items = scandir($path);
            foreach ($sub_items as $sub) {
                if ($sub === '.' || $sub === '..' || $sub === '.DS_Store') continue;
                $folder_files[] = [
                    'name' => $sub,
                    'path' => '/contracts/' . $item . '/' . $sub
                ];
            }
            if (!empty($folder_files)) {
                $releases[] = [
                    'name' => str_replace('_', ' ', $item),
                    'timestamp' => filemtime($path),
                    'files' => $folder_files
                ];
            }
        } else {
            // Root file (Legacy or uncategorized)
             $releases[] = [
                'name' => 'Uncategorized',
                'timestamp' => filemtime($path),
                'files' => [[
                    'name' => $item,
                    'path' => '/contracts/' . $item
                ]]
            ];
        }
    }
}

// Sort releases by date
usort($releases, function ($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Contracts</h1>
    <button onclick="document.getElementById('newContractModal').style.display='flex'" class="ios-btn">
        <ion-icon name="add-outline"></ion-icon> New Contract
    </button>
</div>

<!-- List of Contracts -->
<?php if (empty($releases)): ?>
    <div style="text-align: center; color: var(--ios-secondary); margin-top: 4rem;">
        <ion-icon name="document-text-outline" style="font-size: 3rem;"></ion-icon>
        <p>No contracts generated yet.</p>
    </div>
<?php else: ?>
    <div class="ios-list">
        <?php foreach ($releases as $rel): ?>
            
            <div class="folder-group" style="border: 1px solid #eee; margin-bottom: 1rem; border-radius: 10px; overflow: hidden;">
                <!-- Folder Header -->
                <div onclick="toggleFolder(this)" style="background: #fdfdfd; padding: 1rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid transparent;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <ion-icon name="folder-open-outline" style="font-size: 1.5rem; color: #ff9800;"></ion-icon>
                        <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($rel['name']); ?></strong>
                        <span style="background: #eee; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;"><?php echo count($rel['files']); ?> files</span>
                    </div>
                    <ion-icon name="chevron-down-outline" class="folder-chevron"></ion-icon>
                </div>

                <!-- Files -->
                <div class="folder-content" style="display: none; background: #fff; padding: 0.5rem 1rem;">
                    <?php foreach ($rel['files'] as $file): ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f5f5f5;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <?php if (strpos($file['name'], '.pdf') !== false): ?>
                                    <ion-icon name="document-text" style="color: #f44336; font-size: 1.2rem;"></ion-icon>
                                <?php else: ?>
                                    <ion-icon name="document-outline" style="color: #2196f3; font-size: 1.2rem;"></ion-icon>
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars($file['name']); ?></span>
                            </div>
                            <a href="<?php echo htmlspecialchars($file['path']); ?>" target="_blank" style="text-decoration: none; color: var(--ios-blue); font-size: 0.9rem; font-weight: 600;">Download</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- New Contract Modal -->
<div id="newContractModal" class="modal-overlay" style="display: none;">
    <div class="modal-card" style="width: 100%; max-width: 800px; text-align: left; max-height: 90vh; overflow-y: auto;">
        <h2 style="margin-top: 0; margin-bottom: 1.5rem;">Generate Contracts (Release)</h2>
        <form id="contractForm">
            <!-- Release Details -->
            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
                <h3 style="margin-top:0; font-size:1.1rem; border-bottom:1px solid #ddd; padding-bottom:10px;">Release Details</h3>
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                    <div>
                        <label style="font-size: 0.85rem; color: #666; font-weight: 600;">Release Name</label>
                        <input type="text" id="release_name" placeholder="E.g. Summer Dreams EP" required 
                               style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px;">
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; color: #666; font-weight: 600;">Release Type</label>
                        <select id="release_type" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background: white; margin-top: 5px;">
                            <option value="Single">Single</option>
                            <option value="Album">Album</option>
                            <option value="EP">EP</option>
                            <option value="Compilation">Compilation</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tracks Container -->
            <div id="tracksContainer"></div>

            <button type="button" onclick="addTrack()" class="ios-btn-text" style="margin-bottom: 20px; font-weight:bold;">
                <ion-icon name="add-circle-outline"></ion-icon> Add Track
            </button>

            <!-- Actions -->
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="document.getElementById('newContractModal').style.display='none'"
                    class="ios-btn" style="background: #ccc; flex: 1;">Cancel</button>
                <button type="submit" class="ios-btn" style="flex: 1;">Generate Contracts</button>
            </div>
        </form>
    </div>
</div>

<script>
    // State management
    const allArtists = <?php echo json_encode($artists); ?>;
    let tracksState = [];
    
    function generateArtistOptions(selectedId) {
        let opts = `<option value="">-- Add Artist --</option>`;
        allArtists.forEach(a => {
            const sel = (a.id == selectedId) ? 'selected' : '';
            opts += `<option value="${a.id}" ${sel}>${a.name}</option>`;
        });
        return opts;
    }

    function addTrack() {
        tracksState.push({
            name: '',
            type: 'Original',
            version: 'Original Mix',
            artists: [
                { id: '', roles: ['Vocalist'], isMain: true }
            ]
        });
        renderTracks();
    }

    function removeTrack(tIndex) {
        tracksState.splice(tIndex, 1);
        renderTracks();
    }

    function addArtist(tIndex) {
        tracksState[tIndex].artists.push({ id: '', roles: ['Vocalist'], isMain: false });
        renderTracks();
    }

    function removeArtist(tIndex, aIndex) {
        tracksState[tIndex].artists.splice(aIndex, 1);
        renderTracks();
    }

    function updateTrack(tIndex, field, value) {
        tracksState[tIndex][field] = value;
    }

    function updateArtist(tIndex, aIndex, field, value) {
        tracksState[tIndex].artists[aIndex][field] = value;
    }

    function updateArtistRoles(tIndex, aIndex, options) {
        const roles = Array.from(options).filter(opt => opt.selected).map(opt => opt.value);
        tracksState[tIndex].artists[aIndex].roles = roles;
    }

    function updateArtistMain(tIndex, aIndex, checked) {
        tracksState[tIndex].artists[aIndex].isMain = checked;
    }

    function renderTracks() {
        const container = document.getElementById('tracksContainer');
        container.innerHTML = '';

        tracksState.forEach((track, tIndex) => {
            let artistsHtml = '';
            
            track.artists.forEach((artist, aIndex) => {
                const roles = ['Music Producer', 'Vocalist', 'Writer', 'Composer', 'Musician'];
                let roleOptions = '';
                roles.forEach(r => {
                    const sel = artist.roles.includes(r) ? 'selected' : '';
                    roleOptions += `<option value="${r}" ${sel}>${r}</option>`;
                });

                artistsHtml += `
                    <div style="background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px; position:relative;">
                        <button type="button" onclick="removeArtist(${tIndex}, ${aIndex})" style="position:absolute; top:10px; right:10px; color:red; border:none; background:none; cursor:pointer;" title="Remove Artist"><ion-icon name="trash"></ion-icon></button>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label style="font-size: 0.75rem; color: #666;">Artist</label>
                                <select onchange="updateArtist(${tIndex}, ${aIndex}, 'id', this.value)" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                    ${generateArtistOptions(artist.id)}
                                </select>
                            </div>
                            <div>
                                <label style="font-size: 0.75rem; color: #666;">Roles (Hold Ctrl/Cmd)</label>
                                <select multiple required onchange="updateArtistRoles(${tIndex}, ${aIndex}, this.options)" style="width: 100%; height: 60px; padding: 4px; border: 1px solid #ccc;">
                                    ${roleOptions}
                                </select>
                            </div>
                        </div>
                        <div style="margin-top: 5px;">
                            <label style="font-size: 0.8rem; display:flex; align-items:center; gap:5px;">
                                <input type="checkbox" onchange="updateArtistMain(${tIndex}, ${aIndex}, this.checked)" ${artist.isMain ? 'checked' : ''}> Set as Main Artist
                            </label>
                        </div>
                    </div>
                `;
            });

            // Avoid division by zero
            let artistSplit = track.artists.length > 0 ? (50/track.artists.length).toFixed(2) : 0;

            const html = `
                <div style="background: #fdfdfd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e0e0e0; position:relative;">
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:15px;">
                        <h4 style="margin:0; color:#333;">Track ${tIndex + 1}</h4>
                        <button type="button" onclick="removeTrack(${tIndex})" style="color:red; background:none; border:none; cursor:pointer; font-size:0.9rem;" class="ios-btn-text"><ion-icon name="close-circle-outline"></ion-icon> Remove Track</button>
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; margin-bottom:15px;">
                        <div>
                            <label style="font-size: 0.85rem; color: #666; font-weight: 600;">Track Name</label>
                            <input type="text" value="${track.name}" oninput="updateTrack(${tIndex}, 'name', this.value)" placeholder="E.g. Summer Vibes" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px;">
                        </div>
                        <div>
                            <label style="font-size: 0.85rem; color: #666; font-weight: 600;">Type</label>
                            <select onchange="updateTrack(${tIndex}, 'type', this.value)" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px;">
                                <option value="Original" ${track.type === 'Original' ? 'selected' : ''}>Original</option>
                                <option value="Remix" ${track.type === 'Remix' ? 'selected' : ''}>Remix</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 0.85rem; color: #666; font-weight: 600;">Version</label>
                            <select onchange="updateTrack(${tIndex}, 'version', this.value)" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px;">
                                <option value="Original Mix" ${track.version === 'Original Mix' ? 'selected' : ''}>Original Mix</option>
                                <option value="Radio Edit" ${track.version === 'Radio Edit' ? 'selected' : ''}>Radio Edit</option>
                                <option value="Extended Mix" ${track.version === 'Extended Mix' ? 'selected' : ''}>Extended Mix</option>
                                <option value="Instrumental" ${track.version === 'Instrumental' ? 'selected' : ''}>Instrumental</option>
                                <option value="Acapella" ${track.version === 'Acapella' ? 'selected' : ''}>Acapella</option>
                            </select>
                        </div>
                    </div>

                    <h5 style="margin:0 0 10px 0; color:#444;">Artists on this track</h5>
                    ${artistsHtml}
                    <button type="button" onclick="addArtist(${tIndex})" class="ios-btn-text" style="font-size:0.9rem; margin-top:5px;"><ion-icon name="person-add-outline"></ion-icon> Add Artist to Track</button>
                    
                    <div style="background: #e8f5e9; padding: 10px; border-radius: 5px; margin-top: 15px; font-size:0.85rem; color: #2e7d32;">
                        <strong>Split Logic:</strong> Label 50% / Artists 50% (each artist receives ${artistSplit}%)
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        });
    }

    // Initialize with 1 track
    addTrack();

    // Folder Toggle Logic
    function toggleFolder(header) {
        const content = header.nextElementSibling;
        const icon = header.querySelector('.folder-chevron');
        if (content.style.display === 'none') {
            content.style.display = 'block';
            header.style.borderBottom = '1px solid #eee';
            icon.setAttribute('name', 'chevron-up-outline');
        } else {
            content.style.display = 'none';
            header.style.borderBottom = '1px solid transparent';
            icon.setAttribute('name', 'chevron-down-outline');
        }
    }

    document.getElementById('contractForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validate
        if(tracksState.length === 0) {
            alert("Please add at least one track.");
            return;
        }

        let isValid = true;
        tracksState.forEach(t => {
            if(t.artists.length === 0) {
                alert("Each track must have at least one artist.");
                isValid = false;
            }
            // Check if any artist is unselected
            t.artists.forEach(a => {
                if (!a.id) {
                    alert("Please ensure every selected artist dropdown has a valid artist picked.");
                    isValid = false;
                }
            });
        });
        if(!isValid) return;

        const payload = {
            release_name: document.getElementById('release_name').value,
            release_type: document.getElementById('release_type').value,
            tracks: tracksState
        };

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerText;
        submitBtn.innerText = "Generating Contracts...";
        submitBtn.disabled = true;

        try {
            const response = await fetch('../backend/generate_contract.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.status === 'success') {
                alert('Contracts Generated Successfully!');
                window.location.reload();
            } else {
                alert('Server Error: ' + result.message);
            }
        } catch (error) {
            alert('A network or server error occurred: ' + error.message + '\nCheck console for details.');
            console.error(error);
        } finally {
            submitBtn.innerText = originalText;
            submitBtn.disabled = false;
        }
    });
</script>