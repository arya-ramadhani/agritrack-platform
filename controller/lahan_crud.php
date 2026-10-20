<?php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

// CEK LOGIN
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$action  = $_REQUEST['action'] ?? '';

switch ($action) {

    // ================= CREATE =================
    case 'create':

        $stmt = $conn->prepare("
            INSERT INTO tbl_lahan 
            (id_user, nama_lahan, lokasi, luas_hektar, jenis_tanah, status_lahan)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "issdss",
            $user_id,
            $_POST['nama_lahan'],
            $_POST['lokasi'],
            $_POST['luas_hektar'],
            $_POST['jenis_tanah'],
            $_POST['status_lahan']
        );

        echo json_encode([
            'success' => $stmt->execute()
        ]);
        exit;

    // ================= READ =================
    case 'read':

        $id = intval($_GET['id']);

        $stmt = $conn->prepare("
            SELECT * FROM tbl_lahan 
            WHERE id_lahan = ? AND id_user = ?
        ");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo json_encode($row);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;

    // ================= UPDATE =================
    case 'update':

        $stmt = $conn->prepare("
            UPDATE tbl_lahan SET
                nama_lahan = ?,
                lokasi = ?,
                luas_hektar = ?,
                jenis_tanah = ?,
                status_lahan = ?
            WHERE id_lahan = ? AND id_user = ?
        ");

        $stmt->bind_param(
            "ssdssii",
            $_POST['nama_lahan'],
            $_POST['lokasi'],
            $_POST['luas_hektar'],
            $_POST['jenis_tanah'],
            $_POST['status_lahan'],
            $_POST['id_lahan'],
            $user_id
        );

        echo json_encode(['success' => $stmt->execute()]);
        exit;

    // ================= DELETE =================
    case 'delete':

        $stmt = $conn->prepare("
            DELETE FROM tbl_lahan 
            WHERE id_lahan = ? AND id_user = ?
        ");
        $stmt->bind_param("ii", $_POST['id_lahan'], $user_id);

        echo json_encode(['success' => $stmt->execute()]);
        exit;
}
