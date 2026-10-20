<?php
session_start();
require_once '../../config/koneksi.php';

// =====================
// CEK LOGIN
// =====================
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$id_user = $_SESSION['user_id'];

// =====================
// MEMORY & TIME
// =====================
ini_set('memory_limit', '256M');
set_time_limit(300);

$type = $_GET['type'] ?? 'lahan';

// Load TCPDF
require_once __DIR__ . '/../../vendor/autoload.php';

try {

    // =====================
    // INIT TCPDF (Landscape)
    // =====================
    $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

    $pdf->SetCreator('AgriTrack System');
    $pdf->SetAuthor('AgriTrack');
    $pdf->SetTitle('Laporan Data ' . ucfirst($type));

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(TRUE, 15);

    $pdf->AddPage();

    // =====================
    // HEADER
    // =====================
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 8, 'AgriTrack - Sistem Pertanian Presisi', 0, 1, 'C');

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 6, 'Laporan Data ' . ucfirst($type), 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, 'Tanggal Export: ' . date('d F Y H:i:s'), 0, 1, 'C');

    $pdf->Ln(10);

    $pageWidth = 297;
    $pdf->SetFont('helvetica', '', 9);

    // =====================
    // SWITCH TYPE
    // =====================
    switch ($type) {

        // =====================
        // LAHAN
        // =====================
        case 'lahan':

            $query = "SELECT * FROM tbl_lahan 
                      WHERE id_user = ?
                      ORDER BY created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id_user);
            $stmt->execute();
            $result = $stmt->get_result();

            $header = ['No','Nama Lahan','Lokasi','Luas (Ha)','Jenis Tanah','Status','Tgl Input'];
            $widths = [12,45,70,25,40,25,40];

            $tableWidth = array_sum($widths);
            $startX = ($pageWidth - $tableWidth) / 2;

            $pdf->SetX($startX);
            $pdf->SetFillColor(76,175,80);
            $pdf->SetTextColor(255);
            $pdf->SetFont('helvetica','B',9);

            foreach ($header as $i => $h) {
                $pdf->Cell($widths[$i],8,$h,1,0,'C',true);
            }
            $pdf->Ln();

            $pdf->SetTextColor(0);
            $pdf->SetFont('helvetica','',9);

            $no=1; $fill=false;
            while($row=$result->fetch_assoc()){
                $pdf->SetX($startX);
                $fill=!$fill;
                $pdf->SetFillColor($fill?242:255,$fill?242:255,$fill?242:255);

                $pdf->Cell($widths[0],7,$no++,1,0,'C',true);
                $pdf->Cell($widths[1],7,substr($row['nama_lahan'],0,35),1,0,'L',true);
                $pdf->Cell($widths[2],7,substr($row['lokasi'],0,50),1,0,'L',true);
                $pdf->Cell($widths[3],7,number_format($row['luas_hektar'],2),1,0,'R',true);
                $pdf->Cell($widths[4],7,substr($row['jenis_tanah'],0,25),1,0,'L',true);
                $pdf->Cell($widths[5],7,ucfirst($row['status_lahan']),1,0,'C',true);
                $pdf->Cell($widths[6],7,date('d/m/Y H:i',strtotime($row['created_at'])),1,0,'C',true);
                $pdf->Ln();
            }
            break;

        // =====================
        // PENANAMAN
        // =====================
        case 'penanaman':

            $query = "
                SELECT p.*, l.nama_lahan, l.lokasi
                FROM tbl_penanaman p
                JOIN tbl_lahan l ON p.id_lahan = l.id_lahan
                WHERE l.id_user = ?
                ORDER BY p.created_at DESC
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i",$id_user);
            $stmt->execute();
            $result = $stmt->get_result();

            $header=['No','Lahan','Lokasi','Tanaman','Varietas','Tgl Tanam','Jml Bibit','Status','Tgl Input'];
            $widths=[12,35,35,30,30,25,20,30,30];

            $tableWidth=array_sum($widths);
            $startX=($pageWidth-$tableWidth)/2;

            $pdf->SetX($startX);
            $pdf->SetFillColor(76,175,80);
            $pdf->SetTextColor(255);
            $pdf->SetFont('helvetica','B',9);

            foreach($header as $i=>$h){
                $pdf->Cell($widths[$i],8,$h,1,0,'C',true);
            }
            $pdf->Ln();

            $pdf->SetTextColor(0);
            $pdf->SetFont('helvetica','',9);

            $no=1; $fill=false;
            while($row=$result->fetch_assoc()){
                $pdf->SetX($startX);
                $fill=!$fill;
                $pdf->SetFillColor($fill?242:255,$fill?242:255,$fill?242:255);

                $pdf->Cell($widths[0],7,$no++,1,0,'C',true);
                $pdf->Cell($widths[1],7,substr($row['nama_lahan'],0,30),1,0,'L',true);
                $pdf->Cell($widths[2],7,substr($row['lokasi'],0,30),1,0,'L',true);
                $pdf->Cell($widths[3],7,substr($row['nama_tanaman'],0,25),1,0,'L',true);
                $pdf->Cell($widths[4],7,substr($row['varietas'],0,25),1,0,'L',true);
                $pdf->Cell($widths[5],7,date('d/m/Y',strtotime($row['tanggal_tanam'])),1,0,'C',true);
                $pdf->Cell($widths[6],7,number_format($row['jumlah_bibit']),1,0,'R',true);
                $pdf->Cell($widths[7],7,ucfirst($row['status_pertumbuhan']),1,0,'C',true);
                $pdf->Cell($widths[8],7,date('d/m/Y',strtotime($row['created_at'])),1,0,'C',true);
                $pdf->Ln();
            }
            break;

        // =====================
        // PANEN
        // =====================
        case 'panen':

            $query = "
                SELECT p.*, pn.nama_tanaman, pn.varietas, l.nama_lahan
                FROM tbl_panen p
                JOIN tbl_penanaman pn ON p.id_penanaman = pn.id_penanaman
                JOIN tbl_lahan l ON pn.id_lahan = l.id_lahan
                WHERE l.id_user = ?
                ORDER BY p.created_at DESC
            ";
            $stmt=$conn->prepare($query);
            $stmt->bind_param("i",$id_user);
            $stmt->execute();
            $result=$stmt->get_result();

            $header=['No','Lahan','Tanaman','Varietas','Tgl Panen','Hasil (kg)','Kualitas','Catatan','Tgl Input'];
            $widths=[12,35,30,30,25,25,20,50,35];

            $tableWidth=array_sum($widths);
            $startX=($pageWidth-$tableWidth)/2;

            $pdf->SetX($startX);
            $pdf->SetFillColor(76,175,80);
            $pdf->SetTextColor(255);
            $pdf->SetFont('helvetica','B',9);

            foreach($header as $i=>$h){
                $pdf->Cell($widths[$i],8,$h,1,0,'C',true);
            }
            $pdf->Ln();

            $pdf->SetTextColor(0);
            $pdf->SetFont('helvetica','',9);

            $no=1; $fill=false; $total=0;
            while($row=$result->fetch_assoc()){
                $pdf->SetX($startX);
                $fill=!$fill;
                $pdf->SetFillColor($fill?242:255,$fill?242:255,$fill?242:255);

                $total+=$row['hasil_panen_kg'];

                $pdf->Cell($widths[0],7,$no++,1,0,'C',true);
                $pdf->Cell($widths[1],7,substr($row['nama_lahan'],0,30),1,0,'L',true);
                $pdf->Cell($widths[2],7,substr($row['nama_tanaman'],0,25),1,0,'L',true);
                $pdf->Cell($widths[3],7,substr($row['varietas'],0,25),1,0,'L',true);
                $pdf->Cell($widths[4],7,date('d/m/Y',strtotime($row['tanggal_panen'])),1,0,'C',true);
                $pdf->Cell($widths[5],7,number_format($row['hasil_panen_kg'],2),1,0,'R',true);
                $pdf->Cell($widths[6],7,'Grade '.$row['kualitas'],1,0,'C',true);
                $pdf->Cell($widths[7],7,substr($row['catatan'],0,40),1,0,'L',true);
                $pdf->Cell($widths[8],7,date('d/m/Y H:i',strtotime($row['created_at'])),1,0,'C',true);
                $pdf->Ln();
            }

            // TOTAL
            $pdf->SetX($startX);
            $pdf->SetFillColor(255,235,59);
            $pdf->SetFont('helvetica','B',9);

            $pdf->Cell(array_sum(array_slice($widths,0,5)),8,'TOTAL HASIL PANEN',1,0,'R',true);
            $pdf->Cell($widths[5],8,number_format($total,2).' kg',1,0,'R',true);
            $pdf->Cell(array_sum(array_slice($widths,6)),8,'',1,0,'C',true);
            break;
    }

    // =====================
    // OUTPUT
    // =====================
    $filename = 'laporan_'.$type.'_'.date('Ymd_His').'.pdf';
    $pdf->Output($filename,'D');

} catch (Exception $e) {
    die('Error: '.$e->getMessage());
}
