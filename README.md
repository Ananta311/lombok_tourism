# 🌴 Lombok Tourism Website
## Panduan Instalasi & Menjalankan di Localhost (XAMPP)

> **v2.0** — Sekarang dengan modul Hotel & Penginapan, Restoran & Tempat Makan,
> Google Maps terintegrasi, dan Pencarian Global.

---

## 📁 Struktur Folder

```
lombok_tourism/
├── admin/
│   ├── admin_header.php / admin_footer.php   ← Layout admin
│   ├── dashboard.php                         ← Dashboard admin
│   ├── wisata.php / add_wisata.php / edit_wisata.php / delete_wisata.php
│   ├── hotel.php / add_hotel.php / edit_hotel.php / delete_hotel.php
│   ├── restoran.php / add_restoran.php / edit_restoran.php / delete_restoran.php
│   ├── komentar.php          ← Kelola komentar wisata
│   ├── komentar_hotel.php    ← Kelola ulasan hotel
│   ├── komentar_restoran.php ← Kelola ulasan restoran
│   ├── rating.php            ← Kelola rating wisata
│   ├── users.php             ← Kelola data user
│   └── settings.php          ← Pengaturan website & background
├── assets/
│   ├── css/style.css         ← CSS utama (Langit #0EA5E9, Laut #06B6D4, Alam #22C55E)
│   ├── css/admin.css
│   └── js/main.js
├── config/database.php       ← Koneksi DB + semua helper function
├── includes/
│   ├── auth.php
│   ├── header.php            ← Navbar + Pencarian Global
│   └── footer.php
├── uploads/
│   ├── profile/ wisata/ komentar/ hotel/ restoran/
│   └── .htaccess
├── user/
│   ├── login.php / register.php / logout.php / profile.php
├── index.php                 ← Beranda
├── wisata.php / detail.php   ← Listing & detail wisata
├── hotel.php / hotel_detail.php       ← Listing & detail hotel  [BARU]
├── restoran.php / restoran_detail.php ← Listing & detail restoran [BARU]
├── search.php                ← Halaman hasil pencarian global  [BARU]
├── search_api.php            ← Endpoint AJAX live-search        [BARU]
├── database.sql              ← SQL lengkap (instalasi baru)
└── database_hotel_restoran.sql ← Migrasi (untuk DB yang sudah ada) [BARU]
```

---

## 🆕 Fitur Baru di v2.0

### 1. Modul Hotel & Penginapan
- Admin: tambah/edit/hapus hotel dengan foto, harga, koordinat, dan keterkaitan ke wisata
- User: lihat daftar, cari berdasarkan nama/lokasi, lihat detail + peta + ulasan
- Rating & komentar terintegrasi (1 ulasan per user per hotel, bisa diperbarui)

### 2. Modul Restoran & Tempat Makan
- Struktur identik dengan modul Hotel (tambah/edit/hapus, pencarian, detail, rating+komentar)

### 3. Rekomendasi Otomatis di Halaman Detail Wisata
- Section **"Rekomendasi Penginapan"** dan **"Rekomendasi Tempat Makan"** muncul otomatis
  setelah deskripsi wisata, diambil berdasarkan `wisata_terdekat_id` yang diisi admin

### 4. Integrasi Google Maps
- Wisata, Hotel, dan Restoran masing-masing punya kolom `link_lokasi`
- Tinggal tempel link "Bagikan" dari Google Maps — tombol **"Buka di Google Maps"** otomatis muncul di halaman detail

### 5. Pencarian Global
- Search bar di navbar (desktop & mobile) dengan live-dropdown AJAX
- Mencari di 3 kategori sekaligus: Wisata, Hotel, Restoran
- Tekan Enter atau klik "Lihat semua hasil" → ke halaman `search.php` dengan hasil terkelompok per kategori

### 6. Dashboard Admin Diperluas
Menu sidebar baru: **Kelola Hotel**, **Tambah Hotel**, **Komentar Hotel**,
**Kelola Restoran**, **Tambah Restoran**, **Komentar Restoran** — plus statistik
total hotel & restoran di dashboard.

---

## 🚀 Cara Menjalankan di XAMPP

