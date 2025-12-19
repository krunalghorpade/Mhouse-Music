<?php
// admin/stats_view.php

// 1. Handle Time Range Filter
$range = $_GET['range'] ?? '7days';
$date_condition = '';
$group_format = '%Y-%m-%d';
$labels_php = [];
$date_limit = '';

switch ($range) {
    case '7days':
        $date_limit = date('Y-m-d', strtotime('-7 days'));
        $date_condition = "WHERE visited_at >= '$date_limit'";
        // Generate last 7 days labels to ensure continuity
        for ($i = 6; $i >= 0; $i--)
            $labels_php[] = date('Y-m-d', strtotime("-$i days"));
        break;
    case '28days':
        $date_limit = date('Y-m-d', strtotime('-28 days'));
        $date_condition = "WHERE visited_at >= '$date_limit'";
        $labels_php = []; // We'll let data drive it or sparse
        break;
    case '12months':
        $date_limit = date('Y-m-d', strtotime('-1 year'));
        $date_condition = "WHERE visited_at >= '$date_limit'";
        $group_format = '%Y-%m'; // Group by month
        break;
    case 'lifetime':
        $date_condition = ""; // All time
        $group_format = '%Y-%m';
        break;
    default:
        $date_limit = date('Y-m-d', strtotime('-7 days'));
        $date_condition = "WHERE visited_at >= '$date_limit'";
}

// 2. Fetch Data for Key Metrics

// A. Total Page Views (Hits) within range
$sql_views = "SELECT COUNT(*) FROM page_views $date_condition";
$total_views = $pdo->query($sql_views)->fetchColumn();

// B1. Total Unique Page Views (Unique combination of IP and Page URL)
$sql_unique_views = "SELECT COUNT(*) FROM (SELECT DISTINCT ip_address, page_url FROM page_views $date_condition)";
$total_unique_views = $pdo->query($sql_unique_views)->fetchColumn();

// B2. Total Unique Visitors (approximating 'Clicks' as Visits)
$sql_visitors = "SELECT COUNT(DISTINCT ip_address) FROM page_views $date_condition";
$total_visitors = $pdo->query($sql_visitors)->fetchColumn();

// C. Conversion (Views per Visitor?) user asked "% of clicks to the views"
// If Clicks=Visitors, and Views=PageViews. 
// If specific request is "%", usually means (Views / Clicks) * 100 ? Or (Visits / Views)?
// Let's assume user wants to know ratio of Hits vs Unique Visits
$conversion = ($total_visitors > 0) ? round(($total_visitors / $total_views) * 100, 1) : 0;
// Actually, let's flip it as "Avg Views per Visit" is a more standard metric, but user asked for %
// Let's stick to % of Unique vs Total? (Newness?)
// Or maybe they mean "Goal Conversion"?
// Let's clarify on UI: "% Retention (Visitors / Views)"??
// Re-reading: "% conversion (% of clicks to the views)"
// This phrasing is tricky. If I have 100 views from 10 ppl. 
// "Clicks to Views" -> 10 / 100 = 10%.
// Let's display it as "Visitor/View Ratio".

// 3. Prepare Chart Data (Grouped by Date)
// We need two datasets: Views and Visitors over time
if ($range == '12months' || $range == 'lifetime') {
    // SQLite syntax for grouping by month
    $sql_chart = "SELECT strftime('%Y-%m', visited_at) as period, COUNT(DISTINCT ip_address || page_url) as views, COUNT(DISTINCT ip_address) as visitors 
                  FROM page_views $date_condition 
                  GROUP BY period 
                  ORDER BY period ASC";
} else {
    // Group by day
    $sql_chart = "SELECT strftime('%Y-%m-%d', visited_at) as period, COUNT(DISTINCT ip_address || page_url) as views, COUNT(DISTINCT ip_address) as visitors 
                  FROM page_views $date_condition 
                  GROUP BY period 
                  ORDER BY period ASC";
}

