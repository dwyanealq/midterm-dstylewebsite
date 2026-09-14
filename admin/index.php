<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$pdo = db();
$products = $pdo->query('SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON c.id = p.category_id ORDER BY p.updated_at DESC')->fetchAll();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$userCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

$pageTitle = 'Admin Panel | D’STYLE Apparel';
require __DIR__ . '/../includes/header.php';
?>

<!-- 
admin user: admin@dstyleapp.com
pass: admin123 
-->


<section class="admin-header section-texture">
    <div>
        <span class="section-label">D’STYLE MANAGEMENT</span>
        <h1>ADMIN PANEL</h1>
        <p>Add products, update stock and sizes, and manage categories from one place.</p>
    </div>
    <div class="admin-actions">
        <a class="btn filled" href="product_form.php">ADD PRODUCT</a>
        <a class="btn outline" href="category.php">MANAGE CATEGORIES</a>
    </div>
</section>

<section class="admin-section">
    <div class="admin-stat-grid">
        <div class="admin-stat"><span>PRODUCTS</span><strong><?= count($products) ?></strong></div>
        <div class="admin-stat"><span>CATEGORIES</span><strong><?= count($categories) ?></strong></div>
        <div class="admin-stat"><span>CUSTOMERS</span><strong><?= $userCount ?></strong></div>
    </div>

    <div class="admin-card">
        <div class="admin-card-heading">
            <div>
                <span class="section-label">INVENTORY</span>
                <h2>PRODUCTS</h2>
            </div>
            <a class="mini-btn" href="product_form.php">+ NEW PRODUCT</a>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>PRODUCT</th>
                        <th>CATEGORY</th>
                        <th>PRICE</th>
                        <th>DISCOUNT</th>
                        <th>STOCK</th>
                        <th>SIZES</th>
                        <th>FEATURED</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <div class="admin-product-cell">
                                <img src="../<?= e($product['image']) ?>" alt="<?= e($product['product_name']) ?>">
                                <div><strong><?= e($product['product_name']) ?></strong><small><?= e($product['brand'] ?? '') ?></small></div>
                            </div>
                        </td>
                        <td><?= e($product['category_name']) ?></td>
                        <td><?= format_price((float) $product['price']) ?></td>
                        <td>
                            <?php if ((float) ($product['discount_percent'] ?? 0) > 0): ?>
                                <?= e($product['discount_percent']) ?>% OFF
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= (int) $product['stock_qty'] ?></td>
                        <td><?= e(implode(', ', product_sizes($product['sizes_json']))) ?></td>
                        <td><?= (int) $product['featured'] === 1 ? 'YES' : 'NO' ?></td>
                        <td class="table-actions">
                            <a href="product_form.php?id=<?= (int) $product['id'] ?>">EDIT</a>
                            <a href="delete_product.php?id=<?= (int) $product['id'] ?>" onclick="return confirm('Delete this product?');">DELETE</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
