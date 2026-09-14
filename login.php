<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect_to(is_admin() ? 'admin/index.php' : 'account.php');
}

$error = '';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Please enter a valid email and password.';
    } else {
        $stmt = db()->prepare('SELECT id, first_name, last_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role'] = $user['role'];

            if ($redirect && !str_starts_with($redirect, 'http')) {
                redirect_to($redirect);
            }

            redirect_to($user['role'] === 'admin' ? 'admin/index.php' : 'account.php');
        }

        $error = 'Incorrect email or password.';
    }
}

$pageTitle = 'Log In | D’STYLE Apparel';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-section section-texture">
    <div class="auth-card">
        <span class="section-label">WELCOME BACK</span>
        <h1>LOG IN</h1>
        <p class="auth-intro">Access your D’STYLE account, favorites, and cart.</p>

        <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>

        <form method="post" class="account-form">
            <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
            <label>Email
                <input type="email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
            </label>
            <label>Password
                <input type="password" name="password" required autocomplete="current-password">
            </label>
            <button class="btn filled form-submit" type="submit">LOG IN</button>
        </form>

        <p class="auth-switch">New to D’STYLE? <a href="signup.php">CREATE AN ACCOUNT</a></p>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
