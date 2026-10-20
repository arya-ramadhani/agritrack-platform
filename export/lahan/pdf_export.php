<?php
session_start();
require_once '../../config/koneksi.php';

// Cek login
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Ambil ID user login
$id_user = $_SESSION['user_id'];

// Set memory dan time limit
ini_set('memory_limit', '256M');
set_time_limit(300);

// Load TCPDF
require_once __DIR__ . '/../../vendor/autoload.php';

try {

    // ===============================
    // INIT PDF
    // ===============================
    $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

    $pdf->SetCreator('AgriTrack System');
    $pdf->SetAuthor('AgriTrack');
    $pdf->SetTitle('Laporan Data Lahan');
    $pdf->SetSubject('Laporan Pertanian Presisi');

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();

    // ===============================
    // TITLE
    // ===============================
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(46, 125, 50);
    $pdf->Cell(0, 8, 'AgriTrack - Sistem Pertanian Presisi', 0, 1, 'C');

    $pdf->SetTextColor(0);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 6, 'Laporan Data Lahan Pertanian', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, 'Tanggal Export: ' . date('d F Y H:i:s'), 0, 1, 'C');
    $pdf->Ln(8);

    // ===============================
    // TABLE SETUP
    // ===============================
    $pageWidth = 297;
    $header = ['No', 'Nama Lahan', 'Lokasi', 'Luas (Ha)', 'Jenis Tanah', 'Status'];
    $widths = [12, 50, 70, 30, 45, 30];
    $tableWidth = array_sum($widths);
    $startX = ($pageWidth - $tableWidth) / 2;

    // ===============================
    // QUERY DATA (FILTER USER LOGIN)
    // ===============================
    $stmt = $conn->prepare("
        SELECT nama_lahan, lokasi, luas_hektar, jenis_tanah, status_lahan
        FROM tbl_lahan
        WHERE id_user = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    // ===============================
    // HEADER TABLE
    // ===============================
    $pdf->SetX($startX);
    $pdf->SetFillColor(76, 175, 80);
    $pdf->SetTextColor(255);
    $pdf->SetFont('helvetica', 'B', 9);

    foreach ($header as $i => $col) {
        $pdf->Cell($widths[$i], 8, $col, 1, 0, 'C', true);
    }
    $pdf->Ln();

    // ===============================
    // TABLE CONTENT
    // ===============================
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0);

    $no = 1;
    $fill = false;
    $totalLuas = 0;
    $countAktif = 0;
    $countTidakAktif = 0;

    if ($result->num_rows > 0) {

        // Simpan data untuk reuse (distribusi tanah)
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;

            $pdf->SetX($startX);
            $fill = !$fill;
            $pdf->SetFillColor($fill ? 245 : 255);

            $pdf->Cell($widths[0], 7, $no++, 1, 0, 'C', true);
            $pdf->Cell($widths[1], 7, substr($row['nama_lahan'], 0, 35), 1, 0, 'L', true);
            $pdf->Cell($widths[2], 7, substr($row['lokasi'], 0, 50), 1, 0, 'L', true);

            $luas = (float)$row['luas_hektar'];
            $pdf->Cell($widths[3], 7, number_format($luas, 2), 1, 0, 'R', true);

            $pdf->Cell($widths[4], 7, substr($row['jenis_tanah'], 0, 30), 1, 0, 'C', true);

            if ($row['status_lahan'] === 'aktif') {
                $pdf->SetFillColor(40, 167, 69);
                $countAktif++;
            } else {
                $pdf->SetFillColor(108, 117, 125);
                $countTidakAktif++;
            }

            $pdf->SetTextColor(255);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell($widths[5], 7, ucfirst($row['status_lahan']), 1, 0, 'C', true);

            $pdf->SetTextColor(0);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Ln();

            $totalLuas += $luas;
        }

        // ===============================
        // SUMMARY
        // ===============================
        $totalData = $countAktif + $countTidakAktif;
        $boxWidth = $tableWidth / 2;

        $pdf->Ln(4);
        $pdf->SetX($startX);
        $pdf->SetFillColor(232, 245, 233);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($boxWidth, 8, 'TOTAL DATA LAHAN:', 1, 0, 'R', true);
        $pdf->Cell($boxWidth, 8, $totalData . ' Lahan', 1, 0, 'C', true);
        $pdf->Ln();

        $pdf->SetX($startX);
        $pdf->SetFillColor(255, 249, 196);
        $pdf->Cell($boxWidth, 8, 'TOTAL LUAS LAHAN:', 1, 0, 'R', true);
        $pdf->Cell($boxWidth, 8, number_format($totalLuas, 2) . ' Ha', 1, 0, 'C', true);
        $pdf->Ln();

        // ===============================
        // DISTRIBUSI JENIS TANAH
        // ===============================
        $pdf->Ln(3);
        $pdf->SetX($startX);
        $pdf->SetFillColor(76, 175, 80);
        $pdf->SetTextColor(255);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell($tableWidth, 7, 'DISTRIBUSI JENIS TANAH', 1, 0, 'C', true);
        $pdf->Ln();

        $jenisTanahCount = [];
        foreach ($rows as $r) {
            $jenisTanahCount[$r['jenis_tanah']] = ($jenisTanahCount[$r['jenis_tanah']] ?? 0) + 1;
        }

        $pdf->SetTextColor(0);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($jenisTanahCount as $jenis => $count) {
            $persen = $totalData > 0 ? ($count / $totalData * 100) : 0;
            $pdf->SetX($startX);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell($boxWidth, 7, $jenis . ':', 1, 0, 'R', true);
            $pdf->Cell($boxWidth, 7, $count . ' Lahan (' . number_format($persen, 1) . '%)', 1, 0, 'C', true);
            $pdf->Ln();
        }

    } else {
        $pdf->SetX($startX);
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell($tableWidth, 10, 'Tidak ada data lahan yang tersedia', 1, 0, 'C');
    }

    $stmt->close();

    // ===============================
    // OUTPUT
    // ===============================
    $filename = 'AgriTrack_Lahan_' . date('Ymd_His') . '.pdf';
    $pdf->Output($filename, 'D');
    exit;

} catch (Exception $e) {
    error_log('Export PDF Error: ' . $e->getMessage());
    die('Terjadi kesalahan saat mengekspor PDF: ' . htmlspecialchars($e->getMessage()));
}
?>