<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $menuId = filter_var($_POST['menu_id'] ?? null, FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';

    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $error = 'Permintaan tidak valid. Muat ulang halaman, lalu coba lagi.';
    } elseif (!$menuId) {
        $error = 'Menu tidak valid.';
    } elseif ($action === 'stock') {
        $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
        if ($stock === false || $stock < 0) {
            $error = 'Stok harus berupa bilangan bulat nol atau lebih.';
        } else {
            $stmt_exists = $pdo->prepare('SELECT id FROM menu WHERE id = ?');
            $stmt_exists->execute([$menuId]);
            if (!$stmt_exists->fetchColumn()) {
                $error = 'Menu tidak ditemukan.';
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE menu
                     SET stok = ?, status = CASE WHEN ? = 0 THEN 'tidak tersedia' ELSE status END
                     WHERE id = ?"
                );
                $stmt->execute([$stock, $stock, $menuId]);
                $_SESSION['menu_notice'] = 'Stok menu berhasil diperbarui.';
                header('Location: menu.php');
                exit;
            }
        }
    } elseif ($action === 'availability') {
        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['tersedia', 'tidak tersedia'], true)) {
            $error = 'Status menu tidak valid.';
        } else {
            $stmt = $pdo->prepare(
                "UPDATE menu SET status = ?
                 WHERE id = ? AND (? = 'tidak tersedia' OR stok > 0)"
            );
            $stmt->execute([$status, $menuId, $status]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['menu_notice'] = $status === 'tersedia'
                    ? 'Menu tersedia untuk dipesan.'
                    : 'Menu ditandai habis dan tidak dapat dipesan.';
                header('Location: menu.php');
                exit;
            }
            $error = 'Menu tidak ditemukan atau stok harus ditambah sebelum menu tersedia.';
        }
    } else {
        $error = 'Aksi tidak valid.';
    }
}

$notice = $_SESSION['menu_notice'] ?? '';
unset($_SESSION['menu_notice']);
$menus = $pdo->query(
    'SELECT m.id, m.nama_menu, m.stok, m.status, k.nama_kategori
     FROM menu m
     JOIN kategori k ON k.id = m.kategori_id
     ORDER BY k.nama_kategori, m.nama_menu'
)->fetchAll();

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
    <title>Kelola Menu - Potluck86</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
<div class="d-flex min-vh-100 admin-dashboard-layout">
    <aside class="bg-primary-brown text-white p-3 min-vh-100 admin-dashboard-sidebar">
        <h4>Potluck86</h4>
        <hr>
        <nav class="nav nav-pills flex-column admin-dashboard-nav">
            <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
            <a href="pesanan.php" class="nav-link text-white">Pesanan</a>
            <a href="menu.php" class="nav-link text-white active" aria-current="page">Menu</a>
            <a href="../pages/logout.php" class="nav-link text-danger mt-4">Logout</a>
        </nav>
    </aside>

    <main class="p-4 flex-grow-1 admin-dashboard-main">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <h1 class="h3 mb-0">Kelola Menu dan Stok</h1>
            <div class="ms-auto" style="width: min(100%, 360px);">
                <label for="menu-search" class="visually-hidden">Cari menu</label>
                <input id="menu-search" type="search" class="form-control" placeholder="Cari menu..." autocomplete="off">
            </div>
        </div>
        <?php if ($notice !== ''): ?>
            <div class="alert alert-success" role="status"><?php echo escape($notice); ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger" role="alert"><?php echo escape($error); ?></div>
        <?php endif; ?>
        <p id="menu-search-count" class="small text-muted mb-2" aria-live="polite"></p>

        <div class="table-responsive admin-menu-table">
            <table class="table table-striped table-hover align-middle bg-white">
                <thead>
                    <tr>
                        <th>Nama Menu</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menus as $menu): ?>
                        <?php $available = $menu['status'] === 'tersedia' && (int) $menu['stok'] > 0; ?>
                        <tr class="admin-menu-row" data-search="<?php echo escape($menu['nama_menu'] . ' ' . $menu['nama_kategori']); ?>">
                            <td data-label="Nama Menu"><?php echo escape($menu['nama_menu']); ?></td>
                            <td data-label="Kategori"><?php echo escape($menu['nama_kategori']); ?></td>
                            <td data-label="Stok">
                                <form method="POST" class="d-flex gap-2">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="menu_id" value="<?php echo escape($menu['id']); ?>">
                                    <input type="hidden" name="action" value="stock">
                                    <input type="number" name="stock" class="form-control form-control-sm" min="0" step="1" value="<?php echo escape($menu['stok']); ?>" aria-label="Stok <?php echo escape($menu['nama_menu']); ?>" required>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Simpan</button>
                                </form>
                            </td>
                            <td data-label="Status">
                                <span class="badge <?php echo $available ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $available ? 'Tersedia' : 'Habis'; ?>
                                </span>
                            </td>
                            <td data-label="Aksi">
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo escape($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="menu_id" value="<?php echo escape($menu['id']); ?>">
                                    <input type="hidden" name="action" value="availability">
                                    <input type="hidden" name="status" value="<?php echo $available ? 'tidak tersedia' : 'tersedia'; ?>">
                                    <button type="submit" class="btn btn-sm <?php echo $available ? 'btn-outline-danger' : 'btn-outline-success'; ?>" <?php echo !$available && (int) $menu['stok'] < 1 ? 'disabled' : ''; ?>>
                                        <?php echo $available ? 'Tandai Habis' : 'Aktifkan'; ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="menu-search-empty" hidden>
                        <td colspan="5" class="text-center text-muted py-4">Menu tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('menu-search');
    const menuRows = Array.from(document.querySelectorAll('.admin-menu-row'));
    const emptyMessage = document.getElementById('menu-search-empty');
    const resultCount = document.getElementById('menu-search-count');

    function filterMenu() {
        const query = searchInput.value.trim().toLocaleLowerCase('id');
        let visibleCount = 0;

        menuRows.forEach(row => {
            const matches = row.dataset.search.toLocaleLowerCase('id').includes(query);
            row.hidden = !matches;
            if (matches) visibleCount += 1;
        });

        emptyMessage.hidden = visibleCount !== 0;
        resultCount.textContent = visibleCount + ' menu ditemukan';
    }

    searchInput.addEventListener('input', filterMenu);
    filterMenu();
});
</script>
</body>
</html>
