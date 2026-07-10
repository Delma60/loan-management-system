<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h1 class="h4">Record Repayment</h1>
                <p class="text-muted">This is the repayment recording module placeholder. The repayment form and payment status updates will appear here.</p>
                <a href="history.php" class="btn btn-outline-primary">Back to Repayment History</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php';
