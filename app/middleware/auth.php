<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . base_url('login.php'));
        exit();
    }
}

function requireRole($role) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isLoggedIn()) {
        header("Location: " . base_url('login.php'));
        exit();
    }
    
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
        // Log the mismatch for debugging
        $current_role = $_SESSION['user_role'] ?? 'none';
        error_log("Role mismatch: expected $role, got $current_role");
        
        // Redirect based on their actual role
        redirectUserDashboard($_SESSION['user_role'] ?? '');
        exit();
    }
}

function redirectUserDashboard($role) {
    switch ($role) {
        case 'student':
            header("Location: " . base_url('dashboard_student.php'));
            exit();
        case 'counselor':
            header("Location: " . base_url('dashboard_counselor.php'));
            exit();
        case 'admin':
            header("Location: " . base_url('dashboard_admin.php'));
            exit();
        default:
            header("Location: " . base_url('login.php'));
            exit();
    }
}
?>
