<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../config/db.php';

function getLoanWithCustomer(int $loanId)
{
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare(
        'SELECT l.*, c.Full_Name AS customer_name, c.Monthly_Income, c.Email, c.Phone
         FROM loan l
         JOIN customer c ON l.Customer_ID = c.Customer_ID
         WHERE l.Loan_ID = ?'
    );
    $stmt->execute([$loanId]);
    return $stmt->fetch();
}

function getStoredLoanPrediction(int $loanId)
{
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT * FROM prediction WHERE Loan_ID = ? ORDER BY Prediction_Date DESC LIMIT 1');
    $stmt->execute([$loanId]);
    return $stmt->fetch();
}

function computeLoanEligibility(array $loan)
{
    $customerIncome = (float) $loan['Monthly_Income'];
    $loanAmount = (float) $loan['Loan_Amount'];
    $duration = max(1, (int) $loan['Duration']);
    $interestRate = (float) $loan['Interest_Rate'];

    $monthlyPayment = ($loanAmount * (1 + ($interestRate / 100))) / $duration;
    $score = 0;
    $reasons = [];

    if ($customerIncome >= $monthlyPayment * 3) {
        $score += 2;
        $reasons[] = 'Customer income is at least 3x the estimated monthly payment.';
    } else {
        $reasons[] = 'Customer income is lower than 3x the estimated monthly payment.';
    }

    if ($duration <= 24) {
        $score += 1;
        $reasons[] = 'Loan duration is 24 months or less.';
    } else {
        $reasons[] = 'Longer loan duration increases repayment risk.';
    }

    if ($loanAmount <= $customerIncome * 8) {
        $score += 1;
        $reasons[] = 'Loan amount is within eight months of income.';
    } else {
        $reasons[] = 'Loan amount exceeds eight months of income.';
    }

    if ($loanAmount > $customerIncome * 12) {
        $score -= 1;
        $reasons[] = 'Loan amount exceeds one year of income, which raises risk.';
    }

    if ($interestRate <= 15) {
        $score += 1;
        $reasons[] = 'Interest rate is acceptable for the current application.';
    } else {
        $reasons[] = 'Interest rate is high, increasing borrower cost.';
    }

    if ($loanAmount > 500000 && $customerIncome < 100000) {
        $score -= 1;
        $reasons[] = 'Large loan amount with low income creates a higher risk profile.';
    }

    if ($score >= 3) {
        $result = 'Eligible';
    } elseif ($score >= 1) {
        $result = 'Review';
    } else {
        $result = 'Not Eligible';
    }

    return [
        'result' => $result,
        'score' => $score,
        'reasons' => $reasons,
        'monthly_payment' => round($monthlyPayment, 2),
    ];
}

function saveLoanPrediction(int $loanId, int $customerId, string $predictionResult)
{
    $pdo = getDatabaseConnection();
    $existing = getStoredLoanPrediction($loanId);

    if ($existing) {
        if ($existing['Prediction_Result'] === $predictionResult) {
            return $existing;
        }
        $stmt = $pdo->prepare('UPDATE prediction SET Prediction_Result = ?, Prediction_Date = CURRENT_TIMESTAMP WHERE Prediction_ID = ?');
        $stmt->execute([$predictionResult, $existing['Prediction_ID']]);
        return getStoredLoanPrediction($loanId);
    }

    $stmt = $pdo->prepare('INSERT INTO prediction (Customer_ID, Loan_ID, Prediction_Result) VALUES (?, ?, ?)');
    $stmt->execute([$customerId, $loanId, $predictionResult]);
    return getStoredLoanPrediction($loanId);
}
