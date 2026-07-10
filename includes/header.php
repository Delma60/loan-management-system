<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$currentAdmin = function_exists('getAuthenticatedAdmin') ? getAuthenticatedAdmin() : null;
$adminName = $currentAdmin['Full_Name'] ?? ($currentAdmin['Username'] ?? 'Administrator');

$currentScript = $_SERVER['SCRIPT_NAME'] ?? '';

$navItems = [
    [
        'label' => 'Dashboard',
        'href'  => BASE_URL . '/modules/dashboard.php',
        'match' => ['dashboard.php'],
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>',
    ],
    [
        'label' => 'Customers',
        'href'  => BASE_URL . '/modules/customers/list.php',
        'match' => ['/customers/'],
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    ],
    [
        'label' => 'Loans',
        'href'  => BASE_URL . '/modules/loans/list.php',
        'match' => ['/loans/'],
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>',
    ],
    [
        'label' => 'Repayments',
        'href'  => BASE_URL . '/modules/repayments/history.php',
        'match' => ['/repayments/'],
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
    ],
    [
        'label' => 'Reports',
        'href'  => BASE_URL . '/modules/reports/index.php',
        'match' => ['/reports/'],
        'icon'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    ],
];

$pageTitle = $pageTitle ?? '';
$topbarTitle = $pageTitle !== '' ? $pageTitle : APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(($pageTitle ? $pageTitle . ' · ' : '') . APP_NAME); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
<div class="app-shell" id="appShell">
    <aside class="app-sidebar" id="appSidebar">
        <div class="sidebar-brand">
            <span class="brand-mark">L</span>
            <span class="brand-text">
                <strong>LoanDesk</strong>
                <span>Loan Management</span>
            </span>
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($navItems as $item): ?>
                <?php
                $isActive = false;
                foreach ($item['match'] as $fragment) {
                    if (strpos($currentScript, $fragment) !== false) {
                        $isActive = true;
                        break;
                    }
                }
                ?>
                <a class="sidebar-link<?php echo $isActive ? ' is-active' : ''; ?>" href="<?php echo $item['href']; ?>"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                    <?php echo $item['icon']; ?>
                    <span><?php echo htmlspecialchars($item['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-foot">
            <a class="sidebar-link" href="<?php echo BASE_URL; ?>/modules/logout.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Log out</span>
            </a>
        </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="app-main">
        <header class="app-topbar">
            <button class="topbar-toggle" id="sidebarToggle" type="button" aria-label="Toggle navigation">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <p class="topbar-title"><?php echo htmlspecialchars($topbarTitle); ?></p>
            <div class="topbar-spacer"></div>
            <div class="topbar-admin">
                <div class="admin-meta">
                    <strong><?php echo htmlspecialchars($adminName); ?></strong>
                    <span>Loan Officer</span>
                </div>
                <div class="admin-avatar"><?php echo htmlspecialchars(initials($adminName)); ?></div>
            </div>
        </header>
        <main class="app-content">
