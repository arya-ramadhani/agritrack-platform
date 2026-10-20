<?php
// File: config/koneksi.php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_pertanian_presisi');

// Membuat koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset ke UTF-8
$conn->set_charset("utf8");

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// ── Base URL Dinamis ──────────────────────────────────────────────
// Deteksi otomatis berdasarkan cara akses (localhost/AgriTrack ATAU agritrack.test)
// Selalu gunakan host + path yang sama seperti saat request masuk
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';   // pakai host persis dari request
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);       // folder script yang sedang berjalan

    // Normalkan path: hapus /pages, /controller, /export jika ada
    $basePath = preg_replace('#/(pages|controller|export|api|config)(/.*)?$#', '', $scriptDir);
    $basePath = rtrim($basePath, '/');

    return $protocol . '://' . $host . $basePath;
}

define('BASE_URL', getBaseUrl());
// ─────────────────────────────────────────────────────────────────

// Fungsi untuk mencegah SQL Injection
function escape($string) {
    global $conn;
    return $conn->real_escape_string($string);
}

// Fungsi untuk mengecek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk redirect jika belum login — selalu kembali ke login.php yang benar
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}
?>