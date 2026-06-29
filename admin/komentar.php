<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();

// Delete comment
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    // Delete comment photos from disk
    $cPhotos = $db->query("SELECT foto FROM komentar_foto WHERE komentar_id=$delId")->fetch_all(MYSQLI_ASSOC);
    foreach ($cPhotos as $cp) {
        $path = UPLOAD_DIR . $cp['foto'];
        if (file_exists($path)) unlink($path);
    }
    $db->query("DELETE FROM komentar WHERE id=$delId");
    header('Location: komentar.php?msg=deleted');
    exit;
}

$q = sanitize($_GET['q'] ?? '');
$where = $q ? "WHERE (u.username LIKE '%".addslashes($q)."%' OR k.komentar LIKE '%".addslashes($q)."%' OR tw.nama LIKE '%".addslashes($q)."%')" : '';

$komentar = $db->query("
    SELECT k.*, u.username, tw.nama as wisata_nama, tw.id as wisata_id,
           COUNT(kf.id) as foto_count
    FROM komentar k
    JOIN users u ON u.id = k.user_id
    JOIN tempat_wisata tw ON tw.id = k.wisata_id
    LEFT JOIN komentar_foto kf ON kf.komentar_id = k.id
    $where
    GROUP BY k.id
    ORDER BY k.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Kelola Komentar - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 class="admin-page-title">Kelola Komentar</h1>
            <p class="admin-page-subtitle"><?= count($komentar) ?> komentar total</p>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg']==='deleted'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Komentar berhasil dihapus.</div>
    <?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header">
            <h3><i class="fas fa-comments" style="color:var(--green-400)"></i> Semua Komentar</h3>
            <form method="GET" style="display:flex;gap:8px">
                <div class="search-bar" style="min-width:260px">
                    <i class="fas fa-search" style="color:var(--gray-400)"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari komentar...">
                    <button type="submit">Cari</button>
                </div>
                <?php if ($q): ?><a href="komentar.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a><?php endif; ?>
            </form>
        </div>
        <div style="overflow-x:auto">
            <table class="data-table">
                <thead><tr>
                    <th>User</th><th>Wisata</th><th>Komentar</th><th>Foto</th><th>Tanggal</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                <?php foreach ($komentar as $k): ?>
                <tr>
                    <td>
                        <div style="font-weight:600;color:var(--gray-800)">@<?= htmlspecialchars($k['username']) ?></div>
                    </td>
                    <td>
                        <a href="../detail.php?id=<?= $k['wisata_id'] ?>" target="_blank"
                           style="color:var(--blue-600);font-weight:600;font-size:.88rem">
                            <?= htmlspecialchars($k['wisata_nama']) ?>
                        </a>
                    </td>
                    <td style="max-width:280px">
                        <p style="font-size:.88rem;color:var(--gray-700);line-height:1.5">
                            <?= htmlspecialchars(mb_substr($k['komentar'], 0, 120)) ?>
                            <?= mb_strlen($k['komentar'])>120 ? '…' : '' ?>
                        </p>
                    </td>
                    <td>
                        <?php if ($k['foto_count']): ?>
                            <span class="badge badge-blue"><i class="fas fa-camera"></i> <?= $k['foto_count'] ?></span>
                        <?php else: ?>
                            <span style="color:var(--gray-300);font-size:.82rem">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.82rem;color:var(--gray-400);white-space:nowrap">
                        <?= date('d M Y H:i', strtotime($k['created_at'])) ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px">
                            <a href="../detail.php?id=<?= $k['wisata_id'] ?>" target="_blank" class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="komentar.php?delete=<?= $k['id'] ?>" class="btn btn-danger btn-sm"
                               data-confirm="Hapus komentar ini?">
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
