<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('student');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $support_mode = sanitizeInput($_POST['support_mode'] ?? '');
    $urgency = sanitizeInput($_POST['urgency'] ?? '');
    
    // Optional scores
    $stress_score = !empty($_POST['stress_score']) ? (int)$_POST['stress_score'] : null;
    $sleep_score = !empty($_POST['sleep_score']) ? (int)$_POST['sleep_score'] : null;
    $academic_pressure_score = !empty($_POST['academic_pressure_score']) ? (int)$_POST['academic_pressure_score'] : null;

    if (empty($title) || empty($category) || empty($description) || empty($urgency) || empty($support_mode)) {
        $error = "Please fill in all required fields.";
    } elseif (
        ($stress_score !== null && ($stress_score < 1 || $stress_score > 10)) ||
        ($sleep_score !== null && ($sleep_score < 1 || $sleep_score > 10)) ||
        ($academic_pressure_score !== null && ($academic_pressure_score < 1 || $academic_pressure_score > 10))
    ) {
        $error = "Scores must be between 1 and 10.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO wellness_cases 
                (student_id, title, category, description, support_mode, urgency, stress_score, sleep_score, academic_pressure_score, status, escalation_flag) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', 0)");
            
            $stmt->execute([
                $_SESSION['user_id'], $title, $category, $description, $support_mode, $urgency, 
                $stress_score, $sleep_score, $academic_pressure_score
            ]);
            
            $new_case_id = $pdo->lastInsertId();
            
            if ($urgency === 'high') {
                notifyAdmins($pdo, $_SESSION['user_id'], $new_case_id, 'high_urgency', 'High Urgency Case Submitted', "A new case (#$new_case_id) with high urgency was submitted.");
            }
            
            $_SESSION['success_message'] = "Your wellness concern has been submitted successfully.";
            header("Location: " . base_url('my_cases.php'));
            exit();
        } catch (PDOException $e) {
            $error = "An error occurred while submitting your concern. Please try again.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Submit Wellness Concern</h2>
            <p class="text-muted">Safely share what you're going through. Your request will be handled securely.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Concern Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="Brief summary of your concern">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="academics">Academics</option>
                                    <option value="stress">Stress</option>
                                    <option value="anxiety">Anxiety</option>
                                    <option value="sleep">Sleep Issues</option>
                                    <option value="relationships">Relationships</option>
                                    <option value="career">Career/Future</option>
                                    <option value="financial pressure">Financial Pressure</option>
                                    <option value="family">Family issues</option>
                                    <option value="health">Physical Health</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Urgency Level <span class="text-danger">*</span></label>
                                <select name="urgency" class="form-select" required>
                                    <option value="">Select Urgency</option>
                                    <option value="low">Low - General support needed</option>
                                    <option value="medium">Medium - Causing distress</option>
                                    <option value="high">High - Needs immediate attention</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="5" required placeholder="Please describe what you are experiencing..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Preferred Support Mode <span class="text-danger">*</span></label>
                            <select name="support_mode" class="form-select" required>
                                <option value="">Select Support Mode</option>
                                <option value="anonymous">Anonymous (No direct contact, AI/general guidance only)</option>
                                <option value="counselor callback">Counselor Callback</option>
                                <option value="faculty mentor">Discuss with Faculty Mentor</option>
                                <option value="resource recommendation only">Resource Recommendation Only</option>
                            </select>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Optional Wellness Indicators</h5>
                        <p class="text-muted small">Help us understand your situation better by rating the following out of 10 (1 = minimal, 10 = extreme).</p>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Stress Level</label>
                                <input type="number" name="stress_score" class="form-control" min="1" max="10" placeholder="1-10">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sleep Disturbance</label>
                                <input type="number" name="sleep_score" class="form-control" min="1" max="10" placeholder="1-10">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Academic Pressure</label>
                                <input type="number" name="academic_pressure_score" class="form-control" min="1" max="10" placeholder="1-10">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Submit Support Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
