<?php
require_once __DIR__ . '/../includes/config.php';

function getDatabaseConnection()
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            error_log('Database connection failed: ' . $exception->getMessage());
            http_response_code(500);
            exit(APP_ENV === 'production'
                ? 'Service temporarily unavailable. Please try again later.'
                : 'Database connection failed: ' . $exception->getMessage());
        }
    }

    return $pdo;
}
