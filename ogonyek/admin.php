<?php
/**
 * Панель администратора ресторана «Огонёк».
 * Позволяет управлять заказами, бронированиями, меню и категориями.
 * Доступна исключительно администраторам.
 */

require_once 'php/config/db.php';
require_once 'php/handlers/session.php';

/* Получение данных текущего авторизованного пользователя */
$currentUser = getCurrentUser();

/* Проверка прав администратора, если не админ — редирект на вход */
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

try {
    $totalOrdersCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $completedRevenue = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status = 'completed'")->fetchColumn() ?: 0;
    $activeBookingsCount = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'confirmed' OR status = 'pending'")->fetchColumn();
    $totalProductsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $revenueToday = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status = 'completed' AND DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
    $revenueMonth = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn() ?: 0;
    $bookingsToday = $pdo->query("SELECT COUNT(*) FROM bookings WHERE (status = 'confirmed' OR status = 'pending') AND booking_date = CURDATE()")->fetchColumn();
    $salesDynamicsRaw = $pdo->query("
        SELECT DATE(created_at) as date, SUM(total_price) as revenue 
        FROM orders 
        WHERE status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(created_at)
        ORDER BY DATE(created_at) ASC
    ")->fetchAll();

    $salesDynamics = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $formattedDate = date('d.m', strtotime($d));
        $salesDynamics[$formattedDate] = 0;
    }
    foreach ($salesDynamicsRaw as $row) {
        $formattedDate = date('d.m', strtotime($row['date']));
        $salesDynamics[$formattedDate] = floatval($row['revenue']);
    }

    $topDishes = $pdo->query("
        SELECT p.name, SUM(oi.quantity) as qty
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status = 'completed'
        GROUP BY oi.product_id
        ORDER BY qty DESC
        LIMIT 5
    ")->fetchAll(\PDO::FETCH_ASSOC);

    $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY (deleted_at IS NOT NULL) ASC, sort_order ASC");
    $categories = $categoriesStmt->fetchAll();

    $productsStmt = $pdo->query("SELECT p.*, c.name as category_name, c.deleted_at as category_deleted_at FROM products p JOIN categories c ON p.category_id = c.id ORDER BY (p.deleted_at IS NOT NULL) ASC, p.id DESC");
    $products = $productsStmt->fetchAll();

    $orders = $pdo->query("
        SELECT
            o.*,
            u.name AS user_name,
            u.phone AS user_phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.archived_at IS NULL
        ORDER BY o.created_at DESC
    ")->fetchAll();

    $archivedOrders = $pdo->query("
        SELECT
            o.*,
            u.name AS user_name,
            u.phone AS user_phone
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.archived_at IS NOT NULL
        ORDER BY o.archived_at DESC, o.created_at DESC
    ")->fetchAll();

    $bookings = $pdo->query("
        SELECT *
        FROM bookings
        WHERE archived_at IS NULL
        ORDER BY booking_date DESC, booking_time DESC
    ")->fetchAll();

    $archivedBookings = $pdo->query("
        SELECT *
        FROM bookings
        WHERE archived_at IS NOT NULL
        ORDER BY archived_at DESC, booking_date DESC, booking_time DESC
    ")->fetchAll();

} catch (\PDOException $e) {
    die("Ошибка загрузки данных админ-панели: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора | Ресторан «Огонёк»</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arsenal:wght@700&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css?v=1.4">
    <link rel="stylesheet" href="css/cart.css?v=1.2">
    <link rel="stylesheet" href="css/admin.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.currentUser = <?php echo $currentUser ? json_encode($currentUser) : 'null'; ?>;
        window.salesDynamicsData = <?php echo json_encode($salesDynamics); ?>;
        window.topDishesData = <?php echo json_encode($topDishes); ?>;
    </script>
    <script src="js/admin.js?v=<?php echo time(); ?>" defer></script>
<link rel="icon" type="image/png" href="images/favicon.png">
</head>
<body>

    <?php include 'php/includes/header.php'; ?>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <button class="nav-tab active" onclick="switchTab('dashboard-tab')">
                <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9 14H5v-2h5v2zm0-4H5v-2h5v2zm0-4H5V7h5v2zm9 8h-7v-2h7v2zm0-4h-7v-2h7v2zm0-4h-7V7h7v2z"/></svg>
                <span>Дашборд</span>
            </button>
            <button class="nav-tab" onclick="switchTab('menu-tab')">
                <svg viewBox="0 0 24 24"><path d="M11 9H9V2H7V9H5V2H3V9c0 2.12 1.46 3.9 3.43 4.38l-.43 7.62h2l-.43-7.62C11.54 12.9 13 11.12 13 9V2h-2v7zm8-4c-1.66 0-3 1.34-3 3v5h2v7h2V12h2V8c0-1.66-1.34-3-3-3z"/></svg>
                <span>Меню ресторана</span>
            </button>
            <button class="nav-tab" onclick="switchTab('categories-tab')">
                <svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12z"/></svg>
                <span>Категории меню</span>
            </button>
            <button class="nav-tab" onclick="switchTab('orders-tab')">
                <svg viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                <span>Заказы клиентов</span>
            </button>
            <button class="nav-tab" onclick="switchTab('bookings-tab')">
                <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zm-5-5H7v2h7v-2z"/></svg>
                <span>Бронирования столов</span>
            </button>
            <a href="login.php" class="nav-tab">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                <span>Личный кабинет</span>
            </a>
        </aside>

        <main class="admin-content">
            
            <section id="dashboard-tab" class="tab-content active">
                <h2>Панель управления и статистика</h2>
                
                <div class="stats-grid">
                    <div class="stats-card">
                        <div class="stats-card__info">
                            <span class="stats-card__title">Выручка за сегодня</span>
                            <div class="stats-card__value"><?php echo number_format($revenueToday, 0, '.', ' '); ?> <span>₽</span></div>
                        </div>
                        <div class="stats-card__icon">
                            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/></svg>
                        </div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-card__info">
                            <span class="stats-card__title">Выручка за месяц</span>
                            <div class="stats-card__value"><?php echo number_format($revenueMonth, 0, '.', ' '); ?> <span>₽</span></div>
                        </div>
                        <div class="stats-card__icon">
                            <svg viewBox="0 0 24 24"><path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
                        </div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-card__info">
                            <span class="stats-card__title">Брони на сегодня</span>
                            <div class="stats-card__value"><?php echo $bookingsToday; ?></div>
                        </div>
                        <div class="stats-card__icon">
                            <svg viewBox="0 0 24 24"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/></svg>
                        </div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-card__info">
                            <span class="stats-card__title">Всего заказов</span>
                            <div class="stats-card__value"><?php echo $totalOrdersCount; ?></div>
                        </div>
                        <div class="stats-card__icon">
                            <svg viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                        </div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-card__info">
                            <span class="stats-card__title">Активные брони</span>
                            <div class="stats-card__value"><?php echo $activeBookingsCount; ?></div>
                        </div>
                        <div class="stats-card__icon">
                            <svg viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zm-5-5H7v2h7v-2z"/></svg>
                        </div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-card__info">
                            <span class="stats-card__title">Блюд в меню</span>
                            <div class="stats-card__value"><?php echo $totalProductsCount; ?></div>
                        </div>
                        <div class="stats-card__icon">
                            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        </div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card">
                        <h3>Динамика выручки за последние 7 дней, ₽</h3>
                        <div class="chart-container">
                            <canvas id="salesDynamicsChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h3>Популярные блюда (ТОП-5 по заказам), шт.</h3>
                        <div class="chart-container">
                            <canvas id="topDishesChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>

            <section id="menu-tab" class="tab-content">
                <h2>Управление меню ресторана</h2>
                
                <div class="grid-forms">
                    <div class="form-card" id="product-form-card">
                        <h3 id="product-form-title">Добавить новое блюдо</h3>
                        <form id="add-product-form" enctype="multipart/form-data">
                            <input type="hidden" id="prod-id" name="id">
                            <div class="form-group">
                                <label for="prod-name">Название блюда</label>
                                <input type="text" id="prod-name" name="name" required placeholder="Например: Стейк Рибай">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="prod-category">Категория</label>
                                    <div class="custom-select-container category-select-container" id="category-select-wrapper">
                                        <div class="custom-select-trigger">
                                            <span class="custom-select-trigger-text">Выберите категорию...</span>
                                            <span class="custom-select-arrow"></span>
                                        </div>
                                        <div class="custom-select-options">
                                            <div class="custom-select-option selected" data-value="">Выберите категорию...</div>
                                            <?php foreach ($categories as $cat): ?>
                                                <?php if ($cat['deleted_at'] === null): ?>
                                                    <div class="custom-select-option" data-value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <input type="text" id="prod-category" name="category_id" required class="hidden-input">
                                </div>
                                <div class="form-group">
                                    <label for="prod-price">Цена (₽)</label>
                                    <input type="number" id="prod-price" name="price" required min="1" placeholder="990">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="prod-desc">Описание блюда</label>
                                <textarea id="prod-desc" name="description" rows="3" placeholder="Мраморная говядина зернового откорма..."></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Изображение блюда</label>
                                <div class="file-upload-zone" id="file-upload-zone">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <p><strong>Перетащите изображение</strong> или нажмите для выбора</p>
                                    <span class="file-upload-zone__btn">Выбрать файл</span>
                                    <input type="file" id="prod-image" name="image" accept="image/*" required style="display: none;">
                                    
                                    <div class="file-preview" id="file-preview">
                                        <img src="" alt="Превью" class="file-preview__image" id="file-preview-img">
                                        <div class="file-preview__info">
                                            <span class="file-preview__name" id="file-preview-name"></span>
                                            <span class="file-preview__size" id="file-preview-size"></span>
                                        </div>
                                        <button type="button" class="file-preview__remove" id="file-preview-remove">
                                            <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" id="prod-existing-image" name="existing_image_path">
                            </div>
                            
                            <div class="category-form-actions">
                                <button type="submit" class="submit-btn" id="prod-submit-btn">Добавить в меню</button>
                                <button type="button" class="exit-btn" id="prod-cancel-btn" style="display: none;">Отмена</button>
                            </div>
                        </form>
                    </div>

                    <div class="form-card menu-list-card">
                        <h3>Список блюд меню</h3>
                        <div class="menu-table-container">
                            <table id="products-table">
                                <thead>
                                    <tr>
                                        <th>Фото</th>
                                        <th>Название</th>
                                        <th>Категория</th>
                                        <th>Цена</th>
                                        <th>Действие</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $prod): ?>
                                        <tr data-id="<?php echo $prod['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($prod['name']); ?>" 
                                            data-category-id="<?php echo $prod['category_id']; ?>" 
                                            data-price="<?php echo floatval($prod['price']); ?>" 
                                            data-desc="<?php echo htmlspecialchars($prod['description'] ?? ''); ?>" 
                                            data-image="<?php echo htmlspecialchars($prod['image_path']); ?>"
                                            <?php if ($prod['deleted_at'] !== null) echo 'class="row-deleted"'; ?>>
                                            <td>
                                                <img src="<?php echo htmlspecialchars($prod['image_path']); ?>" alt="" class="product-img-mini">
                                            </td>
                                            <td><strong><?php echo htmlspecialchars($prod['name']); ?></strong></td>
                                            <td>
                                                <?php 
                                                echo htmlspecialchars($prod['category_name']); 
                                                if ($prod['category_deleted_at'] !== null) {
                                                    echo ' <span style="font-size:11px; color:var(--danger); font-style:italic;">(удалена)</span>';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo floatval($prod['price']); ?> ₽</td>
                                            <td>
                                                <div class="product-actions">
                                                    <?php if ($prod['deleted_at'] === null): ?>
                                                        <button class="edit-btn edit-product-btn" onclick="editProduct(this)">Ред.</button>
                                                        <button class="delete-btn" onclick="deleteProduct(<?php echo $prod['id']; ?>)">Удалить</button>
                                                    <?php else: ?>
                                                        <button class="restore-btn" onclick="restoreProduct(<?php echo $prod['id']; ?>)">Восстановить</button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination" id="menu-pagination">
                            <button type="button" class="pagination-btn prev-btn" id="menu-prev-btn">&laquo;</button>
                            <div class="pagination-pages" id="menu-pagination-pages"></div>
                            <button type="button" class="pagination-btn next-btn" id="menu-next-btn">&raquo;</button>
                        </div>
                    </div>
                </div>
            </section>

            <section id="categories-tab" class="tab-content">
                <h2>Управление категориями меню</h2>
                
                <div class="grid-forms">
                    <div class="form-card">
                        <h3 id="category-form-title">Добавить новую категорию</h3>
                        <form id="category-form">
                            <input type="hidden" id="cat-id" name="id">
                            
                            <div class="form-group">
                                <label for="cat-name">Название категории</label>
                                <input type="text" id="cat-name" name="name" required placeholder="Например: Десерты">
                            </div>
                            
                            <div class="form-group">
                                <label for="cat-slug">Ярлык (Slug) для URL</label>
                                <input type="text" id="cat-slug" name="slug" placeholder="Например: deserts (заполнится автоматически)">
                            </div>
                            
                            <div class="form-group">
                                <label for="cat-sort">Порядок сортировки</label>
                                <input type="number" id="cat-sort" name="sort_order" min="0" value="0">
                            </div>
                            
                            <div class="category-form-actions">
                                <button type="submit" class="submit-btn" id="cat-submit-btn">Добавить категорию</button>
                                <button type="button" class="exit-btn" id="cat-cancel-btn" style="display: none;">Отмена</button>
                            </div>
                        </form>
                    </div>

                    <div class="form-card categories-list-card">
                        <h3>Список категорий</h3>
                        <div class="menu-table-container">
                            <table id="categories-table">
                                <thead>
                                    <tr>
                                        <th>Название</th>
                                        <th>Ярлык (Slug)</th>
                                        <th>Порядок</th>
                                        <th>Действие</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr data-id="<?php echo $cat['id']; ?>" 
                                            data-name="<?php echo htmlspecialchars($cat['name']); ?>" 
                                            data-slug="<?php echo htmlspecialchars($cat['slug']); ?>" 
                                            data-sort="<?php echo $cat['sort_order']; ?>"
                                            <?php if ($cat['deleted_at'] !== null) echo 'class="row-deleted"'; ?>>
                                            <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                            <td><code><?php echo htmlspecialchars($cat['slug']); ?></code></td>
                                            <td><?php echo $cat['sort_order']; ?></td>
                                            <td>
                                                <div class="category-actions">
                                                    <?php if ($cat['deleted_at'] === null): ?>
                                                        <button class="edit-btn edit-category-btn" onclick="editCategory(this)">Ред.</button>
                                                        <button class="delete-btn" onclick="deleteCategory(<?php echo $cat['id']; ?>)">Удалить</button>
                                                    <?php else: ?>
                                                        <button class="restore-btn" onclick="restoreCategory(<?php echo $cat['id']; ?>)">Восстановить</button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <section id="orders-tab" class="tab-content">
                <h2>Заказы клиентов</h2>

                <div class="admin-mb-20">
                    <button class="danger-btn" onclick="resetOrders()">
                        Очистить заказы
                    </button>
                </div>

                <div class="form-card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID заказа</th>
                                    <th>Дата заказа</th>
                                    <th>Клиент</th>
                                    <th>Состав заказа</th>
                                    <th>Адрес доставки</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $orderIndex = count($orders); foreach ($orders as $order): 
                                    $orderItemsStmt = $pdo->prepare("
                                        SELECT oi.*, COALESCE(oi.product_name, p.name) AS name 
                                        FROM order_items oi 
                                        LEFT JOIN products p ON oi.product_id = p.id 
                                        WHERE oi.order_id = ?
                                    ");
                                    $orderItemsStmt->execute([$order['id']]);
                                    $items = $orderItemsStmt->fetchAll();

                                    $address = $order['delivery_address'];
                                    $comment = '';
                                    if (preg_match('/\(Примечание: (.*)\)$/', $address, $matches)) {
                                        $comment = $matches[1];
                                        $address = trim(str_replace($matches[0], '', $address));
                                    }
                                ?>
                                    <tr>
                                        <td>
                                            <div class="item-index-display">#<?php echo $orderIndex--; ?></div>
                                            <div class="item-db-id-display">ID: <?php echo $order['id']; ?></div>
                                        </td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <div class="order-client-info">
                                                <strong><?php echo htmlspecialchars($order['user_name'] ?? 'Удаленный пользователь'); ?></strong>
                                                <span>Тел: <?php echo htmlspecialchars($order['user_phone'] ?? 'Нет данных'); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <ul class="order-items-list">
                                                <?php foreach ($items as $item): ?>
                                                    <li><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?> (<?php echo floatval($item['price']); ?> ₽)</li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($address); ?>
                                            <?php if (!empty($comment)): ?>
                                                <div class="order-comment-display"><strong>Комм:</strong> "<?php echo htmlspecialchars($comment); ?>"</div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo floatval($order['total_price']); ?> ₽</strong></td>
                                        <td>
                                            <div class="custom-select-container status-select-container" data-type="order" data-id="<?php echo $order['id']; ?>" data-status="<?php echo $order['status']; ?>">
                                                <div class="custom-select-trigger">
                                                    <span class="custom-select-trigger-text">
                                                        <?php 
                                                        switch($order['status']) {
                                                            case 'pending': echo 'Принят'; break;
                                                            case 'processing': echo 'Готовится'; break;
                                                            case 'completed': echo 'Выполнен'; break;
                                                            case 'cancelled': echo 'Отменен'; break;
                                                            default: echo 'Принят';
                                                        }
                                                        ?>
                                                    </span>
                                                    <span class="custom-select-arrow"></span>
                                                </div>
                                                <div class="custom-select-options">
                                                    <div class="custom-select-option <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>" data-value="pending">Принят</div>
                                                    <div class="custom-select-option <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>" data-value="processing">Готовится</div>
                                                    <div class="custom-select-option <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>" data-value="completed">Выполнен</div>
                                                    <div class="custom-select-option <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>" data-value="cancelled">Отменен</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-card admin-mt-40">
                    <h3>Архивные заказы</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID заказа</th>
                                    <th>Дата заказа</th>
                                    <th>Время архивации</th>
                                    <th>Клиент</th>
                                    <th>Состав заказа</th>
                                    <th>Адрес доставки</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($archivedOrders)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; color: var(--text-gray); padding: 24px;">В архиве нет заказов</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($archivedOrders as $order): 
                                        $orderItemsStmt = $pdo->prepare("
                                            SELECT oi.*, COALESCE(oi.product_name, p.name) AS name 
                                            FROM order_items oi 
                                            LEFT JOIN products p ON oi.product_id = p.id 
                                            WHERE oi.order_id = ?
                                        ");
                                        $orderItemsStmt->execute([$order['id']]);
                                        $items = $orderItemsStmt->fetchAll();

                                        $address = $order['delivery_address'];
                                        $comment = '';
                                        if (preg_match('/\(Примечание: (.*)\)$/', $address, $matches)) {
                                            $comment = $matches[1];
                                            $address = trim(str_replace($matches[0], '', $address));
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="item-db-id-display">ID: <?php echo $order['id']; ?></div>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td><strong style="color: var(--primary);"><?php echo $order['archived_at'] ? date('d.m.Y H:i', strtotime($order['archived_at'])) : '—'; ?></strong></td>
                                            <td>
                                                <div class="order-client-info">
                                                    <strong><?php echo htmlspecialchars($order['user_name'] ?? 'Удаленный пользователь'); ?></strong>
                                                    <span>Тел: <?php echo htmlspecialchars($order['user_phone'] ?? 'Нет данных'); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <ul class="order-items-list">
                                                    <?php foreach ($items as $item): ?>
                                                        <li><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?> (<?php echo floatval($item['price']); ?> ₽)</li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($address); ?>
                                                <?php if (!empty($comment)): ?>
                                                    <div class="order-comment-display"><strong>Комм:</strong> "<?php echo htmlspecialchars($comment); ?>"</div>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo floatval($order['total_price']); ?> ₽</strong></td>
                                            <td>
                                                <span style="font-weight: 500; color: var(--text-gray);">
                                                    <?php 
                                                    switch($order['status']) {
                                                        case 'pending': echo 'Принят'; break;
                                                        case 'processing': echo 'Готовится'; break;
                                                        case 'completed': echo 'Выполнен'; break;
                                                        case 'cancelled': echo 'Отменен'; break;
                                                        default: echo 'Принят';
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="bookings-tab" class="tab-content">
                <h2>Бронирования столиков</h2>
                
                <div class="admin-mb-20">
                    <button class="danger-btn" onclick="resetBookings()">
                        Очистить бронирования
                    </button>
                </div>

                <div class="form-card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>№</th>
                                    <th>Имя гостя</th>
                                    <th>Телефон</th>
                                    <th>E-mail</th>
                                    <th>Дата и время</th>
                                    <th>Гости</th>
                                    <th>Комментарий</th>
                                    <th>Статус брони</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $bookingIndex = count($bookings); foreach ($bookings as $book): ?>
                                    <tr>
                                        <td>
                                            <div class="item-index-display">#<?php echo $bookingIndex--; ?></div>
                                            <div class="item-db-id-display">ID: <?php echo $book['id']; ?></div>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($book['guest_name']); ?></strong>
                                            <?php if ($book['user_id']): ?>
                                                <span class="guest-status-registered">Зарегистрирован</span>
                                            <?php else: ?>
                                                <span class="guest-status-unregistered">Гость</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($book['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($book['email'] ?? '—'); ?></td>
                                        <td>
                                            <div><strong><?php echo date('d.m.Y', strtotime($book['booking_date'])); ?></strong></div>
                                            <div class="booking-time-display"><?php echo date('H:i', strtotime($book['booking_time'])); ?></div>
                                        </td>
                                        <td><?php echo $book['guests_count']; ?> чел.</td>
                                        <td class="booking-comment-display">
                                            <?php echo htmlspecialchars($book['comment'] ?? '—'); ?>
                                        </td>
                                        <td>
                                            <div class="custom-select-container status-select-container" data-type="booking" data-id="<?php echo $book['id']; ?>" data-status="<?php echo $book['status']; ?>">
                                                <div class="custom-select-trigger">
                                                    <span class="custom-select-trigger-text">
                                                        <?php 
                                                        switch($book['status']) {
                                                            case 'pending': echo 'Ожидает'; break;
                                                            case 'confirmed': echo 'Подтверждена'; break;
                                                            case 'cancelled': echo 'Отменена'; break;
                                                            default: echo 'Ожидает';
                                                        }
                                                        ?>
                                                    </span>
                                                    <span class="custom-select-arrow"></span>
                                                </div>
                                                <div class="custom-select-options">
                                                    <div class="custom-select-option <?php echo $book['status'] === 'pending' ? 'selected' : ''; ?>" data-value="pending">Ожидает</div>
                                                    <div class="custom-select-option <?php echo $book['status'] === 'confirmed' ? 'selected' : ''; ?>" data-value="confirmed">Подтверждена</div>
                                                    <div class="custom-select-option <?php echo $book['status'] === 'cancelled' ? 'selected' : ''; ?>" data-value="cancelled">Отменена</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-card admin-mt-40">
                    <h3>Архивные бронирования</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID брони</th>
                                    <th>Имя гостя</th>
                                    <th>Телефон</th>
                                    <th>E-mail</th>
                                    <th>Дата и время</th>
                                    <th>Время архивации</th>
                                    <th>Гости</th>
                                    <th>Комментарий</th>
                                    <th>Статус брони</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($archivedBookings)): ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center; color: var(--text-gray); padding: 24px;">В архиве нет бронирований</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($archivedBookings as $book): ?>
                                        <tr>
                                            <td>
                                                <div class="item-db-id-display">ID: <?php echo $book['id']; ?></div>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($book['guest_name']); ?></strong>
                                                <?php if ($book['user_id']): ?>
                                                    <span class="guest-status-registered">Зарегистрирован</span>
                                                <?php else: ?>
                                                    <span class="guest-status-unregistered">Гость</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($book['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($book['email'] ?? '—'); ?></td>
                                            <td>
                                                <div><strong><?php echo date('d.m.Y', strtotime($book['booking_date'])); ?></strong></div>
                                                <div class="booking-time-display"><?php echo date('H:i', strtotime($book['booking_time'])); ?></div>
                                            </td>
                                            <td><strong style="color: var(--primary);"><?php echo $book['archived_at'] ? date('d.m.Y H:i', strtotime($book['archived_at'])) : '—'; ?></strong></td>
                                            <td><?php echo $book['guests_count']; ?> чел.</td>
                                            <td class="booking-comment-display">
                                                <?php echo htmlspecialchars($book['comment'] ?? '—'); ?>
                                            </td>
                                            <td>
                                                <span style="font-weight: 500; color: var(--text-gray);">
                                                    <?php 
                                                    switch($book['status']) {
                                                        case 'pending': echo 'Ожидает'; break;
                                                        case 'confirmed': echo 'Подтверждена'; break;
                                                        case 'cancelled': echo 'Отменена'; break;
                                                        default: echo 'Ожидает';
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div class="toast-container" id="toast-container"></div>

    <div class="confirm-modal" id="confirm-modal">
        <div class="confirm-dialog">
            <div class="confirm-dialog__header">
                <div class="confirm-dialog__icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                </div>
                <div class="confirm-dialog__title" id="confirm-modal-title">Подтверждение</div>
            </div>
            <div class="confirm-dialog__body" id="confirm-modal-message">
                Вы действительно хотите выполнить это действие?
            </div>
            <div class="confirm-dialog__footer">
                <button type="button" class="confirm-dialog__btn confirm-dialog__btn--cancel" id="confirm-modal-cancel">Отмена</button>
                <button type="button" class="confirm-dialog__btn confirm-dialog__btn--confirm" id="confirm-modal-confirm">Да, продолжить</button>
            </div>
        </div>
    </div>

    





    <?php include 'php/includes/footer.php'; ?>