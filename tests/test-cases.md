# Test Cases and Results

Mirrors Chapter 4.10 of the project spec. Automated coverage of the pure logic
lives in `tests/run-tests.php` (`php tests/run-tests.php`); the table below is the
manual QA script covering the full request/response flow.

## Automated (run-tests.php)

| # | Unit | Input | Expected | Status |
|---|------|-------|----------|--------|
| A1 | `computeLoanEligibility` | Income ₦500k, amount ₦500k, 12 mo, 10% | Result = Eligible | ✅ |
| A2 | `computeLoanEligibility` | Income ₦50k, amount ₦2m, 36 mo, 25% | Result = Not Eligible | ✅ |
| A3 | `computeLoanEligibility` | Income ₦200k, amount ₦2m, 30 mo, 12% | Result = Review | ✅ |
| A4 | `calculateInstallments` | ₦1,200, 0%, 12 mo | 12 × ₦100, final balance ₦0 | ✅ |
| A5 | `calculateInstallments` | ₦1,000, 10%, 3 mo | Parts sum to ₦1,100.00 exactly | ✅ |
| A6 | `calculateInstallments` | Duration 0 | Falls back to a single installment | ✅ |

## Manual QA

| # | Module | Steps | Expected Result | Status |
|---|--------|-------|-----------------|--------|
| 1 | Login | Submit blank form | "Username and password are required." | ☐ |
| 2 | Login | Wrong password | "Invalid username or password." | ☐ |
| 3 | Login | `admin` / `admin123` | Redirects to dashboard | ☐ |
| 4 | Access control | Open `/modules/dashboard.php` while logged out | Redirects to login | ☐ |
| 5 | Logout | Click "Log out" | Session cleared, back to login | ☐ |
| 6 | Dashboard | View after seeding data | Customer/loan counts and outstanding balance are correct (no double-counting) | ☐ |
| 7 | Customer | Register with invalid email | Inline error under Email field | ☐ |
| 8 | Customer | Register with future date of birth | "Date of birth must be in the past." | ☐ |
| 9 | Customer | Register valid customer | Success flash on list, row visible | ☐ |
| 10 | Customer | Edit and save | "Customer record updated." flash | ☐ |
| 11 | Customer | Search by partial name | Only matching rows shown | ☐ |
| 12 | Loan | Apply with amount = 0 | "Loan amount must be a positive number." | ☐ |
| 13 | Loan | Apply with 6 MB document | "Document must be smaller than 5MB." | ☐ |
| 14 | Loan | Apply valid | Appears in list as **Pending** | ☐ |
| 15 | Prediction | Open review for a loan | Verdict badge + reasoning + monthly payment shown | ☐ |
| 16 | Approval | Approve a pending loan | Status → Approved, repayment schedule generated | ☐ |
| 17 | Approval | Re-approve same loan | Schedule not duplicated | ☐ |
| 18 | Repayment | Try to record against a pending loan | Loan not offered / rejected as not approved | ☐ |
| 19 | Repayment | Pay more than the balance | "Amount exceeds the outstanding balance…" | ☐ |
| 20 | Repayment | Pay full balance | Payment status = Completed, balance ₦0 | ☐ |
| 21 | Overdue | Loan with past-due installment, underpaid | Appears in Default Report + overdue banner on Record page | ☐ |
| 22 | Reports | Print any report | Sidebar/topbar/actions hidden, title block shown | ☐ |
| 23 | Reports | Annual report | Yearly disbursed vs collected totals reconcile | ☐ |
| 24 | Responsive | Narrow the window < 992px | Sidebar collapses to a toggle drawer | ☐ |

## Edge cases checked

- Invalid input on every form (empty, non-numeric, out-of-range) → server-side rejection.
- Negative / zero loan amounts → rejected.
- Overpayment → rejected before insert.
- Repayments only accepted on approved loans.
- Outstanding balance uses independent aggregates (no repayment fan-out).
