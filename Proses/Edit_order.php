<?php
session_start();
include '../Admin/Koneksi.php';

$message_type = 'danger';
$message_text = 'Terjadi kesalahan yang tidak diketahui.';

if (isset($_POST['submit_edit_order'])) {
    $id             = isset($_POST['id']) ? mysqli_real_escape_string($conn, $_POST['id']) : "";
    $pelanggan      = isset($_POST['pelanggan']) ? mysqli_real_escape_string($conn, htmlentities($_POST['pelanggan'])) : "";
    $nohp           = isset($_POST['nohp']) ? mysqli_real_escape_string($conn, htmlentities($_POST['nohp'])) : "";
    $alamat         = isset($_POST['alamat']) ? mysqli_real_escape_string($conn, htmlentities($_POST['alamat'])) : "";
    $pesanan        = isset($_POST['pesanan']) ? mysqli_real_escape_string($conn, htmlentities($_POST['pesanan'])) : "";
    $Jpesanan       = isset($_POST['jumlah_pesan']) ? mysqli_real_escape_string($conn, htmlentities($_POST['jumlah_pesan'])) : "";
    $total_harga    = isset($_POST['total_harga']) ? mysqli_real_escape_string($conn, htmlentities($_POST['total_harga'])) : "";
    $pembayaran     = isset($_POST['pembayaran']) ? mysqli_real_escape_string($conn, htmlentities($_POST['pembayaran'])) : "";

    if (!empty($id) && !empty($pelanggan) && !empty($nohp) && !empty($alamat) && !empty($pesanan) && !empty($Jpesanan) && !empty($total_harga) && !empty($pembayaran)) {
        $query = "UPDATE tb_order SET
            pelanggan = ?,
            nohp = ?,
            alamat = ?,
            pesanan = ?,
            jumlah_pesan = ?,
            total_harga = ?,
            pembayaran = ?
            WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssssi", $pelanggan, $nohp, $alamat, $pesanan, $Jpesanan, $total_harga, $pembayaran, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message_type = 'success';
                $message_text = 'Berhasil mengupdate order!';
            } else {
                $message_text = 'Gagal mengupdate order. Error: ' . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $message_text = 'Gagal mempersiapkan statement SQL. Error: ' . mysqli_error($conn);
        }
    } else {
        $missing_fields = [];
        if (empty($id)) $missing_fields[] = "ID";
        if (empty($pelanggan)) $missing_fields[] = "Pelanggan";
        if (empty($nohp)) $missing_fields[] = "No HP";
        if (empty($alamat)) $missing_fields[] = "Alamat";
        if (empty($pesanan)) $missing_fields[] = "Pesanan";
        if (empty($Jpesanan)) $missing_fields[] = "Jumlah Pesanan";
        if (empty($total_harga)) $missing_fields[] = "Total Harga";
        if (empty($pembayaran)) $missing_fields[] = "Pembayaran";
        
        $message_text = 'Semua field wajib diisi! Yang kosong: ' . implode(", ", $missing_fields);
    }
} else {
    $message_text = 'Aksi tidak valid.';
}

mysqli_close($conn);

$_SESSION['status_message'] = [
    'type' => $message_type,
    'text' => $message_text
];

header('Location: ../Admin/Order');
exit;
?>