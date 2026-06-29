<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_restoran = sanitize($_POST['nama_restoran'] ?? '');
    $lokasi        = sanitize($_POST['lokasi'] ?? '');
    $alamat        = sanitize($_POST['alamat'] ?? '');
    $deskripsi     = trim($_POST['deskripsi'] ?? '');
    $harga         = floatval($_POST['harga_rata_rata'] ?? 0);
    $link_lokasi   = sanitize($_POST['link_lokasi'] ?? '');
    $wisataId      = intval($_POST['wisata_terdekat_id'] ?? 0) ?: null;
    $adminId       = intval($_SESSION['user_id']);

    if (strlen($nama_restoran) < 3) {
        $error = 'Nama restoran minimal 3 karakter.';
    } elseif (strlen($lokasi) < 2) {
        $error = 'Lokasi wajib diisi.';
    } else {
        // ── INSERT restoran (foto utama diisi belakangan dari foto pertama) ──
        $ins = $db->prepare("
            INSERT INTO restoran
                (nama_restoran, lokasi, alamat, deskripsi, harga_rata_rata,
                 link_lokasi, wisata_terdekat_id, admin_id)
            VALUES (?,?,?,?,?,?,?,?)
        ");
        $ins->bind_param(
            "ssssdsii",
            $nama_restoran, $lokasi, $alamat, $deskripsi, $harga,
            $link_lokasi, $wisataId, $adminId
        );

        if (!$ins->execute()) {
            $error = 'Gagal menyimpan restoran: ' . $ins->error;
        } else {
            $restoranId = $db->insert_id;
            $isPrimary  = 1; // foto pertama = foto utama
            $firstFoto  = null;

            // ── Upload foto-foto (pola sama persis dengan modul Wisata) ──
            if (!empty($_FILES['fotos']['name'][0])) {
                $uploadDir = UPLOAD_DIR . 'restoran/';
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
                    $up = uploadFile($file, 'restoran');
                    if (isset($up['success'])) {
                        $fn      = $up['filename'];
                        $caption = sanitize($captions[$k] ?? '');
                        $ps = $db->prepare(
                            "INSERT INTO restoran_foto (restoran_id, foto, caption, is_primary)
                             VALUES (?, ?, ?, ?)"
                        );
                        $ps->bind_param("issi", $restoranId, $fn, $caption, $isPrimary);
                        $ps->execute();

                        if ($isPrimary) $firstFoto = $fn;
                        $isPrimary = 0; // hanya foto pertama yang jadi utama
                    }
                }

                // Simpan foto pertama juga ke kolom `foto` di tabel restoran,
                // supaya kartu listing (restoran.php, search.php, dll) tetap
                // bisa menampilkan thumbnail tanpa JOIN tambahan.
                if ($firstFoto) {
                    $upd = $db->prepare("UPDATE restoran SET foto = ? WHERE id = ?");
                    $upd->bind_param("si", $firstFoto, $restoranId);
                    $upd->execute();
                }
            }

            header('Location: restoran.php?msg=added');
            exit;
        }
    }
}

$wisataList = $db->query("SELECT id, nama FROM tempat_wisata ORDER BY nama ASC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Tambah Restoran - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 class="admin-page-title">Tambah Restoran / Tempat Makan</h1>
            <p class="admin-page-subtitle">Isi informasi lengkap restoran</p>
        </div>
        <a href="restoran.php" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
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
                        <input type="text" name="nama_restoran" class="form-control"
                               value="<?= htmlspecialchars($_POST['nama_restoran'] ?? '') ?>"
                               placeholder="Contoh: Ayam Taliwang Bersaudara" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Lokasi <span>*</span></label>
                            <input type="text" name="lokasi" class="form-control"
                                   value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>"
                                   placeholder="Contoh: Kuta Mandalika" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Harga Rata-rata (Rp) <span>*</span></label>
                            <input type="number" name="harga_rata_rata" class="form-control" min="0" step="5000"
                                   value="<?= htmlspecialchars($_POST['harga_rata_rata'] ?? '0') ?>" required>
                            <p class="form-hint">Per porsi/orang</p>
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
                                  placeholder="Ceritakan menu andalan dan suasana restoran..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
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
                        <p class="form-hint">Restoran ini akan muncul sebagai "Tempat Makan Terdekat" di halaman detail wisata terkait</p>
                    </div>
                </div>

                <div class="form-card">
                    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
                        <i class="fas fa-images" style="color:var(--green-600)"></i> Foto-foto Restoran
                    </h3>
                    <p style="font-size:.85rem;color:var(--gray-500);margin-bottom:16px">
                        Upload minimal 1 foto. Foto pertama akan dijadikan foto utama. Bisa pilih beberapa foto sekaligus. Maks 5MB per foto.
                    </p>
                    <div class="file-upload-area" id="restoFotoDropzone">
                        <input type="file" name="fotos[]" id="restoFotoInput" multiple accept="image/*">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p><strong>Klik atau seret foto ke sini</strong></p>
                        <p style="font-size:.8rem;color:var(--gray-400);margin-top:4px">JPG, PNG, WebP — maks 5MB per file</p>
                    </div>
                    <div class="preview-grid" id="restoFotoPreview"></div>
                </div>
            </div>

            <div>
                <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px">
                    <button type="submit" class="btn btn-green btn-lg" style="justify-content:center">
                        <i class="fas fa-plus-circle"></i> Simpan Restoran
                    </button>
                    <a href="restoran.php" class="btn btn-outline" style="justify-content:center">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>