<?php
// File: /Proses/DownloadNotaOmsetBulanan.php

if (session_status() == PHP_SESSION_NONE) { session_start(); }
// ... (autentikasi admin jika perlu) ...

require_once dirname(__DIR__) . '/Admin/Koneksi.php';
$fpdfPath = dirname(__DIR__) . '/libs/fpdf/fpdf.php';
if (!file_exists($fpdfPath)) { die("ERROR: File fpdf.php tidak ditemukan."); }
require_once $fpdfPath;

$selected_month_year = $_GET['bulan'] ?? date('Y-m');
if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $selected_month_year)) { die("Format bulan tidak valid."); }

$nama_toko = "Ayam Bakar Mang Oman"; /* ... info toko ... */
$telepon_toko = "0896-3015-2631";
$alamat_toko = "Jln. Jendral Sudirman No. 45 RT 03 RW 07";


$orders_in_month = [];
$total_omset_bulanan_pdf = 0;
$query_error = null;

if (!$conn || $conn->connect_error) { die("Koneksi DB gagal."); }

$sql_orders = "SELECT id, pelanggan, nohp, pesanan, jumlah_pesan, total_harga, waktu_order_dibuat FROM tb_order WHERE DATE_FORMAT(waktu_order_dibuat, '%Y-%m') = ? AND LOWER(pembayaran) IN ('ya', 'success', 'settlement') ORDER BY waktu_order_dibuat ASC";
$stmt_orders = $conn->prepare($sql_orders);
if ($stmt_orders) {
    $stmt_orders->bind_param("s", $selected_month_year);
    if ($stmt_orders->execute()) {
        $result_orders = $stmt_orders->get_result();
        while ($row = $result_orders->fetch_assoc()) {
            $row['total_harga_numeric'] = (float)preg_replace('/[^0-9.]/', '', $row['total_harga']);
            $orders_in_month[] = $row;
            $total_omset_bulanan_pdf += $row['total_harga_numeric'];
        }
    } else { $query_error = "Error execute orders: " . $stmt_orders->error; }
    $stmt_orders->close();
} else { $query_error = "Error prepare orders: " . $conn->error; }

if ($query_error) { /* ... error handling ... */ }

class PDF_Bulanan extends FPDF
{
    function Header()
    {
        global $nama_toko, $alamat_toko, $telepon_toko, $selected_month_year;
        $this->SetFont('Arial','B',15); $this->Cell(0,10,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$nama_toko),0,1,'C');
        $this->SetFont('Arial','',9); $this->Cell(0,5,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$alamat_toko),0,1,'C'); $this->Cell(0,5,'Telp: '.$telepon_toko,0,1,'C'); $this->Ln(5);
        $this->SetFont('Arial','B',12); $this->Cell(0,10,'Laporan Omset Bulanan',0,1,'C');
        $this->SetFont('Arial','',10); $this->Cell(0,5,'Bulan: '.date('F Y', strtotime($selected_month_year.'-01')),0,1,'C'); $this->Ln(10);
    }
    function Footer() { $this->SetY(-15); $this->SetFont('Arial','I',8); $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C'); $this->SetX(-50); $this->Cell(0,10,'Dicetak: '.date('d/m/Y H:i:s'),0,0,'R'); }
    function FancyTable($header, $data)
    {
        $this->SetFillColor(230,230,230); $this->SetTextColor(0); $this->SetDrawColor(128,128,128);
        $this->SetLineWidth(.3); $this->SetFont('Arial','B',7);
        $w = array(8, 18, 15, 30, 25, 44, 15, 35); // No, Tgl, ID, Pelanggan, No.HP, Pesanan, Jml, Total
        for($i=0;$i<count($header);$i++) $this->Cell($w[$i],7,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$header[$i]),1,0,'C',true);
        $this->Ln();
        $this->SetFillColor(245,245,245); $this->SetTextColor(0); $this->SetFont('Arial','',7);
        $fill = false; $nomor = 1; global $total_omset_bulanan_pdf;

        if (empty($data)) { $this->Cell(array_sum($w),10,'Tidak ada transaksi untuk bulan ini.',1,1,'C'); }
        else {
            foreach($data as $row) {
                $current_y_start = $this->GetY(); $cell_height = 6;
                $this->Cell($w[0],$cell_height,$nomor++,'LR',0,'C',$fill);
                $this->Cell($w[1],$cell_height,date('d/m/y', strtotime($row['waktu_order_dibuat'])),'LR',0,'C',$fill); // Tanggal
                $this->Cell($w[2],$cell_height,$row['id'],'LR',0,'C',$fill);
                $x_pelanggan = $this->GetX(); $this->MultiCell($w[3],$cell_height,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$row['pelanggan']),0,'L',$fill); $this->SetXY($x_pelanggan + $w[3], $current_y_start);
                $this->Cell($w[4],$cell_height,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$row['nohp']),0,0,'L',$fill);
                $x_pesanan = $this->GetX(); $this->MultiCell($w[5],$cell_height,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',strip_tags(str_replace(array("<br />","<br>","\r\n", "\r", "\n"),"; ",$row['pesanan']))),0,'L',$fill); $max_y_after_multicell = $this->GetY(); $this->SetXY($x_pesanan + $w[5], $current_y_start);
                $this->Cell($w[6],$cell_height,htmlspecialchars($row['jumlah_pesan']),0,0,'R',$fill);
                $this->Cell($w[7],$cell_height,'Rp '.number_format($row['total_harga_numeric'],0,',','.'),0,0,'R',$fill);
                $row_height = max($max_y_after_multicell, $this->GetY()) - $current_y_start; if ($row_height < 6) $row_height = 6;
                $this->SetXY($this->lMargin, $current_y_start);
                for($k=0; $k < count($w); $k++){ $this->Cell($w[$k],$row_height,'','LR',0,'C',$fill); } // Border untuk semua
                $this->Ln($row_height); $fill = !$fill;
            }
        }
        $this->Cell(array_sum($w),0,'','T'); $this->Ln();
        $this->SetFont('Arial','B',9);
        $this->Cell(array_sum($w) - $w[count($w)-1] , 8, 'TOTAL OMSET BULANAN', 1, 0, 'R', true);
        $this->Cell($w[count($w)-1], 8, 'Rp ' . number_format($total_omset_bulanan_pdf, 0, ',', '.'), 1, 1, 'R', true);
    }
}

$pdf = new PDF_Bulanan('P','mm','A4'); $pdf->AliasNbPages(); $pdf->AddPage();
$header = array('No.', 'Tanggal', 'ID', 'Pelanggan', 'No. HP', 'Pesanan', 'Jml', 'Total');
$pdf->FancyTable($header, $orders_in_month);
$nama_file_pdf = "Nota_Omset_Bulanan_" . str_replace('-', '', $selected_month_year) . ".pdf";
$pdf->Output('I', $nama_file_pdf);
if (isset($conn) && $conn instanceof mysqli) { $conn->close(); } exit;
?>