<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h1 class="h4">Customer List</h1>
                <p class="text-muted">Customer list, search, and filter will be implemented here.</p>
                <a href="register.php" class="btn btn-primary">Register Customer</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php';
