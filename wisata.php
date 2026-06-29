<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Daftar Wisata - Lombok Tourism';
$db = getDB();

$q = sanitize($_GET['q'] ?? '');
$kategori = sanitize($_GET['kategori'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'rating');

$where = "WHERE 1=1";
$params = [];
$types = '';

if ($q) {
    $where .= " AND (tw.nama LIKE ? OR tw.deskripsi LIKE ? OR tw.kategori LIKE ?)";
    $lq = "%$q%";
    $params = [$lq, $lq, $lq];
    $types = 'sss';
}
if ($kategori) {
    $where .= " AND tw.kategori = ?";
    $params[] = $kategori; $types .= 's';
}

$orderBy = match($sort) {
    'price_asc'  => 'tw.harga_tiket ASC',
    'price_desc' => 'tw.harga_tiket DESC',
    'name'       => 'tw.nama ASC',
    'newest'     => 'tw.created_at DESC',
    default      => 'avg_rating DESC'
};

$sql = "SELECT tw.*, wf.foto as foto_utama,
        COALESCE(AVG(r.rating), tw.rating_awal) as avg_rating,
        COUNT(DISTINCT r.id) as total_rating,
        COUNT(DISTINCT k.id) as total_komentar
        FROM tempat_wisata tw
        LEFT JOIN wisata_foto wf ON wf.wisata_id = tw.id AND wf.is_primary = 1
        LEFT JOIN rating r ON r.wisata_id = tw.id
        LEFT JOIN komentar k ON k.wisata_id = tw.id
        $where
        GROUP BY tw.id
        ORDER BY $orderBy";

$stmt = $db->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$wisataList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$categories = $db->query("SELECT DISTINCT kategori FROM tempat_wisata ORDER BY kategori")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div style="min-height:100vh; padding-top:80px; background:var(--gray-50);">

    <!-- Page header -->
    <div style="background:linear-gradient(135deg, var(--blue-800), var(--blue-600)); padding:56px 24px 40px; color:white;">
        <div class="container">
            <div style="font-size:0.82rem; opacity:0.7; margin-bottom:8px">
                <a href="index.php" style="color:inherit">Beranda</a> › Wisata
            </div>
            <h1 style="font-family:var(--font-display); font-size:2rem; font-weight:800; margin-bottom:8px">
                <?php if ($q): ?>
                    Hasil Pencarian: "<?= htmlspecialchars($q) ?>"
                <?php elseif ($kategori): ?>
                    Wisata <?= htmlspecialchars($kategori) ?>
                <?php else: ?>
                    Semua Destinasi Wisata
                <?php endif; ?>
            </h1>
            <p style="opacity:0.8"><?= count($wisataList) ?> destinasi ditemukan</p>
        </div>
    </div>

    <div class="container" style="padding-top:32px; padding-bottom:64px;">
        <!-- Search & Filter -->
        <div style="background:white; border-radius:var(--radius-lg); padding:24px; margin-bottom:28px; box-shadow:var(--shadow-sm); border:1px solid var(--gray-100)">
            <form method="GET" style="display:flex; flex-wrap:wrap; gap:14px; align-items:center">
                <div class="search-bar" style="flex:1; min-width:240px">
                    <i class="fas fa-search" style="color:var(--gray-400)"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama wisata...">
                    <button type="submit">Cari</button>
                </div>
                <select name="kategori" class="form-control" style="width:auto; min-width:150px" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= htmlspecialchars($c['kategori']) ?>" <?= $kategori === $c['kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="sort" class="form-control" style="width:auto; min-width:160px" onchange="this.form.submit()">
                    <option value="rating"     <?= $sort==='rating'     ? 'selected':'' ?>>Rating Tertinggi</option>
                    <option value="newest"     <?= $sort==='newest'     ? 'selected':'' ?>>Terbaru</option>
                    <option value="name"       <?= $sort==='name'       ? 'selected':'' ?>>Nama A-Z</option>
                    <option value="price_asc"  <?= $sort==='price_asc'  ? 'selected':'' ?>>Harga Termurah</option>
                    <option value="price_desc" <?= $sort==='price_desc' ? 'selected':'' ?>>Harga Termahal</option>
                </select>
                <?php if ($q || $kategori): ?>
                    <a href="wisata.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-times"></i> Reset
                    </a>
                <?php endif; ?>
            </form>

            <!-- Category chips -->
            <div class="filter-chips" style="margin-top:16px">
                <a href="wisata.php?sort=<?= $sort ?>" class="filter-chip <?= !$kategori ? 'active':'' ?>">
                    <i class="fas fa-th-large"></i> Semua
                </a>
                <?php foreach ($categories as $c): ?>
                    <a href="wisata.php?kategori=<?= urlencode($c['kategori']) ?>&sort=<?= $sort ?>" 
                       class="filter-chip <?= $kategori === $c['kategori'] ? 'active':'' ?>">
                        <?= htmlspecialchars($c['kategori']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cards -->
        <?php if (empty($wisataList)): ?>
            <div style="text-align:center; padding:80px 24px; color:var(--gray-400)">
                <i class="fas fa-compass" style="font-size:4rem; margin-bottom:20px; display:block"></i>
                <h3 style="font-size:1.3rem; margin-bottom:8px; color:var(--gray-600)">Tidak ada wisata ditemukan</h3>
                <p>Coba kata kunci atau kategori yang berbeda</p>
                <a href="wisata.php" class="btn btn-primary mt-3">Lihat Semua Wisata</a>
            </div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($wisataList as $i => $w):
                    $avgR = round($w['avg_rating'], 1);
                    $stars = str_repeat('★', round($avgR)) . str_repeat('☆', 5 - round($avgR));
                    $price = $w['harga_tiket'] > 0 ? 'Rp ' . number_format($w['harga_tiket'], 0, ',', '.') : 'Gratis';
                ?>
                <div class="wisata-card" data-reveal data-delay="<?= $i % 3 ?>">
                    <div class="card-img-wrap">
                        <?php if ($w['foto_utama']): ?>
                            <img src="uploads/<?= htmlspecialchars($w['foto_utama']) ?>" alt="<?= htmlspecialchars($w['nama']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="card-img-placeholder"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                        <div class="card-badge"><?= htmlspecialchars($w['kategori']) ?></div>
                        <?php if ($w['is_featured']): ?><div class="card-badge-featured">⭐ Unggulan</div><?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($w['nama']) ?></h3>
                        <p class="card-desc"><?= htmlspecialchars($w['deskripsi']) ?></p>
                        <div style="display:flex; flex-wrap:wrap; gap:12px; font-size:0.82rem; color:var(--gray-500); margin-bottom:12px">
                            <?php if ($w['jam_buka']): ?>
                            <span><i class="fas fa-clock" style="color:var(--blue-400)"></i> <?= substr($w['jam_buka'],0,5) ?>–<?= substr($w['jam_tutup'],0,5) ?></span>
                            <?php endif; ?>
                            <span><i class="fas fa-comment" style="color:var(--green-400)"></i> <?= $w['total_komentar'] ?> ulasan</span>
                        </div>
                        <div class="card-meta">
                            <div class="card-rating">
                                <span class="stars"><?= $stars ?></span>
                                <span><?= $avgR ?></span>
                                <span class="rating-count">(<?= $w['total_rating'] ?>)</span>
                            </div>
                            <div class="card-price"><?= $price ?></div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="detail.php?id=<?= $w['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        <?php if ($w['link_lokasi']): ?>
                            <a href="<?= htmlspecialchars($w['link_lokasi']) ?>" target="_blank" class="btn btn-outline btn-sm">
                                <i class="fas fa-map-marker-alt"></i> Maps
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
