<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: restoran.php'); exit; }

// Delete all gallery photos from disk
$photos = $db->query("SELECT foto FROM restoran_foto WHERE restoran_id=$id")->fetch_all(MYSQLI_ASSOC);
foreach ($photos as $p) {
    $path = UPLOAD_DIR . $p['foto'];
    if (file_exists($path)) unlink($path);
}

// restoran_foto & restoran_rating ikut terhapus otomatis via ON DELETE CASCADE
$db->query("DELETE FROM restoran WHERE id=$id");

header('Location: restoran.php?msg=deleted');
exit;