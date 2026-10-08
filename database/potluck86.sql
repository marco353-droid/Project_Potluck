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
(1, 'Administrator', 'admin', '$2y$10$fZfuxu4xnUz5201IIFw9SOIJDQBFIEf/m61q5uyMqOY8zWymus9g2', 'admin'),
(2, 'Tim Dapur', 'dapur', '$2y$10$vZD8lERZ1htk8VsS5Qg0/uz3l6KhZXXcGU8gi8YkJQGcKg5lof8Wa', 'dapur');

-- 1a. Pengaturan status penerimaan pesanan
CREATE TABLE IF NOT EXISTS `pengaturan_pesanan` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  `status` ENUM('buka', 'tutup') NOT NULL DEFAULT 'buka',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `pengaturan_pesanan` (`id`, `status`) VALUES (1, 'buka');

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
(1, 'Coffee'), (2, 'Non-Coffee'), (3, 'Snacks'), (4, 'Desserts'), (5, 'Main Course'),(6,'Tomyum'),(7, 'Rice Bowl'), (8, 'Kids Meal'), (9, 'Addons');


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

-- Coffee
(1, 1, 'Kopi 86', 'Kopi khas potluck dengan rasa yang khas.', 20000.00, 'kopi.jpg', 50, 'tersedia'),
(2, 1, 'Jaga ', 'Kopi khas potluck dengan rasa aren yang khas.', 35000.00, 'kopi.jpg', 50, 'tersedia'),
(3, 1, 'Mojang', 'Kopi khas potluck dengan rasa Melon yang khas.', 35000.00, 'kopi.jpg', 50, 'tersedia'),
(4, 1, 'Segara', 'Kopi khas potluck dengan rasa Caramel Sea Salt yang khas.', 35000.00, 'kopi.jpg', 50, 'tersedia'),
(5, 1, 'Yuan Yang', 'Kopi khas potluck dengan rasa yang khas.', 20000.00, 'kopi.jpg', 50, 'tersedia'),
(6, 1, 'Kopi Susu', 'Kopi khas potluck dengan rasa yang khas.', 20000.00, 'kopi.jpg', 50, 'tersedia'),
(7, 1, 'Espresso', 'Espresso khas potluck.', 15000.00, 'kopi.jpg', 50, 'tersedia'),
(8, 1, 'On The Rock', 'Kopi khas potluck dengan rasa yang khas.', 20000.00, 'kopi.jpg', 50, 'tersedia'),
(9, 1, 'Americano', 'Americano khas potluck.', 22000.00, 'kopi.jpg', 50, 'tersedia'),
(10, 1, 'Picolo', 'Kopi khas potluck dengan rasa yang khas.', 25000.00, 'kopi.jpg', 50, 'tersedia'),
(11, 1, 'Latte', 'Latte khas potluck.', 28000.00, 'kopi.jpg', 50, 'tersedia'),
(12, 1, 'Cappuccino', 'Cappuccino khas potluck.', 28000.00, 'kopi.jpg', 50, 'tersedia'),
(13, 1, 'Coconut Latte', 'Latte dengan rasa kelapa.', 25000.00, 'kopi.jpg', 50, 'tersedia'),
(14, 1, 'Caramel Macchiato', 'Kopi khas potluck dengan rasa yang khas.', 25000.00, 'kopi.jpg', 50, 'tersedia'),
(15, 1, 'Butterscotch', 'Kopi khas potluck dengan rasa yang khas.', 25000.00, 'kopi.jpg', 50, 'tersedia'),

-- Non Coffee
(16, 2, 'Milo 86', 'Milo', 22000.00, 'milo.jpg', 50, 'tersedia'),
(17, 2, 'Choco 86', 'Minuman Chocolate Khas potluck', 25000.00, 'choco.jpg', 50, 'tersedia'),
(18, 2, 'Original ThaiTea', 'Tea khas potluck dengan rasa yang khas.', 22000.00, 'thaimassage.jpg', 50, 'tersedia'),
(19, 2, 'Original ThaiGreenTea', 'Green Tea khas potluck dengan rasa yang khas.', 22000.00, 'thaimassage.jpg', 50, 'tersedia'),
<<<<<<< HEAD
(20, 2, 'Coconut Matcha', 'Matcha khas potluck dengan rasa kelapa.', 25000.00, 'coconutmatcha.jpg', 50, 'tersedia'),
(21, 2, 'Coconut Pandan', 'Pandan khas potluck dengan rasa kelapa.', 25000.00, 'coconutpandan.jpg', 50, 'tersedia'),
=======
(20, 2, 'Coconut Matcha', 'Matcha khas potluck dengan rasa kelapa.', 25000.00, 'matcha.jpg', 50, 'tersedia'),
(21, 2, 'Coconut Pandan', 'Pandan khas potluck dengan rasa kelapa.', 25000.00, 'thaimassage.jpg', 50, 'tersedia'),
>>>>>>> 6586087aff3a243b94fb26737c14cba16e839a3b

