<?php
session_start();
include '../Admin/Koneksi.php';

$name = isset($_POST['nama']) ? trim(htmlentities($_POST['nama'])) : "";
$deskripsi = isset($_POST['deskripsi']) ? trim(htmlentities($_POST['deskripsi'])) : "";
$harga = isset($_POST['harga']) ? filter_var($_POST['harga'], FILTER_VALIDATE_INT) : false;
$stok = isset($_POST['stok']) ? filter_var($_POST['stok'], FILTER_VALIDATE_INT) : false;

$message_type = 'danger';
$message_text = 'Terjadi kesalahan.';

if (empty($name) || $harga === false || $harga < 0 || $stok === false || $stok < 0) {
    $message_text = 'Data tidak valid. Pastikan Nama, Harga (angka positif), dan Stok (angka positif) terisi.';
} elseif (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] != UPLOAD_ERR_OK) {
    $message_text = 'Foto wajib diupload dan tidak boleh ada error.';
} else {
    $target_dir = "../img/menu/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0775, true);
    }
    $imageType = strtolower(pathinfo(basename($_FILES["foto"]["name"]), PATHINFO_EXTENSION));
    $foto_nama_baru = uniqid('menu_', true) . '.' . $imageType;
    $target_file = $target_dir . $foto_nama_baru;
    $statusupload = 1;

    $cek = getimagesize($_FILES["foto"]["tmp_name"]);
    if ($cek === false) {
        $message_text = "File bukan gambar!";
        $statusupload = 0;
    } elseif ($_FILES["foto"]["size"] > 5000000) {
        $message_text = "Ukuran file terlalu besar (maks 5MB)!";
        $statusupload = 0;
    } elseif (!in_array($imageType, ["jpg", "png", "jpeg"])) {
        $message_text = "Hanya format JPG, JPEG, PNG yang diperbolehkan!";
        $statusupload = 0;
    }

    if ($statusupload == 1) {
        $select = mysqli_query($conn, "SELECT id FROM tb_daftarmenu WHERE nama_menu = '$name'");
        if (mysqli_num_rows($select) > 0) {
            $message_text = "Nama menu sudah ada!";
            $statusupload = 0;
        } else {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                $query = mysqli_query($conn, "INSERT INTO tb_daftarmenu (foto, nama_menu, deskripsi_menu, harga, stok)
                VALUES ('$foto_nama_baru', '$name', '$deskripsi', '$harga', '$stok')");

                if ($query) {
                    $message_type = 'success';
                    $message_text = 'Berhasil menambahkan menu baru.';
                } else {
                    $message_text = 'Gagal menambahkan menu ke database: ' . mysqli_error($conn);
                    if (file_exists($target_file)) {
                        unlink($target_file);
                    }
                }
            } else {
                $message_text = 'Gagal upload file foto.';
            }
        }
    }
}

$_SESSION['status_message'] = ['type' => $message_type, 'text' => $message_text];

header('Location: ../Admin/Menu');

exit;
