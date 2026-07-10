<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../config/db.php';

function getLoanSchedule(int $loanId): array
{
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT * FROM repayment_schedule WHERE Loan_ID = ? ORDER BY Installment_Number ASC');
    $stmt->execute([$loanId]);
    return $stmt->fetchAll();
}

function hasLoanSchedule(int $loanId): bool
{
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM repayment_schedule WHERE Loan_ID = ?');
    $stmt->execute([$loanId]);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Pure installment maths: split principal + flat interest across the term.
 * Every installment is the same base amount except the last, which absorbs
 * the rounding remainder so the parts sum exactly to the total repayable.
 *
 * Returns a list of ['number', 'amount', 'remaining'] rows.
 */
function calculateInstallments(float $loanAmount, float $interestRate, int $duration): array
{
    $duration = max(1, $duration);
    $totalRepayable = round($loanAmount * (1 + ($interestRate / 100)), 2);
    $baseInstallment = floor(($totalRepayable * 100) / $duration) / 100;

    $rows = [];
    $remainingBalance = $totalRepayable;

    for ($i = 1; $i <= $duration; $i++) {
        $installmentAmount = $i === $duration ? round($remainingBalance, 2) : $baseInstallment;
        $remainingBalance = round($remainingBalance - $installmentAmount, 2);

        $rows[] = [
            'number' => $i,
            'amount' => $installmentAmount,
            'remaining' => (float) max(0, $remainingBalance),
        ];
    }

    return $rows;
}

function generateLoanSchedule(int $loanId): bool
{
    if (hasLoanSchedule($loanId)) {
        return false;
    }

    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare('SELECT Loan_Amount, Interest_Rate, Duration, Loan_Date FROM loan WHERE Loan_ID = ? LIMIT 1');
    $stmt->execute([$loanId]);
    $loan = $stmt->fetch();

    if (!$loan) {
        return false;
    }

    $duration = max(1, (int) $loan['Duration']);
    $loanDate = $loan['Loan_Date'] ? new DateTime($loan['Loan_Date']) : new DateTime();
    $installments = calculateInstallments((float) $loan['Loan_Amount'], (float) $loan['Interest_Rate'], $duration);

    $stmt = $pdo->prepare(
        'INSERT INTO repayment_schedule (Loan_ID, Installment_Number, Due_Date, Installment_Amount, Remaining_Balance) VALUES (?, ?, ?, ?, ?)'
    );

    foreach ($installments as $row) {
        $dueDate = (clone $loanDate)->modify('+' . $row['number'] . ' months')->format('Y-m-d');
        $stmt->execute([
            $loanId,
            $row['number'],
            $dueDate,
            $row['amount'],
            $row['remaining'],
        ]);
    }

    return true;
}