-- Milk
(22, 2, 'Fresh Milk', 'Susu segar khas potluck.', 20000.00, 'milk.jpg', 50, 'tersedia'),
(23, 2, 'Chocolate Milk', 'Susu cokelat khas potluck.', 20000.00, 'chocolate_milk.jpg', 50, 'tersedia'),
(24, 2, 'Caramel Milk', 'Susu karamel khas potluck.', 25000.00, 'caramel_milk.jpg', 50, 'tersedia'),
(25, 2, 'Hazelnut Milk', 'Susu hazelnut khas potluck.', 25000.00, 'hazelnut_milk.jpg', 50, 'tersedia'),
(26, 2, 'Vanilla Milk', 'Susu vanilla khas potluck.', 25000.00, 'vanilla_milk.jpg', 50, 'tersedia'),
<<<<<<< HEAD
(27, 2, 'Lychee Milk', 'Susu lychee khas potluck.', 25000.00, 'lychee_milk.jpg', 50, 'tersedia'),
(28, 2, 'Peach Milk', 'Susu peach khas potluck.', 25000.00, 'peach_milk.jpg', 50, 'tersedia'),
    (29, 2, 'Strawberry Milk', 'Susu stroberi khas potluck.', 25000.00, 'strawberry_milk.jpg', 50, 'tersedia'),

-- MockTail
(30, 2, 'Summer Light', 'Mocktail khas potluck dengan rasa yang menyenangkan.', 28000.00, 'mocktail.jpg', 50, 'tersedia'),
(31, 2, 'Lychee Tea', 'Tea khas potluck dengan rasa lychee.', 28000.00, 'lychee_tea.jpg', 50, 'tersedia'),
(32, 2, 'Peach Tea', 'Tea khas potluck dengan rasa peach.', 28000.00, 'peach_tea.jpg', 50, 'tersedia'),
(33, 2, 'Purple Sea', 'Tea khas potluck dengan rasa yang khas.', 28000.00, 'purple_sea.jpg', 50, 'tersedia'),
(34, 2, 'Pink Lady', 'Mocktail khas potluck dengan rasa yang menyenangkan.', 28000.00, 'pink_lady.jpg', 50, 'tersedia'),
(35, 2, 'Blue Lagoon', 'Mocktail khas potluck dengan rasa yang menyenangkan.', 28000.00, 'blue_lagoon.jpg', 50, 'tersedia'),
(36, 2, 'Ocean Wave', 'Tea khas potluck dengan rasa yang menyenangkan.', 28000.00, 'ocean_wave.jpg', 50, 'tersedia'),
=======
(27, 2, 'Lychee Milk', 'Susu lychee khas potluck.', 25000.00, 'thaimassage.jpg', 50, 'tersedia'),
(28, 2, 'Peach Milk', 'Susu peach khas potluck.', 25000.00, 'thaimassage.jpg', 50, 'tersedia'),
    (29, 2, 'Strawberry Milk', 'Susu stroberi khas potluck.', 25000.00, 'thaimassage.jpg', 50, 'tersedia'),

-- MockTail
(30, 2, 'Summer Light', 'Mocktail khas potluck dengan rasa yang menyenangkan.', 28000.00, 'mocktail.jpg', 50, 'tersedia'),
(31, 2, 'Lychee Tea', 'Tea khas potluck dengan rasa lychee.', 28000.00, 'thaimassage.jpg', 50, 'tersedia'),
(32, 2, 'Peach Tea', 'Tea khas potluck dengan rasa peach.', 28000.00, 'thaimassage.jpg', 50, 'tersedia'),
(33, 2, 'Purple Sea', 'Tea khas potluck dengan rasa yang khas.', 28000.00, 'thaimassage.jpg', 50, 'tersedia'),
(34, 2, 'Pink Lady', 'Mocktail khas potluck dengan rasa yang menyenangkan.', 28000.00, 'thaimassage.jpg', 50, 'tersedia'),
(35, 2, 'Blue Lagoon', 'Mocktail khas potluck dengan rasa yang menyenangkan.', 28000.00, 'thaimassage.jpg', 50, 'tersedia'),
(36, 2, 'Ocean Wave', 'Tea khas potluck dengan rasa yang menyenangkan.', 28000.00, 'thaimassage.jpg', 50, 'tersedia'),
>>>>>>> 6586087aff3a243b94fb26737c14cba16e839a3b

