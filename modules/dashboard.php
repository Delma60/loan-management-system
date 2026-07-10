<?php
require_once __DIR__ . '/../includes/auth.php';
ensureAuthenticated();
$admin = getAuthenticatedAdmin();
$pdo = getDatabaseConnection();

$totalCustomers = (int) $pdo->query('SELECT COUNT(*) FROM customer')->fetchColumn();
$totalLoans = (int) $pdo->query('SELECT COUNT(*) FROM loan')->fetchColumn();
$approvedLoans = (int) $pdo->query("SELECT COUNT(*) FROM loan WHERE Approval_Status = 'Approved'")->fetchColumn();
$pendingLoans = (int) $pdo->query("SELECT COUNT(*) FROM loan WHERE Approval_Status = 'Pending'")->fetchColumn();
$rejectedLoans = (int) $pdo->query("SELECT COUNT(*) FROM loan WHERE Approval_Status = 'Rejected'")->fetchColumn();
$totalRepayments = (float) $pdo->query('SELECT COALESCE(SUM(Amount_Paid), 0) FROM repayment')->fetchColumn();

// Outstanding = principal of APPROVED loans minus what has been repaid on them.
// (Computed with two independent aggregates so repayment rows don't fan out and
//  double-count the loan principal, which the previous LEFT JOIN version did.)
$approvedPrincipal = (float) $pdo->query(
    "SELECT COALESCE(SUM(Loan_Amount), 0) FROM loan WHERE Approval_Status = 'Approved'"
)->fetchColumn();
$paidOnApproved = (float) $pdo->query(
    "SELECT COALESCE(SUM(r.Amount_Paid), 0)
     FROM repayment r
     JOIN loan l ON r.Loan_ID = l.Loan_ID
     WHERE l.Approval_Status = 'Approved'"
)->fetchColumn();
$outstandingBalance = max(0, $approvedPrincipal - $paidOnApproved);

$recentLoans = $pdo->query(
    'SELECT l.Loan_ID, l.Loan_Amount, l.Loan_Type, l.Approval_Status, l.Loan_Date, c.Full_Name
     FROM loan l
     JOIN customer c ON l.Customer_ID = c.Customer_ID
     ORDER BY l.Created_At DESC
     LIMIT 6'
)->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Welcome back, <?php echo htmlspecialchars(explode(' ', trim($admin['Full_Name'] ?? 'Administrator'))[0]); ?></h1>
        <p class="page-sub">Here's the current state of the loan portfolio.</p>
    </div>
    <div class="page-actions">
        <a href="loans/apply.php" class="btn btn-primary">New Loan Application</a>
    </div>
</div>

<div class="stat-grid mb-4">
    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Customers</span>
            <span class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </span>
        </div>
        <div class="stat-value"><?php echo number_format($totalCustomers); ?></div>
        <div class="stat-foot">Registered profiles</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Loan Applications</span>
            <span class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </span>
        </div>
        <div class="stat-value"><?php echo number_format($totalLoans); ?></div>
        <div class="stat-breakdown">
            <span>Approved <b><?php echo number_format($approvedLoans); ?></b></span>
            <span>Pending <b><?php echo number_format($pendingLoans); ?></b></span>
            <span>Rejected <b><?php echo number_format($rejectedLoans); ?></b></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Repayments Received</span>
            <span class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </span>
        </div>
        <div class="stat-value money">&#x20A6;<?php echo number_format($totalRepayments, 2); ?></div>
        <div class="stat-foot">Collected to date</div>
    </div>

    <div class="stat-card accent">
        <div class="stat-top">
            <span class="stat-label">Outstanding Balance</span>
            <span class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
        </div>
        <div class="stat-value money">&#x20A6;<?php echo number_format($outstandingBalance, 2); ?></div>
        <div class="stat-foot">Principal owed on approved loans</div>
    </div>
</div>

<div class="table-card">
    <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-2">
        <h2 class="card-title-sm mb-0">Recent Applications</h2>
        <a href="loans/list.php" class="btn btn-sm btn-outline-secondary">View all</a>
    </div>
    <?php if (count($recentLoans) === 0): ?>
        <div class="empty-state" style="border:0;">
            <p>No loan applications yet. Register a customer, then create their first application.</p>
            <a href="customers/register.php" class="btn btn-primary">Register Customer</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLoans as $loan): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($loan['Full_Name']); ?></td>
                            <td><?php echo htmlspecialchars($loan['Loan_Type']); ?></td>
                            <td class="text-end"><?php echo naira($loan['Loan_Amount']); ?></td>
                            <td><?php echo status_badge($loan['Approval_Status']); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($loan['Loan_Date']))); ?></td>
                            <td class="text-end">
                                <a href="loans/approve.php?id=<?php echo (int) $loan['Loan_ID']; ?>" class="btn btn-sm btn-outline-secondary">Review</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php';
