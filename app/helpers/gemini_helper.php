<?php
// includes/gemini_helper.php

require_once __DIR__ . '/../../config/gemini_config.php';

/**
 * Reusable function to call Google Gemini API with robust model fallback and retry logic.
 * 
 * @param string $prompt The user prompt to send to Gemini
 * @return array Clean response array [success, text, model_used, error, debug, models_tried]
 */
function callGeminiAPI($prompt) {
    // 1. Validate API Key
    if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY) || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
        return [
            "success" => false,
            "text" => null,
            "error" => "Gemini API key is not configured correctly. Please check config/gemini_config.php.",
            "debug" => "GEMINI_API_KEY is undefined or placeholder.",
            "models_tried" => []
        ];
    }

    // 2. Get fallback models
    $models = defined('GEMINI_MODELS') ? GEMINI_MODELS : ['gemini-1.5-flash'];
    $models_tried = [];

    foreach ($models as $model) {
        $models_tried[] = $model;
        $attempt = 1;
        $max_attempts = 2; // Retry once for temporary errors

        while ($attempt <= $max_attempts) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . GEMINI_API_KEY;

            $data = [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $prompt]
                        ]
                    ]
                ]
            ];

            $ch = curl_init($url);
            if (!$ch) {
                error_log("Gemini: curl_init failed for model $model");
                break; // Try next model
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
            // 3. Improve request timeout handling
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            // 5. Log and handle attempts
            if ($curl_error) {
                error_log("Gemini Attempt $attempt Failed (Model: $model): cURL Error: $curl_error");
                $attempt++;
                continue; // Retry same model if attempt < max_attempts
            }

            if ($http_code === 200) {
                $result = json_decode($response, true);
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    return [
                        "success" => true,
                        "text" => trim($result['candidates'][0]['content']['parts'][0]['text']),
                        "model_used" => $model
                    ];
                }
            }

            // 11. Add lightweight retry/fallback logic
            error_log("Gemini Attempt $attempt Failed (Model: $model): HTTP $http_code. Response: " . substr($response, 0, 200));

            // Non-recoverable errors: Stop immediately
            if ($http_code === 400 || $http_code === 401 || $http_code === 403) {
                return [
                    "success" => false,
                    "text" => null,
                    "error" => "AI service configuration error.",
                    "debug" => "HTTP $http_code: " . ($http_code == 401 ? "Invalid API Key" : "Bad Request/Forbidden"),
                    "models_tried" => $models_tried
                ];
            }

            // Recoverable errors:
            if ($http_code === 429 || $http_code === 404) {
                // Rate limit or Model not found - don't retry same model, move to next
                break; 
            }

            // Server errors (500, 503) or other issues - retry once
            $attempt++;
        }
    }

    // 4. All models failed
    return [
        "success" => false,
        "text" => null,
        "error" => "AI support is temporarily unavailable. Please try again in a few minutes.",
        "debug" => "All models failed or exhausted. Models tried: " . implode(', ', $models_tried),
        "models_tried" => $models_tried
    ];
}
?>
