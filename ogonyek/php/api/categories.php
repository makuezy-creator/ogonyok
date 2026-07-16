<?php
/**
 * API категорий меню.
 * Позволяет администраторам управлять категориями меню (добавление, редактирование, удаление).
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../handlers/session.php';
require_once '../handlers/validate.php';

/* Все действия с категориями требуют прав администратора */
requireAdmin();

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    /* Добавление новой категории меню */
    case 'add':
        $name = Validate::clean($input['name'] ?? $_POST['name'] ?? '');
        $slug = Validate::clean($input['slug'] ?? $_POST['slug'] ?? '');
        $sortOrder = intval($input['sort_order'] ?? $_POST['sort_order'] ?? 0);

        if (!Validate::string($name, 2)) {
            echo json_encode(['success' => false, 'message' => 'Название категории должно быть не менее 2 символов']);
            exit;
        }

        if (empty($slug)) {
            $slug = generateSlug($name);
        } else {
            $slug = strtolower($slug);
        }

        if (!preg_match('/^[a-z0-9-_]+$/', $slug)) {
            echo json_encode(['success' => false, 'message' => 'Ярлык (slug) может содержать только латинские буквы, цифры, дефисы и подчеркивания']);
            exit;
        }

        try {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
            $checkStmt->execute([$slug]);
            if ($checkStmt->fetchColumn() > 0) {
                $slug = $slug . '-' . time();
            }

            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $sortOrder]);
            echo json_encode(['success' => true, 'message' => 'Категория успешно создана']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
        break;

    /* Редактирование существующей категории меню */
    case 'update':
        $id = intval($input['id'] ?? $_POST['id'] ?? 0);
        $name = Validate::clean($input['name'] ?? $_POST['name'] ?? '');
        $slug = Validate::clean($input['slug'] ?? $_POST['slug'] ?? '');
        $sortOrder = intval($input['sort_order'] ?? $_POST['sort_order'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Некорректный ID категории']);
            exit;
        }

        if (!Validate::string($name, 2)) {
            echo json_encode(['success' => false, 'message' => 'Название категории должно быть не менее 2 символов']);
            exit;
        }

        if (empty($slug)) {
            $slug = generateSlug($name);
        } else {
            $slug = strtolower($slug);
        }

        if (!preg_match('/^[a-z0-9-_]+$/', $slug)) {
            echo json_encode(['success' => false, 'message' => 'Ярлык (slug) может содержать только латинские буквы, цифры, дефисы и подчеркивания']);
            exit;
        }

        try {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = ? AND id != ?");
            $checkStmt->execute([$slug, $id]);
            if ($checkStmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Этот ярлык (slug) уже используется другой категорией']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $sortOrder, $id]);
            echo json_encode(['success' => true, 'message' => 'Категория успешно обновлена']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
        break;

    /* Удаление категории меню (с обязательной предварительной проверкой на наличие активных привязанных блюд) */
    case 'delete':
        $id = intval($input['id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Некорректный ID категории']);
            exit;
        }

        try {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND deleted_at IS NULL");
            $checkStmt->execute([$id]);
            $productsCount = intval($checkStmt->fetchColumn());

            if ($productsCount > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => "Нельзя удалить категорию, так как в ней содержатся активные блюда ({$productsCount} шт.). Сначала удалите или переместите блюда из этой категории."
                ]);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE categories SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Категория успешно удалена']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка удаления категории: ' . $e->getMessage()]);
        }
        break;

    /* Восстановление мягко удаленной категории меню */
    case 'restore':
        $id = intval($input['id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Некорректный ID категории']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE categories SET deleted_at = NULL WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Категория успешно восстановлена']);
        } catch (\PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Ошибка восстановления категории: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Некорректное действие']);
        break;
}
function generateSlug($str) {
    $rus = [
        'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
        'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'
    ];
    $lat = [
        'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', '', 'y', '', 'e', 'yu', 'ya',
        'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', '', 'y', '', 'e', 'yu', 'ya'
    ];
    $text = str_replace($rus, $lat, $str);
    $text = preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return strtolower(trim($text, '-'));
}