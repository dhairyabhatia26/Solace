<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('admin');

// Initialize $counselors as empty array to prevent undefined variable errors
$counselors = [];

try {
    // Get counselors with workload
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email, u.department, u.created_at,
        COUNT(wc.id) as total_assigned,
        SUM(CASE WHEN wc.status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) as open_cases,
        SUM(CASE WHEN wc.status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved_cases
        FROM users u
        LEFT JOIN wellness_cases wc ON u.id = wc.assigned_counselor_id
        WHERE u.role = 'counselor'
        GROUP BY u.id
        ORDER BY u.name ASC
    ");
    if ($stmt) {
        $counselors = $stmt->fetchAll();
    }
} catch (Exception $e) {
    error_log("Error fetching counselors: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Manage Support Staff</h2>
            <p class="text-muted">Supervise counselor workload and manage access.</p>
        </div>
    </div>

    <div class="row">
        <!-- Counselor List -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Active Counselors & Faculty Mentors</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Total Assigned</th>
                                    <th>Open Cases</th>
                                    <th>Resolved</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($counselors) > 0): ?>
                                    <?php foreach ($counselors as $c): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($c['name']); ?></div>
                                                <div class="small text-muted"><?php echo htmlspecialchars($c['email']); ?></div>
                                            </td>
                                            <td><?php echo htmlspecialchars($c['department']); ?></td>
                                            <td><?php echo $c['total_assigned']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $c['open_cases'] > 5 ? 'danger' : ($c['open_cases'] > 0 ? 'warning text-dark' : 'success'); ?>">
                                                    <?php echo $c['open_cases']; ?> Pending
                                                </span>
                                            </td>
                                            <td><?php echo $c['resolved_cases']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-4">No counselors found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Counselor -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Add New Counselor</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo base_url('admin_action.php'); ?>">
                        <input type="hidden" name="action" value="add_counselor">
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department / Clinic</label>
                            <input type="text" name="department" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Temporary Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
