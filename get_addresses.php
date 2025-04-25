<?php
header('Content-Type: application/json');

$filename = 'addresses.txt';

if (!file_exists($filename)) {
    echo json_encode([]);
    exit;
}

$lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$addresses = array_map('trim', $lines);

echo json_encode($addresses);
?>
