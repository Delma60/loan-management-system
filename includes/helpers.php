<?php
// Shared view helpers.

/**
 * Render a status pill for a loan/repayment/prediction state.
 * Colour is paired with the status text (never colour alone) per the a11y floor.
 */
function status_badge(string $status): string
{
    $map = [
        'Approved'     => 'badge-approved',
        'Completed'    => 'badge-approved',
        'Eligible'     => 'badge-approved',
        'Pending'      => 'badge-pending',
        'Review'       => 'badge-pending',
        'Rejected'     => 'badge-rejected',
        'Overdue'      => 'badge-rejected',
        'Not Eligible' => 'badge-rejected',
        'On Hold'      => 'badge-hold',
    ];

    $class = $map[$status] ?? 'badge-hold';

    return '<span class="status-badge ' . $class . '">' . htmlspecialchars($status) . '</span>';
}

/** Format a money value in Naira with two decimals and tabular alignment. */
function naira($amount): string
{
    return '<span class="money">&#x20A6;' . number_format((float) $amount, 2) . '</span>';
}

/** Plain (non-HTML) Naira string, e.g. for <option> labels. */
function naira_text($amount): string
{
    return "\u{20A6}" . number_format((float) $amount, 2);
}

/** Uppercase initials for the topbar avatar. */
function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $letters = '';
    foreach ($parts as $part) {
        if ($part !== '') {
            $letters .= strtoupper($part[0]);
        }
        if (strlen($letters) >= 2) {
            break;
        }
    }
    return $letters !== '' ? $letters : 'AD';
}
