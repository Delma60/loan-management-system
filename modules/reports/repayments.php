<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
$pdo = getDatabaseConnection();

$search = trim($_GET['q'] ?? '');
$params = [];
$sql = 'SELECT r.*, l.Loan_Type, l.Loan_Amount, c.Full_Name
        FROM repayment r
        JOIN loan l ON r.Loan_ID = l.Loan_ID
        JOIN customer c ON l.Customer_ID = c.Customer_ID';

if ($search !== '') {
    $sql .= ' WHERE c.Full_Name LIKE ? OR l.Loan_Type LIKE ? OR r.Payment_Status LIKE ?';
    $like = '%' . $search . '%';
    $params = [$like, $like, $like];
}

$sql .= ' ORDER BY r.Payment_Date DESC, r.Payment_ID DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$repayments = $stmt->fetchAll();

$totalCollected = 0.0;
foreach ($repayments as $payment) {
    $totalCollected += (float) $payment['Amount_Paid'];
}

$pageTitle = 'Repayment Report';
require_once __DIR__ . '/../../includes/header.php';
$reportTitle = 'Repayment Report';
require __DIR__ . '/_print_head.php';
?>
<div class="page-head">
    <div>
        <h1>Repayment Report</h1>
        <p class="page-sub">Repayment activity across customers and loans.</p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-outline-secondary">Back to Reports</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print report</button>
    </div>
</div>
<div class="filter-bar mb-3">
    <form class="row g-2 align-items-center" method="get">
        <div class="col-sm-8 col-md-6">
            <input type="search" id="q" name="q" class="form-control" placeholder="Search customer, loan type, or status" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-sm-4 col-md-2">
            <button type="submit" class="btn btn-outline-secondary w-100">Search</button>
        </div>
    </form>
</div>
<?php if (count($repayments) === 0): ?>
    <div class="empty-state"><p>No repayment records found.</p></div>
<?php else: ?>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Loan Type</th>
                        <th class="text-end">Amount Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($repayments as $payment): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($payment['Full_Name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($payment['Loan_Type']); ?></td>
                            <td class="text-end"><?php echo naira($payment['Amount_Paid']); ?></td>
                            <td class="text-end"><?php echo naira($payment['Balance']); ?></td>
                            <td><?php echo status_badge($payment['Payment_Status']); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($payment['Payment_Date']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">Total collected</th>
                        <th class="text-end"><?php echo naira($totalCollected); ?></th>
                        <th colspan="3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php';
