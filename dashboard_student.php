<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('student');

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['user_name'] ?? 'Student';

// Initialize variables with defaults
$stats = [
    'total_cases' => 0,
    'open_cases' => 0,
    'resolved_cases' => 0,
    'high_urgency_cases' => 0
];
$recent_cases = [];
$suggested_resources = [];

try {
    // 1. Get statistics
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total_cases,
        SUM(CASE WHEN status IN ('submitted', 'under review', 'assigned', 'in progress') THEN 1 ELSE 0 END) as open_cases,
        SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved_cases,
        SUM(CASE WHEN urgency = 'high' AND status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) as high_urgency_cases
        FROM wellness_cases WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $db_stats = $stmt->fetch();
    if ($db_stats) {
        $stats = array_merge($stats, $db_stats);
    }

    // 2. Get recent cases
    $stmt = $pdo->prepare("SELECT id, title, category, status, urgency, created_at FROM wellness_cases WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$student_id]);
    $recent_cases = $stmt->fetchAll();

    // 3. Get suggested resources (defensive column check)
    $has_file_path = column_exists($pdo, 'resources', 'file_path');
    $res_path_col = $has_file_path ? 'file_path' : 'link';
    
    // Check if is_active column exists
    $has_is_active = column_exists($pdo, 'resources', 'is_active');
    $where_clause = $has_is_active ? "WHERE is_active = 1" : "";

    $stmt = $pdo->query("SELECT id, title, category, description, $res_path_col as path FROM resources $where_clause ORDER BY RAND() LIMIT 3");
    $suggested_resources = $stmt->fetchAll();

} catch (Throwable $e) {
    error_log("Dashboard Error: " . $e->getMessage());
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
            <h2 class="page-title">Welcome back, <?php echo htmlspecialchars(explode(' ', $student_name)[0]); ?></h2>
            <p class="page-subtitle">Your wellness is our priority. Here's a quick overview of your support status.</p>
        </div>
        <div class="col-md-4 text-md-end pt-3">
            <a href="<?php echo base_url('submit_case.php'); ?>" class="btn btn-solace px-4 py-2">Submit New Concern</a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4 mb-5 fade-in">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Total Submissions</div>
                <div class="stat-value"><?php echo (int)$stats['total_cases']; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Active Cases</div>
                <div class="stat-value text-info"><?php echo (int)$stats['open_cases']; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">Resolved</div>
                <div class="stat-value text-success"><?php echo (int)$stats['resolved_cases']; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-label">High Priority</div>
                <div class="stat-value text-danger"><?php echo (int)$stats['high_urgency_cases']; ?></div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 fade-in" style="animation-delay: 0.1s;">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h5 class="mb-0 fw-bold">Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recent_cases) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Concern Title</th>
                                        <th>Category</th>
                                        <th>Urgency</th>
                                        <th>Status</th>
                                        <th class="pe-4 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_cases as $case): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold"><?php echo htmlspecialchars($case['title']); ?></td>
                                            <td><span class="badge bg-light text-muted border"><?php echo ucfirst($case['category']); ?></span></td>
                                            <td>
                                                <span class="badge bg-<?php echo $case['urgency'] == 'high' ? 'danger' : ($case['urgency'] == 'medium' ? 'warning' : 'info'); ?>">
                                                    <?php echo ucfirst($case['urgency']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo in_array($case['status'], ['resolved', 'closed']) ? 'success' : 'primary'; ?>">
                                                    <?php echo ucfirst($case['status']); ?>
                                                </span>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <a href="<?php echo base_url('case_details.php?id=' . $case['id']); ?>" class="btn btn-sm btn-solace-outline">Details</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <p class="text-muted mb-0">No wellness concerns submitted yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4 fade-in" style="animation-delay: 0.2s;">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Suggested for You</h5>
                    <a href="<?php echo base_url('resources.php'); ?>" class="text-decoration-none small fw-bold">View Library</a>
                </div>
                <div class="card-body">
                    <?php if (count($suggested_resources) > 0): ?>
                        <div class="d-grid gap-4">
                            <?php foreach ($suggested_resources as $resource): ?>
                                <div class="suggested-item">
                                    <div class="stat-label mb-1" style="font-size: 0.6rem;"><?php echo htmlspecialchars($resource['category']); ?></div>
                                    <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($resource['title']); ?></h6>
                                    <p class="text-muted small mb-3">
                                        <?php 
                                            $desc = (string)($resource['description'] ?? '');
                                            echo htmlspecialchars(strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc); 
                                        ?>
                                    </p>
                                    <?php 
                                        $res_link = !empty($resource['path']) ? base_url($resource['path']) : '#';
                                    ?>
                                    <a href="<?php echo $res_link; ?>" target="_blank" class="btn btn-sm btn-solace w-100">Access Resource</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small text-center py-4">Our curators are adding new resources. Check back soon!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Tips Card -->
            <div class="card border-0 shadow-sm mt-4 bg-primary text-white overflow-hidden position-relative">
                <div class="card-body p-4 position-relative" style="z-index: 1;">
                    <h5 class="fw-bold mb-3">Quick Wellness Tip</h5>
                    <p class="small mb-0 opacity-90">Try the "4-7-8" breathing technique: Inhale for 4s, hold for 7s, exhale for 8s. It helps reset your nervous system.</p>
                </div>
                <div class="position-absolute" style="right: -20px; bottom: -20px; opacity: 0.1;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" fill="currentColor" viewBox="0 0 16 16">
                      <path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zm0 13a6 6 0 1 1 0-12 6 6 0 0 1 0 12z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
