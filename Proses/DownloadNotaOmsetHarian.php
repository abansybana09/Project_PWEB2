<?php
// File: /Proses/DownloadNotaOmsetHarian.php (Menggunakan FPDF)

if (session_status() == PHP_SESSION_NONE) { session_start(); }
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) { die("Akses ditolak."); }

require_once dirname(__DIR__) . '/Admin/Koneksi.php';
$fpdfPath = dirname(__DIR__) . '/libs/fpdf/fpdf.php';
if (!file_exists($fpdfPath)) { die("ERROR: File fpdf.php tidak ditemukan."); }
require_once $fpdfPath;

$selected_date = $_GET['tanggal'] ?? date('Y-m-d');
if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $selected_date)) { die("Format tanggal tidak valid."); }

$nama_toko = "Ayam Bakar Mang Oman";
$alamat_toko = "Jln. Jendral Sudirman No. 45 RT 03 RW 07";
$telepon_toko = "0896-3015-2631";

$orders_on_date = [];
$total_omset_harian_pdf = 0;
$query_error = null;

if (!$conn || $conn->connect_error) { die("Koneksi database gagal: " . ($conn->connect_error ?? 'Tidak diketahui')); }

$sql_orders = "SELECT id, pelanggan, nohp, pesanan, jumlah_pesan, total_harga, waktu_order_dibuat
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

class PDF_Harian extends FPDF
{
    function Header()
    {
        global $nama_toko, $alamat_toko, $telepon_toko, $selected_date;
        $this->SetFont('Arial','B',15);
        $this->Cell(0,10,iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $nama_toko),0,1,'C');
        $this->SetFont('Arial','',9);
        $this->Cell(0,5,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$alamat_toko),0,1,'C');
        $this->Cell(0,5,'Telp: ' . $telepon_toko,0,1,'C');
        $this->Ln(5);
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'Laporan Omset Harian',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,5,'Tanggal: ' . date('d F Y', strtotime($selected_date)),0,1,'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15); $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
        $this->SetX(-50); $this->Cell(0,10,'Dicetak: '.date('d/m/Y H:i:s'),0,0,'R');
    }

    function FancyTable($header, $data)
    {
        $this->SetFillColor(230,230,230); $this->SetTextColor(0); $this->SetDrawColor(128,128,128);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial','B',7); // PERBAIKAN: Tentukan Font Family (Arial)

        $w = array(8, 15, 15, 30, 25, 47, 15, 35); // No, Waktu, ID, Pelanggan, No.HP, Pesanan, Jml, Total
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$header[$i]),1,0,'C',true);
        $this->Ln();

        $this->SetFillColor(245,245,245); $this->SetTextColor(0);
        $this->SetFont('Arial','',7); // PERBAIKAN: Tentukan Font Family (Arial)
        $fill = false; $nomor = 1;
        global $total_omset_harian_pdf;

        if (empty($data)) {
            $this->Cell(array_sum($w),10,'Tidak ada transaksi penjualan untuk tanggal ini.',1,1,'C');
        } else {
            foreach($data as $row) {
                $current_y_start = $this->GetY(); // Simpan Y awal baris

                $this->Cell($w[0],6,$nomor++,'LR',0,'C',$fill);
                $this->Cell($w[1],6,date('H:i', strtotime($row['waktu_order_dibuat'])),'LR',0,'C',$fill);
                $this->Cell($w[2],6,$row['id'],'LR',0,'C',$fill);

                $x_after_id = $this->GetX(); // Simpan X setelah ID
                $this->MultiCell($w[3],6,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$row['pelanggan']),0,'L',$fill); // Border 0 untuk MultiCell
                $y_after_pelanggan = $this->GetY();
                $this->SetXY($x_after_id + $w[3], $current_y_start); // Kembalikan Y ke awal, X setelah pelanggan

                $this->Cell($w[4],6,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',$row['nohp']),0,0,'L',$fill); // No HP, Border 0

                $x_after_nohp = $this->GetX();
                $this->MultiCell($w[5],6,iconv('UTF-8', 'ISO-8859-1//TRANSLIT',strip_tags(str_replace(array("<br />","<br>","\r\n", "\r", "\n"),"; ",$row['pesanan']))),0,'L',$fill); // Border 0
                $y_after_pesanan = $this->GetY();
                $this->SetXY($x_after_nohp + $w[5], $current_y_start); // Kembalikan Y ke awal, X setelah pesanan

                $this->Cell($w[6],6,htmlspecialchars($row['jumlah_pesan']),0,0,'R',$fill); // Border 0
                $this->Cell($w[7],6,'Rp '.number_format($row['total_harga_numeric'],0,',','.'),0,0,'R',$fill); // Border 0
                $y_after_total = $this->GetY();

                // Tentukan tinggi baris maksimum dari semua MultiCell dan Cell
                $row_height = max($y_after_pelanggan, $y_after_pesanan, $y_after_total) - $current_y_start;
                if ($row_height < 6) $row_height = 6; // Tinggi minimum

                // Gambar ulang border LR untuk semua sel dengan tinggi yang sama
                $this->SetXY($this->lMargin, $current_y_start); // Kembali ke X awal baris
                $this->Cell($w[0],$row_height,'','LR',0,'C',$fill); // No
                $this->Cell($w[1],$row_height,'','LR',0,'C',$fill); // Waktu
                $this->Cell($w[2],$row_height,'','LR',0,'C',$fill); // ID Order
                $this->Cell($w[3],$row_height,'','LR',0,'L',$fill); // Pelanggan
                $this->Cell($w[4],$row_height,'','LR',0,'L',$fill); // No. HP
                $this->Cell($w[5],$row_height,'','LR',0,'L',$fill); // Pesanan
                $this->Cell($w[6],$row_height,'','LR',0,'R',$fill); // Jml
                $this->Cell($w[7],$row_height,'','LR',0,'R',$fill); // Total Harga

                $this->Ln($row_height);
                $fill = !$fill;
            }
        }
        $this->Cell(array_sum($w),0,'','T'); $this->Ln();

        $this->SetFont('Arial','B',9); // PERBAIKAN
        $this->Cell(array_sum($w) - $w[count($w)-1] , 8, 'TOTAL OMSET HARIAN', 1, 0, 'R', true);
        $this->Cell($w[count($w)-1], 8, 'Rp ' . number_format($total_omset_harian_pdf, 0, ',', '.'), 1, 1, 'R', true);
    }
}

$pdf = new PDF_Harian('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$header = array('No.', 'Waktu', 'ID', 'Pelanggan', 'No. HP', 'Pesanan', 'Jml', 'Total'); // Sesuaikan header
$pdf->FancyTable($header, $orders_on_date);
$nama_file_pdf = "Nota_Omset_Harian_" . str_replace('-', '', $selected_date) . ".pdf";
$pdf->Output('I', $nama_file_pdf);

if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }
exit;
?>