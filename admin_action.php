<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'override_case') {
        $case_id = (int)$_POST['case_id'];
        $status = $_POST['status'];
        $escalation_flag = isset($_POST['escalation_flag']) ? 1 : 0;
        $assigned_counselor_id = !empty($_POST['assigned_counselor_id']) ? (int)$_POST['assigned_counselor_id'] : null;

        $stmt = $pdo->prepare("UPDATE wellness_cases SET status = ?, escalation_flag = ?, assigned_counselor_id = ? WHERE id = ?");
        $stmt->execute([$status, $escalation_flag, $assigned_counselor_id, $case_id]);
        $_SESSION['success_message'] = "Supervisory overrides applied successfully.";
        
        if ($assigned_counselor_id) {
            createNotification($pdo, $assigned_counselor_id, $_SESSION['user_id'], $case_id, 'assignment', 'Case Assigned', "Admin has assigned or reassigned case #$case_id to you.");
        }
        
        header("Location: " . base_url('admin_case_details.php?id=' . $case_id));
        exit();

    } elseif ($action === 'add_admin_note') {
        $case_id = (int)$_POST['case_id'];
        $note = sanitizeInput($_POST['note']);
        $admin_id = $_SESSION['user_id'];
        
        if (!empty($note)) {
            $stmt = $pdo->prepare("INSERT INTO case_notes (case_id, counselor_id, note_type, note) VALUES (?, ?, 'internal', ?)");
            $stmt->execute([$case_id, $admin_id, $note]);
            $_SESSION['success_message'] = "Supervisory note added.";
        }
        header("Location: " . base_url('admin_case_details.php?id=' . $case_id));
        exit();

    } elseif ($action === 'add_counselor') {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $department = sanitizeInput($_POST['department']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // check email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error_message'] = "Email already in use.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, 'counselor', ?)");
            $stmt->execute([$name, $email, $password, $department]);
            $_SESSION['success_message'] = "Counselor added successfully.";
        }
        header("Location: " . base_url('manage_counselors.php'));
        exit();
        
    } elseif ($action === 'add_resource') {
        $title = sanitizeInput($_POST['title']);
        $category = sanitizeInput($_POST['category']);
        $description = sanitizeInput($_POST['description']);
        $file_path = sanitizeInput($_POST['file_path']);
        $source_name = sanitizeInput($_POST['source_name'] ?? '');
        $source_url = sanitizeInput($_POST['source_url'] ?? '');
        $audience = sanitizeInput($_POST['audience']);
        $admin_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare("INSERT INTO resources (title, category, description, file_path, source_name, source_url, audience, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $category, $description, $file_path, $source_name, $source_url, $audience, $admin_id]);
        $_SESSION['success_message'] = "Resource record created. Ensure you place the PDF file inside assets/resources/pdfs/";
        header("Location: " . base_url('manage_resources.php'));
        exit();

    } elseif ($action === 'update_resource') {
        $id = (int)$_POST['resource_id'];
        $title = sanitizeInput($_POST['title']);
        $category = sanitizeInput($_POST['category']);
        $description = sanitizeInput($_POST['description']);
        $file_path = sanitizeInput($_POST['file_path']);
        $source_name = sanitizeInput($_POST['source_name'] ?? '');
        $source_url = sanitizeInput($_POST['source_url'] ?? '');
        $audience = sanitizeInput($_POST['audience']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE resources SET title = ?, category = ?, description = ?, file_path = ?, source_name = ?, source_url = ?, audience = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$title, $category, $description, $file_path, $source_name, $source_url, $audience, $is_active, $id]);
        $_SESSION['success_message'] = "Resource updated successfully.";
        header("Location: " . base_url('manage_resources.php'));
        exit();
        
    } elseif ($action === 'delete_resource') {
        $resource_id = (int)$_POST['resource_id'];
        $stmt = $pdo->prepare("DELETE FROM resources WHERE id = ?");
        $stmt->execute([$resource_id]);
        $_SESSION['success_message'] = "Resource record deleted.";
        header("Location: " . base_url('manage_resources.php'));
        exit();
    }
}
header("Location: " . base_url('dashboard_admin.php'));
exit();
?>
