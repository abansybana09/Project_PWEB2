<?php
// File: /Proses/Delete_order.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../Admin/Koneksi.php';

$message_type = 'danger';
$message_text = 'Terjadi kesalahan yang tidak diketahui saat mencoba menghapus order.';

// Ambil ID order dari POST dan pastikan itu integer
$id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : false;

// Periksa apakah tombol submit ditekan dan ID valid
if (isset($_POST['submit_order_validate']) && $id !== false && $id > 0) {
    if ($conn) {
        $query_delete = mysqli_prepare($conn, "DELETE FROM tb_order WHERE id = ?");
        if ($query_delete) {
            mysqli_stmt_bind_param($query_delete, "i", $id);

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
    $log_id_info = $id === false ? "ID tidak valid (bukan integer)" : "ID kosong atau tidak valid (nilai: $id)";
    $log_submit_info = isset($_POST['submit_order_validate']) ? "Submit validate ada." : "Submit validate tidak ada.";
    $message_text = 'Aksi tidak valid atau ID order tidak ditemukan/valid.';
    error_log("Delete_order.php - Aksi tidak valid: " . $log_id_info . " | " . $log_submit_info . " | POST Data: " . json_encode($_POST));
}


$_SESSION['status_message'] = [
    'type' => $message_type,
    'text' => $message_text
];

$redirect_url = "../Admin/Order";
header('Location: ' . $redirect_url);
exit;
?>