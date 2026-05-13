<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('counselor');

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department = sanitizeInput($_POST['department']);
    $designation = sanitizeInput($_POST['designation']);
    $specialization = sanitizeInput($_POST['specialization']);
    
    $stmt = $pdo->prepare("UPDATE users SET department = ?, designation = ?, specialization = ? WHERE id = ?");
    $stmt->execute([$department, $designation, $specialization, $user_id]);
    
    $_SESSION['success_message'] = "Profile updated successfully.";
    header("Location: " . base_url('profile_counselor.php'));
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as assigned_cases,
        SUM(CASE WHEN status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) as open_cases,
        SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved_cases,
        SUM(CASE WHEN escalation_flag = 1 THEN 1 ELSE 0 END) as escalated_cases
    FROM wellness_cases 
    WHERE assigned_counselor_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="page-title">Counselor Profile</h2>
        <p class="page-subtitle">Manage your professional details and view your workload.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Professional Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department / Clinic</label>
                        <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($user['department']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Designation / Role</label>
                        <input type="text" name="designation" class="form-control" value="<?php echo htmlspecialchars($user['designation'] ?? ''); ?>" placeholder="e.g. Senior Wellness Counselor">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Specialization / Focus Area</label>
                        <input type="text" name="specialization" class="form-control" value="<?php echo htmlspecialchars($user['specialization'] ?? ''); ?>" placeholder="e.g. Academic Anxiety, Career Counseling">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Workload Summary</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                        Total Assigned Cases
                        <span class="badge bg-primary rounded-pill"><?php echo $stats['assigned_cases'] ?: 0; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                        Open Workload
                        <span class="badge bg-warning rounded-pill text-dark"><?php echo $stats['open_cases'] ?: 0; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                        Escalated Cases
                        <span class="badge bg-danger rounded-pill"><?php echo $stats['escalated_cases'] ?: 0; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 border-bottom-0">
                        Resolved Cases
                        <span class="badge bg-success rounded-pill"><?php echo $stats['resolved_cases'] ?: 0; ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
