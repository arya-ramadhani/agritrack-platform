<?php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

// =======================
// Cek login
// =======================
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id_user = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

switch ($action) {

    // =======================
    // CREATE
    // =======================
    case 'create':
        $id_lahan = intval($_POST['id_lahan']);
        $nama_tanaman = escape($_POST['nama_tanaman']);
        $varietas = escape($_POST['varietas']);
        $tanggal_tanam = escape($_POST['tanggal_tanam']);
        $jumlah_bibit = intval($_POST['jumlah_bibit']);
        $status_pertumbuhan = escape($_POST['status_pertumbuhan']);

        $query = "
            INSERT INTO tbl_penanaman 
            (id_user, id_lahan, nama_tanaman, varietas, tanggal_tanam, jumlah_bibit, status_pertumbuhan)
            VALUES 
            ('$id_user', '$id_lahan', '$nama_tanaman', '$varietas', '$tanggal_tanam', '$jumlah_bibit', '$status_pertumbuhan')
        ";

        if ($conn->query($query)) {
            echo json_encode(['success' => true, 'message' => 'Data berhasil ditambahkan']);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        break;

    // =======================
    // READ (EDIT)
    // =======================
    case 'read':
        $id = intval($_GET['id']);

        $query = "
            SELECT * FROM tbl_penanaman 
            WHERE id_penanaman = '$id'
            AND id_user = '$id_user'
        ";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan atau akses ditolak']);
        }
        break;

    // =======================
    // UPDATE
    // =======================
    case 'update':
        $id_penanaman = intval($_POST['id_penanaman']);
        $id_lahan = intval($_POST['id_lahan']);
        $nama_tanaman = escape($_POST['nama_tanaman']);
        $varietas = escape($_POST['varietas']);
        $tanggal_tanam = escape($_POST['tanggal_tanam']);
        $jumlah_bibit = intval($_POST['jumlah_bibit']);
        $status_pertumbuhan = escape($_POST['status_pertumbuhan']);

        $query = "
            UPDATE tbl_penanaman SET
                id_lahan = '$id_lahan',
                nama_tanaman = '$nama_tanaman',
                varietas = '$varietas',
                tanggal_tanam = '$tanggal_tanam',
                jumlah_bibit = '$jumlah_bibit',
                status_pertumbuhan = '$status_pertumbuhan'
            WHERE id_penanaman = '$id_penanaman'
            AND id_user = '$id_user'
        ";

        if ($conn->query($query)) {
            echo json_encode(['success' => true, 'message' => 'Data berhasil diupdate']);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        break;

    // =======================
    // DELETE
    // =======================
    case 'delete':
        $id = intval($_POST['id_penanaman']);

        $query = "
            DELETE FROM tbl_penanaman
            WHERE id_penanaman = '$id'
            AND id_user = '$id_user'
        ";

        if ($conn->query($query)) {
            echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
