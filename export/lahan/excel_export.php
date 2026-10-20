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
ini_set('memory_limit', '512M');
set_time_limit(600);

// Load PHPSpreadsheet
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

try {

    // ================================
    // BUAT SPREADSHEET
    // ================================
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $spreadsheet->getProperties()
        ->setCreator('AgriTrack System')
        ->setLastModifiedBy('AgriTrack System')
        ->setTitle('Laporan Data Lahan')
        ->setSubject('Laporan Pertanian Presisi')
        ->setDescription('Export data lahan user')
        ->setCategory('Laporan');

    // ================================
    // STYLE
    // ================================
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4CAF50']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ]
    ];

    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ];

    // ================================
    // TITLE
    // ================================
    $sheet->setCellValue('A1', 'AgriTrack - Sistem Pertanian Presisi');
    $sheet->setCellValue('A2', 'Laporan Data Lahan Pertanian');
    $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i:s'));

    $sheet->mergeCells('A1:F1');
    $sheet->mergeCells('A2:F2');
    $sheet->mergeCells('A3:F3');

    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // ================================
    // HEADER TABLE
    // ================================
    $startRow = 5;
    $headers = ['No', 'Nama Lahan', 'Lokasi', 'Luas (Ha)', 'Jenis Tanah', 'Status'];
    $sheet->fromArray($headers, null, 'A' . $startRow);
    $sheet->getStyle("A{$startRow}:F{$startRow}")->applyFromArray($headerStyle);
    $sheet->getRowDimension($startRow)->setRowHeight(30);

    // ================================
    // QUERY DATA (FILTER USER LOGIN)
    // ================================
    $stmt = $conn->prepare("
        SELECT nama_lahan, lokasi, luas_hektar, jenis_tanah, status_lahan
        FROM tbl_lahan
        WHERE id_user = ?
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    // ================================
    // ISI DATA
    // ================================
    $row = $startRow + 1;
    $no = 1;
    $totalLuas = 0;
    $aktif = 0;
    $tidakAktif = 0;

    if ($result->num_rows > 0) {

        while ($data = $result->fetch_assoc()) {

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $data['nama_lahan']);
            $sheet->setCellValue("C{$row}", $data['lokasi']);
            $sheet->setCellValue("D{$row}", $data['luas_hektar']);
            $sheet->setCellValue("E{$row}", $data['jenis_tanah']);
            $sheet->setCellValue("F{$row}", ucfirst($data['status_lahan']));

            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($dataStyle);

            if ($data['status_lahan'] === 'aktif') {
                $sheet->getStyle("F{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('28A745');
                $aktif++;
            } else {
                $sheet->getStyle("F{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('6C757D');
                $tidakAktif++;
            }

            $sheet->getStyle("F{$row}")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');

            $totalLuas += (float)$data['luas_hektar'];
            $row++;
            $no++;
        }

    } else {
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'Tidak ada data lahan');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    $stmt->close();

    // ================================
    // SUMMARY
    // ================================
    $row += 2;

    $sheet->mergeCells("A{$row}:C{$row}");
    $sheet->setCellValue("A{$row}", 'TOTAL LAHAN');
    $sheet->mergeCells("D{$row}:F{$row}");
    $sheet->setCellValue("D{$row}", ($no - 1) . ' Lahan');

    $row++;
    $sheet->mergeCells("A{$row}:C{$row}");
    $sheet->setCellValue("A{$row}", 'TOTAL LUAS');
    $sheet->mergeCells("D{$row}:F{$row}");
    $sheet->setCellValue("D{$row}", number_format($totalLuas, 2) . ' Ha');

    // ================================
    // SET COLUMN WIDTH
    // ================================
    $sheet->getColumnDimension('A')->setWidth(8);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(40);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(20);
    $sheet->getColumnDimension('F')->setWidth(15);

    // Freeze header
    $sheet->freezePane('A6');

    // ================================
    // OUTPUT FILE
    // ================================
    $filename = 'AgriTrack_Lahan_' . date('Ymd_His') . '.xlsx';

    if (ob_get_length()) ob_end_clean();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    exit;

} catch (Exception $e) {
    if (ob_get_length()) ob_end_clean();
    http_response_code(500);
    echo 'Error Export: ' . $e->getMessage();
}
