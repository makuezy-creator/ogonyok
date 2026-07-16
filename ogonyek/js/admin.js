/**
 * Клиентский скрипт для панели администратора.
 * Управляет табами управления, пагинацией меню, кастомными селекторами, Chart.js графиками, drag-and-drop загрузкой.
 */

/* 1. Функция отображения всплывающих Toast-уведомлений (успех, ошибка, предупреждение) */
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;

    let iconSvg = '';
    if (type === 'success') {
        iconSvg = `<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>`;
    } else if (type === 'error') {
        iconSvg = `<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>`;
    } else if (type === 'warning') {
        iconSvg = `<svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>`;
    } else {
        iconSvg = `<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>`;
    }

    toast.innerHTML = `
        <div class="toast__icon">${iconSvg}</div>
        <div class="toast__body">${message}</div>
    `;

    container.appendChild(toast);

    setTimeout(() => toast.classList.add('active'), 10);

    setTimeout(() => {
        toast.classList.remove('active');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

/* 2. Кастомное модальное окно подтверждения действий администратора (Confirm Modal) */
let modalCallback = null;

function showConfirm(message, onConfirm, title = 'Подтверждение') {
    openModal(title, message, onConfirm);
}

function openModal(title, message, callback, options = {}) {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;

    const titleEl = document.getElementById('confirm-modal-title');
    const messageEl = document.getElementById('confirm-modal-message');
    const cancelBtn = document.getElementById('confirm-modal-cancel');
    const confirmBtn = document.getElementById('confirm-modal-confirm');

    if (titleEl) titleEl.textContent = title;
    if (messageEl) messageEl.textContent = message;

    if (cancelBtn) {
        cancelBtn.style.display = options.hideCancel ? 'none' : 'block';
    }

    if (confirmBtn) {
        confirmBtn.textContent = options.confirmText || 'Да, продолжить';
        confirmBtn.disabled = !!options.disabled;
    }

    modalCallback = callback;
    modal.classList.add('active');
}

function closeModal() {
    const modal = document.getElementById('confirm-modal');
    if (modal) modal.classList.remove('active');
    modalCallback = null;
}

// --- 3. Инициализация при загрузке DOM ---
document.addEventListener('DOMContentLoaded', () => {
    // Регистрация обработчиков для Единого Модального Окна
    const confirmCancelBtn = document.getElementById('confirm-modal-cancel');
    const confirmConfirmBtn = document.getElementById('confirm-modal-confirm');
    const confirmModal = document.getElementById('confirm-modal');

    if (confirmCancelBtn) {
        confirmCancelBtn.addEventListener('click', closeModal);
    }

    if (confirmConfirmBtn) {
        confirmConfirmBtn.addEventListener('click', () => {
            const currentCallback = modalCallback;
            closeModal();
            if (currentCallback) {
                currentCallback();
            }
        });
    }

    if (confirmModal) {
        confirmModal.addEventListener('click', (e) => {
            if (e.target === confirmModal) {
                closeModal();
            }
        });
    }

    // Восстановление активного таба
    const activeTab = localStorage.getItem('ogonyek_admin_tab') || 'dashboard-tab';
    if (document.getElementById(activeTab)) {
        switchTab(activeTab);
    }

    // Инициализация компонентов
    initMenuPagination();
    initCustomSelects();
    initCharts();
    initDragAndDrop();
    initAuthForms();
    initCategoriesLogic();
});

// --- 4. Переключение вкладок ---
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.nav-tab').forEach(btn => {
        btn.classList.remove('active');
    });

    const targetTab = document.getElementById(tabId);
    if (targetTab) {
        targetTab.classList.add('active');
    }

    const tabs = ['dashboard-tab', 'menu-tab', 'categories-tab', 'orders-tab', 'bookings-tab'];
    const index = tabs.indexOf(tabId);
    const buttons = document.querySelectorAll('.nav-tab');
    if (buttons[index]) {
        buttons[index].classList.add('active');
    }

    localStorage.setItem('ogonyek_admin_tab', tabId);
}

