<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) { header('Location: ../index.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email    = sanitize($_POST['email']    ?? '');
    $password = $_POST['password']          ?? '';
    $confirm  = $_POST['confirm']           ?? '';

    if (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Password dan konfirmasi tidak cocok.';
    } else {
        $db = getDB();

        // Cek duplikat
        $check = $db->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Username atau email sudah digunakan.';
        } else {
            // 1. Insert user
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?,?,?)");
            $stmt->bind_param("sss", $username, $email, $hash);

            if (!$stmt->execute()) {
                $error = 'Gagal membuat akun, coba lagi.';
            } else {
                $userId = $db->insert_id;

                // 2. Insert profile (satu kali saja)
                $p = $db->prepare("INSERT INTO profile (user_id, full_name) VALUES (?,?)");
                $p->bind_param("is", $userId, $username);
                $p->execute();

                // Redirect ke login dengan pesan sukses
                header('Location: login.php?registered=1');
                exit;
            }
        }
    }
}
$pageTitle = 'Daftar - Lombok Tourism';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
    <!-- Left Panel -->
    <div class="auth-left">
        <div class="auth-left-content">
            <h2>Bergabung dengan Komunitas Wisata Lombok</h2>
            <p style="opacity:.8;line-height:1.7;margin:16px 0 28px">Daftar sekarang dan nikmati fitur lengkap: simpan wisata favorit, tulis ulasan, dan berbagi foto pengalaman Anda.</p>
            <div style="display:flex;flex-direction:column;gap:16px">
                <?php foreach ([
                    ['fa-star',        'Beri rating & komentar wisata'],
                    ['fa-camera',      'Upload foto komentar'],
                    ['fa-user-circle', 'Profil lengkap dengan bio'],
                    ['fa-compass',     'Temukan wisata terbaik'],
                ] as $f): ?>
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas <?= $f[0] ?>"></i>
                    </div>
                    <span style="font-size:.9rem;opacity:.85"><?= $f[1] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="auth-right">
        <div class="auth-form-wrap">
            <div class="auth-logo">
                <div class="logo-icon"><i class="fas fa-compass"></i></div>
                <span>Lombok Tourism</span>
            </div>
            <h1 class="auth-title">Buat Akun Baru</h1>
            <p class="auth-subtitle">Mulai petualangan wisata Lombok Anda</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label class="form-label">Username <span>*</span></label>
                    <div class="input-group">
                        <input type="text" name="username" class="form-control"
                               placeholder="Masukkan username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               required style="padding-left:44px">
                        <i class="fas fa-user" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--gray-400)"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email <span>*</span></label>
                    <div class="input-group">
                        <input type="email" name="email" class="form-control"
                               placeholder="email@contoh.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required style="padding-left:44px">
                        <i class="fas fa-envelope" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--gray-400)"></i>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password <span>*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control"
                                   placeholder="Min. 6 karakter" required>
                            <button type="button" class="input-toggle"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password <span>*</span></label>
                        <div class="input-group">
                            <input type="password" name="confirm" class="form-control"
                                   placeholder="Ulangi password" required>
                            <button type="button" class="input-toggle"><i class="fas fa-eye"></i></button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" required>
                        <span style="font-size:.88rem;color:var(--gray-600)">
                            Saya setuju dengan <a href="#" style="color:var(--blue-600)">Syarat &amp; Ketentuan</a>
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary"
                        style="width:100%;justify-content:center;padding:14px">
                    <i class="fas fa-user-plus"></i> Buat Akun
                </button>
            </form>

            <p class="auth-link">Sudah punya akun? <a href="login.php">Masuk sekarang</a></p>
            <p class="auth-link">
                <a href="../index.php"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
            </p>
        </div>
    </div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>