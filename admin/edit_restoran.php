<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: restoran.php'); exit; }

$restoran = $db->query("SELECT * FROM restoran WHERE id=$id")->fetch_assoc();
if (!$restoran) { header('Location: restoran.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_restoran = sanitize($_POST['nama_restoran'] ?? '');
    $lokasi        = sanitize($_POST['lokasi'] ?? '');
    $alamat        = sanitize($_POST['alamat'] ?? '');
    $deskripsi     = trim($_POST['deskripsi'] ?? '');
    $harga         = floatval($_POST['harga_rata_rata'] ?? 0);
    $link_lokasi   = sanitize($_POST['link_lokasi'] ?? '');
    $wisataId      = intval($_POST['wisata_terdekat_id'] ?? 0) ?: null;

    if (strlen($nama_restoran) < 3) {
        $error = 'Nama restoran minimal 3 karakter.';
    } else {
        $fotoName = $restoran['foto'];

        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $up = uploadFile($_FILES['foto'], 'restoran');
            if (isset($up['error'])) {
                $error = $up['error'];
            } else {
                if ($restoran['foto']) {
                    $oldPath = UPLOAD_DIR . $restoran['foto'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $fotoName = $up['filename'];
            }
        }

        if (!$error) {
            $upd = $db->prepare("
                UPDATE restoran SET nama_restoran=?, lokasi=?, alamat=?, deskripsi=?, harga_rata_rata=?,
                    foto=?, link_lokasi=?, wisata_terdekat_id=?
                WHERE id=?
            ");
            $upd->bind_param(
                "ssssdssii",
                $nama_restoran, $lokasi, $alamat, $deskripsi, $harga,
                $fotoName, $link_lokasi, $wisataId, $id
            );
            if ($upd->execute()) {
                header('Location: restoran.php?msg=updated');
                exit;
            } else {
                $error = 'Gagal update: ' . $upd->error;
            }
        }
    }
}

$wisataList = $db->query("SELECT id, nama FROM tempat_wisata ORDER BY nama ASC")->fetch_all(MYSQLI_ASSOC);
$pageTitle  = 'Edit Restoran - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 class="admin-page-title">Edit Restoran</h1>
            <p class="admin-page-subtitle"><?= htmlspecialchars($restoran['nama_restoran']) ?></p>
        </div>
        <div style="display:flex;gap:8px">
            <a href="../restoran_detail.php?id=<?= $id ?>" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Lihat</a>
            <a href="restoran.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
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
                        <i class="fas fa-utensils" style="color:var(--green-600)"></i> Informasi Restoran
                    </h3>
                    <div class="form-group">
                        <label class="form-label">Nama Restoran <span>*</span></label>
                        <input type="text" name="nama_restoran" class="form-control" value="<?= htmlspecialchars($restoran['nama_restoran']) ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Lokasi <span>*</span></label>
                            <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($restoran['lokasi']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga Rata-rata (Rp)</label>
                            <input type="number" name="harga_rata_rata" class="form-control" min="0" step="5000" value="<?= $restoran['harga_rata_rata'] ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap</label>
                        <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($restoran['alamat'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="5"><?= htmlspecialchars($restoran['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Link Google Maps</label>
                        <input type="url" name="link_lokasi" class="form-control"
                               value="<?= htmlspecialchars($restoran['link_lokasi'] ?? '') ?>"
                               placeholder="https://maps.google.com/...">
                        <p class="form-hint">Salin link dari tombol "Bagikan" di Google Maps</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Wisata Terdekat</label>
                        <select name="wisata_terdekat_id" class="form-control">
                            <option value="">— Tidak terhubung —</option>
                            <?php foreach ($wisataList as $w): ?>
                                <option value="<?= $w['id'] ?>" <?= $restoran['wisata_terdekat_id']==$w['id']?'selected':'' ?>>
                                    <?= htmlspecialchars($w['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-card">
                    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
                        <i class="fas fa-image" style="color:var(--green-600)"></i> Foto Restoran
                    </h3>
                    <?php if ($restoran['foto']): ?>
                        <div style="margin-bottom:14px">
                            <img src="../uploads/<?= htmlspecialchars($restoran['foto']) ?>" style="width:160px;height:120px;object-fit:cover;border-radius:var(--radius-md);border:2px solid var(--gray-100)">
                            <p class="form-hint" style="margin-top:6px">Foto saat ini — upload baru untuk mengganti</p>
                        </div>
                    <?php endif; ?>
                    <div class="file-upload-area">
                        <input type="file" name="foto" id="restoFotoInput" accept="image/*">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Klik untuk ganti foto</strong></p>
                    </div>
                    <div class="preview-grid" id="restoFotoPreview"></div>
                </div>
            </div>

            <div>
                <div style="display:flex;flex-direction:column;gap:10px">
                    <button type="submit" class="btn btn-green btn-lg" style="justify-content:center">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="../restoran_detail.php?id=<?= $id ?>" target="_blank" class="btn btn-outline" style="justify-content:center">
                        <i class="fas fa-eye"></i> Preview
                    </a>
                    <a href="delete_restoran.php?id=<?= $id ?>" class="btn btn-danger" style="justify-content:center"
                       data-confirm="Hapus restoran ini secara permanen?">
                        <i class="fas fa-trash"></i> Hapus Restoran
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('restoFotoInput')?.addEventListener('change', function(){
    const grid = document.getElementById('restoFotoPreview');
    grid.innerHTML = '';
    if (this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { grid.innerHTML = `<div class="preview-item"><img src="${e.target.result}"></div>`; };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php include __DIR__ . '/admin_footer.php'; ?>