// --- 5. Загрузка изображений (Drag and Drop) ---
function initDragAndDrop() {
    const uploadZone = document.getElementById('file-upload-zone');
    const fileInput = document.getElementById('prod-image');
    const filePreview = document.getElementById('file-preview');
    const previewRemove = document.getElementById('file-preview-remove');

    if (!uploadZone || !fileInput) return;

    uploadZone.addEventListener('click', (e) => {
        if (e.target !== previewRemove && !previewRemove.contains(e.target) && !filePreview.contains(e.target)) {
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', function () {
        handleFileSelect(this.files[0]);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            uploadZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            uploadZone.classList.remove('dragover');
        }, false);
    });

    uploadZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    }, false);

    if (previewRemove) {
        previewRemove.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.value = '';
            filePreview.classList.remove('active');
            const previewImg = document.getElementById('file-preview-img');
            if (previewImg) previewImg.src = '';
        });
    }
}

function handleFileSelect(file) {
    if (!file) return;

    const fileInput = document.getElementById('prod-image');
    const filePreview = document.getElementById('file-preview');
    const previewImg = document.getElementById('file-preview-img');
    const previewName = document.getElementById('file-preview-name');
    const previewSize = document.getElementById('file-preview-size');

    if (!file.type.startsWith('image/')) {
        showToast('Пожалуйста, выберите изображение!', 'error');
        if (fileInput) fileInput.value = '';
        return;
    }

    if (previewName) previewName.textContent = file.name;
    const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
    if (previewSize) previewSize.textContent = `${sizeInMb} МБ`;

    const reader = new FileReader();
    reader.onload = function (e) {
        if (previewImg) previewImg.src = e.target.result;
        if (filePreview) filePreview.classList.add('active');
    };
    reader.readAsDataURL(file);
}

