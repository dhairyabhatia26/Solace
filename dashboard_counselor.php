<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('counselor');

$counselor_id = $_SESSION['user_id'];
$counselor_name = $_SESSION['user_name'] ?? 'Counselor';

// Initialize variables
$stats = [
    'assigned_cases' => 0,
    'open_cases' => 0,
    'high_urgency_cases' => 0,
    'escalated_cases' => 0,
    'resolved_cases' => 0
];
$recent_cases = [];
$cat_labels = []; $cat_counts = [];

try {
    // 1. Get assigned cases stats
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as assigned_cases,
        SUM(CASE WHEN status IN ('submitted', 'under review', 'assigned', 'in progress') THEN 1 ELSE 0 END) as open_cases,
        SUM(CASE WHEN urgency = 'high' AND status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) as high_urgency_cases,
        SUM(CASE WHEN escalation_flag = 1 THEN 1 ELSE 0 END) as escalated_cases,
        SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved_cases
        FROM wellness_cases 
        WHERE assigned_counselor_id = ?");
    $stmt->execute([$counselor_id]);
    $db_stats = $stmt->fetch();
    if ($db_stats) {
        $stats = array_merge($stats, $db_stats);
    }

    // 2. Get recent assigned cases
    $stmt = $pdo->prepare("SELECT id, title, category, status, urgency, created_at, student_id, support_mode FROM wellness_cases WHERE assigned_counselor_id = ? AND status NOT IN ('closed', 'resolved') ORDER BY urgency DESC, created_at ASC LIMIT 5");
    $stmt->execute([$counselor_id]);
    $recent_cases = $stmt->fetchAll();

    // 3. Get chart data: category distribution
    $stmt = $pdo->prepare("SELECT category, COUNT(*) as count FROM wellness_cases WHERE assigned_counselor_id = ? GROUP BY category");
    $stmt->execute([$counselor_id]);
    $category_data = $stmt->fetchAll();
    foreach ($category_data as $row) {
        $cat_labels[] = ucfirst($row['category']);
        $cat_counts[] = $row['count'];
    }

} catch (Throwable $e) {
    error_log("Counselor Dashboard Error: " . $e->getMessage());
    $error_info = "Note: Some dashboard data could not be loaded.";
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <?php if (isset($error_info)): ?>
        <div class="alert alert-warning"><?php echo $error_info; ?></div>
    <?php endif; ?>

    <div class="row mb-5 fade-in">
        <div class="col-md-8">
            <h2 class="page-title">Counselor Workspace</h2>
            <p class="page-subtitle">Supporting students through empathetic guidance and timely intervention.</p>
        </div>
        <div class="col-md-4 text-md-end pt-3">
            <a href="<?php echo base_url('counselor_cases.php'); ?>" class="btn btn-solace px-4 py-2">Manage All Assignments</a>
        </div>
    </div>

    <!-- Stat Grid -->
    <div class="row g-4 mb-5 fade-in">
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-label">Assigned</div>
                <div class="stat-value" style="font-size: 1.5rem;"><?php echo (int)$stats['assigned_cases']; ?></div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="stat-card">
                <div class="stat-label">Active</div>
                <div class="stat-value text-info" style="font-size: 1.5rem;"><?php echo (int)$stats['open_cases']; ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-label">High Priority</div>
                <div class="stat-value text-danger" style="font-size: 1.5rem;"><?php echo (int)$stats['high_urgency_cases']; ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-label">Escalated</div>
                <div class="stat-value text-warning" style="font-size: 1.5rem;"><?php echo (int)$stats['escalated_cases']; ?></div>
            </div>
        </div>
        <div class="col-md-2 col-12">
            <div class="stat-card">
                <div class="stat-label">Resolved</div>
                <div class="stat-value text-success" style="font-size: 1.5rem;"><?php echo (int)$stats['resolved_cases']; ?></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Priority Queue -->
        <div class="col-lg-8 fade-in" style="animation-delay: 0.1s;">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Active Support Queue</h5>
                    <span class="badge bg-light text-primary border border-primary-subtle">Priority Order</span>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recent_cases) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Case</th>
                                        <th>Category</th>
                                        <th>Urgency</th>
                                        <th>Status</th>
                                        <th class="pe-4 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_cases as $case): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold">#<?php echo $case['id']; ?></div>
                                                <div class="text-muted x-small"><?php echo htmlspecialchars(substr($case['title'], 0, 30)); ?>...</div>
                                            </td>
                                            <td><span class="badge bg-light text-muted border"><?php echo ucfirst($case['category']); ?></span></td>
                                            <td>
                                                <span class="badge bg-<?php echo $case['urgency'] == 'high' ? 'danger' : ($case['urgency'] == 'medium' ? 'warning' : 'info'); ?>">
                                                    <?php echo ucfirst($case['urgency']); ?>
                                                </span>
                                            </td>
                                            <td><span class="small fw-bold"><?php echo ucfirst($case['status']); ?></span></td>
                                            <td class="pe-4 text-end">
                                                <a href="<?php echo base_url('counselor_case_details.php?id=' . $case['id']); ?>" class="btn btn-sm btn-solace-outline px-3">Open</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <p class="text-muted mb-0">All assigned cases are currently resolved or closed.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="col-lg-4 fade-in" style="animation-delay: 0.2s;">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold">Case Distribution</h5>
                </div>
                <div class="card-body p-4 text-center">
                    <?php if (count($cat_labels) > 0): ?>
                        <canvas id="categoryChart" height="250"></canvas>
                    <?php else: ?>
                        <p class="text-muted py-5">No category data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Chart.defaults.font.family = "'Outfit', sans-serif";

    <?php if (count($cat_labels) > 0): ?>
    new Chart(document.getElementById('categoryChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($cat_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($cat_counts); ?>,
                backgroundColor: ['#2E7D5B', '#49A880', '#D1FAE5', '#E0E7FF', '#FEF3C7', '#FEE2E2', '#F3F4F6'],
                borderWidth: 4,
                borderColor: '#FFFFFF'
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
