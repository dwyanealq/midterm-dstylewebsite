<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = db();
$featuredStmt = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.featured = 1 AND p.stock_qty > 0 ORDER BY p.updated_at DESC LIMIT 3');
$featuredProducts = $featuredStmt->fetchAll();

if (count($featuredProducts) < 3) {
    $fallbackStmt = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.stock_qty > 0 ORDER BY p.updated_at DESC LIMIT 3');
    $fallback = $fallbackStmt->fetchAll();
    $existingIds = array_column($featuredProducts, 'id');
    foreach ($fallback as $item) {
        if (!in_array($item['id'], $existingIds, true)) {
            $featuredProducts[] = $item;
        }
    }
}

$featuredProducts = array_slice($featuredProducts, 0, 3);
$productStripStmt = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id WHERE p.stock_qty > 0 ORDER BY p.created_at DESC LIMIT 8');
$productStrip = $productStripStmt->fetchAll();
$categories = $pdo->query('SELECT name FROM categories ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'D’STYLE Apparel';
require __DIR__ . '/includes/header.php';
?>
<!-- =====================================================
     HERO SECTION
     ===================================================== -->
<section class="hero section-texture reveal" id="home">
    <div class="hero-copy">
        <p class="eyebrow">D’STYLE APPAREL</p>
        <h1>Branded Finds.<br>Thrifted Prices</h1>
        <p class="hero-description">
            Discover quality pre-loved clothing, from everyday essentials to your favorite brands—all at prices you’ll love.
        </p>
        <div class="hero-buttons">
            <a class="btn filled" href="products.php">SHOP PRODUCTS</a>
            <a class="btn outline" href="#latest">LATEST</a>
        </div>
    </div>

    <div class="hero-gallery" aria-label="Featured clothing">
        <?php
        $cardClasses = ['card-left', 'card-center', 'card-right'];
        foreach ($cardClasses as $index => $cardClass):
            $product = $featuredProducts[$index] ?? null;
        ?>
            <?php if ($product): ?>
                <a href="product.php?id=<?= (int) $product['id'] ?>" class="hero-card <?= $cardClass ?>" aria-label="View <?= e($product['product_name']) ?>">
                    <span class="image-placeholder">
                        <img src="<?= e($product['image']) ?>" alt="<?= e($product['product_name']) ?>">
                    </span>
                </a>
            <?php else: ?>
                <a href="products.php" class="hero-card <?= $cardClass ?>" aria-label="Shop products">
                    <span class="image-placeholder">PRODUCT<br>IMAGE</span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- =====================================================
     PRODUCT STRIP
     ===================================================== -->
<section class="products" id="latest">
    <div class="prod-grid">
        <?php $productSlots = array_slice($productStrip, 0, 4); ?>
        <?php foreach ($productSlots as $index => $product): ?>
            <a class="prod-img <?= $index === 0 || $index === 3 ? 'tall' : '' ?> reveal" href="product.php?id=<?= (int) $product['id'] ?>" aria-label="View <?= e($product['product_name']) ?>">
                <img src="<?= e($product['image']) ?>" alt="<?= e($product['product_name']) ?>">
            </a>
            <?php if ($index === 1): ?>
                <div class="prod-copy reveal">all carefully<br>selected to<br>give you<br>stylish finds!</div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- =====================================================
     ABOUT US
     ===================================================== -->
<section class="about section-texture reveal" id="about">
    <div class="about-visual">
        <img src="images/MainLogo.png" style="width:600px;height:300px;">
    </div>

    <div class="about-copy">
        <h2>ABOUT US!</h2>
        <p>
            What started as a simple hobby of collecting clothes gradually grew into a passion for discovering unique, high-quality pieces. Over time, we began selectively sourcing second-hand and branded clothing, choosing pieces that stood out for their style, quality, and character. What was once a personal collection became an opportunity to share these carefully chosen finds with others.
        </p>
        <p>
            While many pieces are selected to be sold, some remain part of our personal collection because they hold a special value or simply deserve to be kept. Our goal is to offer a curated selection of pre-loved and branded clothing that gives each piece a second life while helping others find something they can truly make their own.
        </p>
    </div>
</section>

<!-- =====================================================
     WHY D'STYLE
     ===================================================== -->
<section class="why-dstyle reveal" id="why-dstyle">
    <div class="why-heading">
        <p class="eyebrow">THE D’STYLE EXPERIENCE</p>
        <h2>WHY D’STYLE?</h2>
        <p>Carefully chosen pieces, unique finds, and thrifted prices — giving pre-loved clothing a second life.</p>
    </div>

    <div class="why-grid">
        <div class="why-card"><div class="why-icon">✦</div><h3>CURATED FINDS</h3><p>Every piece is carefully selected for its style, quality, and character.</p></div>
        <div class="why-card"><div class="why-icon">♡</div><h3>PRE-LOVED QUALITY</h3><p>Discover clothing with character while giving great pieces a second life.</p></div>
        <div class="why-card"><div class="why-icon">★</div><h3>UNIQUE & BRANDED</h3><p>Find unique pieces and recognizable brands that stand out from the ordinary.</p></div>
        <div class="why-card"><div class="why-icon">₱</div><h3>THRIFTED PRICES</h3><p>Get stylish pieces you love without paying full retail prices.</p></div>
    </div>
</section>

<!-- =====================================================
     QUICK SHOP LINKS
     ===================================================== -->
<section class="switchers" id="popular-links">
    <a class="btn filled" href="products.php?sort=new">LATEST</a>
    <a class="btn filled" href="products.php?sort=sale">ON SALE</a>
</section>

<!-- =====================================================
     CATEGORY COVERFLOW
     ===================================================== -->
<section class="categories section-texture reveal" id="categories">
    <h2 class="section-title">CATEGORIES</h2>
    <div class="coverflow-wrap">
        <button class="flow-arrow prev" id="prevBtn" type="button" aria-label="Previous category">‹</button>
        <div class="coverflow" id="coverflow">
            <?php
            $categoryClasses = [
                'Men' => 'images/MainLogo.png',
                'Women' => 'placeholder-women',
                'Kids' => 'placeholder-kids',
                'Shoes' => 'placeholder-shoes',
                'Accessories' => 'placeholder-accessories'
            ];
            foreach ($categoryClasses as $name => $class):
            ?>
                <a class="category-card" data-category="<?= e($name) ?>" href="products.php?category=<?= urlencode($name) ?>">
                    <div class="category-image <?= e($class) ?>"></div>
                    <div class="category-label"><?= e(strtoupper($name)) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
        <button class="flow-arrow next" id="nextBtn" type="button" aria-label="Next category">›</button>
    </div>
    <div class="flow-controls">
        <span id="categoryCount">01 / 05</span>
        <span class="flow-hint">Click a card or use the arrows</span>
    </div>
</section>

<!-- =====================================================
     FIND D'STYLE / LOCATION
     ===================================================== -->
<section class="location-section reveal" id="location">
    <div class="location-header">
        <span class="section-label">FIND D’STYLE</span>
        <h2>COME FIND US.</h2>
        <p>Check out our location and come discover your next favorite find.</p>
    </div>

    <div class="location-card">
        <div class="location-info">
            <span class="location-number">01</span>
            <div>
                <h3>Chada Valencia</h3>
                <p>Valencia City, Bukidnon</p>
                <p class="location-address">Add your exact store address here</p>
            </div>
            <a href="https://www.google.com/maps/" target="_blank" rel="noopener noreferrer" class="map-link">VIEW ON GOOGLE MAPS <span>↗</span></a>
        </div>
        <div class="location-map">
            <iframe src="https://www.google.com/maps?q=Chada%20Valencia%20Bukidnon&output=embed" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade" title="D’STYLE location map"></iframe>
            <div class="map-overlay"><span>VIEW MAP</span></div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
