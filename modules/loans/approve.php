<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/prediction.php';
require_once __DIR__ . '/../../includes/loan.php';
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

$prediction = getStoredLoanPrediction($loanId);
$predictionInfo = computeLoanEligibility($loan);
if (!$prediction) {
    $prediction = saveLoanPrediction($loanId, (int) $loan['Customer_ID'], $predictionInfo['result']);
}

$errors = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $decision = $_POST['decision'] ?? '';
    if (!in_array($decision, ['Approved', 'Rejected', 'On Hold'], true)) {
        $errors[] = 'Select a valid decision.';
    }

    if (empty($errors)) {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('UPDATE loan SET Approval_Status = ? WHERE Loan_ID = ?');
        $stmt->execute([$decision, $loanId]);
        $loan['Approval_Status'] = $decision;

        if ($decision === 'Approved') {
            $message = generateLoanSchedule($loanId)
                ? 'Loan approved and repayment schedule generated.'
                : 'Loan approved.';
        } else {
            $message = 'Loan status updated to ' . $decision . '.';
        }
    }
}

$schedule = $loan['Approval_Status'] === 'Approved' ? getLoanSchedule($loanId) : [];
$scheduleTotal = 0.0;
foreach ($schedule as $installment) {
    $scheduleTotal += (float) $installment['Installment_Amount'];
}

$pageTitle = 'Loan Review';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Loan Review</h1>
        <p class="page-sub">Review the application and prediction, then record a decision.</p>
    </div>
    <div class="page-actions">
        <a href="list.php" class="btn btn-outline-secondary">Back to Loans</a>
        <a href="predict.php?id=<?php echo $loanId; ?>" class="btn btn-outline-secondary">Refresh Prediction</a>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success" role="status" data-autodismiss><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($errors[0]); ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title-sm">Application Details</h2>
                <dl class="row detail-list mb-0">
                    <dt class="col-sm-5">Customer</dt>
                    <dd class="col-sm-7"><?php echo htmlspecialchars($loan['customer_name']); ?></dd>

                    <dt class="col-sm-5">Email</dt>
                    <dd class="col-sm-7"><?php echo htmlspecialchars($loan['Email']); ?></dd>

                    <dt class="col-sm-5">Phone</dt>
                    <dd class="col-sm-7"><?php echo htmlspecialchars($loan['Phone']); ?></dd>

                    <dt class="col-sm-5">Monthly Income</dt>
                    <dd class="col-sm-7"><?php echo naira($loan['Monthly_Income']); ?></dd>

                    <dt class="col-sm-5">Loan Type</dt>
                    <dd class="col-sm-7"><?php echo htmlspecialchars($loan['Loan_Type']); ?></dd>

                    <dt class="col-sm-5">Purpose</dt>
                    <dd class="col-sm-7"><?php echo htmlspecialchars($loan['Purpose']); ?></dd>

                    <dt class="col-sm-5">Amount</dt>
                    <dd class="col-sm-7"><?php echo naira($loan['Loan_Amount']); ?></dd>

                    <dt class="col-sm-5">Duration</dt>
                    <dd class="col-sm-7"><?php echo (int) $loan['Duration']; ?> months</dd>

                    <dt class="col-sm-5">Interest Rate</dt>
                    <dd class="col-sm-7"><?php echo htmlspecialchars(rtrim(rtrim($loan['Interest_Rate'], '0'), '.')); ?>%</dd>

                    <dt class="col-sm-5">Guarantor</dt>
                    <dd class="col-sm-7"><?php echo htmlspecialchars($loan['Guarantor_Name']); ?> &middot; <?php echo htmlspecialchars($loan['Guarantor_Phone']); ?></dd>

                    <dt class="col-sm-5">Application Date</dt>
                    <dd class="col-sm-7"><?php echo htmlspecialchars(date('d M Y', strtotime($loan['Loan_Date']))); ?></dd>

                    <?php if (!empty($loan['Document_Path'])): ?>
                        <dt class="col-sm-5">Document</dt>
                        <dd class="col-sm-7"><a href="<?php echo htmlspecialchars(BASE_URL . '/' . $loan['Document_Path']); ?>" target="_blank" rel="noopener">View file</a></dd>
                    <?php endif; ?>

                    <dt class="col-sm-5">Current Status</dt>
                    <dd class="col-sm-7"><?php echo status_badge($loan['Approval_Status']); ?></dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="card-title-sm">Eligibility Prediction</h2>
                <div class="verdict">
                    <?php echo status_badge($predictionInfo['result']); ?>
                    <div class="verdict-score">
                        Est. monthly payment
                        <b><?php echo naira($predictionInfo['monthly_payment']); ?></b>
                    </div>
                </div>
                <ul class="reason-list">
                    <?php foreach ($predictionInfo['reasons'] as $reason): ?>
                        <li><?php echo htmlspecialchars($reason); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="form-text mt-3 mb-0">This is decision support, not a final approval &mdash; the underlying figures are shown above so you can judge for yourself.</p>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title-sm">Decision</h2>
                <form method="post">
                    <div class="mb-3">
                        <label for="decision" class="form-label">Set approval status</label>
                        <select id="decision" name="decision" class="form-select">
                            <?php
                            $decisionOptions = ['Approved' => 'Approve', 'On Hold' => 'Hold', 'Rejected' => 'Reject'];
                            foreach ($decisionOptions as $value => $label):
                                $sel = $loan['Approval_Status'] === $value ? 'selected' : '';
                            ?>
                                <option value="<?php echo $value; ?>" <?php echo $sel; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Save Decision</button>
                </form>
                <p class="form-text mt-3 mb-0">Approving a pending loan automatically generates its repayment schedule.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="card-title-sm">Repayment Schedule</h2>
                <?php if (!empty($schedule)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedule as $installment): ?>
                                    <tr>
                                        <td><?php echo (int) $installment['Installment_Number']; ?></td>
                                        <td><?php echo htmlspecialchars(date('d M Y', strtotime($installment['Due_Date']))); ?></td>
                                        <td class="text-end"><?php echo naira($installment['Installment_Amount']); ?></td>
                                        <td class="text-end"><?php echo naira($installment['Remaining_Balance']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Total repayable</th>
                                    <th class="text-end"><?php echo naira($scheduleTotal); ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php elseif ($loan['Approval_Status'] === 'Approved'): ?>
                    <p class="text-muted mb-0">Repayment schedule has not yet been generated for this loan.</p>
                <?php else: ?>
                    <p class="text-muted mb-0">The schedule is generated automatically when the loan is approved.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php';
