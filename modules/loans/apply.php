<?php
require_once __DIR__ . '/../../includes/auth.php';
ensureAuthenticated();
$pdo = getDatabaseConnection();

$customers = $pdo->query('SELECT Customer_ID, Full_Name FROM customer ORDER BY Full_Name')->fetchAll();

$errors = [];
$input = [
    'customer_id' => '',
    'loan_amount' => '',
    'loan_type' => '',
    'purpose' => '',
    'guarantor_name' => '',
    'guarantor_phone' => '',
    'interest_rate' => '10.00',
    'duration' => '',
    'loan_date' => date('Y-m-d'),
];
$documentPath = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($input as $key => $_) {
        $input[$key] = trim($_POST[$key] ?? '');
    }

    if ($input['customer_id'] === '' || !ctype_digit($input['customer_id'])) {
        $errors['customer_id'] = 'Select a valid customer.';
    }
    if ($input['loan_amount'] === '' || !is_numeric($input['loan_amount']) || (float) $input['loan_amount'] <= 0) {
        $errors['loan_amount'] = 'Loan amount must be a positive number.';
    }
    if ($input['loan_type'] === '') {
        $errors['loan_type'] = 'Loan type is required.';
    }
    if ($input['purpose'] === '') {
        $errors['purpose'] = 'Loan purpose is required.';
    }
    if ($input['interest_rate'] === '' || !is_numeric($input['interest_rate']) || (float) $input['interest_rate'] < 0) {
        $errors['interest_rate'] = 'Interest rate must be a valid number.';
    }
    if ($input['duration'] === '' || !ctype_digit($input['duration']) || (int) $input['duration'] <= 0) {
        $errors['duration'] = 'Duration must be a positive whole number of months.';
    }
    if ($input['loan_date'] === '') {
        $errors['loan_date'] = 'Loan date is required.';
    }
    if ($input['guarantor_name'] === '') {
        $errors['guarantor_name'] = 'Guarantor name is required.';
    }
    if ($input['guarantor_phone'] === '') {
        $errors['guarantor_phone'] = 'Guarantor phone is required.';
    }

    if (isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $fileName = basename($_FILES['document']['name']);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $fileSize = $_FILES['document']['size'];

            if (!in_array($extension, $allowed, true)) {
                $errors['document'] = 'Document must be PDF, JPG, PNG, DOC or DOCX.';
            } elseif ($fileSize > 5 * 1024 * 1024) {
                $errors['document'] = 'Document must be smaller than 5MB.';
            }

            if (!isset($errors['document'])) {
                $uploadDir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $destinationName = uniqid('loan_doc_', true) . '.' . $extension;
                $destinationPath = $uploadDir . $destinationName;

                if (!move_uploaded_file($_FILES['document']['tmp_name'], $destinationPath)) {
                    $errors['document'] = 'Failed to upload document. Please try again.';
                } else {
                    $documentPath = 'assets/uploads/' . $destinationName;
                }
            }
        } else {
            $errors['document'] = 'Error uploading document. Please try again.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO loan (Customer_ID, Loan_Amount, Loan_Type, Purpose, Guarantor_Name, Guarantor_Phone, Document_Path, Interest_Rate, Duration, Loan_Date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $input['customer_id'],
            $input['loan_amount'],
            $input['loan_type'],
            $input['purpose'],
            $input['guarantor_name'],
            $input['guarantor_phone'],
            $documentPath,
            $input['interest_rate'],
            $input['duration'],
            $input['loan_date'],
        ]);

        header('Location: list.php?created=1');
        exit;
    }
}

$err = static function (string $field) use ($errors): string {
    return isset($errors[$field]) ? ' is-invalid' : '';
};

$pageTitle = 'New Loan Application';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>New Loan Application</h1>
        <p class="page-sub">Create a loan application for an existing customer.</p>
    </div>
    <div class="page-actions">
        <a href="list.php" class="btn btn-outline-secondary">Back to Loans</a>
    </div>
