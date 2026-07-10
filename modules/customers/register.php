<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h1 class="h4">Register Customer</h1>
                <p class="text-muted">This is the customer registration module placeholder. The registration form will appear here.</p>
                <a href="list.php" class="btn btn-outline-primary">Back to Customer List</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php';
