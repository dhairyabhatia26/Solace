<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('student');

if (!isset($_GET['case_id']) || !is_numeric($_GET['case_id'])) {
    $_SESSION['error_message'] = "Invalid case ID.";
    header("Location: " . base_url('my_cases.php'));
    exit();
}

$case_id = (int)$_GET['case_id'];
$student_id = $_SESSION['user_id'];

// Verify case exists, belongs to student, and is closed/resolved
$stmt = $pdo->prepare("SELECT id, title, status FROM wellness_cases WHERE id = ? AND student_id = ?");
$stmt->execute([$case_id, $student_id]);
$case = $stmt->fetch();

if (!$case || !in_array($case['status'], ['resolved', 'closed'])) {
    $_SESSION['error_message'] = "You can only provide feedback for resolved or closed cases.";
    header("Location: " . base_url('my_cases.php'));
    exit();
}

// Check if feedback already exists
$stmt = $pdo->prepare("SELECT id FROM feedback WHERE case_id = ?");
$stmt->execute([$case_id]);
if ($stmt->fetch()) {
    $_SESSION['error_message'] = "Feedback has already been submitted for this case.";
    header("Location: " . base_url('case_details.php?id=' . $case_id));
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = (int)$_POST['rating'];
    $comments = sanitizeInput($_POST['comments'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = "Please provide a valid rating between 1 and 5.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO feedback (case_id, student_id, rating, comments) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$case_id, $student_id, $rating, $comments])) {
            $_SESSION['success_message'] = "Thank you for your feedback!";
            header("Location: " . base_url('case_details.php?id=' . $case_id));
            exit();
        } else {
            $error = "An error occurred. Please try again.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Provide Feedback</h2>
            <p class="text-muted">Help us improve our support by providing feedback on your recent experience.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Case #<?php echo $case['id']; ?>: <?php echo htmlspecialchars($case['title']); ?></h5>
                </div>
                <div class="card-body p-4">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold d-block">How would you rate the support received?</label>
                            
                            <div class="btn-group" role="group" aria-label="Rating">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <input type="radio" class="btn-check" name="rating" id="rating<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                                    <label class="btn btn-outline-primary" for="rating<?php echo $i; ?>"><?php echo $i; ?></label>
                                <?php endfor; ?>
                            </div>
                            <div class="d-flex justify-content-center mt-2 small text-muted w-100" style="max-width: 250px; margin: 0 auto;">
                                <span class="me-auto">1 (Poor)</span>
                                <span>5 (Excellent)</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Additional Comments (Optional)</label>
                            <textarea name="comments" class="form-control" rows="4" placeholder="How did this help you? How can we improve?"></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?php echo base_url('case_details.php?id=' . $case_id); ?>" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Submit Feedback</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