$chart_data = $pdo->query($sql_chart)->fetchAll(PDO::FETCH_ASSOC);

$periods = [];
$data_views = [];
$data_visitors = [];

// Fill missing dates for short ranges
if ($range == '7days') {
    $temp_data = [];
    foreach ($chart_data as $d)
        $temp_data[$d['period']] = $d;

    foreach ($labels_php as $date) {
        $periods[] = $date;
        $data_views[] = $temp_data[$date]['views'] ?? 0;
        $data_visitors[] = $temp_data[$date]['visitors'] ?? 0;
    }
} else {
    foreach ($chart_data as $d) {
        $periods[] = $d['period'];
        $data_views[] = $d['views'];
        $data_visitors[] = $d['visitors'];
    }
}

// 4. Top Pages (Filtered)
$sql_top = "SELECT page_url, COUNT(*) as count FROM page_views $date_condition GROUP BY page_url ORDER BY count DESC LIMIT 5";
$top_pages = $pdo->query($sql_top)->fetchAll();

?>

<!-- Filter Toolbar -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <h1>Statistics</h1>

    <div
        style="background: var(--ios-card); padding: 0.3rem; border-radius: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <a href="?view=stats&range=7days" class="chip <?php echo $range == '7days' ? 'active' : ''; ?>"
            style="border-radius: 15px;">7D</a>
        <a href="?view=stats&range=28days" class="chip <?php echo $range == '28days' ? 'active' : ''; ?>"
            style="border-radius: 15px;">28D</a>
        <a href="?view=stats&range=12months" class="chip <?php echo $range == '12months' ? 'active' : ''; ?>"
            style="border-radius: 15px;">12M</a>
        <a href="?view=stats&range=lifetime" class="chip <?php echo $range == 'lifetime' ? 'active' : ''; ?>"
            style="border-radius: 15px;">All</a>
    </div>
</div>

<!-- Key Metrics Grid -->
<div class="card-grid">
    <div class="card">
        <div class="card-title">Unique Visitors ("Clicks")</div>
        <div class="card-value" style="color: var(--ios-blue);"><?php echo number_format($total_visitors); ?></div>
        <div style="font-size: 0.8rem; color: var(--ios-secondary);">Unique IP Addresses</div>
    </div>

    <div class="card">
        <div class="card-title">Unique Page Views</div>
        <div class="card-value"><?php echo number_format($total_unique_views); ?></div>
        <div style="font-size: 0.8rem; color: var(--ios-secondary);">Unique (Page + IP)</div>
    </div>

    <div class="card">
        <div class="card-title">Visitor Ratio</div>
        <div class="card-value"><?php echo $conversion; ?>%</div>
        <div style="font-size: 0.8rem; color: var(--ios-secondary);">% of Unique Visitors to Views</div>
    </div>
</div>

<!-- Chart -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.2rem;">Traffic Overview</h2>
    <div style="height: 300px; width: 100%;">
        <canvas id="trafficChart"></canvas>
    </div>
</div>

<!-- Top Pages -->
<h2>Top Visited Pages</h2>
<div class="ios-list">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <?php foreach ($top_pages as $page): ?>
            <tr>
                <td style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--ios-separator);">
                    <?php echo htmlspecialchars($page['page_url']); ?>
                </td>
                <td
                    style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--ios-separator); font-weight: 600; text-align: right;">
                    <?php echo number_format($page['count']); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('trafficChart').getContext('2d');
    const trafficChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($periods); ?>,
            datasets: [
                {
                    label: 'Unique Page Views',
                    data: <?php echo json_encode($data_views); ?>,
                    borderColor: '#007AFF',
                    backgroundColor: 'rgba(0, 122, 255, 0.1)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Unique Visitors',
                    data: <?php echo json_encode($data_visitors); ?>,
                    borderColor: '#34C759',
                    backgroundColor: 'rgba(52, 199, 89, 0.0)',
                    borderDash: [5, 5],
                    tension: 0.3,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F2F2F7'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
        }
    });
</script>