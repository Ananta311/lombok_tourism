<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();

// Delete user
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    if ($delId !== intval($_SESSION['user_id'])) {
        // Delete profile photo
        $pf = $db->query("SELECT foto_profile FROM profile WHERE user_id=$delId")->fetch_assoc();
        if ($pf && $pf['foto_profile']) {
            $path = UPLOAD_DIR . $pf['foto_profile'];
            if (file_exists($path)) unlink($path);
        }
        $db->query("DELETE FROM users WHERE id=$delId");
        header('Location: users.php?msg=deleted');
        exit;
    }
}

$q = sanitize($_GET['q'] ?? '');
$where = $q ? "WHERE (u.username LIKE '%".addslashes($q)."%' OR u.email LIKE '%".addslashes($q)."%' OR p.full_name LIKE '%".addslashes($q)."%')" : '';

$users = $db->query("
    SELECT u.*, p.full_name, p.foto_profile, p.location, p.phone,
           COUNT(DISTINCT k.id) as total_komentar,
           COUNT(DISTINCT r.id) as total_rating
    FROM users u
    LEFT JOIN profile p  ON p.user_id  = u.id
    LEFT JOIN komentar k ON k.user_id  = u.id
    LEFT JOIN rating r   ON r.user_id  = u.id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Kelola User - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div>
            <h1 class="admin-page-title">Kelola User</h1>
            <p class="admin-page-subtitle"><?= count($users) ?> pengguna terdaftar</p>
        </div>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg']==='deleted'): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> User berhasil dihapus.</div>
    <?php endif; ?>

    <div class="data-table-card">
        <div class="data-table-header">
            <h3><i class="fas fa-users" style="color:var(--blue-400)"></i> Semua User</h3>
            <form method="GET" style="display:flex;gap:8px">
                <div class="search-bar" style="min-width:260px">
                    <i class="fas fa-search" style="color:var(--gray-400)"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari username, email...">
                    <button type="submit">Cari</button>
                </div>
                <?php if ($q): ?><a href="users.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a><?php endif; ?>
            </form>
        </div>
        <div style="overflow-x:auto">
            <table class="data-table">
                <thead><tr>
                    <th>Avatar</th><th>Username</th><th>Email</th><th>Nama</th>
                    <th>Role</th><th>Komentar</th><th>Rating</th><th>Bergabung</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <?php if (!empty($u['foto_profile'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($u['foto_profile']) ?>" class="table-thumb" style="border-radius:50%" alt="">
                        <?php else: ?>
                            <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--blue-300),var(--green-300));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:1rem">
                                <?= strtoupper(substr($u['username'],0,1)) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:700;color:var(--gray-800)">@<?= htmlspecialchars($u['username']) ?></td>
                    <td style="color:var(--gray-500);font-size:.88rem"><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['full_name'] ?: '—') ?></td>
                    <td>
                        <span class="badge <?= $u['role']==='admin'?'badge-gold':'badge-blue' ?>">
                            <i class="fas fa-<?= $u['role']==='admin'?'crown':'user' ?>"></i>
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td><span class="badge badge-gray"><?= $u['total_komentar'] ?></span></td>
                    <td><span class="badge badge-gray"><?= $u['total_rating'] ?></span></td>
                    <td style="font-size:.82rem;color:var(--gray-400)"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['id'] !== intval($_SESSION['user_id']) && $u['role'] !== 'admin'): ?>
                            <a href="users.php?delete=<?= $u['id'] ?>"
                               class="btn btn-danger btn-sm"
                               data-confirm="Hapus user @<?= htmlspecialchars($u['username']) ?>? Semua data mereka akan ikut terhapus.">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php else: ?>
                            <span style="font-size:.78rem;color:var(--gray-400)">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>