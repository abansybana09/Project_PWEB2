<?php
// File: /Proses/Edit_order.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// =======================================================
// DEBUGGING: Tampilkan semua parameter POST yang diterima
// Hapus atau komentari ini setelah fungsi berjalan benar
// echo "<pre style='background: #f2f2f2; padding: 15px; border: 1px solid #ccc;'>";
// echo "<strong>DEBUG (Edit_order.php) - Data POST yang diterima:</strong>\n";
// var_dump($_POST);
// echo "</pre>";
// die(); // Hentikan eksekusi untuk melihat var_dump jika perlu
// =======================================================

require_once dirname(__DIR__) . '/Admin/Koneksi.php'; // Menggunakan dirname(__DIR__)

$message_type = 'danger'; // Default message type
$message_text = 'Terjadi kesalahan yang tidak diketahui saat mencoba mengupdate order.';

if (isset($_POST['submit_edit_order'])) {
    // Ambil dan sanitasi input
    $id             = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : false;
    $pelanggan      = isset($_POST['pelanggan']) ? trim(htmlspecialchars($_POST['pelanggan'])) : "";
    $nohp           = isset($_POST['nohp']) ? trim(htmlspecialchars($_POST['nohp'])) : ""; // Diperlakukan sebagai string
    $alamat         = isset($_POST['alamat']) ? trim(htmlspecialchars($_POST['alamat'])) : "";
    $pesanan        = isset($_POST['pesanan']) ? trim(htmlspecialchars($_POST['pesanan'])) : "";
    $Jpesanan_str   = isset($_POST['jumlah_pesan']) ? trim(htmlspecialchars($_POST['jumlah_pesan'])) : "";
    $total_harga_str= isset($_POST['total_harga']) ? trim(htmlspecialchars($_POST['total_harga'])) : ""; // Ini angka dari input
    $pembayaran     = isset($_POST['pembayaran']) ? trim(htmlspecialchars($_POST['pembayaran'])) : "";

    // Validasi dasar
    $missing_fields = [];
    if ($id === false || $id <= 0) $missing_fields[] = "ID Order tidak valid";
    if (empty($pelanggan)) $missing_fields[] = "Pelanggan";
    if (empty($nohp)) $missing_fields[] = "No HP";
    // Anda bisa tambahkan validasi format No HP di sini jika perlu (misal panjang, hanya angka)
    if (empty($alamat)) $missing_fields[] = "Alamat";
    if (empty($pesanan)) $missing_fields[] = "Pesanan";
    if (empty($Jpesanan_str)) $missing_fields[] = "Jumlah Pesanan";
    if (empty($total_harga_str)) $missing_fields[] = "Total Harga";
    if (empty($pembayaran)) $missing_fields[] = "Status Pembayaran";

    $Jpesanan = filter_var($Jpesanan_str, FILTER_VALIDATE_INT);
    // Total harga sudah angka dari input type="number", tidak perlu preg_replace lagi, cukup validasi
    $total_harga_numeric = filter_var($total_harga_str, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION); // Jika bisa desimal
    // Atau jika harga selalu bulat:
    // $total_harga_numeric = filter_var($total_harga_str, FILTER_VALIDATE_INT);


    if ($Jpesanan === false || $Jpesanan <= 0) $missing_fields[] = "Jumlah Pesanan harus angka positif";
    if ($total_harga_numeric === false || $total_harga_numeric < 0) $missing_fields[] = "Total Harga tidak valid";


    if (empty($missing_fields)) {
        if ($conn) {
            $query = "UPDATE tb_order SET
                pelanggan = ?,
                nohp = ?,
                alamat = ?,
                pesanan = ?,
                jumlah_pesan = ?,
                total_harga = ?, /* Simpan sebagai angka */
                pembayaran = ?
                WHERE id = ?";

            $stmt = mysqli_prepare($conn, $query);
            if ($stmt) {
                // Sesuaikan tipe data bind_param: s=string, i=integer, d=double/decimal
                // Asumsi: pelanggan(s), nohp(s), alamat(s), pesanan(s), jumlah(i), harga(d), pembayaran(s), id(i)
                $tipe_data_bind_edit = "ssssidsi"; // Mengasumsikan total_harga disimpan sebagai DECIMAL/DOUBLE
                // Jika total_harga disimpan sebagai INT, ganti 'd' menjadi 'i': "ssssiisi"

                mysqli_stmt_bind_param($stmt, $tipe_data_bind_edit, $pelanggan, $nohp, $alamat, $pesanan, $Jpesanan, $total_harga_numeric, $pembayaran, $id);

                if (mysqli_stmt_execute($stmt)) {
                    if (mysqli_stmt_affected_rows($stmt) > 0) {
                        $message_type = 'success';
                        $message_text = 'Berhasil mengupdate order!';
                    } else {
                        $message_type = 'info';
                        $message_text = 'Tidak ada perubahan data pada order atau order tidak ditemukan.';
                    }
                } else {
                    $message_text = 'Gagal mengeksekusi update order. Error: ' . mysqli_stmt_error($stmt);
                    error_log("Edit_order.php - Execute Error: " . mysqli_stmt_error($stmt) . " for ID: " . $id);
                }
                mysqli_stmt_close($stmt);
            } else {
                $message_text = 'Gagal mempersiapkan statement SQL untuk update. Error: ' . mysqli_error($conn);
                error_log("Edit_order.php - Prepare Error: " . mysqli_error($conn));
            }
            mysqli_close($conn);
        } else {
            $message_text = 'Koneksi database gagal.';
            error_log("Edit_order.php - Koneksi DB Gagal.");
        }
    } else {
        $message_text = 'Semua field wajib diisi dengan benar! Yang bermasalah: ' . implode(", ", $missing_fields);
        error_log("Edit_order.php - Validasi Gagal: " . implode(", ", $missing_fields) . " | POST Data: " . json_encode($_POST));
    }
} else {
    $message_text = 'Aksi tidak valid (tombol submit tidak ditekan).';
    error_log("Edit_order.php - Aksi tidak valid. POST Data: " . json_encode($_POST));
}


$_SESSION['status_message'] = [
    'type' => $message_type,
    'text' => $message_text
];

// =======================================================
// PERBAIKAN REDIRECT
// =======================================================
$base_admin_url = "../Admin/";
$target_page_with_route_param = "../Admin/Order"; // ASUMSI
// Jika Order.php diakses langsung:
// $target_page_with_route_param = "Order.php?x=Order";

$redirect_url = $base_admin_url . $target_page_with_route_param;

header('Location: ' . $redirect_url);
exit;
?>