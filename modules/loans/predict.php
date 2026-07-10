<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/prediction.php';
ensureAuthenticated();

$loanId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($loanId <= 0) {
    header('Location: list.php');
    exit;
}

$loan = getLoanWithCustomer($loanId);
if (!$loan) {
    header('Location: list.php');
    exit;
}

$predictionInfo = computeLoanEligibility($loan);
$prediction = saveLoanPrediction($loanId, (int) $loan['Customer_ID'], $predictionInfo['result']);

$pageTitle = 'Eligibility Prediction';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Eligibility Prediction</h1>
        <p class="page-sub">Rule-based decision support for this application.</p>
    </div>
    <div class="page-actions">
        <a href="approve.php?id=<?php echo $loanId; ?>" class="btn btn-outline-secondary">Back to Review</a>
    </div>
</div>

<div class="card form-panel">
    <div class="card-body">
        <p class="text-muted mb-3">
            <?php echo htmlspecialchars($loan['Loan_Type']); ?> loan for
            <strong><?php echo htmlspecialchars($loan['customer_name']); ?></strong>
        </p>
        <div class="verdict">
            <?php echo status_badge($predictionInfo['result']); ?>
            <div class="verdict-score">
                Est. monthly payment
                <b><?php echo naira($predictionInfo['monthly_payment']); ?></b>
            </div>
        </div>
        <h3 class="card-title-sm">Reasoning</h3>
        <ul class="reason-list">
            <?php foreach ($predictionInfo['reasons'] as $reason): ?>
                <li><?php echo htmlspecialchars($reason); ?></li>
            <?php endforeach; ?>
        </ul>
        <p class="form-text mt-3 mb-0">This result has been saved to the loan's prediction history.</p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php';
