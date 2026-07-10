<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers.php';

function ensureAuthenticated()
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/modules/login.php');
        exit;
    }
}

function getAuthenticatedAdmin()
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT Admin_ID, Username, Full_Name, Email FROM administrator WHERE Admin_ID = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

function loginAdmin($username, $password)
{
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT Admin_ID, Password FROM administrator WHERE Username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['Password'])) {
        // Prevent session fixation: issue a fresh session ID on login.
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['Admin_ID'];
        return true;
    }

    return false;
}

function logoutAdmin()
{
    session_unset();
    session_destroy();
}
