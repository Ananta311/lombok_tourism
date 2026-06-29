<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$q = sanitize($_GET['q'] ?? '');
$where = $q ? "WHERE h.nama_hotel LIKE '%".addslashes($q)."%' OR h.lokasi LIKE '%".addslashes($q)."%'" : '';

$hotels = $db->query("
    SELECT h.*, tw.nama as wisata_nama,
           COALESCE(AVG(hr.rating),0) as avg_rating,
           COUNT(DISTINCT hr.id) as total_rating
    FROM hotel h
    LEFT JOIN tempat_wisata tw ON tw.id = h.wisata_terdekat_id
    LEFT JOIN hotel_rating hr ON hr.hotel_id = h.id
    $where
    GROUP BY h.id
    ORDER BY h.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Kelola Hotel - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 class="admin-page-title">Kelola Hotel & Penginapan</h1>
            <p class="admin-page-subtitle"><?= count($hotels) ?> hotel terdaftar</p>
        </div>
        <a href="add_hotel.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Hotel
        </a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg']==='added'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Hotel baru berhasil ditambahkan!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg']==='updated'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Hotel berhasil diperbarui!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg']==='deleted'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Hotel berhasil dihapus.</div>
    <?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header">
            <h3><i class="fas fa-hotel" style="color:var(--cyan-600)"></i> Semua Hotel</h3>
            <form method="GET" style="display:flex;gap:8px">
                <div class="search-bar" style="min-width:260px">
                    <i class="fas fa-search" style="color:var(--gray-400)"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari nama / lokasi...">
                    <button type="submit">Cari</button>
                </div>
                <?php if ($q): ?><a href="hotel.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a><?php endif; ?>
            </form>
        </div>
        <div style="overflow-x:auto">
            <table class="data-table">
                <thead><tr>
                    <th>Foto</th><th>Nama Hotel</th><th>Lokasi</th><th>Harga/Malam</th>
                    <th>Rating</th><th>Wisata Terdekat</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                <?php foreach ($hotels as $h): ?>
                <tr>
                    <td>
                        <?php if ($h['foto']): ?>
                            <img src="../uploads/<?= htmlspecialchars($h['foto']) ?>" class="table-thumb" alt="">
                        <?php else: ?>
                            <div style="width:44px;height:44px;border-radius:var(--radius-sm);background:var(--cyan-100);display:flex;align-items:center;justify-content:center;color:var(--cyan-500)">
                                <i class="fas fa-hotel"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight:600;color:var(--gray-800);max-width:180px"><?= htmlspecialchars($h['nama_hotel']) ?></div>
                        <div style="font-size:.75rem;color:var(--gray-400)"><?= date('d M Y', strtotime($h['created_at'])) ?></div>
                    </td>
                    <td><span class="badge badge-cyan"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($h['lokasi']) ?></span></td>
                    <td style="font-weight:600;color:var(--green-600)">
                        Rp <?= number_format($h['harga_per_malam'],0,',','.') ?>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:4px">
                            <span style="color:var(--gold)">★</span>
                            <span style="font-weight:600"><?= round($h['avg_rating'],1) ?></span>
                            <span style="font-size:.75rem;color:var(--gray-400)">(<?= $h['total_rating'] ?>)</span>
                        </div>
                    </td>
                    <td style="font-size:.85rem;color:var(--gray-500)">
                        <?= $h['wisata_nama'] ? htmlspecialchars($h['wisata_nama']) : '—' ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <a href="../hotel_detail.php?id=<?= $h['id'] ?>" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                            <a href="edit_hotel.php?id=<?= $h['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                            <a href="delete_hotel.php?id=<?= $h['id'] ?>" class="btn btn-danger btn-sm"
                               data-confirm="Hapus hotel '<?= htmlspecialchars($h['nama_hotel']) ?>'? Semua ulasan ikut terhapus.">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($hotels)): ?>
                <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--gray-400)">Belum ada data hotel.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
