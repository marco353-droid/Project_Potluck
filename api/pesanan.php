<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/order_status.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
    exit;
}

try {
    ensure_order_status_table($pdo);
    $pdo->beginTransaction();

    $stmt_status = $pdo->query("SELECT status FROM pengaturan_pesanan WHERE id = 1 FOR UPDATE");
    if ($stmt_status->fetchColumn() !== 'buka') {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Potluck sudah tutup. Saat ini kami tidak menerima pesanan.']);
        exit;
    }

    // Cari ID Meja
    $stmt_m = $pdo->prepare("SELECT id FROM meja WHERE nomor_meja = ?");
    $stmt_m->execute([$input['meja']]);
    $meja = $stmt_m->fetch();
    $meja_id = $meja ? $meja['id'] : 1;

    // Hitung Total
    $total = 0;
    foreach ($input['items'] as $item) {
        $total += $item['harga'] * $item['jumlah'];
    }

    // Generate Nomor Pesanan (#PLK086XX)
    $nomor_pesanan = 'PLK' . sprintf("%05d", rand(1, 99999));

    // Insert Pesanan
    $stmt_p = $pdo->prepare("INSERT INTO pesanan (nomor_pesanan, meja_id, total, metode_pembayaran, status) VALUES (?, ?, ?, ?, 'Menunggu')");
    $stmt_p->execute([$nomor_pesanan, $meja_id, $total, $input['metode_pembayaran']]);
    $pesanan_id = $pdo->lastInsertId();

    // Insert Detail Pesanan
    $stmt_d = $pdo->prepare("INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, harga, catatan, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($input['items'] as $item) {
        $subtotal = $item['harga'] * $item['jumlah'];
        $stmt_d->execute([$pesanan_id, $item['id'], $item['jumlah'], $item['harga'], $item['catatan'], $subtotal]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'order_id' => $nomor_pesanan]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>