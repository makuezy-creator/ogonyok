<?php
/**
 * API загрузки файлов.
 * Позволяет администраторам загружать изображения блюд на сервер в папку images/.
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../config/db.php';
require_once '../handlers/session.php';

/* Доступ разрешен исключительно администраторам */
requireAdmin();

/* Проверка метода запроса (разрешен только POST) */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Недопустимый метод запроса']);
    exit;
}

/* Проверка на наличие ошибок при загрузке */
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Ошибка загрузки файла. Файл не выбран или превышен лимит размера.']);
    exit;
}

$file = $_FILES['image'];
$maxSize = 5 * 1024 * 1024; // Лимит: 5 MB
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

/* Проверка размера файла */
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'Размер файла превышает лимит 5 МБ']);
    exit;
}

/* Определение MIME-типа файла для безопасной проверки расширения */
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Недопустимый тип файла. Разрешены только JPG, PNG и WEBP']);
    exit;
}

/* Формирование уникального имени для загружаемого файла */
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
if (empty($ext)) {
    $ext = ($mimeType === 'image/png') ? 'png' : (($mimeType === 'image/webp') ? 'webp' : 'jpg');
}
$newFilename = md5(uniqid(microtime(), true)) . '.' . strtolower($ext);

$uploadDir = '../../images/';

/* Создание папки назначения, если она не существует */
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$destPath = $uploadDir . $newFilename;

/* Перемещение временного файла в папку назначения */
if (move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode([
        'success' => true,
        'message' => 'Изображение успешно загружено',
        'image_path' => 'images/' . $newFilename
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Не удалось сохранить файл на сервере']);
}
