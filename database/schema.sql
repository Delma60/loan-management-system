-- Loan Management System database schema
CREATE DATABASE IF NOT EXISTS loan_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE loan_management_system;

CREATE TABLE IF NOT EXISTS administrator (
    Admin_ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Full_Name VARCHAR(150) NOT NULL,
    Email VARCHAR(150) NOT NULL UNIQUE,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS customer (
    Customer_ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Full_Name VARCHAR(200) NOT NULL,
    Gender ENUM('Male', 'Female', 'Other') NOT NULL,
    Date_of_Birth DATE NOT NULL,
    Address VARCHAR(500) NOT NULL,
    Phone VARCHAR(30) NOT NULL,
    Email VARCHAR(150) NOT NULL,
    Occupation VARCHAR(150) NOT NULL,
    Monthly_Income DECIMAL(12, 2) NOT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS loan (
    Loan_ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Customer_ID INT UNSIGNED NOT NULL,
    Loan_Amount DECIMAL(14, 2) NOT NULL,
    Loan_Type VARCHAR(100) NOT NULL,
    Purpose VARCHAR(255) NULL,
    Guarantor_Name VARCHAR(150) NULL,
    Guarantor_Phone VARCHAR(30) NULL,
    Document_Path VARCHAR(255) NULL,
    Interest_Rate DECIMAL(5, 2) NOT NULL,
    Duration INT UNSIGNED NOT NULL,
    Approval_Status ENUM(
        'Pending',
        'Approved',
        'Rejected',
        'On Hold'
    ) NOT NULL DEFAULT 'Pending',
    Loan_Date DATE NOT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Customer_ID) REFERENCES customer (Customer_ID) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS repayment (
    Payment_ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Loan_ID INT UNSIGNED NOT NULL,
    Payment_Date DATE NOT NULL,
    Amount_Paid DECIMAL(14, 2) NOT NULL,
    Balance DECIMAL(14, 2) NOT NULL,
    Payment_Status ENUM(
        'Pending',
        'Completed',
        'Overdue'
    ) NOT NULL DEFAULT 'Pending',
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Loan_ID) REFERENCES loan (Loan_ID) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS repayment_schedule (
    Schedule_ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Loan_ID INT UNSIGNED NOT NULL,
    Installment_Number INT UNSIGNED NOT NULL,
    Due_Date DATE NOT NULL,
    Installment_Amount DECIMAL(14, 2) NOT NULL,
    Remaining_Balance DECIMAL(14, 2) NOT NULL,
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Loan_ID) REFERENCES loan (Loan_ID) ON DELETE CASCADE,
    UNIQUE KEY ux_loan_installment (Loan_ID, Installment_Number)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS prediction (
    Prediction_ID INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Customer_ID INT UNSIGNED NOT NULL,
    Loan_ID INT UNSIGNED NOT NULL,
    Prediction_Result ENUM(
        'Eligible',
        'Not Eligible',
        'Review'
    ) NOT NULL,
    Prediction_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Customer_ID) REFERENCES customer (Customer_ID) ON DELETE CASCADE,
    FOREIGN KEY (Loan_ID) REFERENCES loan (Loan_ID) ON DELETE CASCADE
) ENGINE = InnoDB;