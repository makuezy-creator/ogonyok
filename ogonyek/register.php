<?php
/**
 * Страница регистрации нового пользователя.
 * Авторизованные пользователи перенаправляются на login.php (профиль).
 * Валидация и AJAX-отправка формы — см. js/auth.js.
 */

/* Если пользователь уже авторизован — перенаправляем в профиль */
require_once 'php/handlers/session.php';
if (isLoggedIn()) {
    header('Location: login.php');
    exit;
}
$currentUser = null;
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация | Ресторан «Огонёк»</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arsenal:wght@700&family=Poppins:wght@400;500;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="css/style.css?v=1.4">
    <link rel="stylesheet" href="css/cart.css?v=1.2">
    <link rel="stylesheet" href="css/auth.css">
    <script>
        window.currentUser = null;
    </script>
<link rel="icon" type="image/png" href="images/favicon.png">
</head>

<body>

    <?php include 'php/includes/header.php'; ?>

    <section class="auth-section">
        <div class="auth-container">

            <div class="auth-header">
                <h1>Регистрация</h1>
                <p>Создайте аккаунт в ресторане «Огонёк»</p>
            </div>

            <form id="register-form" class="auth-form" novalidate>

                <div class="form-group">
                    <label for="register-name">Имя</label>
                    <input type="text" id="register-name" placeholder="Иван" required>
                    <span class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="register-email">E-mail</label>
                    <input type="email" id="register-email" placeholder="example@mail.ru" required>
                    <span class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="register-phone">Телефон</label>
                    <input type="tel" id="register-phone" placeholder="+7 (999) 999-99-99" required>
                    <span class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="register-password">Пароль</label>
                    <div class="password-wrapper">
                        <input type="password" id="register-password" placeholder="••••••" required>
                        <button type="button" class="password-toggle"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></button>
                    </div>
                    <span class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="register-confirm">Подтвердите пароль</label>
                    <div class="password-wrapper">
                        <input type="password" id="register-confirm" placeholder="••••••" required>
                        <button type="button" class="password-toggle"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></button>
                    </div>
                    <span class="error-message"></span>
                </div>

                <button type="submit" class="auth-btn">Зарегистрироваться</button>
            </form>

            <p class="auth-link-text">Уже есть аккаунт? <a href="login.php">Войти</a></p>

        </div>
    </section>

    <?php include 'php/includes/footer.php'; ?>