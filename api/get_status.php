<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$order_id = $_GET['order_id'] ?? '';

$stmt = $pdo->prepare("SELECT status FROM pesanan WHERE nomor_pesanan = ?");
$stmt->execute([$order_id]);
$res = $stmt->fetch();

if ($res) {
    echo json_encode(['status' => 'success', 'order_status' => $res['status']]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Pesanan tidak ditemukan']);
}
?>