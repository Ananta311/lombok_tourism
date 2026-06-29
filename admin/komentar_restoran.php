<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();

if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    $db->query("DELETE FROM restoran_rating WHERE id=$delId");
    header('Location: komentar_restoran.php?msg=deleted');
    exit;
}

$ulasan = $db->query("
    SELECT rr.*, u.username, r.nama_restoran, r.id as restoran_id
    FROM restoran_rating rr
    JOIN users u ON u.id = rr.user_id
    JOIN restoran r ON r.id = rr.restoran_id
    ORDER BY rr.tanggal DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Kelola Komentar Restoran - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <div style="margin-bottom:24px">
        <h1 class="admin-page-title">Kelola Ulasan Restoran</h1>
        <p class="admin-page-subtitle"><?= count($ulasan) ?> ulasan (rating + komentar) dari pengguna</p>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg']==='deleted'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Ulasan berhasil dihapus.</div>
    <?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header">
            <h3><i class="fas fa-star" style="color:var(--gold)"></i> Semua Ulasan Restoran</h3>
        </div>
        <table class="data-table">
            <thead><tr>
                <th>User</th><th>Restoran</th><th>Rating</th><th>Komentar</th><th>Tanggal</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php foreach ($ulasan as $u): ?>
            <tr>
                <td style="font-weight:600">@<?= htmlspecialchars($u['username']) ?></td>
                <td>
                    <a href="../restoran_detail.php?id=<?= $u['restoran_id'] ?>" target="_blank" style="color:var(--green-700);font-weight:600">
                        <?= htmlspecialchars($u['nama_restoran']) ?>
                    </a>
                </td>
                <td>
                    <span style="color:var(--gold)"><?= str_repeat('★',$u['rating']) ?><?= str_repeat('☆',5-$u['rating']) ?></span>
                </td>
                <td style="max-width:280px;font-size:.85rem;color:var(--gray-600)">
                    <?= $u['komentar'] ? htmlspecialchars(mb_substr($u['komentar'],0,100)).(mb_strlen($u['komentar'])>100?'…':'') : '<span style="color:var(--gray-300)">— tanpa komentar —</span>' ?>
                </td>
                <td style="font-size:.82rem;color:var(--gray-400)"><?= date('d M Y', strtotime($u['tanggal'])) ?></td>
                <td>
                    <a href="komentar_restoran.php?delete=<?= $u['id'] ?>" class="btn btn-danger btn-sm" data-confirm="Hapus ulasan ini?">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($ulasan)): ?>
                <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--gray-400)">Belum ada ulasan restoran.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
