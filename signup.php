<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

/* Logged-in users already have an account, so send them to Account. */
if (is_logged_in()) {
    redirect_to('account.php');
}

$errors = [];

/* REGISTRATION PROCESS */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($firstName === '' || $lastName === '') {
        $errors[] = 'First and last name are required.';
    }

    if (mb_strlen($firstName) > 100 || mb_strlen($lastName) > 100) {
        $errors[] = 'Name is too long.';
    }

    if (!preg_match('/^[\p{L}\s\-]+$/u', $firstName)) {
        $errors[] = 'First name contains invalid characters.';
    }

    if (!preg_match('/^[\p{L}\s\-]+$/u', $lastName)) {
        $errors[] = 'Last name contains invalid characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    /* Apply all password security requirements. */
    $errors = array_merge($errors, validate_password_strength($password));

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $check = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $check->execute([$email]);

        if ($check->fetch()) {
            $errors[] = 'An account with that email already exists.';
        } else {
            $stmt = db()->prepare('INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, \'customer\')');
            $stmt->execute([$firstName, $lastName, $email, password_hash($password, PASSWORD_DEFAULT)]);

            flash('success', 'Your account has been created. Welcome to D’STYLE.');
            redirect_to('login.php');
        }
    }
}

$pageTitle = 'Sign Up | D’STYLE Apparel';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-section section-texture">
    <div class="auth-card auth-card-wide">
        <span class="section-label">JOIN D’STYLE</span>
        <h1>CREATE ACCOUNT</h1>
        <p class="auth-intro">Create an account to save favorites and build your cart.</p>

        <?php if ($errors): ?>
            <div class="form-error">
                <?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="account-form form-grid">
            <label>First Name
                <input type="text" name="first_name" required value="<?= e($_POST['first_name'] ?? '') ?>">
            </label>
            <label>Last Name
                <input type="text" name="last_name" required value="<?= e($_POST['last_name'] ?? '') ?>">
            </label>
            <label class="full-width">Email
                <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
            </label>
            <label>Password
                <input type="password" name="password" required minlength="8">
            </label>
            <label>Confirm Password
                <input type="password" name="confirm_password" required minlength="8">
            </label>
            <button class="btn filled form-submit full-width" type="submit">SIGN UP</button>
        </form>

        <p class="auth-switch">Already have an account? <a href="login.php">LOG IN</a></p>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
