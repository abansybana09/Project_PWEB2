<?php
// File: /Proses/CreateSnapToken.php (Untuk Frontend Menu)
error_log("===== CreateSnapToken.php (Frontend) Dipanggil =====");
session_start();

// Gunakan path absolut untuk konsistensi
require_once dirname(__DIR__) . '/Admin/Koneksi.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
    error_log("CreateSnapToken.php: .env loaded successfully.");
} catch (\Throwable $e) {
     error_log("CreateSnapToken.php Critical Error loading .env: " . $e->getMessage());
     header('Content-Type: application/json', true, 500);
     echo json_encode(['success' => false, 'message' => 'Gagal memuat konfigurasi server penting.']);
     exit;
}

// Ambil Konfigurasi Midtrans
$serverKey = $_ENV['MIDTRANS_SERVER_KEY'] ?? $_SERVER['MIDTRANS_SERVER_KEY'] ?? null;
$isProductionStr = $_ENV['MIDTRANS_IS_PRODUCTION'] ?? $_SERVER['MIDTRANS_IS_PRODUCTION'] ?? 'false';
$isProduction = (strtolower($isProductionStr) === 'true');

if (empty($serverKey)) {
    error_log("CreateSnapToken.php Error: MIDTRANS_SERVER_KEY tidak ditemukan.");
    header('Content-Type: application/json', true, 500);
    echo json_encode(['success' => false, 'message' => 'Konfigurasi kunci server Midtrans tidak ditemukan (ENV).']);
    exit;
}

// Set Konfigurasi Midtrans
try {
    \Midtrans\Config::$serverKey = $serverKey;
    \Midtrans\Config::$isProduction = $isProduction;
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;
} catch (\Throwable $e) {
     error_log("CreateSnapToken.php Critical Error setting Midtrans Config: " . $e->getMessage());
     header('Content-Type: application/json', true, 500);
     echo json_encode(['success' => false, 'message' => 'Gagal mengkonfigurasi library pembayaran.']);
     exit;
}

header('Content-Type: application/json');

