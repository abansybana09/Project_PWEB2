<?php
    include '../Admin/Koneksi.php';

    $name = isset($_POST['nama']) ? htmlentities($_POST['nama']) : "";
    $deskripsi = isset($_POST['deskripsi']) ? htmlentities($_POST['deskripsi']) : "";
    $harga = isset($_POST['harga']) ? htmlentities($_POST['harga']) : "";
    $stok = isset($_POST['stok']) ? htmlentities($_POST['stok']) : "";

    $target_dir = "../img/";
    $target_file = $target_dir . basename($_FILES["foto"]["name"]);
    $imageType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if(!empty($_POST['submit_menu_validate'])) {
    $cek = getimagesize($_FILES["foto"]["tmp_name"]);
    if($cek === false) {
        $message = "Ini Bukan Foto!";
        $statusupload = 0;
    }else{
        $statusupload = 1;
        if(file_exists($target_file)) {
            $message = "File sudah ada!";
            $statusupload = 0;
        }else{
            if($_FILES["foto"]["size"] > 5000000) {
                $message = "File terlalu besar!";
                $statusupload = 0;
        }else{
            if($imageType != "jpg" && $imageType != "png" && $imageType != "jpeg") {
                $message = "Hanya JPG, JPEG, PNG yang diperbolehkan!";
                $statusupload = 0;
            }
        }
    }
}
    if($statusupload == 0) {
        $message = "<script>alert('".$message.", Gagal upload foto');
        window.location.href = '../Admin/Menu'</script>";
    }else{
        $select = mysqli_query($conn, "SELECT * FROM tb_daftarmenu WHERE nama_menu = '$name'");
        if(mysqli_num_rows($select) > 0){
            $message = "<script>alert('Menu Sudah Ada');
                        window.location.href = '../Admin/Menu';
                        </script>";
    }else {
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            $query = mysqli_query($conn, "INSERT INTO tb_daftarmenu (foto, nama_menu, deskripsi_menu, harga, stok)
            VALUES ('".$_FILES['foto']['name']."', '$name', '$deskripsi', '$harga', '$stok')");
            if ($query) {
                $message = "<script>
                            alert('Berhasil Menambahkan Menu');
                            window.location.href = '../Admin/Menu';
                            </script>";
            } else {
                $message = "<script>alert('Gagal Menambahkan Menu');
                            window.location.href = '../Admin/Menu';
                            </script>";
            }
        }else{
            $message = "<script>alert('Gagal upload foto');
                        window.location.href = '../Admin/Menu';
                        </script>";
        }
    }
}
}
echo $message;
?>