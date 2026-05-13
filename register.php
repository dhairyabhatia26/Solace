<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirectUserDashboard($_SESSION['user_role']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'] ?? '';
    $department = sanitizeInput($_POST['department']);

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!in_array($role, ['student', 'counselor'])) {
        $error = "Invalid role selected.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email is already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$name, $email, $hashed_password, $role, $department])) {
                    $new_user_id = $pdo->lastInsertId();
                    $pdo->prepare("INSERT INTO settings (user_id, theme_preference) VALUES (?, 'light')")->execute([$new_user_id]);
                    
                    $_SESSION['success_message'] = "Registration successful. Please login.";
                    header("Location: " . base_url('login.php'));
                    exit();
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        } catch (Throwable $e) {
            error_log("Registration Error: " . $e->getMessage());
            $error = "A system error occurred. Please try again later.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">Create an Account</h3>
            <p class="text-muted small">A structured wellness support platform for students and institutions.</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">I am a...</label>
                <select name="role" class="form-select" required>
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="counselor">Counselor / Faculty Mentor</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Department / Major</label>
                <input type="text" name="department" class="form-control">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Register</button>
            </div>
        </form>
        <div class="text-center mt-4">
            <span class="text-muted">Already have an account?</span> <a href="<?php echo base_url('login.php'); ?>" class="fw-bold text-decoration-none">Login here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
