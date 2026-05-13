<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('admin');

// Fetch Aggregated Analytics
$aggregate_data = [];

// 1. Total Cases
$aggregate_data['total_cases'] = $pdo->query("SELECT COUNT(*) FROM wellness_cases")->fetchColumn();

// 2. Open Cases
$aggregate_data['open_cases'] = $pdo->query("SELECT COUNT(*) FROM wellness_cases WHERE status NOT IN ('resolved', 'closed')")->fetchColumn();

// 3. Category Counts
$cat_stmt = $pdo->query("SELECT category, COUNT(*) as count FROM wellness_cases GROUP BY category ORDER BY count DESC");
$aggregate_data['category_distribution'] = $cat_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 4. Urgency Counts
$urg_stmt = $pdo->query("SELECT urgency, COUNT(*) as count FROM wellness_cases GROUP BY urgency");
$aggregate_data['urgency_distribution'] = $urg_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 5. Monthly Trend (last 6 months)
$trend_stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM wellness_cases WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month ASC");
$aggregate_data['monthly_trend'] = $trend_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 6. Escalated Cases
$aggregate_data['escalated_cases_count'] = $pdo->query("SELECT COUNT(*) FROM wellness_cases WHERE escalation_flag = 1")->fetchColumn();

// 7. Average Feedback
$aggregate_data['average_feedback_rating'] = round((float)$pdo->query("SELECT AVG(rating) FROM feedback")->fetchColumn(), 1);

// Generate AI Insight if requested
$ai_insight_html = null;
$ai_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_insights'])) {
    require_once __DIR__ . '/includes/ai_helper.php';
    $ai_response = generateAdminInsights($aggregate_data);
    
    if (isset($ai_response['success'])) {
        // Convert markdown-style bullets and bold text to HTML for safe display
        $formatted = htmlspecialchars($ai_response['success']);
        $formatted = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $formatted);
        $formatted = preg_replace('/\* (.*?)\n/', '<li>$1</li>', $formatted);
        $formatted = str_replace("\n", "<br>", $formatted);
        
        $ai_insight_html = $formatted;
    } else {
        $ai_error = $ai_response['error'];
    }
}

// Chart arrays
$month_labels = array_keys($aggregate_data['monthly_trend'] ?? []);
$month_counts = array_values($aggregate_data['monthly_trend'] ?? []);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="page-title">Analytics & Leadership Insights</h2>
            <p class="page-subtitle">Deep dive into institutional wellness data and AI-generated macro observations.</p>
        </div>
    </div>

    <!-- AI Insights Panel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 border-start border-primary border-4 bg-light">
                <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        AI Institutional Insights
                    </h5>
                    <form method="POST">
                        <button type="submit" name="generate_insights" class="btn btn-solace shadow-sm">
                            Generate Monthly Report
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4"><em>Disclaimer: AI-generated institutional insights are based on aggregated platform data and should be reviewed by institute leadership before action. No PII is analyzed.</em></p>
                    
                    <?php if ($ai_error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($ai_error); ?></div>
                    <?php endif; ?>

                    <?php if ($ai_insight_html): ?>
                        <div class="insight-content bg-white p-4 rounded border shadow-sm" style="font-size: 1.05rem; line-height: 1.6;">
                            <?php echo $ai_insight_html; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted bg-white rounded border border-dashed">
                            <p class="mb-0">Click the button above to analyze current aggregated platform data and generate a leadership summary.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">6-Month Case Volume Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Detailed Category Breakdown</h5>
                </div>
                <div class="card-body">
                    <canvas id="detailedCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.color = '#6B7280';

    new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($month_labels); ?>,
            datasets: [{
                label: 'Total Cases',
                data: <?php echo json_encode($month_counts); ?>,
                borderColor: '#2E7D5B',
                backgroundColor: 'rgba(46, 125, 91, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    new Chart(document.getElementById('detailedCategoryChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($aggregate_data['category_distribution'] ?? [])); ?>,
            datasets: [{
                label: 'Count',
                data: <?php echo json_encode(array_values($aggregate_data['category_distribution'] ?? [])); ?>,
                backgroundColor: '#49A880',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
