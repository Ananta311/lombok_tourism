<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: wisata.php'); exit; }

// Delete all photos from disk
$photos = $db->query("SELECT foto FROM wisata_foto WHERE wisata_id=$id")->fetch_all(MYSQLI_ASSOC);
foreach ($photos as $p) {
    $path = UPLOAD_DIR . $p['foto'];
    if (file_exists($path)) unlink($path);
}

// Delete comment photos from disk
$komFotos = $db->query("
    SELECT kf.foto FROM komentar_foto kf
    JOIN komentar k ON k.id = kf.komentar_id
    WHERE k.wisata_id = $id
")->fetch_all(MYSQLI_ASSOC);
foreach ($komFotos as $kf) {
    $path = UPLOAD_DIR . $kf['foto'];
    if (file_exists($path)) unlink($path);
}

// DB cascade handles the rest
$db->query("DELETE FROM tempat_wisata WHERE id=$id");

header('Location: wisata.php?msg=deleted');
exit;