-- another drinks
(37, 2, 'Mineral Water', 'Air mineral segar.', 10000.00, 'mineral_water.jpg', 50, 'tersedia'),
(38, 2, 'Teh ', 'Minuman teh.', 6000.00, 'teh.jpg', 50, 'tersedia'),
(39, 2, 'Ocha Tea', 'Minuman tea ocha segar.', 10000.00, 'ocha_tea.jpg', 50, 'tersedia'),
(40, 2, 'Lemon Tea', 'Teh lemon segar.', 20000.00, 'iced_tea.jpg', 50, 'tersedia'),
(41, 2, 'Teh Tarik', 'Minuman teh tarik khas.', 20000.00, 'iced_coffee.jpg', 50, 'tersedia'),
(42, 2, 'Ice Orange', 'Minuman es orange.', 20000.00, 'iced_orange.jpg', 50, 'tersedia'),
(43, 2, 'Ice Lychee', 'Minuman es lychee.', 20000.00, 'iced_lychee.jpg', 50, 'tersedia'),
(44, 2, 'Honey Lemon', 'Minuman honey   lemon segar.', 22000.00, 'Honey_Lemon.jpg', 50, 'tersedia'),
(45, 2, 'jeruk Peras', 'Minuman jeruk peras segar.', 22000.00, 'jeruk_peras.jpg', 50, 'tersedia'),

-- Snacks
(46, 3, 'Telur Ayam Kampung', 'Telur ayam kampung segar, dimasak dengan cara yang lezat.', 17000.00, 'telur.jpg', 50, 'tersedia'),
(47, 3, 'Toast Butter Sugar', 'Roti panggang dengan mentaga dan taburan gula halus, cocok untuk sarapan.', 18000.00, 'TBS.jpg', 50, 'tersedia'),
(48, 3, 'Toast Butter Srikaya', 'Roti panggang dengan mentaga dan taburan srikaya, cocok untuk sarapan.', 18000.00, 'rotibakar.jpg', 50, 'tersedia'),
(49, 3, 'Toast Keju', 'Roti panggang dengan mentaga dan taburan keju, cocok untuk sarapan.', 18000.00, 'rotibakar.jpg', 50, 'tersedia'),
(50, 3, 'Toast Jainudin', 'Roti panggang dengan mentaga dan taburan jainudin, cocok untuk sarapan.', 18000.00, 'rotibakar.jpg', 50, 'tersedia'),
(51, 3, 'Smoked Chicken Sandwich', 'Sandwich dengan ayam asap, sayuran segar, dan saus spesial.', 25000.00, 'scs.jpg', 50, 'tersedia'),
(52, 3, 'Pisang Goreng Srikaya', 'Pisang goreng renyah dengan taburan gula halus dan saus srikaya.', 18000.00, 'pisanggoreng.jpg', 50, 'tersedia'),
(53, 3, 'Pisang Goreng Keju', 'Pisang goreng renyah dengan taburan keju parut dan susu.', 20000.00, 'pisanggoreng.jpg', 50, 'tersedia'),
(54, 3, 'Singkong', 'Singkong renyah.', 20000.00, 'singkong.jpg', 50, 'tersedia'),
(55, 3, 'French Fries', 'Kentang goreng renyah dengan saus karamel manis dan taburan kacang.', 18000.00, 'fries.jpg', 50, 'tersedia'),
(56, 3, 'Eggy Chicken Roll', 'Roti gulung dengan isian ayam, telur, dan sayuran segar.', 20000.00, 'eggroll.jpg', 50, 'tersedia'),
(57, 3, 'Shrimpy Roll', 'Roti gulung dengan isian udang, telur, dan sayuran segar.', 20000.00, 'eggroll.jpg', 50, 'tersedia'),
(58, 3, 'Beef Cheese Burger', 'Burger daging sapi dengan keju leleh, selada, dan saus spesial.', 35000.00, 'burger.jpg', 50, 'tersedia'),
(59, 3, 'Chicken Burger', 'Burger ayam dengan keju leleh, selada, dan saus spesial.', 32000.00, 'burger.jpg', 50, 'tersedia'),


-- Deserts
(60, 4, 'Ice Kacang Leci', 'Es kacang merah dengan sirup manis dan taburan kacang.', 25000.00, 'es_kacang.jpg', 50, 'tersedia'),
(61, 4, 'Ice Kacang Orange', 'Es kacang merah dengan sirup orange dan taburan kacang.', 25000.00, 'es_kacang.jpg', 50, 'tersedia'),

-- Main Course
(62, 5, 'Sate Kuah', 'Sate ayam dengan kuah kacang yang lezat.', 29000.00, 'sate_kuah.jpg', 50, 'tersedia'),
(63, 5, 'Mie Keriting 86', 'Mie keriting dengan telur, ayam, dan sayuran segar.', 18000.00, 'mie_keriting.jpg', 50, 'tersedia'),
(64, 5, 'Mie Pok 86', 'Mie pok dengan telur, ayam, dan sayuran segar.', 18000.00, 'mie_goreng.jpg', 50, 'tersedia'),
(65, 5, 'Mie keriting Bakso 86', 'Mie keriting dengan bakso, telur, dan sayuran segar.', 26000.00, 'mie_keriting.jpg', 50, 'tersedia'),
(66, 5, 'Mie Pok Bakso 86', 'Mie pok dengan bakso, telur, dan sayuran segar.', 26000.00, 'mie_goreng.jpg', 50, 'tersedia'),

