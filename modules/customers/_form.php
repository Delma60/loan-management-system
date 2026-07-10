<?php
/**
 * Shared customer form partial.
 * Expects: $input (array), $errors (assoc field => message),
 *          $customerFormTitle, $customerFormSubtitle, $customerFormSubmit.
 */
$err = static function (string $field) use ($errors): string {
    return isset($errors[$field]) ? ' is-invalid' : '';
};
?>
<div class="page-head">
    <div>
        <h1><?php echo htmlspecialchars($customerFormTitle); ?></h1>
        <p class="page-sub"><?php echo htmlspecialchars($customerFormSubtitle); ?></p>
    </div>
    <div class="page-actions">
        <a href="list.php" class="btn btn-outline-secondary">Back to Customers</a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">Please correct the highlighted fields below.</div>
<?php endif; ?>

<div class="card form-panel">
    <div class="card-body">
        <form method="post" novalidate>
            <div class="row g-3">
                <div class="col-12">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control<?php echo $err('full_name'); ?>" value="<?php echo htmlspecialchars($input['full_name']); ?>" required>
                    <?php if (isset($errors['full_name'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['full_name']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="gender" class="form-label">Gender</label>
                    <select id="gender" name="gender" class="form-select<?php echo $err('gender'); ?>" required>
                        <option value="">Select gender</option>
                        <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
                            <option value="<?php echo $g; ?>" <?php echo $input['gender'] === $g ? 'selected' : ''; ?>><?php echo $g; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['gender'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['gender']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control<?php echo $err('date_of_birth'); ?>" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($input['date_of_birth']); ?>" required>
                    <?php if (isset($errors['date_of_birth'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['date_of_birth']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" class="form-control<?php echo $err('phone'); ?>" value="<?php echo htmlspecialchars($input['phone']); ?>" required>
                    <?php if (isset($errors['phone'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['phone']); ?></div><?php endif; ?>
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Address</label>
                    <textarea id="address" name="address" class="form-control<?php echo $err('address'); ?>" rows="3" required><?php echo htmlspecialchars($input['address']); ?></textarea>
                    <?php if (isset($errors['address'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['address']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control<?php echo $err('email'); ?>" value="<?php echo htmlspecialchars($input['email']); ?>" required>
                    <?php if (isset($errors['email'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['email']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="occupation" class="form-label">Occupation</label>
                    <input type="text" id="occupation" name="occupation" class="form-control<?php echo $err('occupation'); ?>" value="<?php echo htmlspecialchars($input['occupation']); ?>" required>
                    <?php if (isset($errors['occupation'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['occupation']); ?></div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="monthly_income" class="form-label">Monthly Income (&#x20A6;)</label>
                    <input type="number" id="monthly_income" name="monthly_income" class="form-control<?php echo $err('monthly_income'); ?>" min="0" step="0.01" value="<?php echo htmlspecialchars($input['monthly_income']); ?>" required>
                    <?php if (isset($errors['monthly_income'])): ?><div class="field-error"><?php echo htmlspecialchars($errors['monthly_income']); ?></div><?php endif; ?>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($customerFormSubmit); ?></button>
                <a href="list.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
