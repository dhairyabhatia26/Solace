<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('student');

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department = sanitizeInput($_POST['department']);
    $emergency_contact = sanitizeInput($_POST['emergency_contact']);
    
    $stmt = $pdo->prepare("UPDATE users SET department = ?, emergency_contact = ? WHERE id = ?");
    $stmt->execute([$department, $emergency_contact, $user_id]);
    
    $_SESSION['success_message'] = "Profile updated successfully.";
    header("Location: " . base_url('profile_student.php'));
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_cases,
        SUM(CASE WHEN status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) as open_cases,
        SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved_cases,
        MAX(created_at) as last_case_date
    FROM wellness_cases 
    WHERE student_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="page-title">My Profile</h2>
        <p class="page-subtitle">Manage your personal information and view your support history.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Personal Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" disabled>
                        <div class="form-text">Contact administration to change your registered name.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department / Major</label>
                        <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($user['department']); ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Emergency Contact (Optional)</label>
                        <input type="text" name="emergency_contact" class="form-control" value="<?php echo htmlspecialchars($user['emergency_contact'] ?? ''); ?>" placeholder="Name and Phone Number">
                        <div class="form-text">This will only be used in critical wellness situations.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Support Summary</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                        Total Requests Submitted
                        <span class="badge bg-primary rounded-pill"><?php echo $stats['total_cases'] ?: 0; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                        Currently Open Cases
                        <span class="badge bg-warning rounded-pill text-dark"><?php echo $stats['open_cases'] ?: 0; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 border-bottom-0">
                        Resolved Cases
                        <span class="badge bg-success rounded-pill"><?php echo $stats['resolved_cases'] ?: 0; ?></span>
                    </li>
                </ul>
                <?php if ($stats['last_case_date']): ?>
                    <hr>
                    <div class="text-center">
                        <span class="text-muted small">Last requested support on:<br><?php echo date('M d, Y', strtotime($stats['last_case_date'])); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
