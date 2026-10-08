<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
require_once '../config/database.php';
require_once '../config/order_status.php';

$status = get_order_status($pdo);
echo json_encode([
    'status' => $status,
    'is_open' => $status === 'buka',
]);
?>