-- tomyum
(67, 6, 'Tomyum Chicken Ramen', 'Ramen dengan kuah tomyum pedas dan ayam.', 32000.00, 'tomyum.jpg', 50, 'tersedia'),
(68, 6, 'Tomyum Beef Ramen', 'Ramen dengan kuah tomyum pedas dan daging sapi.', 34000.00, 'tomyum.jpg', 50, 'tersedia'),
(69, 6, 'Tomyum Shrimp Ramen', 'Ramen dengan kuah tomyum pedas dan udang.', 38000.00, 'tomyum.jpg', 50, 'tersedia'),
(70, 6, 'Tomyum Chicken Friedrice', 'Nasi goreng dengan kuah tomyum pedas dan ayam.', 32000.00, 'tomyum.jpg', 50, 'tersedia'),
(87, 6, 'Tomyum Beef Friedrice', 'Nasi goreng dengan kuah tomyum pedas dan daging sapi.', 34000.00, 'tomyum.jpg', 50, 'tersedia'),
(88, 6, 'Tomyum Shrimp Friedrice', 'Nasi goreng dengan kuah tomyum pedas dan udang.', 38000.00, 'tomyum.jpg', 50, 'tersedia'),

-- Rice Bowl

(89, 7, 'Kiassu Salted Egg', 'Nasi dengan telur asin, sayuran segar, dan saus spesial.', 32000.00, 'rice_bowl.jpg', 50, 'tersedia'),
(90, 7, 'Gringo BBQ', 'Nasi dengan ayam BBQ, sayuran segar, dan saus spesial.', 32000.00, 'rice_bowl.jpg', 50, 'tersedia'),
(91, 7, 'Honey Oppa', 'Nasi dengan ayam honey, sayuran segar, dan saus spesial.', 32000.00, 'rice_bowl.jpg', 50, 'tersedia'),
(92, 7, 'ChickenPop Geprek', 'Nasi dengan ayam BBQ, sayuran segar, dan saus spesial.', 26000.00, 'rice_bowl.jpg', 50, 'tersedia'),
(93, 7, 'Fish Katsu', 'Nasi dengan ikan katsu, sayuran segar, dan saus spesial.', 32000.00, 'rice_bowl.jpg', 50, 'tersedia'),
(94, 7, 'Beef Belly', 'Nasi dengan daging sapi, Black pepper , mushroom dan saus Spicy Butter.', 38000.00, 'rice_bowl.jpg', 50, 'tersedia'),
(95, 7, 'Wagyu Beef', 'Nasi dengan daging sapi Wagyu, Black pepper , mushroom dan saus Spicy Butter.', 45000.00, 'rice_bowl.jpg', 50, 'tersedia'),
(96, 7, 'Bulgogi Beef', 'Nasi dengan daging sapi Bulgogi.', 38000.00, 'rice_bowl.jpg', 50, 'tersedia'),
(97, 7, 'Smoked Beef Ricebowl', 'Nasi dengan daging sapi smoked, sayuran segar, dan saus spesial.', 38000.00, 'rice_bowl.jpg', 50, 'tersedia'),
(98, 7, 'Smoked Chicken Ricebowl', 'Nasi dengan daging ayam smoked, sayuran segar, dan saus spesial.', 32000.00, 'rice_bowl.jpg', 50, 'tersedia'),
(99, 7, 'Smoked Beef Friedrice', 'Nasi goreng dengan daging sapi smoked, sayuran segar, dan saus spesial.', 34000.00, 'rice_bowl.jpg', 50, 'tersedia'),
(100, 7, 'Smoked Chicken Friedrice', 'Nasi goreng dengan daging ayam smoked, sayuran segar, dan saus spesial.', 34000.00, 'rice_bowl.jpg', 50, 'tersedia'),

-- Addons
(101, 9, 'Rice', 'Tambahan nasi.', 6000.00, 'addons.jpg', 50, 'tersedia'),
(102, 9, 'Telur Matamoe', 'Tambahan telur matamoe.', 5000.00, 'addons.jpg', 50, 'tersedia'),
(103, 9, 'Mayo Original', 'Tambahan mayo original.', 4000.00, 'addons.jpg', 50, 'tersedia'),
(104, 9, 'Sambal', 'Tambahan sambal Mata/Reggae/Rookie.', 4000.00, 'addons.jpg', 50, 'tersedia');






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