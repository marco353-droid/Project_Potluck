<?php
session_start();
require_once '../config/database.php';
require_once '../config/order_status.php';
$no_meja =$_SESSION['meja'] ?? '01';
$pesanan_buka = get_order_status($pdo) === 'buka';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potluck86 - Keranjang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<div class="container my-4" style="max-width: 600px;">
    <h4 class="fw-bold mb-3">Keranjang Pesanan (Meja <?php echo htmlspecialchars($no_meja); ?>)</h4>
    <div id="closed-notice" class="alert alert-warning fw-bold" role="alert" <?php echo $pesanan_buka ? 'hidden' : ''; ?>>
        Potluck sudah tutup. Saat ini kami tidak menerima pesanan.
    </div>
    <div id="cart-items" class="mb-4"></div>

    <div class="card p-3 shadow-sm mb-4">
        <h6 class="fw-bold">Metode Pembayaran</h6>
        <select id="metode_pembayaran" class="form-select mt-2">
            <option value="Bayar di Kasir">Bayar di Kasir</option>
            <option value="QRIS">QRIS</option>
        </select>
    </div>

    <div class="card p-3 shadow-sm mb-4">
        <div class="d-flex justify-content-between fw-bold">
            <span>Total Pembayaran:</span>
            <span id="total-harga" class="text-warm-accent">Rp 0</span>
        </div>
    </div>

    <button id="checkout-button" onclick="checkout()" class="btn btn-potluck w-100 py-2 fs-5" <?php echo $pesanan_buka ? '' : 'disabled'; ?>>Konfirmasi Pesanan</button>
</div>

<script>
let orderingOpen = <?php echo json_encode($pesanan_buka); ?>;

function applyOrderingStatus(isOpen) {
    orderingOpen = isOpen;
    document.getElementById('closed-notice').hidden = isOpen;
    document.getElementById('checkout-button').disabled = !isOpen;
    document.querySelectorAll('#cart-items button, #cart-items input').forEach(control => {
        control.disabled = !isOpen;
    });
}

function refreshOrderingStatus() {
    fetch('../api/status_pesanan.php', { cache: 'no-store' })
        .then(response => {
            if (!response.ok) throw new Error('Gagal mengambil status pemesanan.');
            return response.json();
        })
        .then(data => applyOrderingStatus(data.is_open === true))
        .catch(error => console.error(error));
}

function renderCart() {
    let cart = JSON.parse(localStorage.getItem('potluck_cart')) || [];
    let container = document.getElementById('cart-items');
    let total = 0;
    
    if (cart.length === 0) {
        container.innerHTML = '<div class="alert alert-info">Keranjang Anda kosong.</div>';
        document.getElementById('total-harga').innerText = 'Rp 0';
        return;
    }

    let html = '';
    cart.forEach((item, index) => {
        let subtotal = item.harga * item.jumlah;
        total += subtotal;
        html += `
            <div class="card p-3 mb-2 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0">${item.nama}</h6>
                        <small class="text-muted">Rp ${item.harga.toLocaleString()}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <button onclick="changeQty(${index}, -1)" class="btn btn-sm btn-outline-secondary me-2">-</button>
                        <span>${item.jumlah}</span>
                        <button onclick="changeQty(${index}, 1)" class="btn btn-sm btn-outline-secondary ms-2">+</button>
                    </div>
                </div>
                <input type="text" class="form-control form-control-sm mt-2" placeholder="Catatan (misal: pedas/kurangi es)" value="${item.catatan}" onchange="updateNote(${index}, this.value)">
            </div>
        `;
    });
    container.innerHTML = html;
    document.getElementById('total-harga').innerText = 'Rp ' + total.toLocaleString();
}

function changeQty(index, delta) {
    let cart = JSON.parse(localStorage.getItem('potluck_cart')) || [];
    cart[index].jumlah += delta;
    if (cart[index].jumlah <= 0) cart.splice(index, 1);
    localStorage.setItem('potluck_cart', JSON.stringify(cart));
    renderCart();
}

function updateNote(index, note) {
    let cart = JSON.parse(localStorage.getItem('potluck_cart')) || [];
    cart[index].catatan = note;
    localStorage.setItem('potluck_cart', JSON.stringify(cart));
}

function checkout() {
    if (!orderingOpen) {
        alert('Potluck sudah tutup. Saat ini kami tidak menerima pesanan.');
        return;
    }
    let cart = JSON.parse(localStorage.getItem('potluck_cart')) || [];
    if (cart.length === 0) return alert('Keranjang kosong!');

    let data = {
        meja: '<?php echo $no_meja; ?>',
        metode_pembayaran: document.getElementById('metode_pembayaran').value,
        items: cart
    };

    fetch('../api/pesanan.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === 'success') {
            localStorage.removeItem('potluck_cart');
            window.location.href = 'sukses.php?order_id=' + res.order_id;
        } else {
            alert('Gagal memproses pesanan: ' + res.message);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    renderCart();
    applyOrderingStatus(orderingOpen);
    refreshOrderingStatus();
    window.setInterval(refreshOrderingStatus, 5000);
});
</script>
</body>
</html>