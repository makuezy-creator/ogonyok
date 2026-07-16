<?php
/**
 * Страница каталога блюд ресторана «Огонёк».
 * Загружает категории и блюда из БД, рендерит карточки товаров.
 * Интерактивность (табы, поиск, корзина) — см. js/menu.js и js/cart.js.
 */

/* Инициализация сессии и получение данных текущего пользователя */
require_once 'php/handlers/session.php';
$currentUser = getCurrentUser();

/* Загрузка данных каталога: категории и активные блюда */
require_once 'php/config/db.php';
try {
    /* Список категорий, отсортированный по полю sort_order, исключая мягко удаленные */
    $categoriesStmt = $pdo->query("SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY sort_order");
    $categories = $categoriesStmt->fetchAll();

    /* Все не удаленные блюда */
    $productsStmt = $pdo->query("SELECT * FROM products WHERE deleted_at IS NULL ORDER BY id ASC");
    $allProducts = $productsStmt->fetchAll();

    /* Группировка блюд по category_id для удобного вывода по вкладкам */
    $productsByCategory = [];
    foreach ($allProducts as $p) {
        $productsByCategory[$p['category_id']][] = $p;
    }
} catch (\PDOException $e) {
    $categories = [];
    $productsByCategory = [];
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Меню | Огонёк</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arsenal:wght@700&family=Poppins:wght@400;500;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="css/style.css?v=1.4">
    <link rel="stylesheet" href="css/cart.css?v=1.2">
    <link rel="stylesheet" href="css/menu.css?v=1.2">
    <script>
        window.currentUser = <?php echo $currentUser ? json_encode($currentUser) : 'null'; ?>;
    </script>
<link rel="icon" type="image/png" href="images/favicon.png">
</head>

<body>

    <!-- Шапка сайта с навигацией -->
    <?php include 'php/includes/header.php'; ?>

    <!-- Hero-секция: заголовок страницы меню -->
    <section class="menu-hero">
        <div class="menu-hero__overlay"></div>
        <div class="container">
            <div class="menu-hero__content">
                <h1>
                    НАШЕ
                    <span>МЕНЮ</span>
                </h1>
                <p>
                    Отборное мясо,
                    свежие продукты
                    и фирменные рецепты
                    ресторана «Огонёк»
                </p>
            </div>
        </div>
        <img src="images/steak-bg.png" alt="" class="menu-hero__image">
    </section>

    <!-- Каталог блюд: табы категорий + поиск + карточки товаров -->
    <section class="catalog">
        <div class="container">
            <!-- Панель фильтрации: кнопки категорий и поле поиска -->
            <div class="catalog-top">
                <div class="categories">
                    <?php foreach ($categories as $index => $cat): ?>
                        <button class="category-btn<?php echo $index === 0 ? ' active' : ''; ?>" data-category="<?php echo htmlspecialchars($cat['slug']); ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="search">
                    <img src="icons/search-icon.svg" alt="">
                    <input type="text" placeholder="Поиск блюда...">
                </div>
            </div>

            <!-- Блоки товаров по категориям: первая категория видима, остальные скрыты -->
            <?php foreach ($categories as $index => $cat): ?>
                <div id="<?php echo htmlspecialchars($cat['slug']); ?>" class="category-content<?php echo $index === 0 ? ' active' : ''; ?>">
                    <?php 
                    $catProducts = $productsByCategory[$cat['id']] ?? [];
                    if (empty($catProducts)): 
                    ?>
                        <p class="profile-orders-empty" style="grid-column: 1 / -1; text-align: center; width: 100%;">В этой категории пока нет блюд</p>
                    <?php else: ?>
                        <?php foreach ($catProducts as $prod): ?>
                            <div class="product-card" data-product-id="<?php echo $prod['id']; ?>">
                                <div class="product-image">
                                    <img src="<?php echo htmlspecialchars($prod['image_path']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                                </div>
                                <div class="product-content">
                                    <h3><?php echo htmlspecialchars($prod['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($prod['description']); ?></p>
                                    <div class="price"><?php echo number_format($prod['price'], 0, '.', ''); ?> ₽</div>
                                    <button class="add-cart">
                                        <img src="icons/cart.svg" alt="">
                                        В корзину
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Подвал сайта, корзина и подключение скриптов -->
    <?php include 'php/includes/footer.php'; ?>