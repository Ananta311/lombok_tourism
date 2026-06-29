-- ============================================================
-- MIGRATION: hotel_restoran_update.sql
-- Lombok Tourism — Penambahan Modul Hotel, Restoran & Rating
-- Jalankan file ini SETELAH database.sql (untuk database yang sudah ada)
-- Untuk instalasi baru, gunakan database.sql yang sudah include semua ini.
--
-- CATATAN: Versi ini TIDAK menggunakan latitude/longitude.
-- Lokasi cukup disimpan sebagai link Google Maps (link_lokasi).
-- ============================================================

USE lombok_tourism;

-- ────────────────────────────────────────────────────────────
-- 0. Jika sebelumnya sudah pernah menjalankan migrasi versi
--    lama yang menambahkan kolom latitude/longitude, hapus dulu
--    kolom tersebut (aman dijalankan meski kolomnya belum ada —
--    abaikan error "check that column/key exists" jika muncul).
-- ────────────────────────────────────────────────────────────
ALTER TABLE tempat_wisata DROP COLUMN IF EXISTS latitude;
ALTER TABLE tempat_wisata DROP COLUMN IF EXISTS longitude;

-- ────────────────────────────────────────────────────────────
-- 1. TABEL: hotel
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS hotel (
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

-- Jika tabel hotel sudah ada dari migrasi versi lat/long sebelumnya:
ALTER TABLE hotel DROP COLUMN IF EXISTS latitude;
ALTER TABLE hotel DROP COLUMN IF EXISTS longitude;
ALTER TABLE hotel ADD COLUMN IF NOT EXISTS link_lokasi VARCHAR(500) DEFAULT NULL AFTER foto;

-- ────────────────────────────────────────────────────────────
-- 2. TABEL: restoran
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS restoran (
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

ALTER TABLE restoran DROP COLUMN IF EXISTS latitude;
ALTER TABLE restoran DROP COLUMN IF EXISTS longitude;
ALTER TABLE restoran ADD COLUMN IF NOT EXISTS link_lokasi VARCHAR(500) DEFAULT NULL AFTER foto;

-- ────────────────────────────────────────────────────────────
-- 3. TABEL: hotel_rating
--    user_id (FK ke users) menggantikan kolom 'username' agar
--    konsisten dengan tabel rating/komentar yang sudah ada.
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS hotel_rating (
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

-- ────────────────────────────────────────────────────────────
-- 4. TABEL: restoran_rating
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS restoran_rating (
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

-- ────────────────────────────────────────────────────────────
-- 5. Sample data: Hotel
-- ────────────────────────────────────────────────────────────
INSERT INTO hotel (nama_hotel, lokasi, alamat, deskripsi, harga_per_malam, link_lokasi, wisata_terdekat_id, admin_id) VALUES
('Pullman Lombok Merujani Mandalika Beach Resort', 'Kuta Mandalika', 'Jl. Pariwisata, Kuta, Pujut, Lombok Tengah', 'Resort mewah tepi pantai dengan kolam renang infinity dan pemandangan langsung ke Pantai Kuta. Fasilitas lengkap termasuk spa, gym, dan beberapa restoran.', 1800000, 'https://maps.app.goo.gl/hotel1', 1, 1),
('Novotel Lombok Resort & Villas', 'Kuta Mandalika', 'Jl. Pantai Putri Nyale, Kuta, Lombok Tengah', 'Resort dengan vila pribadi dan akses langsung ke pantai. Cocok untuk keluarga dengan kolam anak dan area bermain.', 1200000, 'https://maps.app.goo.gl/hotel2', 1, 1),
('RinjaniView Eco Lodge', 'Senaru', 'Desa Senaru, Bayan, Lombok Utara', 'Penginapan sederhana dengan pemandangan langsung ke Gunung Rinjani. Titik awal favorit untuk pendakian dan trekking ke air terjun.', 350000, 'https://maps.app.goo.gl/hotel3', 2, 1),
('Gili Trawangan Cottages', 'Gili Trawangan', 'Jl. Pantai Gili Trawangan, Lombok Utara', 'Cottage tepi pantai dengan akses snorkeling langsung dari penginapan. Tanpa kendaraan bermotor, suasana tenang dan asri.', 450000, 'https://maps.app.goo.gl/hotel4', 3, 1),
('Senggigi Beach Hotel', 'Senggigi', 'Jl. Raya Senggigi, Lombok Barat', 'Hotel klasik tepi Pantai Senggigi dengan pemandangan sunset terbaik. Dekat dengan pusat oleh-oleh dan kafe.', 650000, 'https://maps.app.goo.gl/hotel5', 6, 1);

-- ────────────────────────────────────────────────────────────
-- 6. Sample data: Restoran
-- ────────────────────────────────────────────────────────────
INSERT INTO restoran (nama_restoran, lokasi, alamat, deskripsi, harga_rata_rata, link_lokasi, wisata_terdekat_id, admin_id) VALUES
('Ayam Taliwang Bersaudara', 'Kuta Mandalika', 'Jl. Raya Kuta No. 12, Lombok Tengah', 'Rumah makan khas Sasak terkenal dengan ayam taliwang pedas dan plecing kangkung otentik. Favorit wisatawan lokal dan mancanegara.', 45000, 'https://maps.app.goo.gl/resto1', 1, 1),
('Kober Cafe Mandalika', 'Kuta Mandalika', 'Jl. Pariwisata, Kuta, Lombok Tengah', 'Kafe santai dengan menu fusion dan pemandangan area sirkuit Mandalika. Cocok untuk makan siang setelah dari pantai.', 65000, 'https://maps.app.goo.gl/resto2', 1, 1),
('Warung Sasak Senaru', 'Senaru', 'Desa Senaru, Bayan, Lombok Utara', 'Warung lokal sederhana dengan menu nasi campur dan kopi khas Lombok Utara. Tempat istirahat favorit pendaki Rinjani.', 25000, 'https://maps.app.goo.gl/resto3', 2, 1),
('Scallywags Gili Trawangan', 'Gili Trawangan', 'Pantai Gili Trawangan, Lombok Utara', 'Restoran seafood tepi pantai dengan suasana sunset romantis. Spesialis ikan bakar dan hidangan laut segar.', 120000, 'https://maps.app.goo.gl/resto4', 3, 1),
('Square Restaurant Senggigi', 'Senggigi', 'Jl. Raya Senggigi, Lombok Barat', 'Restoran internasional dengan live music di malam hari. Menu beragam dari Asia hingga Western.', 85000, 'https://maps.app.goo.gl/resto5', 6, 1);
