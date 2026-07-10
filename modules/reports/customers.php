<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
$pdo = getDatabaseConnection();

$customers = $pdo->query(
    'SELECT c.Customer_ID, c.Full_Name, c.Email, c.Phone, c.Occupation, c.Monthly_Income,
            COUNT(l.Loan_ID) AS loan_count,
            COALESCE(SUM(l.Loan_Amount), 0) AS total_loan_amount
     FROM customer c
     LEFT JOIN loan l ON l.Customer_ID = c.Customer_ID
     GROUP BY c.Customer_ID
     ORDER BY c.Full_Name ASC'
)->fetchAll();

$pageTitle = 'Customer Report';
require_once __DIR__ . '/../../includes/header.php';
$reportTitle = 'Customer Report';
require __DIR__ . '/_print_head.php';
?>
<div class="page-head">
    <div>
        <h1>Customer Report</h1>
        <p class="page-sub">Customer records and their loan exposure.</p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-outline-secondary">Back to Reports</a>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print report</button>
    </div>
</div>
<?php if (count($customers) === 0): ?>
    <div class="empty-state"><p>No customers found.</p></div>
<?php else: ?>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Occupation</th>
                        <th class="text-end">Income</th>
                        <th class="text-end">Loans</th>
                        <th class="text-end">Total Loan Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($customer['Full_Name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($customer['Email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['Phone']); ?></td>
                            <td><?php echo htmlspecialchars($customer['Occupation']); ?></td>
                            <td class="text-end"><?php echo naira($customer['Monthly_Income']); ?></td>
                            <td class="text-end"><?php echo (int) $customer['loan_count']; ?></td>
                            <td class="text-end"><?php echo naira($customer['total_loan_amount']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php';
