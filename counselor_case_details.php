<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('counselor');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . base_url('counselor_cases.php'));
    exit();
}

$case_id = (int)$_GET['id'];
$counselor_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT wc.*, u.name as student_name, u.email as student_email 
    FROM wellness_cases wc
    LEFT JOIN users u ON wc.student_id = u.id
    WHERE wc.id = ? AND (wc.assigned_counselor_id = ? OR wc.assigned_counselor_id IS NULL)
");
$stmt->execute([$case_id, $counselor_id]);
$case = $stmt->fetch();

if (!$case) {
    $_SESSION['error_message'] = "Case not found or you do not have permission to view it.";
    header("Location: " . base_url('counselor_cases.php'));
    exit();
}

// Notes
$stmt = $pdo->prepare("SELECT cn.*, u.name as author_name FROM case_notes cn JOIN users u ON cn.counselor_id = u.id WHERE cn.case_id = ? ORDER BY cn.created_at DESC");
$stmt->execute([$case_id]);
$notes = $stmt->fetchAll();

// Recommended Resources
$stmt = $pdo->prepare("SELECT r.title, r.file_path, r.link, cr.created_at FROM case_resources cr JOIN resources r ON cr.resource_id = r.id WHERE cr.case_id = ?");
$stmt->execute([$case_id]);
$case_resources = $stmt->fetchAll();

// All Resources for dropdown
$stmt = $pdo->query("SELECT id, title FROM resources ORDER BY title ASC");
$all_resources = $stmt->fetchAll();

