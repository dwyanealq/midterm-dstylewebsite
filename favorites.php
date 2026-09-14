<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$stmt = db()->prepare('SELECT p.*, c.name AS category_name FROM favorites f JOIN products p ON p.id = f.product_id JOIN categories c ON c.id = p.category_id WHERE f.user_id = ? ORDER BY f.created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll();

$pageTitle = 'Favorites | D’STYLE Apparel';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading section-texture">
    <span class="section-label">YOUR SAVED FINDS</span>
    <h1>FAVORITES</h1>
</section>
<section class="shop-section">
    <?php if (!$products): ?>
        <div class="empty-state">
            <h2>NOTHING SAVED YET.</h2>
            <p>Tap the heart on any product you want to keep around.</p>
            <a class="btn filled" href="products.php">BROWSE PRODUCTS</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <a class="product-image-wrap" href="product.php?id=<?= (int) $product['id'] ?>">
                        <img class="product-image" src="<?= e($product['image']) ?>" alt="<?= e($product['product_name']) ?>">
                    </a>
                    <div class="product-card-body">
                        <div>
                            <p class="product-category"><?= e($product['category_name']) ?></p>
                            <h2><a href="product.php?id=<?= (int) $product['id'] ?>"><?= e($product['product_name']) ?></a></h2>
                            <p class="product-price"><?= format_price((float) $product['price']) ?></p>
                        </div>
                        <form method="post" action="favorite.php">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="hidden" name="redirect" value="favorites.php">
                            <button class="heart-btn active" type="submit">♥</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
