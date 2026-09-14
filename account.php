<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$pdo = db();
$userId = (int) $_SESSION['user_id'];
$errors = [];

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    redirect_to('logout.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    if ($firstName === '' || $lastName === '') {
        $errors[] = 'First and last name are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please use a valid email address.';
    }

    $emailCheck = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $emailCheck->execute([$email, $userId]);
    if ($emailCheck->fetch()) {
        $errors[] = 'That email is already in use.';
    }

    if ($newPassword !== '' && strlen($newPassword) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }

    if (!$errors) {
        if ($newPassword !== '') {
            $update = $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, password_hash = ? WHERE id = ?');
            $update->execute([$firstName, $lastName, $email, $phone, $address, password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
        } else {
            $update = $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?');
            $update->execute([$firstName, $lastName, $email, $phone, $address, $userId]);
        }

        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        flash('success', 'Your account details have been updated.');
        redirect_to('account.php');
    }

    $user = array_merge($user, compact('firstName', 'lastName', 'email', 'phone', 'address'));
}

$pageTitle = 'My Account | D’STYLE Apparel';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading section-texture">
    <span class="section-label">D’STYLE ACCOUNT</span>
    <h1>MY ACCOUNT</h1>
</section>
<section class="account-page">
    <div class="account-grid">
        <div class="account-card">
            <span class="section-label">PROFILE</span>
            <h2>EDIT YOUR DETAILS</h2>
            <?php if ($errors): ?><div class="form-error"><?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div><?php endif; ?>
            <form method="post" class="account-form form-grid">
                <label>First Name<input type="text" name="first_name" required value="<?= e($user['first_name'] ?? '') ?>"></label>
                <label>Last Name<input type="text" name="last_name" required value="<?= e($user['last_name'] ?? '') ?>"></label>
                <label>Email<input type="email" name="email" required value="<?= e($user['email'] ?? '') ?>"></label>
                <label>Phone<input type="text" name="phone" value="<?= e($user['phone'] ?? '') ?>"></label>
                <label class="full-width">Address<textarea name="address" rows="4"><?= e($user['address'] ?? '') ?></textarea></label>
                <label class="full-width">New Password <small>Leave blank to keep your current password.</small><input type="password" name="new_password" minlength="8"></label>
                <button class="btn filled form-submit full-width" type="submit">SAVE CHANGES</button>
            </form>
        </div>
        <aside class="account-card account-menu-card">
            <span class="section-label">QUICK LINKS</span>
            <h2>MY D’STYLE</h2>
            <a href="favorites.php">♡ FAVORITES</a>
            <a href="cart.php">🛒 CART</a>
            <a href="products.php">SHOP PRODUCTS</a>
            <?php if (is_admin()): ?><a href="admin/index.php">ADMIN PANEL</a><?php endif; ?>
            <a href="logout.php">LOG OUT</a>
        </aside>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
