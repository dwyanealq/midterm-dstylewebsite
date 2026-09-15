<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'D’STYLE Apparel';
$rootPrefix = str_contains($_SERVER['PHP_SELF'] ?? '', '/admin/') ? '../' : '';
$flash = get_flash();
$user = current_user();
$cartCount = 0;
$favoritesCount = 0;

if ($user) {
    try {
        $pdo = db();
        $cartStmt = $pdo->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE user_id = ?');
        $cartStmt->execute([$user['id']]);
        $cartCount = (int) $cartStmt->fetchColumn();

        $favoriteStmt = $pdo->prepare('SELECT COUNT(*) FROM favorites WHERE user_id = ?');
        $favoriteStmt->execute([$user['id']]);
        $favoritesCount = (int) $favoriteStmt->fetchColumn();
    } catch (Throwable $exception) {
        $cartCount = 0;
        $favoritesCount = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="D'STYLE Apparel — branded finds and thrifted prices.">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= $rootPrefix ?>css/style.css">
</head>
<body>
<header class="site-header">
    <a href="<?= $rootPrefix ?>index.php" class="brand" aria-label="D'STYLE Apparel">
        <img src="<?= $rootPrefix ?>images/MainLogo.png" alt="Dstyle" style="width:160px;height:70px;">
    </a>

    <button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false">☰</button>

    <nav class="main-nav">
        <a href="<?= $rootPrefix ?>index.php">HOME</a>
        <div class="nav-dropdown">
            <button class="dropdown-toggle" type="button" aria-expanded="false">
                CATEGORIES <span class="dropdown-arrow"></span>
            </button>
            <div class="dropdown-menu">
                <a href="<?= $rootPrefix ?>products.php?category=Men">MEN</a>
                <a href="<?= $rootPrefix ?>products.php?category=Women">WOMEN</a>
                <a href="<?= $rootPrefix ?>products.php?category=Kids">KIDS</a>
                <a href="<?= $rootPrefix ?>products.php?category=Shoes">SHOES</a>
                <a href="<?= $rootPrefix ?>products.php?category=Accessories">ACCESSORIES</a>
            </div>
        </div>
        <a href="<?= $rootPrefix ?>index.php#about">ABOUT US</a>
        <a href="<?= $rootPrefix ?>index.php#location">FIND D’STYLE</a>
        <a href="<?= $rootPrefix ?>feedback.php">FEEDBACK</a>

    </nav>

    <div class="header-actions">
        <form class="search" id="searchForm" action="<?= $rootPrefix ?>products.php" method="get">
            <input id="searchInput" name="q" type="search" aria-label="Search products" placeholder="Search" value="<?= e($_GET['q'] ?? '') ?>">
        </form>
        <a class="icon-btn" href="<?= $rootPrefix ?>favorites.php" aria-label="Favorites">❤<span class="header-count"><?= $favoritesCount ?></span></a>
        <a class="icon-btn" href="<?= $rootPrefix ?>cart.php" aria-label="Shopping cart">🛒<span class="header-count"><?= $cartCount ?></span></a>
        <?php if ($user): ?>
            <a class="account-link" href="<?= is_admin() ? $rootPrefix . 'admin/index.php' : $rootPrefix . 'account.php' ?>">
                <?= e($user['name']) ?>
            </a>
            <a class="logout-link" href="<?= $rootPrefix ?>logout.php">LOG OUT</a>
        <?php else: ?>
            <a class="account-link" href="<?= $rootPrefix ?>login.php">LOG IN</a>
        <?php endif; ?>
    </div>
</header>

<?php if ($flash): ?>
<div class="site-flash <?= e($flash['type']) ?>"> <?= e($flash['message']) ?> </div>
<?php endif; ?>
<main>
