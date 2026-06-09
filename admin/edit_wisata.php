<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: wisata.php'); exit; }

$wisata = $db->query("SELECT * FROM tempat_wisata WHERE id=$id")->fetch_assoc();
if (!$wisata) { header('Location: wisata.php'); exit; }

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
            $photo = $db->query("SELECT * FROM wisata_foto WHERE id=$photoId AND wisata_id=$id")->fetch_assoc();
            if ($photo) {
                $path = UPLOAD_DIR . $photo['foto'];
                if (file_exists($path)) unlink($path);
                $db->query("DELETE FROM wisata_foto WHERE id=$photoId");
                // Jika foto utama dihapus, jadikan foto pertama yang tersisa sebagai utama
                $remaining = $db->query("SELECT id FROM wisata_foto WHERE wisata_id=$id ORDER BY id ASC LIMIT 1")->fetch_assoc();
                if ($remaining) $db->query("UPDATE wisata_foto SET is_primary=1 WHERE id={$remaining['id']}");
            }
        }
        header("Location: edit_wisata.php?id=$id&msg=photo_deleted");
        exit;
    }

    /* ── Set foto utama ── */
    if ($action === 'set_primary') {
        $photoId = intval($_POST['photo_id'] ?? 0);
        if ($photoId) {
            $db->query("UPDATE wisata_foto SET is_primary=0 WHERE wisata_id=$id");
            $db->query("UPDATE wisata_foto SET is_primary=1 WHERE id=$photoId AND wisata_id=$id");
        }
        header("Location: edit_wisata.php?id=$id&msg=primary_set");
        exit;
    }

    /* ── Update data wisata + tambah foto baru ── */
    if ($action === 'update') {
        $nama        = sanitize($_POST['nama']        ?? '');
        $deskripsi   = trim($_POST['deskripsi']       ?? '');
        $harga       = floatval($_POST['harga_tiket'] ?? 0);
        $jam_buka    = sanitize($_POST['jam_buka']    ?? '');
        $jam_tutup   = sanitize($_POST['jam_tutup']   ?? '');
        $rating_awal = floatval($_POST['rating_awal'] ?? 0);
        $link_lokasi = sanitize($_POST['link_lokasi'] ?? '');
        $kategori    = sanitize($_POST['kategori']    ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        if (strlen($nama) < 3) {
            $error = 'Nama wisata minimal 3 karakter.';
        } else {
            // Update data utama
            $upd = $db->prepare("
                UPDATE tempat_wisata
                SET nama=?, deskripsi=?, harga_tiket=?, jam_buka=?, jam_tutup=?,
                    rating_awal=?, link_lokasi=?, kategori=?, is_featured=?
                WHERE id=?
            ");
            $upd->bind_param("ssdssdsiii",
                $nama, $deskripsi, $harga,
                $jam_buka, $jam_tutup, $rating_awal,
                $link_lokasi, $kategori, $is_featured,
                $id
            );

            if (!$upd->execute()) {
                $error = 'Gagal update: ' . $upd->error;
            } else {
                // Upload foto-foto baru (jika ada)
                if (!empty($_FILES['fotos']['name'][0])) {
                    $uploadDir = UPLOAD_DIR . 'wisata/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

                    // Cek apakah sudah ada foto utama
                    $hasPrimary = $db->query(
                        "SELECT id FROM wisata_foto WHERE wisata_id=$id AND is_primary=1"
                    )->num_rows > 0;

                    foreach ($_FILES['fotos']['tmp_name'] as $k => $tmp) {
                        // Lewati jika tidak ada file atau ada error upload
                        if ($_FILES['fotos']['error'][$k] !== UPLOAD_ERR_OK || empty($tmp)) continue;

                        $file = [
                            'name'     => $_FILES['fotos']['name'][$k],
                            'type'     => $_FILES['fotos']['type'][$k],
                            'tmp_name' => $tmp,
                            'size'     => $_FILES['fotos']['size'][$k],
                        ];
                        $up = uploadFile($file, 'wisata');
                        if (isset($up['success'])) {
                            $fn     = $up['filename'];
                            $isPrim = $hasPrimary ? 0 : 1;
                            $hasPrimary = true; // foto selanjutnya bukan utama
                            $ps = $db->prepare(
                                "INSERT INTO wisata_foto (wisata_id, foto, is_primary) VALUES (?,?,?)"
                            );
                            $ps->bind_param("isi", $id, $fn, $isPrim);
                            $ps->execute();
                        }
                    }
                }
                // Redirect bersih setelah sukses → cegah re-submit saat refresh
                header("Location: edit_wisata.php?id=$id&msg=updated");
                exit;
            }
        }
    }
}

/* ════ Load fresh data setelah redirect / GET ════ */
$wisata         = $db->query("SELECT * FROM tempat_wisata WHERE id=$id")->fetch_assoc();
$existingPhotos = $db->query(
    "SELECT * FROM wisata_foto WHERE wisata_id=$id ORDER BY is_primary DESC, id ASC"
)->fetch_all(MYSQLI_ASSOC);

$msgMap = [
    'updated'       => '✅ Wisata berhasil diperbarui!',
    'photo_deleted' => '🗑️ Foto berhasil dihapus.',
    'primary_set'   => '⭐ Foto utama berhasil diubah.',
];
$msg = $error ?: ($msgMap[$_GET['msg'] ?? ''] ?? '');

$pageTitle = 'Edit Wisata — Admin';
include __DIR__ . '/admin_header.php';
$categories = ['Pantai','Gunung','Pulau','Budaya','Alam','Kuliner','Lainnya'];
?>

<div class="admin-content">

    <!-- Page header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 class="admin-page-title">Edit Wisata</h1>
            <p class="admin-page-subtitle"><?= htmlspecialchars($wisata['nama']) ?></p>
        </div>
        <div style="display:flex;gap:8px">
            <a href="../detail.php?id=<?= $id ?>" target="_blank" class="btn btn-outline btn-sm">
                <i class="fas fa-eye"></i> Lihat
            </a>
            <a href="wisata.php" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
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
         (form-form ini BERDIRI SENDIRI, tidak di dalam form utama)
         ════════════════════════════════ -->
    <?php if (!empty($existingPhotos)): ?>
    <div class="form-card" style="margin-bottom:24px">
        <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
            <i class="fas fa-images" style="color:var(--blue-400)"></i>
            Foto Saat Ini
            <span style="font-size:.78rem;font-weight:400;color:var(--gray-400);margin-left:8px">
                (<?= count($existingPhotos) ?> foto)
            </span>
        </h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px">
            <?php foreach ($existingPhotos as $ph): ?>
            <div style="border:2px solid <?= $ph['is_primary'] ? 'var(--blue-400)' : 'var(--gray-100)' ?>;border-radius:var(--radius-md);overflow:hidden;background:white">
                <!-- Gambar -->
                <div style="position:relative;height:130px;overflow:hidden;background:var(--gray-50)">
                    <img src="../uploads/<?= htmlspecialchars($ph['foto']) ?>"
                         alt="foto wisata"
                         style="width:100%;height:100%;object-fit:cover"
                         data-lightbox="../uploads/<?= htmlspecialchars($ph['foto']) ?>">
                    <?php if ($ph['is_primary']): ?>
                    <div style="position:absolute;top:6px;left:6px;background:var(--blue-500);color:white;font-size:.68rem;font-weight:700;padding:3px 9px;border-radius:999px">
                        ★ UTAMA
                    </div>
                    <?php endif; ?>
                </div>
                <!-- Tombol aksi — masing-masing form TERPISAH, tidak nested -->
                <div style="padding:10px;display:flex;gap:6px;flex-wrap:wrap">
                    <?php if (!$ph['is_primary']): ?>
                    <!-- Form set primary -->
                    <form method="POST" action="edit_wisata.php?id=<?= $id ?>" style="flex:1">
                        <input type="hidden" name="action"   value="set_primary">
                        <input type="hidden" name="photo_id" value="<?= $ph['id'] ?>">
                        <button type="submit"
                                style="width:100%;padding:5px 0;font-size:.72rem;background:var(--blue-50);color:var(--blue-700);border:1px solid var(--blue-200);border-radius:6px;cursor:pointer;font-weight:600">
                            <i class="fas fa-star"></i> Utamakan
                        </button>
                    </form>
                    <?php endif; ?>
                    <!-- Form hapus foto -->
                    <form method="POST" action="edit_wisata.php?id=<?= $id ?>" style="<?= $ph['is_primary'] ? 'flex:1' : '' ?>">
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
         (satu form bersih tanpa nested form di dalamnya)
         ════════════════════════════════ -->
    <form method="POST" action="edit_wisata.php?id=<?= $id ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update">

        <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">

            <!-- Kolom kiri -->
            <div>
                <!-- Info dasar -->
                <div class="form-card" style="margin-bottom:20px">
                    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
                        <i class="fas fa-info-circle" style="color:var(--blue-400)"></i> Informasi Dasar
                    </h3>

                    <div class="form-group">
                        <label class="form-label">Nama Wisata <span>*</span></label>
                        <input type="text" name="nama" class="form-control"
                               value="<?= htmlspecialchars($wisata['nama']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi <span>*</span></label>
                        <textarea name="deskripsi" class="form-control" rows="6" required><?= htmlspecialchars($wisata['deskripsi']) ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" class="form-control">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat ?>" <?= $wisata['kategori']===$cat?'selected':'' ?>>
                                        <?= $cat ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga Tiket (Rp)</label>
                            <input type="number" name="harga_tiket" class="form-control"
                                   min="0" step="1000" value="<?= $wisata['harga_tiket'] ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Jam Buka</label>
                            <input type="time" name="jam_buka" class="form-control"
                                   value="<?= $wisata['jam_buka'] ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jam Tutup</label>
                            <input type="time" name="jam_tutup" class="form-control"
                                   value="<?= $wisata['jam_tutup'] ?>">
                        </div>
                    </div>

                        <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Rating Awal</label>
                            <input type="number" name="rating_awal" class="form-control" min="0" max="5" step="0.1" value="<?= $wisata['rating_awal'] ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Link Google Maps</label>
                            <input type="url" name="link_lokasi" class="form-control" value="<?= htmlspecialchars($wisata['link_lokasi']) ?>" placeholder="https://maps.google.com/...">
                        </div>
                    </div>
                </div>

                <!-- Upload foto baru -->
                <div class="form-card">
                    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
                        <i class="fas fa-plus-circle" style="color:var(--green-500)"></i> Tambah Foto Baru
                    </h3>
                    <p style="font-size:.82rem;color:var(--gray-400);margin-bottom:14px">
                        Foto yang sudah ada <strong>tidak akan terhapus</strong>. Upload di sini hanya menambahkan foto baru.
                    </p>
                    <div class="file-upload-area">
                        <input type="file" name="fotos[]" multiple accept="image/*">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Klik atau seret foto ke sini</strong></p>
                        <p style="font-size:.78rem;color:var(--gray-400);margin-top:4px">JPG, PNG, WebP — maks. 5MB per foto</p>
                    </div>
                    <div class="preview-grid" id="photoPreviewGrid"></div>
                </div>
            </div>

            <!-- Kolom kanan (sidebar) -->
            <div style="position:sticky;top:100px">
                <div class="form-card" style="margin-bottom:16px">
                    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:16px">
                        <i class="fas fa-cog" style="color:var(--blue-400)"></i> Opsi Publikasi
                    </h3>
                    <label class="form-check">
                        <input type="checkbox" name="is_featured" value="1"
                               <?= $wisata['is_featured'] ? 'checked' : '' ?>>
                        <span>Tampilkan di Halaman Beranda ⭐</span>
                    </label>
                    <p class="form-hint" style="margin-top:8px">Wisata unggulan tampil di section "Destinasi Unggulan"</p>
                </div>

                <div style="display:flex;flex-direction:column;gap:10px">
                    <button type="submit" class="btn btn-primary btn-lg" style="justify-content:center">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="../detail.php?id=<?= $id ?>" target="_blank"
                       class="btn btn-outline" style="justify-content:center">
                        <i class="fas fa-eye"></i> Preview Halaman
                    </a>
                    <a href="delete_wisata.php?id=<?= $id ?>"
                       class="btn btn-danger" style="justify-content:center"
                       data-confirm="Hapus wisata ini secara permanen? Semua foto dan komentar ikut terhapus.">
                        <i class="fas fa-trash"></i> Hapus Wisata Ini
                    </a>
                </div>

                <!-- Info foto -->
                <div style="margin-top:16px;padding:14px;background:var(--blue-50);border-radius:var(--radius-md);border:1px solid var(--blue-100)">
                    <p style="font-size:.78rem;color:var(--blue-700);font-weight:700;margin-bottom:6px">
                        <i class="fas fa-info-circle"></i> Info Foto
                    </p>
                    <ul style="font-size:.75rem;color:var(--blue-600);line-height:1.9;padding-left:14px">
                        <li>Foto utama tampil sebagai thumbnail kartu</li>
                        <li>Klik <strong>Utamakan</strong> untuk ganti foto utama</li>
                        <li>Klik <strong>Hapus</strong> untuk hapus foto satu per satu</li>
                        <li>Upload foto baru tidak menghapus yang lama</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>

</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
    <div class="lightbox-inner">
        <button class="lightbox-close" id="lightboxClose"><i class="fas fa-times"></i></button>
        <img src="" id="lightboxImg" alt="">
    </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
