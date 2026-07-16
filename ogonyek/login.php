<?php
/**
 * Страница входа / личного кабинета.
 * Гостям показывается форма авторизации, авторизованным — профиль,
 * адреса, история заказов и бронирований. Логика — см. js/auth.js.
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
    <title>Вход | Ресторан «Огонёк»</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arsenal:wght@700&family=Poppins:wght@400;500;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="css/style.css?v=1.4">
    <link rel="stylesheet" href="css/cart.css?v=1.2">
    <link rel="stylesheet" href="css/auth.css">
    <script>
        window.currentUser = <?php echo $currentUser ? json_encode($currentUser) : 'null'; ?>;
    </script>
<link rel="icon" type="image/png" href="images/favicon.png">
</head>

<body>

    <?php include 'php/includes/header.php'; ?>

    <section class="auth-section">
        <div class="auth-container">

            <div id="profile-view" style="display: none;" class="profile-grid">

                <div class="profile-sidebar">
                    <div class="auth-header" style="margin-bottom: 25px;">
                        <h1>Личный кабинет</h1>
                        <p>Информация об аккаунте</p>
                    </div>
                    <div class="profile-avatar" id="profile-avatar">?</div>
                    <div class="profile-name" id="profile-name">Имя Фамилия</div>
                    
                    <div class="profile-info-view" id="profile-info-view">
                        <div class="profile-email" id="profile-email">email@example.com</div>
                        <div class="profile-phone" id="profile-phone">телефон не указан</div>
                        <button type="button" class="edit-profile-btn" id="edit-profile-btn">Редактировать</button>
                    </div>

                    <form class="profile-info-edit" id="profile-info-edit" style="display: none;" novalidate>
                        <div class="form-group">
                            <label for="profile-edit-email">E-mail</label>
                            <input type="email" id="profile-edit-email" required>
                            <span class="error-message"></span>
                        </div>
                        <div class="form-group">
                            <label for="profile-edit-phone">Телефон</label>
                            <input type="text" id="profile-edit-phone" placeholder="+7 (XXX) XXX-XX-XX" required>
                            <span class="error-message"></span>
                        </div>
                        <div class="profile-edit-buttons">
                            <button type="submit" class="save-profile-btn">Сохранить</button>
                            <button type="button" class="cancel-profile-btn" id="cancel-profile-btn">Отмена</button>
                        </div>
                    </form>
                    
                    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                        <a href="admin.php" class="admin-panel-btn-profile" style="margin-top: 20px;">Панель управления</a>
                    <?php endif; ?>
                </div>
                
                <div class="profile-content">
                    <div class="profile-section">
                        <div class="profile-section-header">
                            <h3 class="address-title">Адреса доставки</h3>
                            <button type="button" class="add-address-btn" id="add-address-toggle-btn">+ Добавить адрес</button>
                        </div>
                        
                        <div id="profile-addresses-list" class="profile-addresses-list">
                        </div>
                        
                        <form id="profile-address-form" class="profile-address-form" style="display: none;" data-edit-id="">
                            <h4 id="address-form-title">Добавить новый адрес</h4>
                            
                            <div class="form-group">
                                <label for="profile-address-label">Название адреса (например: Дом, Работа)</label>
                                <input type="text" id="profile-address-label" placeholder="Дом" required>
                            </div>

                            <div class="form-group">
                                <label for="profile-city">Город</label>
                                <input type="text" id="profile-city" placeholder="Ухта" value="Ухта" required readonly>
                            </div>
                            
                            <div class="form-group">
                                <label for="profile-street">Улица</label>
                                <input type="text" id="profile-street" placeholder="ул. Ленина" required>
                            </div>
                            
                            <div class="profile-address-row">
                                <div class="form-group">
                                    <label for="profile-house">Дом</label>
                                    <input type="text" id="profile-house" placeholder="10" required>
                                </div>
                                <div class="form-group">
                                    <label for="profile-entrance">Подъезд</label>
                                    <input type="text" id="profile-entrance" placeholder="1">
                                </div>
                                <div class="form-group">
                                    <label for="profile-apartment">Кв./Офис</label>
                                    <input type="text" id="profile-apartment" placeholder="45">
                                </div>
                            </div>
                            
                            <div class="profile-address-form-buttons">
                                <button type="submit" class="auth-btn" id="profile-address-save-btn">Сохранить</button>
                                <button type="button" class="cancel-address-btn" id="profile-address-cancel-btn">Отмена</button>
                            </div>
                        </form>
                    </div>

                    <div class="profile-section" style="margin-top: 35px; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 25px;">
                        <h3 class="address-title" style="text-align: left; margin-bottom: 15px;">История заказов</h3>
                        <div id="profile-orders-list" class="profile-orders-list">
                        </div>
                    </div>

                    <div class="profile-section" style="margin-top: 35px; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 25px;">
                        <div class="profile-section-header" style="margin-bottom: 15px;">
                            <h3 class="address-title" style="text-align: left; margin-bottom: 0;">История бронирований</h3>
                            <a href="booking.php" class="add-address-btn" style="text-decoration:none;">+ Забронировать стол</a>
                        </div>
                        <div id="profile-bookings-list" class="profile-orders-list">
                        </div>
                    </div>
                </div>
            </div>

            <div id="login-view">
                <div class="auth-header">
                    <h1>Войти</h1>
                    <p>Добро пожаловать в «Огонёк»</p>
                </div>

                <form id="login-form" class="auth-form" novalidate>
                    <div class="form-group">
                        <label for="login-email">E-mail</label>
                        <input type="email" id="login-email" placeholder="example@mail.ru" required>
                        <span class="error-message"></span>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Пароль</label>
                        <div class="password-wrapper">
                            <input type="password" id="login-password" placeholder="••••••" required>
                            <button type="button" class="password-toggle"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></button>
                        </div>
                        <span class="error-message"></span>
                    </div>

                    <button type="submit" class="auth-btn">Войти</button>
                </form>

                <p class="auth-link-text">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
            </div>

        </div>
    </section>



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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentUser = window.currentUser;
            const loginView = document.getElementById('login-view');
            const profileView = document.getElementById('profile-view');
 
            if (currentUser) {
                const container = document.querySelector('.auth-container');
                if (container) container.classList.add('profile-mode');
                if (loginView) loginView.style.display = 'none';
                if (profileView) {
                    profileView.style.display = 'grid';
                    document.getElementById('profile-name').textContent = currentUser.name;
                    document.getElementById('profile-email').textContent = currentUser.email;
                    const phoneEl = document.getElementById('profile-phone');
                    if (phoneEl) phoneEl.textContent = currentUser.phone || 'телефон не указан';
                    document.getElementById('profile-avatar').textContent = currentUser.name.charAt(0).toUpperCase();
                }
            }
        });
    </script>

    <?php include 'php/includes/footer.php'; ?>