<?php
// admin/demos_view.php

// Handle Rating Update (AJAX is ideal, but using simple POST form for now per constraints)
if (isset($_POST['update_rating'])) {
    $id = $_POST['id'];
    $rating = $_POST['rating'];
    $stmt = $pdo->prepare("UPDATE demos SET rating = ? WHERE id = ?");
    $stmt->execute([$rating, $id]);
    // Redirect to avoid resubmission, keeping filters
    $filter_q = http_build_query($_GET);
    echo "<script>window.location.href='?view=demos&" . $filter_q . "';</script>";
    exit;
}

// Handle Status Update
if (isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE demos SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    $filter_q = http_build_query($_GET);
    echo "<script>window.location.href='?view=demos&" . $filter_q . "';</script>";
    exit;
}

// Search & Sort Params
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'submitted_at';
$order = $_GET['order'] ?? 'DESC';
$status_filter = $_GET['status_filter'] ?? 'all';

// Build Query
$sql = "SELECT * FROM demos WHERE (artist_name LIKE ? OR track_name LIKE ? OR email LIKE ?)";
$search_term = "%$search%";
$params = [$search_term, $search_term, $search_term];

if ($status_filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

$valid_sorts = ['submitted_at', 'rating', 'artist_name'];
if (!in_array($sort, $valid_sorts))
    $sort = 'submitted_at';
if ($order !== 'ASC' && $order !== 'DESC')
    $order = 'DESC';

$sql .= " ORDER BY $sort $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$demos = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Manage Demos</h1>
    <div style="color: var(--ios-secondary);">
        Total: <?php echo count($demos); ?>
    </div>
</div>

<!-- Toolbar -->
<div class="card" style="margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
    <!-- Search -->
    <form method="GET" style="flex: 1; display: flex; gap: 0.5rem; white-space: nowrap;">
        <input type="hidden" name="view" value="demos">
        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
        <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">

        <select name="status_filter" onchange="this.form.submit()" style="width: auto; margin-bottom: 0;">
            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
            <option value="not_heard" <?php echo $status_filter == 'not_heard' ? 'selected' : ''; ?>>Not Heard</option>
            <option value="shortlist" <?php echo $status_filter == 'shortlist' ? 'selected' : ''; ?>>Shortlist</option>
            <option value="accepted" <?php echo $status_filter == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
        </select>

        <input type="text" name="search" placeholder="Search Artist, Track, Email"
            value="<?php echo htmlspecialchars($search); ?>" style="margin-bottom: 0;">
        <button type="submit" class="ios-btn">Search</button>
    </form>

    <!-- Sort -->
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <span style="color: var(--ios-secondary);">Sort:</span>
        <a href="?view=demos&sort=rating&order=DESC&search=<?php echo urlencode($search); ?>"
            class="chip <?php echo $sort == 'rating' ? 'active' : ''; ?>">Rating</a>
        <a href="?view=demos&sort=submitted_at&order=DESC&search=<?php echo urlencode($search); ?>"
            class="chip <?php echo $sort == 'submitted_at' ? 'active' : ''; ?>">Newest</a>
    </div>
</div>

<!-- List -->
<div style="display: grid; gap: 1rem;">
    <?php foreach ($demos as $demo): ?>
        <div class="card" style="display: grid; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 1.2rem; font-weight: 700;"><?php echo htmlspecialchars($demo['artist_name']); ?>
                    </div>
                    <div style="font-size: 1rem; color: var(--ios-secondary);">
                        <?php echo htmlspecialchars($demo['track_name']); ?>
                    </div>
                    <div style="margin-top: 0.5rem;">
                        <a href="<?php echo htmlspecialchars($demo['demo_url']); ?>" target="_blank" class="ios-btn-text"
                            style="display: flex; align-items: center; gap: 0.5rem;">
                            <ion-icon name="link-outline"></ion-icon> Listen Demo
                        </a>
                    </div>
                </div>

                <!-- Rating & Status -->
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                    <!-- Rating -->
                    <form method="POST">
                        <input type="hidden" name="update_rating" value="1">
                        <input type="hidden" name="id" value="<?php echo $demo['id']; ?>">
                        <div style="display: flex; align-items: center;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="submit" name="rating" value="<?php echo $i; ?>"
                                    style="background: none; border: none; cursor: pointer; color: <?php echo $i <= $demo['rating'] ? '#FFD700' : '#E5E5EA'; ?>; font-size: 1.5rem;">
                                    <ion-icon name="<?php echo $i <= $demo['rating'] ? 'star' : 'star-outline'; ?>"></ion-icon>
                                </button>
                            <?php endfor; ?>
                        </div>
                    </form>

                    <!-- Status -->
                    <form method="POST">
                        <input type="hidden" name="update_status" value="1">
                        <input type="hidden" name="id" value="<?php echo $demo['id']; ?>">
                        <select name="status" onchange="this.form.submit()"
                            style="margin-bottom: 0; padding: 0.4rem; font-size: 0.9rem; border: 1px solid var(--ios-separator); background: var(--ios-bg); width: auto;">
                            <option value="not_heard" <?php echo ($demo['status'] ?? 'not_heard') == 'not_heard' ? 'selected' : ''; ?>>Not Heard</option>
                            <option value="shortlist" <?php echo ($demo['status'] ?? '') == 'shortlist' ? 'selected' : ''; ?>>
                                Shortlist</option>
                            <option value="accepted" <?php echo ($demo['status'] ?? '') == 'accepted' ? 'selected' : ''; ?>>
                                Accepted</option>
                            <option value="rejected" <?php echo ($demo['status'] ?? '') == 'rejected' ? 'selected' : ''; ?>>
                                Rejected</option>
                        </select>
                    </form>
                </div>
            </div>

            <div style="height: 1px; background: var(--ios-separator);"></div>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; font-size: 0.9rem;">
                <div>
                    <strong>Email:</strong> <?php echo htmlspecialchars($demo['email']); ?>
                </div>
                <?php if ($demo['instagram']): ?>
                    <div>
                        <strong>IG:</strong> <?php echo htmlspecialchars($demo['instagram']); ?>
                    </div>
                <?php endif; ?>
                <?php if ($demo['phone']): ?>
                    <div>
                        <strong>Phone:</strong> <?php echo htmlspecialchars($demo['phone']); ?>
                    </div>
                <?php endif; ?>
                <div>
                    <strong>Date:</strong> <?php echo date('M d, Y', strtotime($demo['submitted_at'])); ?>
                </div>
            </div>

            <?php if ($demo['message']): ?>
                <div
                    style="background: #F2F2F7; padding: 0.8rem; border-radius: 8px; margin-top: 0.5rem; color: var(--ios-secondary); font-style: italic;">
                    "<?php echo htmlspecialchars($demo['message']); ?>"
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>