<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['id']) && isset($input['status'])) {
    $stmt = $pdo->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    if ($stmt->execute([$input['status'], $input['id']])) {
        echo json_encode(['status' => 'success']);
        exit;
    }
}

echo json_encode(['status' => 'error']);
?>