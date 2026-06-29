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

/* ════════════════════════════════════════════
   HANDLE POST — tiga aksi terpisah, semua redirect setelah selesai
   ════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update';

    /* ── Hapus satu foto ── */
    if ($action === 'delete_photo') {
        $photoId = intval($_POST['photo_id'] ?? 0);
        if ($photoId) {
            $photo = $db->query("SELECT * FROM restoran_foto WHERE id=$photoId AND restoran_id=$id")->fetch_assoc();
            if ($photo) {
                $path = UPLOAD_DIR . $photo['foto'];
                if (file_exists($path)) unlink($path);
                $db->query("DELETE FROM restoran_foto WHERE id=$photoId");

                // Jika foto utama dihapus, jadikan foto pertama yang tersisa sebagai utama
                $remaining = $db->query("SELECT id, foto FROM restoran_foto WHERE restoran_id=$id ORDER BY id ASC LIMIT 1")->fetch_assoc();
                if ($remaining) {
                    $db->query("UPDATE restoran_foto SET is_primary=1 WHERE id={$remaining['id']}");
                    $newFoto = $db->real_escape_string($remaining['foto']);
                    $db->query("UPDATE restoran SET foto='$newFoto' WHERE id=$id");
                } else {
                    $db->query("UPDATE restoran SET foto=NULL WHERE id=$id");
                }
            }
        }
        header("Location: edit_restoran.php?id=$id&msg=photo_deleted");
        exit;
    }

    /* ── Set foto utama ── */
    if ($action === 'set_primary') {
        $photoId = intval($_POST['photo_id'] ?? 0);
        if ($photoId) {
            $db->query("UPDATE restoran_foto SET is_primary=0 WHERE restoran_id=$id");
            $db->query("UPDATE restoran_foto SET is_primary=1 WHERE id=$photoId AND restoran_id=$id");
            $photo = $db->query("SELECT foto FROM restoran_foto WHERE id=$photoId AND restoran_id=$id")->fetch_assoc();
            if ($photo) {
                $newFoto = $db->real_escape_string($photo['foto']);
                $db->query("UPDATE restoran SET foto='$newFoto' WHERE id=$id");
            }
        }
        header("Location: edit_restoran.php?id=$id&msg=primary_set");
        exit;
    }

    /* ── Update data restoran + tambah foto baru ── */
    if ($action === 'update') {
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
            $upd = $db->prepare("
                UPDATE restoran SET nama_restoran=?, lokasi=?, alamat=?, deskripsi=?, harga_rata_rata=?,
                    link_lokasi=?, wisata_terdekat_id=?
                WHERE id=?
            ");
            $upd->bind_param(
                "ssssdsii",
                $nama_restoran, $lokasi, $alamat, $deskripsi, $harga,
                $link_lokasi, $wisataId, $id
            );

            if (!$upd->execute()) {
                $error = 'Gagal update: ' . $upd->error;
            } else {
                // Upload foto-foto baru (jika ada)
                if (!empty($_FILES['fotos']['name'][0])) {
                    $uploadDir = UPLOAD_DIR . 'restoran/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

                    // Cek apakah sudah ada foto utama
                    $hasPrimary = $db->query(
                        "SELECT id FROM restoran_foto WHERE restoran_id=$id AND is_primary=1"
                    )->num_rows > 0;

                    $firstNewFoto = null;
                    foreach ($_FILES['fotos']['tmp_name'] as $k => $tmp) {
                        if ($_FILES['fotos']['error'][$k] !== UPLOAD_ERR_OK || empty($tmp)) continue;

                        $file = [
                            'name'     => $_FILES['fotos']['name'][$k],
                            'type'     => $_FILES['fotos']['type'][$k],
                            'tmp_name' => $tmp,
                            'size'     => $_FILES['fotos']['size'][$k],
                        ];
                        $up = uploadFile($file, 'restoran');
                        if (isset($up['success'])) {
                            $fn     = $up['filename'];
                            $isPrim = $hasPrimary ? 0 : 1;
                            if (!$hasPrimary) $firstNewFoto = $fn;
                            $hasPrimary = true; // foto selanjutnya bukan utama
                            $ps = $db->prepare(
                                "INSERT INTO restoran_foto (restoran_id, foto, is_primary) VALUES (?,?,?)"
                            );
                            $ps->bind_param("isi", $id, $fn, $isPrim);
                            $ps->execute();
                        }
                    }

                    // Jika restoran belum punya foto utama sebelumnya, set kolom foto juga
                    if ($firstNewFoto) {
                        $u2 = $db->prepare("UPDATE restoran SET foto=? WHERE id=?");
                        $u2->bind_param("si", $firstNewFoto, $id);
                        $u2->execute();
                    }
                }
                // Redirect bersih setelah sukses → cegah re-submit saat refresh
                header("Location: edit_restoran.php?id=$id&msg=updated");
                exit;
            }
        }
    }
}

