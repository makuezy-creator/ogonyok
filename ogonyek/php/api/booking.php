<?php
/**
 * API бронирования столов.
 * Обрабатывает создание броней, получение доступных слотов времени и управление статусами (для админа).
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../handlers/session.php';
require_once '../handlers/validate.php';

/* Парсинг входящих параметров */
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? 'create';

/* 1. Создание новой брони стола */
if ($action === 'create') {

    $guestName   = Validate::clean($input['name']         ?? $_POST['name']         ?? '');
    $phone       = Validate::clean($input['phone']        ?? $_POST['phone']        ?? '');
    $email       = Validate::clean($input['email']        ?? $_POST['email']        ?? null);
    $bookingDate = Validate::clean($input['booking_date'] ?? $_POST['booking_date'] ?? $input['date'] ?? '');
    $bookingTime = Validate::clean($input['booking_time'] ?? $_POST['booking_time'] ?? $input['time'] ?? '');
    $guestsCount = intval($input['guests_count']          ?? $_POST['guests_count'] ?? $input['guests'] ?? 1);
    $comment     = Validate::clean($input['comment']      ?? $_POST['comment']      ?? null);

    if (!Validate::string($guestName, 2)) {
        echo json_encode(['success' => false, 'message' => 'Имя должно быть не менее 2 символов']);
        exit;
    }

    if (!Validate::phone($phone)) {
        echo json_encode(['success' => false, 'message' => 'Введите телефон в формате +7 (XXX) XXX-XX-XX']);
        exit;
    }

    if ($email && !Validate::email($email)) {
        echo json_encode(['success' => false, 'message' => 'Введите корректный E-mail']);
        exit;
    }

    if (!Validate::bookingDate($bookingDate)) {
        echo json_encode(['success' => false, 'message' => 'Нельзя забронировать столик на прошедшую дату']);
        exit;
    }

    if (!Validate::bookingTime($bookingTime)) {
        echo json_encode(['success' => false, 'message' => 'Время бронирования должно быть с 11:00 до 22:00 (целые часы)']);
        exit;
    }

    // Если дата бронирования совпадает с текущим днем, проверяем, что время не прошло
    if ($bookingDate === date('Y-m-d')) {
        $slotTime = date('H:i', strtotime($bookingTime)) . ':00';
        $currentTime = date('H:i:s');
        if ($slotTime <= $currentTime) {
            echo json_encode(['success' => false, 'message' => 'Нельзя забронировать столик на прошедшее время']);
            exit;
        }
    }

    if (!Validate::guestsCount($guestsCount)) {
        echo json_encode(['success' => false, 'message' => 'Количество гостей должно быть от 1 до 20 человек']);
        exit;
    }

    try {
        $slotTime = date('H:i', strtotime($bookingTime)) . ':00';
        $stmtCheck = $pdo->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE booking_date = ? AND booking_time = ? AND status != 'cancelled'"
        );
        $stmtCheck->execute([$bookingDate, $slotTime]);
        $existingCount = (int)$stmtCheck->fetchColumn();

        if ($existingCount >= 10) {
            echo json_encode(['success' => false, 'message' => 'Выбранное время уже полностью занято. Пожалуйста, выберите другой слот.']);
            exit;
        }
    } catch (\PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка проверки доступности: ' . $e->getMessage()]);
        exit;
    }

    $userId = isLoggedIn() ? getCurrentUser()['id'] : null;

    try {
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, guest_name, phone, email, booking_date, booking_time, guests_count, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $guestName, $phone, $email, $bookingDate, $bookingTime, $guestsCount, $comment]);
        echo json_encode([
            'success' => true, 
            'message' => 'Столик успешно забронирован! Мы свяжемся с вами в ближайшее время для подтверждения.'
        ]);
    } catch (\PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }

/* 2. Получение списка бронирований текущего авторизованного пользователя */
} elseif ($action === 'get_user_bookings') {
    requireLogin();

    $userId = getCurrentUser()['id'];

    try {
        $stmt = $pdo->prepare(
            "SELECT id, guest_name, phone, email, booking_date, booking_time, guests_count, comment, status, created_at
             FROM bookings
             WHERE user_id = ?
             ORDER BY booking_date DESC, booking_time DESC"
        );
        $stmt->execute([$userId]);
        $bookings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($bookings as &$b) {
            $b['created_at_formatted'] = date('d.m.Y', strtotime($b['created_at']));
            $b['booking_date_formatted'] = date('d.m.Y', strtotime($b['booking_date']));
            $b['booking_time_formatted'] = substr($b['booking_time'], 0, 5); // HH:MM
        }
        unset($b);

        echo json_encode(['success' => true, 'bookings' => $bookings]);
    } catch (\PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка загрузки бронирований: ' . $e->getMessage()]);
    }

/* 3. Обновление статуса бронирования (для панели администратора) */
} elseif ($action === 'update_status') {
    requireAdmin();

    $id = intval($input['id'] ?? $_POST['id'] ?? 0);
    $status = Validate::clean($input['status'] ?? $_POST['status'] ?? '');

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Неверный ID бронирования']);
        exit;
    }

    if (!in_array($status, ['confirmed', 'cancelled', 'pending'])) {
        echo json_encode(['success' => false, 'message' => 'Недопустимый статус']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(['success' => true, 'message' => 'Статус бронирования успешно обновлен']);
    } catch (\PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка обновления статуса: ' . $e->getMessage()]);
    }

/* 4. Получение свободных слотов времени на выбранную дату */
} elseif ($action === 'get_available_slots') {

    $date = Validate::clean($input['date'] ?? $_GET['date'] ?? '');
    if (!$date || !Validate::bookingDate($date)) {
        echo json_encode(['success' => false, 'message' => 'Некорректная дата']);
        exit;
    }

    $allSlots = [
        '11:00:00', '12:00:00', '13:00:00', '14:00:00',
        '15:00:00', '16:00:00', '17:00:00', '18:00:00',
        '19:00:00', '20:00:00', '21:00:00', '22:00:00'
    ];

    try {
        $stmt = $pdo->prepare(
            "SELECT booking_time, COUNT(*) as cnt
             FROM bookings
             WHERE booking_date = ? AND status != 'cancelled'
             GROUP BY booking_time"
        );
        $stmt->execute([$date]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $booked = [];
        foreach ($rows as $row) {
            $booked[$row['booking_time']] = (int)$row['cnt'];
        }

        $maxPerSlot = 10;

        $slots = [];
        $isToday = ($date === date('Y-m-d'));
        $currentTime = date('H:i:s');

        foreach ($allSlots as $slotTime) {
            $cnt = $booked[$slotTime] ?? 0;
            
            $isPast = false;
            if ($isToday && $slotTime <= $currentTime) {
                $isPast = true;
            }

            $slots[] = [
                'time'      => substr($slotTime, 0, 5),
                'available' => !$isPast && ($cnt < $maxPerSlot),
                'booked'    => $cnt,
                'max'       => $maxPerSlot,
            ];
        }

        echo json_encode(['success' => true, 'slots' => $slots]);
    } catch (\PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка получения слотов: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Некорректное действие']);
}