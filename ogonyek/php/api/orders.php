<?php
/**
 * API заказов доставки.
 * Позволяет оформлять новые заказы, получать историю заказов и обновлять статусы (для админа).
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../handlers/session.php';
require_once '../handlers/validate.php';

/* Все действия требуют авторизации пользователя */
requireLogin();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? '';
$user = getCurrentUser();
$userId = $user['id'];

switch ($action) {
    /* Оформление нового заказа с автоматической транзакцией в БД */
    case 'create':
        $addressId = $input['address_id'] ?? $_POST['address_id'] ?? '';
        $comment = Validate::clean($input['comment'] ?? $_POST['comment'] ?? '');
        $items = $input['items'] ?? $_POST['items'] ?? [];

        if (empty($items) || !is_array($items)) {
            echo json_encode(['success' => false, 'message' => 'Корзина пуста']);
            exit;
        }

        $fullAddress = '';
        
        $pdo->beginTransaction();

        try {
            if ($addressId === 'new') {
                $addrData = $input['address_data'] ?? [];
                $label = Validate::clean($addrData['label'] ?? 'Адрес');
                $street = Validate::clean($addrData['street'] ?? '');
                $house = Validate::clean($addrData['house'] ?? '');
                $entrance = Validate::clean($addrData['entrance'] ?? '');
                $apartment = Validate::clean($addrData['apartment'] ?? '');
                $saveToProfile = filter_var($addrData['save_to_profile'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if (!Validate::string($street, 1) || !Validate::string($house, 1)) {
                    echo json_encode(['success' => false, 'message' => 'Улица и Дом обязательны для заполнения адреса']);
                    $pdo->rollBack();
                    exit;
                }

                $fullAddress = "г. Ухта, ул. {$street}, д. {$house}";
                if (!empty($entrance)) $fullAddress .= ", под. {$entrance}";
                if (!empty($apartment)) $fullAddress .= ", кв./офис {$apartment}";

                if ($saveToProfile) {
                    $addrStmt = $pdo->prepare("INSERT INTO user_addresses (user_id, label, street, house, entrance, apartment) VALUES (?, ?, ?, ?, ?, ?)");
                    $addrStmt->execute([$userId, $label, $street, $house, $entrance, $apartment]);
                }
            } else {
                $addrIdVal = intval($addressId);
                $addrStmt = $pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
                $addrStmt->execute([$addrIdVal, $userId]);
                $addressRow = $addrStmt->fetch();

                if (!$addressRow) {
                    echo json_encode(['success' => false, 'message' => 'Указанный адрес доставки не найден']);
                    $pdo->rollBack();
                    exit;
                }

                $fullAddress = "г. Ухта, ул. {$addressRow['street']}, д. {$addressRow['house']}";
                if (!empty($addressRow['entrance'])) $fullAddress .= ", под. {$addressRow['entrance']}";
                if (!empty($addressRow['apartment'])) $fullAddress .= ", кв./офис {$addressRow['apartment']}";
            }

            $totalPrice = 0;
            $validatedItems = [];

            foreach ($items as $item) {
                $productId = intval($item['product_id'] ?? 0);
                $quantity = intval($item['quantity'] ?? 0);

                if ($productId <= 0 || $quantity <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Некорректный состав корзины']);
                    $pdo->rollBack();
                    exit;
                }

                $prodStmt = $pdo->prepare("SELECT id, price, name, deleted_at FROM products WHERE id = ?");
                $prodStmt->execute([$productId]);
                $product = $prodStmt->fetch();

                if (!$product || $product['deleted_at'] !== null) {
                    echo json_encode(['success' => false, 'message' => "Товар с ID {$productId} недоступен или удален"]);
                    $pdo->rollBack();
                    exit;
                }

                $price = floatval($product['price']);
                $totalPrice += $price * $quantity;

                $validatedItems[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'name' => $product['name']
                ];
            }

            $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, status, total_price, delivery_address) VALUES (?, 'pending', ?, ?)");
            $orderStmt->execute([$userId, $totalPrice, $fullAddress]);
            $orderId = $pdo->lastInsertId();

            $itemInsertStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
            foreach ($validatedItems as $vItem) {
                $itemInsertStmt->execute([$orderId, $vItem['product_id'], $vItem['name'], $vItem['quantity'], $vItem['price']]);
            }

            if (!empty($comment)) {
                $addressWithComment = $fullAddress . " (Примечание: " . $comment . ")";
                $updateCommentStmt = $pdo->prepare("UPDATE orders SET delivery_address = ? WHERE id = ?");
                $updateCommentStmt->execute([$addressWithComment, $orderId]);
            }

            // Clear the user's cart in database upon checkout completion
            $clearCartStmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $clearCartStmt->execute([$userId]);

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Заказ успешно оформлен!',
                'order_id' => (int)$orderId,
                'total_price' => $totalPrice,
                'address' => $fullAddress
            ]);
        } catch (\PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Ошибка создания заказа: ' . $e->getMessage()]);
        }
        break;

    /* Получение списка заказов текущего пользователя для личного кабинета */
    case 'list':
        try {
            $ordersStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
            $ordersStmt->execute([$userId]);
            $orders = $ordersStmt->fetchAll();

            $result = [];
            foreach ($orders as $order) {
                $itemsStmt = $pdo->prepare("
                    SELECT oi.*, COALESCE(oi.product_name, p.name) AS name, p.image_path 
                    FROM order_items oi 
                    LEFT JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?
                ");
                $itemsStmt->execute([$order['id']]);
                $items = $itemsStmt->fetchAll();

                $address = $order['delivery_address'];
                $comment = '';
                if (preg_match('/\(Примечание: (.*)\)$/', $address, $matches)) {
                    $comment = $matches[1];
                    $address = trim(str_replace($matches[0], '', $address));
                }

                $result[] = [
                    'id' => (int)$order['id'],
                    'date' => date('d.m.Y H:i', strtotime($order['created_at'])),
                    'status' => $order['status'],
                    'total_price' => floatval($order['total_price']),
                    'address' => $address,
                    'comment' => $comment,
                    'items' => array_map(function($it) {
                        return [
                            'product_id' => (int)$it['product_id'],
                            'name' => $it['name'],
                            'price' => floatval($it['price']),
                            'image' => $it['image_path'],
                            'qty' => (int)$it['quantity']
                        ];
                    }, $items)
                ];
            }

            echo json_encode(['success' => true, 'orders' => $result]);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка получения заказов: ' . $e->getMessage()]);
        }
        break;

    /* Изменение статуса заказа (доступно только администратору) */
    case 'update_status':
        requireAdmin();
        $id = intval($input['id'] ?? $_POST['id'] ?? 0);
        $status = Validate::clean($input['status'] ?? $_POST['status'] ?? '');

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Неверный ID заказа']);
            exit;
        }

        if (!in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
            echo json_encode(['success' => false, 'message' => 'Недопустимый статус']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true, 'message' => 'Статус заказа успешно обновлен']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка обновления статуса: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Некорректное действие']);
        break;
}