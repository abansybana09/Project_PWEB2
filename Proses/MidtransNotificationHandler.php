<?php
// File: /Proses/MidtransNotificationHandler.php
error_log("===== MidtransNotificationHandler.php Dipanggil =====");

// Gunakan path absolut
$baseDir = dirname(__DIR__);
require_once $baseDir . '/Admin/Koneksi.php';
require_once $baseDir . '/vendor/autoload.php';

// Load .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable($baseDir);
    $dotenv->load();
    error_log("MidtransNotificationHandler.php: .env loaded.");
} catch (\Throwable $e) {
    error_log("Webhook Critical Error loading .env: " . $e->getMessage());
    http_response_code(500);
    exit("Konfigurasi server tidak ditemukan.");
}

// Konfigurasi Midtrans dari .env
$serverKey = $_ENV['MIDTRANS_SERVER_KEY'] ?? $_SERVER['MIDTRANS_SERVER_KEY'] ?? null;
$isProduction = (strtolower($_ENV['MIDTRANS_IS_PRODUCTION'] ?? $_SERVER['MIDTRANS_IS_PRODUCTION'] ?? 'false') === 'true');

if (empty($serverKey)) {
    error_log("Webhook Critical Error: MIDTRANS_SERVER_KEY tidak ditemukan.");
    http_response_code(500);
    exit("Konfigurasi kunci server Midtrans tidak ditemukan.");
}
error_log("MidtransNotificationHandler.php: Server Key loaded.");

\Midtrans\Config::$serverKey = $serverKey;
\Midtrans\Config::$isProduction = $isProduction;

$log_file = __DIR__ . '/midtrans_webhook_log.txt'; // Simpan log di folder Proses
$raw_notification = file_get_contents('php://input');

// ===========================================================
// DEFINISI FUNGSI LOGGING YANG HILANG (TAMBAHKAN KEMBALI)
// ===========================================================
function log_to_file($message, $log_file, $raw_data = null) {
    $timestamp = "[" . date("Y-m-d H:i:s") . "] ";
    $log_entry = $timestamp . $message . "\n";
    if ($raw_data !== null) { $log_entry .= "Raw Data: " . $raw_data . "\n"; }
    // Pastikan folder Proses writable oleh server
    // LOCK_EX mencegah penulisan bersamaan yang bisa merusak file log
    file_put_contents($log_file, $log_entry . "\n", FILE_APPEND | LOCK_EX);
}
// ===========================================================

// Pengecekan Koneksi DB
if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
     log_to_file("Webhook Error: Koneksi database tidak valid. Error: " . ($conn->connect_error ?? 'Koneksi tidak ada'), $log_file);
     http_response_code(500);
     exit("Koneksi DB Error.");
}
error_log("MidtransNotificationHandler.php: Koneksi DB valid.");

try {
    log_to_file("Webhook dipanggil.", $log_file, $raw_notification); // Sekarang fungsi ini dikenal
    $notif = new \Midtrans\Notification();

    $transaction_status = $notif->transaction_status;
    $order_id_midtrans = $notif->order_id;
    $fraud_status = $notif->fraud_status ?? null;
    $payment_type = $notif->payment_type;
    $transaction_id_midtrans = $notif->transaction_id;

    log_to_file("Notif Parsed - Order ID: " . $order_id_midtrans . ", Status: " . $transaction_status . ", Payment Type: " . $payment_type . ", Fraud: " . $fraud_status, $log_file);

    if (empty($order_id_midtrans) || empty($transaction_status)) {
        throw new Exception("Data notifikasi tidak valid (order_id atau status kosong).");
    }

    // Tentukan status untuk kolom 'pembayaran' ENUM('Ya', 'Tidak')
    $update_pembayaran_ke_ya = false;

    if ($transaction_status == 'capture') {
        if ($payment_type == 'credit_card') {
            if ($fraud_status == 'accept') $update_pembayaran_ke_ya = true;
        } else {
             $update_pembayaran_ke_ya = true;
        }
    } else if ($transaction_status == 'settlement') {
        $update_pembayaran_ke_ya = true; // LUNAS
    }
    // Status lain tidak mengubah flag

    if ($update_pembayaran_ke_ya) {
        $status_db_final = 'Ya';
        log_to_file("Mapping Status SUKSES - Order ID: " . $order_id_midtrans . ", DB Status akan diupdate ke: " . $status_db_final, $log_file);

        $sql_update = "UPDATE tb_order SET pembayaran = ?, midtrans_transaction_id = ? WHERE midtrans_order_id = ?";
        $params_type = "sss";
        $params_value = [$status_db_final, $transaction_id_midtrans, $order_id_midtrans];

        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            log_to_file("DB Prepare Error - Order ID: " . $order_id_midtrans . ", Error: " . $conn->error . " | SQL: " . $sql_update, $log_file);
            throw new Exception("DB Prepare Error: " . $conn->error);
        }

        $stmt_update->bind_param($params_type, ...$params_value);

        if ($stmt_update->execute()) {
            $affected_rows = $stmt_update->affected_rows;
            if ($affected_rows > 0) {
                 log_to_file("DB Update SUKSES - Order ID: " . $order_id_midtrans . ", Status Baru: " . $status_db_final, $log_file);
            } else {
                $stmt_check = $conn->prepare("SELECT id FROM tb_order WHERE midtrans_order_id = ?");
                if($stmt_check){
                    $stmt_check->bind_param("s", $order_id_midtrans); $stmt_check->execute(); $result_check = $stmt_check->get_result();
                    if($result_check->num_rows === 0){ log_to_file("DB Update WARNING - Order ID: " . $order_id_midtrans . " TIDAK DITEMUKAN di DB.", $log_file); }
                    else { log_to_file("DB Update INFO - Order ID: " . $order_id_midtrans . ", Tidak ada baris terpengaruh (Mungkin status sudah 'Ya'?).", $log_file); }
                    $stmt_check->close();
                } else { log_to_file("DB Update INFO - Order ID: " . $order_id_midtrans . ", Tidak ada baris terpengaruh (Gagal cek).", $log_file); }
            }
            http_response_code(200);
            echo "Notification processed.";
        } else {
            log_to_file("DB Execute Error - Order ID: " . $order_id_midtrans . ", Error: " . $stmt_update->error, $log_file);
            throw new Exception("DB Execute Error: " . $stmt_update->error);
        }
        $stmt_update->close();

    } else {
         log_to_file("Status tidak memerlukan update ke 'Ya' - Order ID: " . $order_id_midtrans . ", Status Midtrans: " . $transaction_status, $log_file);
         http_response_code(200);
         echo "Notification received, status not requiring 'Ya' update.";
    }

} catch (Exception $e) {
    log_to_file("Webhook Exception: " . $e->getMessage(), $log_file, $raw_notification);
    if (strpos($e->getMessage(), "DB") !== false || strpos($e->getMessage(), "SQL") !== false || strpos($e->getMessage(), "Koneksi") !== false) {
        http_response_code(500);
    } else {
        http_response_code(400);
    }
    echo "Error processing notification.";
}

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>