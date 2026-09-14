<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product Not Found | D’STYLE Apparel';
    require __DIR__ . '/includes/header.php';
    echo '<section class="empty-state section-spaced"><h1>PRODUCT NOT FOUND.</h1><a class="btn filled" href="products.php">BACK TO SHOP</a></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$sizes = product_sizes($product['sizes_json']);
$isFavorite = false;
if (is_logged_in()) {
    $fav = db()->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND product_id = ? LIMIT 1');
    $fav->execute([$_SESSION['user_id'], $product['id']]);
    $isFavorite = (bool) $fav->fetchColumn();
}

$pageTitle = e($product['product_name']) . ' | D’STYLE Apparel';
require __DIR__ . '/includes/header.php';
?>
<section class="product-detail section-texture">
    <div class="product-detail-image">
        <img src="<?= e($product['image']) ?>" alt="<?= e($product['product_name']) ?>">
    </div>
    <div class="product-detail-info">
        <p class="product-category"><?= e($product['category_name']) ?></p>
        <h1><?= e($product['product_name']) ?></h1>
        <?php if ($product['brand']): ?><p class="product-brand large-brand"><?= e($product['brand']) ?></p><?php endif; ?>
        <p class="product-price detail-price"><?= format_price((float) $product['price']) ?></p>
        <p class="product-description"><?= nl2br(e($product['description'] ?? '')) ?></p>

        <?php if ((int) $product['stock_qty'] > 0): ?>
            <form class="add-cart-form" method="post" action="cart_action.php">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <?php if ($sizes): ?>
                    <label>SIZE
                        <select name="size" required>
                            <option value="">SELECT SIZE</option>
                            <?php foreach ($sizes as $size): ?><option value="<?= e($size) ?>"><?= e($size) ?></option><?php endforeach; ?>
                        </select>
                    </label>
                <?php else: ?>
                    <input type="hidden" name="size" value="One Size">
                <?php endif; ?>
                <label>QUANTITY
                    <input type="number" name="quantity" min="1" max="<?= max(1, (int) $product['stock_qty']) ?>" value="1">
                </label>
                <button class="btn filled" type="submit">ADD TO CART</button>
            </form>
        <?php else: ?>
            <div class="sold-out-message">THIS ITEM IS CURRENTLY SOLD OUT.</div>
        <?php endif; ?>

        <form method="post" action="favorite.php" class="detail-favorite-form">
            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
            <input type="hidden" name="redirect" value="product.php?id=<?= (int) $product['id'] ?>">
            <button class="btn outline" type="submit"><?= $isFavorite ? '♥ REMOVE FROM FAVORITES' : '♡ ADD TO FAVORITES' ?></button>
        </form>

        <div class="stock-note"><?= (int) $product['stock_qty'] ?> available</div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
