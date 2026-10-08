<?php
session_start();
require_once '../config/database.php';
require_once '../config/order_status.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$status_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!is_string($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $status_error = 'Permintaan tidak valid. Muat ulang halaman, lalu coba lagi.';
    } else {
        $current_status = get_order_status($pdo);
        $next_status = $current_status === 'buka' ? 'tutup' : 'buka';
        $stmt_status = $pdo->prepare("UPDATE pengaturan_pesanan SET status = ? WHERE id = 1");
        $stmt_status->execute([$next_status]);
        header('Location: dashboard.php');
        exit;
    }
}

$order_status = get_order_status($pdo);

// Statistik Singkat
$total_pesanan = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$total_pendapatan = $pdo->query("SELECT SUM(total) FROM pesanan WHERE status = 'Selesai' AND DATE(created_at) = CURDATE()")->fetchColumn() ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Potluck86</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="d-flex">
    <div class="bg-primary-brown text-white p-3 min-vh-100" style="width: 250px;">
        <h4>Potluck86</h4>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="dashboard.php" class="nav-link text-white active">Dashboard</a></li>
            <li><a href="pesanan.php" class="nav-link text-white">Pesanan</a></li>
            <li><a href="menu.php" class="nav-link text-white">Menu</a></li>
            <li><a href="../pages/logout.php" class="nav-link text-danger mt-4">Logout</a></li>
        </ul>
    </div>

    <div class="p-4 flex-grow-1">
        <h2>Dashboard Admin</h2>
        <div class="card p-3 my-3 shadow-sm">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h5 class="mb-1">Penerimaan Pesanan</h5>
                    <span class="badge <?php echo $order_status === 'buka' ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo $order_status === 'buka' ? 'Sedang dibuka' : 'Sedang ditutup'; ?>
                    </span>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" class="btn <?php echo $order_status === 'buka' ? 'btn-danger' : 'btn-success'; ?>">
                        <?php echo $order_status === 'buka' ? 'Tutup Pesanan' : 'Buka Pesanan'; ?>
                    </button>
                </form>
            </div>
            <?php if ($status_error !== ''): ?>
                <div class="alert alert-danger mt-3 mb-0" role="alert"><?php echo htmlspecialchars($status_error); ?></div>
            <?php endif; ?>
        </div>
        <div class="row g-3 my-3">
            <div class="col-md-6">
                <div class="card p-3 bg-light shadow-sm">
                    <h6>Total Pesanan Hari Ini</h6>
                    <h3><?php echo $total_pesanan; ?></h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3 bg-light shadow-sm">
                    <h6>Total Pendapatan Hari Ini</h6>
                    <h3 class="text-success">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>