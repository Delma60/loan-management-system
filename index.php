<?php
require_once __DIR__ . '/includes/config.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: modules/dashboard.php');
} else {
    header('Location: modules/login.php');
}
exit;
