<?php
// File: /Proses/CreateSnapToken.php (Menyimpan HANYA NAMA ITEM ke kolom 'pesanan')
error_log("===== CreateSnapToken.php (Frontend - KERANJANG - NAMA ITEM) Dipanggil =====");
session_start();

require_once dirname(__DIR__) . '/Admin/Koneksi.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
} catch (\Throwable $e) {
     error_log("CreateSnapToken.php Critical Error loading .env: " . $e->getMessage());
     header('Content-Type: application/json', true, 500);
     echo json_encode(['success' => false, 'message' => 'Gagal memuat konfigurasi server penting.']);
     exit;
}

// Ambil Konfigurasi Midtrans
$serverKey = $_ENV['MIDTRANS_SERVER_KEY'] ?? $_SERVER['MIDTRANS_SERVER_KEY'] ?? null;
$isProduction = (strtolower($_ENV['MIDTRANS_IS_PRODUCTION'] ?? $_SERVER['MIDTRANS_IS_PRODUCTION'] ?? 'false') === 'true');

if (empty($serverKey)) { /* ... error handling server key ... */ exit; }

// Set Konfigurasi Midtrans
try {
    \Midtrans\Config::$serverKey = $serverKey;
    \Midtrans\Config::$isProduction = $isProduction;
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;
} catch (\Throwable $e) { /* ... error handling config midtrans ... */ exit; }

header('Content-Type: application/json');

// Ambil data dari POST
$pelanggan = trim($_POST['customer_name'] ?? '');
$nohp = trim($_POST['customer_phone'] ?? '');
$alamat = trim($_POST['customer_address'] ?? '');
$items_json_string = $_POST['order_items'] ?? '[]';
$total_harga_overall_str = $_POST['order_total_price'] ?? '0';

// Validasi input dasar
if (empty($pelanggan) || empty($nohp) || empty($alamat) || empty($items_json_string) || empty($total_harga_overall_str)) { /* ... error handling data tidak lengkap ... */ exit; }

$items_array = json_decode($items_json_string, true);
$total_harga_overall = filter_var($total_harga_overall_str, FILTER_VALIDATE_INT);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($items_array) || empty($items_array) || $total_harga_overall === false || $total_harga_overall <= 0) { /* ... error handling data item/harga tidak valid ... */ exit; }

// 1. Simpan Pesanan BARU ke tb_order
$status_pembayaran_awal = 'Pending';
$order_id_midtrans = 'CART-' . time() . '-' . rand(1000, 9999);

// ==================================================================
// MODIFIKASI: Membuat string nama item untuk kolom 'pesanan'
// ==================================================================
$nama_item_untuk_db_array = [];
$total_jumlah_item_db = 0;
foreach ($items_array as $item) {
    $itemName = $item['name'] ?? 'Item tidak diketahui';
    $itemQuantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;

    $nama_item_untuk_db_array[] = htmlspecialchars($itemName); // Hanya nama item
    $total_jumlah_item_db += $itemQuantity;
}
// Gabungkan nama item dengan koma dan spasi. Anda bisa ganti pemisahnya.
$deskripsi_pesanan_untuk_db = implode(", ", $nama_item_untuk_db_array);
// ==================================================================


// Pengecekan Koneksi DB
if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) { /* ... error handling koneksi ... */ }

$sql_insert = "INSERT INTO tb_order (pelanggan, nohp, alamat, pesanan, jumlah_pesan, total_harga, pembayaran, midtrans_order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
if (!$stmt_insert) { /* ... error handling prepare ... */ }

$tipe_data_bind = "ssssiiss"; // pelanggan(s), nohp(s), alamat(s), deskripsi_pesanan_untuk_db(s), total_jumlah_item_db(i), total_harga_overall(i), status_awal(s), midtrans_id(s)
try {
    $stmt_insert->bind_param($tipe_data_bind, $pelanggan, $nohp, $alamat, $deskripsi_pesanan_untuk_db, $total_jumlah_item_db, $total_harga_overall, $status_pembayaran_awal, $order_id_midtrans);
} catch (TypeError $e) { /* ... error handling bind ... */ }
if (!$stmt_insert->execute()) { /* ... error handling execute ... */ }
$id_pesanan_internal = $stmt_insert->insert_id;
$stmt_insert->close();
error_log("CreateSnapToken.php: Order baru disimpan. ID Internal: " . $id_pesanan_internal . ", Pesanan DB: " . $deskripsi_pesanan_untuk_db);

// 2. Siapkan Parameter untuk Snap Token (item_details tetap detail per item)
$midtrans_item_details = [];
foreach ($items_array as $item_cart) {
    $midtrans_item_details[] = [
        'id'       => $item_cart['id'] ?? ('ITEM-' . uniqid()),
        'price'    => (int)$item_cart['price'],
        'quantity' => (int)$item_cart['quantity'],
        'name'     => substr(htmlspecialchars($item_cart['name']), 0, 50)
    ];
}
$params = [
    'transaction_details' => ['order_id' => $order_id_midtrans,'gross_amount' => $total_harga_overall,],
    'item_details' => $midtrans_item_details, // Kirim detail item ke Midtrans
    'customer_details' => ['first_name' => substr($pelanggan, 0, 20),'phone' => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15),'billing_address'  => ['first_name'=> substr($pelanggan, 0, 20),'phone' => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15),'address' => substr($alamat, 0, 60),'country_code' => 'IDN'],'shipping_address' => ['first_name'=> substr($pelanggan, 0, 20),'phone' => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15),'address' => substr($alamat, 0, 60),'country_code' => 'IDN']],
];

// 3. Dapatkan Snap Token
try {
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    echo json_encode(['success' => true,'snap_token' => $snapToken,'order_id_internal' => $id_pesanan_internal,'order_id_midtrans' => $order_id_midtrans]);
} catch (Exception $e) { /* ... error handling getSnapToken dan rollback DB ... */ }

$conn->close();
?>