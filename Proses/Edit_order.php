<?php
include '../Admin/Koneksi.php';

$id             = isset($_POST['id']) ? mysqli_real_escape_string($conn, htmlentities($_POST['id'])) : "";
$pelanggan      = isset($_POST['pelanggan']) ? mysqli_real_escape_string($conn, htmlentities($_POST['pelanggan'])) : "";
$nohp           = isset($_POST['nohp']) ? mysqli_real_escape_string($conn, htmlentities($_POST['nohp'])) : "";
$alamat         = isset($_POST['alamat']) ? mysqli_real_escape_string($conn, htmlentities($_POST['alamat'])) : "";
$pesanan        = isset($_POST['pesanan']) ? mysqli_real_escape_string($conn, htmlentities($_POST['pesanan'])) : "";
$Jpesanan       = isset($_POST['jumlah_pesan']) ? mysqli_real_escape_string($conn, htmlentities($_POST['jumlah_pesan'])) : "";
$total_harga    = isset($_POST['total_harga']) ? mysqli_real_escape_string($conn, htmlentities($_POST['total_harga'])) : "";

if ($id && $pelanggan && $nohp && $alamat && $pesanan && $total_harga) {
    $update = mysqli_query($conn, "UPDATE tb_order SET
        pelanggan = '$pelanggan',
        nohp = '$nohp',
        alamat = '$alamat',
        pesanan = '$pesanan',
        jumlah_pesan = '$Jpesanan',
        total_harga = '$total_harga'
        WHERE id = '$id'
    ");

    if ($update) {
        echo "<script>
            alert('Berhasil mengupdate order!');
            window.location.href = '../Admin/Order';
        </script>";
    } else {
        echo "<script>
            alert('Gagal mengupdate order.');
            window.location.href = '../Admin/Order';
        </script>";
    }
} else {
    echo "<script>
        alert('Semua field wajib diisi!');
        window.location.href = '../Admin/Order';
    </script>";
}
