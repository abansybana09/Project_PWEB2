<?php
include '../Admin/Koneksi.php';

$id = isset($_POST['id']) ? htmlentities($_POST['id']) : "";

if (!empty($_POST['submit_order_validate'])) {

    $query = mysqli_query($conn, "DELETE FROM tb_order WHERE id = $id");

    if ($query) {
        mysqli_query($conn, "SET @count = 0");
        mysqli_query($conn, "UPDATE tb_order SET id = @count := @count + 1");
        mysqli_query($conn, "ALTER TABLE tb_order AUTO_INCREMENT = 1");

        $message = "<script>
            alert('Orderan berhasil dihapus');
            window.location.href = '../Admin/Order';
        </script>";
    } else {
        $message = "<script>
            alert('Gagal menghapus orderan');
            window.location.href = '../Admin/Order';
        </script>";
    }
    echo $message;
}
?>