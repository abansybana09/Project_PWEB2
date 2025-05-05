<?php
session_start(); // Mulai session di awal
include '../Admin/Koneksi.php'; // Include koneksi

$id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : 0;
$name = isset($_POST['nama']) ? trim(htmlentities($_POST['nama'])) : "";
$deskripsi = isset($_POST['deskripsi']) ? trim(htmlentities($_POST['deskripsi'])) : "";
$harga = isset($_POST['harga']) ? filter_var($_POST['harga'], FILTER_VALIDATE_INT) : false;
$stok = isset($_POST['stok']) ? filter_var($_POST['stok'], FILTER_VALIDATE_INT) : false;
$foto_lama = isset($_POST['foto_lama']) ? trim($_POST['foto_lama']) : "";

$message_type = 'danger';
$message_text = 'Terjadi kesalahan.';
$foto_sql = ""; // Bagian query untuk update foto
$params_type = "ssiii"; // Tipe parameter dasar (nama, desk, harga, stok, id)
$params_value = [$name, $deskripsi, $harga, $stok, $id]; // Nilai parameter dasar

// --- Validasi dasar ---
if ($id <= 0 || empty($name) || $harga === false || $harga < 0 || $stok === false || $stok < 0) {
     $message_text = 'Data tidak valid atau ID tidak ditemukan.';
} else {
    $target_dir = "../img/menu/"; // Pastikan path benar
    $foto_nama_baru = $foto_lama; // Default pakai foto lama
    $foto_diupload = false;

    // --- Proses Upload Jika Ada File Baru ---
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK && $_FILES['foto']['size'] > 0) {
        $imageType = strtolower(pathinfo(basename($_FILES["foto"]["name"]), PATHINFO_EXTENSION));
        $foto_nama_baru_temp = uniqid('menu_', true) . '.' . $imageType;
        $target_file = $target_dir . $foto_nama_baru_temp;
        $uploadOk = 1;

        $cek = getimagesize($_FILES["foto"]["tmp_name"]);
        if($cek === false) { $message_text = "File bukan gambar."; $uploadOk = 0; }
        elseif ($_FILES["foto"]["size"] > 5000000) { $message_text = "Ukuran file terlalu besar (maks 5MB)."; $uploadOk = 0; }
        elseif (!in_array($imageType, ["jpg", "png", "jpeg"])) { $message_text = "Hanya format JPG, JPEG, PNG."; $uploadOk = 0; }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                $foto_nama_baru = $foto_nama_baru_temp; // Gunakan nama baru jika berhasil
                $foto_diupload = true;
                $foto_sql = "foto = ?, "; // Tambahkan update foto ke query
                $params_type = "ssssii"; // Tambah tipe string untuk foto di awal
                array_unshift($params_value, $foto_nama_baru); // Tambah nama foto baru di awal array nilai
            } else {
                $message_text = 'Gagal upload foto baru.';
                $uploadOk = 0; // Tandai upload gagal
            }
        }
         // Jika upload gagal, hentikan proses lebih lanjut
         if ($uploadOk == 0) {
             // Simpan pesan error upload ke session
            $_SESSION['status_message'] = ['type' => $message_type, 'text' => $message_text];
            header('Location: ../Admin/Menu.php');
            exit;
         }
    }

     // --- Proses Update Database ---
     // Cek duplikasi nama (kecuali ID saat ini)
     $stmt_check = mysqli_prepare($conn, "SELECT id FROM tb_daftarmenu WHERE nama_menu = ? AND id != ?");
     mysqli_stmt_bind_param($stmt_check, "si", $name, $id);
     mysqli_stmt_execute($stmt_check);
     mysqli_stmt_store_result($stmt_check);

     if (mysqli_stmt_num_rows($stmt_check) > 0) {
         $message_text = "Nama menu sudah digunakan oleh menu lain.";
         // Hapus file baru jika terlanjur diupload
         if ($foto_diupload && file_exists($target_dir . $foto_nama_baru)) {
             unlink($target_dir . $foto_nama_baru);
         }
     } else {
         mysqli_stmt_close($stmt_check);

         // Gunakan Prepared Statement untuk UPDATE
         // Nama kolom DB: nama_menu, deskripsi_menu, harga, stok
         $sql = "UPDATE tb_daftarmenu SET " . $foto_sql . "nama_menu = ?, deskripsi_menu = ?, harga = ?, stok = ? WHERE id = ?";
         $stmt_update = mysqli_prepare($conn, $sql);

         // Bind parameter dinamis berdasarkan apakah foto diupload atau tidak
         mysqli_stmt_bind_param($stmt_update, $params_type, ...$params_value); // Spread operator (...)

         if (mysqli_stmt_execute($stmt_update)) {
             // Hapus foto lama jika foto baru berhasil diupload & update DB berhasil
             if ($foto_diupload && !empty($foto_lama) && $foto_lama != $foto_nama_baru) {
                 $path_foto_lama = $target_dir . $foto_lama;
                 if (file_exists($path_foto_lama)) {
                     unlink($path_foto_lama);
                 }
             }
             $message_type = 'success';
             $message_text = 'Berhasil mengupdate menu.';
         } else {
             $message_text = 'Gagal mengupdate menu: ' . mysqli_stmt_error($stmt_update);
             // Hapus file baru jika terlanjur diupload tapi DB gagal update
             if ($foto_diupload && file_exists($target_dir . $foto_nama_baru)) {
                 unlink($target_dir . $foto_nama_baru);
             }
         }
         mysqli_stmt_close($stmt_update);
     }
     // Pastikan ditutup jika check gagal dan tidak masuk else
     if (isset($stmt_check) && $stmt_check instanceof mysqli_stmt) {
        mysqli_stmt_close($stmt_check);
     }
}

// Simpan pesan ke Session
$_SESSION['status_message'] = ['type' => $message_type, 'text' => $message_text];

// Redirect kembali ke halaman Menu
header('Location: ../Admin/Menu.php');
exit;

?>