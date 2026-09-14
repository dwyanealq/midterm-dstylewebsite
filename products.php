<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = db();
$category = trim($_GET['category'] ?? '');
$query = trim($_GET['q'] ?? '');

$sql = 'SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE 1=1';
$params = [];

if ($category !== '') {
    $sql .= ' AND c.name = ?';
    $params[] = $category;
}

if ($query !== '') {
    $sql .= ' AND (p.product_name LIKE ? OR p.brand LIKE ? OR p.description LIKE ? OR c.name LIKE ?)';
    $term = '%' . $query . '%';
    array_push($params, $term, $term, $term, $term);
}

$sql .= ' ORDER BY p.featured DESC, p.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$favorites = [];
if (is_logged_in()) {
    $favStmt = $pdo->prepare('SELECT product_id FROM favorites WHERE user_id = ?');
    $favStmt->execute([$_SESSION['user_id']]);
    $favorites = array_map('intval', $favStmt->fetchAll(PDO::FETCH_COLUMN));
}

$categories = $pdo->query('SELECT name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
$pageTitle = 'Shop | D’STYLE Apparel';
require __DIR__ . '/includes/header.php';
?>
<section class="shop-header section-texture">
    <div>
        <span class="section-label">D’STYLE SHOP</span>
        <h1>FIND YOUR NEXT FAVORITE.</h1>
        <p>Curated pre-loved and branded pieces, selected one find at a time.</p>
    </div>
</section>

<section class="shop-section">
    <div class="shop-toolbar">
        <div class="filter-links">
            <a class="filter-link <?= $category === '' ? 'active' : '' ?>" href="products.php">ALL</a>
            <?php foreach ($categories as $item): ?>
                <a class="filter-link <?= strcasecmp($category, $item) === 0 ? 'active' : '' ?>" href="products.php?category=<?= urlencode($item) ?>"><?= e(strtoupper($item)) ?></a>
            <?php endforeach; ?>
        </div>
        <span><?= count($products) ?> item<?= count($products) === 1 ? '' : 's' ?></span>
    </div>

    <?php if (!$products): ?>
        <div class="empty-state">
            <h2>NO FINDS YET.</h2>
            <p>Try another search or category.</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <a class="product-image-wrap" href="product.php?id=<?= (int) $product['id'] ?>">
                        <img class="product-image" src="<?= e($product['image']) ?>" alt="<?= e($product['product_name']) ?>">
                        <?php if ((int) $product['stock_qty'] <= 0): ?><span class="sold-out-badge">SOLD OUT</span><?php elseif ((int) $product['featured'] === 1): ?><span class="featured-badge">FEATURED</span><?php endif; ?>
                    </a>
                    <div class="product-card-body">
                        <div>
                            <p class="product-category"><?= e($product['category_name']) ?></p>
                            <h2><a href="product.php?id=<?= (int) $product['id'] ?>"><?= e($product['product_name']) ?></a></h2>
                            <?php if ($product['brand']): ?><p class="product-brand"><?= e($product['brand']) ?></p><?php endif; ?>
                            <p class="product-price"><?= format_price((float) $product['price']) ?></p>
                        </div>
                        <div class="product-card-actions">
                            <form method="post" action="favorite.php">
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="redirect" value="products.php<?= $category ? '?category=' . urlencode($category) : '' ?>">
                                <button class="heart-btn <?= in_array((int) $product['id'], $favorites, true) ? 'active' : '' ?>" type="submit" aria-label="Favorite <?= e($product['product_name']) ?>">❤</button>
                            </form>
                            <a class="mini-btn" href="product.php?id=<?= (int) $product['id'] ?>">VIEW</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
