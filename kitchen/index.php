<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'dapur') {
    header('Location: ../pages/login.php');
    exit;
}

require_once '../config/database.php';

function escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$nextStatus = [
    'Menunggu' => 'Diproses',
    'Diproses' => 'Siap Disajikan',
    'Siap Disajikan' => 'Selesai',
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $targetStatus = $_POST['next_status'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.';
    } elseif (!$orderId) {
        $error = 'Pesanan tidak valid.';
    } else {
        $stmt = $pdo->prepare('SELECT status FROM pesanan WHERE id = ?');
        $stmt->execute([$orderId]);
        $currentStatus = $stmt->fetchColumn();

        if (!isset($nextStatus[$currentStatus]) || $nextStatus[$currentStatus] !== $targetStatus) {
            $error = 'Perubahan status tidak valid.';
        } else {
            $stmt = $pdo->prepare('UPDATE pesanan SET status = ? WHERE id = ? AND status = ?');
            $stmt->execute([$targetStatus, $orderId, $currentStatus]);

            if ($stmt->rowCount() === 1) {
                header('Location: index.php?updated=1');
                exit;
            }

            $error = 'Status pesanan berubah. Muat ulang halaman sebelum mencoba lagi.';
        }
    }
}

$sql = "SELECT p.id, p.nomor_pesanan, p.status, p.waktu_pesanan,
               meja.nomor_meja, d.menu_id, d.jumlah, d.catatan, m.nama_menu
        FROM pesanan p
        JOIN meja ON meja.id = p.meja_id
        LEFT JOIN detail_pesanan d ON d.pesanan_id = p.id
        LEFT JOIN menu m ON m.id = d.menu_id
        WHERE p.status IN ('Menunggu', 'Diproses', 'Siap Disajikan')
        ORDER BY p.waktu_pesanan ASC, p.id ASC, d.id ASC";
$rows = $pdo->query($sql)->fetchAll();
$orders = [];

foreach ($rows as $row) {
    $orderId = $row['id'];
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'nomor_pesanan' => $row['nomor_pesanan'],
            'status' => $row['status'],
            'waktu_pesanan' => $row['waktu_pesanan'],
            'nomor_meja' => $row['nomor_meja'],
            'items' => [],
        ];
    }

    if ($row['menu_id'] !== null) {
        $orders[$orderId]['items'][] = [
            'nama_menu' => $row['nama_menu'],
            'jumlah' => $row['jumlah'],
            'catatan' => $row['catatan'],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Dapur - Potluck86</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
<header class="bg-primary-brown text-white py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <h1 class="h4 mb-0">Pesanan Dapur</h1>
        <a href="../pages/logout.php" class="btn btn-outline-light btn-sm">Keluar</a>
    </div>
</header>

<main class="container py-4" style="max-width: 900px;">
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success" role="status">Status pesanan berhasil diperbarui.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?php echo escape($error); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Pesanan Aktif</h2>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">Muat ulang</a>
    </div>

    <?php if (!$orders): ?>
        <div class="alert alert-info">Belum ada pesanan aktif.</div>
    <?php else: ?>
        <?php foreach ($orders as $id => $order): ?>
            <article class="card mb-3 shadow-sm">
                <div class="card-header d-flex flex-wrap justify-content-between gap-2">
                    <strong><?php echo escape($order['nomor_pesanan']); ?></strong>
                    <span class="badge bg-secondary"><?php echo escape($order['status']); ?></span>
                </div>
                <div class="card-body">
                    <p class="mb-2">Meja <?php echo escape($order['nomor_meja']); ?> · <?php echo escape($order['waktu_pesanan']); ?></p>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($order['items'] as $item): ?>
                            <li class="list-group-item px-0">
                                <strong><?php echo escape($item['jumlah']); ?>× <?php echo escape($item['nama_menu']); ?></strong>
                                <?php if ($item['catatan']): ?>
                                    <div class="small text-muted">Catatan: <?php echo escape($item['catatan']); ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <form method="POST" class="text-end">
                        <input type="hidden" name="csrf_token" value="<?php echo escape($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="order_id" value="<?php echo escape($id); ?>">
                        <input type="hidden" name="next_status" value="<?php echo escape($nextStatus[$order['status']]); ?>">
                        <button type="submit" class="btn btn-primary">
                            <?php echo escape($nextStatus[$order['status']]); ?>
                        </button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
</body>
</html>
