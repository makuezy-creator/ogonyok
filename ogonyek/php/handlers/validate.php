<?php
date_default_timezone_set('Europe/Moscow');
/**
 * Валидатор входных данных.
 * Очищает данные и выполняет проверки формата почты, телефона, дат, времени и гостей.
 */

class Validate {
    /* Очистка данных от HTML тегов и пробелов для предотвращения XSS */
    public static function clean($data) {
        return htmlspecialchars(trim(strip_tags($data)), ENT_QUOTES, 'UTF-8');
    }
    /* Валидация адреса электронной почты */
    public static function email($email) {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /* Валидация российского номера телефона в формате +7 (XXX) XXX-XX-XX */
    public static function phone($phone) {
        return preg_match('/^\+7\s\(\d{3}\)\s\d{3}-\d{2}-\d{2}$/', trim($phone)) === 1;
    }
    
    /* Проверка длины строки */
    public static function string($str, $minLength = 1) {
        return mb_strlen(trim($str)) >= $minLength;
    }
    
    /* Проверка даты: дата не должна быть в прошлом */
    public static function bookingDate($dateStr) {
        $today = date('Y-m-d');
        return $dateStr >= $today;
    }
    
    /* Проверка времени: сверка со списком разрешённых слотов бронирования стола */
    public static function bookingTime($timeStr) {
        // Разрешённые слоты: с 11:00 до 22:00 включительно, каждый час
        $allowedSlots = ['11:00', '12:00', '13:00', '14:00', '15:00', '16:00',
                         '17:00', '18:00', '19:00', '20:00', '21:00', '22:00'];
        $time = date('H:i', strtotime($timeStr));
        return in_array($time, $allowedSlots);
    }
    
    /* Проверка количества гостей: должно быть в диапазоне от 1 до 20 */
    public static function guestsCount($count) {
        $val = intval($count);
        return $val >= 1 && $val <= 20;
    }
}
