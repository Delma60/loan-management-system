<?php
require_once __DIR__ . '/../includes/auth.php';
ensureAuthenticated();
$admin = getAuthenticatedAdmin();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4">Dashboard</h1>
                <p class="mb-0">Welcome, <?php echo htmlspecialchars($admin['Full_Name'] ?? $admin['Username'] ?? 'Administrator'); ?>.</p>
            </div>
        </div>
    </div>
</div>
<div class="row g-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card text-bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Total Customers</h5>
                <p class="card-text display-6">0</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Loan Applications</h5>
                <p class="card-text display-6">0</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title">Approved Loans</h5>
                <p class="card-text display-6">0</p>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card text-bg-danger h-100">
            <div class="card-body">
                <h5 class="card-title">Outstanding Balance</h5>
                <p class="card-text display-6">0</p>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php';
