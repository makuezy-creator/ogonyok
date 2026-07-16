<?php
/**
 * Главная страница (лендинг) ресторана «Огонёк».
 * Содержит hero-баннер, блок преимуществ и краткое описание ресторана.
 */

/* Инициализация сессии и получение данных текущего пользователя */
require_once 'php/handlers/session.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Главная | Огонёк</title>

    <!-- Подключение шрифтов Google Fonts: Arsenal (заголовки) и Poppins (текст) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arsenal:wght@700&family=Poppins:wght@400;500;700&display=swap"
        rel="stylesheet">

    <!-- Глобальные стили и стили корзины -->
    <link rel="stylesheet" href="css/style.css?v=1.4">
    <link rel="stylesheet" href="css/cart.css?v=1.2">

    <!-- Передача данных авторизованного пользователя в JS-контекст -->
    <script>
        window.currentUser = <?php echo $currentUser ? json_encode($currentUser) : 'null'; ?>;
    </script>
    <link rel="icon" type="image/png" href="images/favicon.png">
</head>

<body>

    <!-- Шапка сайта с навигацией и мобильным меню -->
    <?php include 'php/includes/header.php'; ?>

    <!-- Hero-секция: главный баннер с CTA-кнопками -->
    <section class="hero">
        <div class="hero__overlay"></div>

        <div class="container">
            <div class="hero__content">
                <div class="hero__left">
                    <h1 class="hero__title">
                        КАЖДОЕ БЛЮДО
                        <span>
                            С ХАРАКТЕРОМ
                        </span>
                    </h1>
                    <p class="hero__text">
                        Готовим с душой,
                        доставляем с заботой.

                        Наслаждайтесь ресторанным
                        вкусом дома.
                    </p>
                    <!-- Основные кнопки навигации: меню и бронирование -->
                    <div class="hero-buttons">
                        <a href="menu.php" class="btn-primary">
                            ЗАКАЗАТЬ
                        </a>

                        <a href="booking.php" class="btn-secondary">
                            ЗАБРОНИРОВАТЬ
                        </a>

                    </div>
                </div>
            </div>
        </div>

        <img src="./images/steak-bg.png" alt="Стейк" class="hero__image">
    </section>

    <!-- Секция преимуществ: три карточки с иконками -->
    <section class="features">
        <div class="container">
            <div class="features__wrapper">
                <div class="feature">
                    <img src="./icons/delivery-icon.svg" alt="">
                    <div>
                        <h3>
                            Быстрая доставка
                        </h3>
                        <p>
                            от 30 минут
                        </p>
                    </div>
                </div>

                <div class="feature">
                    <img src="./icons/fire-icon.svg" alt="">
                    <div>
                        <h3>
                            Готовим после заказа
                        </h3>
                        <p>
                            всегда приезжает горячим
                        </p>
                    </div>
                </div>

                <div class="feature">
                    <img src="./icons/star-icon.svg" alt="">
                    <div>
                        <h3>
                            Свежие продукты
                        </h3>
                        <p>
                            только качественные ингредиенты
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Секция «О ресторане»: изображение + описание + ссылка на подробную страницу -->
    <section class="about">
        <div class="container">
            <div class="about__wrapper">
                <div class="about__image">
                    <img src="./images/restouran-bg.png" alt="Ресторан">
                </div>
                <div class="about__content">
                    <h2>
                        О ресторане
                    </h2>
                    <p>
                        Огонёк — ресторан гриль,
                        где каждое блюдо готовится
                        на живом огне.

                        Мы используем только
                        свежие ингредиенты,
                        натуральное мясо
                        и авторские рецепты.
                    </p>
                    <a href="about.php" class="btn-outline">
                        Подробнее о нас
                        <img src="./icons/right-arrow-icon.svg" alt="Подробнее">
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Подвал сайта, корзина и подключение скриптов -->
    <?php include 'php/includes/footer.php'; ?>