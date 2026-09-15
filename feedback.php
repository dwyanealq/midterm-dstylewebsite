<?php

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = db();
$user = current_user();

$errors = [];

/*
|--------------------------------------------------------------------------
| ADMIN VIEW
|--------------------------------------------------------------------------
| If the logged-in user is an admin, show submitted feedback instead
| of the customer feedback form.
*/

$feedbacks = [];

if ($user && is_admin()) {
    $stmt = $pdo->query(
        'SELECT
            feedback.id,
            feedback.name,
            feedback.email,
            feedback.rating,
            feedback.message,
            feedback.created_at
         FROM feedback
         ORDER BY feedback.created_at DESC'
    );

    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| CUSTOMER FEEDBACK FORM
|--------------------------------------------------------------------------
*/

$name = '';
$email = '';
$message = '';
$rating = '';

if ($user && !is_admin()) {
    $stmt = $pdo->prepare(
        'SELECT first_name, last_name, email
         FROM users
         WHERE id = ?
         LIMIT 1'
    );

    $stmt->execute([$user['id']]);
    $account = $stmt->fetch();

    if ($account) {
        $name = trim($account['first_name'] . ' ' . $account['last_name']);
        $email = $account['email'];
    }
}

/*
|--------------------------------------------------------------------------
| SUBMIT FEEDBACK
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user && !is_admin()) {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $rating = trim($_POST['rating'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || strlen($name) > 200) {
        $errors[] = 'Please enter your name.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (
        $rating !== ''
        && !in_array($rating, ['1', '2', '3', '4', '5'], true)
    ) {
        $errors[] = 'Please select a valid rating.';
    }

    if ($message === '' || strlen($message) < 10) {
        $errors[] = 'Feedback must be at least 10 characters.';
    } elseif (strlen($message) > 5000) {
        $errors[] = 'Feedback is too long.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO feedback
                (user_id, name, email, rating, message)
             VALUES
                (?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $user['id'],
            $name,
            $email,
            $rating !== '' ? (int) $rating : null,
            $message,
        ]);

        flash('success', 'Thank you for your feedback!');
        redirect_to('feedback.php');
    }
}

$pageTitle = 'Feedback | D’STYLE Apparel';

require __DIR__ . '/includes/header.php';
?>

<?php if ($user && is_admin()): ?>

    <!-- =====================================================
         ADMIN FEEDBACK VIEW
         ===================================================== -->

    <section class="page-heading section-texture">
        <span class="section-label">D’STYLE ADMIN</span>
        <h1>FEEDBACK</h1>
        <p>View feedback submitted by D’STYLE customers.</p>
    </section>

    <section class="admin-section">
        <div class="admin-card">
            <div class="admin-card-heading">
                <div>
                    <span class="section-label">CUSTOMER RESPONSES</span>
                    <h2>Feedback Received</h2>
                </div>

                <div class="feedback-total">
                    <?= count($feedbacks) ?>
                    <span>Total</span>
                </div>
            </div>

            <?php if (!$feedbacks): ?>

                <div class="empty-state">
                    <h2>No feedback yet</h2>
                    <p>
                        Customer feedback will appear here once
                        someone submits the feedback form.
                    </p>
                </div>

            <?php else: ?>

                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Rating</th>
                                <th>Feedback</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($feedbacks as $feedback): ?>
                                <tr>
                                    <td>
                                        <strong><?= e($feedback['name']) ?></strong>
                                    </td>

                                    <td>
                                        <?= e($feedback['email']) ?>
                                    </td>

                                    <td>
                                        <?php if ($feedback['rating'] !== null): ?>
                                            <span class="feedback-stars">
                                                <?php
                                                $ratingValue = (int) $feedback['rating'];
                                                for ($i = 1; $i <= 5; $i++):
                                                ?>
                                                    <?= $i <= $ratingValue ? '★' : '☆' ?>
                                                <?php endfor; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="no-rating">No rating</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="feedback-message-cell">
                                        <?= nl2br(e($feedback['message'])) ?>
                                    </td>

                                    <td>
                                        <?= e(
                                            date(
                                                'M d, Y h:i A',
                                                strtotime($feedback['created_at'])
                                            )
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    </section>

<?php else: ?>

    <!-- =====================================================
         CUSTOMER FEEDBACK FORM
         ===================================================== -->

    <section class="page-heading section-texture">
        <span class="section-label">D’STYLE EXPERIENCE</span>
        <h1>FEEDBACK</h1>
        <p>Tell us what you think about your D’STYLE experience.</p>
    </section>

    <section class="auth-section">
        <div class="auth-card auth-card-wide">
            <span class="section-label">WE’D LOVE TO HEAR FROM YOU</span>
            <h2>SHARE YOUR THOUGHTS</h2>

            <p class="auth-intro">
                Your feedback helps us improve our products
                and shopping experience.
            </p>

            <?php if (!$user): ?>
                <div class="form-error">
                    Please log in before submitting feedback.
                </div>

                <p class="auth-switch">
                    <a href="login.php">LOGIN</a>
                    to leave your feedback.
                </p>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="form-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?= e($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($user): ?>
                <form method="post" class="account-form">
                    <label>
                        Name
                        <input
                            type="text"
                            name="name"
                            maxlength="200"
                            required
                            value="<?= e($name) ?>"
                        >
                    </label>

                    <label>
                        Email
                        <input
                            type="email"
                            name="email"
                            maxlength="190"
                            required
                            value="<?= e($email) ?>"
                        >
                    </label>

                    <label>
                        Rating
                        <select name="rating">
                            <option value="">SELECT A RATING</option>
                            <option value="5" <?= $rating === '5' ? 'selected' : '' ?>>
                                5 — Excellent
                            </option>
                            <option value="4" <?= $rating === '4' ? 'selected' : '' ?>>
                                4 — Good
                            </option>
                            <option value="3" <?= $rating === '3' ? 'selected' : '' ?>>
                                3 — Okay
                            </option>
                            <option value="2" <?= $rating === '2' ? 'selected' : '' ?>>
                                2 — Needs Improvement
                            </option>
                            <option value="1" <?= $rating === '1' ? 'selected' : '' ?>>
                                1 — Poor
                            </option>
                        </select>
                    </label>

                    <label>
                        Feedback
                        <textarea
                            name="message"
                            rows="8"
                            maxlength="5000"
                            required
                            placeholder="Tell us about your experience..."
                        ><?= e($message) ?></textarea>
                    </label>

                    <button class="btn filled" type="submit">
                        SEND FEEDBACK
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </section>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>