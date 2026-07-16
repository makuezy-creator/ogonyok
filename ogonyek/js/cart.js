/**
 * Клиентский сценарий корзины и оформления заказа.
 * Хранит состояние корзины, считает стоимость, управляет модалками заказа и чекаута.
 */

let cart = [];

const cartToggle = document.getElementById('cart-toggle');
const cartSidebar = document.getElementById('cart-sidebar');
const cartOverlay = document.getElementById('cart-overlay');
const cartClose = document.getElementById('cart-close');
const cartItemsEl = document.getElementById('cart-items');
const cartTotalEl = document.getElementById('cart-total-price');
const cartFooter = document.getElementById('cart-footer');
document.getElementById('mobile-cart-btn').addEventListener('click', (e) => {
    e.preventDefault();
    openCart();
});
const cartOrderBtn = document.querySelector('.cart-order-btn');
const cartCountEls = document.querySelectorAll('.cart-count');
const checkoutModal = document.getElementById('checkout-modal');
const checkoutClose = document.getElementById('checkout-close');
const checkoutFormContainer = document.getElementById('checkout-form-container');
const checkoutConfirmBtn = document.getElementById('checkout-confirm-btn');

/* Открытие боковой панели корзины */
function openCart() {
    if (cartSidebar) cartSidebar.classList.add('active');
    if (cartOverlay) cartOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

/* Закрытие боковой панели корзины */
function closeCart() {
    if (cartSidebar) cartSidebar.classList.remove('active');
    if (cartOverlay) cartOverlay.classList.remove('active');
    document.body.style.overflow = '';
}

/* Открытие модального окна оформления заказа */
function openCheckout() {
    if (checkoutModal) {
        checkoutModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/* Закрытие модального окна оформления заказа */
function closeCheckout() {
    if (checkoutModal) {
        checkoutModal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

if (cartToggle) cartToggle.addEventListener('click', openCart);
if (cartClose) cartClose.addEventListener('click', closeCart);
if (cartOverlay) cartOverlay.addEventListener('click', closeCart);

if (checkoutClose) {
    checkoutClose.addEventListener(
        'click',
        closeCheckout
    );
}

if (checkoutModal) {
    checkoutModal.addEventListener('click', (e) => {
        if (e.target === checkoutModal) {
            closeCheckout();
        }
    });
}

/* Закрытие корзины при нажатии кнопки Escape */
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeCart();
});

/* Добавление товара в корзину (или инкремент количества, если уже добавлен) */
function addToCart(name, price, image, productId) {
    const existing = cart.find(
        item => item.name === name
    );

    if (existing) {
        existing.qty += 1;
    } else {
        cart.push({
            id: productId ? parseInt(productId) : null,
            name,
            price: parseFloat(price),
            image,
            qty: 1
        });
    }

    saveCart();
    renderCart();
    /*openCart();*/
}

/* Удаление товара из корзины по индексу */
function removeFromCart(index) {
    cart.splice(index, 1);
    saveCart();
    renderCart();
}

/* Изменение количества товара в корзине на дельту */
function changeQty(index, delta) {
    cart[index].qty += delta;

    if (cart[index].qty <= 0) {
        cart.splice(index, 1);
    }

    saveCart();
    renderCart();
}

/* Сохранение состояния корзины в localStorage и синхронизация с БД (для авторизованных) */
function saveCart() {
    if (window.currentUser) {
        localStorage.setItem(
            'ogonyok_cart_' + window.currentUser.id,
            JSON.stringify(cart)
        );
        fetch('php/api/cart.php?action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart: cart })
        }).catch(err => console.error('Error saving cart to DB:', err));
    } else {
        localStorage.setItem(
            'ogonyok_cart_guest',
            JSON.stringify(cart)
        );
    }
}

function renderCart() {
    const totalItems = cart.reduce(
        (sum, item) => sum + item.qty, 0
    );

    cartCountEls.forEach(el => {
        el.textContent = totalItems;
        el.setAttribute('data-count', totalItems);

        if (totalItems > 0) {
            el.style.display = 'flex';
        } else {
            el.style.display = 'none';
        }
    });

    if (!cartItemsEl) return;

    if (cart.length === 0) {
        cartItemsEl.innerHTML = '<p class="cart-empty">Корзина пуста</p>';
        if (cartFooter) {
            cartFooter.style.display = 'none';
        }
        return;
    }

    if (cartFooter) {
        cartFooter.style.display = 'block';
    }

    let html = '';
    cart.forEach((item, index) => {
        html += `
        <div class="cart-item">
            <div class="cart-item__image">
                <img src="${item.image}" alt="${item.name}">
            </div>

            <div class="cart-item__info">
                <div class="cart-item__name">
                    ${item.name}
                </div>
                <div class="cart-item__price">
                    ${item.price * item.qty} ₽
                </div>
            </div>

            <div class="cart-item__actions">
                <div class="cart-item__qty">
                    <button onclick="changeQty(${index}, -1)">−</button>
                    <span>${item.qty}</span>
                    <button onclick="changeQty(${index}, +1)">+</button>
                </div>
                <button
                    class="cart-item__remove"
                    onclick="removeFromCart(${index})"
                >
                    Удалить
                </button>
            </div>
        </div>`;
    });

    cartItemsEl.innerHTML = html;

    const total = cart.reduce(
        (sum, item) => sum + item.price * item.qty, 0
    );

    if (cartTotalEl) {
        cartTotalEl.textContent = total + ' ₽';
    }

    if (cartFooter) {
        const oldForm = cartFooter.querySelector('.cart-address-form');
        const oldWarning = cartFooter.querySelector('.cart-auth-warning');
        if (oldForm) oldForm.remove();
        if (oldWarning) oldWarning.remove();

        const currentUser = window.currentUser;

        if (currentUser) {
            const formContainer = document.createElement('div');
            formContainer.className = 'cart-address-form';
            
            const savedAddresses = currentUser.addresses || [];
            let selectHtml = '';
            let fieldsStyle = 'display: block;';
            
            if (savedAddresses.length > 0) {
                fieldsStyle = 'display: none;';
                
                const optionsList = savedAddresses.map(addr => {
                    let text = `${addr.label}: ул. ${addr.street}, д. ${addr.house}`;
                    if (addr.apartment) text += `, кв. ${addr.apartment}`;
                    return `<div class="custom-select-option" data-value="${addr.id}">${text}</div>`;
                }).join('');
                
                selectHtml = `
                    <div style="margin-bottom: 8px;">
                        <label style="font-size: 11px; color: #999; margin-bottom: 4px; display: block;">Выберите адрес доставки:</label>
                        <div class="custom-select-container" id="cart-address-custom-select">
                            <div class="custom-select-trigger">
                                <span class="custom-select-trigger-text">${savedAddresses[0].label}: ул. ${savedAddresses[0].street}, д. ${savedAddresses[0].house}${savedAddresses[0].apartment ? `, кв. ${savedAddresses[0].apartment}` : ''}</span>
                                <span class="custom-select-arrow"></span>
                            </div>
                            <div class="custom-select-options">
                                ${optionsList}
                                <div class="custom-select-option" data-value="new">+ Новый адрес...</div>
                            </div>
                            <input type="hidden" id="cart-address-select" value="${savedAddresses[0].id}">
                        </div>
                    </div>
                `;
            }

            formContainer.innerHTML = `
                <h4>Адрес доставки</h4>
                ${selectHtml}
                
                <div id="cart-new-address-fields" style="${fieldsStyle}">
                    <div style="margin-bottom: 8px;">
                        <input type="text" id="cart-city" placeholder="Город" value="Ухта" required readonly>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <input type="text" id="cart-street" placeholder="Улица (например: Ленина)" required>
                    </div>
                    <div class="cart-address-grid" style="margin-bottom: 8px;">
                        <input type="text" id="cart-house" placeholder="Дом" required>
                        <input type="text" id="cart-entrance" placeholder="Подъезд">
                        <input type="text" id="cart-apartment" placeholder="Кв./Офис">
                    </div>
                    <div style="margin-bottom: 8px;">
                        <input type="text" id="cart-address-label" placeholder="Название адреса в профиле (например: Дом 2)">
                    </div>
                    <label class="cart-save-address" style="margin-bottom: 8px;">
                        <input type="checkbox" id="cart-save-profile" checked>
                        <span class="custom-checkbox"></span>
                        Сохранить адрес в профиле
                    </label>
                </div>
                
                <div style="margin-bottom: 8px;">
                    <textarea id="cart-comment" placeholder="Примечание к доставке (например: домофон 15#)"></textarea>
                </div>
            `;
            
            checkoutFormContainer.innerHTML = '';
            checkoutFormContainer.appendChild(formContainer);
            
            const customSelect = formContainer.querySelector('#cart-address-custom-select');
            const hiddenInput = formContainer.querySelector('#cart-address-select');
            const newFieldsEl = formContainer.querySelector('#cart-new-address-fields');
            
            if (customSelect && hiddenInput && newFieldsEl) {
                const trigger = customSelect.querySelector('.custom-select-trigger');
                const triggerText = customSelect.querySelector('.custom-select-trigger-text');
                
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    customSelect.classList.toggle('open');
                });
                
                customSelect.querySelectorAll('.custom-select-option').forEach(option => {
                    if (option.getAttribute('data-value') === hiddenInput.value) {
                        option.classList.add('selected');
                    }
                    
                    option.addEventListener('click', (e) => {
                        e.stopPropagation();
                        
                        customSelect.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
                        option.classList.add('selected');
                        
                        const val = option.getAttribute('data-value');
                        hiddenInput.value = val;
                        triggerText.textContent = option.textContent;
                        customSelect.classList.remove('open');
                        
                        hiddenInput.dispatchEvent(new Event('change'));
                    });
                });
                
                document.addEventListener('click', () => {
                    customSelect.classList.remove('open');
                });
                
                hiddenInput.addEventListener('change', () => {
                    if (hiddenInput.value === 'new') {
                        newFieldsEl.style.display = 'block';
                    } else {
                        newFieldsEl.style.display = 'none';
                    }
                });
            }

            const cartStreet = formContainer.querySelector('#cart-street');
            const cartHouse = formContainer.querySelector('#cart-house');
            if (cartStreet) {
                cartStreet.addEventListener('input', () => cartStreet.style.borderColor = '');
            }
            if (cartHouse) {
                cartHouse.addEventListener('input', () => cartHouse.style.borderColor = '');
            }

            if (cartOrderBtn) cartOrderBtn.disabled = false;
        } else {
            const warningContainer = document.createElement('div');
            warningContainer.className = 'cart-auth-warning';
            warningContainer.innerHTML = `
                <p>Для оформления заказа необходимо войти в аккаунт.</p>
                <a href="login.php">Войти</a>
            `;
            cartFooter.insertBefore(warningContainer, cartFooter.firstChild);
            
            if (cartOrderBtn) cartOrderBtn.disabled = true;
        }
    }
}

