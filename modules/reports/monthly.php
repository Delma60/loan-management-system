<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
$pdo = getDatabaseConnection();

$loanSummary = $pdo->query(
    'SELECT DATE_FORMAT(Loan_Date, "%Y-%m") AS month, COUNT(*) AS applications, COALESCE(SUM(Loan_Amount), 0) AS loan_volume
     FROM loan
     GROUP BY month
     ORDER BY month DESC'
)->fetchAll();

$repaymentSummary = $pdo->query(
    'SELECT DATE_FORMAT(Payment_Date, "%Y-%m") AS month, COUNT(*) AS payments, COALESCE(SUM(Amount_Paid), 0) AS amount_collected
     FROM repayment
     GROUP BY month
     ORDER BY month DESC'
)->fetchAll();

$monthLabel = static function (string $ym): string {
    $dt = DateTime::createFromFormat('Y-m', $ym);
    return $dt ? $dt->format('M Y') : $ym;
};

$pageTitle = 'Monthly Summary';
require_once __DIR__ . '/../../includes/header.php';
$reportTitle = 'Monthly Summary';
require __DIR__ . '/_print_head.php';
?>
<div class="page-head">
    <div>
        <h1>Monthly Summary</h1>
        <p class="page-sub">Monthly loan applications and repayment performance.</p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-outline-secondary">Back to Reports</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print report</button>
    </div>
</div>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="table-card">
            <h2 class="card-title-sm px-3 pt-3 mb-2">Loan Applications by Month</h2>
            <?php if (count($loanSummary) === 0): ?>
                <p class="text-muted px-3 pb-3 mb-0">No loan application data available.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Applications</th>
                                <th class="text-end">Loan Volume</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loanSummary as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($monthLabel($row['month'])); ?></td>
                                    <td class="text-end"><?php echo (int) $row['applications']; ?></td>
                                    <td class="text-end"><?php echo naira($row['loan_volume']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="table-card">
            <h2 class="card-title-sm px-3 pt-3 mb-2">Repayment Collections by Month</h2>
            <?php if (count($repaymentSummary) === 0): ?>
                <p class="text-muted px-3 pb-3 mb-0">No repayment data available.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Payments</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($repaymentSummary as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($monthLabel($row['month'])); ?></td>
                                    <td class="text-end"><?php echo (int) $row['payments']; ?></td>
                                    <td class="text-end"><?php echo naira($row['amount_collected']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php';
