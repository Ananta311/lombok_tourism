<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin('../user/login.php');

$db     = getDB();
$userId = intval($_SESSION['user_id']);
$msg    = '';
$error  = '';

/* ════════════════════════════════════════════
   HANDLE POST
   ════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* ── Update profil ── */
    if ($action === 'update_profile') {
        $fullName = sanitize($_POST['full_name'] ?? '');
        $bio      = sanitize($_POST['bio']       ?? '');
        $phone    = sanitize($_POST['phone']     ?? '');
        $location = sanitize($_POST['location']  ?? '');

        // Cek apakah ada foto baru
        $newFoto = null;
        if (!empty($_FILES['foto_profile']['name']) && $_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {
            $up = uploadFile($_FILES['foto_profile'], 'profile');
            if (isset($up['error'])) {
                $error = $up['error'];
            } else {
                // Hapus foto lama dari disk
                $old = $db->query("SELECT foto_profile FROM profile WHERE user_id=$userId")->fetch_assoc();
                if ($old && !empty($old['foto_profile'])) {
                    $oldPath = UPLOAD_DIR . $old['foto_profile'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $newFoto = $up['filename'];
            }
        }

        if (!$error) {
            if ($newFoto !== null) {
                // Update termasuk foto
                $stmt = $db->prepare(
                    "INSERT INTO profile (user_id, full_name, bio, phone, location, foto_profile)
                     VALUES (?,?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE
                       full_name=VALUES(full_name), bio=VALUES(bio),
                       phone=VALUES(phone), location=VALUES(location),
                       foto_profile=VALUES(foto_profile)"
                );
                $stmt->bind_param("isssss", $userId, $fullName, $bio, $phone, $location, $newFoto);
            } else {
                // Update tanpa ganti foto
                $stmt = $db->prepare(
                    "INSERT INTO profile (user_id, full_name, bio, phone, location)
                     VALUES (?,?,?,?,?)
                     ON DUPLICATE KEY UPDATE
                       full_name=VALUES(full_name), bio=VALUES(bio),
                       phone=VALUES(phone), location=VALUES(location)"
                );
                $stmt->bind_param("issss", $userId, $fullName, $bio, $phone, $location);
            }

            if ($stmt->execute()) {
                $msg = 'Profil berhasil diperbarui!';
            } else {
                $error = 'Gagal menyimpan profil: ' . $stmt->error;
            }
        }

        header('Location: profile.php?msg=' . urlencode($msg) . '&err=' . urlencode($error));
        exit;
    }

    /* ── Ganti password ── */
    if ($action === 'change_password') {
        $oldPass  = $_POST['old_password']     ?? '';
        $newPass  = $_POST['new_password']     ?? '';
        $confPass = $_POST['confirm_password'] ?? '';

        $userRow = $db->query("SELECT password FROM users WHERE id=$userId")->fetch_assoc();

        if (!$userRow || !password_verify($oldPass, $userRow['password'])) {
            $error = 'Password lama tidak benar.';
        } elseif (strlen($newPass) < 6) {
            $error = 'Password baru minimal 6 karakter.';
        } elseif ($newPass !== $confPass) {
            $error = 'Konfirmasi password tidak cocok.';
        } else {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $s    = $db->prepare("UPDATE users SET password=? WHERE id=?");
            $s->bind_param("si", $hash, $userId);
            if ($s->execute()) {
                $msg = 'Password berhasil diubah!';
            } else {
                $error = 'Gagal mengubah password.';
            }
        }

        header('Location: profile.php?tab=password&msg=' . urlencode($msg) . '&err=' . urlencode($error));
        exit;
    }
}

// Baca pesan dari query string
if (!empty($_GET['msg'])) $msg   = $_GET['msg'];
if (!empty($_GET['err'])) $error = $_GET['err'];
$activeTab = $_GET['tab'] ?? 'edit';

/* ── Load data terkini ── */
$currentUser = getCurrentUser();
if (!$currentUser) { header('Location: ../user/login.php'); exit; }

$myComments = $db->query("
    SELECT k.*, tw.nama as wisata_nama, tw.id as wisata_id,
           COUNT(kf.id) as foto_count
    FROM komentar k
    JOIN tempat_wisata tw ON tw.id = k.wisata_id
    LEFT JOIN komentar_foto kf ON kf.komentar_id = k.id
    WHERE k.user_id = $userId
    GROUP BY k.id ORDER BY k.created_at DESC LIMIT 15
")->fetch_all(MYSQLI_ASSOC);

$myRatings = $db->query("
    SELECT r.*, tw.nama as wisata_nama, tw.id as wisata_id
    FROM rating r JOIN tempat_wisata tw ON tw.id = r.wisata_id
    WHERE r.user_id = $userId
    ORDER BY r.updated_at DESC LIMIT 15
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Profil Saya — Lombok Tourism';
include __DIR__ . '/../includes/header.php';
?>

<div class="profile-page">

<!-- ═══════════ PROFILE HERO BANNER ═══════════ -->
<div class="profile-hero">
    <div class="profile-hero-bg"></div>
    <div class="profile-hero-content">
        <div class="container">
            <!-- Avatar + nama di atas banner -->
            <div class="profile-hero-inner">
                <div class="profile-avatar-ring">
                    <?php if (!empty($currentUser['foto_profile'])): ?>
                        <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($currentUser['foto_profile']) ?>"
                             class="profile-big-avatar" alt="Avatar" id="avatarPreviewImg">
                    <?php else: ?>
                        <div class="profile-big-avatar profile-avatar-initials" id="avatarPreviewImg">
                            <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <!-- Tombol ganti foto (klik area avatar) -->
                    <label for="quickFotoInput" class="avatar-edit-btn" title="Ganti foto profil">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>
                <div class="profile-hero-info">
                    <h1 class="profile-hero-name">
                        <?= htmlspecialchars($currentUser['full_name'] ?: $currentUser['username']) ?>
                    </h1>
                    <p class="profile-hero-username">@<?= htmlspecialchars($currentUser['username']) ?></p>
                    <div class="profile-hero-meta">
                        <?php if (!empty($currentUser['location'])): ?>
                            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($currentUser['location']) ?></span>
                        <?php endif; ?>
                        <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($currentUser['email']) ?></span>
                        <span><i class="fas fa-calendar-alt"></i> Bergabung <?= date('M Y', strtotime($currentUser['created_at'])) ?></span>
                    </div>
                </div>
                <div class="profile-hero-stats">
                    <div class="profile-stat-box">
                        <div class="profile-stat-num"><?= count($myComments) ?></div>
                        <div class="profile-stat-label">Komentar</div>
                    </div>
                    <div class="profile-stat-box">
                        <div class="profile-stat-num"><?= count($myRatings) ?></div>
                        <div class="profile-stat-label">Rating</div>
                    </div>
                    <div class="profile-stat-box">
                        <span class="badge <?= $currentUser['role']==='admin'?'badge-gold':'badge-blue' ?>" style="font-size:.82rem">
                            <i class="fas fa-<?= $currentUser['role']==='admin'?'crown':'user' ?>"></i>
                            <?= ucfirst($currentUser['role']) ?>
                        </span>
                        <div class="profile-stat-label">Role</div>
                    </div>
                </div>
            </div>

            <?php if (!empty($currentUser['bio'])): ?>
            <p class="profile-hero-bio"><?= nl2br(htmlspecialchars($currentUser['bio'])) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ═══════════ MAIN CONTENT ═══════════ -->
<div class="container" style="padding-top:32px;padding-bottom:64px">

    <!-- Alert -->
    <?php if ($msg): ?>
        <div class="alert alert-success" style="max-width:760px;margin:0 auto 20px">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" style="max-width:760px;margin:0 auto 20px">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Tab Card -->
    <div class="profile-tab-card" style="max-width:760px;margin:0 auto">

        <!-- Tab nav -->
        <div class="profile-tab-nav">
            <button class="ptab <?= $activeTab==='edit'?'active':'' ?>"
                    onclick="switchPTab('edit',this)">
                <i class="fas fa-user-edit"></i> Edit Profil
            </button>
            <button class="ptab <?= $activeTab==='password'?'active':'' ?>"
                    onclick="switchPTab('password',this)">
                <i class="fas fa-lock"></i> Password
            </button>
            <button class="ptab <?= $activeTab==='activity'?'active':'' ?>"
                    onclick="switchPTab('activity',this)">
                <i class="fas fa-history"></i> Aktivitas
            </button>
        </div>

        <!-- ── Tab Edit Profil ── -->
        <div class="ptab-content <?= $activeTab==='edit'?'active':'' ?>" id="ptab-edit">
            <form method="POST" enctype="multipart/form-data" action="profile.php">
                <input type="hidden" name="action" value="update_profile">

                <!-- Upload foto di dalam form -->
                <input type="file" id="quickFotoInput" name="foto_profile" accept="image/*"
                       style="display:none" onchange="previewAvatar(this)">

                <!-- Klik avatar di hero juga bisa trigger ini lewat JS -->

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="full_name" class="form-control"
                               value="<?= htmlspecialchars($currentUser['full_name'] ?? '') ?>"
                               placeholder="Nama lengkap Anda">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>"
                               placeholder="+62 xxx-xxxx-xxxx">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Kota / Lokasi</label>
                    <input type="text" name="location" class="form-control"
                           value="<?= htmlspecialchars($currentUser['location'] ?? '') ?>"
                           placeholder="Contoh: Mataram, NTB">
                </div>

                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-control" rows="4"
                              placeholder="Ceritakan sedikit tentang diri Anda..."><?= htmlspecialchars($currentUser['bio'] ?? '') ?></textarea>
                    <p class="form-hint">Tampil di halaman profil Anda</p>
                </div>

                <!-- Foto profil preview inline -->
                <div class="foto-upload-inline">
                    <div class="foto-upload-preview" id="fotoPreviewBox">
                        <?php if (!empty($currentUser['foto_profile'])): ?>
                            <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($currentUser['foto_profile']) ?>"
                                 id="fotoPreviewInline" alt="">
                        <?php else: ?>
                            <div class="foto-placeholder" id="fotoPreviewInline">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label for="quickFotoInput" class="btn btn-outline btn-sm" style="cursor:pointer">
                            <i class="fas fa-camera"></i> Pilih Foto Profil
                        </label>
                        <p class="form-hint" style="margin-top:6px">JPG, PNG, WebP — maks. 5MB</p>
                        <p id="selectedFileName" style="font-size:.78rem;color:var(--blue-600);margin-top:4px;display:none"></p>
                    </div>
                </div>

                <div style="display:flex;gap:12px;margin-top:8px">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- ── Tab Ganti Password ── -->
        <div class="ptab-content <?= $activeTab==='password'?'active':'' ?>" id="ptab-password">
            <form method="POST" action="profile.php">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group">
                    <label class="form-label">Password Lama <span>*</span></label>
                    <div class="input-group">
                        <input type="password" name="old_password" class="form-control" required
                               placeholder="Masukkan password saat ini">
                        <button type="button" class="input-toggle"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru <span>*</span></label>
                    <div class="input-group">
                        <input type="password" name="new_password" class="form-control" required minlength="6"
                               placeholder="Min. 6 karakter">
                        <button type="button" class="input-toggle"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru <span>*</span></label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" class="form-control" required
                               placeholder="Ulangi password baru">
                        <button type="button" class="input-toggle"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-key"></i> Ubah Password
                </button>
            </form>
        </div>

        <!-- ── Tab Aktivitas ── -->
        <div class="ptab-content <?= $activeTab==='activity'?'active':'' ?>" id="ptab-activity">

            <h4 class="activity-section-title">
                <i class="fas fa-comments" style="color:var(--blue-400)"></i> Komentar Saya
                <span class="badge badge-blue"><?= count($myComments) ?></span>
            </h4>

            <?php if (empty($myComments)): ?>
                <div class="activity-empty">
                    <i class="fas fa-comment-slash"></i>
                    <p>Belum ada komentar</p>
                </div>
            <?php else: ?>
                <?php foreach ($myComments as $c): ?>
                <div class="activity-item">
                    <div class="activity-item-header">
                        <a href="../detail.php?id=<?= $c['wisata_id'] ?>" class="activity-wisata-link">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($c['wisata_nama']) ?>
                        </a>
                        <div style="display:flex;align-items:center;gap:8px">
                            <?php if ($c['foto_count']): ?>
                                <span class="badge badge-blue" style="font-size:.72rem">
                                    <i class="fas fa-camera"></i> <?= $c['foto_count'] ?>
                                </span>
                            <?php endif; ?>
                            <span class="activity-date"><?= date('d M Y', strtotime($c['created_at'])) ?></span>
                        </div>
                    </div>
                    <p class="activity-text"><?= htmlspecialchars(mb_substr($c['komentar'], 0, 200)) ?><?= mb_strlen($c['komentar'])>200?'…':'' ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <h4 class="activity-section-title" style="margin-top:32px">
                <i class="fas fa-star" style="color:var(--gold)"></i> Rating Saya
                <span class="badge badge-gold"><?= count($myRatings) ?></span>
            </h4>

            <?php if (empty($myRatings)): ?>
                <div class="activity-empty">
                    <i class="fas fa-star"></i>
                    <p>Belum ada rating</p>
                </div>
            <?php else: ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
                    <?php foreach ($myRatings as $r): ?>
                    <a href="../detail.php?id=<?= $r['wisata_id'] ?>" class="rating-activity-card">
                        <div class="rating-activity-name"><?= htmlspecialchars($r['wisata_nama']) ?></div>
                        <div class="rating-stars-display">
                            <?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5-$r['rating']) ?>
                        </div>
                        <div class="activity-date"><?= date('d M Y', strtotime($r['updated_at'])) ?></div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div><!-- end profile-tab-card -->
</div>
</div><!-- end profile-page -->

<script>
function switchPTab(name, btn) {
    document.querySelectorAll('.ptab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.ptab-content').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('ptab-' + name).classList.add('active');
}

function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        // Update avatar di hero
        const heroAvatar = document.getElementById('avatarPreviewImg');
        if (heroAvatar.tagName === 'IMG') {
            heroAvatar.src = e.target.result;
        } else {
            // placeholder div → ganti dengan img
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'profile-big-avatar';
            img.id = 'avatarPreviewImg';
            heroAvatar.replaceWith(img);
        }
        // Update preview inline
        const inlinePreview = document.getElementById('fotoPreviewInline');
        if (inlinePreview) {
            if (inlinePreview.tagName === 'IMG') {
                inlinePreview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.id = 'fotoPreviewInline';
                inlinePreview.replaceWith(img);
            }
        }
    };
    reader.readAsDataURL(input.files[0]);

    // Tampilkan nama file
    const fn = document.getElementById('selectedFileName');
    if (fn) {
        fn.textContent = '📎 ' + input.files[0].name;
        fn.style.display = 'block';
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
