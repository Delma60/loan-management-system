<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
$pdo = getDatabaseConnection();

$sql = 'SELECT l.Loan_ID, c.Full_Name AS customer_name, l.Loan_Amount, l.Interest_Rate, l.Duration, l.Loan_Date,
        COALESCE((SELECT SUM(Amount_Paid) FROM repayment WHERE Loan_ID = l.Loan_ID), 0) AS total_paid,
        COALESCE((SELECT SUM(Installment_Amount) FROM repayment_schedule WHERE Loan_ID = l.Loan_ID AND Due_Date < CURRENT_DATE()), 0) AS due_amount,
        COALESCE((SELECT COUNT(*) FROM repayment_schedule WHERE Loan_ID = l.Loan_ID AND Due_Date < CURRENT_DATE()), 0) AS overdue_installments
    FROM loan l
    JOIN customer c ON l.Customer_ID = c.Customer_ID
    WHERE l.Approval_Status = ?
    HAVING total_paid < due_amount
    ORDER BY (due_amount - total_paid) DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute(['Approved']);
$defaults = $stmt->fetchAll();

$pageTitle = 'Loan Default Report';
require_once __DIR__ . '/../../includes/header.php';
$reportTitle = 'Loan Default Report';
require __DIR__ . '/_print_head.php';
?>
<div class="page-head">
    <div>
        <h1>Loan Default Report</h1>
        <p class="page-sub">Approved loans with overdue repayments.</p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-outline-secondary">Back to Reports</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print report</button>
    </div>
</div>
<?php if (count($defaults) === 0): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <p>No overdue loans are currently flagged. Everything is on track.</p>
    </div>
<?php else: ?>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th class="text-end">Loan Amount</th>
                        <th class="text-end">Due to Date</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Shortfall</th>
                        <th class="text-end">Overdue Inst.</th>
                        <th>Applied</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($defaults as $loan): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($loan['customer_name']); ?></strong></td>
                            <td class="text-end"><?php echo naira($loan['Loan_Amount']); ?></td>
                            <td class="text-end"><?php echo naira($loan['due_amount']); ?></td>
                            <td class="text-end"><?php echo naira($loan['total_paid']); ?></td>
                            <td class="text-end"><?php echo naira(max(0, $loan['due_amount'] - $loan['total_paid'])); ?></td>
                            <td class="text-end"><?php echo (int) $loan['overdue_installments']; ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($loan['Loan_Date']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php';
