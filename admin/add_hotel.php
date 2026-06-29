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
        $fotoName = null;
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $up = uploadFile($_FILES['foto'], 'hotel');
            if (isset($up['error'])) {
                $error = $up['error'];
            } else {
                $fotoName = $up['filename'];
            }
        }

        if (!$error) {
            $ins = $db->prepare("
                INSERT INTO hotel
                    (nama_hotel, lokasi, alamat, deskripsi, harga_per_malam, foto,
                     link_lokasi, wisata_terdekat_id, admin_id)
                VALUES (?,?,?,?,?,?,?,?,?)
            ");
            $ins->bind_param(
                "ssssdssii",
                $nama_hotel, $lokasi, $alamat, $deskripsi, $harga, $fotoName,
                $link_lokasi, $wisataId, $adminId
            );
            if ($ins->execute()) {
                header('Location: hotel.php?msg=added');
                exit;
            } else {
                $error = 'Gagal menyimpan: ' . $ins->error;
            }
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
                        <i class="fas fa-image" style="color:var(--cyan-600)"></i> Foto Hotel
                    </h3>
                    <div class="file-upload-area">
                        <input type="file" name="foto" id="hotelFotoInput" accept="image/*">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Klik atau seret foto ke sini</strong></p>
                        <p style="font-size:.8rem;color:var(--gray-400);margin-top:4px">JPG, PNG, WebP — maks 5MB</p>
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

<script>
document.getElementById('hotelFotoInput')?.addEventListener('change', function(){
    const grid = document.getElementById('hotelFotoPreview');
    grid.innerHTML = '';
    if (this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            grid.innerHTML = `<div class="preview-item"><img src="${e.target.result}"></div>`;
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php include __DIR__ . '/admin_footer.php'; ?>
