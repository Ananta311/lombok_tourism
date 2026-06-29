<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$q = sanitize($_GET['q'] ?? '');

$where = $q ? "WHERE tw.nama LIKE '%".addslashes($q)."%' OR tw.kategori LIKE '%".addslashes($q)."%'" : '';

$wisataList = $db->query("
    SELECT tw.*,
           wf.foto as foto_utama,
           COALESCE(AVG(r.rating), tw.rating_awal) as avg_rating,
           COUNT(DISTINCT r.id) as total_rating,
           COUNT(DISTINCT k.id) as total_komentar,
           COUNT(DISTINCT wf2.id) as total_foto
    FROM tempat_wisata tw
    LEFT JOIN wisata_foto wf  ON wf.wisata_id  = tw.id AND wf.is_primary=1
    LEFT JOIN wisata_foto wf2 ON wf2.wisata_id = tw.id
    LEFT JOIN rating r        ON r.wisata_id   = tw.id
    LEFT JOIN komentar k      ON k.wisata_id   = tw.id
    $where
    GROUP BY tw.id
    ORDER BY tw.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Kelola Wisata - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 class="admin-page-title">Kelola Wisata</h1>
            <p class="admin-page-subtitle"><?= count($wisataList) ?> destinasi wisata terdaftar</p>
        </div>
        <a href="add_wisata.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Wisata
        </a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg']==='added'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Wisata baru berhasil ditambahkan!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg']==='deleted'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Wisata berhasil dihapus.</div>
    <?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header">
            <h3><i class="fas fa-map-marked-alt" style="color:var(--blue-400)"></i> Semua Wisata</h3>
            <form method="GET" style="display:flex;gap:8px">
                <div class="search-bar" style="min-width:260px">
                    <i class="fas fa-search" style="color:var(--gray-400)"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama wisata...">
                    <button type="submit">Cari</button>
                </div>
                <?php if ($q): ?>
                    <a href="wisata.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </form>
        </div>
        <div style="overflow-x:auto">
            <table class="data-table">
                <thead><tr>
                    <th>Foto</th>
                    <th>Nama Wisata</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Rating</th>
                    <th>Foto</th>
                    <th>Komentar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr></thead>
                <tbody>
                <?php foreach ($wisataList as $w): ?>
                <tr>
                    <td>
                        <?php if ($w['foto_utama']): ?>
                            <img src="../uploads/<?= htmlspecialchars($w['foto_utama']) ?>" class="table-thumb" alt="">
                        <?php else: ?>
                            <div style="width:44px;height:44px;border-radius:var(--radius-sm);background:var(--gray-100);display:flex;align-items:center;justify-content:center;color:var(--gray-300);font-size:1.2rem">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight:600;color:var(--gray-800);max-width:180px"><?= htmlspecialchars($w['nama']) ?></div>
                        <div style="font-size:.75rem;color:var(--gray-400)"><?= date('d M Y', strtotime($w['created_at'])) ?></div>
                    </td>
                    <td><span class="badge badge-blue"><?= htmlspecialchars($w['kategori']) ?></span></td>
                    <td style="font-weight:600;color:var(--green-600)">
                        <?= $w['harga_tiket'] > 0 ? 'Rp '.number_format($w['harga_tiket'],0,',','.') : 'Gratis' ?>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:4px">
                            <span style="color:var(--gold)">★</span>
                            <span style="font-weight:600"><?= round($w['avg_rating'],1) ?></span>
                            <span style="font-size:.75rem;color:var(--gray-400)">(<?= $w['total_rating'] ?>)</span>
                        </div>
                    </td>
                    <td><span class="badge badge-gray"><i class="fas fa-images"></i> <?= $w['total_foto'] ?></span></td>
                    <td><span class="badge badge-gray"><i class="fas fa-comment"></i> <?= $w['total_komentar'] ?></span></td>
                    <td>
                        <?php if ($w['is_featured']): ?>
                            <span class="badge badge-gold"><i class="fas fa-star"></i> Unggulan</span>
                        <?php else: ?>
                            <span class="badge badge-gray">Biasa</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <a href="../detail.php?id=<?= $w['id'] ?>" target="_blank"
                               class="btn btn-outline btn-sm" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit_wisata.php?id=<?= $w['id'] ?>"
                               class="btn btn-primary btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete_wisata.php?id=<?= $w['id'] ?>"
                               class="btn btn-danger btn-sm"
                               data-confirm="Hapus wisata '<?= htmlspecialchars($w['nama']) ?>'? Semua foto dan komentar juga akan terhapus." title="Hapus">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