if (cartOrderBtn) {
    cartOrderBtn.addEventListener('click', () => {

        if (cart.length === 0) return;

        if (!window.currentUser) {
            alert('Пожалуйста, войдите в систему, чтобы оформить заказ.');
            return;
        }

        closeCart();
        openCheckout();
    });
}

if (checkoutConfirmBtn) {
    checkoutConfirmBtn.addEventListener(
        'click',
        async () => {
            checkoutConfirmBtn.disabled = true;
            checkoutConfirmBtn.textContent = 'Отправка...';

            const startTime = Date.now();
        if (cart.length === 0) return;

        const currentUser = window.currentUser;

        if (!currentUser) {
            alert('Пожалуйста, войдите в систему, чтобы оформить заказ.');
            return;
        }

        const selectEl = document.getElementById('cart-address-select');
        const commentEl = document.getElementById('cart-comment');

        const comment = commentEl
            ? commentEl.value.trim()
            : '';

        let addressId;
        let addressData = null;

        const isNewAddress =
            !selectEl || selectEl.value === 'new';

        if (!isNewAddress) {
            addressId = parseInt(selectEl.value);
        } else {
            const streetEl = document.getElementById('cart-street');
            const houseEl = document.getElementById('cart-house');
            const entranceEl = document.getElementById('cart-entrance');
            const apartmentEl = document.getElementById('cart-apartment');
            const labelEl = document.getElementById('cart-address-label');
            const saveProfileEl = document.getElementById('cart-save-profile');

            const street = streetEl ? streetEl.value.trim() : '';
            const house = houseEl ? houseEl.value.trim() : '';

            let hasError = false;
            if (!street) {
                if (streetEl) streetEl.style.borderColor = '#ff3838';
                hasError = true;
            } else {
                if (streetEl) streetEl.style.borderColor = '';
            }
            if (!house) {
                if (houseEl) houseEl.style.borderColor = '#ff3838';
                hasError = true;
            } else {
                if (houseEl) houseEl.style.borderColor = '';
            }

            if (hasError) {
                alert('Пожалуйста, заполните обязательные поля адреса (Улица и Дом).');
                return;
            }

            addressId = 'new';
            addressData = {
                label: labelEl ? labelEl.value.trim() : '',
                street,
                house,
                entrance: entranceEl ? entranceEl.value.trim() : '',
                apartment: apartmentEl ? apartmentEl.value.trim() : '',
                save_to_profile: saveProfileEl ? saveProfileEl.checked : false
            };
        }
     
        const items = cart.map(item => ({
            product_id: item.id,
            quantity: item.qty
        })).filter(item => item.product_id);

        if (items.length === 0) {
            alert('Ошибка: не все товары в корзине имеют ID. Добавьте товары через меню.');
            return;
        }

        if (cartOrderBtn) {
            cartOrderBtn.disabled = true;
            cartOrderBtn.textContent = 'Оформляем...';
        }

        try {
            const body = { address_id: addressId, comment, items };
            if (addressData) body.address_data = addressData;

            const res = await fetch('php/api/orders.php?action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            console.log(data);

            if (data.success) {
                const modal = document.getElementById('cart-modal');
                if (modal) {
                    const p = modal.querySelector('p');
                    if (p) {
                        p.innerHTML = `Заказ №${data.order_id} успешно оформлен и будет доставлен по адресу:<br><strong>${data.address}</strong>.<br><br>Сумма: <strong>${parseFloat(data.total_price).toFixed(2)} ₽</strong><br><br>Мы свяжемся с вами для подтверждения.`;
                    }
                    modal.classList.add('active');
                }
                cart = [];
                saveCart();
                renderCart();
                setTimeout(() => {

            document.getElementById('checkout-modal')
                ?.classList.remove('active');

            document.body.style.overflow = '';

            closeCart();
            closeCheckout();

            }, 3000);
            } else {
                alert('Ошибка: ' + data.message);
            }
        }
        catch (err) {
            alert('Ошибка сети. Попробуйте позже.');
        }
        finally {
            const elapsed = Date.now() - startTime;
            if (elapsed < 3000) {
                await new Promise(resolve =>
                    setTimeout(resolve, 3000 - elapsed)
                );
            }

            checkoutConfirmBtn.disabled = false;
            checkoutConfirmBtn.textContent =
                'Подтвердить заказ';
            if (cartOrderBtn) {
                cartOrderBtn.disabled = false;
                cartOrderBtn.textContent =
                    'Перейти к оформлению';
            }
        }
    });
}

document.addEventListener('click', (e) => {
    if (
        e.target.classList.contains('cart-modal__btn') ||
        e.target.id === 'cart-modal'
    ) {
        const modal = document.getElementById('cart-modal');
        if (modal) {
            modal.classList.remove('active');
        }
    }
});

if (window.currentUser) {
    fetch('php/api/addresses.php?action=list')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.currentUser.addresses = data.addresses;
                renderCart();
            }
        })
        .catch(err => console.error('Error fetching addresses:', err));
}


async function initCart() {
    localStorage.removeItem('ogonyok_cart');

    if (window.currentUser) {
        try {
            const res = await fetch('php/api/cart.php?action=get');
            const data = await res.json();
            if (data.success) {
                cart = data.cart;
            } else {
                cart = [];
            }
        } catch (e) {
            console.error('Failed to load cart from DB, using fallback localStorage', e);
            cart = JSON.parse(localStorage.getItem('ogonyok_cart_' + window.currentUser.id)) || [];
        }
    } else {
        cart = JSON.parse(localStorage.getItem('ogonyok_cart_guest')) || [];
    }
    renderCart();

    if (localStorage.getItem('ogonyok_openCart') === 'true') {
        localStorage.removeItem('ogonyok_openCart');
        setTimeout(openCart, 300);
    }
}

window.clearClientCart = function() {
    cart = [];
    renderCart();
};

initCart();