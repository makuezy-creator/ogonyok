/**
 * Общие клиентские скрипты сайта ресторана «Огонёк».
 * Управляет бургер-меню, плавной прокруткой, поведением шапки при скролле и обновлением количества товаров в корзине.
 */

/* 1. Мобильное бургер-меню (открытие/закрытие и блокировка прокрутки страницы) */
const burger = document.querySelector('.burger');
const mobileMenu = document.querySelector('.mobile-menu');

if (burger) {
    burger.addEventListener('click', () => {
        burger.classList.toggle('active');
        mobileMenu.classList.toggle('active');
        document.body.classList.toggle('lock');
    });
}

/* Закрытие мобильного меню при клике по ссылкам навигации */
document.querySelectorAll('.mobile-nav a').forEach(link => {
    link.addEventListener('click', () => {
        burger.classList.remove('active');
        mobileMenu.classList.remove('active');
        document.body.classList.remove('lock');
    });
});

/* 2. Плавная прокрутка до якорных ссылок (например, #history) */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const target = document.querySelector(
            this.getAttribute('href')
        );

        if (!target) return;

        e.preventDefault();

        target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    });
});

/* 3. Изменение внешнего вида шапки (header) при прокрутке страницы вниз */
const header = document.querySelector('.header');

window.addEventListener('scroll', () => {
    if (window.scrollY > 30) {
        header.style.background = 'rgba(0,0,0,.98)';
        header.style.boxShadow = '0 5px 20px rgba(0,0,0,.4)';
    } else {
        header.style.background = 'rgba(0,0,0,.95)';
        header.style.boxShadow = 'none';
    }
});

/* 4. Обновление счетчика товаров в мобильной версии корзины */
function updateMobileCartCount() {
    const key = window.currentUser ? ('ogonyok_cart_' + window.currentUser.id) : 'ogonyok_cart_guest';
    const cart = JSON.parse(localStorage.getItem(key)) || [];
    const count = cart.reduce((sum, item) => sum + item.qty, 0);

    document.querySelectorAll('.mobile-cart__count').forEach(el => {
        el.textContent = count;
    });
}

document.addEventListener('DOMContentLoaded', updateMobileCartCount);

/* 5. Безопасная очистка сессионных данных в localStorage при разлогине */
document.addEventListener('DOMContentLoaded', () => {
    if (!window.currentUser) {
        localStorage.removeItem('ogonyok_currentUser');
    }
});