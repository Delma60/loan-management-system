<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
$pdo = getDatabaseConnection();

// Loans disbursed per year (approved only) and applications received per year.
$loanByYear = $pdo->query(
    "SELECT YEAR(Loan_Date) AS yr,
            COUNT(*) AS applications,
            SUM(CASE WHEN Approval_Status = 'Approved' THEN 1 ELSE 0 END) AS approved,
            COALESCE(SUM(CASE WHEN Approval_Status = 'Approved' THEN Loan_Amount ELSE 0 END), 0) AS disbursed
     FROM loan
     GROUP BY yr"
)->fetchAll();

$repaymentByYear = $pdo->query(
    'SELECT YEAR(Payment_Date) AS yr, COALESCE(SUM(Amount_Paid), 0) AS collected
     FROM repayment
     GROUP BY yr'
)->fetchAll();

// Merge both series into a single year-keyed table.
$years = [];
foreach ($loanByYear as $row) {
    $y = (int) $row['yr'];
    $years[$y] = [
        'applications' => (int) $row['applications'],
        'approved' => (int) $row['approved'],
        'disbursed' => (float) $row['disbursed'],
        'collected' => 0.0,
    ];
}
foreach ($repaymentByYear as $row) {
    $y = (int) $row['yr'];
    if (!isset($years[$y])) {
        $years[$y] = ['applications' => 0, 'approved' => 0, 'disbursed' => 0.0, 'collected' => 0.0];
    }
    $years[$y]['collected'] = (float) $row['collected'];
}
krsort($years);

$totals = ['applications' => 0, 'approved' => 0, 'disbursed' => 0.0, 'collected' => 0.0];
foreach ($years as $data) {
    $totals['applications'] += $data['applications'];
    $totals['approved'] += $data['approved'];
    $totals['disbursed'] += $data['disbursed'];
    $totals['collected'] += $data['collected'];
}

$pageTitle = 'Annual Financial Report';
require_once __DIR__ . '/../../includes/header.php';
$reportTitle = 'Annual Financial Report';
require __DIR__ . '/_print_head.php';
?>
<div class="page-head">
    <div>
        <h1>Annual Financial Report</h1>
        <p class="page-sub">Year-by-year applications, disbursements, and collections.</p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-outline-secondary">Back to Reports</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print report</button>
    </div>
</div>
<?php if (count($years) === 0): ?>
    <div class="empty-state"><p>No loan or repayment data available yet.</p></div>
<?php else: ?>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th class="text-end">Applications</th>
                        <th class="text-end">Approved</th>
                        <th class="text-end">Amount Disbursed</th>
                        <th class="text-end">Amount Collected</th>
                        <th class="text-end">Net Position</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($years as $year => $data): ?>
                        <tr>
                            <td><strong><?php echo (int) $year; ?></strong></td>
                            <td class="text-end"><?php echo number_format($data['applications']); ?></td>
                            <td class="text-end"><?php echo number_format($data['approved']); ?></td>
                            <td class="text-end"><?php echo naira($data['disbursed']); ?></td>
                            <td class="text-end"><?php echo naira($data['collected']); ?></td>
                            <td class="text-end"><?php echo naira($data['collected'] - $data['disbursed']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>All years</th>
                        <th class="text-end"><?php echo number_format($totals['applications']); ?></th>
                        <th class="text-end"><?php echo number_format($totals['approved']); ?></th>
                        <th class="text-end"><?php echo naira($totals['disbursed']); ?></th>
                        <th class="text-end"><?php echo naira($totals['collected']); ?></th>
                        <th class="text-end"><?php echo naira($totals['collected'] - $totals['disbursed']); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php';
