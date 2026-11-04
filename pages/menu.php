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

$sql = "SELECT m.*, k.nama_kategori FROM menu m JOIN kategori k ON m.kategori_id = k.id";
$params = [];

if ($kat_id !== 'all') {
    $sql .= " AND m.kategori_id = ?";
    $params[] = $kat_id;
}
$stmt_menu = $pdo->prepare($sql);
$stmt_menu->execute($params);
$menus = $stmt_menu->fetchAll();

$menu_image_directory = __DIR__ . '/../assets/img';
$available_menu_images = [];
foreach (glob($menu_image_directory . '/*') ?: [] as $image_path) {
    if (is_file($image_path)) {
        $available_menu_images[strtolower(basename($image_path))] = basename($image_path);
    }
}
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
<body class="pb-5 menu-page">

<nav class="navbar bg-primary-brown sticky-top shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand text-white fw-bold" href="#">☕ Potluck86</a>
        <span class="badge bg-warning text-dark fs-6">Meja <?php echo htmlspecialchars($no_meja); ?></span>
    </div>
</nav>

<main class="container menu-content">
    <div id="closed-notice" class="alert alert-warning fw-bold" role="alert" <?php echo $pesanan_buka ? 'hidden' : ''; ?>>
        Potluck sudah tutup. Saat ini kami tidak menerima pesanan.
    </div>

    <section class="menu-hero" aria-labelledby="menu-hero-title">
        <div class="menu-hero-copy">
            <span class="menu-hero-eyebrow"><i class="bi bi-cup-hot me-1" aria-hidden="true"></i> Potluck86</span>
            <h1 id="menu-hero-title">Temukan Sajian<br><span>Favoritmu</span></h1>
            <p>Nikmati pilihan makanan dan minuman dari Potluck86.</p>
            <form action="" method="GET" class="menu-search">
                <input type="hidden" name="meja" value="<?php echo htmlspecialchars($no_meja); ?>">
                <?php if ($kat_id !== 'all'): ?>
                    <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($kat_id); ?>">
                <?php endif; ?>
                <div class="input-group">
                    <input type="search" name="search" class="form-control" placeholder="Cari makanan atau minuman..." value="<?php echo htmlspecialchars($search); ?>" aria-label="Cari menu">
                    <button class="btn btn-potluck" type="submit" aria-label="Cari"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>
        <div class="menu-hero-art" aria-hidden="true">
            <span class="menu-hero-spark menu-hero-spark-one"><i class="bi bi-stars"></i></span>
            <span class="menu-hero-spark menu-hero-spark-two"><i class="bi bi-heart-fill"></i></span>
            <img src="../assets/img/coconutmatcha.jpg" alt="">
            <span class="menu-hero-caption"><i class="bi bi-cup-straw me-1"></i> Dibuat dengan sepenuh hati</span>
        </div>
    </section>

    <section class="menu-listing" aria-labelledby="menu-listing-title">
        <div class="menu-section-heading">
            <div>
                <span class="menu-section-kicker">Pilih sesuai seleramu</span>
                <h2 id="menu-listing-title">Jelajahi Menu</h2>
            </div>
            <span class="menu-result-count" aria-live="polite"><?php echo count($menus); ?> pilihan</span>
        </div>

        <div class="menu-controls">
            <nav class="menu-categories" aria-label="Filter kategori menu">
                <a href="menu.php?meja=<?php echo urlencode($no_meja); ?>&kategori=all<?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="menu-category <?php echo $kat_id=='all'?'active':''; ?>">Semua</a>
                <?php foreach ($kategori as $k): ?>
                    <a href="menu.php?meja=<?php echo urlencode($no_meja); ?>&kategori=<?php echo urlencode($k['id']); ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>"
                       class="menu-category <?php echo $kat_id==$k['id']?'active':''; ?>">
                       <?php echo htmlspecialchars($k['nama_kategori']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="menu-empty text-center py-5" <?php echo empty($menus) ? '' : 'hidden'; ?>>
            <i class="bi bi-search fs-2 text-warm-accent" aria-hidden="true"></i>
            <p class="fw-semibold mt-2 mb-1">Menu tidak ditemukan</p>
            <p class="text-muted small mb-0">Coba kata kunci atau kategori yang berbeda.</p>
        </div>

    <!-- Product Cards Grid -->
    <div class="row g-2 g-md-3">
        <?php foreach ($menus as $m): ?>
            <div class="col-6 col-sm-4 col-lg-3 col-xxl-2 menu-item">
                <div class="card card-menu h-100">
                    <?php
                    $foto = trim((string) ($m['foto'] ?? ''));
                    if ($m['nama_menu'] === 'Telur Ayam Kampung') {
                        $foto = 'telur.jpg';
                    }
                    $foto = basename($foto);
                    $menu_slug = trim(preg_replace('/[^a-z0-9]+/i', '_', (string) $m['nama_menu']), '_');
                    $foto_candidates = [$foto];
                    foreach (array_unique([$menu_slug, str_replace('_', '-', $menu_slug), str_replace('_', '', $menu_slug)]) as $slug) {
                        foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
                            $foto_candidates[] = $slug . '.' . $extension;
                        }
                    }
                    $foto_terpilih = '';
                    foreach ($foto_candidates as $candidate) {
                        $candidate_key = strtolower(basename($candidate));
                        if ($candidate_key !== '' && $candidate_key !== 'default.jpg' && isset($available_menu_images[$candidate_key])) {
                            $foto_terpilih = $available_menu_images[$candidate_key];
                            break;
                        }
                    }
                    if ($foto_terpilih !== ''):
                    ?>
                        <img src="../assets/img/<?php echo rawurlencode($foto_terpilih); ?>"
                             class="card-img-top card-menu-image"
                             alt="<?php echo htmlspecialchars($m['nama_menu']); ?>"
                             loading="lazy">
                    <?php else: ?>
                        <div class="card-menu-image card-menu-image-placeholder" role="img" aria-label="Foto <?php echo htmlspecialchars($m['nama_menu']); ?> belum tersedia">
                            <i class="bi bi-cup-hot" aria-hidden="true"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column justify-content-between p-2">
                        <div>
                            <h2 class="card-title fw-bold mb-1"><?php echo htmlspecialchars($m['nama_menu']); ?></h2>
                            <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($m['deskripsi']); ?></p>
                        </div>
                        <div>
                            <div class="fw-bold text-warm-accent mb-2">Rp <?php echo number_format($m['harga'], 0, ',', '.'); ?></div>
                            <?php $menu_available = $m['status'] === 'tersedia' && (int) $m['stok'] > 0; ?>
                            <?php if (!$menu_available): ?>
                                <span class="badge bg-secondary mb-2">Stok habis</span>
                            <?php endif; ?>
                            <button type="button" data-menu-available="<?php echo $menu_available ? 'true' : 'false'; ?>" onclick="addToCart(<?php echo $m['id']; ?>, '<?php echo addslashes($m['nama_menu']); ?>', <?php echo $m['harga']; ?>)" class="btn btn-potluck btn-sm w-100 menu-add-button" <?php echo $pesanan_buka && $menu_available ? '' : 'disabled'; ?>><?php echo $menu_available ? '+ Tambah' : 'Stok habis'; ?></button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    </section>
