<?php
session_start();
$no_meja =$_SESSION['meja'] ?? '01';
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

    <button onclick="checkout()" class="btn btn-potluck w-100 py-2 fs-5">Konfirmasi Pesanan</button>
</div>

<script>
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

document.addEventListener('DOMContentLoaded', renderCart);
</script>
</body>
</html>