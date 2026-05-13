<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('student');

$student_id = $_SESSION['user_id'];

// Filtering logic
$where_clauses = ["student_id = ?"];
$params = [$student_id];

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

$where_sql = implode(' AND ', $where_clauses);
$stmt = $pdo->prepare("SELECT id, title, category, status, urgency, severity, created_at FROM wellness_cases WHERE $where_sql ORDER BY created_at DESC");
$stmt->execute($params);
$cases = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>My Support Requests</h2>
            <p class="text-muted">Track the status of your wellness concerns.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="submitted" <?php echo $filter_status == 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                        <option value="under review" <?php echo $filter_status == 'under review' ? 'selected' : ''; ?>>Under Review</option>
                        <option value="in progress" <?php echo $filter_status == 'in progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $filter_status == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="closed" <?php echo $filter_status == 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="academics" <?php echo $filter_category == 'academics' ? 'selected' : ''; ?>>Academics</option>
                        <option value="stress" <?php echo $filter_category == 'stress' ? 'selected' : ''; ?>>Stress</option>
                        <option value="anxiety" <?php echo $filter_category == 'anxiety' ? 'selected' : ''; ?>>Anxiety</option>
                        <option value="sleep" <?php echo $filter_category == 'sleep' ? 'selected' : ''; ?>>Sleep</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Urgency</label>
                    <select name="urgency" class="form-select">
                        <option value="">All Urgencies</option>
                        <option value="low" <?php echo $filter_urgency == 'low' ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo $filter_urgency == 'medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo $filter_urgency == 'high' ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Case Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (count($cases) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Urgency</th>
                                <th>Status</th>
                                <th>Date Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cases as $case): ?>
                                <tr>
                                    <td>#<?php echo $case['id']; ?></td>
                                    <td><?php echo htmlspecialchars($case['title']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($case['category']); ?></span></td>
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
                                    <td><?php echo date('M d, Y', strtotime($case['created_at'])); ?></td>
                                    <td><a href="<?php echo base_url('case_details.php?id=' . $case['id']); ?>" class="btn btn-sm btn-outline-primary">View Details</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <p class="text-muted mb-0">No support requests found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
