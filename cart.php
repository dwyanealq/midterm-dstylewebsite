<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$stmt = db()->prepare('
    SELECT 
        ci.*, 
        p.product_name, 
        p.price, 
        p.discount_percent,
        p.image, 
        p.stock_qty 
    FROM cart_items ci 
    JOIN products p ON p.id = ci.product_id 
    WHERE ci.user_id = ? 
    ORDER BY ci.created_at DESC');$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();
$total = 0.0;
foreach ($items as $item) {
    $originalPrice = (float) $item['price'];
    $discountPercent = (float) ($item['discount_percent'] ?? 0);
    $salePrice = $originalPrice - ($originalPrice * $discountPercent / 100);

    $total += $salePrice * (int) $item['quantity'];
}

$pageTitle = 'Your Cart | D’STYLE Apparel';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading section-texture">
    <span class="section-label">YOUR PICKS</span>
    <h1>SHOPPING CART</h1>
</section>

<section class="account-page shop-section">
    <?php if (!$items): ?>
        <div class="empty-state">
            <h2>YOUR CART IS EMPTY.</h2>
            <p>Find a piece you love and add it to your cart.</p>
            <a class="btn filled" href="products.php">SHOP NOW</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-list">
                <?php foreach ($items as $item): ?>
                    <article class="cart-item">
                        <a href="product.php?id=<?= (int) $item['product_id'] ?>" class="cart-item-image">
                            <img src="<?= e($item['image']) ?>" alt="<?= e($item['product_name']) ?>">
                        </a>
                        <div class="cart-item-info">
                            <p class="product-category">D’STYLE FIND</p>
                            <h2><a href="product.php?id=<?= (int) $item['product_id'] ?>"><?= e($item['product_name']) ?></a></h2>
                            <p>Size: <?= e($item['size']) ?></p>
                            <?php
                                $originalPrice = (float) $item['price'];
                                $discountPercent = (float) ($item['discount_percent'] ?? 0);
                                $salePrice = $originalPrice - ($originalPrice * $discountPercent / 100); ?>

                            <?php if ($discountPercent > 0): ?>
                                <p class="product-price">
                                <span style="text-decoration: line-through; opacity: 0.55;">
                            <?= format_price($originalPrice) ?></span> <br>
                                <strong><?= format_price($salePrice) ?></strong> </p>
                            <?php else: ?>
                                <p class="product-price"> <?= format_price($originalPrice) ?> </p> <?php endif; ?>
                        </div>
                        <form class="cart-quantity" method="post" action="cart_action.php">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="cart_id" value="<?= (int) $item['id'] ?>">
                            <input type="number" name="quantity" min="1" max="<?= max(1, (int) $item['stock_qty']) ?>" value="<?= (int) $item['quantity'] ?>">
                            <button class="mini-btn" type="submit">UPDATE</button>
                        </form>
                            <div class="cart-item-total"> <?= format_price($salePrice * (int) $item['quantity']) ?> </div>                        <form method="post" action="cart_action.php">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="cart_id" value="<?= (int) $item['id'] ?>">
                            <button class="remove-btn" type="submit">REMOVE</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
            <aside class="cart-summary">
                <span class="section-label">ORDER SUMMARY</span>
                <h2>YOUR CART</h2>
                <div class="summary-row"><span>Items</span><span><?= count($items) ?></span></div>
                <div class="summary-row total"><span>Total</span><strong><?= format_price($total) ?></strong></div>
                <button class="btn filled full-width" type="button" onclick="showToast('Checkout can be connected next.')">CHECKOUT</button>
            </aside>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
