<?php
// includes/functions.php

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function getThemePreference($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT theme_preference FROM settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result ? $result['theme_preference'] : 'light';
}

function displayAlert() {
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($_SESSION['success_message']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                ' . htmlspecialchars($_SESSION['error_message']) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['error_message']);
    }
}

function createNotification($pdo, $user_id, $actor_id, $case_id, $type, $title, $message) {
    // Only insert if user_id exists
    if (!$user_id) return false;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, actor_id, case_id, type, title, message) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $actor_id, $case_id, $type, $title, $message]);
}

function notifyAdmins($pdo, $actor_id, $case_id, $type, $title, $message) {
    $stmt = $pdo->query("SELECT id FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll();
    foreach ($admins as $admin) {
        createNotification($pdo, $admin['id'], $actor_id, $case_id, $type, $title, $message);
    }
}

/**
 * Check if a column exists in a table safely
 */
function column_exists($pdo, $table, $column) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}
?>
