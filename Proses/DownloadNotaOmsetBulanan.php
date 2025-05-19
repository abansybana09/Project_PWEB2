<?php
// File: /Proses/DownloadNotaOmsetBulanan.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { die("Akses ditolak."); }

require_once dirname(__DIR__) . '/Admin/Koneksi.php';
require_once dirname(__DIR__) . '/vendor/autoload.php'; // Untuk FPDF jika diinstal via Composer atau mPDF

// Ambil parameter bulan dari GET
$selected_month_year = $_GET['bulan'] ?? date('Y-m'); // Format YYYY-MM
if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/", $selected_month_year)) {
    die("Format bulan tidak valid (YYYY-MM).");
}

$nama_toko = "Ayam Bakar Mang Oman";
$alamat_toko = "Jln. Jendral Sudirman No. 45 RT 03 RW 07";
$telepon_toko = "0896-3015-2631";

$orders_in_month = [];
$total_omset_bulanan_pdf = 0;
$query_error = null;

if (!$conn || $conn->connect_error) { die("Koneksi database gagal: " . ($conn->connect_error ?? 'Tidak diketahui')); }

// Ambil data order untuk bulan yang dipilih
$sql_orders = "SELECT id, pelanggan, pesanan, jumlah_pesan, total_harga, waktu_order_dibuat
               FROM tb_order
               WHERE DATE_FORMAT(waktu_order_dibuat, '%Y-%m') = ?
                 AND LOWER(pembayaran) IN ('ya', 'success', 'settlement')
               ORDER BY waktu_order_dibuat ASC";
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

if ($query_error) { error_log("DownloadNotaOmsetBulanan.php SQL Error: " . $query_error); die("Kesalahan data order: " . htmlspecialchars($query_error)); }

// --- Pembuatan PDF menggunakan FPDF (Sama seperti DownloadNotaOmsetHarian.php, hanya judul dan data yang berbeda) ---
$fpdfPath = dirname(__DIR__) . '/libs/fpdf/fpdf.php'; // Sesuaikan jika path FPDF Anda berbeda
if (!file_exists($fpdfPath)) { die("ERROR: File fpdf.php tidak ditemukan."); }
require_once $fpdfPath;

class PDF_Bulanan extends FPDF
{
    function Header()
    {
        global $nama_toko, $alamat_toko, $telepon_toko, $selected_month_year;
        $this->SetFont('Arial','B',15);
        $this->Cell(0,10,iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $nama_toko),0,1,'C');
        $this->SetFont('Arial','',9);
        $this->Cell(0,5,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$alamat_toko),0,1,'C');
        $this->Cell(0,5,'Telp: ' . $telepon_toko,0,1,'C');
        $this->Ln(5);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Laporan Omset Bulanan',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,5,'Bulan: ' . date('F Y', strtotime($selected_month_year . '-01')),0,1,'C');
        $this->Ln(10);
    }
    function Footer() { /* ... (sama seperti sebelumnya) ... */ }
    function FancyTable($header, $data) { /* ... (sama seperti sebelumnya, pastikan menggunakan $total_omset_bulanan_pdf untuk total) ... */
        // ... (bagian header tabel sama) ...
        // Di dalam loop data:
        // ... (Cell untuk nomor, waktu (HANYA TANGGAL), ID, Pelanggan, Pesanan, Jml, Total Harga) ...
        // Contoh untuk waktu (hanya tanggal):
        // $this->Cell($w[1],6,date('d/m/Y', strtotime($row['waktu_order_dibuat'])),'LR',0,'C',$fill);

        // Di bagian Total:
        // $this->Cell($w[0]+...+$w[5], 8, 'TOTAL OMSET BULANAN', 1, 0, 'R', true);
        // $this->Cell($w[6], 8, 'Rp ' . number_format($GLOBALS['total_omset_bulanan_pdf'], 0, ',', '.'), 1, 1, 'R', true);
        // (Pastikan $GLOBALS['total_omset_bulanan_pdf'] bisa diakses atau passing sebagai parameter)

        // Untuk mempersingkat, saya akan salin dari sebelumnya dan Anda sesuaikan
        $this->SetFillColor(230,230,230); $this->SetTextColor(0); $this->SetDrawColor(128,128,128);
        $this->SetLineWidth(.3); $this->SetFont('','B',8);
        $w = array(10, 20, 25, 40, 50, 20, 25); // No, Tgl, ID, Pelanggan, Pesanan, Jml, Total
        for($i=0;$i<count($header);$i++) $this->Cell($w[$i],7,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$header[$i]),1,0,'C',true);
        $this->Ln();
        $this->SetFillColor(245,245,245); $this->SetTextColor(0); $this->SetFont('','',8);
        $fill = false; $nomor = 1;

        if (empty($data)) {
            $this->Cell(array_sum($w),10,'Tidak ada transaksi penjualan untuk bulan ini.',1,1,'C');
        } else {
            foreach($data as $row) {
                $this->Cell($w[0],6,$nomor++,'LR',0,'C',$fill);
                $this->Cell($w[1],6,date('d/m/y H:i', strtotime($row['waktu_order_dibuat'])),'LR',0,'C',$fill); // Tampilkan juga waktu
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
        $this->Cell(array_sum($w) - $w[count($w)-1] , 8, 'TOTAL OMSET BULANAN', 1, 0, 'R', true);
        $this->Cell($w[count($w)-1], 8, 'Rp ' . number_format($GLOBALS['total_omset_bulanan_pdf'], 0, ',', '.'), 1, 1, 'R', true);
    }
}

$pdf = new PDF_Bulanan('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);
$header = array('No.', 'Waktu', 'ID Order', 'Pelanggan', 'Pesanan', 'Jml', 'Total Harga'); // Sesuaikan header jika perlu
$pdf->FancyTable($header, $orders_in_month);
$nama_file_pdf = "Nota_Omset_Bulanan_" . str_replace('-', '', $selected_month_year) . ".pdf";
$pdf->Output('I', $nama_file_pdf);

if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
exit;
?>