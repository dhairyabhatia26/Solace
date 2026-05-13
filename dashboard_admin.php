<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('admin');

// Initialize variables
$cases_this_month = 0;
$students_supported_this_month = 0;
$resolved_cases = 0;
$open_cases = 0;
$avg_resolution_days = 0;
$stale_cases = 0;
$avg_feedback = 0;
$stat_labels = []; $stat_counts = [];
$cw_labels = []; $cw_counts = [];
$critical_cases = [];

try {
    $current_month_start = date('Y-m-01 00:00:00');

    $cases_this_month = $pdo->query("SELECT COUNT(*) FROM wellness_cases WHERE created_at >= '$current_month_start'")->fetchColumn();
    $students_supported_this_month = $pdo->query("SELECT COUNT(DISTINCT student_id) FROM wellness_cases WHERE created_at >= '$current_month_start'")->fetchColumn();
    $resolved_cases = $pdo->query("SELECT COUNT(*) FROM wellness_cases WHERE status IN ('resolved', 'closed')")->fetchColumn();
    $open_cases = $pdo->query("SELECT COUNT(*) FROM wellness_cases WHERE status NOT IN ('resolved', 'closed')")->fetchColumn();
    
    $res_time = $pdo->query("SELECT AVG(DATEDIFF(resolved_at, created_at)) FROM wellness_cases WHERE status IN ('resolved', 'closed') AND resolved_at IS NOT NULL")->fetchColumn();
    $avg_resolution_days = round((float)$res_time, 1);
    
    $stale_cases = $pdo->query("SELECT COUNT(*) FROM wellness_cases WHERE status NOT IN ('resolved', 'closed') AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)")->fetchColumn();
    $avg_feedback = round((float)$pdo->query("SELECT AVG(rating) as avg_rating FROM feedback")->fetchColumn(), 1);

    // Charts Data
    $stat_data = $pdo->query("SELECT status, COUNT(*) as count FROM wellness_cases GROUP BY status")->fetchAll();
    foreach($stat_data as $row) {
        $stat_labels[] = ucfirst($row['status']);
        $stat_counts[] = $row['count'];
    }

    $workload_data = $pdo->query("SELECT u.name, COUNT(wc.id) as count FROM users u LEFT JOIN wellness_cases wc ON u.id = wc.assigned_counselor_id AND wc.status NOT IN ('resolved', 'closed') WHERE u.role = 'counselor' GROUP BY u.id")->fetchAll();
    foreach($workload_data as $row) {
        $cw_labels[] = $row['name'];
        $cw_counts[] = $row['count'];
    }

    // Recent Critical / Escalated
    $critical_cases = $pdo->query("
        SELECT wc.id, u.name as student_name, wc.category, wc.status, wc.severity, wc.support_mode, wc.created_at, c.name as counselor_name
        FROM wellness_cases wc
        LEFT JOIN users u ON wc.student_id = u.id
        LEFT JOIN users c ON wc.assigned_counselor_id = c.id
        WHERE (wc.escalation_flag = 1 OR wc.severity = 'critical') AND wc.status NOT IN ('resolved', 'closed')
        ORDER BY wc.created_at DESC LIMIT 5
    ")->fetchAll();

} catch (Throwable $e) {
    error_log("Admin Dashboard Error: " . $e->getMessage());
    $error_info = "Note: Some analytics data could not be loaded.";
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <?php if (isset($error_info)): ?>
        <div class="alert alert-warning"><?php echo $error_info; ?></div>
    <?php endif; ?>

    <div class="row mb-5 fade-in">
        <div class="col-md-8">
            <h2 class="page-title">Leadership Insights</h2>
            <p class="page-subtitle">Monitoring institutional wellness outcomes and counselor performance.</p>
        </div>
        <div class="col-md-4 text-md-end pt-3">
            <a href="<?php echo base_url('print_report.php'); ?>" class="btn btn-solace-outline me-2" target="_blank">Print Analysis</a>
            <a href="<?php echo base_url('admin_action.php?task=export_csv'); ?>" class="btn btn-solace">Export Data</a>
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="row g-4 mb-5 fade-in">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-label">Students Supported</div>
                <div class="stat-value text-primary"><?php echo (int)$students_supported_this_month; ?></div>
                <div class="small text-muted mt-1">This current month</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-label">Successful Resolutions</div>
                <div class="stat-value text-success"><?php echo (int)$resolved_cases; ?></div>
                <div class="small text-muted mt-1">Institutional total</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-label">Avg. Response Time</div>
                <div class="stat-value text-info"><?php echo $avg_resolution_days; ?> <small class="fw-bold" style="font-size: 1rem;">Days</small></div>
                <div class="small text-muted mt-1">From submission to resolution</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="stat-label">Student Satisfaction</div>
                <div class="stat-value text-warning"><?php echo $avg_feedback; ?><small class="text-muted" style="font-size: 1rem;">/5</small></div>
                <div class="small text-muted mt-1">Avg. feedback rating</div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-lg-8 fade-in" style="animation-delay: 0.1s;">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Critical Attention Queue</h5>
                    <span class="badge bg-danger"><?php echo count($critical_cases); ?> Escalated</span>
                </div>
                <div class="card-body p-0">
                    <?php if (count($critical_cases) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Case</th>
                                    <th>Category</th>
                                    <th>Severity</th>
                                    <th>Counselor</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($critical_cases as $c): 
                                    $student_display = ($c['support_mode'] === 'anonymous') ? 'Anonymous' : htmlspecialchars($c['student_name']);
                                ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold"><?php echo $student_display; ?></div>
                                            <div class="text-muted x-small">Case #<?php echo $c['id']; ?> • <?php echo date('M d', strtotime($c['created_at'])); ?></div>
                                        </td>
                                        <td><span class="badge bg-light text-muted border"><?php echo ucfirst($c['category']); ?></span></td>
                                        <td><span class="badge bg-danger"><?php echo ucfirst($c['severity']); ?></span></td>
                                        <td class="small fw-bold"><?php echo $c['counselor_name'] ?: '<span class="text-warning">Unassigned</span>'; ?></td>
                                        <td class="pe-4 text-end"><a href="<?php echo base_url('admin_case_details.php?id=' . $c['id']); ?>" class="btn btn-sm btn-solace-outline">Review</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="p-5 text-center text-muted">No escalated cases currently pending.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4 fade-in" style="animation-delay: 0.2s;">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Operational Watchlist</h5>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="stat-label">Stale Cases (> 3 Days)</span>
                            <span class="fw-bold <?php echo $stale_cases > 5 ? 'text-danger' : 'text-warning'; ?>"><?php echo (int)$stale_cases; ?></span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-<?php echo $stale_cases > 5 ? 'danger' : 'warning'; ?>" style="width: <?php echo min(100, (int)$stale_cases * 10); ?>%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="stat-label">Active Workload</span>
                            <span class="fw-bold text-primary"><?php echo (int)$open_cases; ?></span>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar bg-primary" style="width: <?php echo min(100, ((int)$open_cases / 50) * 100); ?>%"></div>
                        </div>
                    </div>

                    <hr class="my-4 opacity-10">
                    <div class="d-grid gap-2">
                        <a href="<?php echo base_url('admin_cases.php'); ?>" class="btn btn-solace-outline w-100">Browse All Cases</a>
                        <a href="<?php echo base_url('admin_analytics.php'); ?>" class="btn btn-solace w-100">AI Trends & Insights</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visual Analytics -->
    <div class="row g-4 fade-in" style="animation-delay: 0.3s;">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Case Workflow Status</h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Counselor Workload Balance</h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="workloadChart" height="250"></canvas>
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

    new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($stat_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($stat_counts); ?>,
                backgroundColor: ['#E5E7EB', '#FEF3C7', '#E0E7FF', '#D1FAE5', '#FEE2E2', '#F3F4F6'],
                borderColor: '#FFFFFF',
                borderWidth: 4
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } } }
        }
    });

    new Chart(document.getElementById('workloadChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($cw_labels); ?>,
            datasets: [{
                label: 'Active Cases',
                data: <?php echo json_encode($cw_counts); ?>,
                backgroundColor: '#2E7D5B',
                borderRadius: 8,
                barThickness: 32
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { 
                y: { beginAtZero: true, grid: { display: false }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
