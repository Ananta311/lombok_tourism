<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$db = getDB();
$q  = sanitize($_GET['q'] ?? '');
$pageTitle = 'Hasil Pencarian: ' . htmlspecialchars($q) . ' - Lombok Tourism';

$wisataResults = $hotelResults = $restoranResults = [];

if (strlen($q) >= 1) {
    $lq = "%$q%";

    $stmt = $db->prepare("
        SELECT tw.*, wf.foto as foto_utama,
               COALESCE(AVG(r.rating), tw.rating_awal) as avg_rating,
               COUNT(DISTINCT r.id) as total_rating
        FROM tempat_wisata tw
        LEFT JOIN wisata_foto wf ON wf.wisata_id = tw.id AND wf.is_primary = 1
        LEFT JOIN rating r ON r.wisata_id = tw.id
        WHERE tw.nama LIKE ? OR tw.deskripsi LIKE ? OR tw.kategori LIKE ?
        GROUP BY tw.id
        ORDER BY avg_rating DESC
    ");
    $stmt->bind_param("sss", $lq, $lq, $lq);
    $stmt->execute();
    $wisataResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $db->prepare("
        SELECT h.*, COALESCE(AVG(hr.rating),0) as avg_rating, COUNT(DISTINCT hr.id) as total_rating
        FROM hotel h
        LEFT JOIN hotel_rating hr ON hr.hotel_id = h.id
        WHERE h.nama_hotel LIKE ? OR h.lokasi LIKE ? OR h.deskripsi LIKE ?
        GROUP BY h.id
        ORDER BY avg_rating DESC
    ");
    $stmt->bind_param("sss", $lq, $lq, $lq);
    $stmt->execute();
    $hotelResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $db->prepare("
        SELECT r.*, COALESCE(AVG(rr.rating),0) as avg_rating, COUNT(DISTINCT rr.id) as total_rating
        FROM restoran r
        LEFT JOIN restoran_rating rr ON rr.restoran_id = r.id
        WHERE r.nama_restoran LIKE ? OR r.lokasi LIKE ? OR r.deskripsi LIKE ?
        GROUP BY r.id
        ORDER BY avg_rating DESC
    ");
    $stmt->bind_param("sss", $lq, $lq, $lq);
    $stmt->execute();
    $restoranResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$totalResults = count($wisataResults) + count($hotelResults) + count($restoranResults);

include __DIR__ . '/includes/header.php';
?>

<div style="min-height:100vh; padding-top:80px; background:var(--gray-50);">

    <div style="background:linear-gradient(135deg, var(--blue-800), var(--cyan-600)); padding:56px 24px 40px; color:white;">
        <div class="container">
            <div style="font-size:0.82rem; opacity:0.7; margin-bottom:8px">
                <a href="index.php" style="color:inherit">Beranda</a> › Hasil Pencarian
            </div>
            <h1 style="font-family:var(--font-display); font-size:2rem; font-weight:800; margin-bottom:8px">
                <i class="fas fa-search"></i> Hasil Pencarian untuk "<?= htmlspecialchars($q) ?>"
            </h1>
            <p style="opacity:0.85"><?= $totalResults ?> hasil ditemukan di seluruh kategori</p>
        </div>
    </div>

    <div class="container" style="padding-top:32px; padding-bottom:64px;">

        <!-- Search box ulang -->
        <div style="background:white; border-radius:var(--radius-lg); padding:20px; margin-bottom:32px; box-shadow:var(--shadow-sm); border:1px solid var(--gray-100)">
            <form method="GET" action="search.php">
                <div class="search-bar" style="max-width:520px;margin:0 auto">
                    <i class="fas fa-search" style="color:var(--gray-400)"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari wisata, hotel, atau restoran...">
                    <button type="submit">Cari</button>
                </div>
            </form>
        </div>

        <?php if ($totalResults === 0): ?>
            <div style="text-align:center; padding:80px 24px; color:var(--gray-400)">
                <i class="fas fa-search" style="font-size:4rem; margin-bottom:20px; display:block"></i>
                <h3 style="font-size:1.3rem; margin-bottom:8px; color:var(--gray-600)">Tidak ada hasil ditemukan</h3>
                <p>Coba kata kunci lain, misalnya nama tempat, lokasi, atau kategori.</p>
            </div>
        <?php else: ?>

            <!-- ═══ WISATA ═══ -->
            <?php if (!empty($wisataResults)): ?>
            <div class="search-result-group">
                <div class="search-result-group-title">
                    <i class="fas fa-map-marked-alt" style="color:var(--blue-500)"></i> Tempat Wisata
                    <span class="count-pill"><?= count($wisataResults) ?></span>
                </div>
                <div class="cards-grid">
                    <?php foreach ($wisataResults as $w):
                        $avgR = round($w['avg_rating'],1);
                        $stars = renderStars($avgR);
                        $price = $w['harga_tiket'] > 0 ? 'Rp '.number_format($w['harga_tiket'],0,',','.') : 'Gratis';
                    ?>
                    <div class="wisata-card">
                        <div class="card-img-wrap">
                            <?php if ($w['foto_utama']): ?>
                                <img src="uploads/<?= htmlspecialchars($w['foto_utama']) ?>" alt="<?= htmlspecialchars($w['nama']) ?>">
                            <?php else: ?>
                                <div class="card-img-placeholder"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                            <div class="card-badge"><?= htmlspecialchars($w['kategori']) ?></div>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= htmlspecialchars($w['nama']) ?></h3>
                            <p class="card-desc"><?= htmlspecialchars($w['deskripsi']) ?></p>
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
                                <i class="fas fa-eye"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ═══ HOTEL ═══ -->
            <?php if (!empty($hotelResults)): ?>
            <div class="search-result-group">
                <div class="search-result-group-title">
                    <i class="fas fa-hotel" style="color:var(--cyan-600)"></i> Hotel & Penginapan
                    <span class="count-pill" style="background:var(--cyan-100);color:var(--cyan-700)"><?= count($hotelResults) ?></span>
                </div>
                <div class="cards-grid">
                    <?php foreach ($hotelResults as $h):
                        $avgR = round($h['avg_rating'],1);
                        $stars = renderStars($avgR);
                    ?>
                    <div class="lodging-card">
                        <div class="lodging-img-wrap">
                            <?php if ($h['foto']): ?>
                                <img src="uploads/<?= htmlspecialchars($h['foto']) ?>" alt="<?= htmlspecialchars($h['nama_hotel']) ?>">
                            <?php else: ?>
                                <div class="lodging-img-placeholder"><i class="fas fa-hotel"></i></div>
                            <?php endif; ?>
                            <div class="lodging-category-badge"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($h['lokasi']) ?></div>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= htmlspecialchars($h['nama_hotel']) ?></h3>
                            <p class="card-desc"><?= htmlspecialchars($h['deskripsi'] ?? '') ?></p>
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
            </div>
            <?php endif; ?>

            <!-- ═══ RESTORAN ═══ -->
            <?php if (!empty($restoranResults)): ?>
            <div class="search-result-group">
                <div class="search-result-group-title">
                    <i class="fas fa-utensils" style="color:var(--green-600)"></i> Restoran & Tempat Makan
                    <span class="count-pill" style="background:var(--green-100);color:var(--green-700)"><?= count($restoranResults) ?></span>
                </div>
                <div class="cards-grid">
                    <?php foreach ($restoranResults as $r):
                        $avgR = round($r['avg_rating'],1);
                        $stars = renderStars($avgR);
                    ?>
                    <div class="lodging-card">
                        <div class="lodging-img-wrap">
                            <?php if ($r['foto']): ?>
                                <img src="uploads/<?= htmlspecialchars($r['foto']) ?>" alt="<?= htmlspecialchars($r['nama_restoran']) ?>">
                            <?php else: ?>
                                <div class="lodging-img-placeholder" style="background:linear-gradient(135deg,var(--green-100),var(--cyan-100));color:var(--green-500)"><i class="fas fa-utensils"></i></div>
                            <?php endif; ?>
                            <div class="lodging-category-badge" style="background:linear-gradient(135deg,var(--green-500),var(--cyan-500))"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($r['lokasi']) ?></div>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><?= htmlspecialchars($r['nama_restoran']) ?></h3>
                            <p class="card-desc"><?= htmlspecialchars($r['deskripsi'] ?? '') ?></p>
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
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
