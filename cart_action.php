<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$pdo = db();
$action = $_POST['action'] ?? '';
$userId = (int) $_SESSION['user_id'];

if ($action === 'add') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $size = trim($_POST['size'] ?? 'One Size');
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

    $stmt = $pdo->prepare('SELECT stock_qty FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$productId]);
    $stock = $stmt->fetchColumn();

    if ($stock === false || (int) $stock <= 0) {
        flash('error', 'That product is sold out.');
        redirect_to('products.php');
    }

    $existing = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ? AND size = ? LIMIT 1');
    $existing->execute([$userId, $productId, $size]);
    $row = $existing->fetch();

    if ($row) {
        $newQuantity = min((int) $stock, (int) $row['quantity'] + $quantity);
        $update = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
        $update->execute([$newQuantity, $row['id']]);
    } else {
        $insert = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, size, quantity) VALUES (?, ?, ?, ?)');
        $insert->execute([$userId, $productId, $size, min((int) $stock, $quantity)]);
    }

    flash('success', 'Product added to your cart.');
    redirect_to('cart.php');
}

if ($action === 'update') {
    $cartId = (int) ($_POST['cart_id'] ?? 0);
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

    $stmt = $pdo->prepare('SELECT ci.id, p.stock_qty FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.id = ? AND ci.user_id = ?');
    $stmt->execute([$cartId, $userId]);
    $row = $stmt->fetch();

    if ($row) {
        $quantity = min($quantity, max(1, (int) $row['stock_qty']));
        $update = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?');
        $update->execute([$quantity, $cartId, $userId]);
    }

    redirect_to('cart.php');
}

if ($action === 'remove') {
    $cartId = (int) ($_POST['cart_id'] ?? 0);
    $delete = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND user_id = ?');
    $delete->execute([$cartId, $userId]);
    flash('success', 'Item removed from your cart.');
    redirect_to('cart.php');
}

redirect_to('cart.php');
