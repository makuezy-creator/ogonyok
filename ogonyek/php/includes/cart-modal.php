<!-- 
    Компонент модальных окон корзины и чекаута.
    Содержит разметку боковой панели корзины, форму оформления заказа (checkout)
    и модальное окно успешного оформления заказа.
-->
    <!-- Затеняющий слой заднего фона при открытии корзины -->

    <div class="cart-overlay" id="cart-overlay"></div>

    <!-- Боковая панель корзины -->
    <aside class="cart-sidebar" id="cart-sidebar">
        <!-- Шапка корзины -->
        <div class="cart-header">
            <h3>Корзина</h3>
            <button class="cart-close" id="cart-close">×</button>
        </div>

        <!-- Список добавленных блюд (заполняется динамически через JS) -->
        <div class="cart-items" id="cart-items">
            <p class="cart-empty">Корзина пуста</p>
        </div>

        <!-- Футер корзины со стоимостью и кнопкой заказа -->
        <div class="cart-footer" id="cart-footer" style="display:none">
            <div class="cart-total">
                <span>Итого:</span>
                <span id="cart-total-price">0 ₽</span>
            </div>
            <button class="cart-order-btn">
                Перейти к оформлению
            </button>
        </div>
    </aside>

    <!-- Модальное окно оформления заказа (выбор адреса, примечание) -->
    <div class="checkout-modal" id="checkout-modal">
        <div class="checkout-modal__content">
            <div class="checkout-modal__header">
                <h3>Оформление заказа</h3>
                <button id="checkout-close">×</button>
            </div>

            <div id="checkout-form-container"></div>
            <button class="checkout-confirm-btn" id="checkout-confirm-btn">
                Подтвердить заказ
            </button>
        </div>
    </div>

    <!-- Модальное окно успешного оформления заказа -->
    <div class="cart-modal" id="cart-modal">
        <div class="cart-modal__content">
            <div class="cart-modal__icon">✓</div>
            <h3>Заказ оформлен!</h3>
            <p>Мы свяжемся с вами в ближайшее время для подтверждения заказа.</p>
            <button class="cart-modal__btn">Отлично</button>
        </div>
    </div>