// --- 6. Выход и Формы Меню ---
function initAuthForms() {
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            showConfirm('Вы действительно хотите выйти из панели управления?', () => {
                localStorage.removeItem('ogonyok_currentUser');
                if (typeof window.clearClientCart === 'function') {
                    window.clearClientCart();
                }
                fetch('php/api/auth.php?action=logout')
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'index.php';
                        }
                    });
            }, 'Выход из панели');
        });
    }

    const addProductForm = document.getElementById('add-product-form');
    if (addProductForm) {
        addProductForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const id = document.getElementById('prod-id').value;
            const action = id ? 'update' : 'add';
            const imageInput = document.getElementById('prod-image');

            function submitProductData(imagePath) {
                const productData = {
                    id: id ? parseInt(id) : null,
                    category_id: parseInt(document.getElementById('prod-category').value),
                    name: document.getElementById('prod-name').value,
                    price: parseFloat(document.getElementById('prod-price').value),
                    description: document.getElementById('prod-desc').value,
                    image_path: imagePath
                };

                fetch(`php/api/products.php?action=${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(productData)
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showToast(id ? 'Блюдо успешно обновлено!' : 'Блюдо успешно добавлено в меню!', 'success');
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            showToast('Ошибка: ' + data.message, 'error');
                        }
                    })
                    .catch(err => {
                        showToast('Ошибка сервера: ' + err.message, 'error');
                    });
            }

            if (imageInput.files.length > 0) {
                const imgFormData = new FormData();
                imgFormData.append('image', imageInput.files[0]);

                fetch('php/api/upload.php', {
                    method: 'POST',
                    body: imgFormData
                })
                    .then(res => res.json())
                    .then(uploadData => {
                        if (!uploadData.success) {
                            throw new Error(uploadData.message || 'Ошибка загрузки изображения');
                        }
                        submitProductData(uploadData.image_path);
                    })
                    .catch(err => {
                        showToast(err.message, 'error');
                    });
            } else {
                if (id) {
                    const existingImage = document.getElementById('prod-existing-image').value;
                    submitProductData(existingImage);
                } else {
                    showToast('Пожалуйста, выберите изображение блюда', 'warning');
                }
            }
        });
    }

    const prodCancelBtn = document.getElementById('prod-cancel-btn');
    if (prodCancelBtn) {
        prodCancelBtn.addEventListener('click', function () {
            document.getElementById('prod-id').value = '';
            document.getElementById('add-product-form').reset();
            document.getElementById('prod-existing-image').value = '';
            document.getElementById('prod-image').setAttribute('required', 'required');
            
            // Reset category custom select
            const selectWrapper = document.getElementById('category-select-wrapper');
            if (selectWrapper) {
                const triggerText = selectWrapper.querySelector('.custom-select-trigger-text');
                const options = selectWrapper.querySelectorAll('.custom-select-option');
                options.forEach(opt => opt.classList.remove('selected'));
                if (options[0]) options[0].classList.add('selected');
                if (triggerText) triggerText.textContent = 'Выберите категорию...';
            }
            document.getElementById('prod-category').value = '';
            
            // Reset image preview
            const filePreview = document.getElementById('file-preview');
            const previewImg = document.getElementById('file-preview-img');
            if (filePreview) filePreview.classList.remove('active');
            if (previewImg) previewImg.src = '';
            
            document.getElementById('product-form-title').textContent = 'Добавить новое блюдо';
            document.getElementById('prod-submit-btn').textContent = 'Добавить в меню';
            this.style.display = 'none';
        });
    }
}

function editProduct(btn) {
    const row = btn.closest('tr');
    const id = row.getAttribute('data-id');
    const name = row.getAttribute('data-name');
    const categoryId = row.getAttribute('data-category-id');
    const price = row.getAttribute('data-price');
    const desc = row.getAttribute('data-desc');
    const image = row.getAttribute('data-image');

    document.getElementById('prod-id').value = id;
    document.getElementById('prod-name').value = name;
    document.getElementById('prod-price').value = price;
    document.getElementById('prod-desc').value = desc;
    document.getElementById('prod-existing-image').value = image;

    // Set custom select category value
    const selectWrapper = document.getElementById('category-select-wrapper');
    if (selectWrapper) {
        const triggerText = selectWrapper.querySelector('.custom-select-trigger-text');
        const options = selectWrapper.querySelectorAll('.custom-select-option');
        let categoryName = 'Выберите категорию...';
        
        options.forEach(opt => {
            opt.classList.remove('selected');
            if (opt.getAttribute('data-value') == categoryId) {
                opt.classList.add('selected');
                categoryName = opt.textContent;
            }
        });
        if (triggerText) triggerText.textContent = categoryName;
    }
    document.getElementById('prod-category').value = categoryId;

    // Show image preview
    const filePreview = document.getElementById('file-preview');
    const previewImg = document.getElementById('file-preview-img');
    const previewName = document.getElementById('file-preview-name');
    const previewSize = document.getElementById('file-preview-size');
    
    if (image) {
        if (previewImg) previewImg.src = image;
        if (previewName) previewName.textContent = image.split('/').pop();
        if (previewSize) previewSize.textContent = 'Существующее';
        if (filePreview) filePreview.classList.add('active');
    } else {
        if (filePreview) filePreview.classList.remove('active');
        if (previewImg) previewImg.src = '';
    }

    // Set image input not required for editing
    const prodImgInput = document.getElementById('prod-image');
    if (prodImgInput) prodImgInput.removeAttribute('required');

    document.getElementById('product-form-title').textContent = 'Редактировать блюдо';
    document.getElementById('prod-submit-btn').textContent = 'Сохранить изменения';

    const prodCancelBtn = document.getElementById('prod-cancel-btn');
    if (prodCancelBtn) prodCancelBtn.style.display = 'inline-block';

    const prodFormCard = document.getElementById('product-form-card');
    if (prodFormCard) prodFormCard.scrollIntoView({ behavior: 'smooth' });
}

function deleteProduct(id) {
    showConfirm('Вы действительно хотите удалить это блюдо из меню?', () => {
        fetch(`php/api/products.php?action=delete&id=${id}`, {
            method: 'POST'
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    showToast('Ошибка: ' + data.message, 'error');
                }
            });
    }, 'Удаление блюда');
}

// --- 7. Изменение статусов заказов и бронирований ---
function updateOrderStatus(id, status) {
    fetch('php/api/orders.php?action=update_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Статус заказа #' + id + ' успешно обновлен!', 'success');
            } else {
                showToast('Ошибка: ' + data.message, 'error');
            }
        });
}

function updateBookingStatus(id, status) {
    fetch('php/api/booking.php?action=update_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Статус бронирования успешно обновлен!', 'success');
            } else {
                showToast('Ошибка: ' + data.message, 'error');
            }
        });
}

// --- 8. Пагинация меню ---
const itemsPerPage = 6;
let currentMenuPage = 1;

function initMenuPagination() {
    const table = document.getElementById('products-table');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const totalItems = rows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);

    function showPage(page) {
        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;
        currentMenuPage = page;

        const startIdx = (page - 1) * itemsPerPage;
        const endIdx = startIdx + itemsPerPage;

        rows.forEach((row, idx) => {
            if (idx >= startIdx && idx < endIdx) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        renderPaginationControls();
    }

    function renderPaginationControls() {
        const pagesContainer = document.getElementById('menu-pagination-pages');
        const prevBtn = document.getElementById('menu-prev-btn');
        const nextBtn = document.getElementById('menu-next-btn');

        if (!pagesContainer) return;

        pagesContainer.innerHTML = '';

        const paginationContainer = document.getElementById('menu-pagination');
        if (totalPages <= 1) {
            if (paginationContainer) paginationContainer.style.display = 'none';
            return;
        } else {
            if (paginationContainer) paginationContainer.style.display = 'flex';
        }

        if (prevBtn) prevBtn.disabled = currentMenuPage === 1;
        if (nextBtn) nextBtn.disabled = currentMenuPage === totalPages;

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `pagination-page-btn ${i === currentMenuPage ? 'active' : ''}`;
            btn.textContent = i;
            btn.addEventListener('click', () => showPage(i));
            pagesContainer.appendChild(btn);
        }
    }

    const prevBtn = document.getElementById('menu-prev-btn');
    const nextBtn = document.getElementById('menu-next-btn');

    if (prevBtn) {
        prevBtn.onclick = () => {
            if (currentMenuPage > 1) showPage(currentMenuPage - 1);
        };
    }

    if (nextBtn) {
        nextBtn.onclick = () => {
            if (currentMenuPage < totalPages) showPage(currentMenuPage + 1);
        };
    }

    showPage(currentMenuPage);
}

// --- 9. Кастомные выпадающие списки (Select) ---
function initCustomSelects() {
    // Общий обработчик открытия/закрытия для всех селектов
    document.querySelectorAll('.custom-select-container').forEach(container => {
        const trigger = container.querySelector('.custom-select-trigger');
        const triggerText = container.querySelector('.custom-select-trigger-text');
        const options = container.querySelectorAll('.custom-select-option');

        if (trigger) {
            trigger.onclick = (e) => {
                e.stopPropagation();
                document.querySelectorAll('.custom-select-container').forEach(other => {
                    if (other !== container) other.classList.remove('open');
                });
                container.classList.toggle('open');
            };
        }

        options.forEach(option => {
            option.onclick = (e) => {
                e.stopPropagation();
                const val = option.getAttribute('data-value');
                const text = option.textContent;

                options.forEach(o => o.classList.remove('selected'));
                option.classList.add('selected');

                if (triggerText) triggerText.textContent = text;

                // Обработка логики в зависимости от класса/типа селекта
                if (container.classList.contains('status-select-container')) {
                    const type = container.getAttribute('data-type');
                    const id = parseInt(container.getAttribute('data-id'));
                    container.setAttribute('data-status', val);

                    if (type === 'order') {
                        updateOrderStatus(id, val);
                    } else if (type === 'booking') {
                        updateBookingStatus(id, val);
                    }
                } else if (container.classList.contains('category-select-container')) {
                    const hiddenInput = document.getElementById('prod-category');
                    if (hiddenInput) {
                        hiddenInput.value = val;
                        hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                        hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }

                container.classList.remove('open');
            };
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-select-container').forEach(c => c.classList.remove('open'));
    });
}

/* 10. Построение аналитических графиков Chart.js (динамика продаж, топ блюд) */
function initCharts() {
    const salesDynamicsData = window.salesDynamicsData;
    const topDishesData = window.topDishesData;

    const salesCtx = document.getElementById('salesDynamicsChart');
    if (salesCtx && salesDynamicsData) {
        const dates = Object.keys(salesDynamicsData);
        const values = Object.values(salesDynamicsData);
        const ctx = salesCtx.getContext('2d');

        const gradient = ctx.createLinearGradient(0, 0, 0, 250);
        gradient.addColorStop(0, 'rgba(253, 101, 0, 0.4)');
        gradient.addColorStop(1, 'rgba(253, 101, 0, 0.0)');

        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Выручка (₽)',
                    data: values,
                    borderColor: '#FD6500',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#FD6500',
                    pointBorderColor: '#141414',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(20, 20, 20, 0.95)',
                        titleFont: { family: 'Poppins', size: 12 },
                        bodyFont: { family: 'Poppins', size: 13, weight: 'bold' },
                        borderColor: 'rgba(253, 101, 0, 0.3)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return context.parsed.y.toLocaleString('ru-RU') + ' ₽';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: { color: '#a0a0a0', font: { family: 'Poppins', size: 11 } }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: {
                            color: '#a0a0a0',
                            font: { family: 'Poppins', size: 11 },
                            callback: function (value) {
                                return value.toLocaleString('ru-RU') + ' ₽';
                            }
                        }
                    }
                }
            }
        });
    }

    const topCtx = document.getElementById('topDishesChart');
    if (topCtx && topDishesData) {
        const labels = topDishesData.map(d => d.name);
        const values = topDishesData.map(d => d.qty);

        new Chart(topCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Продано порций',
                    data: values,
                    backgroundColor: '#FD6500',
                    hoverBackgroundColor: '#e05600',
                    borderRadius: 5,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(20, 20, 20, 0.95)',
                        titleFont: { family: 'Poppins', size: 12 },
                        bodyFont: { family: 'Poppins', size: 13, weight: 'bold' },
                        borderColor: 'rgba(253, 101, 0, 0.3)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return 'Продано: ' + context.parsed.x + ' шт.';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: { color: '#a0a0a0', font: { family: 'Poppins', size: 11 }, precision: 0 }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#f3f3f3', font: { family: 'Poppins', size: 12, weight: '500' } }
                    }
                }
            }
        });
    }
}

// --- 11. Логика управления категориями ---
function initCategoriesLogic() {
    const catNameInput = document.getElementById('cat-name');
    const catSlugInput = document.getElementById('cat-slug');
    if (catNameInput && catSlugInput) {
        catNameInput.addEventListener('input', function () {
            const text = this.value;
            catSlugInput.value = generateSlugJS(text);
        });
    }

    const catForm = document.getElementById('category-form');
    if (catForm) {
        catForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('cat-id').value;
            const name = document.getElementById('cat-name').value;
            const slug = document.getElementById('cat-slug').value;
            const sortOrder = document.getElementById('cat-sort').value;

            const action = id ? 'update' : 'add';
            const payload = { id: parseInt(id), name, slug, sort_order: parseInt(sortOrder) };

            fetch(`php/api/categories.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(err => {
                    showToast('Ошибка сервера: ' + err.message, 'error');
                });
        });
    }

    const catCancelBtn = document.getElementById('cat-cancel-btn');
    if (catCancelBtn) {
        catCancelBtn.addEventListener('click', function () {
            document.getElementById('cat-id').value = '';
            document.getElementById('category-form').reset();
            document.getElementById('category-form-title').textContent = 'Добавить новую категорию';
            document.getElementById('cat-submit-btn').textContent = 'Добавить категорию';
            this.style.display = 'none';
        });
    }
}

