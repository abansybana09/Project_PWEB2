<?php
include '../Admin/Koneksi.php';

$id         = isset($_POST['id']) ? htmlentities($_POST['id']) : "";
$name       = isset($_POST['nama']) ? htmlentities($_POST['nama']) : "";
$deskripsi  = isset($_POST['deskripsi']) ? htmlentities($_POST['deskripsi']) : "";
$harga      = isset($_POST['harga']) ? htmlentities($_POST['harga']) : "";
$stok       = isset($_POST['stok']) ? htmlentities($_POST['stok']) : "";

$target_dir = "../img/";
$target_file = $target_dir . basename($_FILES["foto"]["name"]);
$imageType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

if (!empty($_POST['submit_menu_validate'])) {
    if (!empty($_FILES["foto"]["tmp_name"])) {
        $cek = getimagesize($_FILES["foto"]["tmp_name"]);
        if ($cek === false) {
            $message = "<script>alert('Ini bukan file foto! Gagal upload.');
                        window.location.href = '../Admin/Menu';</script>";
            echo $message;
            exit();
        }
        if (file_exists($target_file)) {
            $message = "<script>alert('File sudah ada!');
                        window.location.href = '../Admin/Menu';</script>";
            echo $message;
            exit();
        }
        if ($_FILES["foto"]["size"] > 5000000) {
            $message = "<script>alert('Ukuran file terlalu besar!');
                        window.location.href = '../Admin/Menu';</script>";
            echo $message;
            exit();
        }
        if ($imageType != "jpg" && $imageType != "png" && $imageType != "jpeg") {
            $message = "<script>alert('Hanya file JPG, JPEG, PNG yang diizinkan!');
                        window.location.href = '../Admin/Menu';</script>";
            echo $message;
            exit();
        }

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            $update = mysqli_query($conn, "UPDATE tb_daftarmenu
                SET foto = '" . $_FILES['foto']['name'] . "',
                    nama_menu = '$name',
                    deskripsi_menu = '$deskripsi',
                    harga = '$harga',
                    stok = '$stok'
                WHERE id = $id");
        } else {
            $message = "<script>alert('Gagal upload foto baru!');
                        window.location.href = '../Admin/Menu';</script>";
            echo $message;
            exit();
        }
    } else {
        $update = mysqli_query($conn, "UPDATE tb_daftarmenu
            SET nama_menu = '$name',
                deskripsi_menu = '$deskripsi',
                harga = '$harga',
                stok = '$stok'
            WHERE id = $id");
    }

    if ($update) {
        $message = "<script>alert('Berhasil Mengupdate Menu');
                    window.location.href = '../Admin/Menu';</script>";
    } else {
        $message = "<script>alert('Gagal Mengupdate Menu');
                    window.location.href = '../Admin/Menu';</script>";
    }
}
echo $message;
?>