$student_display = ($case['support_mode'] === 'anonymous') ? 'Anonymous Student' : htmlspecialchars($case['student_name']);
$is_critical = ($case['severity'] === 'critical' || strtolower($case['ai_risk_pattern'] ?? '') === 'critical');

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2>Manage Support Request #<?php echo $case['id']; ?></h2>
                <span class="badge bg-secondary"><?php echo ucfirst($case['category']); ?></span>
            </div>
            <a href="<?php echo base_url('counselor_cases.php'); ?>" class="btn btn-outline-secondary">Back to Cases</a>
        </div>
    </div>

    <?php if ($is_critical): ?>
        <div class="alert alert-danger shadow-sm border-0 mb-4">
            <h5 class="alert-heading">Critical Risk Alert</h5>
            <p class="mb-0">This concern indicates critical risk. If this concern involves immediate harm or emergency risk, please contact institutional authorities, a trusted person, or emergency services immediately. Ensure escalation flag is set.</p>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Left Column: Case Details & AI -->
        <div class="col-md-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Student Concern</h5>
                    <span class="badge bg-<?php echo $case['urgency'] == 'high' ? 'danger' : ($case['urgency'] == 'medium' ? 'warning' : 'info'); ?>">
                        Urgency: <?php echo ucfirst($case['urgency']); ?>
                    </span>
                </div>
                <div class="card-body">
                    <h6 class="card-title fw-bold"><?php echo htmlspecialchars($case['title']); ?></h6>
                    <p class="mb-3"><?php echo nl2br(htmlspecialchars($case['description'])); ?></p>
                    
                    <hr>
                    <div class="row small text-muted">
                        <div class="col-sm-4"><strong>Student:</strong> <?php echo $student_display; ?></div>
                        <div class="col-sm-4"><strong>Mode:</strong> <?php echo ucwords(str_replace('_', ' ', $case['support_mode'])); ?></div>
                        <div class="col-sm-4"><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($case['created_at'])); ?></div>
                    </div>
                    <?php if ($case['stress_score'] || $case['sleep_score'] || $case['academic_pressure_score']): ?>
                        <div class="row mt-3 bg-light p-2 rounded">
                            <div class="col-sm-4"><strong>Stress:</strong> <?php echo $case['stress_score'] ?: 'N/A'; ?>/10</div>
                            <div class="col-sm-4"><strong>Sleep:</strong> <?php echo $case['sleep_score'] ?: 'N/A'; ?>/10</div>
                            <div class="col-sm-4"><strong>Academic:</strong> <?php echo $case['academic_pressure_score'] ?: 'N/A'; ?>/10</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- AI Intelligence Panel -->
            <div class="card shadow-sm border-0 mb-4 border-start border-info border-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">AI Intelligence</h5>
                    <small class="text-muted">Gemini Assistant</small>
                </div>
                <div class="card-body">
                    
                    <!-- AI Risk Pattern -->
                    <div class="mb-4">
                        <h6 class="fw-bold d-flex justify-content-between align-items-center">
                            AI-Indicated Risk Pattern
                            <form method="POST" action="<?php echo base_url('counselor_action.php'); ?>" class="d-inline">
                                <input type="hidden" name="action" value="ai_risk_pattern">
                                <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-info">Generate/Refresh</button>
                            </form>
                        </h6>
                        <?php if ($case['ai_risk_pattern']): ?>
                            <span class="badge bg-<?php echo in_array($case['ai_risk_pattern'], ['high', 'critical']) ? 'danger' : 'secondary'; ?> fs-6">
                                <?php echo ucfirst($case['ai_risk_pattern']); ?>
                            </span>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No risk pattern generated yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- AI Summary -->
                    <div class="mb-4">
                        <h6 class="fw-bold d-flex justify-content-between align-items-center">
                            Counselor-Facing Summary
                            <form method="POST" action="<?php echo base_url('counselor_action.php'); ?>" class="d-inline">
                                <input type="hidden" name="action" value="ai_summary">
                                <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-info">Generate/Refresh</button>
                            </form>
                        </h6>
                        <?php if ($case['ai_summary']): ?>
                            <div class="bg-light p-3 rounded small">
                                <?php echo nl2br(htmlspecialchars($case['ai_summary'])); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No summary generated yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- AI Guidance -->
                    <div class="mb-0">
                        <h6 class="fw-bold d-flex justify-content-between align-items-center">
                            Non-Clinical Guidance Suggestions
                            <form method="POST" action="<?php echo base_url('counselor_action.php'); ?>" class="d-inline">
                                <input type="hidden" name="action" value="ai_guidance">
                                <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-info">Generate/Refresh</button>
                            </form>
                        </h6>
                        <?php if ($case['ai_guidance']): ?>
                            <div class="bg-light p-3 rounded small">
                                <?php echo nl2br(htmlspecialchars($case['ai_guidance'])); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No guidance generated yet.</p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            
            <!-- Notes Timeline -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Case Notes</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($notes) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($notes as $note): ?>
                                <li class="list-group-item p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-bold"><?php echo htmlspecialchars($note['author_name']); ?></span>
                                        <span class="badge bg-<?php echo $note['note_type'] == 'internal' ? 'secondary' : 'primary'; ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $note['note_type'])); ?>
                                        </span>
                                    </div>
                                    <p class="mb-1 small"><?php echo nl2br(htmlspecialchars($note['note'])); ?></p>
                                    <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($note['created_at'])); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">No notes added yet.</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Right Column: Actions & Updates -->
        <div class="col-md-5">
            <!-- Update Case Form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Update Case</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo base_url('counselor_action.php'); ?>">
                        <input type="hidden" name="action" value="update_case">
                        <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php
                                $statuses = ['submitted', 'under review', 'assigned', 'in progress', 'resolved', 'closed'];
                                foreach ($statuses as $st) {
                                    $sel = ($case['status'] == $st) ? 'selected' : '';
                                    echo "<option value=\"$st\" $sel>".ucfirst($st)."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Severity Assessment</label>
                            <select name="severity" class="form-select">
                                <option value="">Select Severity</option>
                                <?php
                                $severities = ['low', 'moderate', 'high', 'critical'];
                                foreach ($severities as $sv) {
                                    $sel = ($case['severity'] == $sv) ? 'selected' : '';
                                    echo "<option value=\"$sv\" $sel>".ucfirst($sv)."</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="escalation_flag" id="escFlag" value="1" <?php echo $case['escalation_flag'] ? 'checked' : ''; ?>>
                            <label class="form-check-label text-danger fw-bold" for="escFlag">Escalate to Leadership (HOD/Principal)</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Updates</button>
                    </form>
                </div>
            </div>

            <!-- Add Note Form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Add Note</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo base_url('counselor_action.php'); ?>">
                        <input type="hidden" name="action" value="add_note">
                        <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">
                        
                        <div class="mb-3">
                            <textarea name="note" class="form-control" rows="3" required placeholder="Type your note here..."></textarea>
                        </div>
                        <div class="mb-3">
                            <select name="note_type" class="form-select">
                                <option value="internal">Internal Note (Hidden from Student)</option>
                                <option value="student_visible">Student-Visible Remark</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100">Add Note</button>
                    </form>
                </div>
            </div>

            <!-- Recommend Resource Form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recommend Resource</h5>
                </div>
                <div class="card-body">
                    <?php if (count($case_resources) > 0): ?>
                        <ul class="mb-3 list-unstyled">
                            <?php foreach ($case_resources as $cr): 
                                $res_link = !empty($cr['file_path']) ? base_url($cr['file_path']) : $cr['link'];
                            ?>
                                <li>✅ <a href="<?php echo htmlspecialchars($res_link); ?>" target="_blank"><?php echo htmlspecialchars($cr['title']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo base_url('counselor_action.php'); ?>">
                        <input type="hidden" name="action" value="recommend_resource">
                        <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">
                        
                        <div class="input-group">
                            <select name="resource_id" class="form-select" required>
                                <option value="">Select Resource...</option>
                                <?php foreach ($all_resources as $r): ?>
                                    <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-outline-success">Attach</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
