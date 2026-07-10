<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
$pdo = getDatabaseConnection();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: list.php');
    exit;
}

$errors = [];

$stmt = $pdo->prepare('SELECT * FROM customer WHERE Customer_ID = ? LIMIT 1');
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: list.php');
    exit;
}

$input = [
    'full_name' => $customer['Full_Name'],
    'gender' => $customer['Gender'],
    'date_of_birth' => $customer['Date_of_Birth'],
    'address' => $customer['Address'],
    'phone' => $customer['Phone'],
    'email' => $customer['Email'],
    'occupation' => $customer['Occupation'],
    'monthly_income' => $customer['Monthly_Income'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($input as $key => $_) {
        $input[$key] = trim($_POST[$key] ?? '');
    }

    if ($input['full_name'] === '') {
        $errors['full_name'] = 'Full name is required.';
    }
    if (!in_array($input['gender'], ['Male', 'Female', 'Other'], true)) {
        $errors['gender'] = 'Please select a gender.';
    }
    if ($input['date_of_birth'] === '') {
        $errors['date_of_birth'] = 'Date of birth is required.';
    } elseif ($input['date_of_birth'] >= date('Y-m-d')) {
        $errors['date_of_birth'] = 'Date of birth must be in the past.';
    }
    if ($input['address'] === '') {
        $errors['address'] = 'Address is required.';
    }
    if ($input['phone'] === '') {
        $errors['phone'] = 'Phone number is required.';
    }
    if ($input['email'] === '' || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email address is required.';
    }
    if ($input['occupation'] === '') {
        $errors['occupation'] = 'Occupation is required.';
    }
    if ($input['monthly_income'] === '' || !is_numeric($input['monthly_income']) || (float) $input['monthly_income'] < 0) {
        $errors['monthly_income'] = 'Monthly income must be a valid positive number.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE customer SET Full_Name = ?, Gender = ?, Date_of_Birth = ?, Address = ?, Phone = ?, Email = ?, Occupation = ?, Monthly_Income = ? WHERE Customer_ID = ?');
        $stmt->execute([
            $input['full_name'],
            $input['gender'],
            $input['date_of_birth'],
            $input['address'],
            $input['phone'],
            $input['email'],
            $input['occupation'],
            $input['monthly_income'],
            $id,
        ]);

        header('Location: list.php?updated=1');
        exit;
    }
}

$pageTitle = 'Edit Customer';
require_once __DIR__ . '/../../includes/header.php';
$customerFormTitle = 'Edit Customer';
$customerFormSubtitle = "Update this customer's profile and contact details.";
$customerFormSubmit = 'Update Customer';
require __DIR__ . '/_form.php';
require_once __DIR__ . '/../../includes/footer.php';
