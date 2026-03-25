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
                                <?php if (strpos($file['name'], '.doc') !== false): ?>
                                    <ion-icon name="document-text" style="color: #2196f3; font-size: 1.2rem;"></ion-icon>
                                <?php else: ?>
                                    <ion-icon name="print" style="color: #4caf50; font-size: 1.2rem;"></ion-icon>
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
    <div class="modal-card" style="width: 100%; max-width: 600px; text-align: left;">
        <h2 style="margin-top: 0; margin-bottom: 1.5rem;">Generate New Contract</h2>

        <form id="contractForm">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 700; margin-bottom: 10px; font-size: 1.1rem;">Track Details</label>
                
                <div style="margin-bottom: 10px;">
                    <label style="font-size: 0.85rem; color: #666; font-weight: 600;">Track Name</label>
                    <input type="text" name="track_name" placeholder="E.g. Summer Vibes" required 
                           style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px;">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="font-size: 0.85rem; color: #666; font-weight: 600;">Type</label>
                        <select name="track_type" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; background: white; margin-top: 5px;">
                            <option value="Original">Original</option>
                            <option value="Remix">Remix</option>
                            <option value="Cover">Cover</option>
                            <option value="Bootleg">Bootleg</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; color: #666; font-weight: 600;">Version</label>
                        <select name="track_version" required style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; background: white; margin-top: 5px;">
                            <option value="Original Mix">Original Mix</option>
                            <option value="Radio Edit">Radio Edit</option>
                            <option value="Extended Mix">Extended Mix</option>
                            <option value="Instrumental">Instrumental</option>
                            <option value="Acapella">Acapella</option>
                        </select>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 700; margin-bottom: 5px; font-size: 1.1rem;">Select Artists</label>
                <select id="artistSelect" style="width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; background: white;">
                    <option value="">-- Add Artist --</option>
                    <?php foreach ($artists as $a): ?>
                        <option value="<?php echo $a['id']; ?>">
                            <?php echo htmlspecialchars($a['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="selectedArtistsList"
                style="margin-bottom: 1.5rem; border: 1px solid #eee; padding: 10px; max-height: 200px; overflow-y: auto; background: #f9f9f9; border-radius: 5px;">
                <!-- Selected artists will appear here -->
                <p style="color: #999; text-align: center; font-size: 0.9rem;" id="noArtistMsg">No artists selected</p>
            </div>

            <div style="background: #e8f5e9; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: space-between; font-weight: 700;">
                    <span>Label Share:</span>
                    <span>50% (Mech) / 70% (Pub)</span>
                </div>
                <div
                    style="display: flex; justify-content: space-between; font-weight: 700; color: #4caf50; margin-top: 5px;">
                    <span>Artist Share (Total):</span>
                    <span>50% (Mech) / 30% (Pub)</span>
                </div>
                <div style="margin-top: 5px; font-size: 0.9rem; color: #666;">
                    Split per artist: <span id="splitPerArtist">0%</span>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('newContractModal').style.display='none'"
                    class="ios-btn" style="background: #ccc; flex: 1;">Cancel</button>
                <button type="submit" class="ios-btn" style="flex: 1;">Generate Contracts</button>
            </div>
        </form>
    </div>
</div>

<script>
    const selectedArtists = new Map();
    const artistSelect = document.getElementById('artistSelect');
    const selectedArtistsList = document.getElementById('selectedArtistsList');
    const noArtistMsg = document.getElementById('noArtistMsg');
    const splitPerArtist = document.getElementById('splitPerArtist');

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

    // Add Artist Logic
    artistSelect.addEventListener('change', (e) => {
        const id = e.target.value;
        const name = e.target.options[e.target.selectedIndex].text;

        if (id && !selectedArtists.has(id)) {
            addArtist(id, name);
            e.target.value = ""; // Reset select
        }
    });

    function addArtist(id, name) {
        selectedArtists.set(id, { name: name, roles: [] });
        renderArtists();
        updateSplits();
    }

    function removeArtist(id) {
        selectedArtists.delete(id);
        renderArtists();
        updateSplits();
    }

    function updateArtistRoles(id, options) {
        const roles = Array.from(options).filter(opt => opt.selected).map(opt => opt.value);
        const artist = selectedArtists.get(id);
        artist.roles = roles;
        selectedArtists.set(id, artist);
    }

    function renderArtists() {
        if (selectedArtists.size === 0) {
            selectedArtistsList.innerHTML = '';
            selectedArtistsList.appendChild(noArtistMsg);
            noArtistMsg.style.display = 'block';
            return;
        }

        selectedArtistsList.innerHTML = '';
        noArtistMsg.style.display = 'none';

        selectedArtists.forEach((data, id) => {
            const item = document.createElement('div');
            item.style.background = '#fff';
            item.style.padding = '10px';
            item.style.marginBottom = '10px';
            item.style.border = '1px solid #ddd';
            item.style.borderRadius = '5px';

            const rolesHtml = `
                <select multiple onchange="updateArtistRoles('${id}', this.options)" style="width: 100%; margin-top: 5px; height: 80px; padding: 5px; border: 1px solid #ccc;">
                    <option value="Singer">Singer</option>
                    <option value="Lyricist">Lyricist</option>
                    <option value="Producer">Producer</option>
                    <option value="Performer">Performer</option>
                </select>
                <div style="font-size: 0.75rem; color: #999; margin-top: 2px;">Hold Ctrl/Cmd to select multiple roles</div>
            `;

            item.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <strong>${data.name}</strong>
                    <button type="button" onclick="removeArtist('${id}')" style="background: none; border: none; color: red; cursor: pointer; font-size: 1.2rem;">&times;</button>
                </div>
                ${rolesHtml}
            `;
            selectedArtistsList.appendChild(item);
        });
    }

    function updateSplits() {
        const count = selectedArtists.size;
        if (count > 0) {
            const mechSplit = (50 / count).toFixed(2);
            const pubSplit = (30 / count).toFixed(2);
            splitPerArtist.innerText = `${mechSplit}% (Mech) / ${pubSplit}% (Pub)`;
        } else {
            splitPerArtist.innerText = "0%";
        }
    }

    // Handle Form Submit
    document.getElementById('contractForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        if (selectedArtists.size === 0) {
            alert("Please select at least one artist.");
            return;
        }

        const formData = new FormData(e.target);

        // Append Artists Data
        selectedArtists.forEach((data, id) => {
            if (data.roles.length === 0) {
                // Default if no role selected? Or force select?
                // Let's assume 'Artist' if nothing selected, or handle in backend.
            }
            data.roles.forEach(role => {
                formData.append(`artists[${id}][roles][]`, role);
            });
            // If no roles, ensure ID catches
            if (data.roles.length === 0) {
                formData.append(`artists[${id}][roles][]`, 'Artist');
            }
        });

        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerText;
        submitBtn.innerText = "Generating...";
        submitBtn.disabled = true;

        try {
            const response = await fetch('../backend/generate_contract.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                alert('Contracts Generated Successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('An error occurred. Please try again.');
            console.error(error);
        } finally {
            submitBtn.innerText = originalText;
            submitBtn.disabled = false;
        }
    });

    // Make functions global for inline onclick
    window.removeArtist = removeArtist;
    window.updateArtistRoles = updateArtistRoles;
    window.toggleFolder = toggleFolder;
</script>