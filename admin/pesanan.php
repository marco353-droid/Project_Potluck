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
<div class="d-flex min-vh-100">
    <aside class="bg-primary-brown text-white p-3" style="width: 250px;">
        <h4>Potluck86</h4>
        <hr>
        <nav class="nav nav-pills flex-column">
            <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
            <a href="pesanan.php" class="nav-link text-white active" aria-current="page">Pesanan</a>
            <a href="../pages/menu.php" class="nav-link text-white">Menu</a>
            <a href="../pages/logout.php" class="nav-link text-danger mt-4">Logout</a>
        </nav>
    </aside>

    <main class="p-4 flex-grow-1">
        <h1 class="h3 mb-4">Barang yang Dipesan</h1>
        <div class="table-responsive">
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
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Belum ada barang pesanan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo escape($item['nomor_pesanan']); ?></td>
                                <td><?php echo escape($item['menu_id']); ?></td>
                                <td><?php echo escape($item['nama_menu']); ?></td>
                                <td><?php echo escape($item['nomor_meja']); ?></td>
                                <td><?php echo escape($item['jumlah']); ?></td>
                                <td>Rp <?php echo number_format((float) $item['harga'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format((float) $item['subtotal'], 0, ',', '.'); ?></td>
                                <td><?php echo escape($item['status']); ?></td>
                                <td><?php echo escape($item['waktu_pesanan']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
