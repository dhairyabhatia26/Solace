<?php
// Define Core Paths if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', BASE_PATH . '/config');
}

// Helper for Browser URLs
if (!function_exists('base_url')) {
    function base_url($path = '') {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

require_once __DIR__ . '/../app/helpers/functions.php';
