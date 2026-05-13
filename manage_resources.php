<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('admin');

// Initialize variables
$resources = [];

try {
    // Build query for resources
    $has_file_path = column_exists($pdo, 'resources', 'file_path');
    $res_path_col = $has_file_path ? 'file_path' : 'link';
    
    $has_is_active = column_exists($pdo, 'resources', 'is_active');
    $active_col = $has_is_active ? ", is_active" : ", 1 as is_active";

    $stmt = $pdo->query("SELECT id, title, category, description, $res_path_col as path $active_col FROM resources ORDER BY created_at DESC");
    $resources = $stmt->fetchAll();

} catch (Throwable $e) {
    error_log("Manage Resources Error: " . $e->getMessage());
    $error_info = "Note: Resource list could not be loaded.";
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-5 fade-in">
        <div class="col-md-8">
            <h2 class="page-title">Manage Resources</h2>
            <p class="page-subtitle">Add, edit, or remove wellness content from the library.</p>
        </div>
        <div class="col-md-4 text-md-end pt-3">
            <button type="button" class="btn btn-solace" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                Add New Resource
            </button>
        </div>
    </div>

    <?php displayAlert(); ?>
    <?php if (isset($error_info)): ?>
        <div class="alert alert-warning"><?php echo $error_info; ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm fade-in">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($resources) > 0): ?>
                            <?php foreach ($resources as $res): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?php echo htmlspecialchars($res['title']); ?></td>
                                    <td><span class="badge bg-light text-muted border"><?php echo ucfirst(htmlspecialchars($res['category'])); ?></span></td>
                                    <td>
                                        <span class="badge bg-<?php echo $res['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $res['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="<?php echo base_url($res['path']); ?>" target="_blank" class="btn btn-sm btn-link text-decoration-none">View</a>
                                        <form action="<?php echo base_url('admin_action.php'); ?>" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_resource">
                                            <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-solace-outline ms-2">Toggle</button>
                                        </form>
                                        <form action="<?php echo base_url('admin_action.php'); ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                            <input type="hidden" name="action" value="delete_resource">
                                            <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 ms-2">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">No resources available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Resource Modal -->
<div class="modal fade" id="addResourceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold px-3 pt-3">Add New Resource</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo base_url('admin_action.php'); ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_resource">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="Resource Title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="category" class="form-select" required>
                            <option value="stress">Stress Management</option>
                            <option value="anxiety">Anxiety Awareness</option>
                            <option value="sleep">Sleep Hygiene</option>
                            <option value="academics">Academic Support</option>
                            <option value="career">Career Guidance</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3" required placeholder="Brief summary of the content"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Upload PDF</label>
                        <input type="file" name="resource_file" class="form-control" accept=".pdf">
                        <div class="form-text x-small">Or provide a link below</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">External Link</label>
                        <input type="text" name="link" class="form-control" placeholder="https://...">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-toggle="modal">Cancel</button>
                    <button type="submit" class="btn btn-solace px-4">Upload Resource</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