/* ════ Load fresh data setelah redirect / GET ════ */
$restoran       = $db->query("SELECT * FROM restoran WHERE id=$id")->fetch_assoc();
$existingPhotos = $db->query(
    "SELECT * FROM restoran_foto WHERE restoran_id=$id ORDER BY is_primary DESC, id ASC"
)->fetch_all(MYSQLI_ASSOC);

$msgMap = [
    'updated'       => '✅ Restoran berhasil diperbarui!',
    'photo_deleted' => '🗑️ Foto berhasil dihapus.',
    'primary_set'   => '⭐ Foto utama berhasil diubah.',
];
$msg = $error ?: ($msgMap[$_GET['msg'] ?? ''] ?? '');

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

    <?php if ($msg): ?>
        <div class="alert alert-<?= str_starts_with($msg,'❌')||str_starts_with($msg,'Gagal')?'danger':'success' ?>">
            <i class="fas fa-<?= str_starts_with($msg,'❌')||str_starts_with($msg,'Gagal')?'exclamation-circle':'check-circle' ?>"></i>
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <!-- ════════════════════════════════
         BAGIAN 1 — Foto yang sudah ada
         ════════════════════════════════ -->
    <?php if (!empty($existingPhotos)): ?>
    <div class="form-card" style="margin-bottom:24px">
        <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
            <i class="fas fa-images" style="color:var(--green-600)"></i>
            Foto Saat Ini
            <span style="font-size:.78rem;font-weight:400;color:var(--gray-400);margin-left:8px">
                (<?= count($existingPhotos) ?> foto)
            </span>
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px">
            <?php foreach ($existingPhotos as $ph): ?>
            <div style="border:2px solid <?= $ph['is_primary'] ? 'var(--green-400)' : 'var(--gray-100)' ?>;border-radius:var(--radius-md);overflow:hidden;background:white">
                <div style="position:relative;height:130px;overflow:hidden;background:var(--gray-50)">
                    <img src="../uploads/<?= htmlspecialchars($ph['foto']) ?>"
                         alt="foto restoran"
                         style="width:100%;height:100%;object-fit:cover"
                         data-lightbox="../uploads/<?= htmlspecialchars($ph['foto']) ?>">
                    <?php if ($ph['is_primary']): ?>
                    <div style="position:absolute;top:6px;left:6px;background:var(--green-500);color:white;font-size:.68rem;font-weight:700;padding:3px 9px;border-radius:999px">
                        ★ UTAMA
                    </div>
                    <?php endif; ?>
                </div>
                <div style="padding:10px;display:flex;gap:6px;flex-wrap:wrap">
                    <?php if (!$ph['is_primary']): ?>
                    <form method="POST" action="edit_restoran.php?id=<?= $id ?>" style="flex:1">
                        <input type="hidden" name="action"   value="set_primary">
                        <input type="hidden" name="photo_id" value="<?= $ph['id'] ?>">
                        <button type="submit"
                                style="width:100%;padding:5px 0;font-size:.72rem;background:var(--green-50);color:var(--green-700);border:1px solid var(--green-200);border-radius:6px;cursor:pointer;font-weight:600">
                            <i class="fas fa-star"></i> Utamakan
                        </button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" action="edit_restoran.php?id=<?= $id ?>" style="<?= $ph['is_primary'] ? 'flex:1' : '' ?>">
                        <input type="hidden" name="action"   value="delete_photo">
                        <input type="hidden" name="photo_id" value="<?= $ph['id'] ?>">
                        <button type="submit"
                                data-confirm="Hapus foto ini secara permanen?"
                                style="width:100%;padding:5px 8px;font-size:.72rem;background:#fef2f2;color:var(--danger);border:1px solid #fecaca;border-radius:6px;cursor:pointer;font-weight:600">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ════════════════════════════════
         BAGIAN 2 — Form utama edit data + tambah foto baru
         ════════════════════════════════ -->
    <form method="POST" action="edit_restoran.php?id=<?= $id ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update">

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

                <!-- Tambah foto baru -->
                <div class="form-card">
                    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
                        <i class="fas fa-plus-circle" style="color:var(--green-500)"></i> Tambah Foto Baru
                    </h3>
                    <p style="font-size:.82rem;color:var(--gray-400);margin-bottom:14px">
                        Foto yang sudah ada <strong>tidak akan terhapus</strong>. Upload di sini hanya menambahkan foto baru. Bisa pilih beberapa sekaligus.
                    </p>
                    <div class="file-upload-area">
                        <input type="file" name="fotos[]" multiple accept="image/*">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Klik atau seret foto ke sini</strong></p>
                        <p style="font-size:.78rem;color:var(--gray-400);margin-top:4px">JPG, PNG, WebP — maks. 5MB per foto</p>
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

<?php include __DIR__ . '/admin_footer.php'; ?>