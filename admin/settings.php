<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db  = getDB();
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* Update text settings */
    if ($action === 'update_settings') {
        $keys = ['site_name','site_tagline','hero_title','hero_subtitle'];
        foreach ($keys as $key) {
            $val = sanitize($_POST[$key] ?? '');
            $stmt = $db->prepare("INSERT INTO site_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?");
            $stmt->bind_param("sss", $key, $val, $val);
            $stmt->execute();
        }
        $msg = 'Pengaturan berhasil disimpan!';
    }

    /* Upload hero background */
    if ($action === 'upload_bg') {
        if (!empty($_FILES['hero_bg']['name'])) {
            // Delete old bg
            $oldBg = getSetting('hero_bg');
            if ($oldBg) {
                $oldPath = UPLOAD_DIR . $oldBg;
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $up = uploadFile($_FILES['hero_bg'], 'profile');
            if (isset($up['error'])) {
                $error = $up['error'];
            } else {
                $fn   = $up['filename'];
                $stmt = $db->prepare("INSERT INTO site_settings (setting_key,setting_value) VALUES ('hero_bg',?) ON DUPLICATE KEY UPDATE setting_value=?");
                $stmt->bind_param("ss", $fn, $fn);
                $stmt->execute();
                $msg = 'Background berhasil diperbarui!';
            }
        }
    }

    /* Remove background */
    if ($action === 'remove_bg') {
        $oldBg = getSetting('hero_bg');
        if ($oldBg) {
            $oldPath = UPLOAD_DIR . $oldBg;
            if (file_exists($oldPath)) unlink($oldPath);
        }
        $db->query("UPDATE site_settings SET setting_value='' WHERE setting_key='hero_bg'");
        $msg = 'Background berhasil dihapus.';
    }

    header('Location: settings.php?msg=' . urlencode($msg) . '&err=' . urlencode($error));
    exit;
}

if (isset($_GET['msg'])) $msg   = $_GET['msg'];
if (isset($_GET['err'])) $error = $_GET['err'];

// Load current settings
$settings = [];
$rows = $db->query("SELECT setting_key, setting_value FROM site_settings")->fetch_all(MYSQLI_ASSOC);
foreach ($rows as $r) $settings[$r['setting_key']] = $r['setting_value'];

$heroBg = $settings['hero_bg'] ?? '';
$pageTitle = 'Pengaturan - Admin';
include __DIR__ . '/admin_header.php';
?>

<div class="admin-content">
    <h1 class="admin-page-title">Pengaturan Website</h1>
    <p class="admin-page-subtitle">Konfigurasi tampilan dan konten website</p>

    <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start">

        <!-- Text Settings -->
        <div class="form-card">
            <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
                <i class="fas fa-font" style="color:var(--blue-400)"></i> Teks & Identitas Website
            </h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_settings">
                <div class="form-group">
                    <label class="form-label">Nama Website</label>
                    <input type="text" name="site_name" class="form-control"
                           value="<?= htmlspecialchars($settings['site_name'] ?? 'Lombok Tourism') ?>">
                    <p class="form-hint">Tampil di navbar dan tab browser</p>
                </div>
                <div class="form-group">
                    <label class="form-label">Tagline</label>
                    <input type="text" name="site_tagline" class="form-control"
                           value="<?= htmlspecialchars($settings['site_tagline'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Judul Hero (Halaman Beranda)</label>
                    <input type="text" name="hero_title" class="form-control"
                           value="<?= htmlspecialchars($settings['hero_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Subjudul Hero</label>
                    <textarea name="hero_subtitle" class="form-control" rows="3"><?= htmlspecialchars($settings['hero_subtitle'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </form>
        </div>

        <!-- Background Settings -->
        <div>
            <div class="form-card" style="margin-bottom:20px">
                <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--gray-100)">
                    <i class="fas fa-image" style="color:var(--blue-400)"></i> Background Halaman Beranda
                </h3>
                <p style="font-size:.85rem;color:var(--gray-500);margin-bottom:16px">
                    Upload gambar untuk latar belakang hero section di halaman beranda. Ukuran ideal: 1920×1080px, JPG/WebP.
                </p>

                <!-- Current BG preview -->
                <div id="bgPreview" style="
                    height:180px; border-radius:var(--radius-md); margin-bottom:16px;
                    background:<?= $heroBg ? "url('../uploads/".htmlspecialchars($heroBg)."') center/cover" : "linear-gradient(135deg,var(--blue-800),var(--green-700))" ?>;
                    display:flex; align-items:center; justify-content:center;
                    color:rgba(255,255,255,.5); font-size:.88rem; border:2px dashed rgba(255,255,255,.2);">
                    <?= $heroBg ? '' : '<div style="text-align:center"><i class="fas fa-image" style="font-size:2rem;display:block;margin-bottom:8px"></i>Belum ada background</div>' ?>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_bg">
                    <div style="margin-bottom:14px">
                        <label class="form-label">Pilih Gambar Background</label>
                        <input type="file" name="hero_bg" id="bgFileInput" class="form-control" accept="image/*">
                        <p class="form-hint">JPG, PNG, WebP – maksimal 5MB</p>
                    </div>
                    <div style="display:flex;gap:10px">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Background
                        </button>
                        <?php if ($heroBg): ?>
                        </form>
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="action" value="remove_bg">
                            <button type="submit" class="btn btn-danger" data-confirm="Hapus background saat ini?">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        <form style="display:none"><!-- dummy closing tag for nesting fix -->
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/admin_footer.php'; ?>
