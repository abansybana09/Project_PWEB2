<?php
include '../Admin/Koneksi.php';

$id = isset($_POST['id']) ? htmlentities($_POST['id']) : "";

if (!empty($_POST['submit_menu_validate'])) {
    $getFoto = mysqli_query($conn, "SELECT foto FROM tb_daftarmenu WHERE id = $id");
    $dataFoto = mysqli_fetch_assoc($getFoto);

    if ($dataFoto) {
        $filePath = "../img/" . $dataFoto['foto'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $query = mysqli_query($conn, "DELETE FROM tb_daftarmenu WHERE id = $id");
    if ($query) {
        mysqli_query($conn, "SET @count = 0");
        mysqli_query($conn, "UPDATE tb_daftarmenu SET id = @count := @count + 1");
        mysqli_query($conn, "ALTER TABLE tb_daftarmenu AUTO_INCREMENT = 1");

        $message = "<script>alert('Menu Berhasil Dihapus');
        window.location.href = '../Admin/Menu';
        </script>";
    } else {
        $message = "<script>alert('Gagal Menghapus Menu');
        window.location.href = '../Admin/Menu';
        </script>";
    }
}
echo $message;
?>
