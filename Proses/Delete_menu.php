<?php
session_start();
include '../Admin/Koneksi.php';

$id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : 0;
$foto_lama = isset($_POST['foto_lama']) ? trim($_POST['foto_lama']) : "";

$message_type = 'danger';
$message_text = 'Terjadi kesalahan.';

// Validasi ID
if ($id <= 0) {
    $message_text = 'ID menu tidak valid.';
} else {
    $target_dir = "../img/menu/";

    $stmt_delete = mysqli_prepare($conn, "DELETE FROM tb_daftarmenu WHERE id = ?");
    mysqli_stmt_bind_param($stmt_delete, "i", $id);

    if (mysqli_stmt_execute($stmt_delete)) {
        if (mysqli_stmt_affected_rows($stmt_delete) > 0) {
            if (!empty($foto_lama)) {
                $filePath = $target_dir . $foto_lama;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $message_type = 'success';
            $message_text = 'Menu berhasil dihapus.';
        } else {
            $message_text = 'Menu dengan ID tersebut tidak ditemukan (mungkin sudah dihapus).';
            $message_type = 'warning';
        }
    } else {
        $message_text = 'Gagal menghapus menu dari database: ' . mysqli_stmt_error($stmt_delete);
    }
    mysqli_stmt_close($stmt_delete);
}

$_SESSION['status_message'] = ['type' => $message_type, 'text' => $message_text];

header('Location: ../Admin/Menu');
exit;
