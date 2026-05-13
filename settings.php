<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . base_url('login.php'));
    exit();
}

$user_id = $_SESSION['user_id'];

// Get current settings
$stmt = $pdo->prepare("SELECT * FROM settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->fetch();

if (!$settings) {
    $pdo->prepare("INSERT INTO settings (user_id) VALUES (?)")->execute([$user_id]);
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_settings') {
        $compact_view = isset($_POST['ui_compact_view']) ? 1 : 0;
        $notif_case_updates = isset($_POST['notif_case_updates']) ? 1 : 0;
        $notif_new_remarks = isset($_POST['notif_new_remarks']) ? 1 : 0;
        $notif_ai_summary = isset($_POST['notif_ai_summary']) ? 1 : 0;
        $notif_escalations = isset($_POST['notif_escalations']) ? 1 : 0;
        $default_support_mode = $_POST['default_support_mode'] ?? 'counselor callback';

        $stmt = $pdo->prepare("
            UPDATE settings SET 
                ui_compact_view = ?, 
                notif_case_updates = ?, 
                notif_new_remarks = ?, 
                notif_ai_summary = ?, 
                notif_escalations = ?, 
                default_support_mode = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $compact_view, $notif_case_updates, $notif_new_remarks, 
            $notif_ai_summary, $notif_escalations, $default_support_mode, $user_id
        ]);
        
        $_SESSION['success_message'] = "Settings updated successfully.";
        header("Location: " . base_url('settings.php'));
        exit();

    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user_id]);
                $_SESSION['success_message'] = "Password changed successfully.";
            } else {
                $_SESSION['error_message'] = "New passwords do not match.";
            }
        } else {
            $_SESSION['error_message'] = "Current password is incorrect.";
        }
        header("Location: " . base_url('settings.php'));
        exit();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="page-title">Account Settings</h2>
        <p class="page-subtitle">Customize your Solace experience and notification preferences.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Preferences & Notifications</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <h6 class="fw-bold mb-3">Notification Preferences</h6>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="notif_case_updates" value="1" <?php echo $settings['notif_case_updates'] ? 'checked' : ''; ?>>
                        <label class="form-check-label">Case Status Updates</label>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="notif_new_remarks" value="1" <?php echo $settings['notif_new_remarks'] ? 'checked' : ''; ?>>
                        <label class="form-check-label">New Remarks / Messages</label>
                    </div>
                    <?php if ($_SESSION['user_role'] === 'counselor' || $_SESSION['user_role'] === 'admin'): ?>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="notif_ai_summary" value="1" <?php echo $settings['notif_ai_summary'] ? 'checked' : ''; ?>>
                        <label class="form-check-label">AI Insights Generated</label>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="notif_escalations" value="1" <?php echo $settings['notif_escalations'] ? 'checked' : ''; ?>>
                        <label class="form-check-label">Critical Escalations</label>
                    </div>
                    <?php endif; ?>

                    <?php if ($_SESSION['user_role'] === 'student'): ?>
                    <h6 class="fw-bold mb-3">Privacy & Defaults</h6>
                    <div class="mb-4">
                        <label class="form-label text-muted small">Default Support Mode</label>
                        <select name="default_support_mode" class="form-select w-50">
                            <option value="counselor callback" <?php echo $settings['default_support_mode'] === 'counselor callback' ? 'selected' : ''; ?>>Counselor Callback</option>
                            <option value="anonymous" <?php echo $settings['default_support_mode'] === 'anonymous' ? 'selected' : ''; ?>>Anonymous Request</option>
                            <option value="faculty mentor" <?php echo $settings['default_support_mode'] === 'faculty mentor' ? 'selected' : ''; ?>>Faculty Mentor</option>
                            <option value="resource recommendation only" <?php echo $settings['default_support_mode'] === 'resource recommendation only' ? 'selected' : ''; ?>>Resource Recommendation Only</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary">Save Preferences</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Security</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-outline-danger w-100">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