function generateSlugJS(str) {
    const rus = 'а б в г д е ё ж з и й к л м н о п р с т у ф х ц ч ш щ ъ ы ь э ю я'.split(' ');
    const lat = 'a b v g d e io zh z i y k l m n o p r s t u f h ts ch sh sht  y  e yu ya'.split(' ');
    let text = str.toLowerCase();
    for (let i = 0; i < rus.length; i++) {
        text = text.split(rus[i]).join(lat[i] || '');
    }
    text = text.replace(/[^a-z0-9\s-]/g, '');
    text = text.replace(/[\s-]+/g, '-');
    return text.trim().replace(/^-+|-+$/g, '');
}

function editCategory(btn) {
    const row = btn.closest('tr');
    const id = row.getAttribute('data-id');
    const name = row.getAttribute('data-name');
    const slug = row.getAttribute('data-slug');
    const sort = row.getAttribute('data-sort');

    document.getElementById('cat-id').value = id;
    document.getElementById('cat-name').value = name;
    document.getElementById('cat-slug').value = slug;
    document.getElementById('cat-sort').value = sort;

    document.getElementById('category-form-title').textContent = 'Редактировать категорию';
    document.getElementById('cat-submit-btn').textContent = 'Сохранить изменения';

    const catCancelBtn = document.getElementById('cat-cancel-btn');
    if (catCancelBtn) catCancelBtn.style.display = 'inline-block';

    const catForm = document.getElementById('category-form');
    if (catForm) catForm.scrollIntoView({ behavior: 'smooth' });
}

