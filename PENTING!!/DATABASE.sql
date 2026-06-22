--
--
--    !!PENTING !!
-- INI MERUPAKAN ISI DARI DATABASE LOKAL. KALAU MAU MELIHAT ISI WEBSITE DENGAN LENGKAP, PASTIKAN ANDA MEMBUAT DATABASE DENGAN NAMA lombok_tourism. SETELAH ITU, PILIH DATABASE lombok_tourism, klik SQL, lalu paste semua code ini.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lombok_tourism`
--

-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

CREATE TABLE `komentar` (
  `id` int(11) NOT NULL,
  `wisata_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `komentar` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `komentar_foto`
--

CREATE TABLE `komentar_foto` (
  `id` int(11) NOT NULL,
  `komentar_id` int(11) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `foto_profile` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`id`, `user_id`, `full_name`, `bio`, `phone`, `location`, `foto_profile`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrator', 'Admin website Lombok Tourism', '081070720007', 'Mataram', 'profile/6a3773de63bba_1782019038.jpg', '2026-06-21 04:20:12', '2026-06-21 05:17:18'),
(2, 3, 'bon', NULL, NULL, NULL, NULL, '2026-06-21 04:30:03', '2026-06-21 04:30:03');

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `id` int(11) NOT NULL,
  `wisata_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_name', 'Lombok Tourism', '2026-06-21 04:20:12'),
(2, 'site_tagline', 'Surga Tersembunyi di Nusa Tenggara', '2026-06-21 04:20:12'),
(3, 'hero_bg', 'profile/6a3775229d998_1782019362.jpg', '2026-06-21 05:22:42'),
(4, 'hero_title', 'Jelajahi Keindahan Lombok', '2026-06-21 04:20:12'),
(5, 'hero_subtitle', 'Temukan destinasi wisata terbaik di Pulau Seribu Masjid', '2026-06-21 04:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `tempat_wisata`
--

CREATE TABLE `tempat_wisata` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga_tiket` decimal(10,2) DEFAULT 0.00,
  `jam_buka` time DEFAULT NULL,
  `jam_tutup` time DEFAULT NULL,
  `rating_awal` decimal(3,1) DEFAULT 0.0,
  `link_lokasi` varchar(500) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT 'Wisata Alam',
  `is_featured` tinyint(1) DEFAULT 0,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tempat_wisata`
--

INSERT INTO `tempat_wisata` (`id`, `nama`, `deskripsi`, `harga_tiket`, `jam_buka`, `jam_tutup`, `rating_awal`, `link_lokasi`, `latitude`, `longitude`, `kategori`, `is_featured`, `admin_id`, `created_at`, `updated_at`) VALUES
(1, 'Pantai Kuta Lombok', 'Pantai Kuta Lombok menawarkan keindahan pasir putih yang luar biasa dengan air biru jernih. Berbeda dengan Kuta di Bali, pantai ini masih relatif sepi dan alami. Gelombang yang ideal untuk surfing dan pemandangan matahari terbenam yang memukau.', 10000.00, '06:00:00', '18:00:00', 0.0, 'https://maps.app.goo.gl/example1', -8.8853000, 116.2754000, 'Pantai', 1, 1, '2026-06-21 04:20:12', '2026-06-22 03:28:57'),
(2, 'Gunung Rinjani', 'Gunung Rinjani adalah gunung berapi aktif kedua tertinggi di Indonesia dengan ketinggian 3.726 mdpl. Pendakian ke puncak Rinjani menawarkan pemandangan spektakuler Danau Segara Anak dan panorama pulau-pulau sekitarnya.', 150000.00, '00:00:00', '23:59:00', 0.0, 'https://maps.app.goo.gl/example2', -8.4109000, 116.4567000, 'Gunung', 1, 1, '2026-06-21 04:20:12', '2026-06-22 03:28:57'),
(3, 'Gili Trawangan', 'Gili Trawangan adalah pulau terbesar dari tiga Gili yang terkenal. Dikenal dengan keindahan bawah lautnya, snorkeling, diving, dan suasana pantai yang meriah. Tidak ada kendaraan bermotor di pulau ini, hanya sepeda dan cidomo.', 0.00, '00:00:00', '23:59:00', 0.0, 'https://maps.app.goo.gl/jNykrQwKCxyhd6WR9', -8.3493000, 116.0420000, 'Pulau', 1, 1, '2026-06-21 04:20:12', '2026-06-22 03:28:57'),
(4, 'Air Terjun Sendang Gile', 'Air terjun yang memukau di kaki Gunung Rinjani dengan ketinggian sekitar 30 meter. Dikelilingi hutan tropis yang rimbun, air terjun ini menawarkan suasana sejuk dan pemandangan alam yang menakjubkan.', 20000.00, '07:00:00', '17:00:00', 0.0, 'https://maps.app.goo.gl/example4', -8.3978000, 116.4193000, 'Alam', 0, 1, '2026-06-21 04:20:12', '2026-06-22 03:28:57'),
(5, 'Desa Sade', 'Desa tradisional suku Sasak yang masih mempertahankan adat dan budaya asli Lombok. Pengunjung dapat melihat rumah adat, tenun tradisional Sasak, dan berbagai ritual budaya yang unik.', 10000.00, '08:00:00', '17:00:00', 0.0, 'https://maps.app.goo.gl/example5', -8.8709000, 116.2879000, 'Budaya', 0, 1, '2026-06-21 04:20:12', '2026-06-22 03:28:57'),
(6, 'Pantai Senggigi', 'Pantai Senggigi adalah salah satu destinasi wisata paling populer di Lombok. Dengan pemandangan Gunung Agung Bali di kejauhan dan sunset yang indah, pantai ini menjadi favorit wisatawan lokal maupun mancanegara.', 5000.00, '06:00:00', '18:00:00', 0.0, 'https://maps.app.goo.gl/example6', -8.4923000, 116.0414000, 'Pantai', 1, 1, '2026-06-21 04:20:12', '2026-06-22 03:28:57'),
(7, 'Selong Belanak', 'Selong Belanak adalah sebuah desa sekaligus kawasan pantai yang terletak di bagian selatan Pulau Lombok, tepatnya di Selong Belanak. Kawasan ini terkenal karena memiliki pantai berpasir putih yang luas, air laut yang jernih, serta garis pantai berbentuk teluk yang melengkung indah.\r\n\r\nPantai di Selong Belanak menawarkan suasana yang tenang dan alami, sehingga menjadi salah satu destinasi wisata favorit bagi wisatawan domestik maupun mancanegara. Ombaknya yang relatif bersahabat di beberapa bagian pantai menjadikannya lokasi yang cocok untuk belajar berselancar, sementara area lain yang lebih tenang sering dimanfaatkan untuk berenang dan bersantai.\r\n\r\nDi sekitar pantai, pengunjung dapat menikmati pemandangan perbukitan hijau yang mengelilingi teluk, menciptakan panorama yang memadukan keindahan laut dan alam pegunungan. Aktivitas masyarakat setempat, seperti nelayan yang berangkat dan kembali melaut, juga menambah daya tarik budaya kawasan ini.\r\n\r\nSelain keindahan alamnya, Selong Belanak dikenal sebagai salah satu kawasan yang masih mempertahankan suasana pedesaan khas Lombok. Keramahan masyarakat lokal, kuliner laut yang segar, serta pemandangan matahari terbenam yang memukau menjadikan Selong Belanak destinasi yang menawarkan pengalaman wisata alam dan budaya yang berkesan.', 5000.00, '00:00:00', '23:59:00', 0.0, 'https://maps.google.com/?q=Pantai+Selong+Belanak+Lombok&amp;utm_source=chatgpt.com', NULL, NULL, 'Pantai', 0, 1, '2026-06-22 00:01:36', '2026-06-22 00:08:04'),
(8, 'sesaot', 'Sesaot adalah sebuah desa yang terletak di Kecamatan Narmada, Kabupaten Lombok Barat, Provinsi Nusa Tenggara Barat. Desa Sesaot dikenal sebagai kawasan wisata alam yang memiliki hutan lindung, sumber mata air yang melimpah, serta sungai yang jernih dan sejuk.\r\n\r\nDaya tarik utama Sesaot adalah kawasan Hutan Wisata Sesaot, yang menjadi bagian dari kawasan hutan lindung di lereng Gunung Rinjani. Lingkungan yang masih asri dengan pepohonan rindang menciptakan udara yang segar dan suasana yang tenang, sehingga cocok untuk rekreasi keluarga, berkemah, maupun kegiatan wisata alam.\r\n\r\nSungai yang mengalir di kawasan ini memiliki air yang sangat jernih dan berasal dari sumber mata air pegunungan. Banyak wisatawan datang untuk berenang, bersantai di tepi sungai, atau menikmati kuliner khas Lombok di gazebo-gazebo yang berada di sekitar area wisata.', 0.00, '08:00:00', '17:00:00', 0.0, 'https://maps.google.com/?q=Desa+Sesaot+Lombok+Barat&amp;amp;amp;amp;utm_source=chatgpt.com', NULL, NULL, 'Alam', 0, 1, '2026-06-22 00:15:10', '2026-06-22 00:19:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@lomboktravel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-06-21 04:20:12', '2026-06-21 04:20:12'),
(3, 'bon', 'bon1234@gmail.com', '$2y$10$ldYz22ALcs6EU9cyhYEpqu7g/N0lL7aNcLp11qP4ypXsnxyCBlmLi', 'user', '2026-06-21 04:30:03', '2026-06-21 04:30:03');

-- --------------------------------------------------------

--
-- Table structure for table `wisata_foto`
--

CREATE TABLE `wisata_foto` (
  `id` int(11) NOT NULL,
  `wisata_id` int(11) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `caption` varchar(200) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wisata_foto`
--

INSERT INTO `wisata_foto` (`id`, `wisata_id`, `foto`, `caption`, `is_primary`, `created_at`) VALUES
(1, 2, 'wisata/6a37694e3b7ba_1782016334.jpg', NULL, 1, '2026-06-21 04:32:14'),
(2, 2, 'wisata/6a37694e3d678_1782016334.jpg', NULL, 0, '2026-06-21 04:32:14'),
(3, 2, 'wisata/6a37695bf0d00_1782016347.jpg', NULL, 0, '2026-06-21 04:32:27'),
(4, 3, 'wisata/6a37698527151_1782016389.webp', NULL, 1, '2026-06-21 04:33:09'),
(5, 3, 'wisata/6a37698528d78_1782016389.jpg', NULL, 0, '2026-06-21 04:33:09'),
(6, 3, 'wisata/6a3769852a635_1782016389.jpg', NULL, 0, '2026-06-21 04:33:09'),
(7, 6, 'wisata/6a3769c93336c_1782016457.jpg', NULL, 1, '2026-06-21 04:34:17'),
(8, 6, 'wisata/6a3769c93503a_1782016457.jpg', NULL, 0, '2026-06-21 04:34:17'),
(9, 6, 'wisata/6a3769c936c37_1782016457.jpg', NULL, 0, '2026-06-21 04:34:17'),
(10, 4, 'wisata/6a376ef3dd36f_1782017779.png', NULL, 1, '2026-06-21 04:56:19'),
(11, 4, 'wisata/6a376ef3dfd53_1782017779.jpg', NULL, 0, '2026-06-21 04:56:19'),
(12, 4, 'wisata/6a376efb87b89_1782017787.jpg', NULL, 0, '2026-06-21 04:56:27'),
(13, 5, 'wisata/6a377115a8cd0_1782018325.jpg', NULL, 0, '2026-06-21 05:05:25'),
(14, 5, 'wisata/6a377115aab08_1782018325.jpg', NULL, 0, '2026-06-21 05:05:25'),
(15, 5, 'wisata/6a3771217ea12_1782018337.jpg', NULL, 1, '2026-06-21 05:05:37'),
(16, 1, 'wisata/6a3774743bc59_1782019188.jpeg', NULL, 1, '2026-06-21 05:19:48'),
(17, 1, 'wisata/6a3774743e013_1782019188.jpg', NULL, 0, '2026-06-21 05:19:48'),
(18, 1, 'wisata/6a3774743fb7c_1782019188.jpg', NULL, 0, '2026-06-21 05:19:48'),
(19, 7, 'wisata/6a387c7b27bcb_1782086779.jpg', NULL, 0, '2026-06-22 00:06:19'),
(20, 7, 'wisata/6a387c84abcf8_1782086788.jpg', NULL, 0, '2026-06-22 00:06:28'),
(21, 7, 'wisata/6a387ca735c2f_1782086823.jpeg', NULL, 1, '2026-06-22 00:07:03'),
(22, 8, 'wisata/6a387e8e2bf4e_1782087310.jpg', '', 1, '2026-06-22 00:15:10'),
(23, 8, 'wisata/6a387f6057ad6_1782087520.jpg', NULL, 0, '2026-06-22 00:18:40'),
(24, 8, 'wisata/6a387f7b965e6_1782087547.jpeg', NULL, 0, '2026-06-22 00:19:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `komentar`
--
ALTER TABLE `komentar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wisata_id` (`wisata_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `komentar_foto`
--
ALTER TABLE `komentar_foto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `komentar_id` (`komentar_id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`wisata_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `tempat_wisata`
--
ALTER TABLE `tempat_wisata`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wisata_foto`
--
ALTER TABLE `wisata_foto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wisata_id` (`wisata_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `komentar`
--
ALTER TABLE `komentar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `komentar_foto`
--
ALTER TABLE `komentar_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tempat_wisata`
--
ALTER TABLE `tempat_wisata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wisata_foto`
--
ALTER TABLE `wisata_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `komentar`
--
ALTER TABLE `komentar`
  ADD CONSTRAINT `komentar_ibfk_1` FOREIGN KEY (`wisata_id`) REFERENCES `tempat_wisata` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `komentar_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `komentar_foto`
--
ALTER TABLE `komentar_foto`
  ADD CONSTRAINT `komentar_foto_ibfk_1` FOREIGN KEY (`komentar_id`) REFERENCES `komentar` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`wisata_id`) REFERENCES `tempat_wisata` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tempat_wisata`
--
ALTER TABLE `tempat_wisata`
  ADD CONSTRAINT `tempat_wisata_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wisata_foto`
--
ALTER TABLE `wisata_foto`
  ADD CONSTRAINT `wisata_foto_ibfk_1` FOREIGN KEY (`wisata_id`) REFERENCES `tempat_wisata` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
