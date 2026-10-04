CREATE DATABASE IF NOT EXISTS `potluck86` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `potluck86`;

-- 1. Tabel users
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'dapur') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password hashing untuk: admin123 dan dapur123
INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`) VALUES
<<<<<<< HEAD
(1, 'Administrator', 'admin', '$2y$10$wE99Sj3J6RjG6pXQfXy5v.vE5z2O31QGv10T1K3v8L7mY2S9m91y.', 'admin'),
(2, 'Tim Dapur', 'dapur', '$2y$10$8k9Y2mK5uX7kZ9sN6vP1g.aJ8aO0P2k3L4m5N6o7P8q9R0s1T2u3V.', 'dapur');
=======
(1, 'Administrator', 'admin', '$2y$10$j8o1GeIDQlmO55C6bYlEp.bTH9j5J2CK0BZpCzh/mmkKX5WI0GzXG', 'admin'),
(2, 'Tim Dapur', 'dapur', '$2y$10$Pa5rPPnGv.Z4o3pytiVNMeayKBc99Pr6Yu1N4uKdwPyJD2yJ695d2', 'dapur');
>>>>>>> 5070144 (V2)

-- 2. Tabel meja
CREATE TABLE `meja` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_meja` VARCHAR(10) NOT NULL UNIQUE,
  `qr_code` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('tersedia', 'terisi') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `meja` (`nomor_meja`, `status`) VALUES
('01', 'tersedia'), ('02', 'tersedia'), ('03', 'tersedia'), ('04', 'tersedia'), ('05', 'tersedia'),
('06', 'tersedia'), ('07', 'tersedia'), ('08', 'tersedia'), ('09', 'tersedia'), ('10', 'tersedia');

-- 3. Tabel kategori
CREATE TABLE `kategori` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kategori` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES
(1, 'Makanan'), (2, 'Snack'), (3, 'Minuman'), (4, 'Coffee'), (5, 'Non-Coffee'), (6, 'Dessert');

-- 4. Tabel menu
CREATE TABLE `menu` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kategori_id` INT NOT NULL,
  `nama_menu` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT,
  `harga` DECIMAL(10,2) NOT NULL,
  `foto` VARCHAR(255) DEFAULT 'default.jpg',
  `stok` INT DEFAULT 50,
  `status` ENUM('tersedia', 'tidak tersedia') DEFAULT 'tersedia',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`kategori_id`) REFERENCES `kategori`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `menu` (`id`, `kategori_id`, `nama_menu`, `deskripsi`, `harga`, `foto`, `stok`, `status`) VALUES
(1, 1, 'Nasi Goreng Special', 'Nasi goreng dengan rempah pilihan, telur, dan ayam suwir.', 25000.00, 'nasgor.jpg', 50, 'tersedia'),
(2, 1, 'Mie Goreng Dok-Dok', 'Mie goreng gurih khas cafe dengan sosis dan sayuran segar.', 23000.00, 'miegoreng.jpg', 50, 'tersedia'),
(3, 1, 'Chicken Steak', 'Dada ayam panggang saus lada hitam served with french fries.', 35000.00, 'steak.jpg', 30, 'tersedia'),
(4, 2, 'French Fries', 'Kentang goreng renyah dengan taburan bumbu balado/asin.', 18000.00, 'fries.jpg', 100, 'tersedia'),
(5, 2, 'Chicken Wings', 'Sayap ayam goreng bersalut saus BBQ pedas manis.', 28000.00, 'wings.jpg', 40, 'tersedia'),
(6, 4, 'Es Kopi Susu Potluck', 'Perpaduan espresso espresso, gula aren murni, dan susu segar.', 20000.00, 'kobisus.jpg', 100, 'tersedia'),
(7, 4, 'Americano', 'Espresso shot ganda disajikan dingin atau hangat.', 18000.00, 'americano.jpg', 100, 'tersedia'),
(8, 4, 'Cappuccino', 'Espresso nikmat dikombinasikan dengan steamed milk tebal.', 22000.00, 'cappuccino.jpg', 100, 'tersedia'),
(9, 5, 'Matcha Latte', 'Matcha Jepang autentik dengan susu pilihan yang creamy.', 24000.00, 'matcha.jpg', 80, 'tersedia'),
(10, 5, 'Lemon Tea', 'Teh segar diseduh alami dengan perasan jeruk lemon asli.', 15000.00, 'lemontea.jpg', 100, 'tersedia');

-- 5. Tabel pesanan
CREATE TABLE `pesanan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_pesanan` VARCHAR(20) NOT NULL UNIQUE,
  `meja_id` INT NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `metode_pembayaran` VARCHAR(50) NOT NULL,
  `status` ENUM('Menunggu', 'Diproses', 'Siap Disajikan', 'Selesai', 'Dibatalkan') DEFAULT 'Menunggu',
  `waktu_pesanan` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`meja_id`) REFERENCES `meja`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabel detail_pesanan
CREATE TABLE `detail_pesanan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pesanan_id` INT NOT NULL,
  `menu_id` INT NOT NULL,
  `jumlah` INT NOT NULL,
  `harga` DECIMAL(10,2) NOT NULL,
  `catatan` TEXT DEFAULT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`menu_id`) REFERENCES `menu`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dummy Pesanan untuk Dashboard
INSERT INTO `pesanan` (`id`, `nomor_pesanan`, `meja_id`, `total`, `metode_pembayaran`, `status`, `waktu_pesanan`) VALUES
(1, 'PLK08601', 8, 65000.00, 'Bayar di Kasir', 'Diproses', NOW());

INSERT INTO `detail_pesanan` (`pesanan_id`, `menu_id`, `jumlah`, `harga`, `catatan`, `subtotal`) VALUES
(1, 1, 1, 25000.00, 'Jangan terlalu pedas', 25000.00),
(1, 6, 2, 20000.00, 'Sedikit es', 40000.00);