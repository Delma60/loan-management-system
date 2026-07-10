<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
$pdo = getDatabaseConnection();

$search = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$params = [];
$sql = 'SELECT l.*, c.Full_Name AS customer_name FROM loan l JOIN customer c ON l.Customer_ID = c.Customer_ID';
$where = [];

if ($search !== '') {
    $where[] = '(c.Full_Name LIKE ? OR c.Email LIKE ? OR c.Phone LIKE ? OR l.Loan_Type LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if ($status !== '') {
    $where[] = 'l.Approval_Status = ?';
    $params[] = $status;
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY l.Created_At DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$loans = $stmt->fetchAll();

$flash = isset($_GET['created']) ? 'Loan application submitted successfully.' : '';

$pageTitle = 'Loans';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Loan Applications</h1>
        <p class="page-sub">Manage applications and review their approval status.</p>
    </div>
    <div class="page-actions">
        <a href="apply.php" class="btn btn-primary">New Loan Application</a>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-success" role="status" data-autodismiss><?php echo htmlspecialchars($flash); ?></div>
<?php endif; ?>

<div class="filter-bar mb-3">
    <form class="row g-2 align-items-center" method="get">
        <div class="col-sm-6 col-md-5">
            <input type="search" id="q" name="q" class="form-control" placeholder="Search customer, email, phone, or type" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-sm-4 col-md-3">
            <select id="status" name="status" class="form-select">
                <option value="">All statuses</option>
                <?php foreach (['Pending', 'Approved', 'Rejected', 'On Hold'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-2 col-md-2">
            <button type="submit" class="btn btn-outline-secondary w-100">Filter</button>
        </div>
        <?php if ($search !== '' || $status !== ''): ?>
            <div class="col-sm-2 col-md-2">
                <a href="list.php" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        <?php endif; ?>
    </form>
</div>

<?php if (count($loans) === 0): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <p><?php echo ($search !== '' || $status !== '') ? 'No applications match your filters.' : 'No loan applications yet. Create one to get started.'; ?></p>
        <?php if ($search === '' && $status === ''): ?>
            <a href="apply.php" class="btn btn-primary">New Loan Application</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Interest</th>
                        <th class="text-end">Duration</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($loan['customer_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($loan['Loan_Type']); ?></td>
                            <td class="text-end"><?php echo naira($loan['Loan_Amount']); ?></td>
                            <td class="text-end"><?php echo htmlspecialchars(rtrim(rtrim($loan['Interest_Rate'], '0'), '.')); ?>%</td>
                            <td class="text-end"><?php echo (int) $loan['Duration']; ?> mo</td>
                            <td><?php echo status_badge($loan['Approval_Status']); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($loan['Loan_Date']))); ?></td>
                            <td class="text-end">
                                <a href="approve.php?id=<?php echo (int) $loan['Loan_ID']; ?>" class="btn btn-sm btn-outline-secondary">Review</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php';