### Instalasi Baru (belum pernah install sebelumnya)
1. Install **XAMPP**, jalankan **Apache** dan **MySQL**
2. Copy folder `lombok_tourism` ke `C:\xampp\htdocs\lombok_tourism\`
3. Buka `http://localhost/phpmyadmin` → buat database `lombok_tourism`
4. Import **`database.sql`** (sudah lengkap dengan tabel hotel/restoran + data sample)
5. Buka `http://localhost/lombok_tourism/`

### Upgrade dari Versi Lama (sudah punya database sebelumnya)
1. Backup database Anda dulu (Export via phpMyAdmin)
2. Buka tab **SQL** di phpMyAdmin pada database `lombok_tourism` Anda
3. Import **`database_hotel_restoran.sql`** — ini akan:
   - Membuat tabel baru: `hotel`, `restoran`, `hotel_rating`, `restoran_rating` (masing-masing dengan kolom `link_lokasi`)
   - Mengisi data sample hotel & restoran
4. Replace seluruh folder project dengan file-file baru ini
5. Selesai — data wisata/user/komentar lama Anda tetap aman

---

## 🔑 Akun Default

| Role  | Username | Password |
|-------|----------|----------|
| Admin | `admin`  | `admin123` |

---

## 🎨 Cara Mengganti Background Hero

1. Login sebagai admin → **Pengaturan**
2. Upload gambar di bagian **Background Halaman Beranda** (JPG/PNG/WebP, maks 5MB)
3. Klik **Upload Background**

---

## 🗺️ Cara Mengisi Link Google Maps

1. Buka [Google Maps](https://maps.google.com), cari lokasi wisata/hotel/restoran
2. Klik tombol **Bagikan** (Share) pada lokasi tersebut
3. Klik **Salin link** (Copy link)
4. Paste link tersebut ke field **Link Google Maps** di form admin
5. Tombol "Buka di Google Maps" di halaman detail akan otomatis aktif

---

## ✨ Fitur Lengkap

### Fitur User
- Register & Login dengan enkripsi password (bcrypt)
- Edit profil, upload foto profil, ganti password
- Pencarian wisata + filter kategori + sort
- **Pencarian global** (wisata + hotel + restoran) di navbar

### Fitur Admin
- CRUD lengkap: Wisata, Hotel, Restoran
- Upload multi-foto wisata, foto tunggal untuk hotel/restoran
- Kelola komentar & rating untuk wisata, hotel, dan restoran secara terpisah
- Hubungkan hotel/restoran ke wisata terdekat (otomatis tampil sebagai rekomendasi)
- Pengaturan teks website & background hero

### Fitur Detail Wisata
- Galeri foto + lightbox
- Peta embed + tombol buka Maps (jika koordinat diisi)
- Rating bintang + distribusi visual
- Komentar + upload foto komentar
- **Rekomendasi Penginapan** & **Rekomendasi Tempat Makan** otomatis

### Fitur Detail Hotel & Restoran
- Foto, deskripsi, harga, peta embed
- Rating + ulasan (gabungan, 1 per user, bisa diedit)
- Daftar hotel/restoran lain di sekitar wisata yang sama

---

## 🛠️ Teknologi

- **Backend:** PHP 8+ (Native, tanpa framework)
- **Database:** MySQL 5.7+ / MariaDB 10+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla, AJAX Fetch API)
- **Font:** Playfair Display + DM Sans
- **Icons:** Font Awesome 6.5
- **Warna:** Langit `#0EA5E9` · Laut `#06B6D4` · Alam/Daun `#22C55E`

---

## ❓ Troubleshooting

**Hotel/Restoran tidak muncul di rekomendasi wisata?**
- Pastikan field **"Wisata Terdekat"** sudah dipilih saat tambah/edit hotel/restoran

**Tombol "Buka di Google Maps" tidak muncul?**
- Pastikan field **Link Google Maps** sudah diisi dengan link yang valid (diawali `http://` atau `https://`)

**Upload foto gagal?**
- Pastikan folder `uploads/hotel/` dan `uploads/restoran/` ada dan bisa ditulis
- Cek `php.ini`: `upload_max_filesize = 10M`, `post_max_size = 20M`


