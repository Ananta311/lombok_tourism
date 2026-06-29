<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_hotel   = sanitize($_POST['nama_hotel'] ?? '');
    $lokasi       = sanitize($_POST['lokasi'] ?? '');
    $alamat       = sanitize($_POST['alamat'] ?? '');
    $deskripsi    = trim($_POST['deskripsi'] ?? '');
    $harga        = floatval($_POST['harga_per_malam'] ?? 0);
    $link_lokasi  = sanitize($_POST['link_lokasi'] ?? '');
    $wisataId     = intval($_POST['wisata_terdekat_id'] ?? 0) ?: null;
    $adminId      = intval($_SESSION['user_id']);

    if (strlen($nama_hotel) < 3) {
        $error = 'Nama hotel minimal 3 karakter.';
    } elseif (strlen($lokasi) < 2) {
        $error = 'Lokasi wajib diisi.';
    } else {
        // ── INSERT hotel (foto utama diisi belakangan dari foto pertama) ──
        $ins = $db->prepare("
            INSERT INTO hotel
                (nama_hotel, lokasi, alamat, deskripsi, harga_per_malam,
                 link_lokasi, wisata_terdekat_id, admin_id)
            VALUES (?,?,?,?,?,?,?,?)
        ");
        $ins->bind_param(
            "ssssdsii",
            $nama_hotel, $lokasi, $alamat, $deskripsi, $harga,
            $link_lokasi, $wisataId, $adminId
        );

        if (!$ins->execute()) {
            $error = 'Gagal menyimpan hotel: ' . $ins->error;
        } else {
            $hotelId   = $db->insert_id;
            $isPrimary = 1; // foto pertama = foto utama
            $firstFoto = null;

            // ── Upload foto-foto (pola sama persis dengan modul Wisata) ──
            if (!empty($_FILES['fotos']['name'][0])) {
                $uploadDir = UPLOAD_DIR . 'hotel/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $captions = $_POST['captions'] ?? [];
                foreach ($_FILES['fotos']['tmp_name'] as $k => $tmp) {
                    if (empty($tmp) || $_FILES['fotos']['error'][$k] !== UPLOAD_ERR_OK) continue;

                    $file = [
                        'name'     => $_FILES['fotos']['name'][$k],
                        'type'     => $_FILES['fotos']['type'][$k],
                        'tmp_name' => $tmp,
                        'size'     => $_FILES['fotos']['size'][$k],
                        'error'    => $_FILES['fotos']['error'][$k],
                    ];
                    $up = uploadFile($file, 'hotel');
                    if (isset($up['success'])) {
                        $fn      = $up['filename'];
                        $caption = sanitize($captions[$k] ?? '');
                        $ps = $db->prepare(
                            "INSERT INTO hotel_foto (hotel_id, foto, caption, is_primary)
                             VALUES (?, ?, ?, ?)"
                        );
                        $ps->bind_param("issi", $hotelId, $fn, $caption, $isPrimary);
                        $ps->execute();

                        if ($isPrimary) $firstFoto = $fn;
                        $isPrimary = 0; // hanya foto pertama yang jadi utama
                    }
                }

                // Simpan foto pertama juga ke kolom `foto` di tabel hotel,
                // supaya kartu listing (hotel.php, search.php, dll) tetap
                // bisa menampilkan thumbnail tanpa JOIN tambahan.
                if ($firstFoto) {
                    $upd = $db->prepare("UPDATE hotel SET foto = ? WHERE id = ?");
                    $upd->bind_param("si", $firstFoto, $hotelId);
                    $upd->execute();
                }
            }

            header('Location: hotel.php?msg=added');
            exit;
        }
    }
}

$wisataList = $db->query("SELECT id, nama FROM tempat_wisata ORDER BY nama ASC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Tambah Hotel - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 class="admin-page-title">Tambah Hotel / Penginapan</h1>
            <p class="admin-page-subtitle">Isi informasi lengkap hotel</p>
        </div>
        <a href="hotel.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">

            <div>
                <div class="form-card" style="margin-bottom:20px">
                    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
                        <i class="fas fa-hotel" style="color:var(--cyan-600)"></i> Informasi Hotel
                    </h3>

                    <div class="form-group">
                        <label class="form-label">Nama Hotel <span>*</span></label>
                        <input type="text" name="nama_hotel" class="form-control"
                               value="<?= htmlspecialchars($_POST['nama_hotel'] ?? '') ?>"
                               placeholder="Contoh: Pullman Lombok Mandalika Beach Resort" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Lokasi <span>*</span></label>
                            <input type="text" name="lokasi" class="form-control"
                                   value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>"
                                   placeholder="Contoh: Kuta Mandalika" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga per Malam (Rp) <span>*</span></label>
                            <input type="number" name="harga_per_malam" class="form-control" min="0" step="10000"
                                   value="<?= htmlspecialchars($_POST['harga_per_malam'] ?? '0') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap</label>
                        <input type="text" name="alamat" class="form-control"
                               value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>"
                               placeholder="Jl. ..., Kecamatan, Kabupaten">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="5"
                                  placeholder="Ceritakan fasilitas, suasana, dan keunggulan hotel..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Link Google Maps</label>
                        <input type="url" name="link_lokasi" class="form-control"
                               value="<?= htmlspecialchars($_POST['link_lokasi'] ?? '') ?>"
                               placeholder="https://maps.google.com/...">
                        <p class="form-hint">Salin link dari tombol "Bagikan" di Google Maps</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Wisata Terdekat</label>
                        <select name="wisata_terdekat_id" class="form-control">
                            <option value="">— Tidak terhubung dengan wisata manapun —</option>
                            <?php foreach ($wisataList as $w): ?>
                                <option value="<?= $w['id'] ?>" <?= (isset($_POST['wisata_terdekat_id']) && $_POST['wisata_terdekat_id']==$w['id']) ? 'selected':'' ?>>
                                    <?= htmlspecialchars($w['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint">Hotel ini akan muncul sebagai "Rekomendasi Penginapan" di halaman detail wisata terkait</p>
                    </div>
                </div>

                <!-- Foto -->
                <div class="form-card">
                    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
                        <i class="fas fa-images" style="color:var(--cyan-600)"></i> Foto-foto Hotel
                    </h3>
                    <p style="font-size:.85rem;color:var(--gray-500);margin-bottom:16px">
                        Upload minimal 1 foto. Foto pertama akan dijadikan foto utama. Bisa pilih beberapa foto sekaligus. Maks 5MB per foto.
                    </p>
                    <div class="file-upload-area" id="hotelFotoDropzone">
                        <input type="file" name="fotos[]" id="hotelFotoInput" multiple accept="image/*">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Klik atau seret foto ke sini</strong></p>
                        <p style="font-size:.8rem;color:var(--gray-400);margin-top:4px">JPG, PNG, WebP — maks 5MB per file</p>
                    </div>
                    <div class="preview-grid" id="hotelFotoPreview"></div>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px">
                    <button type="submit" class="btn btn-primary btn-lg" style="justify-content:center">
                        <i class="fas fa-plus-circle"></i> Simpan Hotel
                    </button>
                    <a href="hotel.php" class="btn btn-outline" style="justify-content:center">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>