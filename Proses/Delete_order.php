<?php
session_start();
include '../Admin/Koneksi.php';

$message_type = 'danger';
$message_text = 'Terjadi kesalahan yang tidak diketahui.';

$id = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : "";

if (!empty($_POST['submit_order_validate']) && !empty($id)) {
    $query_delete = mysqli_prepare($conn, "DELETE FROM tb_order WHERE id = ?");
    mysqli_stmt_bind_param($query_delete, "i", $id);

    if (mysqli_stmt_execute($query_delete)) {
        $affected_rows = mysqli_stmt_affected_rows($query_delete);
        mysqli_stmt_close($query_delete);

        if ($affected_rows > 0) {
            $message_type = 'success';
            $message_text = 'Orderan berhasil dihapus.';

        } else {
            $message_type = 'warning';
            $message_text = 'Orderan tidak ditemukan atau sudah dihapus.';
        }
    } else {
        $error_info = mysqli_stmt_error($query_delete);
        if (isset($query_delete)) mysqli_stmt_close($query_delete);
        $message_text = 'Gagal menghapus orderan. Error: ' . $error_info;
    }
} else {
    $message_text = 'Aksi tidak valid atau ID tidak ditemukan.';
}

mysqli_close($conn);

$_SESSION['status_message'] = [
    'type' => $message_type,
    'text' => $message_text
];

header('Location: ../Admin/Order');
exit;
?>