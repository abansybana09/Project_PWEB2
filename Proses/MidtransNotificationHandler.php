<?php
// File: /Proses/MidtransNotificationHandler.php
require_once __DIR__ . '/../Admin/Koneksi.php'; // Pastikan path ini benar
require_once __DIR__ . '/../vendor/autoload.php'; // Load Composer

// Load .env (Penting untuk Server Key)
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..'); // Path ke folder PROJECR2
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    error_log("Webhook Error: File .env tidak ditemukan.");
    http_response_code(500);
    exit("Konfigurasi server tidak ditemukan.");
}

// Konfigurasi Midtrans dari .env
$serverKey = getenv('MIDTRANS_SERVER_KEY');
$isProduction = (getenv('MIDTRANS_IS_PRODUCTION') === 'true');

if (!$serverKey) {
    error_log("Webhook Error: MIDTRANS_SERVER_KEY tidak diset di .env.");
    http_response_code(500);
    exit("Konfigurasi kunci server Midtrans tidak ditemukan.");
}

\Midtrans\Config::$serverKey = $serverKey;
\Midtrans\Config::$isProduction = $isProduction;

$log_file = __DIR__ . '/midtrans_webhook_log.txt'; // Simpan log di folder Proses
$raw_notification = file_get_contents('php://input');

// Fungsi logging
function log_message($message, $log_file, $raw_data = null) {
    $timestamp = "[" . date("Y-m-d H:i:s") . "] ";
    $log_entry = $timestamp . $message . "\n";
    if ($raw_data !== null) { $log_entry .= "Raw Data: " . $raw_data . "\n"; }
    file_put_contents($log_file, $log_entry . "\n", FILE_APPEND);
}

// Gunakan objek koneksi $conn dari Koneksi.php
if (!isset($conn) || !($conn instanceof mysqli)) {
     log_message("Webhook Error: Koneksi database tidak valid.", $log_file);
     http_response_code(500);
     exit("Koneksi DB Error.");
}


try {
    log_message("Webhook dipanggil.", $log_file, $raw_notification);
    $notif = new \Midtrans\Notification(); // Ini akan mem-parse $raw_notification dan melakukan verifikasi dasar

    $transaction_status = $notif->transaction_status;
    $order_id_midtrans = $notif->order_id; // Ini adalah midtrans_order_id dari tb_order
    $fraud_status = $notif->fraud_status ?? null;
    $payment_type = $notif->payment_type;
    $transaction_id_midtrans = $notif->transaction_id; // ID transaksi unik dari Midtrans

    log_message("Notif Parsed - Order ID: " . $order_id_midtrans . ", Status: " . $transaction_status . ", Payment Type: " . $payment_type, $log_file);

    if (empty($order_id_midtrans) || empty($transaction_status)) {
        throw new Exception("Data notifikasi tidak valid (order_id atau status kosong).");
    }

    // ==================================================================
    // LOGIKA UTAMA: Tentukan status untuk kolom 'pembayaran' di DB Anda
    // ==================================================================
    $status_pembayaran_db = null; // Default null, hanya update jika status final

    if ($transaction_status == 'capture') {
        if ($payment_type == 'credit_card') {
            if ($fraud_status == 'accept') {
                $status_pembayaran_db = 'Ya'; // LUNAS (Kartu Kredit, Fraud Aman)
            } elseif ($fraud_status == 'challenge') {
                $status_pembayaran_db = 'Challenge'; // Butuh verifikasi manual
            } else {
                 $status_pembayaran_db = 'Gagal'; // Fraud Deny
            }
        } else {
             // Capture untuk non-kartu kredit biasanya dianggap lunas
             $status_pembayaran_db = 'Ya'; // LUNAS
        }
    } else if ($transaction_status == 'settlement') {
        // Pembayaran berhasil dikonfirmasi (untuk metode selain capture kartu kredit)
        $status_pembayaran_db = 'Ya'; // LUNAS
    } else if ($transaction_status == 'pending') {
        // Pembayaran masih menunggu (misal transfer bank belum dibayar)
        $status_pembayaran_db = 'Pending'; // Biarkan atau set ke Pending
    } else if ($transaction_status == 'deny' || $transaction_status == 'failure') {
        // Pembayaran ditolak atau gagal
        $status_pembayaran_db = 'Gagal';
    } else if ($transaction_status == 'expire') {
        // Waktu pembayaran habis
        $status_pembayaran_db = 'Kadaluarsa';
    } else if ($transaction_status == 'cancel') {
        // Pembayaran dibatalkan oleh sistem atau pengguna
        $status_pembayaran_db = 'Dibatalkan';
    }
    // ==================================================================

    // Hanya update database jika statusnya final atau relevan untuk diubah
    if ($status_pembayaran_db !== null && $status_pembayaran_db !== 'Pending') { // Jangan update jika masih pending (kecuali dari status awal 'Tidak')
        log_message("Mapping Status Final - Order ID: " . $order_id_midtrans . ", DB Status akan diupdate ke: " . $status_pembayaran_db, $log_file);

        // Update tb_order berdasarkan midtrans_order_id
        // Pastikan Anda punya kolom midtrans_transaction_id
        $sql_update = "UPDATE tb_order SET pembayaran = ?, midtrans_transaction_id = ? WHERE midtrans_order_id = ?";
        $params_type = "sss"; // Tipe: pembayaran (string), midtrans_transaction_id (string), midtrans_order_id (string)
        $params_value = [$status_pembayaran_db, $transaction_id_midtrans, $order_id_midtrans];

        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            throw new Exception("DB Prepare Error: " . $conn->error . " | SQL: " . $sql_update);
        }

        $stmt_update->bind_param($params_type, ...$params_value);

        if ($stmt_update->execute()) {
            $affected_rows = $stmt_update->affected_rows;
            if ($affected_rows > 0) {
                 log_message("DB Update SUKSES - Order ID: " . $order_id_midtrans . ", Status Baru: " . $status_pembayaran_db, $log_file);
            } else {
                log_message("DB Update INFO - Order ID: " . $order_id_midtrans . ", Tidak ada baris terpengaruh (Mungkin ID tidak cocok atau status sudah sama?).", $log_file);
            }
            http_response_code(200); // Beri tahu Midtrans OK
            echo "Notification processed.";
        } else {
            throw new Exception("DB Execute Error: " . $stmt_update->error);
        }
        $stmt_update->close();

    } else {
         log_message("Status tidak memerlukan update DB atau masih Pending - Order ID: " . $order_id_midtrans . ", Status Midtrans: " . $transaction_status, $log_file);
         http_response_code(200); // Tetap kirim 200 OK agar Midtrans tidak retry untuk status pending
         echo "Notification received, no final status update required yet.";
    }


} catch (Exception $e) {
    log_message("Webhook Exception: " . $e->getMessage(), $log_file, $raw_notification);
    if (strpos($e->getMessage(), "DB") !== false || strpos($e->getMessage(), "SQL") !== false) {
        http_response_code(500); // Error Server/DB, Midtrans akan coba lagi
    } else {
        http_response_code(400); // Error data notifikasi, Midtrans mungkin tidak coba lagi
    }
    echo "Error processing notification."; // Jangan tampilkan detail error ke Midtrans
}

$conn->close();
?>