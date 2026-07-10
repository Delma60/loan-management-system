<?php
require_once __DIR__ . '/../includes/auth.php';

// Already signed in? Skip the login screen.
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $errors[] = 'Username and password are required.';
    } elseif (loginAdmin($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $errors[] = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in &middot; <?php echo htmlspecialchars(APP_NAME); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-brand">
            <span class="brand-mark">L</span>
            <div>
                <strong>LoanDesk</strong>
                <span><?php echo htmlspecialchars(APP_NAME); ?></span>
            </div>
        </div>

        <h1>Sign in</h1>
        <p class="auth-sub">Enter your officer credentials to continue.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($errors[0]); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" autocomplete="username" value="<?php echo htmlspecialchars($username); ?>" autofocus required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign in</button>
        </form>

        <p class="auth-hint">Authorized personnel only. Contact your administrator for access.</p>
    </div>
</div>
</body>
</html>
