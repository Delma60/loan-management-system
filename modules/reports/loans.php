<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
$pdo = getDatabaseConnection();

$loans = $pdo->query(
    'SELECT l.Loan_ID, c.Full_Name AS customer_name, l.Loan_Type, l.Loan_Amount, l.Interest_Rate, l.Duration,
            l.Approval_Status, l.Loan_Date,
            COALESCE(SUM(r.Amount_Paid), 0) AS total_paid
     FROM loan l
     JOIN customer c ON l.Customer_ID = c.Customer_ID
     LEFT JOIN repayment r ON r.Loan_ID = l.Loan_ID
     GROUP BY l.Loan_ID
     ORDER BY l.Loan_Date DESC'
)->fetchAll();

$pageTitle = 'Loan Report';
require_once __DIR__ . '/../../includes/header.php';
$reportTitle = 'Loan Report';
require __DIR__ . '/_print_head.php';
?>
<div class="page-head">
    <div>
        <h1>Loan Report</h1>
        <p class="page-sub">Loan application status and repayment progress.</p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-outline-secondary">Back to Reports</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print report</button>
    </div>
</div>
<?php if (count($loans) === 0): ?>
    <div class="empty-state"><p>No loans found.</p></div>
<?php else: ?>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Outstanding</th>
                        <th>Status</th>
                        <th>Applied</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($loan['customer_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($loan['Loan_Type']); ?></td>
                            <td class="text-end"><?php echo naira($loan['Loan_Amount']); ?></td>
                            <td class="text-end"><?php echo naira($loan['total_paid']); ?></td>
                            <td class="text-end"><?php echo naira(max(0, $loan['Loan_Amount'] - $loan['total_paid'])); ?></td>
                            <td><?php echo status_badge($loan['Approval_Status']); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($loan['Loan_Date']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php';
