--
--
--    !!PENTING !!
-- INI MERUPAKAN ISI DARI DATABASE LOKAL. KALAU MAU MELIHAT ISI WEBSITE DENGAN LENGKAP, PASTIKAN ANDA MEMBUAT DATABASE DENGAN NAMA lombok_tourism. SETELAH ITU, PILIH DATABASE lombok_tourism, klik SQL, lalu paste semua code ini.

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2026 at 07:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
-- Table structure for table `hotel`
--

CREATE TABLE `hotel` (
  `id` int(11) NOT NULL,
  `nama_hotel` varchar(150) NOT NULL,
  `lokasi` varchar(150) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga_per_malam` decimal(12,2) DEFAULT 0.00,
  `foto` varchar(255) DEFAULT NULL,
  `link_lokasi` varchar(500) DEFAULT NULL,
  `wisata_terdekat_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotel`
--

INSERT INTO `hotel` (`id`, `nama_hotel`, `lokasi`, `alamat`, `deskripsi`, `harga_per_malam`, `foto`, `link_lokasi`, `wisata_terdekat_id`, `admin_id`, `created_at`, `updated_at`) VALUES
(1, 'Pullman Lombok Merujani Mandalika Beach Resort', 'Kuta Mandalika', 'Jl. Pariwisata, Kuta, Pujut, Lombok Tengah', 'Resort mewah tepi pantai dengan kolam renang infinity dan pemandangan langsung ke Pantai Kuta. Fasilitas lengkap termasuk spa, gym, dan beberapa restoran.', 1800000.00, 'hotel/6a41fb7635615_1782709110.jpg', 'https://www.google.com/maps/search/Pullman+Lombok+Merujani+Mandalika+Beach+Resort', 1, 1, '2026-06-29 02:44:10', '2026-06-29 04:58:30'),
(2, 'Novotel Lombok Resort &amp;amp; Villas', 'Kuta Mandalika', 'Jl. Pantai Putri Nyale, Kuta, Lombok Tengah', 'Resort dengan vila pribadi dan akses langsung ke pantai. Cocok untuk keluarga dengan kolam anak dan area bermain.', 1200000.00, 'hotel/6a41fc22efb87_1782709282.jpg', 'https://www.google.com/maps/search/Novotel+Lombok+Resort+%26+Villas', 1, 1, '2026-06-29 02:44:10', '2026-06-29 05:01:23'),
(3, 'RinjaniView Eco Lodge', 'Senaru', 'Desa Senaru, Bayan, Lombok Utara', 'Penginapan sederhana dengan pemandangan langsung ke Gunung Rinjani. Titik awal favorit untuk pendakian dan trekking ke air terjun.', 350000.00, 'hotel/6a41fc6a682ff_1782709354.jpg', 'https://www.google.com/maps/search/RinjaniView+Eco+Lodge+Lombok', 2, 1, '2026-06-29 02:44:10', '2026-06-29 05:02:34'),
(4, 'Gili Trawangan Cottages', 'Gili Trawangan', 'Jl. Pantai Gili Trawangan, Lombok Utara', 'Cottage tepi pantai dengan akses snorkeling langsung dari penginapan. Tanpa kendaraan bermotor, suasana tenang dan asri.', 450000.00, 'hotel/6a41fd19b284a_1782709529.jpg', 'https://www.google.com/maps/search/Gili+Trawangan+Cottages', 3, 1, '2026-06-29 02:44:10', '2026-06-29 05:05:29'),
(5, 'Senggigi Beach Hotel', 'Senggigi', 'Jl. Raya Senggigi, Lombok Barat', 'Hotel klasik tepi Pantai Senggigi dengan pemandangan sunset terbaik. Dekat dengan pusat oleh-oleh dan kafe.', 650000.00, 'hotel/6a41fdaf2a90e_1782709679.webp', 'https://www.google.com/maps/search/Senggigi+Beach+Hotel+Lombok', 6, 1, '2026-06-29 02:44:10', '2026-06-29 05:07:59'),
(8, 'Gili Air Santai Hostel', 'Gili Air', 'Gili Air, Lombok Utara', 'Hostel ramah backpacker dengan kamar dorm dan privat, berjarak beberapa menit dari pelabuhan kecil Gili Air. Atmosfer santai dengan rooftop untuk menikmati sunset.', 250000.00, 'hotel/6a41fef481a9f_1782710004.jpg', 'https://www.google.com/maps/search/Gili+Air+Santai+Hostel', 11, 1, '2026-06-29 02:44:10', '2026-06-29 05:13:24'),
(9, 'Karma Reef Gili Meno', 'Gili Meno', 'Gili Meno, Lombok Utara', 'Resort tepi pantai dengan desain villa kayu yang menyatu dengan alam, dikenal karena ketenangannya jauh dari aktivitas Gili Trawangan yang ramai. Beach club di tepi laut menawarkan suasana romantis untuk pasangan.', 900000.00, 'hotel/6a41ff6238493_1782710114.jpg', 'https://www.google.com/maps/search/Karma+Reef+Gili+Meno', 12, 1, '2026-06-29 02:44:10', '2026-06-29 05:15:14'),
(10, 'Tetebatu Jungle Homestay', 'Tetebatu', 'Desa Tetebatu, Lombok Timur', 'Homestay keluarga dengan pemandangan sawah dan kebun kopi langsung dari teras kamar. Pemilik homestay dapat membantu mengatur trekking ke air terjun dan hutan monyet sekitar desa.', 300000.00, 'hotel/6a41ffc8f129d_1782710216.jpg', 'https://www.google.com/maps/search/Tetebatu+Jungle+Homestay+Lombok', 17, 1, '2026-06-29 02:44:10', '2026-06-29 05:16:57'),
(11, 'Lombok Astoria Hotel', 'Mataram', 'Jl. Pejanggik, Mataram, Lombok', 'Hotel bisnis di pusat kota Mataram dengan akses mudah ke bandara dan pusat perbelanjaan. Pilihan tepat untuk wisatawan yang transit atau memiliki urusan di pusat kota.', 380000.00, 'hotel/6a41ffff304df_1782710271.jpg', 'https://www.google.com/maps/search/Lombok+Astoria+Hotel+Mataram', NULL, 1, '2026-06-29 02:44:10', '2026-06-29 05:17:51');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_foto`
--

CREATE TABLE `hotel_foto` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `caption` varchar(200) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotel_foto`
--

INSERT INTO `hotel_foto` (`id`, `hotel_id`, `foto`, `caption`, `is_primary`, `created_at`) VALUES
(1, 1, 'hotel/6a41fb7635615_1782709110.jpg', NULL, 1, '2026-06-29 04:58:30'),
(2, 1, 'hotel/6a41fb763aa78_1782709110.jpg', NULL, 0, '2026-06-29 04:58:30'),
(3, 1, 'hotel/6a41fb7e83632_1782709118.jpg', NULL, 0, '2026-06-29 04:58:38'),
(4, 2, 'hotel/6a41fc22efb87_1782709282.jpg', NULL, 1, '2026-06-29 05:01:22'),
(5, 2, 'hotel/6a41fc22f1411_1782709282.jpg', NULL, 0, '2026-06-29 05:01:22'),
(6, 2, 'hotel/6a41fc22f2ec0_1782709282.jpg', NULL, 0, '2026-06-29 05:01:22'),
(7, 3, 'hotel/6a41fc6a682ff_1782709354.jpg', NULL, 1, '2026-06-29 05:02:34'),
(8, 3, 'hotel/6a41fc6a69320_1782709354.jpg', NULL, 0, '2026-06-29 05:02:34'),
(9, 3, 'hotel/6a41fc6a6a57c_1782709354.jpg', NULL, 0, '2026-06-29 05:02:34'),
(10, 4, 'hotel/6a41fd19b284a_1782709529.jpg', NULL, 1, '2026-06-29 05:05:29'),
(11, 4, 'hotel/6a41fd19b4197_1782709529.webp', NULL, 0, '2026-06-29 05:05:29'),
(12, 4, 'hotel/6a41fd19b580d_1782709529.webp', NULL, 0, '2026-06-29 05:05:29'),
(13, 5, 'hotel/6a41fdaf2a90e_1782709679.webp', NULL, 1, '2026-06-29 05:07:59'),
(14, 5, 'hotel/6a41fdaf2c382_1782709679.webp', NULL, 0, '2026-06-29 05:07:59'),
(15, 5, 'hotel/6a41fdaf2de5d_1782709679.jpg', NULL, 0, '2026-06-29 05:07:59'),
(16, 8, 'hotel/6a41fef481a9f_1782710004.jpg', NULL, 1, '2026-06-29 05:13:24'),
(17, 9, 'hotel/6a41ff6238493_1782710114.jpg', NULL, 1, '2026-06-29 05:15:14'),
(18, 10, 'hotel/6a41ffc8f129d_1782710216.jpg', NULL, 1, '2026-06-29 05:16:56'),
(19, 10, 'hotel/6a41ffc8f2b26_1782710216.jpg', NULL, 0, '2026-06-29 05:16:56'),
(20, 10, 'hotel/6a41ffc8f3f89_1782710216.jpeg', NULL, 0, '2026-06-29 05:16:57'),
(21, 11, 'hotel/6a41ffff304df_1782710271.jpg', NULL, 1, '2026-06-29 05:17:51');

-- --------------------------------------------------------

--
-- Table structure for table `hotel_rating`
--

CREATE TABLE `hotel_rating` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `komentar` text DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

--
-- Dumping data for table `komentar`
--

INSERT INTO `komentar` (`id`, `wisata_id`, `user_id`, `komentar`, `created_at`, `updated_at`) VALUES
(1, 2, 2, 'Gunung yang sangat indah. Tapi akan sangat indah jika aku pergi denganmu', '2026-06-29 05:43:26', '2026-06-29 05:43:26'),
(2, 2, 3, 'Bagus', '2026-06-29 05:47:07', '2026-06-29 05:47:07'),
(3, 2, 4, 'bagusss... tapi kemaren ga diajak😒😅', '2026-06-29 05:55:30', '2026-06-29 05:55:30');

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

--
-- Dumping data for table `komentar_foto`
--

INSERT INTO `komentar_foto` (`id`, `komentar_id`, `foto`, `created_at`) VALUES
(1, 1, 'komentar/6a4205feb9e46_1782711806.jpeg', '2026-06-29 05:43:26');

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
(1, 1, 'Administrator', 'Admin website Lombok Tourism', '', '', 'profile/6a41dc9b75b35_1782701211.jpg', '2026-06-29 02:44:10', '2026-06-29 02:46:51'),
(3, 2, 'Mr. Day', '', '', '', 'profile/6a42053eb1150_1782711614.jpeg', '2026-06-29 05:38:36', '2026-06-29 05:40:41'),
(6, 3, 'AfrarizaBon', '', '', 'Gerung', 'profile/6a4206b13c866_1782711985.jpeg', '2026-06-29 05:44:44', '2026-06-29 05:46:25'),
(8, 4, 'insania', '', '', '', 'profile/6a420823cd6ae_1782712355.jpg', '2026-06-29 05:49:35', '2026-06-29 05:52:35');

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

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`id`, `wisata_id`, `user_id`, `rating`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 5, '2026-06-29 05:46:39', '2026-06-29 05:46:39'),
(2, 2, 2, 4, '2026-06-29 05:48:26', '2026-06-29 05:48:26'),
(3, 2, 4, 5, '2026-06-29 05:55:00', '2026-06-29 05:55:00');

-- --------------------------------------------------------

--
-- Table structure for table `restoran`
--

CREATE TABLE `restoran` (
  `id` int(11) NOT NULL,
  `nama_restoran` varchar(150) NOT NULL,
  `lokasi` varchar(150) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga_rata_rata` decimal(12,2) DEFAULT 0.00,
  `foto` varchar(255) DEFAULT NULL,
  `link_lokasi` varchar(500) DEFAULT NULL,
  `wisata_terdekat_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restoran`
--

INSERT INTO `restoran` (`id`, `nama_restoran`, `lokasi`, `alamat`, `deskripsi`, `harga_rata_rata`, `foto`, `link_lokasi`, `wisata_terdekat_id`, `admin_id`, `created_at`, `updated_at`) VALUES
(1, 'Ayam Taliwang Bersaudara', 'Kuta Mandalika', 'Jl. Raya Kuta No. 12, Lombok Tengah', 'Rumah makan khas Sasak terkenal dengan ayam taliwang pedas dan plecing kangkung otentik. Favorit wisatawan lokal dan mancanegara.', 45000.00, 'restoran/6a41fb0fd0b27_1782709007.webp', 'https://maps.app.goo.gl/resto1', 1, 1, '2026-06-29 02:44:10', '2026-06-29 04:56:47'),
(2, 'Kober Cafe Mandalika', 'Kuta Mandalika', 'Jl. Pariwisata, Kuta, Lombok Tengah', 'Kafe santai dengan menu fusion dan pemandangan area sirkuit Mandalika. Cocok untuk makan siang setelah dari pantai.', 65000.00, 'restoran/6a4200ca06f7c_1782710474.webp', 'https://maps.app.goo.gl/resto2', 1, 1, '2026-06-29 02:44:10', '2026-06-29 05:21:14'),
(3, 'Warung Sasak Senaru', 'Senaru', 'Desa Senaru, Bayan, Lombok Utara', 'Warung lokal sederhana dengan menu nasi campur dan kopi khas Lombok Utara. Tempat istirahat favorit pendaki Rinjani.', 25000.00, 'restoran/6a420136f4216_1782710582.jpg', 'https://maps.app.goo.gl/resto3', 2, 1, '2026-06-29 02:44:10', '2026-06-29 05:23:03'),
(4, 'Scallywags Gili Trawangan', 'Gili Trawangan', 'Pantai Gili Trawangan, Lombok Utara', 'Restoran seafood tepi pantai dengan suasana sunset romantis. Spesialis ikan bakar dan hidangan laut segar.', 120000.00, 'restoran/6a42024027789_1782710848.jpg', 'https://maps.app.goo.gl/resto4', 3, 1, '2026-06-29 02:44:10', '2026-06-29 05:27:28'),
(5, 'Square Restaurant Senggigi', 'Senggigi', 'Jl. Raya Senggigi, Lombok Barat', 'Restoran internasional dengan live music di malam hari. Menu beragam dari Asia hingga Western.', 85000.00, 'restoran/6a420264eb4f5_1782710884.jpg', 'https://maps.app.goo.gl/resto5', 6, 1, '2026-06-29 02:44:10', '2026-06-29 05:28:04'),
(8, 'Mowie&#039;s Gili Air', 'Gili Air', 'Gili Air, Lombok Utara', 'Restoran tepi pantai dengan menu seafood segar dan suasana santai khas Gili. Live music akustik di malam tertentu menambah suasana liburan yang santai.', 95000.00, 'restoran/6a4202e0ad049_1782711008.jpg', 'https://maps.app.goo.gl/resto-mowies', 11, 1, '2026-06-29 02:44:10', '2026-06-29 05:30:08');

-- --------------------------------------------------------

--
-- Table structure for table `restoran_foto`
--

CREATE TABLE `restoran_foto` (
  `id` int(11) NOT NULL,
  `restoran_id` int(11) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `caption` varchar(200) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restoran_foto`
--

INSERT INTO `restoran_foto` (`id`, `restoran_id`, `foto`, `caption`, `is_primary`, `created_at`) VALUES
(1, 1, 'restoran/6a41fb0fd0b27_1782709007.webp', NULL, 1, '2026-06-29 04:56:47'),
(2, 1, 'restoran/6a41fb0fd2d6e_1782709007.webp', NULL, 0, '2026-06-29 04:56:47'),
(3, 2, 'restoran/6a4200ca06f7c_1782710474.webp', NULL, 1, '2026-06-29 05:21:14'),
(4, 3, 'restoran/6a420136f4216_1782710582.jpg', NULL, 1, '2026-06-29 05:23:03'),
(5, 4, 'restoran/6a42024027789_1782710848.jpg', NULL, 1, '2026-06-29 05:27:28'),
(6, 5, 'restoran/6a420264eb4f5_1782710884.jpg', NULL, 1, '2026-06-29 05:28:04'),
(7, 8, 'restoran/6a4202e0ad049_1782711008.jpg', NULL, 1, '2026-06-29 05:30:08');

-- --------------------------------------------------------

--
-- Table structure for table `restoran_rating`
--

CREATE TABLE `restoran_rating` (
  `id` int(11) NOT NULL,
  `restoran_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `komentar` text DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
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
(1, 'site_name', 'Lombok Tourism', '2026-06-29 02:44:10'),
(2, 'site_tagline', 'Surga Tersembunyi di Nusa Tenggara', '2026-06-29 02:44:10'),
(3, 'hero_bg', 'profile/6a42047e43cae_1782711422.jpg', '2026-06-29 05:37:02'),
(4, 'hero_title', 'Jelajahi Keindahan Lombok', '2026-06-29 02:44:10'),
(5, 'hero_subtitle', 'Rasakan Keindahan Alam, Budaya, dan Keramahan Lombok', '2026-06-29 05:36:29');

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
  `kategori` varchar(50) DEFAULT 'Wisata Alam',
  `is_featured` tinyint(1) DEFAULT 0,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tempat_wisata`
--

INSERT INTO `tempat_wisata` (`id`, `nama`, `deskripsi`, `harga_tiket`, `jam_buka`, `jam_tutup`, `rating_awal`, `link_lokasi`, `kategori`, `is_featured`, `admin_id`, `created_at`, `updated_at`) VALUES
(1, 'Pantai Kuta Lombok', 'Pantai Kuta Lombok menawarkan keindahan pasir putih yang luar biasa dengan air biru jernih. Berbeda dengan Kuta di Bali, pantai ini masih relatif sepi dan alami. Gelombang yang ideal untuk surfing dan pemandangan matahari terbenam yang memukau.', 10000.00, '06:00:00', '18:00:00', 0.0, 'https://www.google.com/maps/search/Pantai+Kuta+Lombok', 'Pantai', 1, 1, '2026-06-29 02:44:10', '2026-06-29 03:14:47'),
(2, 'Gunung Rinjani', 'Gunung Rinjani adalah gunung berapi aktif kedua tertinggi di Indonesia dengan ketinggian 3.726 mdpl. Pendakian ke puncak Rinjani menawarkan pemandangan spektakuler Danau Segara Anak dan panorama pulau-pulau sekitarnya.', 150000.00, '00:00:00', '23:59:00', 0.0, 'https://www.google.com/maps/search/Gunung+Rinjani,+Lombok', 'Gunung', 1, 1, '2026-06-29 02:44:10', '2026-06-29 03:15:51'),
(3, 'Gili Trawangan', 'Gili Trawangan adalah pulau terbesar dari tiga Gili yang terkenal. Dikenal dengan keindahan bawah lautnya, snorkeling, diving, dan suasana pantai yang meriah. Tidak ada kendaraan bermotor di pulau ini, hanya sepeda dan cidomo.', 0.00, '00:00:00', '23:59:00', 0.0, 'https://www.google.com/maps/search/Gili+Trawangan,+Lombok', 'Pulau', 1, 1, '2026-06-29 02:44:10', '2026-06-29 03:17:26'),
(5, 'Desa Sade', 'Desa tradisional suku Sasak yang masih mempertahankan adat dan budaya asli Lombok. Pengunjung dapat melihat rumah adat, tenun tradisional Sasak, dan berbagai ritual budaya yang unik.', 10000.00, '08:00:00', '17:00:00', 0.0, 'https://www.google.com/maps/search/Desa+Sade,+Lombok', 'Budaya', 0, 1, '2026-06-29 02:44:10', '2026-06-29 03:21:23'),
(6, 'Pantai Senggigi', 'Pantai Senggigi adalah salah satu destinasi wisata paling populer di Lombok. Dengan pemandangan Gunung Agung Bali di kejauhan dan sunset yang indah, pantai ini menjadi favorit wisatawan lokal maupun mancanegara.', 5000.00, '06:00:00', '18:00:00', 0.0, 'https://www.google.com/maps/search/Pantai+Senggigi,+Lombok', 'Pantai', 1, 1, '2026-06-29 02:44:10', '2026-06-29 03:22:23'),
(7, 'Pantai Tanjung Aan', 'Pantai Tanjung Aan terkenal dengan pasir merica (butiran kasar seperti merica) dan teluk berbentuk M yang ikonik. Air laut yang sangat jernih dengan gradasi warna biru kehijauan menjadikan pantai ini favorit untuk berenang dan berjemur.', 10000.00, '06:00:00', '18:00:00', 0.0, 'https://www.google.com/maps/search/Tanjung+Aan+Beach,+Lombok', 'Pantai', 1, 1, '2026-06-29 02:44:10', '2026-06-29 03:32:07'),
(8, 'Bukit Merese', 'Bukit Merese menawarkan pemandangan 360 derajat ke arah Pantai Tanjung Aan, Pantai Kuta, dan perbukitan sabana hijau. Spot favorit untuk menyaksikan matahari terbenam dengan padang rumput yang menghampar luas.', 5000.00, '06:00:00', '18:00:00', 0.0, 'https://www.google.com/maps/search/Bukit+Merese,+Lombok', 'Alam', 1, 1, '2026-06-29 02:44:10', '2026-06-29 03:27:54'),
(9, 'Pantai Selong Belanak', 'Pantai Selong Belanak memiliki ombak landai yang ideal untuk belajar surfing, dikelilingi perbukitan hijau dan pasir putih lembut. Banyak sekolah surfing lokal yang menawarkan kursus untuk pemula.', 5000.00, '06:00:00', '18:00:00', 0.0, 'https://www.google.com/maps/search/Selong+Belanak+Beach,+Lombok', 'Pantai', 0, 1, '2026-06-29 02:44:10', '2026-06-29 03:24:57'),
(10, 'Pantai Mawun', 'Pantai Mawun berbentuk teluk setengah lingkaran dengan air laut bergradasi tosca yang tenang, diapit dua tebing hijau di kedua sisinya. Pantai yang relatif sepi ini cocok untuk berenang dengan suasana tenang.', 10000.00, '06:00:00', '18:00:00', 0.0, 'https://www.google.com/maps/search/Pantai+Mawun,+Lombok', 'Pantai', 0, 1, '2026-06-29 02:44:10', '2026-06-29 03:23:29'),
(11, 'Gili Air', 'Gili Air adalah pulau kecil yang lebih tenang dibanding Gili Trawangan, dengan suasana santai dan penduduk lokal yang masih menetap. Snorkeling di sisi timur pulau menawarkan terumbu karang dan penyu laut.', 0.00, '00:00:00', '23:59:00', 0.0, 'https://www.google.com/maps/search/Gili+Air,+Lombok', 'Pulau', 1, 1, '2026-06-29 02:44:10', '2026-06-29 03:26:38'),
(12, 'Gili Meno', 'Gili Meno adalah yang paling kecil dan sepi dari tiga Gili, dikenal dengan Meno Wall untuk diving dan danau air asin di tengah pulau. Pilihan tepat bagi yang mencari ketenangan jauh dari keramaian.', 0.00, '00:00:00', '23:59:00', 0.0, 'https://www.google.com/maps/search/Gili+Meno,+Lombok', 'Pulau', 0, 1, '2026-06-29 02:44:10', '2026-06-29 03:44:07'),
(13, 'Air Terjun Tiu Kelep', 'Air Terjun Tiu Kelep terletak tidak jauh dari Sendang Gile dengan trekking melewati sungai dan bisa berdiri tepat di balik tirai air terjunnya. Debit air yang besar menciptakan kolam alami yang menyegarkan untuk berenang.', 15000.00, '07:00:00', '17:00:00', 0.0, 'https://www.google.com/maps/search/Tiu+Kelep+Waterfall,+Lombok', 'Alam', 1, 1, '2026-06-29 02:44:10', '2026-06-29 03:44:20'),
(14, 'Air Terjun Benang Stokel', 'Air Terjun Benang Stokel memiliki aliran air terjun tunggal yang besar dikelilingi hutan tropis asri di lereng Gunung Rinjani. Sering dikombinasikan dengan kunjungan ke Air Terjun Benang Kelambu yang berjarak tidak jauh.', 10000.00, '07:00:00', '17:00:00', 0.0, 'https://www.google.com/maps/search/Benang+Stokel+Waterfall,+Lombok', 'Alam', 0, 1, '2026-06-29 02:44:10', '2026-06-29 03:34:03'),
(15, 'Taman Narmada', 'Taman Narmada adalah taman air bersejarah peninggalan Kerajaan Karangasem yang dibangun menyerupai miniatur Gunung Rinjani dan Danau Segara Anak. Terdapat kolam pemandian dan Pura Kalasa yang masih aktif digunakan untuk upacara keagamaan.', 5000.00, '08:00:00', '17:00:00', 0.0, 'https://www.google.com/maps/search/Taman+Narmada,+Lombok', 'Budaya', 0, 1, '2026-06-29 02:44:10', '2026-06-29 03:35:40'),
(16, 'Bukit Pergasingan', 'Bukit Pergasingan adalah salah satu spot sunrise terbaik di Lombok Timur dengan pemandangan langsung ke Gunung Rinjani. Pendakian santai sekitar 2-3 jam dari Desa Sapit menjadikannya alternatif trekking yang lebih ringan.', 0.00, '00:00:00', '23:59:00', 0.0, 'https://www.google.com/maps/search/Bukit+Pergasingan,+Sembalun,+Lombok', 'Gunung', 0, 1, '2026-06-29 02:44:10', '2026-06-29 03:41:04'),
(17, 'Desa Tetebatu', 'Desa Tetebatu berada di kaki Gunung Rinjani dengan udara sejuk, hamparan sawah, dan kebun kopi. Dari sini wisatawan dapat trekking ke air terjun, hutan monyet, serta menikmati pemandangan Gunung Rinjani yang menjulang.', 0.00, '00:00:00', '23:59:00', 0.0, 'https://www.google.com/maps/search/Tetebatu+Village,+Lombok', 'Budaya', 0, 1, '2026-06-29 02:44:10', '2026-06-29 03:39:02'),
(18, 'Masjid Kuno Bayan Beleq', 'Masjid Kuno Bayan Beleq dipercaya sebagai salah satu masjid tertua di Lombok, dibangun dengan material tradisional seperti bambu dan atap alang-alang. Tempat ini menjadi saksi sejarah masuknya Islam ke wilayah Lombok Utara.', 0.00, '08:00:00', '17:00:00', 0.0, 'https://www.google.com/maps/search/Masjid+Kuno+Bayan+Beleq,+Lombok', 'Budaya', 0, 1, '2026-06-29 02:44:10', '2026-06-29 03:43:29');

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
(1, 'admin', 'admin@lomboktravel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-06-29 02:44:10', '2026-06-29 02:44:10'),
(2, 'jose', 'jose23@gmail.com', '$2y$10$06EoRt7gKEJa/t9b0uUgR.ujGjgR.FVAKLPIIad8A7B84Et9qI8Ia', 'user', '2026-06-29 05:38:36', '2026-06-29 05:38:36'),
(3, 'AfrarizaBon', 'bon12@gmail.com', '$2y$10$3SbDqBidor3PG0t1oZmgWuQc1bQ7yijRWMv69m1Lq/NeqVt3XBkD6', 'user', '2026-06-29 05:44:44', '2026-06-29 05:44:44'),
(4, 'insania', 'insania@gmail.com', '$2y$10$/fbl/YxUEybPMoMFmFSGO.HZ1/rLDU5/hugPzteQ2lZ1YOQ51hUEi', 'user', '2026-06-29 05:49:35', '2026-06-29 05:49:35');

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
(4, 1, 'wisata/6a41e3272c75a_1782702887.jpeg', NULL, 1, '2026-06-29 03:14:47'),
(5, 1, 'wisata/6a41e3272dd3d_1782702887.jpg', NULL, 0, '2026-06-29 03:14:47'),
(6, 1, 'wisata/6a41e32730727_1782702887.jpg', NULL, 0, '2026-06-29 03:14:47'),
(7, 2, 'wisata/6a41e3678ddd7_1782702951.jpg', NULL, 1, '2026-06-29 03:15:51'),
(8, 2, 'wisata/6a41e3678ff63_1782702951.jpg', NULL, 0, '2026-06-29 03:15:51'),
(9, 2, 'wisata/6a41e3778e0d5_1782702967.jpg', NULL, 0, '2026-06-29 03:16:07'),
(10, 3, 'wisata/6a41e3c6ec239_1782703046.jpg', NULL, 1, '2026-06-29 03:17:26'),
(11, 3, 'wisata/6a41e3c6ed393_1782703046.jpg', NULL, 0, '2026-06-29 03:17:26'),
(12, 3, 'wisata/6a41e3d2c58a0_1782703058.webp', NULL, 0, '2026-06-29 03:17:38'),
(13, 5, 'wisata/6a41e4b3173cb_1782703283.jpg', NULL, 1, '2026-06-29 03:21:23'),
(14, 5, 'wisata/6a41e4bd2e548_1782703293.jpg', NULL, 0, '2026-06-29 03:21:33'),
(15, 5, 'wisata/6a41e4bd2f84d_1782703293.jpg', NULL, 0, '2026-06-29 03:21:33'),
(16, 6, 'wisata/6a41e4ef12b57_1782703343.jpg', NULL, 1, '2026-06-29 03:22:23'),
(17, 6, 'wisata/6a41e4ef13d79_1782703343.jpg', NULL, 0, '2026-06-29 03:22:23'),
(18, 6, 'wisata/6a41e500013c5_1782703360.jpg', NULL, 0, '2026-06-29 03:22:40'),
(19, 10, 'wisata/6a41e53173db3_1782703409.jpg', NULL, 1, '2026-06-29 03:23:29'),
(20, 10, 'wisata/6a41e54a60c72_1782703434.jpg', NULL, 0, '2026-06-29 03:23:54'),
(21, 10, 'wisata/6a41e55b6fd40_1782703451.jpg', NULL, 0, '2026-06-29 03:24:11'),
(22, 9, 'wisata/6a41e589d1a5c_1782703497.jpg', NULL, 1, '2026-06-29 03:24:57'),
(23, 9, 'wisata/6a41e596046cc_1782703510.jpg', NULL, 0, '2026-06-29 03:25:10'),
(24, 11, 'wisata/6a41e5ee2d342_1782703598.jpg', NULL, 1, '2026-06-29 03:26:38'),
(25, 11, 'wisata/6a41e5ee2e997_1782703598.webp', NULL, 0, '2026-06-29 03:26:38'),
(26, 11, 'wisata/6a41e5ee30096_1782703598.jpg', NULL, 0, '2026-06-29 03:26:38'),
(27, 12, 'wisata/6a41e6070487d_1782703623.jpg', NULL, 1, '2026-06-29 03:27:03'),
(28, 12, 'wisata/6a41e6070618f_1782703623.webp', NULL, 0, '2026-06-29 03:27:03'),
(29, 8, 'wisata/6a41e63a15ec1_1782703674.jpg', NULL, 1, '2026-06-29 03:27:54'),
(30, 8, 'wisata/6a41e64b49aa0_1782703691.jpg', NULL, 0, '2026-06-29 03:28:11'),
(31, 13, 'wisata/6a41e66189c2b_1782703713.png', NULL, 1, '2026-06-29 03:28:33'),
(32, 13, 'wisata/6a41e66eae20c_1782703726.jpg', NULL, 0, '2026-06-29 03:28:46'),
(33, 13, 'wisata/6a41e67ed424f_1782703742.jpg', NULL, 0, '2026-06-29 03:29:02'),
(34, 7, 'wisata/6a41e737dfad6_1782703927.jpg', NULL, 1, '2026-06-29 03:32:07'),
(35, 7, 'wisata/6a41e737e136d_1782703927.jpg', NULL, 0, '2026-06-29 03:32:07'),
(36, 7, 'wisata/6a41e737e31b7_1782703927.webp', NULL, 0, '2026-06-29 03:32:07'),
(37, 14, 'wisata/6a41e7abbebb7_1782704043.jpg', NULL, 1, '2026-06-29 03:34:03'),
(38, 14, 'wisata/6a41e7abbfd4f_1782704043.jpg', NULL, 0, '2026-06-29 03:34:03'),
(39, 14, 'wisata/6a41e7abc12cf_1782704043.jpg', NULL, 0, '2026-06-29 03:34:03'),
(40, 15, 'wisata/6a41e80c5cf93_1782704140.jpg', NULL, 1, '2026-06-29 03:35:40'),
(41, 15, 'wisata/6a41e80c5e3b9_1782704140.jpg', NULL, 0, '2026-06-29 03:35:40'),
(42, 15, 'wisata/6a41e80c5fa41_1782704140.webp', NULL, 0, '2026-06-29 03:35:40'),
(43, 17, 'wisata/6a41e8d6e9175_1782704342.jpeg', NULL, 1, '2026-06-29 03:39:02'),
(44, 17, 'wisata/6a41e8d6ea36f_1782704342.jpg', NULL, 0, '2026-06-29 03:39:02'),
(45, 16, 'wisata/6a41e950e9b12_1782704464.jpg', NULL, 1, '2026-06-29 03:41:04'),
(46, 16, 'wisata/6a41e950eb51c_1782704464.jpg', NULL, 0, '2026-06-29 03:41:04'),
(47, 16, 'wisata/6a41e950edc4e_1782704464.webp', NULL, 0, '2026-06-29 03:41:04'),
(48, 18, 'wisata/6a41e9e18ac71_1782704609.jpg', NULL, 1, '2026-06-29 03:43:29'),
(49, 18, 'wisata/6a41e9e18c13a_1782704609.jpg', NULL, 0, '2026-06-29 03:43:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hotel`
--
ALTER TABLE `hotel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wisata_terdekat_id` (`wisata_terdekat_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `hotel_foto`
--
ALTER TABLE `hotel_foto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indexes for table `hotel_rating`
--
ALTER TABLE `hotel_rating`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_hotel_rating` (`hotel_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `restoran`
--
ALTER TABLE `restoran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wisata_terdekat_id` (`wisata_terdekat_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `restoran_foto`
--
ALTER TABLE `restoran_foto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restoran_id` (`restoran_id`);

--
-- Indexes for table `restoran_rating`
--
ALTER TABLE `restoran_rating`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_restoran_rating` (`restoran_id`,`user_id`),
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
-- AUTO_INCREMENT for table `hotel`
--
ALTER TABLE `hotel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `hotel_foto`
--
ALTER TABLE `hotel_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `hotel_rating`
--
ALTER TABLE `hotel_rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `komentar`
--
ALTER TABLE `komentar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `komentar_foto`
--
ALTER TABLE `komentar_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `restoran`
--
ALTER TABLE `restoran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `restoran_foto`
--
ALTER TABLE `restoran_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `restoran_rating`
--
ALTER TABLE `restoran_rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tempat_wisata`
--
ALTER TABLE `tempat_wisata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wisata_foto`
--
ALTER TABLE `wisata_foto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hotel`
--
ALTER TABLE `hotel`
  ADD CONSTRAINT `hotel_ibfk_1` FOREIGN KEY (`wisata_terdekat_id`) REFERENCES `tempat_wisata` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `hotel_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hotel_foto`
--
ALTER TABLE `hotel_foto`
  ADD CONSTRAINT `hotel_foto_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotel` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hotel_rating`
--
ALTER TABLE `hotel_rating`
  ADD CONSTRAINT `hotel_rating_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `hotel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hotel_rating_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `restoran`
--
ALTER TABLE `restoran`
  ADD CONSTRAINT `restoran_ibfk_1` FOREIGN KEY (`wisata_terdekat_id`) REFERENCES `tempat_wisata` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `restoran_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `restoran_foto`
--
ALTER TABLE `restoran_foto`
  ADD CONSTRAINT `restoran_foto_ibfk_1` FOREIGN KEY (`restoran_id`) REFERENCES `restoran` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restoran_rating`
--
ALTER TABLE `restoran_rating`
  ADD CONSTRAINT `restoran_rating_ibfk_1` FOREIGN KEY (`restoran_id`) REFERENCES `restoran` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `restoran_rating_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
