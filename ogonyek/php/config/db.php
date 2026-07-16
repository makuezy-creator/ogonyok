<?php
/**
 * Конфигурация и подключение к базе данных MySQL через PDO.
 */

// Параметры подключения к MySQL
$host    = getenv('DB_HOST')    ?: 'db';
$db      = getenv('DB_NAME')    ?: 'ogonyok_db';
$user    = getenv('DB_USER')    ?: 'ogonyok';
$pass    = getenv('DB_PASS')    ?: '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
 
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Ошибка подключения к БД']));
}
