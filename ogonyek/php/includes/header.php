<?php
/**
 * Шапка сайта (header) с логотипом, основным навигационным меню, кнопкой корзины и кнопками авторизации.
 * Также содержит разметку мобильного выпадающего меню.
 */

/* Подключение сессии, если переменная $currentUser не определена в файле-родителе */
if (!isset($currentUser)) {
    require_once __DIR__ . '/../handlers/session.php';
    $currentUser = getCurrentUser();
}
/* Текущая активная страница для подсветки пункта меню */
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<header class="header">
    <div class="container">
        <div class="header__wrapper">
            <a href="index.php" class="logo">
                <img src="./icons/logo.svg" alt="Огонёк">
            </a>

            <nav class="nav">
                <ul class="nav__list">
                    <li>
                        <a href="index.php" class="nav__link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                            Главная
                        </a>
                    </li>

                    <li>
                        <a href="menu.php" class="nav__link <?php echo $currentPage === 'menu.php' ? 'active' : ''; ?>">
                            Меню
                        </a>
                    </li>

                    <li>
                        <a href="about.php" class="nav__link <?php echo $currentPage === 'about.php' ? 'active' : ''; ?>">
                            О нас
                        </a>
                    </li>

                    <li>
                        <a href="contacts.php" class="nav__link <?php echo $currentPage === 'contacts.php' ? 'active' : ''; ?>">
                            Контакты
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Кнопки действий: корзина и авторизация -->
            <div class="header__actions">
                <button class="cart-btn" id="cart-toggle">
                    <img src="./icons/cart.svg" alt="Корзина">
                    <span class="cart-count">0</span>
                </button>

                <?php if ($currentUser): ?>
                    <!-- Отображение имени пользователя и ссылки в личный кабинет/админку -->
                    <a href="<?php echo $currentUser['role'] === 'admin' ? 'admin.php' : 'login.php'; ?>" class="profile-link-btn">
                        <span><?php echo htmlspecialchars($currentUser['name']); ?></span>
                    </a>
                    <button class="logout-btn-header" id="header-logout-btn">
                        <img src="./icons/login.svg" alt="Выйти">
                        <span>Выйти</span>
                    </button>
                <?php else: ?>
                    <a href="login.php" class="login-btn">
                        <img src="./icons/login.svg" alt="Вход">
                        <span>Войти</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Бургер-кнопка для открытия меню на мобильных устройствах -->
            <button class="burger">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<div class="mobile-menu">
    <nav class="mobile-nav">
        <a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Главная</a>
        <a href="menu.php" class="<?php echo $currentPage === 'menu.php' ? 'active' : ''; ?>">Меню</a>
        <a href="about.php" class="<?php echo $currentPage === 'about.php' ? 'active' : ''; ?>">О нас</a>
        <a href="contacts.php" class="<?php echo $currentPage === 'contacts.php' ? 'active' : ''; ?>">Контакты</a>
        <a href="#cart" class="mobile-cart" id="mobile-cart-btn">
            <img src="./icons/cart.svg" alt="Корзина" class="mobile-cart__icon">
            <span class="mobile-cart__text">Корзина</span>
        </a>
        <?php if ($currentUser): ?>
            <a href="<?php echo $currentUser['role'] === 'admin' ? 'admin.php' : 'login.php'; ?>" class="mobile-profile"><?php echo htmlspecialchars($currentUser['name']); ?></a>
            <a href="#" class="mobile-logout" id="mobile-logout-btn">Выйти</a>
        <?php else: ?>
            <a href="login.php" class="mobile-login">Войти</a>
        <?php endif; ?>
    </nav>
</div>
