<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}

$sql = "SELECT p.nomor_pesanan, p.status, p.waktu_pesanan,
               d.menu_id, d.jumlah, d.harga, d.subtotal,
               m.nama_menu, meja.nomor_meja
        FROM detail_pesanan d
        JOIN pesanan p ON p.id = d.pesanan_id
        JOIN menu m ON m.id = d.menu_id
        JOIN meja ON meja.id = p.meja_id
        ORDER BY p.waktu_pesanan DESC, p.id DESC, d.id ASC";
$items = $pdo->query($sql)->fetchAll();
$orders = [];
foreach ($items as $item) {
    $orderNumber = $item['nomor_pesanan'];
    if (!isset($orders[$orderNumber])) {
        $orders[$orderNumber] = [
            'status' => $item['status'],
            'waktu_pesanan' => $item['waktu_pesanan'],
            'meja' => $item['nomor_meja'],
            'items' => [],
        ];
    }

    $orders[$orderNumber]['items'][] = $item;
}

function escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan - Potluck86</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex min-vh-100 admin-dashboard-layout">
    <aside class="bg-primary-brown text-white p-3 min-vh-100 admin-dashboard-sidebar">
        <h4>Potluck86</h4>
        <hr>
        <nav class="nav nav-pills flex-column admin-dashboard-nav">
            <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
            <a href="pesanan.php" class="nav-link text-white active" aria-current="page">Pesanan</a>
            <a href="menu.php" class="nav-link text-white">Menu</a>
            <a href="../pages/logout.php" class="nav-link text-danger mt-4">Logout</a>
        </nav>
    </aside>

    <main class="p-4 flex-grow-1 admin-dashboard-main">
        <h1 class="h3 mb-4">Barang yang Dipesan</h1>
        <div class="table-responsive admin-orders-table">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Kode Pesanan</th>
                        <th>Kode Barang</th>
                        <th>Nama Barang</th>
                        <th>Meja</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                        <th>Status</th>
                        <th>Waktu Pesanan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$items): ?>
                        <tr class="admin-orders-empty">
                            <td colspan="9" class="text-center text-muted py-4">Belum ada barang pesanan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td data-label="Kode Pesanan"><?php echo escape($item['nomor_pesanan']); ?></td>
                                <td data-label="Kode Barang"><?php echo escape($item['menu_id']); ?></td>
                                <td data-label="Nama Barang"><?php echo escape($item['nama_menu']); ?></td>
                                <td data-label="Meja"><?php echo escape($item['nomor_meja']); ?></td>
                                <td data-label="Jumlah"><?php echo escape($item['jumlah']); ?></td>
                                <td data-label="Harga">Rp <?php echo number_format((float) $item['harga'], 0, ',', '.'); ?></td>
                                <td data-label="Subtotal">Rp <?php echo number_format((float) $item['subtotal'], 0, ',', '.'); ?></td>
                                <td data-label="Status"><?php echo escape($item['status']); ?></td>
                                <td data-label="Waktu Pesanan"><?php echo escape($item['waktu_pesanan']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!$orders): ?>
            <div class="admin-orders-mobile-empty text-center text-muted py-4">Belum ada barang pesanan.</div>
        <?php else: ?>
            <div class="admin-orders-mobile-list">
                <?php foreach ($orders as $orderNumber => $order): ?>
                    <article class="admin-order-card">
                        <header class="admin-order-card-header">
                            <div>
                                <span class="admin-order-label">Kode Pesanan</span>
                                <h2><?php echo escape($orderNumber); ?></h2>
                            </div>
                            <span class="badge bg-secondary"><?php echo escape($order['status']); ?></span>
                        </header>
                        <div class="admin-order-meta">
                            <div>
                                <span class="admin-order-label">Meja</span>
                                <strong><?php echo escape($order['meja']); ?></strong>
                            </div>
                            <div>
                                <span class="admin-order-label">Waktu Pesanan</span>
                                <strong><?php echo escape($order['waktu_pesanan']); ?></strong>
                            </div>
                        </div>
                        <div class="admin-order-items">
                            <h3>Item Pesanan <span><?php echo count($order['items']); ?></span></h3>
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="admin-order-item">
                                    <div class="admin-order-item-name">
                                        <strong><?php echo escape($item['nama_menu']); ?></strong>
                                        <span>Kode barang: <?php echo escape($item['menu_id']); ?></span>
                                    </div>
                                    <div class="admin-order-item-quantity">
                                        <span>Jumlah</span>
                                        <strong><?php echo escape($item['jumlah']); ?></strong>
                                    </div>
                                    <div class="admin-order-item-price">
                                        <span>Harga</span>
                                        <strong>Rp <?php echo number_format((float) $item['harga'], 0, ',', '.'); ?></strong>
                                    </div>
                                    <div class="admin-order-item-subtotal">
                                        <span>Subtotal</span>
                                        <strong>Rp <?php echo number_format((float) $item['subtotal'], 0, ',', '.'); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
