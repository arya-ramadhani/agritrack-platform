<?php
session_start();
require_once '../../config/koneksi.php';

// Cek login
requireLogin();

// Ambil id user login
$id_user = $_SESSION['user_id'];

// PHPSpreadsheet
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {

    // =============================
    // Query Data Penanaman (PER USER)
    // =============================
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

    // =============================
    // Buat Spreadsheet
    // =============================
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Judul
    $sheet->setCellValue('A1', 'LAPORAN DATA PENANAMAN');
    $sheet->mergeCells('A1:H1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A2', 'Tanggal Export: ' . date('d/m/Y H:i'));
    $sheet->mergeCells('A2:H2');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // =============================
    // Header Tabel
    // =============================
    $headers = [
        'No',
        'Nama Lahan',
        'Lokasi',
        'Nama Tanaman',
        'Varietas',
        'Tanggal Tanam',
        'Jumlah Bibit',
        'Status Pertumbuhan'
    ];

    $sheet->fromArray($headers, null, 'A4');

    $headerStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '2E7D32']
        ],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ];

    $sheet->getStyle('A4:H4')->applyFromArray($headerStyle);

    // =============================
    // Isi Data
    // =============================
    $row = 5;
    $no  = 1;

    while ($data = $result->fetch_assoc()) {

        $sheet->setCellValue("A$row", $no++);
        $sheet->setCellValue("B$row", $data['nama_lahan']);
        $sheet->setCellValue("C$row", $data['lokasi']);
        $sheet->setCellValue("D$row", $data['nama_tanaman']);
        $sheet->setCellValue("E$row", $data['varietas']);
        $sheet->setCellValue("F$row", date('d/m/Y', strtotime($data['tanggal_tanam'])));
        $sheet->setCellValue("G$row", $data['jumlah_bibit']);
        $sheet->setCellValue(
            "H$row",
            ucfirst(str_replace('_', ' ', $data['status_pertumbuhan']))
        );

        $sheet->getStyle("A$row:H$row")->getBorders()
              ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $row++;
    }

    // Auto width kolom
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Freeze header
    $sheet->freezePane('A5');

    // =============================
    // Download Excel
    // =============================
    if (ob_get_length()) ob_end_clean();

    $filename = 'Data_Penanaman_' . date('Ymd_His') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo "Gagal export Excel: " . $e->getMessage();
}
