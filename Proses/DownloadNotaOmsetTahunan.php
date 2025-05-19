<?php
// File: /Proses/DownloadNotaOmsetTahunan.php

if (session_status() == PHP_SESSION_NONE) { session_start(); }
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { die("Akses ditolak."); }

require_once dirname(__DIR__) . '/Admin/Koneksi.php';
require_once dirname(__DIR__) . '/vendor/autoload.php'; // Atau path ke FPDF manual

// Ambil parameter tahun dari GET
$selected_year = $_GET['tahun'] ?? date('Y');
if (!preg_match("/^[0-9]{4}$/", $selected_year)) {
    die("Format tahun tidak valid (YYYY).");
}

$nama_toko = "Ayam Bakar Mang Oman";
$alamat_toko = "Jln. Jendral Sudirman No. 45 RT 03 RW 07";
$telepon_toko = "0896-3015-2631";

$orders_in_year = [];
$total_omset_tahunan_pdf = 0;
$query_error = null;

if (!$conn || $conn->connect_error) { die("Koneksi database gagal: " . ($conn->connect_error ?? 'Tidak diketahui')); }

// Ambil data order untuk tahun yang dipilih
$sql_orders = "SELECT id, pelanggan, pesanan, jumlah_pesan, total_harga, waktu_order_dibuat
               FROM tb_order
               WHERE DATE_FORMAT(waktu_order_dibuat, '%Y') = ?
                 AND LOWER(pembayaran) IN ('ya', 'success', 'settlement')
               ORDER BY waktu_order_dibuat ASC";
$stmt_orders = $conn->prepare($sql_orders);
if ($stmt_orders) {
    $stmt_orders->bind_param("s", $selected_year);
    if ($stmt_orders->execute()) {
        $result_orders = $stmt_orders->get_result();
        while ($row = $result_orders->fetch_assoc()) {
            $row['total_harga_numeric'] = (float)preg_replace('/[^0-9.]/', '', $row['total_harga']);
            $orders_in_year[] = $row;
            $total_omset_tahunan_pdf += $row['total_harga_numeric'];
        }
    } else { $query_error = "Error execute orders: " . $stmt_orders->error; }
    $stmt_orders->close();
} else { $query_error = "Error prepare orders: " . $conn->error; }

if ($query_error) { error_log("DownloadNotaOmsetTahunan.php SQL Error: " . $query_error); die("Kesalahan data order: " . htmlspecialchars($query_error)); }

// --- Pembuatan PDF menggunakan FPDF ---
$fpdfPath = dirname(__DIR__) . '/libs/fpdf/fpdf.php';
if (!file_exists($fpdfPath)) { die("ERROR: File fpdf.php tidak ditemukan."); }
require_once $fpdfPath;

class PDF_Tahunan extends FPDF
{
    function Header()
    {
        global $nama_toko, $alamat_toko, $telepon_toko, $selected_year;
        $this->SetFont('Arial','B',15);
        $this->Cell(0,10,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$nama_toko),0,1,'C');
        $this->SetFont('Arial','',9);
        $this->Cell(0,5,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$alamat_toko),0,1,'C');
        $this->Cell(0,5,'Telp: ' . $telepon_toko,0,1,'C');
        $this->Ln(5);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Laporan Omset Tahunan',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,5,'Tahun: ' . $selected_year,0,1,'C');
        $this->Ln(10);
    }
    function Footer() { /* ... (sama seperti sebelumnya) ... */ }
    function FancyTable($header, $data) { /* ... (sama seperti sebelumnya, gunakan $total_omset_tahunan_pdf dan sesuaikan header jika perlu) ... */
        // ... (Anda bisa menyederhanakan kolom, mungkin hanya menampilkan rangkuman per bulan atau per tanggal)
        // Untuk contoh ini, saya akan tetap menampilkan detail order harian dalam tahun tersebut
        $this->SetFillColor(230,230,230); $this->SetTextColor(0); $this->SetDrawColor(128,128,128);
        $this->SetLineWidth(.3); $this->SetFont('','B',8);
        $w = array(10, 25, 25, 35, 50, 20, 25); // No, Tgl-Bln, ID, Pelanggan, Pesanan, Jml, Total
        for($i=0;$i<count($header);$i++) $this->Cell($w[$i],7,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$header[$i]),1,0,'C',true);
        $this->Ln();
        $this->SetFillColor(245,245,245); $this->SetTextColor(0); $this->SetFont('','',8);
        $fill = false; $nomor = 1;

        if (empty($data)) {
            $this->Cell(array_sum($w),10,'Tidak ada transaksi penjualan untuk tahun ini.',1,1,'C');
        } else {
            foreach($data as $row) {
                $this->Cell($w[0],6,$nomor++,'LR',0,'C',$fill);
                $this->Cell($w[1],6,date('d/m/Y', strtotime($row['waktu_order_dibuat'])),'LR',0,'C',$fill); // Tampilkan tanggal lengkap
                $this->Cell($w[2],6,$row['id'],'LR',0,'C',$fill);
                $x = $this->GetX(); $y = $this->GetY(); $this->MultiCell($w[3],6,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$row['pelanggan']),'LR','L',$fill); $this->SetXY($x + $w[3], $y);
                $x = $this->GetX(); $y = $this->GetY(); $this->MultiCell($w[4],6,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',strip_tags(str_replace("<br />","\n",$row['pesanan']))),'LR','L',$fill); $this->SetXY($x + $w[4], $y);
                $this->Cell($w[5],6,$row['jumlah_pesan'],'LR',0,'R',$fill);
                $this->Cell($w[6],6,'Rp '.number_format($row['total_harga_numeric'],0,',','.'),'LR',0,'R',$fill);
                $this->Ln(); $fill = !$fill;
            }
        }
        $this->Cell(array_sum($w),0,'','T'); $this->Ln();
        $this->SetFont('','B',9);
        $this->Cell(array_sum($w) - $w[count($w)-1] , 8, 'TOTAL OMSET TAHUNAN', 1, 0, 'R', true);
        $this->Cell($w[count($w)-1], 8, 'Rp ' . number_format($GLOBALS['total_omset_tahunan_pdf'], 0, ',', '.'), 1, 1, 'R', true);
    }
}

$pdf = new PDF_Tahunan('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);
$header = array('No.', 'Tanggal', 'ID Order', 'Pelanggan', 'Pesanan', 'Jml', 'Total Harga'); // Header untuk tahunan
$pdf->FancyTable($header, $orders_in_year);
$nama_file_pdf = "Nota_Omset_Tahunan_" . $selected_year . ".pdf";
$pdf->Output('I', $nama_file_pdf);

if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
exit;
?>