<?php
// ============================================================
// search_api.php — Endpoint AJAX untuk pencarian global di navbar
// Mengembalikan JSON hasil pencarian dari 3 kategori:
// wisata, hotel, restoran — dibatasi masing-masing 4 hasil teratas
// untuk ditampilkan di dropdown live-search.
// ============================================================
require_once __DIR__ . '/config/database.php';
header('Content-Type: application/json');

$db = getDB();
$q  = sanitize($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode(['wisata' => [], 'hotel' => [], 'restoran' => []]);
    exit;
}

$lq = "%$q%";

// ── Wisata ──
$stmt = $db->prepare("SELECT id, nama, kategori FROM tempat_wisata WHERE nama LIKE ? OR kategori LIKE ? LIMIT 4");
$stmt->bind_param("ss", $lq, $lq);
$stmt->execute();
$wisata = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Hotel ──
$stmt = $db->prepare("SELECT id, nama_hotel as nama, lokasi FROM hotel WHERE nama_hotel LIKE ? OR lokasi LIKE ? LIMIT 4");
$stmt->bind_param("ss", $lq, $lq);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Restoran ──
$stmt = $db->prepare("SELECT id, nama_restoran as nama, lokasi FROM restoran WHERE nama_restoran LIKE ? OR lokasi LIKE ? LIMIT 4");
$stmt->bind_param("ss", $lq, $lq);
$stmt->execute();
$restoran = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'wisata'   => $wisata,
    'hotel'    => $hotel,
    'restoran' => $restoran,
]);
