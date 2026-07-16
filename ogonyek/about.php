<?php
/**
 * Страница «О ресторане».
 * Содержит: hero-баннер, преимущества, историю, команду и статистику.
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
    <title>О нас | Огонёк</title>
    <link rel="stylesheet" href="css/style.css?v=1.4">
    <link rel="stylesheet" href="css/cart.css?v=1.2">
    <link rel="stylesheet" href="css/about.css">
    <script>
        window.currentUser = <?php echo $currentUser ? json_encode($currentUser) : 'null'; ?>;
    </script>
<link rel="icon" type="image/png" href="images/favicon.png">
</head>

<body>

    <!-- Шапка сайта -->
    <?php include 'php/includes/header.php'; ?>

    <!-- Hero-секция: заголовок и дескриптор страницы -->
    <section class="about-hero">

        <div class="about-hero__overlay"></div>

        <img src="images/restouran2-bg.png" alt="" class="about-hero__image">

        <div class="container">

            <div class="about-hero__content">
                <h1>
                    О РЕСТОРАНЕ
                </h1>

                <h2>
                    Огонёк — это больше,
                    чем просто гриль
                </h2>

                <p>
                    Мы готовим с душой,
                    используя только свежие ингредиенты
                    и качественное мясо.

                    Каждое блюдо —
                    это наш характер и любовь
                    к настоящему вкусу.
                </p>
            </div>
        </div>
    </section>

    <!-- Преимущества: четыре карточки с иконками -->
    <section class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <img src="icons/fire-icon.svg" alt="">
                    <div>
                        <h3>
                            Живой огонь
                        </h3>

                        <p>
                            Для яркого вкуса!
                        </p>
                    </div>
                </div>

                <div class="feature-card">
                    <img src="icons/steak-icon.svg" alt="">
                    <div>
                        <h3>
                            Отборное мясо
                        </h3>

                        <p>
                            От проверенных поставщиков!
                        </p>
                    </div>
                </div>

                <div class="feature-card">
                    <img src="icons/producrs-icon.svg" alt="">
                    <div>
                        <h3>
                            Свежие продукты
                        </h3>

                        <p>
                            Только свежие ингредиенты!
                        </p>
                    </div>
                </div>

                <div class="feature-card">

                    <img src="icons/heart-icon.svg" alt="">
                    <div>
                        <h3>
                            Готовим с душой
                        </h3>

                        <p>
                            Каждое блюдо с заботой о гостях!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- История ресторана: фото + текст -->
    <section id="history" class="history">
        <div class="container">
            <div class="history-grid">
                <div class="history-image">
                    <img src="images/restouran-bg.png" alt="">
                </div>

                <div class="history-content">
                    <h2>
                        НАША ИСТОРИЯ
                    </h2>

                    <h3>
                        Огонёк — вкус,
                        рождённый огнём
                    </h3>

                    <p>
                        Мы начали с небольшой
                        гриль-кухни и большой мечты —
                        создать место, где каждый
                        почувствует настоящий вкус
                        мяса и гостеприимство.
                    </p>

                    <p>
                        Сегодня «Огонёк» —
                        это уютный ресторан
                        и быстрая доставка любимых
                        блюд прямо к вам домой.
                    </p>

                    <p>
                        Мы не стоим на месте
                        и каждый день становимся
                        лучше для вас.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Команда: карточки сотрудников -->
    <section class="team">
        <div class="container">
            <div class="section-title">
                <h2>
                    НАША КОМАНДА
                </h2>

                <p>
                    Профессионалы своего дела
                </p>
            </div>

            <div class="team-grid">

                <div class="team-card">
                    <img src="images/team-1.png" alt="">
                    <h3>
                        Иван Петров
                    </h3>

                    <span>
                        Шеф-повар
                    </span>

                    <p>
                        Более 10 лет опыта
                        в гриль-кухне
                    </p>
                </div>

                <div class="team-card">
                    <img src="images/team-2.png" alt="">
                    <h3>
                        Дмитрий Соколов
                    </h3>

                    <span>
                        Су-шеф
                    </span>

                    <p>
                        Следит за качеством
                        каждого блюда
                    </p>
                </div>

                <div class="team-card">
                    <img src="images/team-3.png" alt="">
                    <h3>
                        Анна Смирнова
                    </h3>

                    <span>
                        Менеджер зала
                    </span>

                    <p>
                        Заботится
                        о вашем комфорте
                    </p>
                </div>

                <div class="team-card">
                    <img src="images/team-4.png" alt="">
                    <h3>
                        Алексей Волков
                    </h3>

                    <span>
                        Бренд-шеф
                    </span>

                    <p>
                        Создаёт новые вкусы
                        и рецепты
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Статистика ресторана: ключевые цифры -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <img src="icons/burger-icon.svg" alt="">
                    <div class="stat-content">
                        <h3>150+</h3>
                        <p>блюд в меню</p>
                    </div>
                </div>

                <div class="stat-item">
                    <img src="icons/clients-icon.svg" alt="">
                    <div class="stat-content">
                        <h3>5000+</h3>
                        <p>довольных гостей</p>
                    </div>
                </div>

                <div class="stat-item">
                    <img src="icons/time-icon.svg" alt="">
                    <div class="stat-content">
                        <h3>45 мин</h3>
                        <p>среднее время доставки</p>
                    </div>
                </div>

                <div class="stat-item">
                    <img src="icons/star-icon.svg" alt="">
                    <div class="stat-content">
                        <h3>4.9</h3>
                        <p>рейтинг гостей</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Подвал, корзина и скрипты -->
    <?php include 'php/includes/footer.php'; ?>