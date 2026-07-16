<?php
/**
 * API блюд и товаров (позиций меню).
 * Позволяет администраторам добавлять новые блюда в меню и удалять/скрывать существующие.
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../handlers/session.php';
require_once '../handlers/validate.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? '';

if (in_array($action, ['add', 'delete', 'restore', 'update'])) {
    requireAdmin();

    switch ($action) {
        /* Добавление нового блюда в меню (только для администратора) */
        case 'add':
            $categoryId = intval($input['category_id'] ?? $_POST['category_id'] ?? 0);
            $name = Validate::clean($input['name'] ?? $_POST['name'] ?? '');
            $description = Validate::clean($input['description'] ?? $_POST['description'] ?? '');
            $price = floatval($input['price'] ?? $_POST['price'] ?? 0);
            $imagePath = Validate::clean($input['image_path'] ?? $_POST['image_path'] ?? 'images/steak-bg.png');

            if ($categoryId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Выберите корректную категорию']);
                exit;
            }
            if (!Validate::string($name, 2)) {
                echo json_encode(['success' => false, 'message' => 'Название блюда должно быть не менее 2 символов']);
                exit;
            }
            if ($price <= 0) {
                echo json_encode(['success' => false, 'message' => 'Цена должна быть больше нуля']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, image_path) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$categoryId, $name, $description, $price, $imagePath]);
                echo json_encode(['success' => true, 'message' => 'Блюдо успешно добавлено в меню']);
            } catch (\PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
            }
            break;


        /* Мягкое удаление блюда из меню */
        case 'delete':
            $id = intval($input['id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID блюда']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("UPDATE products SET deleted_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Блюдо успешно удалено']);
            } catch (\PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Ошибка удаления блюда: ' . $e->getMessage()]);
            }
            break;


        /* Восстановление мягко удаленного блюда */
        case 'restore':
            $id = intval($input['id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID блюда']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("UPDATE products SET deleted_at = NULL WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Блюдо успешно восстановлено в меню']);
            } catch (\PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Ошибка восстановления блюда: ' . $e->getMessage()]);
            }
            break;


        /* Редактирование блюда */
        case 'update':
            $id = intval($input['id'] ?? $_POST['id'] ?? 0);
            $categoryId = intval($input['category_id'] ?? $_POST['category_id'] ?? 0);
            $name = Validate::clean($input['name'] ?? $_POST['name'] ?? '');
            $description = Validate::clean($input['description'] ?? $_POST['description'] ?? '');
            $price = floatval($input['price'] ?? $_POST['price'] ?? 0);
            $imagePath = Validate::clean($input['image_path'] ?? $_POST['image_path'] ?? '');

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Неверный ID блюда']);
                exit;
            }
            if ($categoryId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Выберите корректную категорию']);
                exit;
            }
            if (!Validate::string($name, 2)) {
                echo json_encode(['success' => false, 'message' => 'Название блюда должно быть не менее 2 символов']);
                exit;
            }
            if ($price <= 0) {
                echo json_encode(['success' => false, 'message' => 'Цена должна быть больше нуля']);
                exit;
            }

            try {
                if (empty($imagePath)) {
                    $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ? WHERE id = ?");
                    $stmt->execute([$categoryId, $name, $description, $price, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, image_path = ? WHERE id = ?");
                    $stmt->execute([$categoryId, $name, $description, $price, $imagePath, $id]);
                }
                echo json_encode(['success' => true, 'message' => 'Блюдо успешно обновлено']);
            } catch (\PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
            }
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Некорректное действие']);
}