function deleteCategory(id) {
    showConfirm('Вы действительно хотите удалить эту категорию? В ней не должно быть блюд.', () => {
        fetch(`php/api/categories.php?action=delete&id=${id}`, {
            method: 'POST'
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(err => {
                showToast('Ошибка при удалении: ' + err.message, 'error');
            });
    }, 'Удаление категории');
}

// --- 12. Кастомные диалоговые модальные окна (Modal Dialogs) ---
// Удалена старая избыточная реализация, её заменяет объединенный Confirm Modal с функциями openModal/closeModal/showConfirm.

function resetOrders() {
    openModal(
        'Архивация заказов',
        'Перенести все заказы в архив?',
        () => {
            openModal(
                'Готово',
                'Заказы переносятся в архив...',
                null,
                {
                    hideCancel: true,
                    confirmText: 'Архивируется...',
                    disabled: true
                }
            );

            fetch('php/api/admin.php?action=archive_orders', {
                method: 'POST'
            })
                .then(res => res.json())
                .then(data => {
                    setTimeout(() => {
                        openModal(
                            'Готово',
                            data.message,
                            () => location.reload(),
                            {
                                hideCancel: true,
                                confirmText: 'Отлично'
                            }
                        );
                    }, 3000);
                });
        }
    );
}

function resetBookings() {
    openModal(
        'Архивация бронирований',
        'Перенести все бронирования в архив?',
        () => {
            openModal(
                'Готово',
                'Бронирования переносятся в архив...',
                null,
                {
                    hideCancel: true,
                    confirmText: 'Архивируется...',
                    disabled: true
                }
            );

            fetch('php/api/admin.php?action=archive_bookings', {
                method: 'POST'
            })
                .then(res => res.json())
                .then(data => {
                    setTimeout(() => {
                        openModal(
                            'Готово',
                            data.message,
                            () => location.reload(),
                            {
                                hideCancel: true,
                                confirmText: 'Отлично'
                            }
                        );
                    }, 3000);
                });
        }
    );
}

function restoreProduct(id) {
    showConfirm('Вы действительно хотите восстановить это блюдо в меню?', () => {
        fetch(`php/api/products.php?action=restore&id=${id}`, {
            method: 'POST'
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    showToast('Ошибка: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('Ошибка при восстановлении: ' + err.message, 'error');
            });
    }, 'Восстановление блюда');
}

function restoreCategory(id) {
    showConfirm('Вы действительно хотите восстановить эту категорию меню?', () => {
        fetch(`php/api/categories.php?action=restore&id=${id}`, {
            method: 'POST'
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    showToast('Ошибка: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('Ошибка при восстановлении: ' + err.message, 'error');
            });
    }, 'Восстановление категории');
}

