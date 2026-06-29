<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
$db = getDB();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: wisata.php'); exit; }

// Get wisata
$stmt = $db->prepare("SELECT tw.* FROM tempat_wisata tw WHERE tw.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$wisata = $stmt->get_result()->fetch_assoc();
if (!$wisata) { header('Location: wisata.php'); exit; }

// Get photos
$photos = $db->query("SELECT * FROM wisata_foto WHERE wisata_id = $id ORDER BY is_primary DESC, id ASC")->fetch_all(MYSQLI_ASSOC);

// Rekomendasi Hotel terdekat (berdasarkan wisata_terdekat_id)
$recommendedHotels = $db->query("
    SELECT h.*, COALESCE(AVG(hr.rating),0) as avg_rating, COUNT(DISTINCT hr.id) as total_rating
    FROM hotel h
    LEFT JOIN hotel_rating hr ON hr.hotel_id = h.id
    WHERE h.wisata_terdekat_id = $id
    GROUP BY h.id
    ORDER BY avg_rating DESC
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Rekomendasi Restoran terdekat (berdasarkan wisata_terdekat_id)
$recommendedRestorans = $db->query("
    SELECT r.*, COALESCE(AVG(rr.rating),0) as avg_rating, COUNT(DISTINCT rr.id) as total_rating
    FROM restoran r
    LEFT JOIN restoran_rating rr ON rr.restoran_id = r.id
    WHERE r.wisata_terdekat_id = $id
    GROUP BY r.id
    ORDER BY avg_rating DESC
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Rating
$ratingData = getAvgRating($id);
$avgRating = round($ratingData['avg_r'] ?? $wisata['rating_awal'], 1);
$totalRating = $ratingData['total'];

// User's rating
$userRating = 0;
if (isLoggedIn()) {
    $uid = intval($_SESSION['user_id']);
    $ur = $db->query("SELECT rating FROM rating WHERE wisata_id=$id AND user_id=$uid")->fetch_assoc();
    $userRating = $ur ? $ur['rating'] : 0;
}

// Submit rating
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if (!isLoggedIn()) {
        header('Location: user/login.php?redirect=' . urlencode("detail.php?id=$id"));
        exit;
    }

    if ($_POST['action'] === 'rating') {
        $r = intval($_POST['rating']);
        if ($r >= 1 && $r <= 5) {
            $uid = intval($_SESSION['user_id']);
            $stmt = $db->prepare("INSERT INTO rating (wisata_id, user_id, rating) VALUES (?,?,?) ON DUPLICATE KEY UPDATE rating=?");
            $stmt->bind_param("iiii", $id, $uid, $r, $r);
            $stmt->execute();
            $msg = ['type'=>'success', 'text'=>'Rating berhasil disimpan!'];
        }
    }

    if ($_POST['action'] === 'komentar') {
        $kom = sanitize($_POST['komentar'] ?? '');
        if (strlen($kom) < 3) {
            $msg = ['type'=>'danger', 'text'=>'Komentar minimal 3 karakter.'];
        } else {
            $uid = intval($_SESSION['user_id']);
            $stmt = $db->prepare("INSERT INTO komentar (wisata_id, user_id, komentar) VALUES (?,?,?)");
            $stmt->bind_param("iis", $id, $uid, $kom);
            $stmt->execute();
            $komId = $db->insert_id;

            // Upload comment photos
            if (!empty($_FILES['foto_komentar']['name'][0])) {
                foreach ($_FILES['foto_komentar']['tmp_name'] as $k => $tmp) {
                    $file = [
                        'name' => $_FILES['foto_komentar']['name'][$k],
                        'type' => $_FILES['foto_komentar']['type'][$k],
                        'tmp_name' => $tmp,
                        'size' => $_FILES['foto_komentar']['size'][$k],
                    ];
                    $up = uploadFile($file, 'komentar');
                    if (isset($up['success'])) {
                        $fn = $up['filename'];
                        $stmt2 = $db->prepare("INSERT INTO komentar_foto (komentar_id, foto) VALUES (?,?)");
                        $stmt2->bind_param("is", $komId, $fn);
                        $stmt2->execute();
                    }
                }
            }
            $msg = ['type'=>'success', 'text'=>'Komentar berhasil ditambahkan!'];
        }
    }

    // Redirect to avoid resubmit
    $msgParam = $msg ? '&msg=' . urlencode(json_encode($msg)) : '';
    header("Location: detail.php?id=$id$msgParam");
    exit;
}

// Get message from redirect
if (isset($_GET['msg'])) {
    $msg = json_decode(urldecode($_GET['msg']), true);
}

// Get comments
$komentar = $db->query("
    SELECT k.*, u.username, p.full_name, p.foto_profile,
           (SELECT rating FROM rating WHERE wisata_id=$id AND user_id=k.user_id) as user_rating
    FROM komentar k
    JOIN users u ON u.id = k.user_id
    LEFT JOIN profile p ON p.user_id = k.user_id
    WHERE k.wisata_id = $id
    ORDER BY k.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Get comment photos
foreach ($komentar as &$kom) {
    $kom['fotos'] = $db->query("SELECT foto FROM komentar_foto WHERE komentar_id = {$kom['id']}")->fetch_all(MYSQLI_ASSOC);
}
unset($kom);

$pageTitle = htmlspecialchars($wisata['nama']) . ' - Lombok Tourism';
include __DIR__ . '/includes/header.php';
$stars = str_repeat('★', round($avgRating)) . str_repeat('☆', 5 - round($avgRating));
$price = $wisata['harga_tiket'] > 0 ? 'Rp ' . number_format($wisata['harga_tiket'], 0, ',', '.') : 'Gratis';
?>

<div style="padding-top:72px; min-height:100vh; background:var(--gray-50)">

<!-- Hero Photo -->
<div class="wisata-detail-hero">
    <?php $mainPhoto = $photos[0] ?? null; ?>
    <?php if ($mainPhoto): ?>
        <img src="uploads/<?= htmlspecialchars($mainPhoto['foto']) ?>" alt="<?= htmlspecialchars($wisata['nama']) ?>" id="mainHeroImg">
    <?php else: ?>
        <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--blue-800),var(--green-700));display:flex;align-items:center;justify-content:center;font-size:5rem;color:rgba(255,255,255,0.3)"><i class="fas fa-image"></i></div>
    <?php endif; ?>
    <div class="wisata-detail-info">
        <div class="container">
            <div style="font-size:0.82rem;color:rgba(255,255,255,0.7);margin-bottom:10px">
                <a href="index.php" style="color:inherit">Beranda</a> ›
                <a href="wisata.php" style="color:inherit">Wisata</a> ›
                <?= htmlspecialchars($wisata['nama']) ?>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:10px">
                <span style="background:var(--blue-500);color:white;padding:4px 14px;border-radius:999px;font-size:0.78rem;font-weight:600">
                    <?= htmlspecialchars($wisata['kategori']) ?>
                </span>
                <?php if ($wisata['is_featured']): ?>
                    <span style="background:var(--gold);color:white;padding:4px 12px;border-radius:999px;font-size:0.78rem;font-weight:600">⭐ Unggulan</span>
                <?php endif; ?>
            </div>
            <h1 style="font-family:var(--font-display);font-size:clamp(1.6rem,4vw,2.6rem);font-weight:900;margin-bottom:8px">
                <?= htmlspecialchars($wisata['nama']) ?>
            </h1>
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;font-size:0.9rem">
                <span style="color:var(--gold)"><?= $stars ?></span>
                <span><?= $avgRating ?> / 5 (<?= $totalRating ?> rating)</span>
                <span style="opacity:0.7">•</span>
                <span><?= count($komentar) ?> komentar</span>
            </div>
        </div>
    </div>
</div>

<div class="container" style="padding:40px 24px">
    <div style="display:grid;grid-template-columns:1fr 340px;gap:40px;align-items:start">

        <!-- LEFT CONTENT -->
        <div>
            <!-- Alert -->
            <?php if ($msg): ?>
                <div class="alert alert-<?= $msg['type'] ?>">
                    <i class="fas fa-<?= $msg['type']==='success'?'check-circle':'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($msg['text']) ?>
                </div>
            <?php endif; ?>

            <!-- Gallery -->
            <?php if (count($photos) > 0): ?>
            <div class="form-card" style="padding:24px;margin-bottom:28px">
                <h3 style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;margin-bottom:16px">
                    <i class="fas fa-images" style="color:var(--blue-400)"></i> Galeri Foto
                </h3>
                <div class="photo-gallery">
                    <div class="gallery-main">
                        <img src="uploads/<?= htmlspecialchars($photos[0]['foto']) ?>" alt="gallery main" id="galleryMainImg" data-lightbox="uploads/<?= htmlspecialchars($photos[0]['foto']) ?>">
                    </div>
                    <?php if (count($photos) > 1): ?>
                    <div class="gallery-thumbs" style="margin-top:8px">
                        <?php foreach (array_slice($photos, 0, 4) as $pi => $pho): ?>
                            <?php $isLast = $pi === 3 && count($photos) > 4; ?>
                            <?php if ($isLast): ?>
                                <div class="gallery-more" data-lightbox="uploads/<?= htmlspecialchars($pho['foto']) ?>">
                                    <img src="uploads/<?= htmlspecialchars($pho['foto']) ?>">
                                    <div class="gallery-more-overlay">+<?= count($photos)-4 ?></div>
                                </div>
                            <?php else: ?>
                                <div class="gallery-thumb" onclick="document.getElementById('galleryMainImg').src='uploads/<?= htmlspecialchars($pho['foto']) ?>';document.getElementById('galleryMainImg').dataset.lightbox='uploads/<?= htmlspecialchars($pho['foto']) ?>'">
                                    <img src="uploads/<?= htmlspecialchars($pho['foto']) ?>" alt="foto <?= $pi+1 ?>">
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Deskripsi -->
            <div class="form-card" style="padding:28px;margin-bottom:28px">
                <h3 style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;margin-bottom:14px">
                    <i class="fas fa-info-circle" style="color:var(--blue-400)"></i> Tentang Tempat Ini
                </h3>
                <p style="color:var(--gray-700);line-height:1.85;font-size:0.95rem"><?= nl2br(htmlspecialchars($wisata['deskripsi'])) ?></p>
            </div>

            <!-- Rekomendasi Penginapan -->
            <div class="recommend-section">
                <div class="recommend-header">
                    <div class="recommend-title">
                        <i class="fas fa-hotel" style="color:var(--cyan-600)"></i> Rekomendasi Penginapan
                    </div>
                    <?php if (!empty($recommendedHotels)): ?>
                        <a href="hotel.php" style="font-size:.82rem;color:var(--cyan-600);font-weight:600">Lihat Semua Hotel →</a>
                    <?php endif; ?>
                </div>
                <?php if (empty($recommendedHotels)): ?>
                    <div class="recommend-empty">
                        <i class="fas fa-hotel" style="font-size:1.6rem;display:block;margin-bottom:8px;color:var(--gray-300)"></i>
                        Belum ada rekomendasi penginapan untuk wisata ini.
                    </div>
                <?php else: ?>
                    <div class="recommend-scroll">
                        <?php foreach ($recommendedHotels as $h):
                            $hAvg = round($h['avg_rating'],1);
                        ?>
                        <div class="mini-card">
                            <div class="mini-card-img">
                                <?php if ($h['foto']): ?>
                                    <img src="uploads/<?= htmlspecialchars($h['foto']) ?>" alt="<?= htmlspecialchars($h['nama_hotel']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="mini-card-img-placeholder"><i class="fas fa-hotel"></i></div>
                                <?php endif; ?>
                                <div class="mini-card-badge"><?= htmlspecialchars($h['lokasi']) ?></div>
                            </div>
                            <div class="mini-card-body">
                                <div class="mini-card-name"><?= htmlspecialchars($h['nama_hotel']) ?></div>
                                <div class="mini-card-location"><i class="fas fa-bed"></i> Penginapan</div>
                                <div class="mini-card-meta">
                                    <span class="mini-card-price">Rp <?= number_format($h['harga_per_malam'],0,',','.') ?>/malam</span>
                                    <span class="mini-card-rating">★ <?= $hAvg ?> <span>(<?= $h['total_rating'] ?>)</span></span>
                                </div>
                                <a href="hotel_detail.php?id=<?= $h['id'] ?>" class="mini-card-btn">Detail</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Rekomendasi Tempat Makan -->
            <div class="recommend-section">
                <div class="recommend-header">
                    <div class="recommend-title">
                        <i class="fas fa-utensils" style="color:var(--green-600)"></i> Rekomendasi Tempat Makan
                    </div>
                    <?php if (!empty($recommendedRestorans)): ?>
                        <a href="restoran.php" style="font-size:.82rem;color:var(--green-600);font-weight:600">Lihat Semua Restoran →</a>
                    <?php endif; ?>
                </div>
                <?php if (empty($recommendedRestorans)): ?>
                    <div class="recommend-empty">
                        <i class="fas fa-utensils" style="font-size:1.6rem;display:block;margin-bottom:8px;color:var(--gray-300)"></i>
                        Belum ada rekomendasi tempat makan untuk wisata ini.
                    </div>
                <?php else: ?>
                    <div class="recommend-scroll">
                        <?php foreach ($recommendedRestorans as $r):
                            $rAvg = round($r['avg_rating'],1);
                        ?>
                        <div class="mini-card">
                            <div class="mini-card-img">
                                <?php if ($r['foto']): ?>
                                    <img src="uploads/<?= htmlspecialchars($r['foto']) ?>" alt="<?= htmlspecialchars($r['nama_restoran']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="mini-card-img-placeholder" style="background:linear-gradient(135deg,var(--green-100),var(--cyan-100));color:var(--green-500)"><i class="fas fa-utensils"></i></div>
                                <?php endif; ?>
                                <div class="mini-card-badge"><?= htmlspecialchars($r['lokasi']) ?></div>
                            </div>
                            <div class="mini-card-body">
                                <div class="mini-card-name"><?= htmlspecialchars($r['nama_restoran']) ?></div>
                                <div class="mini-card-location"><i class="fas fa-utensils"></i> Tempat Makan</div>
                                <div class="mini-card-meta">
                                    <span class="mini-card-price">Rp <?= number_format($r['harga_rata_rata'],0,',','.') ?>/porsi</span>
                                    <span class="mini-card-rating">★ <?= $rAvg ?> <span>(<?= $r['total_rating'] ?>)</span></span>
                                </div>
                                <a href="restoran_detail.php?id=<?= $r['id'] ?>" class="mini-card-btn" style="background:linear-gradient(135deg,var(--green-500),var(--cyan-500))">Detail</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Rating Section -->
            <div class="form-card" style="padding:28px;margin-bottom:28px">
                <h3 style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;margin-bottom:20px">
                    <i class="fas fa-star" style="color:var(--gold)"></i> Rating & Ulasan
                </h3>

                <!-- Rating Overview -->
                <div style="display:flex;align-items:center;gap:32px;padding:20px;background:var(--gray-50);border-radius:var(--radius-md);margin-bottom:24px">
                    <div style="text-align:center">
                        <div style="font-family:var(--font-display);font-size:3.5rem;font-weight:900;color:var(--gray-900);line-height:1"><?= $avgRating ?></div>
                        <div style="color:var(--gold);font-size:1.2rem;margin:4px 0"><?= $stars ?></div>
                        <div style="font-size:0.8rem;color:var(--gray-400)"><?= $totalRating ?> penilaian</div>
                    </div>
                    <div style="flex:1">
                        <?php for ($s = 5; $s >= 1; $s--):
                            $cnt = $db->query("SELECT COUNT(*) FROM rating WHERE wisata_id=$id AND rating=$s")->fetch_row()[0];
                            $pct = $totalRating > 0 ? ($cnt / $totalRating * 100) : 0;
                        ?>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;font-size:0.82rem">
                            <span style="width:16px;text-align:right;color:var(--gray-500)"><?= $s ?></span>
                            <i class="fas fa-star" style="color:var(--gold);font-size:0.78rem"></i>
                            <div style="flex:1;height:8px;background:var(--gray-200);border-radius:4px;overflow:hidden">
                                <div style="height:100%;width:<?= $pct ?>%;background:var(--gold);border-radius:4px;transition:width 1s ease"></div>
                            </div>
                            <span style="width:24px;color:var(--gray-400)"><?= $cnt ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Give rating -->
                <?php if (isLoggedIn()): ?>
                <form method="POST" style="background:var(--blue-50);padding:20px;border-radius:var(--radius-md)">
                    <input type="hidden" name="action" value="rating">
                    <p style="font-weight:600;color:var(--gray-700);margin-bottom:12px">
                        <?= $userRating ? 'Rating Anda:' : 'Beri Rating:' ?>
                    </p>
                    <div class="star-rating" style="margin-bottom:14px">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <i class="fas fa-star <?= $userRating >= $s ? 'active' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingVal" value="<?= $userRating ?>">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-check"></i> Simpan Rating
                    </button>
                </form>
                <?php else: ?>
                <div style="background:var(--blue-50);padding:16px;border-radius:var(--radius-md);text-align:center">
                    <p style="color:var(--gray-600);margin-bottom:10px">Login untuk memberikan rating</p>
                    <a href="user/login.php?redirect=<?= urlencode("detail.php?id=$id") ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Comments -->
            <div class="form-card" style="padding:28px;margin-bottom:28px">
                <h3 style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;margin-bottom:20px">
                    <i class="fas fa-comments" style="color:var(--green-500)"></i> Komentar (<?= count($komentar) ?>)
                </h3>

                <!-- Add comment -->
                <?php if (isLoggedIn()): 
                    $cu = getCurrentUser();
                ?>
                <form method="POST" enctype="multipart/form-data" style="margin-bottom:28px;padding-bottom:28px;border-bottom:1px solid var(--gray-100)">
                    <input type="hidden" name="action" value="komentar">
                    <div style="display:flex;gap:14px;align-items:flex-start">
                        <?php if (!empty($cu['foto_profile'])): ?>
                            <img src="uploads/<?= htmlspecialchars($cu['foto_profile']) ?>" class="comment-avatar" style="flex-shrink:0" alt="">
                        <?php else: ?>
                            <div class="comment-avatar-placeholder" style="flex-shrink:0"><?= strtoupper(substr($cu['username'],0,1)) ?></div>
                        <?php endif; ?>
                        <div style="flex:1">
                            <textarea name="komentar" class="form-control" rows="3" placeholder="Bagikan pengalaman Anda..." required style="margin-bottom:12px"></textarea>
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;color:var(--gray-500);font-size:0.88rem;padding:8px 14px;border:2px dashed var(--gray-200);border-radius:var(--radius-md);transition:var(--transition)" 
                                       onmouseenter="this.style.borderColor='var(--blue-300)'" onmouseleave="this.style.borderColor='var(--gray-200)'">
                                    <i class="fas fa-camera" style="color:var(--blue-400)"></i>
                                    Tambah Foto
                                    <input type="file" name="foto_komentar[]" id="commentPhotos" multiple accept="image/*" style="display:none">
                                </label>
                                <button type="submit" class="btn btn-green btn-sm">
                                    <i class="fas fa-paper-plane"></i> Kirim
                                </button>
                            </div>
                            <div id="commentPhotoPreview" class="comment-photos" style="margin-top:10px"></div>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                <div style="text-align:center;padding:20px;background:var(--gray-50);border-radius:var(--radius-md);margin-bottom:20px">
                    <p style="color:var(--gray-500);margin-bottom:10px">Login untuk meninggalkan komentar</p>
                    <a href="user/login.php?redirect=<?= urlencode("detail.php?id=$id") ?>" class="btn btn-primary btn-sm">Masuk</a>
                </div>
                <?php endif; ?>

                <!-- Comment list -->
                <?php if (empty($komentar)): ?>
                    <div style="text-align:center;padding:40px;color:var(--gray-400)">
                        <i class="fas fa-comment-slash" style="font-size:2.5rem;margin-bottom:12px;display:block"></i>
                        <p>Belum ada komentar. Jadilah yang pertama!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($komentar as $k): 
                        $kStars = $k['user_rating'] ? str_repeat('★', $k['user_rating']) . str_repeat('☆', 5-$k['user_rating']) : '';
                    ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <?php if (!empty($k['foto_profile'])): ?>
                                <img src="uploads/<?= htmlspecialchars($k['foto_profile']) ?>" class="comment-avatar" alt="">
                            <?php else: ?>
                                <div class="comment-avatar-placeholder"><?= strtoupper(substr($k['username'],0,1)) ?></div>
                            <?php endif; ?>
                            <div style="flex:1">
                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                    <span class="comment-user"><?= htmlspecialchars($k['full_name'] ?: $k['username']) ?></span>
                                    <?php if ($kStars): ?>
                                        <span class="stars" style="font-size:0.78rem;color:var(--gold)"><?= $kStars ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="comment-date"><?= date('d M Y, H:i', strtotime($k['created_at'])) ?></div>
                            </div>
                        </div>
                        <p class="comment-text"><?= nl2br(htmlspecialchars($k['komentar'])) ?></p>
                        <?php if (!empty($k['fotos'])): ?>
                            <div class="comment-photos">
                                <?php foreach ($k['fotos'] as $cf): ?>
                                    <div class="comment-photo" data-lightbox="uploads/<?= htmlspecialchars($cf['foto']) ?>">
                                        <img src="uploads/<?= htmlspecialchars($cf['foto']) ?>" alt="foto komentar">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT SIDEBAR -->
        <div>
            <div class="wisata-info-card">
                <h3 style="font-family:var(--font-display);font-size:1.05rem;font-weight:700;color:var(--gray-900);margin-bottom:4px">Info Wisata</h3>
                <p style="font-size:0.8rem;color:var(--gray-400);margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--gray-100)">Detail & Informasi Kunjungan</p>

                <div class="wisata-info-item">
                    <div class="wisata-info-icon"><i class="fas fa-ticket-alt"></i></div>
                    <div>
                        <div class="wisata-info-label">Harga Tiket</div>
                        <div class="wisata-info-value" style="color:var(--green-600)"><?= $price ?></div>
                    </div>
                </div>

                <?php if ($wisata['jam_buka']): ?>
                <div class="wisata-info-item">
                    <div class="wisata-info-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="wisata-info-label">Jam Operasional</div>
                        <div class="wisata-info-value"><?= substr($wisata['jam_buka'],0,5) ?> – <?= substr($wisata['jam_tutup'],0,5) ?> WITA</div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="wisata-info-item">
                    <div class="wisata-info-icon"><i class="fas fa-star"></i></div>
                    <div>
                        <div class="wisata-info-label">Rating</div>
                        <div class="wisata-info-value">
                            <span style="color:var(--gold)"><?= str_repeat('★', round($avgRating)) ?><?= str_repeat('☆', 5-round($avgRating)) ?></span>
                            <?= $avgRating ?>/5
                        </div>
                    </div>
                </div>

                <div class="wisata-info-item">
                    <div class="wisata-info-icon"><i class="fas fa-tag"></i></div>
                    <div>
                        <div class="wisata-info-label">Kategori</div>
                        <div class="wisata-info-value"><?= htmlspecialchars($wisata['kategori']) ?></div>
                    </div>
                </div>

                <?php if ($wisata['link_lokasi']): ?>
                <div style="margin-top:20px">
                    <a href="<?= htmlspecialchars($wisata['link_lokasi']) ?>" target="_blank" class="btn btn-primary" style="width:100%;justify-content:center">
                        <i class="fas fa-map-marker-alt"></i> Buka di Google Maps
                    </a>
                </div>
                <?php endif; ?>

                <?php if (!isLoggedIn()): ?>
                <div style="margin-top:12px;padding-top:16px;border-top:1px solid var(--gray-100);text-align:center">
                    <p style="font-size:0.83rem;color:var(--gray-500);margin-bottom:10px">Login untuk memberi rating & komentar</p>
                    <a href="user/login.php" class="btn btn-outline btn-sm" style="width:100%;justify-content:center">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Share -->
            <div class="wisata-info-card" style="margin-top:20px">
                <h4 style="font-weight:700;font-size:0.9rem;color:var(--gray-700);margin-bottom:14px">Bagikan Wisata Ini</h4>
                <div style="display:flex;gap:10px">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL.'detail.php?id='.$id) ?>" target="_blank" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:var(--radius-md);background:#1877f2;color:white;font-size:0.82rem;font-weight:600;transition:var(--transition)" onmouseenter="this.style.opacity='0.85'" onmouseleave="this.style.opacity='1'">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://wa.me/?text=<?= urlencode($wisata['nama'].' - '.BASE_URL.'detail.php?id='.$id) ?>" target="_blank" style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:var(--radius-md);background:#25d366;color:white;font-size:0.82rem;font-weight:600;transition:var(--transition)" onmouseenter="this.style.opacity='0.85'" onmouseleave="this.style.opacity='1'">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
    <div class="lightbox-inner">
        <button class="lightbox-close" id="lightboxClose"><i class="fas fa-times"></i></button>
        <img src="" id="lightboxImg" alt="">
    </div>
</div>

<script>
// Fix star rating to update hidden input
document.addEventListener('DOMContentLoaded', function () {
    const starWrap = document.querySelector('.star-rating');
    const ratingInput = document.getElementById('ratingVal');
    if (starWrap && ratingInput) {
        starWrap.querySelectorAll('i').forEach((star, idx) => {
            star.addEventListener('click', () => ratingInput.value = idx + 1);
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
