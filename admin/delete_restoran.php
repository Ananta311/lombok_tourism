<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../user/login.php');

$db = getDB();
$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: restoran.php'); exit; }

$restoran = $db->query("SELECT foto FROM restoran WHERE id=$id")->fetch_assoc();
if ($restoran && $restoran['foto']) {
    $path = UPLOAD_DIR . $restoran['foto'];
    if (file_exists($path)) unlink($path);
}

$db->query("DELETE FROM restoran WHERE id=$id");

header('Location: restoran.php?msg=deleted');
exit;
