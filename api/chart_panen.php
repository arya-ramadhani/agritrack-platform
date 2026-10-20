<?php
session_start();
require_once '../config/koneksi.php';
requireLogin();

header('Content-Type: application/json');

$id_user = $_SESSION['user_id'];
$bulan   = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 6;
if (!in_array($bulan, [3, 6, 12])) $bulan = 6;

// Cari tanggal panen terbaru milik user ini (agar data dummy 2025 tetap terbaca)
$latest = $conn->query("SELECT MAX(tanggal_panen) AS max_date FROM tbl_panen WHERE id_user = '$id_user'")->fetch_assoc();
$max_date = $latest['max_date'] ?? date('Y-m-d');

// Ambil data N bulan ke belakang dari tanggal panen terbaru
$result = $conn->query("
    SELECT 
        DATE_FORMAT(tanggal_panen, '%b %Y') AS bulan_label,
        DATE_FORMAT(tanggal_panen, '%Y-%m') AS bulan_sort,
        SUM(hasil_panen_kg) AS total_kg,
        COUNT(*) AS jumlah_panen
    FROM tbl_panen
    WHERE id_user = '$id_user'
      AND tanggal_panen >= DATE_SUB('$max_date', INTERVAL $bulan MONTH)
      AND tanggal_panen <= '$max_date'
    GROUP BY bulan_sort, bulan_label
    ORDER BY bulan_sort ASC
");

$labels = [];
$data   = [];
$counts = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['bulan_label'];
    $data[]   = (float)$row['total_kg'];
    $counts[]  = (int)$row['jumlah_panen'];
}

echo json_encode([
    'labels'   => $labels,
    'data'     => $data,
    'counts'   => $counts,
    'periode'  => $bulan,
    'max_date' => $max_date,
]);
