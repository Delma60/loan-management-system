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
- [ ] Login page (username + password form)
- [ ] Server-side auth check against `administrator` table
- [ ] Password hashing (bcrypt/password_hash, not plaintext)
- [ ] Session handling + logout
- [ ] Basic access control (redirect unauthenticated users)

## 3. Dashboard Module
- [ ] Layout/nav shared across authenticated pages
- [ ] Query + display: total customers, total loan applications, approved/pending/rejected counts, total repayments received, outstanding balances

## 4. Customer Registration Module
- [ ] "Add customer" form with validation (name, gender, DOB, address, phone, email, occupation, monthly income)
- [ ] Customer list view (search/filter)
- [ ] Edit/update customer record
- [ ] (Optional) delete/deactivate customer

## 5. Loan Application Module
- [ ] "New loan application" form (amount, type, purpose, duration, guarantor info, documents)
- [ ] Link application to existing customer record
- [ ] Server-side validation before saving
- [ ] Application list view with status

## 6. Loan Eligibility Prediction Module
- [ ] Define the decision rules/criteria (income, employment status, repayment history, requested amount, duration) — write these down explicitly since the spec says "predefined decision criteria" but doesn't fully enumerate them
- [ ] Implement prediction function (rule-based scoring, or simple ML model if going beyond the base spec)
- [ ] Store result in `prediction` table, linked to Customer_ID + Loan_ID
- [ ] Display prediction result to loan officer at review time (clearly labeled as decision support, not final say)

## 7. Loan Approval Module
- [ ] Review screen: application details + prediction result side by side
- [ ] Approve / Reject / Hold actions, update `Approval_Status`
- [ ] Auto-generate repayment schedule on approval (amount, interest rate, duration → installments)

## 8. Loan Repayment Module
- [ ] Record repayment form (loan, amount paid, date)
- [ ] Auto-update outstanding balance and payment status
- [ ] Overdue account detection/flagging
- [ ] Repayment history view per loan/customer

## 9. Report Module
- [ ] Customer report
- [ ] Loan report
- [ ] Repayment report
- [ ] Loan default report
- [ ] Monthly loan summary
- [ ] Annual financial report
- [ ] Printable/exportable view (PDF or print-friendly CSS)

## 10. Testing
- [ ] Write test cases per module (mirror Chapter 4.10 "Test Cases and Results" table)
- [ ] Manual QA pass: login, registration, application, prediction, approval, repayment, reports
- [ ] Edge cases: invalid input, duplicate customers, zero/negative loan amounts, overdue edge dates

## 11. Polish / Deployment
- [ ] Responsive check across screen sizes (Bootstrap breakpoints)
- [ ] Basic input sanitization / SQL injection prevention (prepared statements)
- [ ] Error/success messaging for all forms
- [ ] Deployment target decision (shared hosting, VPS, or keep local/XAMPP for demo)
- [ ] Write actual setup instructions once folder structure is final (replace placeholder in README)

## Later / Nice-to-have
- [ ] Role-based access (admin vs loan officer permissions)
- [ ] Email/SMS notifications for approval, repayment due, overdue
- [ ] Replace rule-based prediction with a trained ML model (per Chapter 1 framing, this is flagged as a possible future improvement / limitation)
- [ ] Audit log of admin actions
