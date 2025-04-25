<?php
header('Content-Type: application/json');

$filename = 'addresses.txt';
$logfile = 'log.txt';

// Функция для логирования действий
function logAction($message) {
    global $logfile;
    date_default_timezone_set('Europe/Moscow'); // Устанавливаем московское время
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($logfile, $timestamp . ' ' . $message . PHP_EOL, FILE_APPEND);
}

if (isset($_POST['route_start']) && isset($_POST['route_end'])) {
    logAction("Построен маршрут от {$_POST['route_start']} до {$_POST['route_end']}");
}
// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $coords = $_POST['coords'] ?? '';

    // Валидация координат
    $isValidCoords = preg_match('/^-?\d+\.?\d*,-?\d+\.?\d*$/', $coords);

    if ($action === 'add' && $isValidCoords) {
        // Добавление координат
        file_put_contents($filename, $coords . PHP_EOL, FILE_APPEND);
        logAction("Добавлена новая запись: $coords");
        echo json_encode(['success' => true, 'message' => 'Координаты добавлены']);
    }
    elseif ($action === 'remove' && $isValidCoords) {
        // Удаление координат
        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $updatedLines = array_filter($lines, function($line) use ($coords) {
            return trim($line) !== $coords;
        });

        if (count($lines) !== count($updatedLines)) {
            file_put_contents($filename, update_addresses . phpimplode(PHP_EOL, $updatedLines) . PHP_EOL);
            logAction("Удалена запись: $coords");
            echo json_encode(['success' => true, 'message' => 'Координаты удалены']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Координаты не найдены']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неподдерживаемый метод запроса']);
}
?>