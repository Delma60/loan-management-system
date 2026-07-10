<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
$pdo = getDatabaseConnection();

/**
 * Total the customer must repay on a loan = principal + flat interest.
 * Kept consistent with the generated repayment schedule (see includes/loan.php).
 */
function loanTotalRepayable(array $loan): float
{
    return round((float) $loan['Loan_Amount'] * (1 + ((float) $loan['Interest_Rate'] / 100)), 2);
}

// Approved loans that still have an outstanding balance.
$approvedLoans = $pdo->query(
    "SELECT l.Loan_ID, l.Loan_Amount, l.Interest_Rate, c.Full_Name,
            COALESCE((SELECT SUM(Amount_Paid) FROM repayment WHERE Loan_ID = l.Loan_ID), 0) AS total_paid
     FROM loan l
     JOIN customer c ON l.Customer_ID = c.Customer_ID
     WHERE l.Approval_Status = 'Approved'
     ORDER BY c.Full_Name, l.Loan_ID DESC"
)->fetchAll();

$openLoans = [];
foreach ($approvedLoans as $loan) {
    $remaining = round(loanTotalRepayable($loan) - (float) $loan['total_paid'], 2);
    if ($remaining > 0.005) {
        $loan['remaining'] = $remaining;
        $openLoans[] = $loan;
    }
}

// Overdue accounts: scheduled amount due before today exceeds what has been paid.
$overdueLoans = $pdo->query(
    "SELECT l.Loan_ID, c.Full_Name,
            COALESCE((SELECT SUM(Installment_Amount) FROM repayment_schedule WHERE Loan_ID = l.Loan_ID AND Due_Date < CURDATE()), 0) AS due_to_date,
            COALESCE((SELECT SUM(Amount_Paid) FROM repayment WHERE Loan_ID = l.Loan_ID), 0) AS paid
     FROM loan l
     JOIN customer c ON l.Customer_ID = c.Customer_ID
     WHERE l.Approval_Status = 'Approved'
     HAVING paid < due_to_date
     ORDER BY (due_to_date - paid) DESC"
)->fetchAll();

$errors = [];
$success = '';
$input = [
    'loan_id' => '',
    'payment_date' => date('Y-m-d'),
    'amount_paid' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($input as $key => $_) {
        $input[$key] = trim($_POST[$key] ?? '');
    }

    if ($input['loan_id'] === '' || !ctype_digit($input['loan_id'])) {
        $errors['loan_id'] = 'Select a valid loan.';
    }
    if ($input['amount_paid'] === '' || !is_numeric($input['amount_paid']) || (float) $input['amount_paid'] <= 0) {
        $errors['amount_paid'] = 'Payment amount must be a positive number.';
    }
    if ($input['payment_date'] === '') {
        $errors['payment_date'] = 'Payment date is required.';
    }

    if (empty($errors)) {
        $loanId = (int) $input['loan_id'];
        $stmt = $pdo->prepare('SELECT Loan_Amount, Interest_Rate, Approval_Status FROM loan WHERE Loan_ID = ? LIMIT 1');
        $stmt->execute([$loanId]);
        $loan = $stmt->fetch();

        if (!$loan) {
            $errors['loan_id'] = 'Selected loan does not exist.';
        } elseif ($loan['Approval_Status'] !== 'Approved') {
            $errors['loan_id'] = 'Repayments can only be recorded for approved loans.';
        } else {
            $stmt = $pdo->prepare('SELECT COALESCE(SUM(Amount_Paid), 0) FROM repayment WHERE Loan_ID = ?');
            $stmt->execute([$loanId]);
            $totalPaid = (float) $stmt->fetchColumn();

            $totalRepayable = loanTotalRepayable($loan);
            $remaining = round($totalRepayable - $totalPaid, 2);
            $amountPaid = round((float) $input['amount_paid'], 2);

            if ($amountPaid > $remaining + 0.005) {
                $errors['amount_paid'] = 'Amount exceeds the outstanding balance of ' . naira_text($remaining) . '.';
            } else {
                $newBalance = round(max(0, $remaining - $amountPaid), 2);
                $status = $newBalance <= 0.005 ? 'Completed' : 'Pending';

                $stmt = $pdo->prepare('INSERT INTO repayment (Loan_ID, Payment_Date, Amount_Paid, Balance, Payment_Status) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$loanId, $input['payment_date'], $amountPaid, $newBalance, $status]);

                header('Location: history.php?recorded=1');
                exit;
            }
        }
    }
}

$err = static function (string $field) use ($errors): string {
    return isset($errors[$field]) ? ' is-invalid' : '';
};

$pageTitle = 'Record Repayment';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Record Repayment</h1>
        <p class="page-sub">Register a payment against an approved loan.</p>
    </div>
    <div class="page-actions">
        <a href="history.php" class="btn btn-outline-secondary">Repayment History</a>
    </div>
</div>

<?php if (!empty($overdueLoans)): ?>
    <div class="alert alert-info" role="status">
        <strong><?php echo count($overdueLoans); ?></strong> approved loan<?php echo count($overdueLoans) === 1 ? '' : 's'; ?> currently overdue:
        <?php
        $names = array_map(static function ($row) {
            return htmlspecialchars($row['Full_Name']) . ' (' . naira_text((float) $row['due_to_date'] - (float) $row['paid']) . ')';
        }, array_slice($overdueLoans, 0, 5));
        echo implode(', ', $names);
        echo count($overdueLoans) > 5 ? '&hellip;' : '';
        ?>
    </div>
<?php endif; ?>

<?php if (empty($openLoans)): ?>
    <div class="empty-state">
        <p>There are no approved loans with an outstanding balance. Approve a loan or wait for new applications.</p>
        <a href="../loans/list.php" class="btn btn-primary">Go to Loans</a>
    </div>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">Please correct the highlighted fields below.</div>
    <?php endif; ?>
    <div class="card form-panel">
        <div class="card-body">
            <form method="post" novalidate>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="loan_id" class="form-label">Loan</label>
                        <select id="loan_id" name="loan_id" class="form-select<?php echo $err('loan_id'); ?>" required>
                            <option value="">Select an approved loan</option>
                            <?php foreach ($openLoans as $loan): ?>
                                <option value="<?php echo (int) $loan['Loan_ID']; ?>" <?php echo $input['loan_id'] === (string) $loan['Loan_ID'] ? 'selected' : ''; ?>>
                                    #<?php echo (int) $loan['Loan_ID']; ?> &middot; <?php echo htmlspecialchars($loan['Full_Name']); ?> &middot; balance <?php echo naira_text($loan['remaining']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['loan_id'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['loan_id']); ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="amount_paid" class="form-label">Amount Paid (&#x20A6;)</label>
                        <input type="number" id="amount_paid" name="amount_paid" class="form-control<?php echo $err('amount_paid'); ?>" min="0" step="0.01" value="<?php echo htmlspecialchars($input['amount_paid']); ?>" required>
                        <?php if (isset($errors['amount_paid'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['amount_paid']); ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date" id="payment_date" name="payment_date" class="form-control<?php echo $err('payment_date'); ?>" value="<?php echo htmlspecialchars($input['payment_date']); ?>" required>
                        <?php if (isset($errors['payment_date'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['payment_date']); ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Record Repayment</button>
                    <a href="history.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php';