// Ambil data dari POST
$pelanggan = trim($_POST['pelanggan'] ?? '');
$nohp = trim($_POST['nohp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$pesanan = trim($_POST['pesanan'] ?? '');
$jumlah_pesan_str = $_POST['jumlah_pesan'] ?? '0';
$total_harga_str = $_POST['total_harga'] ?? '0';

// Validasi input dasar
if (empty($pelanggan) || empty($nohp) || empty($alamat) || empty($pesanan) || empty($jumlah_pesan_str) || empty($total_harga_str)) {
    error_log("CreateSnapToken.php Error: Data POST tidak lengkap.");
    echo json_encode(['success' => false, 'message' => 'Data pesanan tidak lengkap. Mohon isi semua field.']);
    exit;
}

// Konversi dan validasi tipe data numerik
$jumlah_pesan = filter_var($jumlah_pesan_str, FILTER_VALIDATE_INT);
$total_harga = filter_var(preg_replace('/[^0-9]/', '', $total_harga_str), FILTER_VALIDATE_INT);

if ($jumlah_pesan === false || $jumlah_pesan <= 0 || $total_harga === false || $total_harga <= 0) {
    error_log("CreateSnapToken.php Error: Jumlah ($jumlah_pesan_str) atau total harga ($total_harga_str) tidak valid.");
    echo json_encode(['success' => false, 'message' => 'Jumlah atau total harga tidak valid.']);
    exit;
}

// 1. Simpan Pesanan BARU ke tb_order
$status_pembayaran_awal = 'Tidak'; // Sesuai ENUM('Ya', 'Tidak'), default 'Tidak'
$order_id_midtrans = 'SNAP-' . time() . '-' . rand(1000, 9999);

// Pengecekan Koneksi DB sebelum prepare
if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
    error_log("CreateSnapToken.php Error: Koneksi DB tidak valid sebelum prepare. Error: " . ($conn->connect_error ?? 'Koneksi tidak ada'));
    echo json_encode(['success' => false, 'message' => 'Koneksi database bermasalah.']);
    exit;
}

// Query INSERT (Pastikan kolom midtrans_order_id sudah ada)
$sql_insert = "INSERT INTO tb_order (pelanggan, nohp, alamat, pesanan, jumlah_pesan, total_harga, pembayaran, midtrans_order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);

if (!$stmt_insert) {
    error_log("DB Prepare Error (Insert tb_order): " . $conn->error . " | SQL: " . $sql_insert);
    echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan statement database.']);
    exit;
}

// Tipe data bind_param (Asumsi jumlah=INT, harga=INT, pembayaran=String, midtrans_id=String)
$tipe_data_bind = "ssssiiss";
// Jika total_harga di DB adalah DECIMAL/FLOAT/DOUBLE, ganti 'i' kedua menjadi 'd': "ssssidss"

try {
    $stmt_insert->bind_param($tipe_data_bind, $pelanggan, $nohp, $alamat, $pesanan, $jumlah_pesan, $total_harga, $status_pembayaran_awal, $order_id_midtrans);
} catch (TypeError $e) {
     error_log("DB Bind Param Error (Insert tb_order): " . $e->getMessage() . " | Tipe Data Dicoba: " . $tipe_data_bind);
     echo json_encode(['success' => false, 'message' => 'Tipe data tidak cocok saat menyiapkan database.']);
     $stmt_insert->close(); $conn->close(); exit;
}

if (!$stmt_insert->execute()) {
    error_log("DB Execute Error (Insert tb_order): " . $stmt_insert->error);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesanan ke database.']);
    $stmt_insert->close(); $conn->close(); exit;
}
$id_pesanan_internal = $stmt_insert->insert_id;
$stmt_insert->close();
error_log("CreateSnapToken.php: Order baru disimpan ke DB. ID Internal: " . $id_pesanan_internal . ", Midtrans Order ID: " . $order_id_midtrans);

// 2. Siapkan Parameter untuk Snap Token
$harga_per_item = ($jumlah_pesan > 0) ? round($total_harga / $jumlah_pesan) : 0;
$params = [
    'transaction_details' => ['order_id' => $order_id_midtrans,'gross_amount' => $total_harga,],
    'item_details' => [['id' => 'ITEM-' . $id_pesanan_internal,'price' => $harga_per_item,'quantity' => $jumlah_pesan,'name' => substr($pesanan, 0, 50)]],
    'customer_details' => ['first_name' => substr($pelanggan, 0, 20),'phone' => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15),'billing_address'  => ['first_name'=> substr($pelanggan, 0, 20),'phone' => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15),'address' => substr($alamat, 0, 60),'country_code' => 'IDN'],'shipping_address' => ['first_name'=> substr($pelanggan, 0, 20),'phone' => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15),'address' => substr($alamat, 0, 60),'country_code' => 'IDN']],
];

// 3. Dapatkan Snap Token
try {
    error_log("CreateSnapToken.php: Meminta Snap Token untuk Midtrans Order ID: " . $order_id_midtrans);
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    error_log("CreateSnapToken.php: Snap Token berhasil didapatkan.");
    echo json_encode(['success' => true,'snap_token' => $snapToken,'order_id_internal' => $id_pesanan_internal,'order_id_midtrans' => $order_id_midtrans]);
} catch (Exception $e) {
    $stmt_delete = $conn->prepare("DELETE FROM tb_order WHERE id = ?");
    if($stmt_delete){ $stmt_delete->bind_param("i", $id_pesanan_internal); $stmt_delete->execute(); $stmt_delete->close(); error_log("CreateSnapToken.php: Order ID " . $id_pesanan_internal . " dihapus karena gagal getSnapToken."); }
    error_log("Midtrans Snap Token Exception (Frontend): " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Gagal membuat token pembayaran Midtrans: ' . $e->getMessage()]);
}

$conn->close();
?>