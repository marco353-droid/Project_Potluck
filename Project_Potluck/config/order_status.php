<?php
function ensure_order_status_table(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS pengaturan_pesanan (
            id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
            status ENUM('buka', 'tutup') NOT NULL DEFAULT 'buka',
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec("INSERT IGNORE INTO pengaturan_pesanan (id, status) VALUES (1, 'buka')");
}

function get_order_status(PDO $pdo): string
{
    ensure_order_status_table($pdo);
    $status = $pdo->query("SELECT status FROM pengaturan_pesanan WHERE id = 1")->fetchColumn();

    if (!in_array($status, ['buka', 'tutup'], true)) {
        throw new RuntimeException('Status pemesanan tidak valid.');
    }

    return $status;
}
?>
