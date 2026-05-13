<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
$current_route = $_GET['page'] ?? '';

// Load unread notifications count
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../../config/db.php';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    $unread_notifs = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solace - Student Wellness Intelligence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/solace-ui.css'); ?>">
</head>
<body>

<?php if (isset($_SESSION['user_id'])): ?>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <?php
            $dashboard_link = 'index.php';
            if (isset($_SESSION['user_role'])) {
                $dashboard_link = 'dashboard_' . $_SESSION['user_role'] . '.php';
            }
            ?>
            <a class="navbar-brand d-flex align-items-center" href="<?php echo base_url($dashboard_link); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                Solace
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if ($_SESSION['user_role'] === 'student'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'dashboard_student.php' ? 'active' : ''; ?>" href="<?php echo base_url('dashboard_student.php'); ?>">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'submit_case.php' ? 'active' : ''; ?>" href="<?php echo base_url('submit_case.php'); ?>">Submit Concern</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo in_array($current_page, ['my_cases.php', 'case_details.php', 'feedback.php']) ? 'active' : ''; ?>" href="<?php echo base_url('my_cases.php'); ?>">My Cases</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'resources.php' ? 'active' : ''; ?>" href="<?php echo base_url('resources.php'); ?>">Resources</a>
                        </li>
                    <?php elseif ($_SESSION['user_role'] === 'counselor'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'dashboard_counselor.php' ? 'active' : ''; ?>" href="<?php echo base_url('dashboard_counselor.php'); ?>">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo in_array($current_page, ['counselor_cases.php', 'counselor_case_details.php']) ? 'active' : ''; ?>" href="<?php echo base_url('counselor_cases.php'); ?>">Assigned Cases</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'resources.php' ? 'active' : ''; ?>" href="<?php echo base_url('resources.php'); ?>">Resource Library</a>
                        </li>
                    <?php elseif ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'dashboard_admin.php' ? 'active' : ''; ?>" href="<?php echo base_url('dashboard_admin.php'); ?>">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo in_array($current_page, ['admin_cases.php', 'admin_case_details.php']) ? 'active' : ''; ?>" href="<?php echo base_url('admin_cases.php'); ?>">All Cases</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'admin_analytics.php' ? 'active' : ''; ?>" href="<?php echo base_url('admin_analytics.php'); ?>">Analytics & Insights</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'manage_counselors.php' ? 'active' : ''; ?>" href="<?php echo base_url('manage_counselors.php'); ?>">Counselors</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page == 'manage_resources.php' ? 'active' : ''; ?>" href="<?php echo base_url('manage_resources.php'); ?>">Manage Resources</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item me-3 d-flex align-items-center">
                        <a href="<?php echo base_url('notifications.php'); ?>" class="nav-link position-relative notification-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                            <?php if($unread_notifs > 0): ?>
                                <span class="notification-badge"><?php echo $unread_notifs > 99 ? '99+' : $unread_notifs; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                            </div>
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm" aria-labelledby="profileDropdown">
                            <?php if ($_SESSION['user_role'] === 'student' || $_SESSION['user_role'] === 'counselor'): ?>
                                <li><a class="dropdown-item" href="<?php echo base_url('profile_' . $_SESSION['user_role'] . '.php'); ?>">My Profile</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?php echo base_url('settings.php'); ?>">Account Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo base_url('logout.php'); ?>">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div id="content">
        <div class="container-fluid">
            <?php displayAlert(); ?>
<?php endif; ?>
