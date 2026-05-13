<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/resource_helper.php';

requireLogin();

// Initialize variables
$categories = [];
$resources = [];
$selected_category = $_GET['category'] ?? 'all';

try {
    // 1. Get unique categories
    $stmt = $pdo->query("SELECT DISTINCT category FROM resources ORDER BY category ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Build query for resources
    $has_file_path = column_exists($pdo, 'resources', 'file_path');
    $res_path_col = $has_file_path ? 'file_path' : 'link';
    
    $has_is_active = column_exists($pdo, 'resources', 'is_active');
    $where_clause = $has_is_active ? "WHERE is_active = 1" : "WHERE 1=1";
    
    $params = [];
    if ($selected_category !== 'all') {
        $where_clause .= " AND category = ?";
        $params[] = $selected_category;
    }

    $stmt = $pdo->prepare("SELECT id, title, category, description, $res_path_col as path FROM resources $where_clause ORDER BY created_at DESC");
    $stmt->execute($params);
    $resources = $stmt->fetchAll();

} catch (Throwable $e) {
    error_log("Resources Page Error: " . $e->getMessage());
    $error_info = "Note: Some resources could not be loaded.";
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-5 fade-in">
        <div class="col-md-8">
            <h2 class="page-title">Wellness Library</h2>
            <p class="page-subtitle">Curated content to support your mental and academic well-being.</p>
        </div>
        <div class="col-md-4">
            <form method="GET" action="" class="d-flex gap-2 pt-3">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars((string)$cat); ?>" <?php echo $selected_category == $cat ? 'selected' : ''; ?>>
                            <?php echo ucfirst(htmlspecialchars((string)$cat)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if (isset($error_info)): ?>
        <div class="alert alert-warning"><?php echo $error_info; ?></div>
    <?php endif; ?>

    <div class="row g-4 fade-in" style="animation-delay: 0.1s;">
        <?php if (count($resources) > 0): ?>
            <?php foreach ($resources as $res): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-body p-4">
                            <span class="badge bg-light text-primary mb-3 border border-primary-subtle">
                                <?php echo ucfirst(htmlspecialchars((string)($res['category'] ?? 'General'))); ?>
                            </span>
                            <h5 class="fw-bold mb-3"><?php echo htmlspecialchars((string)($res['title'] ?? 'Untitled Resource')); ?></h5>
                            <p class="text-muted small mb-4">
                                <?php 
                                    $description = trim((string)($res['description'] ?? ''));
                                    if ($description === '') {
                                        $description = getResourceFallbackDescription($res['category'] ?? 'other');
                                    }
                                    $shortDescription = strlen($description) > 120 
                                        ? substr($description, 0, 120) . '...' 
                                        : $description;
                                    echo htmlspecialchars($shortDescription); 
                                ?>
                            </p>
                            <?php 
                                $res_link = !empty($res['path']) ? base_url($res['path']) : '#';
                            ?>
                            <a href="<?php echo $res_link; ?>" target="_blank" class="btn btn-solace w-100">
                                View Resource
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="p-5 bg-light rounded-4">
                    <p class="text-muted mb-0">No resources found in this category. Check back later!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
