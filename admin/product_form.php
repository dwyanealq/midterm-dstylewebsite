<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

/* Only administrators can add or edit products. */
require_admin();

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$errors = [];
$product = [
    'product_name' => '', 'brand' => '', 'description' => '', 'category_id' => '',
    'price' => '', 'cost_price' => '', 'discount_percent' => 0, 'stock_qty' => 0, 'sizes_json' => '[]',
    'image' => 'images/Products/.jpg', 'featured' => 0
];

/* Load an existing product when editing. */
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        redirect_to('index.php');
    }
    $product = $existing;
}

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

/* SAVE PRODUCT. */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $costPrice = (float) ($_POST['cost_price'] ?? 0);
    $discountPercent = (float) ($_POST['discount_percent'] ?? 0);
    $stock = max(0, (int) ($_POST['stock_qty'] ?? 0));
    $image = trim($_POST['image'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $sizesInput = trim($_POST['sizes'] ?? '');
    $sizes = array_values(array_filter(array_map('trim', preg_split('/[,\n]+/', $sizesInput))));

/* Basic product validation. */
    if ($name === '') $errors[] = 'Product name is required.';
    if ($categoryId <= 0) $errors[] = 'Select a category.';
    if ($price < 0 || $costPrice < 0) {
    $errors[] = 'Prices cannot be negative.'; }

    if ($discountPercent < 0 || $discountPercent > 100) {
    $errors[] = 'Discount must be between 0% and 100%.'; }    

    if ($image === '') $errors[] = 'Image path is required.';

/* Keep the user's prepared image paths inside images/. */
    if (!str_starts_with($image, 'images/')) $errors[] = 'Image path must begin with images/. This keeps your prepared image folder unchanged.';

    if (!$errors) {
        if ($id) {
    /* Update an existing product. */

            $stmt = $pdo->prepare('UPDATE products SET product_name = ?, brand = ?, description = ?, category_id = ?, price = ?, cost_price = ?, discount_percent = ?, stock_qty = ?, sizes_json = ?, image = ?, featured = ? WHERE id = ?');
            $stmt->execute([
                $name,
                $brand,
                $description,
                $categoryId,
                $price,
                $costPrice,
                $discountPercent,
                $stock,
                json_encode($sizes),
                $image,
                $featured,
                $id ]);
        } else {
            /* Insert a brand-new product. */
            $stmt = $pdo->prepare('INSERT INTO products (product_name, brand, description, category_id, price, cost_price, discount_percent, stock_qty, sizes_json, image, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

            $stmt->execute([
                $name,
                $brand,
                $description,
                $categoryId,
                $price,
                $costPrice,
                $discountPercent,
                $stock,
                json_encode($sizes),
                $image,
                $featured ]);
        }
        redirect_to('index.php');
    }
    /* Keep the submitted values visible if validation fails. */

    $product = array_merge($product, [
        'product_name' => $name,
        'brand' => $brand,
        'description' => $description,
        'category_id' => $categoryId,
        'price' => $price,
        'cost_price' => $costPrice,
        'discount_percent' => $discountPercent,
        'stock_qty' => $stock,
        'sizes_json' => json_encode($sizes),
        'image' => $image,
        'featured' => $featured,
    ]);
}

$pageTitle = ($id ? 'Edit Product' : 'Add Product') . ' | D’STYLE Apparel';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-heading section-texture">
    <span class="section-label">ADMIN PANEL</span>
    <h1><?= $id ? 'EDIT PRODUCT' : 'ADD PRODUCT' ?></h1>
</section>
<section class="admin-section">
    <div class="admin-card admin-form-card">
        <?php if ($errors): ?><div class="form-error"><?php foreach ($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div><?php endif; ?>
        <form method="post" class="account-form form-grid">
            <label>Product Name<input type="text" name="product_name" required value="<?= e($product['product_name']) ?>"></label>
            <label>Brand<input type="text" name="brand" value="<?= e($product['brand']) ?>"></label>
            <label>Category
                <select name="category_id" required>
                    <option value="">SELECT CATEGORY</option>
                    <?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $product['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label> Price <input type="number" name="price" step="0.01" min="0" required value="<?= e($product['price']) ?>"> </label>
            <label> Cost Price <input type="number" name="cost_price" step="0.01" min="0" value="<?= e($product['cost_price']) ?>"> </label>
            <label> Discount (%) <input type="number" name="discount_percent" step="0.01" min="0" max="100" value="<?= e($product['discount_percent'] ?? 0) ?>"> </label>
            <label> Quantity / Stock<input type="number" name="stock_qty" min="0" required value="<?= e($product['stock_qty']) ?>"></label>
            <label class="full-width">Sizes <small>Separate sizes with commas. Example: S, M, L, XL</small><input type="text" name="sizes" value="<?= e(implode(', ', product_sizes($product['sizes_json'])) ) ?>"></label>
            <label class="full-width">Image Path <small>Keep your prepared paths inside images/.</small><input type="text" name="image" required value="<?= e($product['image']) ?>"></label>
            <label class="full-width">Description<textarea name="description" rows="6"><?= e($product['description']) ?></textarea></label>
            <label class="checkbox-label full-width"><input type="checkbox" name="featured" <?= (int) $product['featured'] === 1 ? 'checked' : '' ?>> FEATURE THIS PRODUCT</label>
            <div class="form-button-row full-width">
                <button class="btn filled" type="submit">SAVE PRODUCT</button>
                <a class="btn outline" href="index.php">CANCEL</a>
            </div>
        </form>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
