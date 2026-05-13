<?php
/**
 * Authentication Compatibility Wrapper
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../app/middleware/auth.php';
