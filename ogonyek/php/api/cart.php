<?php
/**
 * API корзины.
 * Синхронизирует состояние корзины пользователя между localStorage на клиенте и базой данных MySQL.
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../handlers/session.php';
require_once '../handlers/validate.php';

/* Проверка авторизации: корзина в БД доступна только вошедшим пользователям */
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

$user = getCurrentUser();
$userId = $user['id'];

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    /* Получение списка товаров в корзине пользователя */
    case 'get':
        try {
            $stmt = $pdo->prepare("
                SELECT c.product_id AS id, p.name, p.price, p.image_path AS image, c.quantity AS qty
                FROM cart_items c
                JOIN products p ON c.product_id = p.id
                WHERE c.user_id = ?
            ");
            $stmt->execute([$userId]);
            $items = $stmt->fetchAll();
            
            /* Преобразование типов данных для корректного парсинга в JS */
            foreach ($items as &$item) {
                $item['id'] = (int)$item['id'];
                $item['price'] = (float)$item['price'];
                $item['qty'] = (int)$item['qty'];
            }
            
            echo json_encode(['success' => true, 'cart' => $items]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
        break;

    /* Сохранение (перезапись) корзины пользователя в БД */
    case 'save':
        $cart = $input['cart'] ?? $_POST['cart'] ?? [];
        if (!is_array($cart)) {
            echo json_encode(['success' => false, 'message' => 'Некорректный формат корзины']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            /* Полная очистка предыдущей корзины пользователя */
            $deleteStmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $deleteStmt->execute([$userId]);

            /* Поочередная вставка новых элементов */
            if (!empty($cart)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO cart_items (user_id, product_id, quantity)
                    VALUES (?, ?, ?)
                ");
                foreach ($cart as $item) {
                    $productId = intval($item['id'] ?? 0);
                    $quantity = intval($item['qty'] ?? 0);
                    if ($productId > 0 && $quantity > 0) {
                        $insertStmt->execute([$userId, $productId, $quantity]);
                    }
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Корзина успешно синхронизирована']);
        } catch (\PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Ошибка сохранения корзины: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Некорректное действие']);
        break;
}
