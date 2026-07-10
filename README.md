# Loan Management System with Loan Eligibility Prediction Module

A web-based Loan Management System (LMS) for financial institutions (commercial banks, microfinance banks, cooperative societies) that automates customer registration, loan application, approval, repayment monitoring, reporting, and includes a rule-based loan eligibility prediction module.

Based on the project specification in `CHAPTER ONE - FOUR` (Introduction, Literature Review, Methodology, Implementation & Testing).

## Problem It Solves

Many small/medium financial institutions in Nigeria still rely on manual or partially computerized loan processing, which leads to:
- Misplaced/duplicated customer records
- Slow, error-prone loan approval decisions
- Poor repayment tracking and overdue monitoring
- Inconsistent, judgment-only eligibility assessment
- Weak reporting for management decision-making

This system replaces those manual processes with a centralized, database-backed web application, plus a prediction module that gives loan officers a data-informed second opinion (not a replacement for human judgment).

## Core Features / Modules

- **Login Module** — authenticated access for admins/loan officers
- **Dashboard Module** — overview: total customers, loans, approved/pending/rejected counts, repayments received, outstanding balances
- **Customer Registration Module** — capture and manage customer profiles (name, contact, occupation, income, etc.)
- **Loan Application Module** — capture loan amount, type, purpose, duration, guarantor info, documents
- **Loan Eligibility Prediction Module** — rule-based prediction (income, employment status, repayment history, requested amount, duration) producing Eligible / Not Eligible as a decision-support signal
- **Loan Approval Module** — approve / reject / hold applications, auto-generate repayment schedule on approval
- **Loan Repayment Module** — record payments, auto-update balance and status, flag overdue accounts
- **Report Module** — customer report, loan report, repayment report, default report, monthly/annual summaries

## Tech Stack (as specified in the project methodology)

| Layer | Technology |
|---|---|
| Structure | HTML |
| Styling / responsiveness | CSS, Bootstrap |
| Client-side interactivity | JavaScript (form validation, dynamic UI) |
| Server-side logic | PHP |
| Database | MySQL |
| Local dev environment | XAMPP (Apache + PHP + MySQL) |
| DB administration | phpMyAdmin |
| Editor | Visual Studio Code |

> Note: the source document specifies a classic PHP/MySQL/Bootstrap stack. If you'd rather build this as a modern SPA (e.g. React + a Node/PHP API), treat the stack above as the reference implementation and adapt — flag this decision explicitly if you deviate, since it changes hosting, auth, and deployment approach.

## Database Design

Five core tables (see Chapter 3.13 for full field lists):

- **Administrator** — Admin_ID, Username, Password (hashed), Full_Name, Email
- **Customer** — Customer_ID, Full_Name, Gender, Date_of_Birth, Address, Phone, Email, Occupation, Monthly_Income
- **Loan** — Loan_ID, Customer_ID (FK), Loan_Amount, Loan_Type, Interest_Rate, Duration, Approval_Status, Loan_Date
- **Repayment** — Payment_ID, Loan_ID (FK), Payment_Date, Amount_Paid, Balance, Payment_Status
- **Prediction** — Prediction_ID, Customer_ID (FK), Loan_ID (FK), Prediction_Result, Prediction_Date

## Project Structure

```text
loan-management-system/
├── index.php                 # entry point → redirects to login/dashboard
├── config/
│   └── db.php                # PDO connection (prepared statements only)
├── includes/
│   ├── config.php            # app + DB settings (env-overridable)
│   ├── auth.php              # login, session, access control
│   ├── helpers.php           # status badges, money formatting
│   ├── loan.php              # repayment-schedule generation
│   ├── prediction.php        # rule-based eligibility scoring
│   ├── header.php / footer.php   # shared sidebar + topbar layout
├── modules/
│   ├── login.php, logout.php, dashboard.php
│   ├── customers/            # register, list, edit
│   ├── loans/               # apply, list, approve (review), predict
│   ├── repayments/          # record, history
│   └── reports/             # customers, loans, repayments, defaults, monthly, annual
├── assets/                   # css/, js/, uploads/ (loan documents)
├── database/
│   ├── schema.sql            # table definitions (source of truth)
│   └── seed.sql              # initial admin account
└── tests/
    ├── run-tests.php         # pure-logic unit tests (no DB needed)
    └── test-cases.md         # manual QA + Chapter 4.10 test table
```

## Getting Started (local / XAMPP)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** + **MySQL**.
2. Copy the project into `htdocs/loan-management-system`.
3. Create the database and load the schema + seed (from the project root):
   ```bash
   # via the mysql client
   mysql -u root < database/schema.sql
   mysql -u root loan_management_system < database/seed.sql
   ```
   …or import both files through **phpMyAdmin** (`http://localhost/phpmyadmin`).
4. The default credentials in `includes/config.php` match a stock XAMPP install
   (`root`, no password). No edit needed for local use.
5. Visit `http://localhost/loan-management-system/`.

**Default login:** `admin` / `admin123` — change this immediately in any real
deployment.

### Running the tests

```bash
php tests/run-tests.php
```

Covers the rule-based prediction and repayment-schedule maths (no database
required). See `tests/test-cases.md` for the manual QA checklist.

## Deployment

**Decision: local XAMPP is the primary target** (project demo / defense), with a
clear path to shared hosting or a VPS for a live pilot.

Configuration reads environment variables first and falls back to XAMPP
defaults, so **no code changes or committed secrets** are needed to deploy —
set these on the host instead:

| Variable | Purpose | Default |
|---|---|---|
| `APP_ENV` | `production` hides error details and logs them | `development` |
| `DB_HOST` / `DB_NAME` / `DB_USER` / `DB_PASS` | database connection | XAMPP defaults |
| `BASE_URL` | URL path the app is served from | `/loan-management-system` |

For a production host: set `APP_ENV=production` (errors are logged, not shown),
serve over HTTPS (session cookies are then flagged `Secure`), point the document
root appropriately, and set a real DB password. `assets/uploads/` must remain
writable by the web server.

## Project Status

Fully implemented: authentication, dashboard, customer registration, loan
application, rule-based eligibility prediction, approval with auto-generated
repayment schedules, repayment recording with overdue flagging, and the full
report suite (customer, loan, repayment, default, monthly, annual) with a
print-friendly view.

## Out of Scope

Per the original scope (Chapter 1.8): core banking operations such as forex, investment banking, treasury management, internet banking, and mobile payment processing are **not** part of this system.