<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$q = sanitize($_GET['q'] ?? '');
$where = $q ? "WHERE r.nama_restoran LIKE '%".addslashes($q)."%' OR r.lokasi LIKE '%".addslashes($q)."%'" : '';

$restorans = $db->query("
    SELECT r.*, tw.nama as wisata_nama,
           COALESCE(AVG(rr.rating),0) as avg_rating,
           COUNT(DISTINCT rr.id) as total_rating
    FROM restoran r
    LEFT JOIN tempat_wisata tw ON tw.id = r.wisata_terdekat_id
    LEFT JOIN restoran_rating rr ON rr.restoran_id = r.id
    $where
    GROUP BY r.id
    ORDER BY r.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Kelola Restoran - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 class="admin-page-title">Kelola Restoran & Tempat Makan</h1>
            <p class="admin-page-subtitle"><?= count($restorans) ?> restoran terdaftar</p>
        </div>
        <a href="add_restoran.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Restoran
        </a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg']==='added'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Restoran baru berhasil ditambahkan!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg']==='updated'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Restoran berhasil diperbarui!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg']==='deleted'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Restoran berhasil dihapus.</div>
    <?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header">
            <h3><i class="fas fa-utensils" style="color:var(--green-600)"></i> Semua Restoran</h3>
            <form method="GET" style="display:flex;gap:8px">
                <div class="search-bar" style="min-width:260px">
                    <i class="fas fa-search" style="color:var(--gray-400)"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama / lokasi...">
                    <button type="submit">Cari</button>
                </div>
                <?php if ($q): ?><a href="restoran.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a><?php endif; ?>
            </form>
        </div>
        <div style="overflow-x:auto">
            <table class="data-table">
                <thead><tr>
                    <th>Foto</th><th>Nama Restoran</th><th>Lokasi</th><th>Harga Rata-rata</th>
                    <th>Rating</th><th>Wisata Terdekat</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                <?php foreach ($restorans as $r): ?>
                <tr>
                    <td>
                        <?php if ($r['foto']): ?>
                            <img src="../uploads/<?= htmlspecialchars($r['foto']) ?>" class="table-thumb" alt="">
                        <?php else: ?>
                            <div style="width:44px;height:44px;border-radius:var(--radius-sm);background:var(--green-100);display:flex;align-items:center;justify-content:center;color:var(--green-500)">
                                <i class="fas fa-utensils"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight:600;color:var(--gray-800);max-width:180px"><?= htmlspecialchars($r['nama_restoran']) ?></div>
                        <div style="font-size:.75rem;color:var(--gray-400)"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
                    </td>
                    <td><span class="badge badge-green"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($r['lokasi']) ?></span></td>
                    <td style="font-weight:600;color:var(--green-600)">
                        Rp <?= number_format($r['harga_rata_rata'],0,',','.') ?>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:4px">
                            <span style="color:var(--gold)">★</span>
                            <span style="font-weight:600"><?= round($r['avg_rating'],1) ?></span>
                            <span style="font-size:.75rem;color:var(--gray-400)">(<?= $r['total_rating'] ?>)</span>
                        </div>
                    </td>
                    <td style="font-size:.85rem;color:var(--gray-500)">
                        <?= $r['wisata_nama'] ? htmlspecialchars($r['wisata_nama']) : '—' ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <a href="../restoran_detail.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                            <a href="edit_restoran.php?id=<?= $r['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                            <a href="delete_restoran.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                               data-confirm="Hapus restoran '<?= htmlspecialchars($r['nama_restoran']) ?>'? Semua ulasan ikut terhapus.">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($restorans)): ?>
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--gray-400)">Belum ada data restoran.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
