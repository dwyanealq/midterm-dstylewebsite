<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$productId = (int) ($_POST['product_id'] ?? 0);
$redirect = $_POST['redirect'] ?? 'products.php';

/* Confirm that the product still exists before changing favorites. */
$check = db()->prepare('SELECT id FROM products WHERE id = ?');
$check->execute([$productId]);

if (!$check->fetch()) {
    flash('error', 'That product is no longer available.');
    redirect_to('products.php');
}

/* Check whether this product is already in the user's favorites. */
$exists = db()->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND product_id = ? LIMIT 1');
$exists->execute([$_SESSION['user_id'], $productId]);

if ($exists->fetchColumn()) {
    /* Remove it when the user clicks Favorite again. */
    $stmt = db()->prepare('DELETE FROM favorites WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$_SESSION['user_id'], $productId]);
} else {
    /* Add it when the user has not favorited it yet. */
    $stmt = db()->prepare('INSERT INTO favorites (user_id, product_id) VALUES (?, ?)');
    $stmt->execute([$_SESSION['user_id'], $productId]);
}

redirect_to($redirect);