</main>

<!-- Floating Cart Button -->
<div class="sticky-cart">
    <a href="keranjang.php" id="cart-link" class="btn btn-potluck w-100 d-flex justify-content-between align-items-center py-2 px-3 shadow <?php echo $pesanan_buka ? '' : 'disabled'; ?>" <?php echo $pesanan_buka ? '' : 'aria-disabled="true" tabindex="-1"'; ?>>
        <span><i class="bi bi-bag-fill me-2"></i> Keranjang Pesanan</span>
        <span id="cart-count" class="badge bg-light text-dark">0 Item</span>
    </a>
</div>

<div id="cart-confirmation" class="cart-confirmation" role="alertdialog" aria-modal="true" aria-labelledby="cart-confirmation-title" aria-describedby="cart-confirmation-message" hidden>
    <div class="cart-confirmation-card">
        <div class="cart-confirmation-icon" aria-hidden="true"><i class="bi bi-check-lg"></i></div>
        <h2 id="cart-confirmation-title">Berhasil ditambahkan!</h2>
        <p id="cart-confirmation-message">Pesanan sudah masuk ke keranjang.</p>
        <button id="cart-confirmation-ok" type="button" class="btn btn-potluck">Oke</button>
    </div>
</div>

<script>
let orderingOpen = <?php echo json_encode($pesanan_buka); ?>;
let lastAddButton = null;

function applyOrderingStatus(isOpen) {
    orderingOpen = isOpen;
    document.getElementById('closed-notice').hidden = isOpen;
    document.querySelectorAll('.menu-add-button').forEach(button => {
        button.disabled = !isOpen || button.dataset.menuAvailable !== 'true';
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
    showCartConfirmation();
}

function showCartConfirmation() {
    const confirmation = document.getElementById('cart-confirmation');
    lastAddButton = document.activeElement;
    confirmation.hidden = false;
    document.body.style.overflow = 'hidden';
    document.getElementById('cart-confirmation-ok').focus();
}

function hideCartConfirmation() {
    document.getElementById('cart-confirmation').hidden = true;
    document.body.style.overflow = '';
    if (lastAddButton instanceof HTMLElement) {
        lastAddButton.focus();
    }
}

function updateCartBadge() {
    let cart = JSON.parse(localStorage.getItem('potluck_cart')) || [];
    let totalItems = cart.reduce((sum, item) => sum + item.jumlah, 0);
    document.getElementById('cart-count').innerText = totalItems + ' Item';
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('cart-confirmation-ok').addEventListener('click', hideCartConfirmation);
    updateCartBadge();
    refreshOrderingStatus();
    window.setInterval(refreshOrderingStatus, 5000);

    const searchInput = document.querySelector('.menu-search input[name="search"]');
    const menuItems = Array.from(document.querySelectorAll('.menu-item'));
    const resultCount = document.querySelector('.menu-result-count');
    const emptyMessage = document.querySelector('.menu-empty');

    function filterMenu() {
        const query = searchInput.value.trim().toLocaleLowerCase('id');
        let visibleCount = 0;

        menuItems.forEach(item => {
            const menuName = item.querySelector('.card-title').textContent.toLocaleLowerCase('id');
            const isVisible = menuName.includes(query);
            item.hidden = !isVisible;
            if (isVisible) visibleCount += 1;
        });

        resultCount.textContent = visibleCount + ' pilihan';
        emptyMessage.hidden = visibleCount !== 0;

        if (query === '') {
            const url = new URL(window.location.href);
            url.searchParams.delete('search');
            window.history.replaceState(null, '', url);
        }
    }

    searchInput.addEventListener('input', filterMenu);
    filterMenu();
});
</script>
</body>
</html>