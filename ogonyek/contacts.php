<?php
/**
 * Страница контактов ресторана «Огонёк».
 * Содержит адрес, телефоны, режим работы и встроенную Яндекс.Карту.
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
    <title>Контакты | Огонёк</title>
    <link rel="stylesheet" href="css/style.css?v=1.4">
    <link rel="stylesheet" href="css/cart.css?v=1.2">
    <link rel="stylesheet" href="css/contacts.css">
    <script>
        window.currentUser = <?php echo $currentUser ? json_encode($currentUser) : 'null'; ?>;
    </script>
<link rel="icon" type="image/png" href="images/favicon.png">
</head>

<body>

    <?php include 'php/includes/header.php'; ?>

    <section class="contacts-hero">

        <img src="images/restouran-bg.png" alt="" class="contacts-hero__image">

        <div class="contacts-hero__overlay"></div>

        <div class="container">

            <div class="contacts-hero__content">

                <h1>
                    КОНТАКТЫ
                </h1>

                <h2>
                    Мы всегда рады вам!
                </h2>

                <p>
                    Свяжитесь с нами любым удобным способом
                    или приходите в гости.
                    Будем рады видеть вас в нашем ресторане!
                </p>

                <a href="booking.php" class="hero-btn">
                    Забронировать стол
                    <img src="icons/right-arrow-icon.svg" alt="">
                </a>
            </div>
        </div>
    </section>

    <section class="contacts-info">

        <div class="container">

            <div class="contacts-grid">

                <div class="contact-card">

                    <img src="icons/phone-icon.svg" alt="">

                    <div class="contact-card__info">
                        <h3>
                            Телефон
                        </h3>

                        <span>
                            +7 (999) 123-45-67
                        </span>

                        <p>
                            Ежедневно с 10:00 до 23:00
                        </p>
                    </div>
                </div>

                <div class="contact-card">

                    <img src="icons/sms-icon.svg" alt="">

                    <div class="contact-card__info">
                        <h3>
                            Соцсети
                        </h3>

                        <span>
                            VK, TG, INST
                        </span>

                        <p>
                            Ответим в течение часа
                        </p>
                    </div>
                </div>

                <div class="contact-card">

                    <img src="icons/address-icon.svg" alt="">

                    <div class="contact-card__info">
                        <h3>
                            Адрес
                        </h3>

                        <span>
                            г. Ухта, ул. Заводская улица, 3с1
                        </span>

                        <p>
                            Вход со стороны "Магнит"
                        </p>
                    </div>
                </div>

                <div class="contact-card">

                    <img src="icons/time-icon.svg" alt="">

                    <div class="contact-card__info">
                        <h3>
                            Время работы
                        </h3>

                        <span>
                            10:00–23:00
                        </span>

                        <p>
                            Без выходных
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="map-section">

        <div class="container">
            <div class="section-title">
                <h2>КАК НАС НАЙТИ</h2>

                <p>
                    Мы находимся в удобном месте с парковкой и удобным подъездом
                </p>
            </div>

            <div class="map-box">
                <iframe
                    src="https://yandex.ru/map-widget/v1/?ll=53.706569%2C63.566863&mode=whatshere&whatshere%5Bpoint%5D=53.706546%2C63.565504&whatshere%5Bzoom%5D=17&z=16.41"
                    width="100%" height="600" frameborder="0" allowfullscreen>
                </iframe>
            </div>
        </div>
    </section>

    <section class="advantages">
        <div class="container">
            <div class="advantages-grid">
                <div class="advantage-card">
                    <img src="icons/delivery-icon.svg" alt="">

                    <div>
                        <h3>
                            Быстрая доставка
                        </h3>

                        <p>
                            от 30 минут
                        </p>
                    </div>
                </div>

                <div class="advantage-card">
                    <img src="icons/fire-icon.svg" alt="">

                    <div>
                        <h3>
                            Готовим после заказа
                        </h3>

                        <p>
                            всегда приезжает горячим
                        </p>
                    </div>
                </div>

                <div class="advantage-card">

                    <img src="icons/star-icon.svg" alt="">
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

    <?php include 'php/includes/footer.php'; ?>