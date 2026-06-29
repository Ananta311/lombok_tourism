<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Hotel & Penginapan - Lombok Tourism';
$db = getDB();

$q = sanitize($_GET['q'] ?? '');
$lokasi = sanitize($_GET['lokasi'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'rating');

$where = "WHERE 1=1";
$params = []; $types = '';

if ($q) {
    $where .= " AND (h.nama_hotel LIKE ? OR h.lokasi LIKE ?)";
    $lq = "%$q%"; $params[] = $lq; $params[] = $lq; $types .= 'ss';
}
if ($lokasi) {
    $where .= " AND h.lokasi = ?";
    $params[] = $lokasi; $types .= 's';
}

$orderBy = match($sort) {
    'price_asc'  => 'h.harga_per_malam ASC',
    'price_desc' => 'h.harga_per_malam DESC',
    'name'       => 'h.nama_hotel ASC',
    default      => 'avg_rating DESC'
};

$sql = "SELECT h.*,
        COALESCE(AVG(hr.rating),0) as avg_rating,
        COUNT(DISTINCT hr.id) as total_rating,
        tw.nama as wisata_nama
        FROM hotel h
        LEFT JOIN hotel_rating hr ON hr.hotel_id = h.id
        LEFT JOIN tempat_wisata tw ON tw.id = h.wisata_terdekat_id
        $where
        GROUP BY h.id
        ORDER BY $orderBy";
$stmt = $db->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$hotels = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$locations = $db->query("SELECT DISTINCT lokasi FROM hotel ORDER BY lokasi")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div style="min-height:100vh; padding-top:80px; background:var(--gray-50);">

    <div style="background:linear-gradient(135deg, var(--cyan-700), var(--blue-600)); padding:56px 24px 40px; color:white;">
        <div class="container">
            <div style="font-size:0.82rem; opacity:0.7; margin-bottom:8px">
                <a href="index.php" style="color:inherit">Beranda</a> › Hotel & Penginapan
            </div>
            <h1 style="font-family:var(--font-display); font-size:2rem; font-weight:800; margin-bottom:8px">
                <i class="fas fa-hotel"></i> Hotel & Penginapan
            </h1>
            <p style="opacity:0.85"><?= count($hotels) ?> penginapan tersedia di seluruh Lombok</p>
        </div>
    </div>

    <div class="container" style="padding-top:32px; padding-bottom:64px;">
        <div style="background:white; border-radius:var(--radius-lg); padding:24px; margin-bottom:28px; box-shadow:var(--shadow-sm); border:1px solid var(--gray-100)">
            <form method="GET" style="display:flex; flex-wrap:wrap; gap:14px; align-items:center">
                <div class="search-bar" style="flex:1; min-width:240px">
                    <i class="fas fa-search" style="color:var(--gray-400)"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama hotel...">
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
                    <a href="hotel.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($hotels)): ?>
            <div style="text-align:center; padding:80px 24px; color:var(--gray-400)">
                <i class="fas fa-hotel" style="font-size:4rem; margin-bottom:20px; display:block"></i>
                <h3 style="font-size:1.3rem; margin-bottom:8px; color:var(--gray-600)">Tidak ada hotel ditemukan</h3>
                <p>Coba kata kunci atau lokasi yang berbeda</p>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($hotels as $i => $h):
                    $avgR = round($h['avg_rating'], 1);
                    $stars = renderStars($avgR);
                ?>
                <div class="lodging-card" data-reveal data-delay="<?= $i % 3 ?>">
                    <div class="lodging-img-wrap">
                        <?php if ($h['foto']): ?>
                            <img src="uploads/<?= htmlspecialchars($h['foto']) ?>" alt="<?= htmlspecialchars($h['nama_hotel']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="lodging-img-placeholder"><i class="fas fa-hotel"></i></div>
                        <?php endif; ?>
                        <div class="lodging-category-badge"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($h['lokasi']) ?></div>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($h['nama_hotel']) ?></h3>
                        <p class="card-desc"><?= htmlspecialchars($h['deskripsi'] ?? '') ?></p>
                        <?php if ($h['wisata_nama']): ?>
                        <div style="font-size:.8rem;color:var(--cyan-600);margin-bottom:10px">
                            <i class="fas fa-map-pin"></i> Dekat <?= htmlspecialchars($h['wisata_nama']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="card-meta">
                            <div class="card-rating">
                                <span class="stars"><?= $stars ?></span>
                                <span><?= $avgR ?></span>
                                <span class="rating-count">(<?= $h['total_rating'] ?>)</span>
                            </div>
                            <div class="card-price">Rp <?= number_format($h['harga_per_malam'],0,',','.') ?><span style="font-size:.7rem;color:var(--gray-400)">/malam</span></div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="hotel_detail.php?id=<?= $h['id'] ?>" class="btn btn-primary btn-sm">
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
