<?php
require_once __DIR__ . '/../includes/auth.php';

$adminCurrentUser = getCurrentUser();
$adminUser   = $adminCurrentUser['username'] ?? ($_SESSION['username'] ?? 'Admin');
$adminFoto   = $adminCurrentUser['foto_profile'] ?? null;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin - Lombok Tourism') ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="admin-body">
<div class="admin-layout">

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-logo">
        <div class="logo-icon"><i class="fas fa-compass"></i></div>
        <span>Lombok Admin</span>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section-title">Utama</div>
        <a href="dashboard.php" class="sidebar-link <?= $currentPage==='dashboard.php'?'active':'' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="sidebar-section-title">Konten</div>
        <a href="wisata.php" class="sidebar-link <?= in_array($currentPage,['wisata.php','add_wisata.php','edit_wisata.php'])?'active':'' ?>">
            <i class="fas fa-map-marked-alt"></i> Kelola Wisata
        </a>
        <a href="add_wisata.php" class="sidebar-link <?= $currentPage==='add_wisata.php'?'active':'' ?>">
            <i class="fas fa-plus-circle"></i> Tambah Wisata
        </a>
        <a href="komentar.php" class="sidebar-link <?= $currentPage==='komentar.php'?'active':'' ?>">
            <i class="fas fa-comments"></i> Komentar Wisata
        </a>
        <a href="rating.php" class="sidebar-link <?= $currentPage==='rating.php'?'active':'' ?>">
            <i class="fas fa-star"></i> Rating Wisata
        </a>

        <div class="sidebar-section-title">Hotel & Penginapan</div>
        <a href="hotel.php" class="sidebar-link <?= in_array($currentPage,['hotel.php','add_hotel.php','edit_hotel.php'])?'active':'' ?>">
            <i class="fas fa-hotel"></i> Kelola Hotel
        </a>
        <a href="add_hotel.php" class="sidebar-link <?= $currentPage==='add_hotel.php'?'active':'' ?>">
            <i class="fas fa-plus-circle"></i> Tambah Hotel
        </a>
        <a href="komentar_hotel.php" class="sidebar-link <?= $currentPage==='komentar_hotel.php'?'active':'' ?>">
            <i class="fas fa-comment-dots"></i> Komentar Hotel
        </a>

        <div class="sidebar-section-title">Restoran & Tempat Makan</div>
        <a href="restoran.php" class="sidebar-link <?= in_array($currentPage,['restoran.php','add_restoran.php','edit_restoran.php'])?'active':'' ?>">
            <i class="fas fa-utensils"></i> Kelola Restoran
        </a>
        <a href="add_restoran.php" class="sidebar-link <?= $currentPage==='add_restoran.php'?'active':'' ?>">
            <i class="fas fa-plus-circle"></i> Tambah Restoran
        </a>
        <a href="komentar_restoran.php" class="sidebar-link <?= $currentPage==='komentar_restoran.php'?'active':'' ?>">
            <i class="fas fa-comment-dots"></i> Komentar Restoran
        </a>

        <div class="sidebar-section-title">Manajemen</div>
        <a href="users.php" class="sidebar-link <?= $currentPage==='users.php'?'active':'' ?>">
            <i class="fas fa-users"></i> Data User
        </a>
        <a href="settings.php" class="sidebar-link <?= $currentPage==='settings.php'?'active':'' ?>">
            <i class="fas fa-cog"></i> Pengaturan
        </a>

        <div class="sidebar-section-title">Lainnya</div>
        <a href="../index.php" class="sidebar-link" target="_blank">
            <i class="fas fa-globe"></i> Lihat Website
        </a>
        <a href="../user/logout.php" class="sidebar-link" style="color:#fc8181">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </nav>
</aside>

<!-- Main content -->
<main class="admin-main">
    <!-- Topbar -->
    <div class="admin-topbar">
        <div style="display:flex;align-items:center;gap:12px">
            <button id="adminSidebarToggle"
                    style="background:none;border:none;cursor:pointer;color:var(--gray-500);font-size:1.1rem;display:none">
                <i class="fas fa-bars"></i>
            </button>
            <span class="admin-topbar-title">
                <?= htmlspecialchars($pageTitle ?? 'Admin Panel') ?>
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="../index.php" target="_blank"
               style="font-size:.82rem;color:var(--gray-500);display:flex;align-items:center;gap:6px">
                <i class="fas fa-external-link-alt"></i> Website
            </a>
            <div style="display:flex;align-items:center;gap:8px;padding:6px 14px;background:var(--gray-50);border-radius:var(--radius-full)">
                <?php if (!empty($adminFoto)): ?>
                    <img src="../uploads/<?= htmlspecialchars($adminFoto) ?>" alt="Avatar"
                         style="width:30px;height:30px;border-radius:50%;object-fit:cover;border:2px solid var(--blue-200)">
                <?php else: ?>
                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--blue-400),var(--green-400));display:flex;align-items:center;justify-content:center;color:white;font-size:.8rem;font-weight:700">
                        <?= strtoupper(substr($adminUser,0,1)) ?>
                    </div>
                <?php endif; ?>
                <span style="font-size:.88rem;font-weight:600;color:var(--gray-700)"><?= htmlspecialchars($adminUser) ?></span>
            </div>
        </div>
    </div>