<?php
date_default_timezone_set('Europe/Moscow');
/**
 * Обработчик сессий PHP.
 * Инициализирует сессию и предоставляет функции проверки авторизации и ролей.
 */

/* Безопасный запуск сессии, если она еще не была запущена */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Проверяет, вошел ли пользователь в систему */
function isLoggedIn() {
    return isset($_SESSION['user']);
}

/* Возвращает массив данных текущего пользователя или null */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/* Требует авторизацию. Если запрос AJAX — возвращает JSON с кодом 401, иначе редиректит на вход */
function requireLogin() {
    if (!isLoggedIn()) {
        if (
            (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
            (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
        ) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
            exit;
        } else {
            header('Location: login.php');
            exit;
        }
    }
}

/* Проверяет, является ли пользователь администратором */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

/* Требует прав администратора. Если запрос AJAX — возвращает JSON с кодом 403, иначе редиректит на вход */
function requireAdmin() {
    if (!isAdmin()) {
        if (
            (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || 
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
            (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
        ) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
            exit;
        } else {
            header('Location: login.php');
            exit;
        }
    }
}
