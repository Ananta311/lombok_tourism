<?php
// includes/header.php
require_once __DIR__ . '/../includes/auth.php';
$siteName = getSetting('site_name') ?: 'Lombok Tourism';
$currentUser = getCurrentUser();
$heroBg = getSetting('hero_bg');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $siteName) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>" class="nav-logo">
            <span class="logo-icon"><i class="fas fa-compass"></i></span>
            <span class="logo-text"><?= htmlspecialchars($siteName) ?></span>
        </a>

        <!-- ═══ Pencarian Global (Desktop) ═══ -->
        <div class="global-search-wrap" id="globalSearchWrap">
            <i class="fas fa-search global-search-icon"></i>
            <input type="text" id="globalSearchInput" class="global-search-input"
                   placeholder="Cari wisata, hotel, restoran..." autocomplete="off">
            <div class="global-search-dropdown" id="globalSearchDropdown"></div>
        </div>

        <button class="nav-toggle" id="navToggle">
            <span></span><span></span><span></span>
        </button>

        <ul class="nav-menu" id="navMenu">
            </li>
            <li><a href="<?= BASE_URL ?>" class="nav-link">Beranda</a></li>
            <li><a href="<?= BASE_URL ?>wisata.php" class="nav-link">Wisata</a></li>
            <li><a href="<?= BASE_URL ?>hotel.php" class="nav-link">Hotel</a></li>
            <li><a href="<?= BASE_URL ?>restoran.php" class="nav-link">Restoran</a></li>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <li><a href="<?= BASE_URL ?>admin/dashboard.php" class="nav-link nav-admin">
                        <i class="fas fa-tachometer-alt"></i> Admin
                    </a></li>
                <?php endif; ?>
                <li class="nav-dropdown">
                    <a href="#" class="nav-link nav-user-btn">
                        <?php if (!empty($currentUser['foto_profile'])): ?>
                            <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($currentUser['foto_profile']) ?>" class="nav-avatar" alt="Avatar">
                        <?php else: ?>
                            <div class="nav-avatar-placeholder"><i class="fas fa-user"></i></div>
                        <?php endif; ?>
                        <?= htmlspecialchars($currentUser['username']) ?>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="<?= BASE_URL ?>user/profile.php"><i class="fas fa-user-circle"></i> Profil Saya</a>
                        <a href="<?= BASE_URL ?>user/logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                    </div>
                </li>
            <?php else: ?>
                <li><a href="<?= BASE_URL ?>user/login.php" class="nav-link">Masuk</a></li>
                <li><a href="<?= BASE_URL ?>user/register.php" class="nav-btn">Daftar</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
// ═══════════════════════════════════════════════════════════
// PENCARIAN GLOBAL — live search dropdown (desktop & mobile)
// ═══════════════════════════════════════════════════════════
(function () {
    const BASE = '<?= BASE_URL ?>';
    function setupSearch(inputId, dropdownId) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        if (!input || !dropdown) return;

        let debounceTimer = null;

        input.addEventListener('input', function () {
            const q = this.value.trim();
            clearTimeout(debounceTimer);

            if (q.length < 2) {
                dropdown.classList.remove('open');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(BASE + 'search_api.php?q=' + encodeURIComponent(q))
                    .then(res => res.json())
                    .then(data => renderDropdown(data, dropdown, q))
                    .catch(() => { dropdown.innerHTML = ''; dropdown.classList.remove('open'); });
            }, 300);
        });

        input.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' && this.value.trim().length >= 1) {
                window.location.href = BASE + 'search.php?q=' + encodeURIComponent(this.value.trim());
            }
        });

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('open');
            }
        });
    }

    function renderDropdown(data, dropdown, q) {
        const groups = [
            { key: 'wisata',   label: 'Tempat Wisata',        icon: 'fa-map-marked-alt', iconClass: 'gsd-icon-wisata', url: 'detail.php?id=' },
            { key: 'hotel',    label: 'Hotel & Penginapan',    icon: 'fa-hotel',          iconClass: 'gsd-icon-hotel',  url: 'hotel_detail.php?id=' },
            { key: 'restoran', label: 'Restoran & Tempat Makan', icon: 'fa-utensils',     iconClass: 'gsd-icon-resto',  url: 'restoran_detail.php?id=' },
        ];

        let html = '';
        let totalCount = 0;

        groups.forEach(g => {
            const items = data[g.key] || [];
            if (items.length === 0) return;
            totalCount += items.length;
            html += `<div class="gsd-group-title">${g.label}</div>`;
            items.forEach(item => {
                const sub = item.kategori || item.lokasi || '';
                html += `
                    <a href="${BASE}${g.url}${item.id}" class="gsd-item" style="text-decoration:none">
                        <div class="gsd-item-icon ${g.iconClass}"><i class="fas ${g.icon}"></i></div>
                        <div>
                            <div class="gsd-item-name">${escapeHtml(item.nama)}</div>
                            <div class="gsd-item-sub">${escapeHtml(sub)}</div>
                        </div>
                    </a>`;
            });
        });

        if (totalCount === 0) {
            html = `<div class="gsd-empty"><i class="fas fa-search" style="font-size:1.4rem;display:block;margin-bottom:8px;color:var(--gray-300)"></i>Tidak ada hasil untuk "${escapeHtml(q)}"</div>`;
        } else {
            html += `<div class="gsd-footer"><a href="${BASE}search.php?q=${encodeURIComponent(q)}">Lihat semua hasil untuk "${escapeHtml(q)}" →</a></div>`;
        }

        dropdown.innerHTML = html;
        dropdown.classList.add('open');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    setupSearch('globalSearchInput', 'globalSearchDropdown');
    setupSearch('globalSearchInputMobile', 'globalSearchDropdownMobile');
})();
</script>

