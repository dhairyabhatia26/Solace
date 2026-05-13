<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('counselor');
$counselor_id = $_SESSION['user_id'];

// Build filters
$where_clauses = ["(assigned_counselor_id = ? OR assigned_counselor_id IS NULL)"];
$params = [$counselor_id];

$filter_status = $_GET['status'] ?? '';
if ($filter_status) {
    $where_clauses[] = "status = ?";
    $params[] = $filter_status;
}

$filter_category = $_GET['category'] ?? '';
if ($filter_category) {
    $where_clauses[] = "category = ?";
    $params[] = $filter_category;
}

$filter_urgency = $_GET['urgency'] ?? '';
if ($filter_urgency) {
    $where_clauses[] = "urgency = ?";
    $params[] = $filter_urgency;
}

$filter_severity = $_GET['severity'] ?? '';
if ($filter_severity) {
    $where_clauses[] = "severity = ?";
    $params[] = $filter_severity;
}

$filter_escalated = $_GET['escalated'] ?? '';
if ($filter_escalated === '1') {
    $where_clauses[] = "escalation_flag = 1";
}

$where_sql = implode(' AND ', $where_clauses);

$stmt = $pdo->prepare("
    SELECT wc.*, u.name as student_name 
    FROM wellness_cases wc
    LEFT JOIN users u ON wc.student_id = u.id
    WHERE $where_sql 
    ORDER BY created_at DESC
");
$stmt->execute($params);
$cases = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Manage Cases</h2>
            <p class="text-muted">View your assigned cases and unassigned new requests.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="submitted" <?php echo $filter_status == 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                        <option value="assigned" <?php echo $filter_status == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                        <option value="in progress" <?php echo $filter_status == 'in progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $filter_status == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="academics" <?php echo $filter_category == 'academics' ? 'selected' : ''; ?>>Academics</option>
                        <option value="stress" <?php echo $filter_category == 'stress' ? 'selected' : ''; ?>>Stress</option>
                        <option value="anxiety" <?php echo $filter_category == 'anxiety' ? 'selected' : ''; ?>>Anxiety</option>
                        <option value="sleep" <?php echo $filter_category == 'sleep' ? 'selected' : ''; ?>>Sleep</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Urgency</label>
                    <select name="urgency" class="form-select">
                        <option value="">All Urgencies</option>
                        <option value="low" <?php echo $filter_urgency == 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo $filter_urgency == 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo $filter_urgency == 'high' ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Severity</label>
                    <select name="severity" class="form-select">
                        <option value="">All</option>
                        <option value="low" <?php echo $filter_severity == 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="moderate" <?php echo $filter_severity == 'moderate' ? 'selected' : ''; ?>>Moderate</option>
                        <option value="high" <?php echo $filter_severity == 'high' ? 'selected' : ''; ?>>High</option>
                        <option value="critical" <?php echo $filter_severity == 'critical' ? 'selected' : ''; ?>>Critical</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Escalated</label>
                    <select name="escalated" class="form-select">
                        <option value="">Any</option>
                        <option value="1" <?php echo $filter_escalated === '1' ? 'selected' : ''; ?>>Yes</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Case Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Severity</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($cases) > 0): ?>
                            <?php foreach ($cases as $case): ?>
                                <?php
                                    $student_display = ($case['support_mode'] === 'anonymous') ? 'Anonymous Student' : htmlspecialchars($case['student_name']);
                                ?>
                                <tr>
                                    <td>
                                        #<?php echo $case['id']; ?>
                                        <?php if ($case['escalation_flag']): ?>
                                            <span class="badge bg-danger ms-1" title="Escalated">!</span>
                                        <?php endif; ?>
                                        <?php if (is_null($case['assigned_counselor_id'])): ?>
                                            <span class="badge bg-warning ms-1" title="Unassigned">New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $student_display; ?></td>
                                    <td><?php echo htmlspecialchars(substr($case['title'], 0, 30)); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($case['category']); ?></span></td>
                                    <td>
                                        <span class="badge bg-<?php echo $case['urgency'] == 'high' ? 'danger' : ($case['urgency'] == 'medium' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($case['urgency']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo ucfirst($case['status']); ?></td>
                                    <td><?php echo $case['severity'] ? ucfirst($case['severity']) : '-'; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($case['created_at'])); ?></td>
                                    <td><a href="<?php echo base_url('counselor_case_details.php?id=' . $case['id']); ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center py-4">No cases found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
