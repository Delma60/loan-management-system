# TODO — Loan Management System

Build plan derived from the project spec (Chapters 1–4). Roughly ordered; check off as you go.

## 0. Setup
- [ ] Decide final stack: reference spec is PHP + MySQL + Bootstrap + JS (XAMPP). Confirm or consciously deviate (e.g. React frontend) before starting.
- [ ] Install XAMPP (or chosen stack equivalent)
- [ ] Init git repo, `.gitignore` (vendor/, node_modules/, config with DB creds)
- [ ] Create project folder structure (`/public`, `/includes` or `/src`, `/assets`, `/database`)

## 1. Database
- [ ] Create MySQL database `loan_management_system` (or similar) via phpMyAdmin
- [ ] Create `administrator` table (Admin_ID, Username, Password, Full_Name, Email)
- [ ] Create `customer` table (Customer_ID, Full_Name, Gender, Date_of_Birth, Address, Phone, Email, Occupation, Monthly_Income)
- [ ] Create `loan` table (Loan_ID, Customer_ID FK, Loan_Amount, Loan_Type, Interest_Rate, Duration, Approval_Status, Loan_Date)
- [ ] Create `repayment` table (Payment_ID, Loan_ID FK, Payment_Date, Amount_Paid, Balance, Payment_Status)
- [ ] Create `prediction` table (Prediction_ID, Customer_ID FK, Loan_ID FK, Prediction_Result, Prediction_Date)
- [ ] Add foreign key constraints + indexes on FK columns
- [ ] Export schema to `/database/schema.sql`
- [ ] Seed a test admin user (hashed password)

## 2. Auth / Login Module
- [x] Login page (username + password form)
- [x] Server-side auth check against `administrator` table
- [x] Password hashing (bcrypt/password_hash, not plaintext)
- [x] Session handling + logout (session regenerated on login to prevent fixation)
- [x] Basic access control (redirect unauthenticated users)

## 3. Dashboard Module
- [x] Layout/nav shared across authenticated pages (forest sidebar + topbar)
- [x] Query + display: total customers, total loan applications, approved/pending/rejected counts, total repayments received, outstanding balances (fixed double-count bug; added recent-activity table)

## 4. Customer Registration Module
- [x] "Add customer" form with validation (name, gender, DOB, address, phone, email, occupation, monthly income) — now inline per-field errors
- [x] Customer list view (search/filter)
- [x] Edit/update customer record
- [ ] (Optional) delete/deactivate customer

## 5. Loan Application Module
- [x] "New loan application" form (amount, type, purpose, duration, guarantor info, documents)
- [x] Link application to existing customer record
- [x] Server-side validation before saving
- [x] Application list view with status

## 6. Loan Eligibility Prediction Module
- [x] Define the decision rules/criteria (income, requested amount, duration, interest rate) — implemented in `computeLoanEligibility()`
- [x] Implement prediction function (rule-based scoring)
- [x] Store result in `prediction` table, linked to Customer_ID + Loan_ID
- [x] Display prediction result to loan officer at review time (labeled as decision support; underlying figures shown alongside the verdict)

## 7. Loan Approval Module
- [x] Review screen: application details + prediction result side by side
- [x] Approve / Reject / Hold actions, update `Approval_Status`
- [x] Auto-generate repayment schedule on approval (amount, interest rate, duration → installments)

## 8. Loan Repayment Module
- [x] Record repayment form (loan, amount paid, date) — restricted to approved loans, overpayment guarded
- [x] Auto-update outstanding balance and payment status
- [x] Overdue account detection/flagging (overdue banner on record page + Default Report)
- [x] Repayment history view per loan/customer

## 9. Report Module
- [x] Customer report
- [x] Loan report
- [x] Repayment report
- [x] Loan default report
- [x] Monthly loan summary
- [x] Annual financial report
- [x] Printable/exportable view (print-friendly CSS: hides chrome, adds report title block)

## 10. Testing
- [x] Write test cases per module (`tests/test-cases.md` mirrors Chapter 4.10; `tests/run-tests.php` covers the pure logic)
- [x] Manual QA pass: login, registration, application, prediction, approval, repayment, reports (smoke-tested end-to-end via HTTP)
- [x] Edge cases: invalid input, zero/negative loan amounts, overpayment, unapproved-loan repayment, overdue dates

## 11. Polish / Deployment
- [x] Responsive check across screen sizes (sidebar collapses to a drawer < 992px, stat grid reflows 4→2→1, tables scroll within their container)
- [x] Basic input sanitization / SQL injection prevention (all queries use PDO prepared statements; all output escaped with `htmlspecialchars`)
- [x] Error/success messaging for all forms (inline per-field errors + success flashes across customers/loans/repayments; error alert on login/approve)
- [x] Deployment target decision — local XAMPP primary (demo/defense) with env-var-driven path to shared hosting/VPS; documented in README
- [x] Write actual setup instructions (README rewritten: real folder structure, DB import, default login, tests, deployment table). Also: env-overridable config, production error hiding, hardened session cookies, `.gitignore` fixed to commit schema/seed and ignore uploads

## Later / Nice-to-have
- [ ] Role-based access (admin vs loan officer permissions)
- [ ] Email/SMS notifications for approval, repayment due, overdue
- [ ] Replace rule-based prediction with a trained ML model (per Chapter 1 framing, this is flagged as a possible future improvement / limitation)
- [ ] Audit log of admin actions
