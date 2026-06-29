<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();

if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    $db->query("DELETE FROM rating WHERE id=$delId");
    header('Location: rating.php?msg=deleted');
    exit;
}

$ratings = $db->query("
    SELECT r.*, u.username, tw.nama as wisata_nama, tw.id as wisata_id
    FROM rating r
    JOIN users u ON u.id = r.user_id
    JOIN tempat_wisata tw ON tw.id = r.wisata_id
    ORDER BY r.updated_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Kelola Rating - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <div style="margin-bottom:24px">
        <h1 class="admin-page-title">Kelola Rating</h1>
        <p class="admin-page-subtitle"><?= count($ratings) ?> rating diberikan</p>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg']==='deleted'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Rating berhasil dihapus.</div>
    <?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header">
            <h3><i class="fas fa-star" style="color:var(--gold)"></i> Semua Rating</h3>
        </div>
        <table class="data-table">
            <thead><tr>
                <th>User</th><th>Wisata</th><th>Rating</th><th>Tanggal</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php foreach ($ratings as $r): ?>
            <tr>
                <td style="font-weight:600">@<?= htmlspecialchars($r['username']) ?></td>
                <td>
                    <a href="../detail.php?id=<?= $r['wisata_id'] ?>" target="_blank" style="color:var(--blue-600);font-weight:600">
                        <?= htmlspecialchars($r['wisata_nama']) ?>
                    </a>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px">
                        <span style="color:var(--gold);font-size:1rem"><?= str_repeat('★',$r['rating']) ?><?= str_repeat('☆',5-$r['rating']) ?></span>
                        <strong><?= $r['rating'] ?>/5</strong>
                    </div>
                </td>
                <td style="font-size:.82rem;color:var(--gray-400)"><?= date('d M Y', strtotime($r['updated_at'])) ?></td>
                <td>
                    <a href="rating.php?delete=<?= $r['id'] ?>" class="btn btn-danger btn-sm"
                       data-confirm="Hapus rating ini?">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
