/**
 * Клиентский скрипт для страницы интерактивного меню.
 * Реализует переключение вкладок категорий блюд, поиск по названию в реальном времени и добавление в корзину.
 */

/* 1. Переключение вкладок (табов) категорий меню */
const categoryButtons = document.querySelectorAll('.category-btn');
const categoryContents = document.querySelectorAll('.category-content');

categoryButtons.forEach(button => {
    button.addEventListener('click', () => {
        const targetCategory = button.dataset.category;

        /* Сброс активного класса у всех кнопок */
        categoryButtons.forEach(btn => {
            btn.classList.remove('active');
        });

        /* Скрытие всех блоков с блюдами */
        categoryContents.forEach(content => {
            content.classList.remove('active');
        });

        /* Активация нажатой кнопки и соответствующего ей блока */
        button.classList.add('active');

        const targetBlock = document.getElementById(targetCategory);
        if (targetBlock) {
            targetBlock.classList.add('active');
        }
    });
});

/* 2. Поиск по названию блюда в активной категории */
const searchInput = document.querySelector('.search input');

if (searchInput) {
    searchInput.addEventListener('input', () => {
        const value = searchInput.value.toLowerCase().trim();
        const activeCategory = document.querySelector('.category-content.active');

        if (!activeCategory) return;

        const cards = activeCategory.querySelectorAll('.product-card');

        /* Фильтрация карточек товаров по совпадению названия с поисковым запросом */
        cards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();

            if (title.includes(value)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
}

/* 3. Кнопка добавления товара в корзину с визуальным подтверждением */
const cartButtons = document.querySelectorAll('.add-cart');

cartButtons.forEach(button => {
    button.addEventListener('click', () => {
        const card = button.closest('.product-card');

        if (!card) return;

        /* Сбор данных о товаре из разметки карточки */
        const name = card.querySelector('h3').textContent.trim();
        const priceText = card.querySelector('.price').textContent.trim();
        const price = parseInt(priceText.replace(/[^\d]/g, ''));
        const imgEl = card.querySelector('.product-image img');
        const image = imgEl ? imgEl.getAttribute('src') : '';
        const productId = card.dataset.productId || null;

        /* Вызов глобальной функции добавления в корзину (из cart.js) */
        if (typeof addToCart === 'function') {
            addToCart(name, price, image, productId);
        }

        /* Анимация кнопки: временное изменение текста на "✓ Добавлено" */
        const originalHTML = button.innerHTML;

        button.innerHTML = '✓ Добавлено';
        button.style.background = '#ff7300';
        button.style.color = '#ffffff';

        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.style.background = '';
            button.style.color = '';
        }, 1200);
    });
});