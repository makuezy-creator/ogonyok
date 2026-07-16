<?php
/**
 * API адресов доставки.
 * Обрабатывает получение, добавление, обновление и удаление адресов доставки в профиле пользователя.
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../handlers/session.php';
require_once '../handlers/validate.php';

/* Все операции с адресами требуют авторизации пользователя */
requireLogin();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? '';
$user = getCurrentUser();
$userId = $user['id'];

switch ($action) {
    /* Получение списка всех сохраненных адресов пользователя */
    case 'list':
        try {
            $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY id DESC");
            $stmt->execute([$userId]);
            $addresses = $stmt->fetchAll();
            echo json_encode(['success' => true, 'addresses' => $addresses]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка получения адресов: ' . $e->getMessage()]);
        }
        break;

    /* Добавление нового адреса доставки */
    case 'add':
        $label = Validate::clean($input['label'] ?? $_POST['label'] ?? '');
        $street = Validate::clean($input['street'] ?? $_POST['street'] ?? '');
        $house = Validate::clean($input['house'] ?? $_POST['house'] ?? '');
        $entrance = Validate::clean($input['entrance'] ?? $_POST['entrance'] ?? null);
        $apartment = Validate::clean($input['apartment'] ?? $_POST['apartment'] ?? null);

        if (empty($label)) {
            $label = 'Адрес';
        }

        if (!Validate::string($street, 1) || !Validate::string($house, 1)) {
            echo json_encode(['success' => false, 'message' => 'Улица и Дом являются обязательными для заполнения']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, label, street, house, entrance, apartment) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $label, $street, $house, $entrance, $apartment]);
            $addressId = $pdo->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'Адрес успешно добавлен',
                'address_id' => (int)$addressId,
                'address' => [
                    'id' => (int)$addressId,
                    'user_id' => $userId,
                    'label' => $label,
                    'city' => 'Ухта',
                    'street' => $street,
                    'house' => $house,
                    'entrance' => $entrance,
                    'apartment' => $apartment
                ]
            ]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка сохранения адреса: ' . $e->getMessage()]);
        }
        break;

    /* Изменение существующего адреса доставки */
    case 'update':
        $id = intval($input['id'] ?? $_POST['id'] ?? 0);
        $label = Validate::clean($input['label'] ?? $_POST['label'] ?? '');
        $street = Validate::clean($input['street'] ?? $_POST['street'] ?? '');
        $house = Validate::clean($input['house'] ?? $_POST['house'] ?? '');
        $entrance = Validate::clean($input['entrance'] ?? $_POST['entrance'] ?? null);
        $apartment = Validate::clean($input['apartment'] ?? $_POST['apartment'] ?? null);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Неверный ID адреса']);
            exit;
        }

        if (empty($label)) {
            $label = 'Адрес';
        }

        if (!Validate::string($street, 1) || !Validate::string($house, 1)) {
            echo json_encode(['success' => false, 'message' => 'Улица и Дом являются обязательными для заполнения']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Адрес не найден или у вас нет прав на его изменение']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE user_addresses SET label = ?, street = ?, house = ?, entrance = ?, apartment = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$label, $street, $house, $entrance, $apartment, $id, $userId]);

            echo json_encode([
                'success' => true,
                'message' => 'Адрес успешно изменен',
                'address' => [
                    'id' => $id,
                    'user_id' => $userId,
                    'label' => $label,
                    'city' => 'Ухта',
                    'street' => $street,
                    'house' => $house,
                    'entrance' => $entrance,
                    'apartment' => $apartment
                ]
            ]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка изменения адреса: ' . $e->getMessage()]);
        }
        break;

    /* Удаление сохраненного адреса доставки */
    case 'delete':
        $id = intval($input['id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Неверный ID адреса']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            if (!$stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Адрес не найден или у вас нет прав на его удаление']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true, 'message' => 'Адрес успешно удален']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка удаления адреса: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Некорректное действие']);
        break;
}