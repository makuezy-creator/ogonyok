/**
 * Клиентский сценарий авторизации, регистрации и управления профилем.
 * Управляет формами входа/регистрации, валидацией, личным кабинетом, адресами доставки и заказами.
 */

document.addEventListener('DOMContentLoaded', () => {
    updateNavbarUser();

    if (window.currentUser) {
        const container = document.querySelector('.auth-container');
        if (container) container.classList.add('profile-mode');
    }

    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const wrapper = btn.closest('.password-wrapper');
            const input = wrapper.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            }
        });
    });

    /* Маска ввода телефонного номера в формате +7 (XXX) XXX-XX-XX */
    const phoneInputs = document.querySelectorAll('#register-phone, #profile-edit-phone');
    phoneInputs.forEach(phoneInput => {
        phoneInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('7') || value.startsWith('8')) {
                value = value.substring(1);
            }
            let formatted = '+7 ';
            if (value.length > 0) formatted += '(' + value.substring(0, 3);
            if (value.length >= 4) formatted += ') ' + value.substring(3, 6);
            if (value.length >= 7) formatted += '-' + value.substring(6, 8);
            if (value.length >= 9) formatted += '-' + value.substring(8, 10);
            e.target.value = value.length === 0 ? '' : formatted;
        });
        phoneInput.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && phoneInput.value.length <= 4) {
                phoneInput.value = '';
            }
        });
    });

    /* Обработка формы регистрации пользователя с клиентской валидацией */
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            let isValid = true;

            const nameEl = document.getElementById('register-name');
            const emailEl = document.getElementById('register-email');
            const phoneEl = document.getElementById('register-phone');
            const passEl = document.getElementById('register-password');
            const confirmEl = document.getElementById('register-confirm');

            registerForm.querySelectorAll('.form-group').forEach(g => {
                g.classList.remove('invalid');
                const err = g.querySelector('.error-message');
                if (err) err.textContent = '';
            });

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneRegex = /^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/;

            if (nameEl.value.trim().length < 2) {
                showFieldError(nameEl, 'Имя должно быть не менее 2 символов');
                isValid = false;
            }
            if (!emailRegex.test(emailEl.value.trim())) {
                showFieldError(emailEl, 'Введите корректный E-mail');
                isValid = false;
            }
            if (!phoneRegex.test(phoneEl.value)) {
                showFieldError(phoneEl, 'Введите телефон в формате +7 (XXX) XXX-XX-XX');
                isValid = false;
            }
            if (passEl.value.length < 6) {
                showFieldError(passEl, 'Пароль должен быть не менее 6 символов');
                isValid = false;
            }
            if (confirmEl.value !== passEl.value) {
                showFieldError(confirmEl, 'Пароли не совпадают');
                isValid = false;
            }

            if (!isValid) {
                registerForm.querySelectorAll('.form-group.invalid').forEach(g => shakeGroup(g));
                return;
            }

            try {
                const res = await fetch('php/api/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: nameEl.value.trim(),
                        email: emailEl.value.trim().toLowerCase(),
                        phone: phoneEl.value,
                        password: passEl.value
                    })
                });
                const data = await res.json();

                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    showFieldError(emailEl, data.message || 'Ошибка регистрации');
                    shakeGroup(emailEl.parentElement);
                }
            } catch (err) {
                alert('Ошибка сети. Попробуйте позже.');
            }
        });
    }

    /* Обработка формы авторизации (входа) с AJAX-запросом к API */
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            let isValid = true;

            const emailEl = document.getElementById('login-email');
            const passEl = document.getElementById('login-password');

            loginForm.querySelectorAll('.form-group').forEach(g => {
                g.classList.remove('invalid');
                const err = g.querySelector('.error-message');
                if (err) err.textContent = '';
            });

            if (!emailEl.value.trim()) {
                showFieldError(emailEl, 'Введите E-mail');
                isValid = false;
            }
            if (!passEl.value) {
                showFieldError(passEl, 'Введите пароль');
                isValid = false;
            }

            if (!isValid) {
                loginForm.querySelectorAll('.form-group.invalid').forEach(g => shakeGroup(g));
                return;
            }

            try {
                const res = await fetch('php/api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: emailEl.value.trim().toLowerCase(),
                        password: passEl.value
                    })
                });
                const data = await res.json();

                if (data.success) {
                    if (data.user && data.user.role === 'admin') {
                        window.location.href = 'admin.php';
                    } else {
                        window.location.href = 'index.php';
                    }
                } else {
                    showFieldError(emailEl, data.message || 'Неверный E-mail или пароль');
                    showFieldError(passEl, data.message || 'Неверный E-mail или пароль');
                    loginForm.querySelectorAll('.form-group.invalid').forEach(g => shakeGroup(g));
                }
            } catch (err) {
                alert('Ошибка сети. Попробуйте позже.');
            }
        });
    }

    const addressesListEl = document.getElementById('profile-addresses-list');
    const ordersListEl = document.getElementById('profile-orders-list');
    const addressForm = document.getElementById('profile-address-form');
    const addAddressToggleBtn = document.getElementById('add-address-toggle-btn');
    const addressCancelBtn = document.getElementById('profile-address-cancel-btn');
    const addressFormTitle = document.getElementById('address-form-title');

    if (window.currentUser) {
        renderProfileAddresses();
        renderProfileOrders();
        renderProfileBookings();

        const nameEl = document.getElementById('profile-name');
        const emailEl = document.getElementById('profile-email');
        const phoneEl = document.getElementById('profile-phone');
        const avatarEl = document.getElementById('profile-avatar');
        if (nameEl) nameEl.textContent = window.currentUser.name;
        if (emailEl) emailEl.textContent = window.currentUser.email;
        if (phoneEl) phoneEl.textContent = window.currentUser.phone || 'телефон не указан';
        if (avatarEl) avatarEl.textContent = window.currentUser.name.charAt(0).toUpperCase();

        const editBtn = document.getElementById('edit-profile-btn');
        const cancelBtn = document.getElementById('cancel-profile-btn');
        const infoView = document.getElementById('profile-info-view');
        const infoEdit = document.getElementById('profile-info-edit');
        const editEmailInput = document.getElementById('profile-edit-email');
        const editPhoneInput = document.getElementById('profile-edit-phone');

        if (editBtn && infoView && infoEdit) {
            editBtn.addEventListener('click', () => {
                infoView.style.display = 'none';
                infoEdit.style.display = 'flex';
                if (editEmailInput) editEmailInput.value = window.currentUser.email || '';
                if (editPhoneInput) editPhoneInput.value = window.currentUser.phone || '';

                infoEdit.querySelectorAll('.form-group').forEach(g => {
                    g.classList.remove('invalid');
                    const err = g.querySelector('.error-message');
                    if (err) err.textContent = '';
                });
            });
        }

        if (cancelBtn && infoView && infoEdit) {
            cancelBtn.addEventListener('click', () => {
                infoEdit.style.display = 'none';
                infoView.style.display = 'flex';
                infoEdit.reset();
            });
        }

        if (infoEdit) {
            infoEdit.addEventListener('submit', async (e) => {
                e.preventDefault();
                let isValid = true;

                infoEdit.querySelectorAll('.form-group').forEach(g => {
                    g.classList.remove('invalid');
                    const err = g.querySelector('.error-message');
                    if (err) err.textContent = '';
                });

                const emailVal = editEmailInput.value.trim().toLowerCase();
                const phoneVal = editPhoneInput.value.trim();

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                const phoneRegex = /^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/;

                if (!emailRegex.test(emailVal)) {
                    showFieldError(editEmailInput, 'Введите корректный E-mail');
                    isValid = false;
                }
                if (!phoneRegex.test(phoneVal)) {
                    showFieldError(editPhoneInput, 'Введите телефон в формате +7 (XXX) XXX-XX-XX');
                    isValid = false;
                }

                if (!isValid) {
                    infoEdit.querySelectorAll('.form-group.invalid').forEach(g => shakeGroup(g));
                    return;
                }

                try {
                    const res = await fetch('php/api/auth.php?action=update_profile', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email: emailVal, phone: phoneVal })
                    });
                    const data = await res.json();

                    if (data.success) {
                        window.currentUser = data.user;

                        if (emailEl) emailEl.textContent = window.currentUser.email;
                        if (phoneEl) phoneEl.textContent = window.currentUser.phone || 'телефон не указан';

                        infoEdit.style.display = 'none';
                        infoView.style.display = 'flex';

                        updateNavbarUser();
                    } else {
                        showFieldError(editEmailInput, data.message || 'Ошибка обновления профиля');
                        shakeGroup(editEmailInput.parentElement);
                    }
                } catch (err) {
                    alert('Ошибка сети. Попробуйте позже.');
                }
            });
        }
    }

    if (addAddressToggleBtn && addressForm) {
        addAddressToggleBtn.addEventListener('click', () => {
            if (addressForm.style.display === 'none') {
                addressForm.reset();
                addressForm.style.display = 'flex';
                addressForm.dataset.editId = '';
                if (addressFormTitle) addressFormTitle.textContent = 'Добавить новый адрес';
                addAddressToggleBtn.textContent = 'Свернуть форму';
            } else {
                addressForm.style.display = 'none';
                addAddressToggleBtn.textContent = '+ Добавить адрес';
            }
        });
    }

    if (addressCancelBtn && addressForm) {
        addressCancelBtn.addEventListener('click', () => {
            addressForm.style.display = 'none';
            if (addAddressToggleBtn) addAddressToggleBtn.textContent = '+ Добавить адрес';
        });
    }

    if (addressForm) {
        addressForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const label = document.getElementById('profile-address-label').value.trim();
            const street = document.getElementById('profile-street').value.trim();
            const house = document.getElementById('profile-house').value.trim();
            const entrance = document.getElementById('profile-entrance').value.trim();
            const apartment = document.getElementById('profile-apartment').value.trim();

            const editIdStr = addressForm.dataset.editId;
            const action = editIdStr ? 'update' : 'add';
            const body = { label, street, house, entrance, apartment };
            if (editIdStr) body.id = parseInt(editIdStr);

            try {
                const res = await fetch(`php/api/addresses.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(body)
                });
                const data = await res.json();

                if (data.success) {
                    addressForm.reset();
                    addressForm.style.display = 'none';
                    if (addAddressToggleBtn) addAddressToggleBtn.textContent = '+ Добавить адрес';
                    renderProfileAddresses();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            } catch (err) {
                alert('Ошибка сети.');
            }
        });
    }

    async function renderProfileAddresses() {
        if (!addressesListEl) return;
        try {
            const res = await fetch('php/api/addresses.php?action=list');
            const data = await res.json();
            if (!data.success) {
                addressesListEl.innerHTML = '<p class="profile-orders-empty">Ошибка загрузки адресов</p>';
                return;
            }
            const addresses = data.addresses;
            if (addresses.length === 0) {
                addressesListEl.innerHTML = '<p class="profile-orders-empty">У вас нет сохраненных адресов</p>';
                return;
            }
            addressesListEl.innerHTML = addresses.map(addr => {
                let fullStr = `ул. ${addr.street}, д. ${addr.house}`;
                if (addr.entrance) fullStr += `, под. ${addr.entrance}`;
                if (addr.apartment) fullStr += `, кв./офис ${addr.apartment}`;
                return `
                    <div class="profile-address-card" data-id="${addr.id}">
                        <div class="profile-address-card__info">
                            <span class="profile-address-card__label">${addr.label || 'Адрес'}</span>
                            <div class="profile-address-card__text">${fullStr}</div>
                        </div>
                        <div class="profile-address-card__actions">
                            <button type="button" class="profile-address-card__btn profile-address-card__btn--edit" onclick="editProfileAddress(${addr.id}, '${escapeAttr(addr.label)}', '${escapeAttr(addr.street)}', '${escapeAttr(addr.house)}', '${escapeAttr(addr.entrance)}', '${escapeAttr(addr.apartment)}')">Ред.</button>
                            <button type="button" class="profile-address-card__btn profile-address-card__btn--delete" onclick="deleteProfileAddress(${addr.id})">Удал.</button>
                        </div>
                    </div>
                `;
            }).join('');
        } catch (err) {
            addressesListEl.innerHTML = '<p class="profile-orders-empty">Ошибка соединения</p>';
        }
    }

    async function renderProfileOrders() {
        if (!ordersListEl) return;
        try {
            const res = await fetch('php/api/orders.php?action=list');
            const data = await res.json();
            if (!data.success) {
                ordersListEl.innerHTML = '<p class="profile-orders-empty">Ошибка загрузки заказов</p>';
                return;
            }
            const orders = data.orders;
            if (orders.length === 0) {
                ordersListEl.innerHTML = '<p class="profile-orders-empty">У вас нет прошлых заказов</p>';
                return;
            }
            const statusMap = {
                'pending': 'Принят',
                'processing': 'Готовится',
                'completed': 'Доставлен',
                'cancelled': 'Отменён'
            };
            ordersListEl.innerHTML = orders.map(ord => {
                const itemsList = ord.items.map(item => `
                    <li>
                        <span>${item.name} x ${item.qty}</span>
                        <span>${(item.price * item.qty).toFixed(2)} ₽</span>
                    </li>
                `).join('');
                const status = statusMap[ord.status] || ord.status;
                return `
                    <div class="profile-order-card">
                        <div class="profile-order-card__header">
                            <div class="profile-order-card__meta">
                                <h4>Заказ #${ord.id}</h4>
                                <span>${ord.date}</span>
                            </div>
                            <div class="profile-order-card__total">
                                <div class="price">${parseFloat(ord.total_price).toFixed(2)} ₽</div>
                                <div class="status">${status}</div>
                            </div>
                        </div>
                        <div class="profile-order-card__details">
                            <ul class="profile-order-card__items">${itemsList}</ul>
                            <div class="profile-order-card__address">
                                <strong>Адрес:</strong> ${ord.address}
                                ${ord.comment ? `<br><strong>Примечание:</strong> "${ord.comment}"` : ''}
                            </div>
                        </div>
                        <button type="button" class="profile-order-repeat-btn" onclick="repeatProfileOrder(${ord.id})">Повторить заказ</button>
                    </div>
                `;
            }).join('');
        } catch (err) {
            ordersListEl.innerHTML = '<p class="profile-orders-empty">Ошибка соединения</p>';
        }
    }

    async function renderProfileBookings() {
        const bookingsListEl = document.getElementById('profile-bookings-list');
        if (!bookingsListEl) return;
        try {
            const res = await fetch('php/api/booking.php?action=get_user_bookings');
            const data = await res.json();
            if (!data.success) {
                bookingsListEl.innerHTML = '<p class="profile-orders-empty">Ошибка загрузки бронирований</p>';
                return;
            }
            const bookings = data.bookings;
            if (bookings.length === 0) {
                bookingsListEl.innerHTML = `
                    <div class="profile-bookings-empty">
                        <p>У вас нет бронирований столов</p>
                    </div>
                `;
                return;
            }
            const statusMap = {
                'pending': { label: 'Ожидает', color: '#f59e0b', bg: 'rgba(245, 158, 11, 0.1)' },
                'confirmed': { label: 'Подтверждено', color: '#00ca72', bg: 'rgba(0, 202, 114, 0.1)' },
                'cancelled': { label: 'Отменено', color: '#ff4d4d', bg: 'rgba(255, 77, 77, 0.1)' }
            };
            bookingsListEl.innerHTML = bookings.map(b => {
                const st = statusMap[b.status] || { label: b.status, color: '#878787', bg: 'rgba(135,135,135,0.1)' };
                const guestsWord = b.guests_count === 1 ? 'гость' : (b.guests_count < 5 ? 'гостя' : 'гостей');
                return `
                    <div class="profile-booking-card">
                        <div class="profile-booking-card__header">
                            <div class="profile-booking-card__meta">
                                <h4>Бронь #${b.id}</h4>
                                <span>Создано: ${b.created_at_formatted}</span>
                            </div>
                            <span class="profile-booking-card__status" style="color:${st.color}; background:${st.bg}">${st.label}</span>
                        </div>
                        <div class="profile-booking-card__details">
                            <div class="profile-booking-card__row">
                                <span class="profile-booking-card__icon"><img src="./icons/calendar.svg" alt="Календарь"></span>
                                <span>${b.booking_date_formatted} в ${b.booking_time_formatted}</span>
                            </div>
                            <div class="profile-booking-card__row">
                                <span class="profile-booking-card__icon"><img src="./icons/users.svg" alt="Гости"></span>
                                <span>${b.guests_count} ${guestsWord}</span>
                            </div>
                            ${b.comment ? `
                            <div class="profile-booking-card__row">
                                <span class="profile-booking-card__icon"><img src="./icons/comment.svg" alt="Комментарий"></span>
                                <span class="profile-booking-card__comment">${b.comment}</span>
                            </div>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        } catch (err) {
            const bookingsListEl2 = document.getElementById('profile-bookings-list');
            if (bookingsListEl2) bookingsListEl2.innerHTML = '<p class="profile-orders-empty">Ошибка соединения</p>';
        }
    }
});

window.editProfileAddress = function (id, label, street, house, entrance, apartment) {
    const addressForm = document.getElementById('profile-address-form');
    if (!addressForm) return;
    document.getElementById('profile-address-label').value = label || '';
    document.getElementById('profile-street').value = street || '';
    document.getElementById('profile-house').value = house || '';
    document.getElementById('profile-entrance').value = entrance || '';
    document.getElementById('profile-apartment').value = apartment || '';
    addressForm.style.display = 'flex';
    addressForm.dataset.editId = id;
    const title = document.getElementById('address-form-title');
    if (title) title.textContent = 'Редактировать адрес';
    const addBtn = document.getElementById('add-address-toggle-btn');
    if (addBtn) addBtn.textContent = 'Свернуть форму';
    addressForm.scrollIntoView({ behavior: 'smooth' });
};

window.deleteProfileAddress = function (id) {
    showConfirm('Вы уверены, что хотите удалить этот адрес?', async () => {
        try {
            const res = await fetch(`php/api/addresses.php?action=delete&id=${id}`, { method: 'POST' });
            const data = await res.json();
            if (data.success) {
                document.querySelector(`.profile-address-card[data-id="${id}"]`)?.remove();
                const remaining = document.querySelectorAll('.profile-address-card');
                if (remaining.length === 0) {
                    const listEl = document.getElementById('profile-addresses-list');
                    if (listEl) listEl.innerHTML = '<p class="profile-orders-empty">У вас нет сохраненных адресов</p>';
                }
            } else {
                alert('Ошибка: ' + data.message);
            }
        } catch (err) {
            alert('Ошибка сети.');
        }
    }, 'Удаление адреса');
};

window.repeatProfileOrder = async function (orderId) {
    try {
        const res = await fetch('php/api/orders.php?action=list');
        const data = await res.json();
        if (!data.success) return;
        const order = data.orders.find(o => o.id === orderId);
        if (!order) return;

        let newCart = order.items.map(item => ({
            id: item.product_id,
            name: item.name,
            price: parseFloat(item.price),
            image: item.image || 'images/steak-bg.png',
            qty: item.qty
        }));

        if (window.currentUser) {
            localStorage.setItem('ogonyok_cart_' + window.currentUser.id, JSON.stringify(newCart));
            try {
                await fetch('php/api/cart.php?action=save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cart: newCart })
                });
            } catch (e) {
                console.error('Error syncing repeated cart to DB:', e);
            }
        } else {
            localStorage.setItem('ogonyok_cart_guest', JSON.stringify(newCart));
        }
        localStorage.setItem('ogonyok_openCart', 'true');
        window.location.href = 'menu.php';
    } catch (err) {
        alert('Ошибка повторения заказа.');
    }
};

function escapeAttr(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function showFieldError(inputEl, message) {
    const group = inputEl.closest('.form-group');
    if (!group) return;
    group.classList.add('invalid');
    const err = group.querySelector('.error-message');
    if (err) err.textContent = message;
}

function shakeGroup(group) {
    group.classList.remove('shake-animation');
    void group.offsetWidth;
    group.classList.add('shake-animation');
}

window.triggerLogoutFlow = function () {
    showConfirm('Вы уверены, что хотите выйти из аккаунта?', async () => {
        localStorage.removeItem('ogonyek_currentUser');
        if (typeof window.clearClientCart === 'function') {
            window.clearClientCart();
        }
        try {
            await fetch('php/api/auth.php?action=logout');
        } catch (e) { }
        window.location.href = 'index.php';
    }, 'Выход из аккаунта');
};

function updateNavbarUser() {
    const currentUser = window.currentUser;
    const headerActions = document.querySelector('.header__actions');
    const mobileNav = document.querySelector('.mobile-nav');

    if (currentUser) {
        if (headerActions) {
            const loginBtn = headerActions.querySelector('.login-btn');
            if (loginBtn) {
                loginBtn.remove();
            }

            headerActions.querySelector('.profile-link-btn')?.remove();
            headerActions.querySelector('.logout-btn-header')?.remove();

            const profileUrl = currentUser.role === 'admin' ? 'admin.php' : 'login.php';
            const profileLink = document.createElement('a');
            profileLink.href = profileUrl;
            profileLink.className = 'profile-link-btn';
            profileLink.innerHTML = `<span>${escapeHtml(currentUser.name)}</span>`;

            const logoutBtn = document.createElement('button');
            logoutBtn.className = 'logout-btn-header';
            logoutBtn.id = 'header-logout-btn';
            logoutBtn.innerHTML = `
                <img src="./icons/login.svg" alt="Выйти">
                <span>Выйти</span>
            `;

            headerActions.appendChild(profileLink);
            headerActions.appendChild(logoutBtn);

            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.triggerLogoutFlow();
            });
        }

        if (mobileNav) {
            const mobileLogin = mobileNav.querySelector('.mobile-login');
            if (mobileLogin) {
                mobileLogin.remove();
            }
            mobileNav.querySelector('.mobile-profile')?.remove();
            mobileNav.querySelector('.mobile-logout')?.remove();

            const profileUrl = currentUser.role === 'admin' ? 'admin.php' : 'login.php';
            const profileLink = document.createElement('a');
            profileLink.href = profileUrl;
            profileLink.className = 'mobile-profile';
            profileLink.textContent = currentUser.name;

            const logoutLink = document.createElement('a');
            logoutLink.href = '#';
            logoutLink.className = 'mobile-logout';
            logoutLink.id = 'mobile-logout-btn';
            logoutLink.textContent = 'Выйти';

            mobileNav.appendChild(profileLink);
            mobileNav.appendChild(logoutLink);

            logoutLink.addEventListener('click', (e) => {
                e.preventDefault();
                window.triggerLogoutFlow();
            });
        }
    } else {
        if (headerActions) {
            headerActions.querySelector('.profile-link-btn')?.remove();
            headerActions.querySelector('.logout-btn-header')?.remove();
            if (!headerActions.querySelector('.login-btn')) {
                const loginBtn = document.createElement('a');
                loginBtn.href = 'login.php';
                loginBtn.className = 'login-btn';
                loginBtn.innerHTML = `
                    <img src="./icons/login.svg" alt="Вход">
                    <span>Войти</span>
                `;
                headerActions.appendChild(loginBtn);
            }
        }
        if (mobileNav) {
            mobileNav.querySelector('.mobile-profile')?.remove();
            mobileNav.querySelector('.mobile-logout')?.remove();
            if (!mobileNav.querySelector('.mobile-login')) {
                const mobileLogin = document.createElement('a');
                mobileLogin.href = 'login.php';
                mobileLogin.className = 'mobile-login';
                mobileLogin.textContent = 'Войти';
                mobileNav.appendChild(mobileLogin);
            }
        }
    }
}

function escapeHtml(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    if (modal) {
        modal.classList.remove('active');
    }
    confirmCallback = null;
}

function acceptConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    if (modal) {
        modal.classList.remove('active');
    }
    if (confirmCallback) {
        confirmCallback();
        confirmCallback = null;
    }
}

window.showConfirm = function (message, onConfirm, title = 'Подтверждение') {
    let modal = document.getElementById('confirm-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.className = 'confirm-modal';
        modal.id = 'confirm-modal';
        modal.innerHTML = `
            <div class="confirm-dialog">
                <div class="confirm-dialog__header">
                    <div class="confirm-dialog__icon">
                        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    </div>
                    <div class="confirm-dialog__title" id="confirm-modal-title"></div>
                </div>
                <div class="confirm-dialog__body" id="confirm-modal-message"></div>
                <div class="confirm-dialog__footer">
                    <button type="button" class="confirm-dialog__btn confirm-dialog__btn--cancel" id="confirm-modal-cancel">Отмена</button>
                    <button type="button" class="confirm-dialog__btn confirm-dialog__btn--confirm" id="confirm-modal-confirm">Да, продолжить</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        const cancelBtn = modal.querySelector('#confirm-modal-cancel');
        const confirmBtn = modal.querySelector('#confirm-modal-confirm');

        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeConfirmModal);
        }
        if (confirmBtn) {
            confirmBtn.addEventListener('click', acceptConfirmModal);
        }
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeConfirmModal();
            }
        });
    }

    const titleEl = modal.querySelector('#confirm-modal-title');
    const msgEl = modal.querySelector('#confirm-modal-message');
    if (titleEl) titleEl.textContent = title;
    if (msgEl) msgEl.textContent = message;

    void modal.offsetWidth;
    modal.classList.add('active');
    confirmCallback = onConfirm;
};

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;

    const cancelBtn = modal.querySelector('#confirm-modal-cancel');
    const confirmBtn = modal.querySelector('#confirm-modal-confirm');

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeConfirmModal);
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', acceptConfirmModal);
    }

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeConfirmModal();
        }
    });
});