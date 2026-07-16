<!-- Подвал сайта (footer) с правовой информацией, соцсетями и временем работы -->
    <footer class="footer">
        <div class="container">
            <div class="footer__wrapper">
                <!-- Логотип ресторана -->
                <div class="footer__logo">
                    <img src="./icons/logo.svg" alt="Огонёк">
                </div>

                <!-- Правовые ссылки и email поддержки -->
                <div class="footer__links">
                    <a href="#">
                        Политика конфиденциальности
                    </a>
                    <a href="#">
                        Пользовательское соглашение
                    </a>
                    <a href="#">
                        Техническая поддержка
                    </a>
                    <a href="#">
                        msq.support@gmail.com
                    </a>
                </div>

                <!-- Режим работы заведения -->
                <div class="footer__work">
                    <p>
                        Время работы
                    </p>
                    <span>
                        10:00 – 23:00
                    </span>
                    <span>
                        Без выходных
                    </span>
                </div>

                <!-- Ссылки на социальные сети -->
                <div class="footer__social">
                    <p>
                        Мы в соцсетях
                    </p>
                    <div class="socials">
                        <a href="#">
                            <img src="./icons/vk-icon.svg" alt="">
                        </a>
                        <a href="#">
                            <img src="./icons/telegram-icon.svg" alt="">
                        </a>
                        <a href="#">
                            <img src="./icons/instagramm-icon.svg" alt="">
                        </a>
                    </div>
                </div>
            </div>

            <!-- Копирайт -->
            <div class="footer__bottom">
                © 2026 Ресторан «Огонёк». Все права защищены.
            </div>
        </div>
    </footer>

    <!-- Подключение боковой панели корзины и модальных окон заказа -->
    <?php include 'cart-modal.php'; ?>

    <!-- Подключение общих клиентских JS-сценариев -->
    <script src="js/cart.js?v=1.2"></script>
    <script src="js/main.js?v=1.3"></script>
    <script src="js/auth.js?v=2.1"></script>
    
    <?php
    /* Подключение специализированных скриптов в зависимости от активной страницы */
    $currentPage = basename($_SERVER['SCRIPT_NAME']);
    if ($currentPage === 'menu.php') {
        echo '<script src="js/menu.js"></script>';
    } elseif ($currentPage === 'booking.php') {
        echo '<script src="js/booking.js?v=1.3"></script>';
    }
    ?>
</body>
</html>
