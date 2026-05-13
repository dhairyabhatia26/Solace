<?php
// includes/ai_helper.php

require_once __DIR__ . '/gemini_helper.php';

/**
 * Prompt Guardrails & Safety Context
 */
define('AI_SAFETY_CONTEXT', "
IMPORTANT GUIDELINES:
- This is a wellness support platform, NOT a medical or clinical diagnostic system.
- Avoid providing any medical diagnosis or clinical treatment plans.
- Do NOT claim certainty about mental health conditions.
- Use supportive, non-judgmental, and encouraging language.
- Use terms like 'wellness concern', 'support request', 'concern pattern', and 'non-clinical guidance'.
- If a student mentions self-harm, immediate danger, or a severe crisis, provide a response that strongly encourages contacting professional emergency services or institutional crisis hotlines immediately.
");

function generateCounselorSummary($case_data) {
    $prompt = AI_SAFETY_CONTEXT . "\n\n";
    $prompt .= "You are an AI assistant helping a student wellness counselor. Create a concise, 4-6 line counselor-facing summary of the following student wellness concern. Use neutral language. Focus on the observed concern pattern.\n\n";
    $prompt .= "Title: " . ($case_data['title'] ?? '') . "\n";
    $prompt .= "Category: " . ($case_data['category'] ?? '') . "\n";
    $prompt .= "Urgency: " . ($case_data['urgency'] ?? '') . "\n";
    $prompt .= "Stress Score (1-10): " . ($case_data['stress_score'] ?? 'N/A') . "\n";
    $prompt .= "Sleep Score (1-10): " . ($case_data['sleep_score'] ?? 'N/A') . "\n";
    $prompt .= "Academic Pressure (1-10): " . ($case_data['academic_pressure_score'] ?? 'N/A') . "\n";
    $prompt .= "Description: " . ($case_data['description'] ?? '') . "\n";

    $response = callGeminiAPI($prompt);
    
    if ($response['success']) {
        return ['success' => $response['text']];
    } else {
        return ['error' => $response['error']];
    }
}

function generateGuidance($case_data) {
    $prompt = AI_SAFETY_CONTEXT . "\n\n";
    $prompt .= "You are an AI assistant helping a student wellness counselor. Based on the following wellness concern, provide supportive non-clinical guidance points. Include possible next conversation questions to ask the student and recommended coping/resource directions.\n\n";
    $prompt .= "Title: " . ($case_data['title'] ?? '') . "\n";
    $prompt .= "Category: " . ($case_data['category'] ?? '') . "\n";
    $prompt .= "Urgency: " . ($case_data['urgency'] ?? '') . "\n";
    $prompt .= "Description: " . ($case_data['description'] ?? '') . "\n";

    $response = callGeminiAPI($prompt);
    
    if ($response['success']) {
        return ['success' => $response['text']];
    } else {
        return ['error' => $response['error']];
    }
}

function generateRiskPattern($case_data) {
    $prompt = AI_SAFETY_CONTEXT . "\n\n";
    $prompt .= "Analyze the following student wellness concern description and scores. Classify the AI-indicated risk pattern into exactly one of the following words: low, moderate, high, critical.\n";
    $prompt .= "Do not include any other text in your response, just the single word.\n\n";
    $prompt .= "Urgency given by student: " . ($case_data['urgency'] ?? '') . "\n";
    $prompt .= "Stress Score (1-10): " . ($case_data['stress_score'] ?? 'N/A') . "\n";
    $prompt .= "Sleep Score (1-10): " . ($case_data['sleep_score'] ?? 'N/A') . "\n";
    $prompt .= "Academic Pressure (1-10): " . ($case_data['academic_pressure_score'] ?? 'N/A') . "\n";
    $prompt .= "Description: " . ($case_data['description'] ?? '') . "\n";

    $response = callGeminiAPI($prompt);
    
    if ($response['success']) {
        $pattern = strtolower(trim($response['text']));
        $pattern = str_replace(['.', ',', "\n"], '', $pattern);
        if (in_array($pattern, ['low', 'moderate', 'high', 'critical'])) {
            return ['success' => $pattern];
        }
        return ['success' => 'moderate'];
    }
    
    return ['error' => $response['error']];
}

function generateAdminInsights($aggregate_data) {
    $prompt = AI_SAFETY_CONTEXT . "\n\n";
    $prompt .= "You are an AI providing institutional wellness insights for a college HOD/Principal. Analyze the following aggregated platform data to generate a leadership-level monthly insight.\n\n";
    $prompt .= "Format the output strictly with exactly the following sections:\n";
    $prompt .= "  1. 4-6 bullet leadership summary\n";
    $prompt .= "  2. Key concern trends\n";
    $prompt .= "  3. Operational bottlenecks\n";
    $prompt .= "  4. Counselor workload observations\n";
    $prompt .= "  5. Suggested preventive initiatives\n";
    $prompt .= "  6. Follow-up actions for HOD/Principal\n\n";
    $prompt .= "DATA TO ANALYZE:\n";
    $prompt .= json_encode($aggregate_data, JSON_PRETTY_PRINT) . "\n";

    $response = callGeminiAPI($prompt);
    
    if ($response['success']) {
        return ['success' => $response['text']];
    } else {
        return ['error' => $response['error']];
    }
}
?>
