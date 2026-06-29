-- ============================================
-- DATABASE: lombok_tourism
-- Website Pariwisata Lombok
-- ============================================

CREATE DATABASE IF NOT EXISTS lombok_tourism CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lombok_tourism;

-- ============================================
-- TABLE: users
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- TABLE: profile
-- ============================================
CREATE TABLE profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    full_name VARCHAR(100),
    bio TEXT,
    phone VARCHAR(20),
    location VARCHAR(100),
    foto_profile VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABLE: tempat_wisata
-- ============================================
CREATE TABLE tempat_wisata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    harga_tiket DECIMAL(10,2) DEFAULT 0,
    jam_buka TIME,
    jam_tutup TIME,
    rating_awal DECIMAL(3,1) DEFAULT 0,
    link_lokasi VARCHAR(500),
    kategori VARCHAR(50) DEFAULT 'Wisata Alam',
    is_featured TINYINT(1) DEFAULT 0,
    admin_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABLE: wisata_foto (multiple photos per wisata)
-- ============================================
CREATE TABLE wisata_foto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wisata_id INT NOT NULL,
    foto VARCHAR(255) NOT NULL,
    caption VARCHAR(200),
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wisata_id) REFERENCES tempat_wisata(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABLE: komentar
-- ============================================
CREATE TABLE komentar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wisata_id INT NOT NULL,
    user_id INT NOT NULL,
    komentar TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (wisata_id) REFERENCES tempat_wisata(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABLE: komentar_foto
-- ============================================
CREATE TABLE komentar_foto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    komentar_id INT NOT NULL,
    foto VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (komentar_id) REFERENCES komentar(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABLE: rating
-- ============================================
CREATE TABLE rating (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wisata_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rating (wisata_id, user_id),
    FOREIGN KEY (wisata_id) REFERENCES tempat_wisata(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABLE: hotel
-- ============================================
CREATE TABLE hotel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_hotel VARCHAR(150) NOT NULL,
    lokasi VARCHAR(150) NOT NULL,
    alamat VARCHAR(255),
    deskripsi TEXT,
    harga_per_malam DECIMAL(12,2) DEFAULT 0,
    foto VARCHAR(255) DEFAULT NULL,
    link_lokasi VARCHAR(500) DEFAULT NULL,
    wisata_terdekat_id INT DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (wisata_terdekat_id) REFERENCES tempat_wisata(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABLE: restoran
-- ============================================
CREATE TABLE restoran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_restoran VARCHAR(150) NOT NULL,
    lokasi VARCHAR(150) NOT NULL,
    alamat VARCHAR(255),
    deskripsi TEXT,
    harga_rata_rata DECIMAL(12,2) DEFAULT 0,
    foto VARCHAR(255) DEFAULT NULL,
    link_lokasi VARCHAR(500) DEFAULT NULL,
    wisata_terdekat_id INT DEFAULT NULL,
    admin_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (wisata_terdekat_id) REFERENCES tempat_wisata(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABLE: hotel_rating
-- Catatan: user_id (FK ke users) menggantikan kolom 'username' agar
-- konsisten dengan tabel rating/komentar yang sudah ada — username
-- otomatis di-JOIN dari sesi login. Satu user = satu ulasan per hotel
-- (bisa diperbarui).
-- ============================================
CREATE TABLE hotel_rating (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_hotel_rating (hotel_id, user_id),
    FOREIGN KEY (hotel_id) REFERENCES hotel(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABLE: restoran_rating
-- ============================================
CREATE TABLE restoran_rating (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restoran_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    komentar TEXT,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_restoran_rating (restoran_id, user_id),
    FOREIGN KEY (restoran_id) REFERENCES restoran(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABLE: site_settings (background & config)
-- ============================================
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- DEFAULT DATA
-- ============================================

-- Admin default: username=admin, password=admin123
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@lomboktravel.com', '$2y$10$wHn7d2qQe1P7j4Q2e6Q4P.ZzV4oGqkL8zM4s2J5Y8bL3QeR5aP7zW', 'admin');

INSERT INTO profile (user_id, full_name, bio) VALUES
(1, 'Administrator', 'Admin website Lombok Tourism');

-- Default site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'Lombok Tourism'),
('site_tagline', 'Surga Tersembunyi di Nusa Tenggara'),
('hero_bg', ''),
('hero_title', 'Jelajahi Keindahan Lombok'),
('hero_subtitle', 'Temukan destinasi wisata terbaik di Pulau Seribu Masjid');

-- Sample wisata data
INSERT INTO tempat_wisata (nama, deskripsi, harga_tiket, jam_buka, jam_tutup, rating_awal, link_lokasi, kategori, is_featured, admin_id) VALUES
('Pantai Kuta Lombok', 'Pantai Kuta Lombok menawarkan keindahan pasir putih yang luar biasa dengan air biru jernih. Berbeda dengan Kuta di Bali, pantai ini masih relatif sepi dan alami. Gelombang yang ideal untuk surfing dan pemandangan matahari terbenam yang memukau.', 10000, '06:00:00', '18:00:00', 4.5, 'https://maps.app.goo.gl/example1', 'Pantai', 1, 1),
('Gunung Rinjani', 'Gunung Rinjani adalah gunung berapi aktif kedua tertinggi di Indonesia dengan ketinggian 3.726 mdpl. Pendakian ke puncak Rinjani menawarkan pemandangan spektakuler Danau Segara Anak dan panorama pulau-pulau sekitarnya.', 150000, '00:00:00', '23:59:00', 4.8, 'https://maps.app.goo.gl/example2', 'Gunung', 1, 1),
('Gili Trawangan', 'Gili Trawangan adalah pulau terbesar dari tiga Gili yang terkenal. Dikenal dengan keindahan bawah lautnya, snorkeling, diving, dan suasana pantai yang meriah. Tidak ada kendaraan bermotor di pulau ini, hanya sepeda dan cidomo.', 0, '00:00:00', '23:59:00', 4.7, 'https://maps.app.goo.gl/example3', 'Pulau', 1, 1),
('Air Terjun Sendang Gile', 'Air terjun yang memukau di kaki Gunung Rinjani dengan ketinggian sekitar 30 meter. Dikelilingi hutan tropis yang rimbun, air terjun ini menawarkan suasana sejuk dan pemandangan alam yang menakjubkan.', 20000, '07:00:00', '17:00:00', 4.3, 'https://maps.app.goo.gl/example4', 'Alam', 0, 1),
('Desa Sade', 'Desa tradisional suku Sasak yang masih mempertahankan adat dan budaya asli Lombok. Pengunjung dapat melihat rumah adat, tenun tradisional Sasak, dan berbagai ritual budaya yang unik.', 10000, '08:00:00', '17:00:00', 4.2, 'https://maps.app.goo.gl/example5', 'Budaya', 0, 1),
('Pantai Senggigi', 'Pantai Senggigi adalah salah satu destinasi wisata paling populer di Lombok. Dengan pemandangan Gunung Agung Bali di kejauhan dan sunset yang indah, pantai ini menjadi favorit wisatawan lokal maupun mancanegara.', 5000, '06:00:00', '18:00:00', 4.4, 'https://maps.app.goo.gl/example6', 'Pantai', 1, 1);

-- Wisata tambahan (12 destinasi lagi)
INSERT INTO tempat_wisata (nama, deskripsi, harga_tiket, jam_buka, jam_tutup, rating_awal, link_lokasi, kategori, is_featured, admin_id) VALUES
('Pantai Tanjung Aan', 'Pantai Tanjung Aan terkenal dengan pasir merica (butiran kasar seperti merica) dan teluk berbentuk M yang ikonik. Air laut yang sangat jernih dengan gradasi warna biru kehijauan menjadikan pantai ini favorit untuk berenang dan berjemur.', 10000, '06:00:00', '18:00:00', 4.6, 'https://maps.app.goo.gl/tanjungaan', 'Pantai', 1, 1),
('Bukit Merese', 'Bukit Merese menawarkan pemandangan 360 derajat ke arah Pantai Tanjung Aan, Pantai Kuta, dan perbukitan sabana hijau. Spot favorit untuk menyaksikan matahari terbenam dengan padang rumput yang menghampar luas.', 5000, '06:00:00', '18:00:00', 4.7, 'https://maps.app.goo.gl/bukitmerese', 'Alam', 1, 1),
('Pantai Selong Belanak', 'Pantai Selong Belanak memiliki ombak landai yang ideal untuk belajar surfing, dikelilingi perbukitan hijau dan pasir putih lembut. Banyak sekolah surfing lokal yang menawarkan kursus untuk pemula.', 5000, '06:00:00', '18:00:00', 4.5, 'https://maps.app.goo.gl/selongbelanak', 'Pantai', 0, 1),
('Pantai Mawun', 'Pantai Mawun berbentuk teluk setengah lingkaran dengan air laut bergradasi tosca yang tenang, diapit dua tebing hijau di kedua sisinya. Pantai yang relatif sepi ini cocok untuk berenang dengan suasana tenang.', 10000, '06:00:00', '18:00:00', 4.6, 'https://maps.app.goo.gl/mawun', 'Pantai', 0, 1),
('Gili Air', 'Gili Air adalah pulau kecil yang lebih tenang dibanding Gili Trawangan, dengan suasana santai dan penduduk lokal yang masih menetap. Snorkeling di sisi timur pulau menawarkan terumbu karang dan penyu laut.', 0, '00:00:00', '23:59:00', 4.6, 'https://maps.app.goo.gl/giliair', 'Pulau', 1, 1),
('Gili Meno', 'Gili Meno adalah yang paling kecil dan sepi dari tiga Gili, dikenal dengan Meno Wall untuk diving dan danau air asin di tengah pulau. Pilihan tepat bagi yang mencari ketenangan jauh dari keramaian.', 0, '00:00:00', '23:59:00', 4.5, 'https://maps.app.goo.gl/gilimeno', 'Pulau', 0, 1),
('Air Terjun Tiu Kelep', 'Air Terjun Tiu Kelep terletak tidak jauh dari Sendang Gile dengan trekking melewati sungai dan bisa berdiri tepat di balik tirai air terjunnya. Debit air yang besar menciptakan kolam alami yang menyegarkan untuk berenang.', 15000, '07:00:00', '17:00:00', 4.6, 'https://maps.app.goo.gl/tiukelep', 'Alam', 1, 1),
('Air Terjun Benang Stokel', 'Air Terjun Benang Stokel memiliki aliran air terjun tunggal yang besar dikelilingi hutan tropis asri di lereng Gunung Rinjani. Sering dikombinasikan dengan kunjungan ke Air Terjun Benang Kelambu yang berjarak tidak jauh.', 10000, '07:00:00', '17:00:00', 4.4, 'https://maps.app.goo.gl/benangstokel', 'Alam', 0, 1),
('Taman Narmada', 'Taman Narmada adalah taman air bersejarah peninggalan Kerajaan Karangasem yang dibangun menyerupai miniatur Gunung Rinjani dan Danau Segara Anak. Terdapat kolam pemandian dan Pura Kalasa yang masih aktif digunakan untuk upacara keagamaan.', 5000, '08:00:00', '17:00:00', 4.2, 'https://maps.app.goo.gl/narmada', 'Budaya', 0, 1),
('Bukit Pergasingan', 'Bukit Pergasingan adalah salah satu spot sunrise terbaik di Lombok Timur dengan pemandangan langsung ke Gunung Rinjani. Pendakian santai sekitar 2-3 jam dari Desa Sapit menjadikannya alternatif trekking yang lebih ringan.', 0, '00:00:00', '23:59:00', 4.5, 'https://maps.app.goo.gl/pergasingan', 'Gunung', 0, 1),
('Desa Tetebatu', 'Desa Tetebatu berada di kaki Gunung Rinjani dengan udara sejuk, hamparan sawah, dan kebun kopi. Dari sini wisatawan dapat trekking ke air terjun, hutan monyet, serta menikmati pemandangan Gunung Rinjani yang menjulang.', 0, '00:00:00', '23:59:00', 4.3, 'https://maps.app.goo.gl/tetebatu', 'Budaya', 0, 1),
('Masjid Kuno Bayan Beleq', 'Masjid Kuno Bayan Beleq dipercaya sebagai salah satu masjid tertua di Lombok, dibangun dengan material tradisional seperti bambu dan atap alang-alang. Tempat ini menjadi saksi sejarah masuknya Islam ke wilayah Lombok Utara.', 0, '08:00:00', '17:00:00', 4.1, 'https://maps.app.goo.gl/bayanbeleq', 'Budaya', 0, 1);

-- ============================================
-- SAMPLE DATA: hotel
-- ============================================
INSERT INTO hotel (nama_hotel, lokasi, alamat, deskripsi, harga_per_malam, link_lokasi, wisata_terdekat_id, admin_id) VALUES
('Pullman Lombok Merujani Mandalika Beach Resort', 'Kuta Mandalika', 'Jl. Pariwisata, Kuta, Pujut, Lombok Tengah', 'Resort mewah tepi pantai dengan kolam renang infinity dan pemandangan langsung ke Pantai Kuta. Fasilitas lengkap termasuk spa, gym, dan beberapa restoran.', 1800000, 'https://maps.app.goo.gl/hotel1', 1, 1),
('Novotel Lombok Resort & Villas', 'Kuta Mandalika', 'Jl. Pantai Putri Nyale, Kuta, Lombok Tengah', 'Resort dengan vila pribadi dan akses langsung ke pantai. Cocok untuk keluarga dengan kolam anak dan area bermain.', 1200000, 'https://maps.app.goo.gl/hotel2', 1, 1),
('RinjaniView Eco Lodge', 'Senaru', 'Desa Senaru, Bayan, Lombok Utara', 'Penginapan sederhana dengan pemandangan langsung ke Gunung Rinjani. Titik awal favorit untuk pendakian dan trekking ke air terjun.', 350000, 'https://maps.app.goo.gl/hotel3', 2, 1),
('Gili Trawangan Cottages', 'Gili Trawangan', 'Jl. Pantai Gili Trawangan, Lombok Utara', 'Cottage tepi pantai dengan akses snorkeling langsung dari penginapan. Tanpa kendaraan bermotor, suasana tenang dan asri.', 450000, 'https://maps.app.goo.gl/hotel4', 3, 1),
('Senggigi Beach Hotel', 'Senggigi', 'Jl. Raya Senggigi, Lombok Barat', 'Hotel klasik tepi Pantai Senggigi dengan pemandangan sunset terbaik. Dekat dengan pusat oleh-oleh dan kafe.', 650000, 'https://maps.app.goo.gl/hotel5', 6, 1);

-- Hotel tambahan (6 penginapan lagi)
-- ID wisata mengikuti urutan insert di atas: 7=Tanjung Aan, 9=Selong Belanak, 11=Gili Air, 12=Gili Meno, 17=Tetebatu
INSERT INTO hotel (nama_hotel, lokasi, alamat, deskripsi, harga_per_malam, link_lokasi, wisata_terdekat_id, admin_id) VALUES
('Aan Beach Bungalows', 'Tanjung Aan', 'Jl. Pantai Tanjung Aan, Kuta, Lombok Tengah', 'Bungalow sederhana dengan akses jalan kaki langsung ke Pantai Tanjung Aan. Teras kamar menghadap ke arah bukit dan laut, cocok untuk yang ingin suasana santai dekat pantai.', 400000, 'https://maps.app.goo.gl/hotel-aan', 7, 1),
('Mimpi Manis Lombok', 'Selong Belanak', 'Jl. Raya Selong Belanak, Praya Barat Daya, Lombok Tengah', 'Penginapan bergaya tropis dengan kolam renang dan taman asri, berjarak 5 menit jalan kaki ke Pantai Selong Belanak. Populer di kalangan peselancar karena dekat dengan sekolah surfing.', 550000, 'https://maps.app.goo.gl/hotel-mimpimanis', 9, 1),
('Gili Air Santai Hostel', 'Gili Air', 'Gili Air, Lombok Utara', 'Hostel ramah backpacker dengan kamar dorm dan privat, berjarak beberapa menit dari pelabuhan kecil Gili Air. Atmosfer santai dengan rooftop untuk menikmati sunset.', 250000, 'https://maps.app.goo.gl/hotel-giliairsantai', 11, 1),
('Karma Reef Gili Meno', 'Gili Meno', 'Gili Meno, Lombok Utara', 'Resort tepi pantai dengan desain villa kayu yang menyatu dengan alam, dikenal karena ketenangannya jauh dari aktivitas Gili Trawangan yang ramai. Beach club di tepi laut menawarkan suasana romantis untuk pasangan.', 900000, 'https://maps.app.goo.gl/hotel-karmareef', 12, 1),
('Tetebatu Jungle Homestay', 'Tetebatu', 'Desa Tetebatu, Lombok Timur', 'Homestay keluarga dengan pemandangan sawah dan kebun kopi langsung dari teras kamar. Pemilik homestay dapat membantu mengatur trekking ke air terjun dan hutan monyet sekitar desa.', 300000, 'https://maps.app.goo.gl/hotel-tetebatu', 17, 1),
('Lombok Astoria Hotel', 'Mataram', 'Jl. Pejanggik, Mataram, Lombok', 'Hotel bisnis di pusat kota Mataram dengan akses mudah ke bandara dan pusat perbelanjaan. Pilihan tepat untuk wisatawan yang transit atau memiliki urusan di pusat kota.', 380000, 'https://maps.app.goo.gl/hotel-astoria', NULL, 1);

-- ============================================
-- SAMPLE DATA: restoran
-- ============================================
INSERT INTO restoran (nama_restoran, lokasi, alamat, deskripsi, harga_rata_rata, link_lokasi, wisata_terdekat_id, admin_id) VALUES
('Ayam Taliwang Bersaudara', 'Kuta Mandalika', 'Jl. Raya Kuta No. 12, Lombok Tengah', 'Rumah makan khas Sasak terkenal dengan ayam taliwang pedas dan plecing kangkung otentik. Favorit wisatawan lokal dan mancanegara.', 45000, 'https://maps.app.goo.gl/resto1', 1, 1),
('Kober Cafe Mandalika', 'Kuta Mandalika', 'Jl. Pariwisata, Kuta, Lombok Tengah', 'Kafe santai dengan menu fusion dan pemandangan area sirkuit Mandalika. Cocok untuk makan siang setelah dari pantai.', 65000, 'https://maps.app.goo.gl/resto2', 1, 1),
('Warung Sasak Senaru', 'Senaru', 'Desa Senaru, Bayan, Lombok Utara', 'Warung lokal sederhana dengan menu nasi campur dan kopi khas Lombok Utara. Tempat istirahat favorit pendaki Rinjani.', 25000, 'https://maps.app.goo.gl/resto3', 2, 1),
('Scallywags Gili Trawangan', 'Gili Trawangan', 'Pantai Gili Trawangan, Lombok Utara', 'Restoran seafood tepi pantai dengan suasana sunset romantis. Spesialis ikan bakar dan hidangan laut segar.', 120000, 'https://maps.app.goo.gl/resto4', 3, 1),
('Square Restaurant Senggigi', 'Senggigi', 'Jl. Raya Senggigi, Lombok Barat', 'Restoran internasional dengan live music di malam hari. Menu beragam dari Asia hingga Western.', 85000, 'https://maps.app.goo.gl/resto5', 6, 1);

-- Restoran tambahan (6 tempat makan lagi)
INSERT INTO restoran (nama_restoran, lokasi, alamat, deskripsi, harga_rata_rata, link_lokasi, wisata_terdekat_id, admin_id) VALUES
('Element Bar Tanjung Aan', 'Tanjung Aan', 'Jl. Pantai Tanjung Aan, Kuta, Lombok Tengah', 'Bar pantai dengan kursi santai menghadap laut, menyajikan cocktail segar dan menu western ringan. Tempat favorit untuk menikmati sunset sambil bersantai di pinggir pantai.', 75000, 'https://maps.app.goo.gl/resto-element', 7, 1),
('Sasak Cafe Selong Belanak', 'Selong Belanak', 'Jl. Raya Selong Belanak, Lombok Tengah', 'Kafe sederhana dengan menu lokal dan internasional, favorit di kalangan peselancar untuk sarapan sebelum sesi surfing. Smoothie bowl dan kopi lokal menjadi menu andalan.', 40000, 'https://maps.app.goo.gl/resto-sasakcafe', 9, 1),
('Mowie''s Gili Air', 'Gili Air', 'Gili Air, Lombok Utara', 'Restoran tepi pantai dengan menu seafood segar dan suasana santai khas Gili. Live music akustik di malam tertentu menambah suasana liburan yang santai.', 95000, 'https://maps.app.goo.gl/resto-mowies', 11, 1),
('Sasak House Tetebatu', 'Tetebatu', 'Desa Tetebatu, Lombok Timur', 'Rumah makan tradisional dengan menu khas Sasak seperti ayam taliwang dan sayur urap, disajikan dengan pemandangan sawah hijau. Tempat istirahat favorit setelah trekking di sekitar Tetebatu.', 35000, 'https://maps.app.goo.gl/resto-sasakhouse', 17, 1),
('Lesehan Narmada', 'Narmada', 'Jl. Taman Narmada, Lombok Barat', 'Warung lesehan di dekat Taman Narmada yang menyajikan menu khas Lombok dengan harga terjangkau. Pilihan favorit pengunjung taman untuk makan siang setelah berkeliling.', 30000, 'https://maps.app.goo.gl/resto-lesehannarmada', 15, 1),
('RM Tepi Sawah Mataram', 'Mataram', 'Jl. Selaparang, Mataram, Lombok', 'Restoran keluarga dengan konsep tepi sawah buatan di tengah kota Mataram, menyajikan menu Indonesia dan Sasak dalam porsi besar. Pilihan tepat untuk makan bersama rombongan atau keluarga.', 50000, 'https://maps.app.goo.gl/resto-tepisawah', NULL, 1);