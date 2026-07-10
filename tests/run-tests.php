<?php
/**
 * Lightweight test runner for the pure business logic (no DB, no framework).
 * Run from the project root:  php tests/run-tests.php
 *
 * Covers the two pieces of logic that don't touch the database:
 *   - computeLoanEligibility()  (rule-based prediction)
 *   - calculateInstallments()   (repayment schedule maths)
 */

require_once __DIR__ . '/../includes/prediction.php';
require_once __DIR__ . '/../includes/loan.php';

$passed = 0;
$failed = 0;

function check(string $name, bool $condition): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  PASS  {$name}\n";
    } else {
        $failed++;
        echo "  FAIL  {$name}\n";
    }
}

function loanFixture(float $income, float $amount, int $duration, float $rate): array
{
    return [
        'Monthly_Income' => $income,
        'Loan_Amount' => $amount,
        'Duration' => $duration,
        'Interest_Rate' => $rate,
    ];
}

echo "Eligibility prediction\n";

$strong = computeLoanEligibility(loanFixture(500000, 500000, 12, 10));
check('strong applicant is Eligible', $strong['result'] === 'Eligible');
check('strong applicant monthly payment ~ 45,833.33', abs($strong['monthly_payment'] - 45833.33) < 0.5);

$weak = computeLoanEligibility(loanFixture(50000, 2000000, 36, 25));
check('weak applicant is Not Eligible', $weak['result'] === 'Not Eligible');

$borderline = computeLoanEligibility(loanFixture(200000, 2000000, 30, 12));
check('borderline applicant is Review', $borderline['result'] === 'Review');

check('every prediction returns reasons', is_array($strong['reasons']) && count($strong['reasons']) > 0);

echo "\nRepayment schedule maths\n";

// Zero-interest, evenly divisible: 12 x 100 = 1200
$flat = calculateInstallments(1200, 0, 12);
check('zero-interest produces 12 installments', count($flat) === 12);
check('zero-interest installments are all 100', array_sum(array_column($flat, 'amount')) === 1200.0);
check('final remaining balance is 0', end($flat)['remaining'] === 0.0);

// Interest + rounding: parts must still sum exactly to total repayable
$rounded = calculateInstallments(1000, 10, 3);
$total = round(array_sum(array_column($rounded, 'amount')), 2);
check('with interest, total repayable is 1100.00', $total === 1100.0);
check('last installment absorbs the rounding remainder', $rounded[2]['amount'] !== $rounded[0]['amount']);

// Guard: duration below 1 is treated as a single installment
$single = calculateInstallments(5000, 5, 0);
check('duration < 1 falls back to one installment', count($single) === 1);
check('single installment equals full repayable (5250.00)', $single[0]['amount'] === 5250.0);

echo "\n--------------------------------------\n";
echo "Passed: {$passed}   Failed: {$failed}\n";

exit($failed === 0 ? 0 : 1);
