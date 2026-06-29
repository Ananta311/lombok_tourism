<?php
// ============================================
// config/database.php
// Konfigurasi Database
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lombok_tourism');
define('DB_CHARSET', 'utf8mb4');

define('BASE_URL', 'http://localhost/lombok_tourism/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset(DB_CHARSET);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'Koneksi database gagal: ' . $conn->connect_error]));
        }
    }
    return $conn;
}

// Helper: Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Helper: Upload file
function uploadFile($file, $folder, $allowedTypes = ['image/jpeg','image/png','image/webp','image/gif']) {
    $uploadPath = UPLOAD_DIR . $folder . '/';
    if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Tipe file tidak diizinkan.'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'Ukuran file maksimal 5MB.'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dest = $uploadPath . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => true, 'filename' => $folder . '/' . $filename];
    }
    return ['error' => 'Gagal mengupload file.'];
}

// Helper: Get site setting
function getSetting($key) {
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['setting_value'] : '';
}

// Helper: Average rating
function getAvgRating($wisataId) {
    $db = getDB();
    $wisataId = intval($wisataId);
    $result = $db->query("SELECT AVG(rating) as avg_r, COUNT(*) as total FROM rating WHERE wisata_id = $wisataId")->fetch_assoc();
    return $result;
}

// Helper: Average hotel rating
function getAvgHotelRating($hotelId) {
    $db = getDB();
    $hotelId = intval($hotelId);
    $result = $db->query("SELECT AVG(rating) as avg_r, COUNT(*) as total FROM hotel_rating WHERE hotel_id = $hotelId")->fetch_assoc();
    return $result;
}

// Helper: Average restoran rating
function getAvgRestoranRating($restoranId) {
    $db = getDB();
    $restoranId = intval($restoranId);
    $result = $db->query("SELECT AVG(rating) as avg_r, COUNT(*) as total FROM restoran_rating WHERE restoran_id = $restoranId")->fetch_assoc();
    return $result;
}

// Helper: Validasi link Google Maps sebelum ditampilkan sebagai tombol
function hasMapsLink($link) {
    return !empty($link) && filter_var($link, FILTER_VALIDATE_URL) !== false;
}

// Helper: Render bintang rating (★★★★☆ style) sebagai HTML string aman
function renderStars($avg) {
    $avg = round(floatval($avg));
    $avg = max(0, min(5, $avg));
    return str_repeat('★', $avg) . str_repeat('☆', 5 - $avg);
}
