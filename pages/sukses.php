<?php
session_start();
$order_id = $_GET['order_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan - Potluck86</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<div class="container my-5 text-center" style="max-width: 500px;">
    <div class="card p-4 shadow-sm">
        <h3 class="text-success fw-bold">Pesanan Berhasil!</h3>
        <p class="text-muted">Nomor Pesanan Anda:</p>
        <h4 class="fw-bold text-warm-accent">#<?php echo htmlspecialchars($order_id); ?></h4>

        <hr>

        <h5 class="fw-bold mb-3">Status Pesanan:</h5>
        <div id="status-badge" class="badge bg-warning text-dark fs-5 py-2 px-4 mb-3">Menunggu Konfirmasi</div>

        <p class="small text-muted">Halaman ini diperbarui secara otomatis secara real-time.</p>
        
        <a href="menu.php" class="btn btn-potluck w-100 mt-3">Kembali ke Menu</a>
    </div>
</div>

<script>
function checkStatus() {
    fetch('../api/get_status.php?order_id=<?php echo $order_id; ?>')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                let badge = document.getElementById('status-badge');
                badge.innerText = data.order_status;
                
                if (data.order_status === 'Diproses') {
                    badge.className = 'badge bg-info text-dark fs-5 py-2 px-4 mb-3';
                } else if (data.order_status === 'Siap Disajikan') {
                    badge.className = 'badge bg-primary fs-5 py-2 px-4 mb-3';
                } else if (data.order_status === 'Selesai') {
                    badge.className = 'badge bg-success fs-5 py-2 px-4 mb-3';
                }
            }
        });
}

// AJAX Polling setiap 3 detik
setInterval(checkStatus, 3000);
document.addEventListener('DOMContentLoaded', checkStatus);
</script>
</body>
</html>