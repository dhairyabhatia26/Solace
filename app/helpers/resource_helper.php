<?php
// includes/resource_helper.php

/**
 * Get all supported resource categories
 */
function getResourceCategories() {
    return [
        "Stress Management",
        "Anxiety Support",
        "Emotional Wellbeing",
        "Mental Health Awareness",
        "Self-Care",
        "Sleep & Rest",
        "Peer Support",
        "Crisis & Safety",
        "Counselor Guides"
    ];
}

/**
 * Get resources filtered by category and/or audience
 */
function getResourcesByCategory($pdo, $category = null, $audience = null, $only_active = true) {
    $sql = "SELECT * FROM resources WHERE 1=1";
    $params = [];

    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    if ($audience) {
        if ($audience === 'both') {
            $sql .= " AND (audience = 'student' OR audience = 'counselor' OR audience = 'both')";
        } else {
            $sql .= " AND (audience = ? OR audience = 'both')";
            $params[] = $audience;
        }
    }

    if ($only_active) {
        $sql .= " AND is_active = 1";
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get all active resources for a specific audience
 */
function getActiveResources($pdo, $audience = 'student') {
    return getResourcesByCategory($pdo, null, $audience, true);
}

/**
 * Add a new resource record
 */
function addResource($pdo, $title, $category, $description, $file_path, $source_name, $source_url, $audience, $created_by) {
    $sql = "INSERT INTO resources (title, category, description, file_path, source_name, source_url, audience, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$title, $category, $description, $file_path, $source_name, $source_url, $audience, $created_by]);
}

/**
 * Update an existing resource record
 */
function updateResource($pdo, $id, $title, $category, $description, $file_path, $source_name, $source_url, $audience, $is_active) {
    $sql = "UPDATE resources SET 
            title = ?, 
            category = ?, 
            description = ?, 
            file_path = ?, 
            source_name = ?, 
            source_url = ?, 
            audience = ?, 
            is_active = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$title, $category, $description, $file_path, $source_name, $source_url, $audience, $is_active, $id]);
}

/**
 * Deactivate a resource
 */
function deactivateResource($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE resources SET is_active = 0 WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Get a single resource by ID
 */
function getResourceById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM resources WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}
/**
 * Get a fallback description based on category
 */
function getResourceFallbackDescription($category) {
    $category = strtolower(trim((string)$category));
    
    $fallbacks = [
        'anxiety support' => 'Practical guidance to understand and manage anxiety-related concerns.',
        'counselor guides' => 'Professional support material for counselors and student wellbeing teams.',
        'emotional wellbeing' => 'Helpful material to support emotional awareness and wellbeing.',
        'mental health awareness' => 'Educational material to build awareness around mental health and support.',
        'peer support' => 'Guidance to encourage supportive conversations and peer-led care.',
        'stress management' => 'Simple techniques and guidance to manage stress more effectively.',
        'academics' => 'Study and academic support material for students.',
        'career' => 'Career-readiness guidance for student growth and transitions.',
        'health' => 'Health and wellbeing information for everyday student life.',
        'relationships' => 'Communication and relationship support guidance.'
    ];

    return $fallbacks[$category] ?? 'A helpful wellness resource curated for student support.';
}
?>
