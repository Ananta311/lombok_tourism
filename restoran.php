<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Restoran & Tempat Makan - Lombok Tourism';
$db = getDB();

$q = sanitize($_GET['q'] ?? '');
$lokasi = sanitize($_GET['lokasi'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'rating');

$where = "WHERE 1=1";
$params = []; $types = '';

if ($q) {
    $where .= " AND (r.nama_restoran LIKE ? OR r.lokasi LIKE ?)";
    $lq = "%$q%"; $params[] = $lq; $params[] = $lq; $types .= 'ss';
}
if ($lokasi) {
    $where .= " AND r.lokasi = ?";
    $params[] = $lokasi; $types .= 's';
}

$orderBy = match($sort) {
    'price_asc'  => 'r.harga_rata_rata ASC',
    'price_desc' => 'r.harga_rata_rata DESC',
    'name'       => 'r.nama_restoran ASC',
    default      => 'avg_rating DESC'
};

$sql = "SELECT r.*, rf.foto as foto_utama,
        COALESCE(AVG(rr.rating),0) as avg_rating,
        COUNT(DISTINCT rr.id) as total_rating,
        tw.nama as wisata_nama
        FROM restoran r
        LEFT JOIN restoran_rating rr ON rr.restoran_id = r.id
        LEFT JOIN restoran_foto rf ON rf.restoran_id = r.id AND rf.is_primary = 1
        LEFT JOIN tempat_wisata tw ON tw.id = r.wisata_terdekat_id
        $where
        GROUP BY r.id
        ORDER BY $orderBy";
$stmt = $db->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$restorans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$locations = $db->query("SELECT DISTINCT lokasi FROM restoran ORDER BY lokasi")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div style="min-height:100vh; padding-top:80px; background:var(--gray-50);">

    <div style="background:linear-gradient(135deg, var(--green-700), var(--cyan-600)); padding:56px 24px 40px; color:white;">
        <div class="container">
            <div style="font-size:0.82rem; opacity:0.7; margin-bottom:8px">
                <a href="index.php" style="color:inherit">Beranda</a> › Restoran & Tempat Makan
            </div>
            <h1 style="font-family:var(--font-display); font-size:2rem; font-weight:800; margin-bottom:8px">
                <i class="fas fa-utensils"></i> Restoran & Tempat Makan
            </h1>
            <p style="opacity:0.85"><?= count($restorans) ?> tempat makan tersedia di seluruh Lombok</p>
        </div>
    </div>

    <div class="container" style="padding-top:32px; padding-bottom:64px;">
        <div style="background:white; border-radius:var(--radius-lg); padding:24px; margin-bottom:28px; box-shadow:var(--shadow-sm); border:1px solid var(--gray-100)">
            <form method="GET" style="display:flex; flex-wrap:wrap; gap:14px; align-items:center">
                <div class="search-bar" style="flex:1; min-width:240px">
                    <i class="fas fa-search" style="color:var(--gray-400)"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama restoran...">
                    <button type="submit">Cari</button>
                </div>
                <select name="lokasi" class="form-control" style="width:auto; min-width:160px" onchange="this.form.submit()">
                    <option value="">Semua Lokasi</option>
                    <?php foreach ($locations as $l): ?>
                        <option value="<?= htmlspecialchars($l['lokasi']) ?>" <?= $lokasi===$l['lokasi']?'selected':'' ?>>
                            <?= htmlspecialchars($l['lokasi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="sort" class="form-control" style="width:auto; min-width:160px" onchange="this.form.submit()">
                    <option value="rating" <?= $sort==='rating'?'selected':'' ?>>Rating Tertinggi</option>
                    <option value="name" <?= $sort==='name'?'selected':'' ?>>Nama A-Z</option>
                    <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Harga Termurah</option>
                    <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Harga Termahal</option>
                </select>
                <?php if ($q || $lokasi): ?>
                    <a href="restoran.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($restorans)): ?>
            <div style="text-align:center; padding:80px 24px; color:var(--gray-400)">
                <i class="fas fa-utensils" style="font-size:4rem; margin-bottom:20px; display:block"></i>
                <h3 style="font-size:1.3rem; margin-bottom:8px; color:var(--gray-600)">Tidak ada restoran ditemukan</h3>
                <p>Coba kata kunci atau lokasi yang berbeda</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($restorans as $i => $r):
                    $avgR = round($r['avg_rating'], 1);
                    $stars = renderStars($avgR);
                ?>
                <div class="lodging-card" data-reveal data-delay="<?= $i % 3 ?>">
                    <div class="lodging-img-wrap">
                        <?php if ($r['foto_utama']): ?>
                            <img src="uploads/<?= htmlspecialchars($r['foto_utama']) ?>" alt="<?= htmlspecialchars($r['nama_restoran']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="lodging-img-placeholder" style="background:linear-gradient(135deg,var(--green-100),var(--cyan-100));color:var(--green-500)"><i class="fas fa-utensils"></i></div>
                        <?php endif; ?>
                        <div class="lodging-category-badge" style="background:linear-gradient(135deg,var(--green-500),var(--cyan-500))"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($r['lokasi']) ?></div>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($r['nama_restoran']) ?></h3>
                        <p class="card-desc"><?= htmlspecialchars($r['deskripsi'] ?? '') ?></p>
                        <?php if ($r['wisata_nama']): ?>
                        <div style="font-size:.8rem;color:var(--green-600);margin-bottom:10px">
                            <i class="fas fa-map-pin"></i> Dekat <?= htmlspecialchars($r['wisata_nama']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="card-meta">
                            <div class="card-rating">
                                <span class="stars"><?= $stars ?></span>
                                <span><?= $avgR ?></span>
                                <span class="rating-count">(<?= $r['total_rating'] ?>)</span>
                            </div>
                            <div class="card-price">Rp <?= number_format($r['harga_rata_rata'],0,',','.') ?><span style="font-size:.7rem;color:var(--gray-400)">/porsi</span></div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="restoran_detail.php?id=<?= $r['id'] ?>" class="btn btn-green btn-sm">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>