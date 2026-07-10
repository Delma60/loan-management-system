<?php
// Application configuration.
//
// Settings fall back to XAMPP defaults for local demo use, but every value can
// be overridden with an environment variable so the same code deploys to shared
// hosting / a VPS without editing (or committing) credentials.

define('APP_ENV', getenv('APP_ENV') ?: 'development');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'loan_management_system');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

define('APP_NAME', 'Loan Management System');
define('BASE_URL', getenv('BASE_URL') ?: '/loan-management-system');

// Harden the session cookie before it is issued.
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $secure,
    ]);
    session_start();
}

// Show errors while developing; hide (but log) them in production.
if (APP_ENV === 'production') {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
