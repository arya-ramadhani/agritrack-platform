<?php
session_start();
require_once '../../config/koneksi.php';

// =====================
// CEK LOGIN
// =====================
requireLogin();

// Ambil id user login
$id_user = $_SESSION['user_id'];

// Set memory & time limit
ini_set('memory_limit', '256M');
set_time_limit(300);

// Load TCPDF
require_once __DIR__ . '/../../vendor/autoload.php';

try {

    // =====================
    // INIT TCPDF (Landscape)
    // =====================
    $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

    $pdf->SetCreator('AgriTrack System');
    $pdf->SetAuthor('AgriTrack');
    $pdf->SetTitle('Laporan Data Penanaman');
    $pdf->SetSubject('Laporan Pertanian Presisi');

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();

    // =====================
    // HEADER
    // =====================
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(46, 125, 50);
    $pdf->Cell(0, 8, 'AgriTrack - Sistem Pertanian Presisi', 0, 1, 'C');

    $pdf->SetTextColor(0);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 6, 'Laporan Data Penanaman', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, 'Tanggal Export: ' . date('d F Y H:i:s'), 0, 1, 'C');
    $pdf->Ln(8);

    // =====================
    // QUERY DATA (PER USER)
    // =====================
    $query = "
        SELECT p.*, l.nama_lahan, l.lokasi
        FROM tbl_penanaman p
        JOIN tbl_lahan l ON p.id_lahan = l.id_lahan
        WHERE p.id_user = ?
        ORDER BY p.created_at DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    // =====================
    // TABEL SETUP
    // =====================
    $header = [
        'No',
        'Lahan',
        'Nama Tanaman',
        'Varietas',
        'Tgl Tanam',
        'Jml Bibit',
        'Status Pertumbuhan'
    ];

    $widths = [12, 40, 40, 40, 30, 25, 40];
    $tableWidth = array_sum($widths);
    $pageWidth = 297;
    $startX = ($pageWidth - $tableWidth) / 2;

    // Header tabel
    $pdf->SetX($startX);
    $pdf->SetFillColor(76, 175, 80);
    $pdf->SetTextColor(255);
    $pdf->SetFont('helvetica', 'B', 9);

    foreach ($header as $i => $text) {
        $pdf->Cell($widths[$i], 8, $text, 1, 0, 'C', true);
    }
    $pdf->Ln();

    // =====================
    // DATA TABEL
    // =====================
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0);

    $no = 1;
    $fill = false;

    $totalBibit = 0;
    $countPembibitan = 0;
    $countVegetatif = 0;
    $countGeneratif = 0;
    $countSiapPanen = 0;

    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {

            $pdf->SetX($startX);
            $fill = !$fill;
            $pdf->SetFillColor($fill ? 245 : 255);

            $pdf->Cell($widths[0], 7, $no++, 1, 0, 'C', true);
            $pdf->Cell($widths[1], 7, substr($row['nama_lahan'], 0, 30), 1, 0, 'L', true);
            $pdf->Cell($widths[2], 7, substr($row['nama_tanaman'], 0, 30), 1, 0, 'L', true);
            $pdf->Cell($widths[3], 7, substr($row['varietas'], 0, 30), 1, 0, 'L', true);
            $pdf->Cell($widths[4], 7, date('d/m/Y', strtotime($row['tanggal_tanam'])), 1, 0, 'C', true);
            $pdf->Cell($widths[5], 7, number_format($row['jumlah_bibit']), 1, 0, 'R', true);

            // Status
            $status = $row['status_pertumbuhan'];
            $statusText = ucfirst(str_replace('_', ' ', $status));

            switch ($status) {
                case 'pembibitan':
                    $pdf->SetFillColor(255, 193, 7);
                    $countPembibitan++;
                    break;
                case 'vegetatif':
                    $pdf->SetFillColor(40, 167, 69);
                    $countVegetatif++;
                    break;
                case 'generatif':
                    $pdf->SetFillColor(0, 123, 255);
                    $countGeneratif++;
                    break;
                case 'siap_panen':
                    $pdf->SetFillColor(255, 87, 34);
                    $countSiapPanen++;
                    break;
                default:
                    $pdf->SetFillColor(108, 117, 125);
            }

            $pdf->SetTextColor(255);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell($widths[6], 7, $statusText, 1, 0, 'C', true);
            $pdf->Ln();

            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(0);

            $totalBibit += (int)$row['jumlah_bibit'];
        }
    } else {
        $pdf->SetX($startX);
        $pdf->Cell($tableWidth, 10, 'Tidak ada data penanaman', 1, 0, 'C');
    }

    // =====================
    // STATISTIK (CENTER)
    // =====================
    $pdf->Ln(6);

    // Hitung ulang posisi center untuk statistik
    $statWidth = $tableWidth;
    $statX = ($pageWidth - $statWidth) / 2;

    $pdf->SetX($statX);
    $pdf->SetFont('helvetica', 'B', 9);

    $box = $statWidth / 2;

    // Baris Total Data
    $pdf->SetFillColor(232, 245, 233);
    $pdf->Cell($box, 8, 'Total Data:', 1, 0, 'R', true);
    $pdf->Cell($box, 8, ($no - 1) . ' Data', 1, 1, 'C', true);

    // Baris Total Bibit
    $pdf->SetX($statX);
    $pdf->SetFillColor(255, 249, 196);
    $pdf->Cell($box, 8, 'Total Bibit:', 1, 0, 'R', true);
    $pdf->Cell($box, 8, number_format($totalBibit) . ' Bibit', 1, 1, 'C', true);


    // =====================
    // OUTPUT
    // =====================
    $filename = 'AgriTrack_Penanaman_' . date('Ymd_His') . '.pdf';
    $pdf->Output($filename, 'D');
    exit;
} catch (Exception $e) {
    error_log('Export PDF Error: ' . $e->getMessage());
    die('Gagal export PDF');
}
