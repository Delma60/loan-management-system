<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();

$reports = [
    [
        'href' => 'customers.php',
        'title' => 'Customer Report',
        'desc' => 'Customer profiles and their loan exposure.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>',
    ],
    [
        'href' => 'loans.php',
        'title' => 'Loan Report',
        'desc' => 'Loan volume, repayment progress, and statuses.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
    ],
    [
        'href' => 'repayments.php',
        'title' => 'Repayment Report',
        'desc' => 'Repayment activity across all loans.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
    ],
    [
        'href' => 'defaults.php',
        'title' => 'Loan Default Report',
        'desc' => 'Approved loans with overdue installments.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    ],
    [
        'href' => 'monthly.php',
        'title' => 'Monthly Summary',
        'desc' => 'Monthly loan and repayment performance.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
    ],
    [
        'href' => 'annual.php',
        'title' => 'Annual Financial Report',
        'desc' => 'Year-by-year disbursement and collection totals.',
        'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    ],
];

$pageTitle = 'Reports';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Reports</h1>
        <p class="page-sub">Choose a report to view or print.</p>
    </div>
</div>
<div class="row g-3">
    <?php foreach ($reports as $report): ?>
        <div class="col-md-6 col-xl-4">
            <a href="<?php echo $report['href']; ?>" class="report-tile">
                <span class="tile-icon"><?php echo $report['icon']; ?></span>
                <h2><?php echo htmlspecialchars($report['title']); ?></h2>
                <p><?php echo htmlspecialchars($report['desc']); ?></p>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php';
