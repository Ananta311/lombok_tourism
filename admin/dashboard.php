<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();

$totalWisata  = $db->query("SELECT COUNT(*) FROM tempat_wisata")->fetch_row()[0];
$totalUser    = $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetch_row()[0];
$totalKomentar= $db->query("SELECT COUNT(*) FROM komentar")->fetch_row()[0];
$totalFoto    = $db->query("SELECT COUNT(*) FROM wisata_foto")->fetch_row()[0];
$totalHotel   = $db->query("SELECT COUNT(*) FROM hotel")->fetch_row()[0];
$totalRestoran= $db->query("SELECT COUNT(*) FROM restoran")->fetch_row()[0];

$latestWisata = $db->query("
    SELECT tw.*, wf.foto as foto_utama,
           COALESCE(AVG(r.rating), tw.rating_awal) as avg_rating
    FROM tempat_wisata tw
    LEFT JOIN wisata_foto wf ON wf.wisata_id = tw.id AND wf.is_primary=1
    LEFT JOIN rating r ON r.wisata_id = tw.id
    GROUP BY tw.id ORDER BY tw.created_at DESC LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

$latestUsers = $db->query("
    SELECT u.*, p.full_name, p.foto_profile
    FROM users u LEFT JOIN profile p ON p.user_id=u.id
    WHERE u.role='user' ORDER BY u.created_at DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$latestComments = $db->query("
    SELECT k.*, u.username, tw.nama as wisata_nama
    FROM komentar k
    JOIN users u ON u.id=k.user_id
    JOIN tempat_wisata tw ON tw.id=k.wisata_id
    ORDER BY k.created_at DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Dashboard Admin - Lombok Tourism';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <h1 class="admin-page-title">Dashboard</h1>
    <p class="admin-page-subtitle">Selamat datang kembali, <?= htmlspecialchars($_SESSION['username']) ?>! Berikut ringkasan data website.</p>

    <!-- Stat Cards -->
    <div class="stat-cards">
        <div class="stat-card" data-reveal>
            <div class="stat-icon blue"><i class="fas fa-map-marked-alt"></i></div>
            <div>
                <div class="stat-number" data-count="<?= $totalWisata ?>">0</div>
                <div class="stat-label">Total Wisata</div>
            </div>
        </div>
        <div class="stat-card" data-reveal data-delay="1">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-number" data-count="<?= $totalUser ?>">0</div>
                <div class="stat-label">Total User</div>
            </div>
        </div>
        <div class="stat-card" data-reveal data-delay="2">
            <div class="stat-icon gold"><i class="fas fa-comments"></i></div>
            <div>
                <div class="stat-number" data-count="<?= $totalKomentar ?>">0</div>
                <div class="stat-label">Total Komentar</div>
            </div>
        </div>
        <div class="stat-card" data-reveal data-delay="3">
            <div class="stat-icon danger"><i class="fas fa-images"></i></div>
            <div>
                <div class="stat-number" data-count="<?= $totalFoto ?>">0</div>
                <div class="stat-label">Total Foto</div>
            </div>
        </div>
        <div class="stat-card" data-reveal data-delay="4">
            <div class="stat-icon" style="background:var(--cyan-100);color:var(--cyan-700)"><i class="fas fa-hotel"></i></div>
            <div>
                <div class="stat-number" data-count="<?= $totalHotel ?>">0</div>
                <div class="stat-label">Total Hotel</div>
            </div>
        </div>
        <div class="stat-card" data-reveal data-delay="5">
            <div class="stat-icon green"><i class="fas fa-utensils"></i></div>
            <div>
                <div class="stat-number" data-count="<?= $totalRestoran ?>">0</div>
                <div class="stat-label">Total Restoran</div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px">

        <!-- Latest Wisata -->
        <div class="data-table-card" data-reveal>
            <div class="data-table-header">
                <h3><i class="fas fa-map-marker-alt" style="color:var(--blue-400)"></i> Wisata Terbaru</h3>
                <a href="wisata.php" class="btn btn-outline btn-sm">Lihat Semua</a>
            </div>
            <table class="data-table">
                <thead><tr>
                    <th>Foto</th><th>Nama</th><th>Kategori</th><th>Rating</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                <?php foreach ($latestWisata as $w): ?>
                <tr>
                    <td>
                        <?php if ($w['foto_utama']): ?>
                            <img src="../uploads/<?= htmlspecialchars($w['foto_utama']) ?>" class="table-thumb" alt="">
                        <?php else: ?>
                            <div style="width:44px;height:44px;border-radius:var(--radius-sm);background:var(--gray-100);display:flex;align-items:center;justify-content:center;color:var(--gray-400)"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight:600;color:var(--gray-800)"><?= htmlspecialchars($w['nama']) ?></div>
                        <div style="font-size:.75rem;color:var(--gray-400)"><?= date('d M Y', strtotime($w['created_at'])) ?></div>
                    </td>
                    <td><span class="badge badge-blue"><?= htmlspecialchars($w['kategori']) ?></span></td>
                    <td><span style="color:var(--gold)">★</span> <?= round($w['avg_rating'],1) ?></td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="../detail.php?id=<?= $w['id'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                            <a href="edit_wisata.php?id=<?= $w['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                            <a href="delete_wisata.php?id=<?= $w['id'] ?>" class="btn btn-danger btn-sm"
                               data-confirm="Yakin hapus wisata ini?"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Latest Comments -->
        <div class="data-table-card" data-reveal data-delay="1">
            <div class="data-table-header">
                <h3><i class="fas fa-comments" style="color:var(--green-400)"></i> Komentar Terbaru</h3>
                <a href="komentar.php" class="btn btn-outline btn-sm">Semua</a>
            </div>
            <div style="padding:8px 0">
                <?php foreach ($latestComments as $k): ?>
                <div style="padding:14px 20px;border-bottom:1px solid var(--gray-50)">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                        <span style="font-weight:600;font-size:.88rem;color:var(--gray-800)"><?= htmlspecialchars($k['username']) ?></span>
                        <span style="font-size:.75rem;color:var(--gray-400)"><?= date('d/m', strtotime($k['created_at'])) ?></span>
                    </div>
                    <div style="font-size:.78rem;color:var(--blue-500);margin-bottom:4px">
                        <i class="fas fa-map-pin"></i> <?= htmlspecialchars($k['wisata_nama']) ?>
                    </div>
                    <p style="font-size:.82rem;color:var(--gray-600);line-height:1.5">
                        <?= htmlspecialchars(mb_substr($k['komentar'],0,80)) ?>…
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Latest Users -->
    <div class="data-table-card" data-reveal>
        <div class="data-table-header">
            <h3><i class="fas fa-users" style="color:var(--blue-400)"></i> User Terbaru</h3>
            <a href="users.php" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <table class="data-table">
            <thead><tr>
                <th>Avatar</th><th>Username</th><th>Email</th><th>Nama Lengkap</th><th>Bergabung</th>
            </tr></thead>
            <tbody>
            <?php foreach ($latestUsers as $u): ?>
            <tr>
                <td>
                    <?php if (!empty($u['foto_profile'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($u['foto_profile']) ?>" class="table-thumb" style="border-radius:50%" alt="">
                    <?php else: ?>
                        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--blue-400),var(--green-400));display:flex;align-items:center;justify-content:center;color:white;font-weight:700">
                            <?= strtoupper(substr($u['username'],0,1)) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td style="font-weight:600">@<?= htmlspecialchars($u['username']) ?></td>
                <td style="color:var(--gray-500)"><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['full_name'] ?: '-') ?></td>
                <td style="color:var(--gray-400);font-size:.85rem"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
