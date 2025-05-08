<?php
// File: /Proses/CreateSnapToken.php (Untuk Frontend Menu)
error_log("===== CreateSnapToken.php (Frontend) Dipanggil ====="); // Log awal
session_start(); // Mulai session jika diperlukan

// Include Koneksi Database
// Pastikan path ini benar relatif dari folder Proses
require_once __DIR__ . '/../Admin/Koneksi.php';

// Include Autoloader Composer
// Pastikan path ini benar relatif dari folder Proses
require_once __DIR__ . '/../vendor/autoload.php';

// Load Environment Variables dari .env
try {
    // Path ke direktori yang berisi file .env (root proyek)
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    error_log("CreateSnapToken.php: .env loaded successfully.");
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // Error jika file .env tidak ditemukan
    error_log("CreateSnapToken.php Error: File .env tidak ditemukan di " . (__DIR__ . '/..'));
    header('Content-Type: application/json');
    // Jangan tampilkan path di pesan error produksi
    echo json_encode(['success' => false, 'message' => 'Konfigurasi server (.env) tidak ditemukan.']);
    exit;
} catch (\Throwable $e) { // Tangkap error lain saat load .env
     error_log("CreateSnapToken.php Error loading .env: " . $e->getMessage());
     header('Content-Type: application/json');
     echo json_encode(['success' => false, 'message' => 'Gagal memuat konfigurasi server.']);
     exit;
}

// Ambil Konfigurasi Midtrans dari Environment Variables
// Gunakan $_ENV atau $_SERVER yang lebih reliabel setelah dotenv->load()
$serverKey = $_ENV['MIDTRANS_SERVER_KEY'] ?? $_SERVER['MIDTRANS_SERVER_KEY'] ?? null;
$isProductionStr = $_ENV['MIDTRANS_IS_PRODUCTION'] ?? $_SERVER['MIDTRANS_IS_PRODUCTION'] ?? 'false';
$isProduction = (strtolower($isProductionStr) === 'true');

// Validasi Server Key
if (empty($serverKey)) { // Periksa empty() bukan hanya !$serverKey
    error_log("CreateSnapToken.php Error: MIDTRANS_SERVER_KEY tidak ditemukan di environment variables.");
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Konfigurasi kunci server Midtrans tidak ditemukan (ENV).']);
    exit;
}
error_log("CreateSnapToken.php: Server Key loaded (first 15 chars): " . substr($serverKey, 0, 15) . "...");
error_log("CreateSnapToken.php: Is Production: " . ($isProduction ? 'true' : 'false'));

// Set Konfigurasi Midtrans
\Midtrans\Config::$serverKey = $serverKey;
\Midtrans\Config::$isProduction = $isProduction;
\Midtrans\Config::$isSanitized = true; // Aktifkan sanitasi input
\Midtrans\Config::$is3ds = true; // Aktifkan 3D Secure jika menggunakan kartu kredit

// Set header output ke JSON
header('Content-Type: application/json');

