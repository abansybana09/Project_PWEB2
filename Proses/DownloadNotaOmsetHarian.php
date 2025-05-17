<?php
// File: /Proses/DownloadNotaOmsetHarian.php (Menggunakan FPDF)

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pastikan hanya admin yang bisa akses (implementasi login Anda)
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     die("Akses ditolak.");
// }

require_once dirname(__DIR__) . '/Admin/Koneksi.php'; // Koneksi Database

// ==================================================================
// LOAD FPDF
// ==================================================================
// Cara 1: Jika unduh manual dan diletakkan di libs/fpdf/
$fpdfPath = dirname(__DIR__) . '/libs/fpdf/fpdf.php';
if (!file_exists($fpdfPath)) {
    die("ERROR: File fpdf.php tidak ditemukan. Pastikan FPDF sudah diunduh dan diletakkan di folder yang benar (misal: PROJECR2/libs/fpdf/).");
}
require_once $fpdfPath;

// Cara 2: Jika instal via Composer (composer require setasign/fpdf)
// Maka baris di atas tidak perlu, cukup pastikan vendor/autoload.php sudah di-include.
// require_once dirname(__DIR__) . '/vendor/autoload.php'; // Jika FPDF diinstal via Composer
// ==================================================================


// Ambil tanggal dari parameter GET
$selected_date = $_GET['tanggal'] ?? date('Y-m-d');
if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $selected_date)) {
    die("Format tanggal tidak valid.");
}

$nama_toko = "Ayam Bakar Mang Oman";
$alamat_toko = "Jln. Jendral Sudirman No. 45 RT 03 RW 07";
$telepon_toko = "0896-3015-2631";

$orders_on_date = [];
$total_omset_harian_pdf = 0;
$query_error = null;

if (!$conn || $conn->connect_error) {
    die("Koneksi database gagal: " . ($conn->connect_error ?? 'Tidak diketahui'));
}

// Ambil data order
$sql_orders = "SELECT id, pelanggan, pesanan, jumlah_pesan, total_harga, waktu_order_dibuat
               FROM tb_order
               WHERE DATE(waktu_order_dibuat) = ?
                 AND LOWER(pembayaran) IN ('ya', 'success', 'settlement')
               ORDER BY waktu_order_dibuat ASC";
$stmt_orders = $conn->prepare($sql_orders);
if ($stmt_orders) {
    $stmt_orders->bind_param("s", $selected_date);
    if ($stmt_orders->execute()) {
        $result_orders = $stmt_orders->get_result();
        while ($row = $result_orders->fetch_assoc()) {
            $row['total_harga_numeric'] = (float)preg_replace('/[^0-9.]/', '', $row['total_harga']);
            $orders_on_date[] = $row;
            $total_omset_harian_pdf += $row['total_harga_numeric'];
        }
    } else { $query_error = "Error execute orders: " . $stmt_orders->error; }
    $stmt_orders->close();
} else { $query_error = "Error prepare orders: " . $conn->error; }

if ($query_error) { error_log("DownloadNotaOmsetHarian.php SQL Error: " . $query_error); die("Terjadi kesalahan saat mengambil data order: " . htmlspecialchars($query_error)); }

// ==================================================================
// MEMBUAT PDF DENGAN FPDF
// ==================================================================

class PDF extends FPDF
{
    // Page header
    function Header()
    {
        global $nama_toko, $alamat_toko, $telepon_toko, $selected_date;
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Move to the right
        // $this->Cell(80);
        // Title
        $this->Cell(0,10,iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $nama_toko),0,1,'C'); // iconv untuk karakter non-latin
        $this->SetFont('Arial','',9);
        $this->Cell(0,5,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$alamat_toko),0,1,'C');
        $this->Cell(0,5,'Telp: ' . $telepon_toko,0,1,'C');
        $this->Ln(5); // Line break

        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Laporan Omset Harian',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,5,'Tanggal: ' . date('d F Y', strtotime($selected_date)),0,1,'C');
        // Line break
        $this->Ln(10);
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
        $this->SetX(-50); // Posisi X untuk tanggal cetak
        $this->Cell(0,10,'Dicetak: '.date('d/m/Y H:i:s'),0,0,'R');
    }

    // Colored table
    function FancyTable($header, $data)
    {
        // Colors, line width and bold font
        $this->SetFillColor(230,230,230); // Abu-abu muda
        $this->SetTextColor(0);
        $this->SetDrawColor(128,128,128); // Abu-abu border
        $this->SetLineWidth(.3);
        $this->SetFont('','B',8); // Font bold untuk header
        // Header
        // Lebar kolom (total 190 untuk A4 dengan margin 10 kiri-kanan)
        $w = array(10, 20, 25, 40, 50, 20, 25); // No, Waktu, ID, Pelanggan, Pesanan, Jml, Total
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$header[$i]),1,0,'C',true);
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(245,245,245);
        $this->SetTextColor(0);
        $this->SetFont('','',8); // Font reguler untuk data
        // Data
        $fill = false;
        $nomor = 1;
        global $total_omset_harian_pdf; // Ambil total omset

        if (empty($data)) {
            $this->Cell(array_sum($w),10,'Tidak ada transaksi penjualan untuk tanggal ini.',1,1,'C');
        } else {
            foreach($data as $row)
            {
                $this->Cell($w[0],6,$nomor++,'LR',0,'C',$fill);
                $this->Cell($w[1],6,date('H:i', strtotime($row['waktu_order_dibuat'])),'LR',0,'C',$fill);
                $this->Cell($w[2],6,$row['id'],'LR',0,'C',$fill);
                // MultiCell untuk pelanggan dan pesanan jika bisa panjang
                $x = $this->GetX(); $y = $this->GetY();
                $this->MultiCell($w[3],6,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$row['pelanggan']),'LR','L',$fill);
                $this->SetXY($x + $w[3], $y);
                $x = $this->GetX(); $y = $this->GetY();
                $this->MultiCell($w[4],6,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',strip_tags(str_replace("<br />","\n",$row['pesanan']))),'LR','L',$fill); // Hapus br dan ganti dengan newline
                $this->SetXY($x + $w[4], $y);

                $this->Cell($w[5],6,$row['jumlah_pesan'],'LR',0,'R',$fill);
                $this->Cell($w[6],6,'Rp '.number_format($row['total_harga_numeric'],0,',','.'),'LR',0,'R',$fill);
                $this->Ln();
                $fill = !$fill;
            }
        }
        // Closing line
        $this->Cell(array_sum($w),0,'','T');
        $this->Ln();

        // Total Omset
        $this->SetFont('','B',9);
        $this->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5], 8, 'TOTAL OMSET HARIAN', 1, 0, 'R', true);
        $this->Cell($w[6], 8, 'Rp ' . number_format($total_omset_harian_pdf, 0, ',', '.'), 1, 1, 'R', true);
    }
}

// Instanciation of inherited class
$pdf = new PDF('P','mm','A4'); // P = Portrait, mm = milimeter, A4 = ukuran kertas
$pdf->AliasNbPages(); // Untuk nomor halaman {nb}
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

// Table Header
$header = array('No.', 'Waktu', 'ID Order', 'Pelanggan', 'Pesanan', 'Jml', 'Total Harga');
// Data
$pdf->FancyTable($header, $orders_on_date);

$nama_file_pdf = "Nota_Omset_Harian_" . str_replace('-', '', $selected_date) . ".pdf";
$pdf->Output('I', $nama_file_pdf); // I: inline, D: download

// ==================================================================

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
exit;
?>