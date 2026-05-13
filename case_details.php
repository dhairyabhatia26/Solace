<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('student');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid case ID.";
    header("Location: " . base_url('my_cases.php'));
    exit();
}

$case_id = (int)$_GET['id'];
$student_id = $_SESSION['user_id'];

// Fetch case details only if it belongs to the logged-in student
$stmt = $pdo->prepare("SELECT * FROM wellness_cases WHERE id = ? AND student_id = ?");
$stmt->execute([$case_id, $student_id]);
$case = $stmt->fetch();

if (!$case) {
    $_SESSION['error_message'] = "Case not found or access denied.";
    header("Location: " . base_url('my_cases.php'));
    exit();
}

// Fetch student-visible remarks
$stmt = $pdo->prepare("
    SELECT cn.note, cn.created_at, u.name as counselor_name 
    FROM case_notes cn
    JOIN users u ON cn.counselor_id = u.id
    WHERE cn.case_id = ? AND cn.note_type = 'student_visible'
    ORDER BY cn.created_at DESC
");
$stmt->execute([$case_id]);
$remarks = $stmt->fetchAll();

// Check if feedback exists
$stmt = $pdo->prepare("SELECT id FROM feedback WHERE case_id = ?");
$stmt->execute([$case_id]);
$has_feedback = $stmt->fetch() !== false;

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2>Case #<?php echo $case['id']; ?>: <?php echo htmlspecialchars($case['title']); ?></h2>
                <p class="text-muted mb-0">Submitted on <?php echo date('F j, Y, g:i a', strtotime($case['created_at'])); ?></p>
            </div>
            <a href="<?php echo base_url('my_cases.php'); ?>" class="btn btn-outline-secondary">Back to My Cases</a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            
            <?php if (in_array($case['urgency'], ['high']) || in_array($case['severity'], ['high', 'critical'])): ?>
                <div class="alert alert-danger shadow-sm border-0 mb-4">
                    <strong>Important Support Notice:</strong> If this concern involves immediate harm or emergency risk, please contact institutional authorities, a trusted person, or emergency services immediately.
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Concern Description</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($case['description'])); ?></p>
                </div>
            </div>

            <!-- AI Guidance Display -->
            <?php if (!empty($case['ai_guidance'])): ?>
                <div class="card shadow-sm border-0 mb-4 border-start border-primary border-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 text-primary">AI-Guided Support Suggestion</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning py-2 mb-3 small">
                            <em>Disclaimer: AI-generated guidance is for support and awareness only. It is not a medical diagnosis or professional treatment recommendation.</em>
                        </div>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($case['ai_guidance'])); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Counselor Remarks -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Counselor Remarks</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($remarks) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($remarks as $remark): ?>
                                <li class="list-group-item p-4">
                                    <div class="d-flex w-100 justify-content-between mb-2">
                                        <h6 class="mb-1 text-primary"><?php echo htmlspecialchars($remark['counselor_name']); ?></h6>
                                        <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($remark['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($remark['note'])); ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <p class="mb-0">No remarks from counselors yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Sidebar Details -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Case Details</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <strong>Status</strong>
                            <span class="badge bg-<?php echo in_array($case['status'], ['resolved', 'closed']) ? 'success' : 'primary'; ?>">
                                <?php echo ucfirst($case['status']); ?>
                            </span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <strong>Category</strong>
                            <span><?php echo ucfirst($case['category']); ?></span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <strong>Urgency</strong>
                            <span class="badge bg-<?php echo $case['urgency'] == 'high' ? 'danger' : ($case['urgency'] == 'medium' ? 'warning' : 'info'); ?>">
                                <?php echo ucfirst($case['urgency']); ?>
                            </span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <strong>Support Mode</strong>
                            <span class="text-muted"><?php echo ucwords(str_replace('_', ' ', $case['support_mode'])); ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Wellness Indicators -->
            <?php if ($case['stress_score'] || $case['sleep_score'] || $case['academic_pressure_score']): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Wellness Indicators</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php if ($case['stress_score']): ?>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span>Stress Level</span>
                            <strong><?php echo $case['stress_score']; ?>/10</strong>
                        </li>
                        <?php endif; ?>
                        <?php if ($case['sleep_score']): ?>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span>Sleep Disturbance</span>
                            <strong><?php echo $case['sleep_score']; ?>/10</strong>
                        </li>
                        <?php endif; ?>
                        <?php if ($case['academic_pressure_score']): ?>
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span>Academic Pressure</span>
                            <strong><?php echo $case['academic_pressure_score']; ?>/10</strong>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- Feedback Button -->
            <?php if (in_array($case['status'], ['resolved', 'closed']) && !$has_feedback): ?>
                <div class="d-grid">
                    <a href="<?php echo base_url('feedback.php?case_id=' . $case['id']); ?>" class="btn btn-success">Provide Feedback</a>
                </div>
            <?php elseif ($has_feedback): ?>
                <div class="alert alert-success text-center">
                    <small>Feedback has been submitted. Thank you!</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
