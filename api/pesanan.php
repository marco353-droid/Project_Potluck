<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/order_status.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input) || !isset($input['items']) || !is_array($input['items']) || !$input['items']) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data pesanan tidak valid.']);
    exit;
}

$items = [];
foreach ($input['items'] as $item) {
    if (!is_array($item)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Item pesanan tidak valid.']);
        exit;
    }

    $menuId = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($item['jumlah'] ?? null, FILTER_VALIDATE_INT);
    if (!$menuId || !$quantity || $quantity < 1 || isset($items[$menuId])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Item pesanan tidak valid.']);
        exit;
    }

    $note = $item['catatan'] ?? '';
    if (!is_string($note)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Catatan pesanan tidak valid.']);
        exit;
    }

    $items[$menuId] = ['jumlah' => $quantity, 'catatan' => $note];
}

$paymentMethod = $input['metode_pembayaran'] ?? '';
if (!in_array($paymentMethod, ['Bayar di Kasir', 'QRIS'], true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Metode pembayaran tidak valid.']);
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

    $verifiedItems = [];
    $total = 0;
    $stmt_menu = $pdo->prepare('SELECT nama_menu, harga, stok, status FROM menu WHERE id = ? FOR UPDATE');
    foreach ($items as $menuId => $item) {
        $stmt_menu->execute([$menuId]);
        $menu = $stmt_menu->fetch();
        if (!$menu || $menu['status'] !== 'tersedia' || (int) $menu['stok'] < $item['jumlah']) {
            $pdo->rollBack();
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => $menu
                    ? $menu['nama_menu'] . ' sedang habis atau stoknya tidak mencukupi.'
                    : 'Menu yang dipilih tidak ditemukan.',
            ]);
            exit;
        }

        $unitPrice = (float) $menu['harga'];
        $subtotal = $unitPrice * $item['jumlah'];
        $total += $subtotal;
        $verifiedItems[] = [
            'id' => $menuId,
            'jumlah' => $item['jumlah'],
            'catatan' => $item['catatan'],
            'harga' => $unitPrice,
            'subtotal' => $subtotal,
        ];
    }

    $stmt_m = $pdo->prepare("SELECT id FROM meja WHERE nomor_meja = ?");
    $stmt_m->execute([$input['meja'] ?? '']);
    $meja = $stmt_m->fetch();
    $meja_id = $meja ? $meja['id'] : 1;

    $nomor_pesanan = 'PLK' . sprintf("%05d", rand(1, 99999));

    $stmt_p = $pdo->prepare("INSERT INTO pesanan (nomor_pesanan, meja_id, total, metode_pembayaran, status) VALUES (?, ?, ?, ?, 'Menunggu')");
    $stmt_p->execute([$nomor_pesanan, $meja_id, $total, $paymentMethod]);
    $pesanan_id = $pdo->lastInsertId();

    $stmt_d = $pdo->prepare("INSERT INTO detail_pesanan (pesanan_id, menu_id, jumlah, harga, catatan, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_stock = $pdo->prepare(
        "UPDATE menu
         SET stok = stok - ?,
             status = CASE WHEN stok - ? <= 0 THEN 'tidak tersedia' ELSE status END
         WHERE id = ? AND status = 'tersedia' AND stok >= ?"
    );
    foreach ($verifiedItems as $item) {
        $stmt_stock->execute([$item['jumlah'], $item['jumlah'], $item['id'], $item['jumlah']]);
        if ($stmt_stock->rowCount() !== 1) {
            throw new RuntimeException('Stok berubah saat pesanan diproses.');
        }
        $stmt_d->execute([
            $pesanan_id,
            $item['id'],
            $item['jumlah'],
            $item['harga'],
            $item['catatan'],
            $item['subtotal'],
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'order_id' => $nomor_pesanan]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Pesanan gagal diproses. Silakan coba lagi.']);
}
?>