<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('admin');

// Build filters
$where_clauses = ["1=1"];
$params = [];

if (!empty($_GET['status'])) {
    $where_clauses[] = "wc.status = ?";
    $params[] = $_GET['status'];
}
if (!empty($_GET['category'])) {
    $where_clauses[] = "wc.category = ?";
    $params[] = $_GET['category'];
}
if (!empty($_GET['urgency'])) {
    $where_clauses[] = "wc.urgency = ?";
    $params[] = $_GET['urgency'];
}
if (!empty($_GET['severity'])) {
    $where_clauses[] = "wc.severity = ?";
    $params[] = $_GET['severity'];
}
if (!empty($_GET['counselor_id'])) {
    $where_clauses[] = "wc.assigned_counselor_id = ?";
    $params[] = $_GET['counselor_id'];
}
if (!empty($_GET['escalated']) && $_GET['escalated'] === '1') {
    $where_clauses[] = "wc.escalation_flag = 1";
}
if (!empty($_GET['support_mode'])) {
    $where_clauses[] = "wc.support_mode = ?";
    $params[] = $_GET['support_mode'];
}

$where_sql = implode(' AND ', $where_clauses);

$stmt = $pdo->prepare("
    SELECT wc.*, u.name as student_name, c.name as counselor_name 
    FROM wellness_cases wc
    LEFT JOIN users u ON wc.student_id = u.id
    LEFT JOIN users c ON wc.assigned_counselor_id = c.id
    WHERE $where_sql 
    ORDER BY wc.created_at DESC
");
$stmt->execute($params);
$cases = $stmt->fetchAll();

// Get counselors for dropdown
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'counselor' ORDER BY name ASC");
$counselors = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="page-title">Institution Cases Overview</h2>
            <p class="page-subtitle">Supervise all student wellness cases across the institution.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4 fade-in">
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label stat-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="academics" <?php echo ($_GET['category']??'') == 'academics' ? 'selected' : ''; ?>>Academics</option>
                        <option value="stress" <?php echo ($_GET['category']??'') == 'stress' ? 'selected' : ''; ?>>Stress</option>
                        <option value="sleep" <?php echo ($_GET['category']??'') == 'sleep' ? 'selected' : ''; ?>>Sleep</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label stat-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="submitted" <?php echo ($_GET['status']??'') == 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                        <option value="assigned" <?php echo ($_GET['status']??'') == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                        <option value="in progress" <?php echo ($_GET['status']??'') == 'in progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo ($_GET['status']??'') == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label stat-label">Urgency</label>
                    <select name="urgency" class="form-select">
                        <option value="">All Urgencies</option>
                        <option value="high" <?php echo ($_GET['urgency']??'') == 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo ($_GET['urgency']??'') == 'medium' ? 'selected' : ''; ?>>Medium</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label stat-label">Counselor</label>
                    <select name="counselor_id" class="form-select">
                        <option value="">All Counselors</option>
                        <?php foreach($counselors as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($_GET['counselor_id']??'') == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label stat-label">Escalated</label>
                    <select name="escalated" class="form-select">
                        <option value="">Any</option>
                        <option value="1" <?php echo ($_GET['escalated']??'') === '1' ? 'selected' : ''; ?>>Yes</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label stat-label">Mode</label>
                    <select name="support_mode" class="form-select">
                        <option value="">Any</option>
                        <option value="anonymous" <?php echo ($_GET['support_mode']??'') === 'anonymous' ? 'selected' : ''; ?>>Anon</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-solace w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Case Table -->
    <div class="card shadow-sm border-0 fade-in" style="animation-delay: 0.1s;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Student</th>
                            <th>Category</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Counselor</th>
                            <th>Date</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($cases) > 0): ?>
                            <?php foreach ($cases as $case): ?>
                                <?php
                                    $student_display = ($case['support_mode'] === 'anonymous') ? '<span class="text-muted">Anonymous</span>' : htmlspecialchars($case['student_name']);
                                    $counselor_display = $case['counselor_name'] ? htmlspecialchars($case['counselor_name']) : '<span class="text-warning">Unassigned</span>';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        #<?php echo $case['id']; ?>
                                        <?php if ($case['escalation_flag']): ?>
                                            <span class="badge bg-danger ms-1" title="Escalated">!</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold"><?php echo $student_display; ?></td>
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
                                    <td class="small fw-bold"><?php echo $counselor_display; ?></td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($case['created_at'])); ?></td>
                                    <td class="pe-4 text-end">
                                        <a href="<?php echo base_url('admin_case_details.php?id=' . $case['id']); ?>" class="btn btn-sm btn-solace-outline">Review</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center py-5 text-muted">No cases found matching criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
