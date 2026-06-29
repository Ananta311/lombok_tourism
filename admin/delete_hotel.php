<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: hotel.php'); exit; }

$hotel = $db->query("SELECT foto FROM hotel WHERE id=$id")->fetch_assoc();
if ($hotel && $hotel['foto']) {
    $path = UPLOAD_DIR . $hotel['foto'];
    if (file_exists($path)) unlink($path);
}

// hotel_rating ikut terhapus otomatis via ON DELETE CASCADE
$db->query("DELETE FROM hotel WHERE id=$id");

header('Location: hotel.php?msg=deleted');
exit;
