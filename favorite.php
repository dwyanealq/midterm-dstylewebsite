<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$productId = (int) ($_POST['product_id'] ?? 0);
$redirect = $_POST['redirect'] ?? 'products.php';

$check = db()->prepare('SELECT id FROM products WHERE id = ?');
$check->execute([$productId]);
if (!$check->fetch()) {
    flash('error', 'That product is no longer available.');
    redirect_to('products.php');
}

$exists = db()->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND product_id = ? LIMIT 1');
$exists->execute([$_SESSION['user_id'], $productId]);

if ($exists->fetchColumn()) {
    $stmt = db()->prepare('DELETE FROM favorites WHERE user_id = ? AND product_id = ?');
    $stmt->execute([$_SESSION['user_id'], $productId]);
} else {
    $stmt = db()->prepare('INSERT INTO favorites (user_id, product_id) VALUES (?, ?)');
    $stmt->execute([$_SESSION['user_id'], $productId]);
}

redirect_to($redirect);
