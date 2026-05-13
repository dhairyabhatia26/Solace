<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/ai_helper.php';

requireRole('counselor');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $case_id = (int)$_POST['case_id'];
    $counselor_id = $_SESSION['user_id'];

    // Ensure counselor has access (assigned or unassigned)
    $stmt = $pdo->prepare("SELECT assigned_counselor_id, escalation_flag, student_id FROM wellness_cases WHERE id = ?");
    $stmt->execute([$case_id]);
    $case_check = $stmt->fetch();
    
    if (!$case_check || ($case_check['assigned_counselor_id'] !== null && $case_check['assigned_counselor_id'] != $counselor_id)) {
        $_SESSION['error_message'] = "Access denied.";
        header("Location: " . base_url('counselor_cases.php'));
        exit();
    }

    if ($action === 'update_case') {
        $status = $_POST['status'];
        $severity = !empty($_POST['severity']) ? $_POST['severity'] : null;
        $escalation_flag = isset($_POST['escalation_flag']) ? 1 : 0;

        // If taking ownership of unassigned case
        $assign_update = "";
        if ($case_check['assigned_counselor_id'] === null) {
            $assign_update = ", assigned_counselor_id = " . $counselor_id;
        }

        $stmt = $pdo->prepare("UPDATE wellness_cases SET status = ?, severity = ?, escalation_flag = ? $assign_update WHERE id = ?");
        $stmt->execute([$status, $severity, $escalation_flag, $case_id]);
        $_SESSION['success_message'] = "Case updated successfully.";
        
        $student_id = $case_check['student_id'];
        createNotification($pdo, $student_id, $counselor_id, $case_id, 'status_update', 'Case Status Updated', "Your case (#$case_id) status was updated to $status.");

        // Notify admins if escalated
        if ($escalation_flag && (!$case_check['escalation_flag'])) {
            notifyAdmins($pdo, $counselor_id, $case_id, 'escalation', 'Case Escalated', "Case #$case_id has been escalated by the counselor.");
        }

    } elseif ($action === 'add_note') {
        $note = sanitizeInput($_POST['note']);
        $note_type = $_POST['note_type'] === 'student_visible' ? 'student_visible' : 'internal';
        
        if (!empty($note)) {
            $stmt = $pdo->prepare("INSERT INTO case_notes (case_id, counselor_id, note_type, note) VALUES (?, ?, ?, ?)");
            $stmt->execute([$case_id, $counselor_id, $note_type, $note]);
            $_SESSION['success_message'] = "Note added successfully.";
            
            if ($note_type === 'student_visible') {
                $student_id = $case_check['student_id'];
                createNotification($pdo, $student_id, $counselor_id, $case_id, 'new_remark', 'New Remark Added', "A counselor has left a new remark on your case (#$case_id).");
            }
        }

    } elseif ($action === 'recommend_resource') {
        $resource_id = (int)$_POST['resource_id'];
        $stmt = $pdo->prepare("INSERT INTO case_resources (case_id, resource_id, recommended_by) VALUES (?, ?, ?)");
        $stmt->execute([$case_id, $resource_id, $counselor_id]);
        $_SESSION['success_message'] = "Resource recommended.";
        
        $student_id = $case_check['student_id'];
        createNotification($pdo, $student_id, $counselor_id, $case_id, 'resource', 'New Resource Recommended', "A new resource has been recommended for your case (#$case_id).");

    } elseif (in_array($action, ['ai_summary', 'ai_guidance', 'ai_risk_pattern'])) {
        
        $stmt = $pdo->prepare("SELECT * FROM wellness_cases WHERE id = ?");
        $stmt->execute([$case_id]);
        $case_data = $stmt->fetch();

        if ($action === 'ai_summary') {
            $res = generateCounselorSummary($case_data);
            if (isset($res['success'])) {
                $stmt = $pdo->prepare("UPDATE wellness_cases SET ai_summary = ? WHERE id = ?");
                $stmt->execute([$res['success'], $case_id]);
                $_SESSION['success_message'] = "AI Summary generated.";
            } else {
                $_SESSION['error_message'] = $res['error'];
            }
        } elseif ($action === 'ai_guidance') {
            $res = generateGuidance($case_data);
            if (isset($res['success'])) {
                $stmt = $pdo->prepare("UPDATE wellness_cases SET ai_guidance = ? WHERE id = ?");
                $stmt->execute([$res['success'], $case_id]);
                $_SESSION['success_message'] = "AI Guidance generated.";
                
                $student_id = $case_data['student_id'];
                createNotification($pdo, $student_id, $counselor_id, $case_id, 'ai_guidance', 'AI Guidance Available', "AI guidance has been generated for your case (#$case_id).");
            } else {
                $_SESSION['error_message'] = $res['error'];
            }
        } elseif ($action === 'ai_risk_pattern') {
            $res = generateRiskPattern($case_data);
            if (isset($res['success'])) {
                $stmt = $pdo->prepare("UPDATE wellness_cases SET ai_risk_pattern = ? WHERE id = ?");
                $stmt->execute([$res['success'], $case_id]);
                $_SESSION['success_message'] = "AI Risk Pattern updated to: " . ucfirst($res['success']);
                
                if (in_array(strtolower($res['success']), ['critical'])) {
                    notifyAdmins($pdo, $counselor_id, $case_id, 'critical_risk', 'Critical Risk Pattern Detected', "AI identified a critical risk pattern for case #$case_id.");
                }
            } else {
                $_SESSION['error_message'] = $res['error'];
            }
        }
    }

    header("Location: " . base_url('counselor_case_details.php?id=' . $case_id));
    exit();
}
?>
