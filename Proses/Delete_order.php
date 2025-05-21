<?php
// File: /Proses/Delete_order.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// =======================================================
// DEBUGGING: Tampilkan semua parameter POST yang diterima
// Hapus atau komentari ini setelah fungsi berjalan benar
// echo "<pre style='background: #f2f2f2; padding: 15px; border: 1px solid #ccc;'>";
// echo "<strong>DEBUG (Delete_order.php) - Data POST yang diterima:</strong>\n";
// var_dump($_POST);
// echo "</pre>";
// die(); // Hentikan eksekusi untuk melihat var_dump jika perlu
// =======================================================

require_once dirname(__DIR__) . '/Admin/Koneksi.php'; // Menggunakan dirname(__DIR__) untuk path yang lebih robust

$message_type = 'danger'; // Default message type
$message_text = 'Terjadi kesalahan yang tidak diketahui saat mencoba menghapus order.';

// Ambil ID order dari POST dan pastikan itu integer positif
$id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : false;

// Periksa apakah tombol submit ditekan dan ID valid
// (Nama tombol 'submit_order_validate' dari form Anda)
if (isset($_POST['submit_order_validate']) && $id !== false && $id > 0) {
    if ($conn) {
        // Gunakan prepared statement untuk keamanan
        $query_delete = mysqli_prepare($conn, "DELETE FROM tb_order WHERE id = ?");
        if ($query_delete) {
            mysqli_stmt_bind_param($query_delete, "i", $id); // "i" untuk integer

            if (mysqli_stmt_execute($query_delete)) {
                $affected_rows = mysqli_stmt_affected_rows($query_delete);
                if ($affected_rows > 0) {
                    $message_type = 'success';
                    $message_text = 'Orderan berhasil dihapus.';
                } else {
                    $message_type = 'warning';
                    $message_text = 'Orderan tidak ditemukan atau sudah dihapus sebelumnya (tidak ada baris yang terpengaruh).';
                }
            } else {
                $message_text = 'Gagal mengeksekusi penghapusan order. Error: ' . mysqli_stmt_error($query_delete);
                error_log("Delete_order.php - Execute Error: " . mysqli_stmt_error($query_delete) . " for ID: " . $id);
            }
            mysqli_stmt_close($query_delete);
        } else {
            $message_text = 'Gagal mempersiapkan statement SQL untuk penghapusan. Error: ' . mysqli_error($conn);
            error_log("Delete_order.php - Prepare Error: " . mysqli_error($conn));
        }
        mysqli_close($conn);
    } else {
        $message_text = 'Koneksi database gagal.';
        error_log("Delete_order.php - Koneksi DB Gagal.");
    }
} else {
    $log_id_info = $id === false ? "ID tidak valid (bukan integer atau tidak ada)" : "ID kosong atau tidak valid (nilai: $id)";
    $log_submit_info = isset($_POST['submit_order_validate']) ? "Tombol submit ada." : "Tombol submit 'submit_order_validate' tidak ada.";
    $message_text = 'Aksi tidak valid atau ID order tidak ditemukan/valid.';
    error_log("Delete_order.php - Aksi tidak valid: " . $log_id_info . " | " . $log_submit_info . " | POST Data: " . json_encode($_POST));
}


$_SESSION['status_message'] = [
    'type' => $message_type,
    'text' => $message_text
];

// =======================================================
// PERBAIKAN REDIRECT
// Sesuaikan path dan parameter routing utama Anda (misalnya x=Order atau page=order)
// =======================================================
$base_admin_url = "../Admin/"; // Path ke folder Admin dari folder Proses

// Tentukan halaman tujuan dan parameter routingnya
// Ganti 'Main.php?x=Order' dengan struktur URL halaman order admin Anda
// Jika Order.php diakses langsung: 'Order.php?x=Order'
// Jika melalui Main.php: 'Main.php?x=Order' (atau parameter routing yang sesuai)
$target_page_with_route_param = "../Admin/Order"; // ASUMSI

// Jika Anda menyimpan filter terakhir di session dan ingin kembali ke filter itu:
// $filter_params = $_SESSION['last_admin_order_filter'] ?? '';
// $redirect_url = $base_admin_url . $target_page_with_route_param . ($filter_params ? '&' . $filter_params : '');

// Redirect sederhana ke halaman order (tanpa mempertahankan filter)
$redirect_url = $base_admin_url . $target_page_with_route_param;

header('Location: ' . $redirect_url);
exit;
?>