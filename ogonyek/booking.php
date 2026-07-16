<?php
/**
 * Страница бронирования столиков.
 * Содержит форму резервирования, кастомный календарь и слоты времени.
 * Интерактивная логика — см. js/booking.js.
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
    <title>Забронировать стол | Ресторан «Огонёк»</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arsenal:wght@700&family=Poppins:wght@400;500;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="css/style.css?v=1.4">
    <link rel="stylesheet" href="css/cart.css?v=1.2">
    <link rel="stylesheet" href="css/booking.css?v=1.2">
    <script>
        window.currentUser = <?php echo $currentUser ? json_encode($currentUser) : 'null'; ?>;
    </script>
<link rel="icon" type="image/png" href="images/favicon.png">
</head>

<body>

    <?php include 'php/includes/header.php'; ?>

    <section class="booking-hero">
        <img src="images/restouran-bg.png" alt="Зал ресторана" class="booking-hero__image">
        <div class="booking-hero__overlay"></div>
        <div class="container">
            <div class="booking-hero__content">
                <h1>ЗАБРОНИРОВАТЬ <span>СТОЛ</span></h1>
                <p>Проведите незабываемый вечер в уютной атмосфере ресторана «Огонёк».
                    Пожалуйста, заполните форму ниже, чтобы зарезервировать столик.</p>
            </div>
        </div>
    </section>

    <section class="booking-section">
        <div class="container">
            <div class="booking-wrapper">
                <h2 class="booking-title">РЕЗЕРВ СТОЛА</h2>
                <p class="booking-subtitle">Заполните данные, и мы подготовим всё к вашему визиту</p>

                <form id="table-booking-form" class="booking-form" novalidate>
                    <div class="form-group">
                        <label for="booking-name">Ваше имя *</label>
                        <input type="text" id="booking-name" placeholder="Иван" required>
                        <span class="error-message"></span>
                    </div>

                    <div class="form-group">
                        <label for="booking-phone">Номер телефона *</label>
                        <input type="tel" id="booking-phone" placeholder="+7 (999) 999-99-99" required>
                        <span class="error-message"></span>
                    </div>

                    <div class="form-group">
                        <label for="booking-email">E-mail *</label>
                        <input type="email" id="booking-email" placeholder="example@mail.ru" required>
                        <span class="error-message"></span>
                    </div>

                    <div class="form-group">
                        <label for="booking-guests">Количество гостей *</label>
                        <input type="number" id="booking-guests" min="1" max="20" placeholder="2" required>
                        <span class="error-message"></span>
                    </div>

                    <div class="form-group date-picker-group" style="position: relative;">
                        <label for="booking-date">Дата визита *</label>
                        <div class="custom-date-input-wrapper">
                            <input type="text" id="booking-date" placeholder="— выберите дату —" readonly required>
                            <span class="calendar-trigger-icon">
                                <img src="./icons/calendar.svg" alt="Календарь">
                            </span>
                        </div>
                        <div class="custom-calendar-popup" id="custom-calendar-popup"></div>
                        <span class="error-message"></span>
                    </div>

                    <div class="form-group" id="booking-time-group">
                        <label for="booking-time">Время визита *</label>
                        <select id="booking-time" required style="display: none;">
                            <option value="">— сначала выберите дату —</option>
                        </select>
                        <div id="booking-time-slots" class="booking-time-slots">
                            <div class="time-slots-placeholder">— сначала выберите дату —</div>
                        </div>
                        <span class="slots-loading" id="slots-loading" style="display:none">⏳ Загружаем доступные слоты...</span>
                        <span class="error-message"></span>
                    </div>

                    <div class="form-group full-width">
                        <label for="booking-comment">Особые пожелания</label>
                        <textarea id="booking-comment"
                            placeholder="Например: столик у окна, детский стул, аллергия на орехи..."></textarea>
                        <span class="error-message"></span>
                    </div>

                    <button type="submit" class="booking-submit-btn">Подтвердить бронирование</button>
                </form>
            </div>
        </div>
    </section>

    <div class="booking-modal" id="booking-success-modal">
        <div class="booking-modal__overlay"></div>
        <div class="booking-modal__content">
            <div class="booking-modal__icon">✓</div>
            <h3>Стол забронирован!</h3>
            <p>Мы свяжемся с вами по телефону в ближайшее время для подтверждения.</p>
            <button class="booking-modal__btn" id="booking-modal-btn">Отлично</button>
        </div>
    </div>

    <?php include 'php/includes/footer.php'; ?>