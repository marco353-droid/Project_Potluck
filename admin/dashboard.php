<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}

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
<<<<<<< HEAD
            <li><a href="menu.php" class="nav-link text-white">Menu</a></li>
=======
            <li><a href="../pages/menu.php" class="nav-link text-white">Menu</a></li>
>>>>>>> 5070144 (V2)
            <li><a href="../pages/logout.php" class="nav-link text-danger mt-4">Logout</a></li>
        </ul>
    </div>

    <div class="p-4 flex-grow-1">
        <h2>Dashboard Admin</h2>
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