</div>

<?php if (count($customers) === 0): ?>
    <div class="alert alert-info" role="alert">
        You need at least one customer before creating a loan. <a href="../customers/register.php">Register a customer</a> first.
    </div>
<?php else: ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">Please correct the highlighted fields below.</div>
    <?php endif; ?>

    <div class="card form-panel">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" novalidate>
                <div class="row g-3">
                    <div class="col-12">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select id="customer_id" name="customer_id" class="form-select<?php echo $err('customer_id'); ?>" required>
                            <option value="">Select customer</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo (int) $customer['Customer_ID']; ?>" <?php echo $input['customer_id'] === (string) $customer['Customer_ID'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($customer['Full_Name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['customer_id'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['customer_id']); ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="loan_amount" class="form-label">Loan Amount (&#x20A6;)</label>
                        <input type="number" id="loan_amount" name="loan_amount" class="form-control<?php echo $err('loan_amount'); ?>" min="0" step="0.01" value="<?php echo htmlspecialchars($input['loan_amount']); ?>" required>
                        <?php if (isset($errors['loan_amount'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['loan_amount']); ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="loan_type" class="form-label">Loan Type</label>
                        <input type="text" id="loan_type" name="loan_type" class="form-control<?php echo $err('loan_type'); ?>" value="<?php echo htmlspecialchars($input['loan_type']); ?>" placeholder="e.g. Personal, Micro, Asset" required>
                        <?php if (isset($errors['loan_type'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['loan_type']); ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label for="purpose" class="form-label">Loan Purpose</label>
                        <textarea id="purpose" name="purpose" class="form-control<?php echo $err('purpose'); ?>" rows="3" required><?php echo htmlspecialchars($input['purpose']); ?></textarea>
                        <?php if (isset($errors['purpose'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['purpose']); ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="guarantor_name" class="form-label">Guarantor Name</label>
                        <input type="text" id="guarantor_name" name="guarantor_name" class="form-control<?php echo $err('guarantor_name'); ?>" value="<?php echo htmlspecialchars($input['guarantor_name']); ?>" required>
                        <?php if (isset($errors['guarantor_name'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['guarantor_name']); ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="guarantor_phone" class="form-label">Guarantor Phone</label>
                        <input type="text" id="guarantor_phone" name="guarantor_phone" class="form-control<?php echo $err('guarantor_phone'); ?>" value="<?php echo htmlspecialchars($input['guarantor_phone']); ?>" required>
                        <?php if (isset($errors['guarantor_phone'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['guarantor_phone']); ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label for="document" class="form-label">Supporting Document</label>
                        <input type="file" id="document" name="document" class="form-control<?php echo $err('document'); ?>">
                        <div class="form-text">Optional: PDF, JPG, PNG, DOC, or DOCX (max 5MB).</div>
                        <?php if (isset($errors['document'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['document']); ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label for="interest_rate" class="form-label">Interest Rate (%)</label>
                        <input type="number" id="interest_rate" name="interest_rate" class="form-control<?php echo $err('interest_rate'); ?>" min="0" step="0.01" value="<?php echo htmlspecialchars($input['interest_rate']); ?>" required>
                        <?php if (isset($errors['interest_rate'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['interest_rate']); ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label for="duration" class="form-label">Duration (months)</label>
                        <input type="number" id="duration" name="duration" class="form-control<?php echo $err('duration'); ?>" min="1" step="1" value="<?php echo htmlspecialchars($input['duration']); ?>" required>
                        <?php if (isset($errors['duration'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['duration']); ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <label for="loan_date" class="form-label">Loan Date</label>
                        <input type="date" id="loan_date" name="loan_date" class="form-control<?php echo $err('loan_date'); ?>" value="<?php echo htmlspecialchars($input['loan_date']); ?>" required>
                        <?php if (isset($errors['loan_date'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['loan_date']); ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                    <a href="list.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../includes/footer.php';
