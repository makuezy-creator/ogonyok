<?php
/**
 * API аутентификации и управления профилем.
 * Обрабатывает регистрацию, вход, выход и изменение контактных данных.
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../handlers/session.php';
require_once '../handlers/validate.php';

/* Парсинг входящих данных JSON или POST */
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    /* Регистрация нового пользователя */
    case 'register':
        $name = Validate::clean($input['name'] ?? $_POST['name'] ?? '');
        $email = strtolower(trim($input['email'] ?? $_POST['email'] ?? ''));
        $phone = Validate::clean($input['phone'] ?? $_POST['phone'] ?? '');
        $password = $input['password'] ?? $_POST['password'] ?? '';

        if (!Validate::string($name, 2)) {
            echo json_encode(['success' => false, 'message' => 'Имя должно быть не менее 2 символов']);
            exit;
        }
        if (!Validate::email($email)) {
            echo json_encode(['success' => false, 'message' => 'Введите корректный E-mail']);
            exit;
        }
        if (!Validate::phone($phone)) {
            echo json_encode(['success' => false, 'message' => 'Введите телефон в формате +7 (XXX) XXX-XX-XX']);
            exit;
        }
        if (!Validate::string($password, 6)) {
            echo json_encode(['success' => false, 'message' => 'Пароль должен быть не менее 6 символов']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Пользователь с таким E-mail уже зарегистрирован']);
                exit;
            }

            /* Хэширование пароля перед записью в базу данных */
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->execute([$name, $email, $phone, $passwordHash]);
            $userId = $pdo->lastInsertId();

            $_SESSION['user'] = [
                'id' => (int)$userId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => 'user'
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Регистрация прошла успешно',
                'user' => $_SESSION['user']
            ]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
        break;

    /* Авторизация (вход) пользователя */
    case 'login':
        $email = strtolower(trim($input['email'] ?? $_POST['email'] ?? ''));
        $password = $input['password'] ?? $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            /* Проверка существования пользователя и совпадения хэша пароля */
            if (!$user || !password_verify($password, $user['password_hash'])) {
                echo json_encode(['success' => false, 'message' => 'Неверный E-mail или пароль']);
                exit;
            }

            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role']
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Вход выполнен успешно',
                'user' => $_SESSION['user']
            ]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
        break;

    /* Выход из учетной записи (сброс сессии) */
    case 'logout':
        unset($_SESSION['user']);
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Вы успешно вышли из аккаунта']);
        break;

    /* Обновление E-mail и телефона в профиле */
    case 'update_profile':
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $email = strtolower(trim($input['email'] ?? $_POST['email'] ?? ''));
        $phone = Validate::clean($input['phone'] ?? $_POST['phone'] ?? '');

        if (!Validate::email($email)) {
            echo json_encode(['success' => false, 'message' => 'Введите корректный E-mail']);
            exit;
        }
        if (!Validate::phone($phone)) {
            echo json_encode(['success' => false, 'message' => 'Введите телефон в формате +7 (XXX) XXX-XX-XX']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Пользователь с таким E-mail уже зарегистрирован']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE users SET email = ?, phone = ? WHERE id = ?");
            $stmt->execute([$email, $phone, $userId]);

            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;

            echo json_encode([
                'success' => true,
                'message' => 'Профиль успешно обновлен',
                'user' => $_SESSION['user']
            ]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Некорректное действие']);
        break;
}