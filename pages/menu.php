<?php
session_start();
require_once '../config/database.php';
require_once '../config/order_status.php';

$no_meja = $_GET['meja'] ?? ($_SESSION['meja'] ?? '01');
$_SESSION['meja'] = $no_meja;
$pesanan_buka = get_order_status($pdo) === 'buka';

// Ambil Kategori
$stmt_kat = $pdo->query("SELECT * FROM kategori");
$kategori = $stmt_kat->fetchAll();

// Ambil Menu
$kat_id = $_GET['kategori'] ?? 'all';
$search = $_GET['search'] ?? '';

$sql = "SELECT m.*, k.nama_kategori FROM menu m JOIN kategori k ON m.kategori_id = k.id WHERE m.status = 'tersedia'";
$params = [];

if ($kat_id !== 'all') {
    $sql .= " AND m.kategori_id = ?";
    $params[] = $kat_id;
}
if (!empty($search)) {
    $sql .= " AND m.nama_menu LIKE ?";
    $params[] = "%$search%";
}

$stmt_menu = $pdo->prepare($sql);
$stmt_menu->execute($params);
$menus = $stmt_menu->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potluck86 - Menu Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="pb-5">

<nav class="navbar bg-primary-brown sticky-top shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand text-white fw-bold" href="#">☕ Potluck86</a>
        <span class="badge bg-warning text-dark fs-6">Meja <?php echo htmlspecialchars($no_meja); ?></span>
    </div>
</nav>

<div class="container mt-3">
    <div id="closed-notice" class="alert alert-warning fw-bold" role="alert" <?php echo $pesanan_buka ? 'hidden' : ''; ?>>
        Potluck sudah tutup. Saat ini kami tidak menerima pesanan.
    </div>

    <!-- Search Bar -->
    <form action="" method="GET" class="mb-3">
        <input type="hidden" name="meja" value="<?php echo htmlspecialchars($no_meja); ?>">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari makanan atau minuman..." value="<?php echo htmlspecialchars($search); ?>">
            <button class="btn btn-potluck" type="submit"><i class="bi bi-search"></i></button>
        </div>
    </form>

    <!-- Kategori Bar -->
    <div class="d-flex overflow-auto pb-2 mb-3" style="white-space: nowrap;">
        <a href="menu.php?meja=<?php echo $no_meja; ?>&kategori=all" class="btn btn-sm me-2 <?php echo $kat_id=='all'?'btn-dark':'btn-outline-dark'; ?>">Semua</a>
        <?php foreach ($kategori as $k): ?>
            <a href="menu.php?meja=<?php echo $no_meja; ?>&kategori=<?php echo $k['id']; ?>" 
               class="btn btn-sm me-2 <?php echo $kat_id==$k['id']?'btn-dark':'btn-outline-dark'; ?>">
               <?php echo htmlspecialchars($k['nama_kategori']); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Product Cards Grid -->
    <div class="row g-3">
        <?php foreach ($menus as $m): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card card-menu h-100">
                    <?php
                    $foto = trim((string) ($m['foto'] ?? ''));
                    if ($m['nama_menu'] === 'Telur Ayam Kampung') {
                        $foto = 'telur.jpg';
                    }
                    if ($foto !== ''):
                    ?>
                        <img src="../assets/img/<?php echo rawurlencode($foto); ?>"
                             class="card-img-top card-menu-image"
                             alt="<?php echo htmlspecialchars($m['nama_menu']); ?>"
                             loading="lazy">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column justify-content-between p-2">
                        <div>
                            <h6 class="card-title fw-bold mb-1"><?php echo htmlspecialchars($m['nama_menu']); ?></h6>
                            <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($m['deskripsi']); ?></p>
                        </div>
                        <div>
                            <div class="fw-bold text-warm-accent mb-2">Rp <?php echo number_format($m['harga'], 0, ',', '.'); ?></div>
                            <button type="button" onclick="addToCart(<?php echo $m['id']; ?>, '<?php echo addslashes($m['nama_menu']); ?>', <?php echo $m['harga']; ?>)" class="btn btn-potluck btn-sm w-100 menu-add-button" <?php echo $pesanan_buka ? '' : 'disabled'; ?>>+ Tambah</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Floating Cart Button -->
<div class="sticky-cart">
    <a href="keranjang.php" id="cart-link" class="btn btn-potluck w-100 d-flex justify-content-between align-items-center py-2 px-3 shadow <?php echo $pesanan_buka ? '' : 'disabled'; ?>" <?php echo $pesanan_buka ? '' : 'aria-disabled="true" tabindex="-1"'; ?>>
        <span><i class="bi bi-bag-fill me-2"></i> Keranjang Pesanan</span>
        <span id="cart-count" class="badge bg-light text-dark">0 Item</span>
    </a>
</div>

<script>
let orderingOpen = <?php echo json_encode($pesanan_buka); ?>;

function applyOrderingStatus(isOpen) {
    orderingOpen = isOpen;
    document.getElementById('closed-notice').hidden = isOpen;
    document.querySelectorAll('.menu-add-button').forEach(button => {
        button.disabled = !isOpen;
    });
    const cartLink = document.getElementById('cart-link');
    cartLink.classList.toggle('disabled', !isOpen);
    cartLink.setAttribute('aria-disabled', String(!isOpen));
    if (isOpen) {
        cartLink.removeAttribute('tabindex');
    } else {
        cartLink.setAttribute('tabindex', '-1');
    }
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

function addToCart(id, nama, harga) {
    if (!orderingOpen) return;
    let cart = JSON.parse(localStorage.getItem('potluck_cart')) || [];
    let existing = cart.find(item => item.id === id);
    if (existing) {
        existing.jumlah += 1;
    } else {
        cart.push({ id: id, nama: nama, harga: harga, jumlah: 1, catatan: '' });
    }
    localStorage.setItem('potluck_cart', JSON.stringify(cart));
    updateCartBadge();
    alert(nama + ' telah ditambahkan ke keranjang!');
}

function updateCartBadge() {
    let cart = JSON.parse(localStorage.getItem('potluck_cart')) || [];
    let totalItems = cart.reduce((sum, item) => sum + item.jumlah, 0);
    document.getElementById('cart-count').innerText = totalItems + ' Item';
}

document.addEventListener('DOMContentLoaded', () => {
    updateCartBadge();
    refreshOrderingStatus();
    window.setInterval(refreshOrderingStatus, 5000);
});
</script>
</body>
</html>