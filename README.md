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

## Getting Started (reference stack)

1. Install XAMPP and start Apache + MySQL.
2. Clone/copy the project into `htdocs/loan-management-system`.
3. Create the database via phpMyAdmin and import the schema (see `/database` once created).
4. Update DB credentials in the config file.
5. Visit `http://localhost/loan-management-system` in your browser.

## Project Status

This repo now includes an initial PHP/MySQL skeleton for the Loan Management System, including:

- `public/` for entry pages and authenticated views
- `includes/` for shared config, DB, auth, and layout components
- `assets/` for CSS and JS
- `database/schema.sql` for table creation
- `database/seed.sql` for an initial admin account

The current skeleton supports login, dashboard, customers, loans, repayments, and reports placeholders.

## Out of Scope

Per the original scope (Chapter 1.8): core banking operations such as forex, investment banking, treasury management, internet banking, and mobile payment processing are **not** part of this system.