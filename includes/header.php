<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(APP_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/modules/dashboard.php"><?php echo htmlspecialchars(APP_NAME); ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/modules/dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/modules/customers/list.php">Customers</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/modules/loans/list.php">Loans</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/modules/repayments/history.php">Repayments</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/modules/reports/index.php">Reports</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/modules/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">