<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
$pdo = getDatabaseConnection();

$search = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM customer';
$params = [];

if ($search !== '') {
    $sql .= ' WHERE Full_Name LIKE ? OR Email LIKE ? OR Phone LIKE ? OR Occupation LIKE ?';
    $like = '%' . $search . '%';
    $params = [$like, $like, $like, $like];
}

$sql .= ' ORDER BY Created_At DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$flash = '';
if (isset($_GET['created'])) {
    $flash = 'Customer registered successfully.';
} elseif (isset($_GET['updated'])) {
    $flash = 'Customer record updated.';
}

$pageTitle = 'Customers';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Customers</h1>
        <p class="page-sub">Search and manage registered customer profiles.</p>
    </div>
    <div class="page-actions">
        <a href="register.php" class="btn btn-primary">Register Customer</a>
    </div>
</div>

<?php if ($flash): ?>
    <div class="alert alert-success" role="status" data-autodismiss>
        <?php echo htmlspecialchars($flash); ?>
    </div>
<?php endif; ?>

<div class="filter-bar mb-3">
    <form method="get" class="row g-2 align-items-center">
        <div class="col-sm-8 col-md-6">
            <input type="search" name="q" class="form-control" placeholder="Search by name, email, phone, or occupation" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-sm-4 col-md-2">
            <button type="submit" class="btn btn-outline-secondary w-100">Search</button>
        </div>
        <?php if ($search !== ''): ?>
            <div class="col-sm-4 col-md-2">
                <a href="list.php" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        <?php endif; ?>
    </form>
</div>

<?php if (count($customers) === 0): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <p><?php echo $search !== '' ? 'No customers match your search.' : 'No customers yet. Register the first customer to get started.'; ?></p>
        <?php if ($search === ''): ?>
            <a href="register.php" class="btn btn-primary">Register Customer</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Occupation</th>
                        <th class="text-end">Monthly Income</th>
                        <th>Registered</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($customer['Full_Name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($customer['Phone']); ?></td>
                            <td><?php echo htmlspecialchars($customer['Email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['Occupation']); ?></td>
                            <td class="text-end"><?php echo naira($customer['Monthly_Income']); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', strtotime($customer['Created_At']))); ?></td>
                            <td class="text-end">
                                <a href="edit.php?id=<?php echo (int) $customer['Customer_ID']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php';
