<?php
// File: /Proses/CreateSnapToken.php (Untuk Frontend Menu - Pendekatan Ringkasan)
error_log("===== CreateSnapToken.php (Frontend - KERANJANG - RINGKASAN) Dipanggil =====");
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

// Ambil data dari POST (dari FormData JavaScript di views/menu/index.php)
$pelanggan = trim($_POST['customer_name'] ?? '');
$nohp = trim($_POST['customer_phone'] ?? '');
$alamat = trim($_POST['customer_address'] ?? '');
$items_json_string = $_POST['order_items'] ?? '[]';
$total_harga_overall_str = $_POST['order_total_price'] ?? '0';

error_log("CreateSnapToken.php (Keranjang): Data POST - Pelanggan: " . $pelanggan . ", Items JSON: " . $items_json_string);

// Validasi input dasar
if (empty($pelanggan) || empty($nohp) || empty($alamat) || empty($items_json_string) || empty($total_harga_overall_str)) {
    error_log("CreateSnapToken.php Error: Data POST tidak lengkap untuk keranjang.");
    echo json_encode(['success' => false, 'message' => 'Data pesanan tidak lengkap. Mohon isi semua field.']);
    exit;
}

$items_array = json_decode($items_json_string, true);
$total_harga_overall = filter_var($total_harga_overall_str, FILTER_VALIDATE_INT);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($items_array) || empty($items_array) || $total_harga_overall === false || $total_harga_overall <= 0) {
    error_log("CreateSnapToken.php Error: Data item keranjang tidak valid atau total harga tidak valid.");
    echo json_encode(['success' => false, 'message' => 'Data item keranjang atau total harga tidak valid.']);
    exit;
}

// 1. Simpan Pesanan BARU ke tb_order
$status_pembayaran_awal = 'Pending';
$order_id_midtrans = 'CART-' . time() . '-' . rand(1000, 9999);

$deskripsi_pesanan_db = "";
$total_jumlah_item_db = 0;
foreach ($items_array as $item) {
    // Pastikan key 'name' dan 'quantity' ada di setiap item
    $itemName = $item['name'] ?? 'Item tidak diketahui';
    $itemQuantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;

    $deskripsi_pesanan_db .= htmlspecialchars($itemName) . " (Qty: " . $itemQuantity . ")\n";
    $total_jumlah_item_db += $itemQuantity;
}
$deskripsi_pesanan_db = trim($deskripsi_pesanan_db);


// Pengecekan Koneksi DB
if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
    error_log("CreateSnapToken.php Error: Koneksi DB tidak valid sebelum prepare. Error: " . ($conn->connect_error ?? 'Koneksi tidak ada'));
    echo json_encode(['success' => false, 'message' => 'Koneksi database bermasalah.']);
    exit;
}

// Query INSERT (Pastikan semua nama kolom ini ada di tabel tb_order Anda)
$sql_insert = "INSERT INTO tb_order (pelanggan, nohp, alamat, pesanan, jumlah_pesan, total_harga, pembayaran, midtrans_order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);

if (!$stmt_insert) {
    error_log("DB Prepare Error (Insert tb_order): " . $conn->error . " | SQL: " . $sql_insert);
    echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan statement database.']);
    exit;
}

$tipe_data_bind = "ssssiiss"; // Asumsi total_harga di DB adalah INT

try {
    $stmt_insert->bind_param($tipe_data_bind, $pelanggan, $nohp, $alamat, $deskripsi_pesanan_db, $total_jumlah_item_db, $total_harga_overall, $status_pembayaran_awal, $order_id_midtrans);
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
error_log("CreateSnapToken.php (Keranjang): Order baru disimpan. ID Internal: " . $id_pesanan_internal . ", Midtrans Order ID: " . $order_id_midtrans);

// 2. Siapkan Parameter untuk Snap Token (PENDEKATAN RINGKASAN)
$params = array(
    'transaction_details' => array(
        'order_id' => $order_id_midtrans,
        'gross_amount' => $total_harga_overall,
    ),
    'item_details' => array(array(
        'id' => 'ORDER-' . $id_pesanan_internal,
        'price' => $total_harga_overall,
        'quantity' => 1,
        'name' => substr('Pesanan dari ' . htmlspecialchars($pelanggan), 0, 50)
    )),
    'customer_details' => array(
        'first_name' => substr(htmlspecialchars($pelanggan), 0, 20),
        'phone' => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15),
        'billing_address'  => array(
            'first_name'=> substr(htmlspecialchars($pelanggan), 0, 20),
            'phone'     => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15),
            'address'   => substr(htmlspecialchars($alamat), 0, 60),
            'country_code' => 'IDN'
        ),
        'shipping_address' => array(
            'first_name'=> substr(htmlspecialchars($pelanggan), 0, 20),
            'phone'     => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15),
            'address'   => substr(htmlspecialchars($alamat), 0, 60),
            'country_code' => 'IDN'
        )
    ),
);

// 3. Dapatkan Snap Token
try {
    error_log("CreateSnapToken.php (Keranjang): Meminta Snap Token untuk Midtrans Order ID: " . $order_id_midtrans);
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    error_log("CreateSnapToken.php (Keranjang): Snap Token berhasil didapatkan.");
    echo json_encode([
        'success' => true,
        'snap_token' => $snapToken,
        'order_id_internal' => $id_pesanan_internal,
        'order_id_midtrans' => $order_id_midtrans
    ]);
} catch (Exception $e) {
    // Jika gagal dapat token, coba hapus order yang tadi dibuat di DB (rollback manual)
    $stmt_delete = $conn->prepare("DELETE FROM tb_order WHERE id = ?");
    if($stmt_delete){
        $stmt_delete->bind_param("i", $id_pesanan_internal);
        $stmt_delete->execute();
        $stmt_delete->close();
        error_log("CreateSnapToken.php: Order ID " . $id_pesanan_internal . " dihapus karena gagal getSnapToken.");
    } else {
        error_log("CreateSnapToken.php: Gagal prepare statement DELETE untuk rollback order ID " . $id_pesanan_internal);
    }
    error_log("Midtrans Snap Token Exception (Frontend): " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Gagal membuat token pembayaran Midtrans: ' . $e->getMessage()]);
}

$conn->close();
?>