<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
$db = getDB();

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: hotel.php'); exit; }

$hotel = $db->query("
    SELECT h.*, tw.nama as wisata_nama, tw.id as wisata_id
    FROM hotel h LEFT JOIN tempat_wisata tw ON tw.id = h.wisata_terdekat_id
    WHERE h.id = $id
")->fetch_assoc();
if (!$hotel) { header('Location: hotel.php'); exit; }

// Galeri foto (pola sama dengan modul Wisata)
$photos = $db->query("SELECT * FROM hotel_foto WHERE hotel_id = $id ORDER BY is_primary DESC, id ASC")->fetch_all(MYSQLI_ASSOC);

$msg = '';

/* Submit rating + komentar (1 form gabungan, upsert) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'review') {
    if (!isLoggedIn()) {
        header('Location: user/login.php?redirect=' . urlencode("hotel_detail.php?id=$id"));
        exit;
    }
    $rating   = intval($_POST['rating'] ?? 0);
    $komentar = sanitize($_POST['komentar'] ?? '');
    $uid      = intval($_SESSION['user_id']);

    if ($rating < 1 || $rating > 5) {
        $msg = ['type'=>'danger','text'=>'Pilih rating bintang terlebih dahulu.'];
    } else {
        $stmt = $db->prepare("
            INSERT INTO hotel_rating (hotel_id, user_id, rating, komentar) VALUES (?,?,?,?)
            ON DUPLICATE KEY UPDATE rating=?, komentar=?, updated_at=CURRENT_TIMESTAMP
        ");
        $stmt->bind_param("iiisis", $id, $uid, $rating, $komentar, $rating, $komentar);
        $stmt->execute();
        $msg = ['type'=>'success','text'=>'Ulasan berhasil disimpan. Terima kasih!'];
    }
    header("Location: hotel_detail.php?id=$id&msg=" . urlencode(json_encode($msg)));
    exit;
}
if (isset($_GET['msg'])) $msg = json_decode(urldecode($_GET['msg']), true);

/* Rating overview */
$ratingData  = getAvgHotelRating($id);
$avgRating   = round($ratingData['avg_r'] ?? 0, 1);
$totalRating = $ratingData['total'];

$userReview = null;
if (isLoggedIn()) {
    $uid = intval($_SESSION['user_id']);
    $userReview = $db->query("SELECT * FROM hotel_rating WHERE hotel_id=$id AND user_id=$uid")->fetch_assoc();
}

/* Daftar ulasan */
$ulasanList = $db->query("
    SELECT hr.*, u.username, p.full_name, p.foto_profile
    FROM hotel_rating hr
    JOIN users u ON u.id = hr.user_id
    LEFT JOIN profile p ON p.user_id = hr.user_id
    WHERE hr.hotel_id = $id
    ORDER BY hr.tanggal DESC
")->fetch_all(MYSQLI_ASSOC);

/* Hotel lain dekat wisata yang sama (rekomendasi) */
$relatedHotels = [];
if ($hotel['wisata_terdekat_id']) {
    $relatedHotels = $db->query("
        SELECT h.*, COALESCE(AVG(hr.rating),0) as avg_rating
        FROM hotel h LEFT JOIN hotel_rating hr ON hr.hotel_id = h.id
        WHERE h.wisata_terdekat_id = {$hotel['wisata_terdekat_id']} AND h.id != $id
        GROUP BY h.id LIMIT 4
    ")->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = htmlspecialchars($hotel['nama_hotel']) . ' - Lombok Tourism';
include __DIR__ . '/includes/header.php';
$stars = renderStars($avgRating);
$mapsLink = $hotel['link_lokasi'] ?? '';
?>

<div style="padding-top:72px; min-height:100vh; background:var(--gray-50)">

<div class="lodging-hero">
    <?php $mainPhoto = $photos[0] ?? null; ?>
    <?php if ($mainPhoto): ?>
        <img src="uploads/<?= htmlspecialchars($mainPhoto['foto']) ?>" alt="<?= htmlspecialchars($hotel['nama_hotel']) ?>">
    <?php else: ?>
        <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--cyan-700),var(--blue-700));display:flex;align-items:center;justify-content:center;font-size:5rem;color:rgba(255,255,255,.3)"><i class="fas fa-hotel"></i></div>
    <?php endif; ?>
    <div class="lodging-hero-info">
        <div class="container">
            <div style="font-size:0.82rem;color:rgba(255,255,255,0.7);margin-bottom:10px">
                <a href="index.php" style="color:inherit">Beranda</a> ›
                <a href="hotel.php" style="color:inherit">Hotel</a> ›
                <?= htmlspecialchars($hotel['nama_hotel']) ?>
            </div>
            <span style="background:var(--cyan-500);color:white;padding:4px 14px;border-radius:999px;font-size:0.78rem;font-weight:600">
                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($hotel['lokasi']) ?>
            </span>
            <h1 style="font-family:var(--font-display);font-size:clamp(1.6rem,4vw,2.6rem);font-weight:900;margin:10px 0 8px">
                <?= htmlspecialchars($hotel['nama_hotel']) ?>
            </h1>
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;font-size:0.9rem">
                <span style="color:var(--gold)"><?= $stars ?></span>
                <span><?= $avgRating ?> / 5 (<?= $totalRating ?> ulasan)</span>
            </div>
        </div>
    </div>
</div>

<div class="container" style="padding:40px 24px">
    <div style="display:grid;grid-template-columns:1fr 340px;gap:40px;align-items:start">

        <div>
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
                    <i class="fas fa-images" style="color:var(--cyan-600)"></i> Galeri Foto
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

            <div class="form-card" style="padding:28px;margin-bottom:28px">
                <h3 style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;margin-bottom:14px">
                    <i class="fas fa-info-circle" style="color:var(--cyan-600)"></i> Tentang Hotel Ini
                </h3>
                <?php if ($hotel['alamat']): ?>
                    <p style="font-size:.88rem;color:var(--gray-500);margin-bottom:12px">
                        <i class="fas fa-map-marker-alt" style="color:var(--cyan-500)"></i> <?= htmlspecialchars($hotel['alamat']) ?>
                    </p>
                <?php endif; ?>
                <p style="color:var(--gray-700);line-height:1.85;font-size:0.95rem"><?= nl2br(htmlspecialchars($hotel['deskripsi'] ?: 'Belum ada deskripsi.')) ?></p>
            </div>

            <!-- Lokasi -->
            <?php if (hasMapsLink($mapsLink)): ?>
            <div class="form-card" style="padding:28px;margin-bottom:28px">
                <h3 style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;margin-bottom:14px">
                    <i class="fas fa-map-marked-alt" style="color:var(--cyan-600)"></i> Lokasi
                </h3>
                <p style="font-size:.9rem;color:var(--gray-500);margin-bottom:16px">
                    Klik tombol di bawah untuk membuka lokasi hotel langsung di Google Maps.
                </p>
                <a href="<?= htmlspecialchars($mapsLink) ?>" target="_blank" class="btn btn-primary" style="width:100%;justify-content:center">
                    <i class="fas fa-location-arrow"></i> Buka di Google Maps
                </a>
            </div>
            <?php endif; ?>

            <!-- Rating & Review -->
            <div class="form-card" style="padding:28px;margin-bottom:28px">
                <h3 style="font-family:var(--font-display);font-size:1.1rem;font-weight:700;margin-bottom:20px">
                    <i class="fas fa-star" style="color:var(--gold)"></i> Rating & Ulasan
                </h3>

                <div style="display:flex;align-items:center;gap:32px;padding:20px;background:var(--gray-50);border-radius:var(--radius-md);margin-bottom:24px;flex-wrap:wrap">
                    <div style="text-align:center">
                        <div style="font-family:var(--font-display);font-size:3.5rem;font-weight:900;color:var(--gray-900);line-height:1"><?= $avgRating ?></div>
                        <div style="color:var(--gold);font-size:1.2rem;margin:4px 0"><?= $stars ?></div>
                        <div style="font-size:0.8rem;color:var(--gray-400)"><?= $totalRating ?> ulasan</div>
                    </div>
                    <div style="flex:1;min-width:200px">
                        <?php for ($s=5;$s>=1;$s--):
                            $cnt = $db->query("SELECT COUNT(*) FROM hotel_rating WHERE hotel_id=$id AND rating=$s")->fetch_row()[0];
                            $pct = $totalRating>0 ? ($cnt/$totalRating*100) : 0;
                        ?>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;font-size:0.82rem">
                            <span style="width:16px;text-align:right;color:var(--gray-500)"><?= $s ?></span>
                            <i class="fas fa-star" style="color:var(--gold);font-size:0.78rem"></i>
                            <div style="flex:1;height:8px;background:var(--gray-200);border-radius:4px;overflow:hidden">
                                <div style="height:100%;width:<?= $pct ?>%;background:var(--gold);border-radius:4px"></div>
                            </div>
                            <span style="width:24px;color:var(--gray-400)"><?= $cnt ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <?php if (isLoggedIn()): ?>
                <form method="POST" style="background:var(--cyan-100);padding:20px;border-radius:var(--radius-md)">
                    <input type="hidden" name="action" value="review">
                    <p style="font-weight:600;color:var(--gray-700);margin-bottom:12px">
                        <?= $userReview ? 'Perbarui ulasan Anda:' : 'Beri Rating & Ulasan:' ?>
                    </p>
                    <div class="star-rating" style="margin-bottom:14px">
                        <?php for ($s=1;$s<=5;$s++): ?>
                            <i class="fas fa-star <?= ($userReview && $userReview['rating']>=$s) ? 'active':'' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="ratingVal" value="<?= $userReview['rating'] ?? 0 ?>">
                    <textarea name="komentar" class="form-control" rows="3" placeholder="Bagikan pengalaman menginap Anda... (opsional)" style="margin-bottom:12px"><?= htmlspecialchars($userReview['komentar'] ?? '') ?></textarea>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-check"></i> Simpan Ulasan
                    </button>
                </form>
                <?php else: ?>
                <div style="background:var(--cyan-100);padding:16px;border-radius:var(--radius-md);text-align:center">
                    <p style="color:var(--gray-600);margin-bottom:10px">Login untuk memberikan rating & ulasan</p>
                    <a href="user/login.php?redirect=<?= urlencode("hotel_detail.php?id=$id") ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-sign-in-alt"></i> Masuk
                    </a>
                </div>
                <?php endif; ?>

                <!-- Daftar ulasan -->
                <div style="margin-top:24px">
                    <?php if (empty($ulasanList)): ?>
                        <div style="text-align:center;padding:30px;color:var(--gray-400)">
                            <i class="fas fa-comment-slash" style="font-size:2rem;display:block;margin-bottom:10px"></i>
                            Belum ada ulasan. Jadilah yang pertama!
                        </div>
                    <?php else: ?>
                        <?php foreach ($ulasanList as $u): ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <?php if (!empty($u['foto_profile'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($u['foto_profile']) ?>" class="comment-avatar" alt="">
                                <?php else: ?>
                                    <div class="comment-avatar-placeholder"><?= strtoupper(substr($u['username'],0,1)) ?></div>
                                <?php endif; ?>
                                <div style="flex:1">
                                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                        <span class="comment-user"><?= htmlspecialchars($u['full_name'] ?: $u['username']) ?></span>
                                        <span class="stars" style="font-size:.78rem;color:var(--gold)"><?= str_repeat('★',$u['rating']) ?><?= str_repeat('☆',5-$u['rating']) ?></span>
                                    </div>
                                    <div class="comment-date"><?= date('d M Y', strtotime($u['tanggal'])) ?></div>
                                </div>
                            </div>
                            <?php if ($u['komentar']): ?>
                                <p class="comment-text"><?= nl2br(htmlspecialchars($u['komentar'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="wisata-info-card">
                <h3 style="font-family:var(--font-display);font-size:1.05rem;font-weight:700;color:var(--gray-900);margin-bottom:4px">Info Hotel</h3>
                <p style="font-size:0.8rem;color:var(--gray-400);margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--gray-100)">Detail penginapan</p>

                <div class="wisata-info-item">
                    <div class="wisata-info-icon" style="background:var(--cyan-100);color:var(--cyan-600)"><i class="fas fa-tag"></i></div>
                    <div>
                        <div class="wisata-info-label">Harga per Malam</div>
                        <div class="wisata-info-value" style="color:var(--green-600)">Rp <?= number_format($hotel['harga_per_malam'],0,',','.') ?></div>
                    </div>
                </div>
                <div class="wisata-info-item">
                    <div class="wisata-info-icon" style="background:var(--cyan-100);color:var(--cyan-600)"><i class="fas fa-star"></i></div>
                    <div>
                        <div class="wisata-info-label">Rating</div>
                        <div class="wisata-info-value"><span style="color:var(--gold)"><?= $stars ?></span> <?= $avgRating ?>/5</div>
                    </div>
                </div>
                <?php if ($hotel['wisata_nama']): ?>
                <div class="wisata-info-item">
                    <div class="wisata-info-icon" style="background:var(--cyan-100);color:var(--cyan-600)"><i class="fas fa-map-pin"></i></div>
                    <div>
                        <div class="wisata-info-label">Wisata Terdekat</div>
                        <div class="wisata-info-value">
                            <a href="detail.php?id=<?= $hotel['wisata_id'] ?>" style="color:var(--blue-600)"><?= htmlspecialchars($hotel['wisata_nama']) ?></a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($mapsLink): ?>
                <div style="margin-top:20px">
                    <a href="<?= htmlspecialchars($mapsLink) ?>" target="_blank" class="btn btn-primary" style="width:100%;justify-content:center">
                        <i class="fas fa-map-marker-alt"></i> Buka di Google Maps
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($relatedHotels)): ?>
            <div class="wisata-info-card" style="margin-top:20px">
                <h4 style="font-weight:700;font-size:.9rem;color:var(--gray-700);margin-bottom:14px">Hotel Lain di Sekitar Sini</h4>
                <?php foreach ($relatedHotels as $rh): ?>
                <a href="hotel_detail.php?id=<?= $rh['id'] ?>" style="display:flex;gap:10px;align-items:center;padding:10px 0;border-bottom:1px solid var(--gray-100);text-decoration:none">
                    <?php if ($rh['foto']): ?>
                        <img src="uploads/<?= htmlspecialchars($rh['foto']) ?>" style="width:48px;height:48px;border-radius:8px;object-fit:cover">
                    <?php else: ?>
                        <div style="width:48px;height:48px;border-radius:8px;background:var(--cyan-100);display:flex;align-items:center;justify-content:center;color:var(--cyan-500)"><i class="fas fa-hotel"></i></div>
                    <?php endif; ?>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:.82rem;font-weight:600;color:var(--gray-800);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($rh['nama_hotel']) ?></div>
                        <div style="font-size:.76rem;color:var(--green-600)">Rp <?= number_format($rh['harga_per_malam'],0,',','.') ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
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
document.addEventListener('DOMContentLoaded', function () {
    const starWrap = document.querySelector('.star-rating');
    const ratingInput = document.getElementById('ratingVal');
    if (starWrap && ratingInput) {
        starWrap.querySelectorAll('i').forEach((star, idx) => {
            star.addEventListener('click', () => {
                ratingInput.value = idx + 1;
                starWrap.querySelectorAll('i').forEach((s,i) => s.classList.toggle('active', i<=idx));
            });
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>