// Ambil data dari POST request (dari FormData JavaScript)
$pelanggan = trim($_POST['pelanggan'] ?? ''); // Trim spasi
$nohp = trim($_POST['nohp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$pesanan = trim($_POST['pesanan'] ?? ''); // Nama item
$jumlah_pesan_str = $_POST['jumlah_pesan'] ?? '0';
$total_harga_str = $_POST['total_harga'] ?? '0';

error_log("CreateSnapToken.php: Data POST diterima - Pelanggan: " . $pelanggan . ", NoHP: " . $nohp);

// Validasi input dasar
if (empty($pelanggan) || empty($nohp) || empty($alamat) || empty($pesanan) || empty($jumlah_pesan_str) || empty($total_harga_str)) {
    error_log("CreateSnapToken.php Error: Data POST tidak lengkap.");
    echo json_encode(['success' => false, 'message' => 'Data pesanan tidak lengkap. Mohon isi semua field.']);
    exit;
}

// Konversi dan validasi tipe data numerik
$jumlah_pesan = filter_var($jumlah_pesan_str, FILTER_VALIDATE_INT);
// Ambil angka saja dari total harga, lalu jadikan integer
$total_harga = filter_var(preg_replace('/[^0-9]/', '', $total_harga_str), FILTER_VALIDATE_INT);

if ($jumlah_pesan === false || $jumlah_pesan <= 0 || $total_harga === false || $total_harga <= 0) {
    error_log("CreateSnapToken.php Error: Jumlah ($jumlah_pesan_str) atau total harga ($total_harga_str) tidak valid.");
    echo json_encode(['success' => false, 'message' => 'Jumlah atau total harga tidak valid.']);
    exit;
}

// 1. Simpan Pesanan BARU ke tb_order
$status_pembayaran_awal = 'Pending'; // Status awal saat order dibuat
$order_id_midtrans = 'SNAP-' . time() . '-' . rand(1000, 9999); // Buat Order ID unik untuk Midtrans

// Pengecekan Koneksi DB sebelum prepare
if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
    error_log("CreateSnapToken.php Error: Koneksi DB tidak valid sebelum prepare. Error: " . ($conn->connect_error ?? 'Koneksi tidak ada'));
    echo json_encode(['success' => false, 'message' => 'Koneksi database bermasalah.']);
    exit;
}

// Query INSERT (Pastikan nama kolom sesuai tabel tb_order Anda)
$sql_insert = "INSERT INTO tb_order (pelanggan, nohp, alamat, pesanan, jumlah_pesan, total_harga, pembayaran, midtrans_order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);

// Periksa apakah prepare berhasil
if (!$stmt_insert) {
    // Log error MySQL yang spesifik
    error_log("DB Prepare Error (Insert tb_order): " . $conn->error . " | SQL: " . $sql_insert);
    echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan statement database.']);
    exit;
}

// Tentukan tipe data untuk bind_param (SESUAIKAN DENGAN TABEL ANDA!)
// s = string, i = integer, d = double/decimal
// Asumsi: pelanggan(s), nohp(s), alamat(s), pesanan(s), jumlah(i), harga(i), pembayaran(s), midtrans_id(s)
$tipe_data_bind = "ssssiiss";
// Jika total_harga di DB adalah DECIMAL/FLOAT/DOUBLE, ganti 'i' kedua menjadi 'd': "ssssidss"

try {
    // Bind parameter ke statement
    $stmt_insert->bind_param($tipe_data_bind, $pelanggan, $nohp, $alamat, $pesanan, $jumlah_pesan, $total_harga, $status_pembayaran_awal, $order_id_midtrans);
} catch (TypeError $e) {
     // Error jika tipe data variabel PHP tidak cocok dengan tipe di bind_param
     error_log("DB Bind Param Error (Insert tb_order): " . $e->getMessage() . " | Tipe Data Dicoba: " . $tipe_data_bind);
     echo json_encode(['success' => false, 'message' => 'Tipe data tidak cocok saat menyiapkan database.']);
     $stmt_insert->close();
     $conn->close();
     exit;
}

// Eksekusi statement INSERT
if (!$stmt_insert->execute()) {
    error_log("DB Execute Error (Insert tb_order): " . $stmt_insert->error);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesanan ke database.']);
    $stmt_insert->close();
    $conn->close();
    exit;
}
// Dapatkan ID dari baris yang baru saja dimasukkan
$id_pesanan_internal = $stmt_insert->insert_id;
$stmt_insert->close(); // Tutup statement setelah selesai
error_log("CreateSnapToken.php: Order baru disimpan ke DB. ID Internal: " . $id_pesanan_internal . ", Midtrans Order ID: " . $order_id_midtrans);


// 2. Siapkan Parameter untuk Midtrans Snap Token
$harga_per_item = ($jumlah_pesan > 0) ? round($total_harga / $jumlah_pesan) : 0; // Hitung harga per item (integer)

$params = array(
    'transaction_details' => array(
        'order_id' => $order_id_midtrans, // Gunakan ID Midtrans yang unik
        'gross_amount' => $total_harga,   // Total harga (integer)
    ),
    'item_details' => array(array(
        'id' => 'ITEM-' . $id_pesanan_internal, // ID unik untuk item
        'price' => $harga_per_item,            // Harga per item (integer)
        'quantity' => $jumlah_pesan,           // Jumlah item (integer)
        'name' => substr($pesanan, 0, 50)      // Nama item (maks 50 char)
    )),
    'customer_details' => array(
        'first_name' => substr($pelanggan, 0, 20), // Nama pelanggan (maks 20 char)
        'phone' => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15), // No HP (hanya angka, maks 15)
        'billing_address'  => array(
            'first_name'=> substr($pelanggan, 0, 20),
            'phone'     => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15),
            'address'   => substr($alamat, 0, 60), // Alamat (maks 60 char)
            'country_code' => 'IDN' // Kode negara (wajib untuk Snap)
        ),
        'shipping_address' => array( // Alamat pengiriman (wajib untuk Snap)
            'first_name'=> substr($pelanggan, 0, 20),
            'phone'     => substr(preg_replace('/[^0-9]/', '', $nohp), 0, 15),
            'address'   => substr($alamat, 0, 60),
            'country_code' => 'IDN'
        )
    ),
    // Anda bisa menambahkan parameter lain di sini jika perlu
    // 'enabled_payments' => ['gopay', 'shopeepay', 'bank_transfer'], // Contoh membatasi metode bayar
    // 'expiry' => [
    //     'unit' => 'minute',
    //     'duration' => 30 // Token berlaku 30 menit
    // ],
);

// 3. Dapatkan Snap Token dari Midtrans
try {
    error_log("CreateSnapToken.php: Meminta Snap Token untuk Midtrans Order ID: " . $order_id_midtrans);
    // Panggil library Midtrans untuk membuat token
    $snapToken = \Midtrans\Snap::getSnapToken($params);
    error_log("CreateSnapToken.php: Snap Token berhasil didapatkan.");

    // Kirim respons sukses ke frontend
    echo json_encode([
        'success' => true,
        'snap_token' => $snapToken,
        'order_id_internal' => $id_pesanan_internal, // Kirim ID internal jika perlu di JS
        'order_id_midtrans' => $order_id_midtrans // Kirim ID Midtrans
    ]);

} catch (Exception $e) {
    // Jika gagal mendapatkan token dari Midtrans
    error_log("Midtrans Snap Token Exception (Frontend): " . $e->getMessage());

    // Coba hapus order yang tadi dibuat di DB (rollback manual)
    $stmt_delete = $conn->prepare("DELETE FROM tb_order WHERE id = ?");
    if($stmt_delete){
        $stmt_delete->bind_param("i", $id_pesanan_internal);
        $stmt_delete->execute();
        $stmt_delete->close();
        error_log("CreateSnapToken.php: Order ID " . $id_pesanan_internal . " dihapus karena gagal getSnapToken.");
    } else {
        error_log("CreateSnapToken.php: Gagal prepare statement DELETE untuk rollback order ID " . $id_pesanan_internal);
    }

    // Kirim pesan error ke frontend
    echo json_encode(['success' => false, 'message' => 'Gagal membuat token pembayaran Midtrans: ' . $e->getMessage()]);
}

// Tutup koneksi database
$conn->close();
?>