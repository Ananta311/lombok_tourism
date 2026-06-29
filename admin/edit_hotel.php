<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: hotel.php'); exit; }

$hotel = $db->query("SELECT * FROM hotel WHERE id=$id")->fetch_assoc();
if (!$hotel) { header('Location: hotel.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_hotel  = sanitize($_POST['nama_hotel'] ?? '');
    $lokasi      = sanitize($_POST['lokasi'] ?? '');
    $alamat      = sanitize($_POST['alamat'] ?? '');
    $deskripsi   = trim($_POST['deskripsi'] ?? '');
    $harga       = floatval($_POST['harga_per_malam'] ?? 0);
    $link_lokasi = sanitize($_POST['link_lokasi'] ?? '');
    $wisataId    = intval($_POST['wisata_terdekat_id'] ?? 0) ?: null;

    if (strlen($nama_hotel) < 3) {
        $error = 'Nama hotel minimal 3 karakter.';
    } else {
        $fotoName = $hotel['foto']; // default: foto lama dipertahankan

        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $up = uploadFile($_FILES['foto'], 'hotel');
            if (isset($up['error'])) {
                $error = $up['error'];
            } else {
                // Hapus foto lama dari disk
                if ($hotel['foto']) {
                    $oldPath = UPLOAD_DIR . $hotel['foto'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $fotoName = $up['filename'];
            }
        }

        if (!$error) {
            $upd = $db->prepare("
                UPDATE hotel SET nama_hotel=?, lokasi=?, alamat=?, deskripsi=?, harga_per_malam=?,
                    foto=?, link_lokasi=?, wisata_terdekat_id=?
                WHERE id=?
            ");
            $upd->bind_param(
                "ssssdssii",
                $nama_hotel, $lokasi, $alamat, $deskripsi, $harga,
                $fotoName, $link_lokasi, $wisataId, $id
            );
            if ($upd->execute()) {
                header('Location: hotel.php?msg=updated');
                exit;
            } else {
                $error = 'Gagal update: ' . $upd->error;
            }
        }
    }
}

$wisataList = $db->query("SELECT id, nama FROM tempat_wisata ORDER BY nama ASC")->fetch_all(MYSQLI_ASSOC);
$pageTitle  = 'Edit Hotel - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 class="admin-page-title">Edit Hotel</h1>
            <p class="admin-page-subtitle"><?= htmlspecialchars($hotel['nama_hotel']) ?></p>
        </div>
        <div style="display:flex;gap:8px">
            <a href="../hotel_detail.php?id=<?= $id ?>" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Lihat</a>
            <a href="hotel.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
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
                        <input type="text" name="nama_hotel" class="form-control" value="<?= htmlspecialchars($hotel['nama_hotel']) ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Lokasi <span>*</span></label>
                            <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($hotel['lokasi']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga per Malam (Rp)</label>
                            <input type="number" name="harga_per_malam" class="form-control" min="0" step="10000" value="<?= $hotel['harga_per_malam'] ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap</label>
                        <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($hotel['alamat'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="5"><?= htmlspecialchars($hotel['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Link Google Maps</label>
                        <input type="url" name="link_lokasi" class="form-control"
                               value="<?= htmlspecialchars($hotel['link_lokasi'] ?? '') ?>"
                               placeholder="https://maps.google.com/...">
                        <p class="form-hint">Salin link dari tombol "Bagikan" di Google Maps</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Wisata Terdekat</label>
                        <select name="wisata_terdekat_id" class="form-control">
                            <option value="">— Tidak terhubung —</option>
                            <?php foreach ($wisataList as $w): ?>
                                <option value="<?= $w['id'] ?>" <?= $hotel['wisata_terdekat_id']==$w['id']?'selected':'' ?>>
                                    <?= htmlspecialchars($w['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-card">
                    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
                        <i class="fas fa-image" style="color:var(--cyan-600)"></i> Foto Hotel
                    </h3>
                    <?php if ($hotel['foto']): ?>
                        <div style="margin-bottom:14px">
                            <img src="../uploads/<?= htmlspecialchars($hotel['foto']) ?>" style="width:160px;height:120px;object-fit:cover;border-radius:var(--radius-md);border:2px solid var(--gray-100)">
                            <p class="form-hint" style="margin-top:6px">Foto saat ini — upload baru untuk mengganti</p>
                        </div>
                    <?php endif; ?>
                    <div class="file-upload-area">
                        <input type="file" name="foto" id="hotelFotoInput" accept="image/*">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Klik untuk ganti foto</strong></p>
                    </div>
                    <div class="preview-grid" id="hotelFotoPreview"></div>
                </div>
            </div>

            <div>
                <div style="display:flex;flex-direction:column;gap:10px">
                    <button type="submit" class="btn btn-primary btn-lg" style="justify-content:center">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="../hotel_detail.php?id=<?= $id ?>" target="_blank" class="btn btn-outline" style="justify-content:center">
                        <i class="fas fa-eye"></i> Preview
                    </a>
                    <a href="delete_hotel.php?id=<?= $id ?>" class="btn btn-danger" style="justify-content:center"
                       data-confirm="Hapus hotel ini secara permanen?">
                        <i class="fas fa-trash"></i> Hapus Hotel
                    </a>
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
        reader.onload = e => { grid.innerHTML = `<div class="preview-item"><img src="${e.target.result}"></div>`; };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php include __DIR__ . '/admin_footer.php'; ?>
