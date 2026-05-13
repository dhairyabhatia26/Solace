<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirectUserDashboard($_SESSION['user_role']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $selected_role = $_POST['role'] ?? '';

    if (empty($email) || empty($password) || empty($selected_role)) {
        $error = "Please enter email, password, and select your role.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['role'] !== $selected_role) {
                    $error = "The selected role does not match this account.";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    redirectUserDashboard($user['role']);
                }
            } else {
                $error = "Invalid email or password.";
            }
        } catch (Throwable $e) {
            error_log("Login Error: " . $e->getMessage());
            $error = "A system error occurred. Please try again later.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">Login to Solace</h3>
            <p class="text-muted small">A structured wellness support platform for students and institutions.</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php displayAlert(); ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Login As</label>
                <select name="role" class="form-select" required autofocus>
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="counselor">Counselor / Faculty Mentor</option>
                    <option value="admin">Admin / HOD / Principal</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="d-grid mt-2">
                <button type="submit" class="btn btn-primary btn-lg">Login</button>
            </div>
        </form>
        <div class="text-center mt-4">
            <span class="text-muted">Don't have an account?</span> <a href="<?php echo base_url('register.php'); ?>" class="fw-bold text-decoration-none">Register here</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
