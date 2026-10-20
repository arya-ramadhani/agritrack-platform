<?php
session_start();
require_once '../../config/koneksi.php';

requireLogin();
$id_user = $_SESSION['user_id'];

ini_set('memory_limit', '512M');
set_time_limit(600);

$type = $_GET['type'] ?? 'lahan';

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

try {

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    /* ================= STYLE ================= */
    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4CAF50']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];

    $dataStyle = [
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];

    /* ================= TITLE ================= */
    $sheet->setCellValue('A1', 'AgriTrack - Sistem Pertanian Presisi');
    $sheet->setCellValue('A2', 'Laporan Data ' . ucfirst($type));
    $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d/m/Y H:i:s'));

    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $startRow = 5;

    /* ================= SWITCH ================= */
    switch ($type) {

        /* =====================================================
         * LAHAN
         * ===================================================== */
        case 'lahan':

            $headers = ['No','Nama Lahan','Lokasi','Luas (Ha)','Jenis Tanah','Status','Tanggal Input'];
            $lastCol = 'G';

            $sheet->mergeCells("A1:$lastCol"."1");
            $sheet->mergeCells("A2:$lastCol"."2");
            $sheet->mergeCells("A3:$lastCol"."3");

            $sheet->fromArray($headers, null, "A$startRow");
            $sheet->getStyle("A$startRow:$lastCol$startRow")->applyFromArray($headerStyle);
            $sheet->getRowDimension($startRow)->setRowHeight(32);

            /* === QUERY === */
            $stmt = $conn->prepare(
                "SELECT * FROM tbl_lahan 
                 WHERE id_user = ?
                 ORDER BY created_at DESC"
            );
            $stmt->bind_param("i", $id_user);
            $stmt->execute();
            $result = $stmt->get_result();

            $row = $startRow + 1;
            $no  = 1;

            while ($d = $result->fetch_assoc()) {

                $sheet->setCellValue("A$row", $no++);
                $sheet->setCellValue("B$row", $d['nama_lahan']);
                $sheet->setCellValue("C$row", $d['lokasi']);
                $sheet->setCellValue("D$row", $d['luas_hektar']);
                $sheet->setCellValue("E$row", $d['jenis_tanah']);
                $sheet->setCellValue("F$row", ucfirst($d['status_lahan']));
                $sheet->setCellValue("G$row", date('d/m/Y H:i', strtotime($d['created_at'])));

                $sheet->getStyle("D$row")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("A$row:D$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A$row:$lastCol$row")->applyFromArray($dataStyle);

                if ($no % 2 == 0) {
                    $sheet->getStyle("A$row:$lastCol$row")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F7F7F7');
                }

                $sheet->getRowDimension($row)->setRowHeight(-1);
                $row++;
            }

            /* === LEBAR KOLOM (SANGAT RAPI) === */
            $widths = [
                'A' => 6,   // No
                'B' => 26,  // Nama Lahan
                'C' => 40,  // Lokasi
                'D' => 14,  // Luas
                'E' => 20,  // Jenis Tanah
                'F' => 14,  // Status
                'G' => 22   // Tanggal
            ];
            foreach ($widths as $col => $w) {
                $sheet->getColumnDimension($col)->setWidth($w);
            }
            break;

        /* =====================================================
         * PENANAMAN
         * ===================================================== */
        case 'penanaman':

            $headers = ['No','Nama Lahan','Lokasi','Tanaman','Varietas','Tgl Tanam','Bibit','Status','Tgl Input'];
            $lastCol = 'I';

            $sheet->mergeCells("A1:$lastCol"."1");
            $sheet->mergeCells("A2:$lastCol"."2");
            $sheet->mergeCells("A3:$lastCol"."3");

            $sheet->fromArray($headers, null, "A$startRow");
            $sheet->getStyle("A$startRow:$lastCol$startRow")->applyFromArray($headerStyle);

            $stmt = $conn->prepare("
                SELECT p.*, l.nama_lahan, l.lokasi
                FROM tbl_penanaman p
                JOIN tbl_lahan l ON p.id_lahan = l.id_lahan
                WHERE l.id_user = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->bind_param("i", $id_user);
            $stmt->execute();
            $result = $stmt->get_result();

            $row = $startRow + 1;
            $no  = 1;

            while ($d = $result->fetch_assoc()) {

                $sheet->setCellValue("A$row", $no++);
                $sheet->setCellValue("B$row", $d['nama_lahan']);
                $sheet->setCellValue("C$row", $d['lokasi']);
                $sheet->setCellValue("D$row", $d['nama_tanaman']);
                $sheet->setCellValue("E$row", $d['varietas']);
                $sheet->setCellValue("F$row", date('d/m/Y', strtotime($d['tanggal_tanam'])));
                $sheet->setCellValue("G$row", $d['jumlah_bibit']);
                $sheet->setCellValue("H$row", ucfirst(str_replace('_',' ',$d['status_pertumbuhan'])));
                $sheet->setCellValue("I$row", date('d/m/Y H:i', strtotime($d['created_at'])));

                $sheet->getStyle("A$row:$lastCol$row")->applyFromArray($dataStyle);
                $sheet->getRowDimension($row)->setRowHeight(-1);
                $row++;
            }

            $widths = [
                'A'=>6,'B'=>24,'C'=>38,'D'=>18,'E'=>18,'F'=>14,'G'=>10,'H'=>20,'I'=>22
            ];
            foreach ($widths as $c=>$w) $sheet->getColumnDimension($c)->setWidth($w);
            break;

        /* =====================================================
         * PANEN
         * ===================================================== */
        case 'panen':

            $headers = ['No','Nama Lahan','Tanaman','Varietas','Tgl Panen','Hasil (Kg)','Kualitas','Catatan','Tgl Input'];
            $lastCol = 'I';

            $sheet->mergeCells("A1:$lastCol"."1");
            $sheet->mergeCells("A2:$lastCol"."2");
            $sheet->mergeCells("A3:$lastCol"."3");

            $sheet->fromArray($headers, null, "A$startRow");
            $sheet->getStyle("A$startRow:$lastCol$startRow")->applyFromArray($headerStyle);

            $stmt = $conn->prepare("
                SELECT p.*, pn.nama_tanaman, pn.varietas, l.nama_lahan
                FROM tbl_panen p
                JOIN tbl_penanaman pn ON p.id_penanaman = pn.id_penanaman
                JOIN tbl_lahan l ON pn.id_lahan = l.id_lahan
                WHERE l.id_user = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->bind_param("i", $id_user);
            $stmt->execute();
            $result = $stmt->get_result();

            $row = $startRow + 1;
            $no = 1;

            while ($d = $result->fetch_assoc()) {

                $sheet->setCellValue("A$row", $no++);
                $sheet->setCellValue("B$row", $d['nama_lahan']);
                $sheet->setCellValue("C$row", $d['nama_tanaman']);
                $sheet->setCellValue("D$row", $d['varietas']);
                $sheet->setCellValue("E$row", date('d/m/Y', strtotime($d['tanggal_panen'])));
                $sheet->setCellValue("F$row", $d['hasil_panen_kg']);
                $sheet->setCellValue("G$row", 'Grade '.$d['kualitas']);
                $sheet->setCellValue("H$row", $d['catatan']);
                $sheet->setCellValue("I$row", date('d/m/Y H:i', strtotime($d['created_at'])));

                $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("A$row:$lastCol$row")->applyFromArray($dataStyle);
                $sheet->getRowDimension($row)->setRowHeight(-1);
                $row++;
            }

            $widths = [
                'A'=>6,'B'=>24,'C'=>20,'D'=>18,'E'=>14,'F'=>14,'G'=>12,'H'=>40,'I'=>22
            ];
            foreach ($widths as $c=>$w) $sheet->getColumnDimension($c)->setWidth($w);
            break;
    }

    $sheet->freezePane('A6');

    if (ob_get_length()) ob_end_clean();

    $filename = 'AgriTrack_' . ucfirst($type) . '_' . date('Ymd_His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: max-age=0');

    (new Xlsx($spreadsheet))->save('php://output');
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo 'Export gagal: '.$e->getMessage();
}
