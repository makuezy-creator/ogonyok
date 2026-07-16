<?php
/**
 * API администратора.
 * Позволяет выполнять служебные административные действия, такие как архивация заказов и бронирований.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../handlers/session.php';

// Проверка прав администратора
requireAdmin();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        /* Архивация всех заказов (запись времени архивации) */
        case 'archive_orders':
            $pdo->exec("UPDATE orders SET archived_at = NOW() WHERE archived_at IS NULL");
            echo json_encode([
                'success' => true,
                'message' => 'Заказы перенесены в архив'
            ]);
            break;

        /* Архивация всех бронирований (запись времени архивации) */
        case 'archive_bookings':
            $pdo->exec("UPDATE bookings SET archived_at = NOW() WHERE archived_at IS NULL");
            echo json_encode([
                'success' => true,
                'message' => 'Бронирования перенесены в архив'
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Некорректное или неизвестное действие'
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка сервера: ' . $e->getMessage()
    ]);
}
