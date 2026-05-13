<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    header("Location: " . base_url('login.php'));
    exit();
}

$user_id = $_SESSION['user_id'];

// Get notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Mark all as read on load
$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$stmt->execute([$user_id]);

require_once __DIR__ . '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="page-title">Notifications</h2>
        <p class="page-subtitle">Recent updates on cases and support actions.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <?php if (count($notifications) > 0): ?>
                    <div class="list-group list-group-flush rounded">
                        <?php foreach ($notifications as $n): ?>
                            <?php 
                                $bg_class = $n['is_read'] ? 'bg-transparent' : 'bg-light border-start border-primary border-4';
                                
                                $link = '#';
                                if ($n['case_id']) {
                                    if ($_SESSION['user_role'] === 'student') $link = base_url('case_details.php?id=' . $n['case_id']);
                                    elseif ($_SESSION['user_role'] === 'counselor') $link = base_url('counselor_case_details.php?id=' . $n['case_id']);
                                    elseif ($_SESSION['user_role'] === 'admin') $link = base_url('admin_case_details.php?id=' . $n['case_id']);
                                }
                            ?>
                            <a href="<?php echo $link; ?>" class="list-group-item list-group-item-action p-4 <?php echo $bg_class; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($n['title']); ?></h6>
                                    <small class="text-muted"><?php echo date('M d, g:i A', strtotime($n['created_at'])); ?></small>
                                </div>
                                <p class="mb-1 small text-muted"><?php echo htmlspecialchars($n['message']); ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mb-3 opacity-50"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <p class="mb-0">You have no notifications.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
