<?php
session_start();
require_once '../config/koneksi.php';
header('Content-Type: application/json');

$response = ['success' => false];

if (!isLoggedIn()) {
    echo json_encode($response);
    exit;
}

$id_user = $_SESSION['user_id'];
$action  = $_REQUEST['action'] ?? '';

switch ($action) {

case 'create':
    $id_penanaman = intval($_POST['id_penanaman'] ?? 0);
    $tanggal      = escape($_POST['tanggal_panen'] ?? '');
    $hasil        = floatval($_POST['hasil_panen_kg'] ?? 0);
    $kualitas     = escape($_POST['kualitas'] ?? '');
    $catatan      = escape($_POST['catatan'] ?? '');

    if (!$id_penanaman || !$tanggal || !$hasil || !$kualitas) {
        $response['message'] = 'Data tidak lengkap';
        echo json_encode($response);
        exit;
    }

    $cek = $conn->query("
        SELECT id_penanaman FROM tbl_penanaman
        WHERE id_penanaman='$id_penanaman'
        AND id_user='$id_user'
    ");

    if (!$cek || !$cek->num_rows) {
        $response['message'] = 'Penanaman tidak valid';
        echo json_encode($response);
        exit;
    }

    if ($conn->query("
        INSERT INTO tbl_panen
        (id_user,id_penanaman,tanggal_panen,hasil_panen_kg,kualitas,catatan)
        VALUES
        ('$id_user','$id_penanaman','$tanggal','$hasil','$kualitas','$catatan')
    ")) {
        $response['success'] = true;
    }

    echo json_encode($response);
    break;


case 'read':
    $id = intval($_GET['id'] ?? 0);
    $q  = $conn->query("
        SELECT * FROM tbl_panen
        WHERE id_panen='$id' AND id_user='$id_user'
    ");
    echo json_encode($q ? $q->fetch_assoc() : []);
    break;


case 'update':
    $id = intval($_POST['id_panen'] ?? 0);

    if ($conn->query("
        UPDATE tbl_panen SET
        tanggal_panen='".escape($_POST['tanggal_panen'])."',
        hasil_panen_kg='".floatval($_POST['hasil_panen_kg'])."',
        kualitas='".escape($_POST['kualitas'])."',
        catatan='".escape($_POST['catatan'])."'
        WHERE id_panen='$id' AND id_user='$id_user'
    ")) {
        $response['success'] = true;
    }

    echo json_encode($response);
    break;


case 'delete':
    if ($conn->query("
        DELETE FROM tbl_panen
        WHERE id_panen='".intval($_POST['id_panen'])."'
        AND id_user='$id_user'
    ")) {
        $response['success'] = true;
    }

    echo json_encode($response);
    break;

default:
    echo json_encode($response);
}
