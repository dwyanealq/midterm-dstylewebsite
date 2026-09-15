<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

/* Only administrators can manage categories. */
require_admin();

$pdo = db();
$error = '';

/* =========================================================
   ADD / EDIT CATEGORY
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $action = $_POST['action'] ?? 'add';
    $id = (int) ($_POST['id'] ?? 0);

    if ($name === '') {
        $error = 'Category name is required.';
    } else {
        try {
            if ($action === 'edit') {
                $stmt = $pdo->prepare('UPDATE categories SET name = ? WHERE id = ?');
                $stmt->execute([$name, $id]);
                flash('success', 'Category updated.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
                $stmt->execute([$name]);
                flash('success', 'Category added.');
            }
            redirect_to('category.php');
        } catch (PDOException $exception) {
            $error = 'That category already exists or cannot be changed.';
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    try {
        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Category deleted.');
    } catch (PDOException $exception) {
        flash('error', 'Category cannot be deleted while products still use it.');
    }
    redirect_to('category.php');
}

$categories = $pdo->query('SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
$pageTitle = 'Categories | D’STYLE Admin';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-heading section-texture">
    <span class="section-label">ADMIN PANEL</span>
    <h1>MANAGE CATEGORIES</h1>
</section>
<section class="admin-section">
    <div class="admin-two-column">
        <div class="admin-card">
            <span class="section-label">ADD CATEGORY</span>
            <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>
            <form method="post" class="account-form">
                <input type="hidden" name="action" value="add">
                <label>Category Name<input type="text" name="name" required></label>
                <button class="btn filled" type="submit">ADD CATEGORY</button>
            </form>
        </div>
        <div class="admin-card">
            <span class="section-label">CURRENT CATEGORIES</span>
            <div class="category-admin-list">
                <?php foreach ($categories as $category): ?>
                    <div class="category-admin-row">
                        <form method="post" class="category-edit-form">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                            <input type="text" name="name" value="<?= e($category['name']) ?>" required>
                            <span><?= (int) $category['product_count'] ?> products</span>
                            <button class="mini-btn" type="submit">SAVE</button>
                        </form>
                        <a class="remove-btn" href="category.php?delete=<?= (int) $category['id'] ?>" onclick="return confirm('Delete this category?');">DELETE</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
