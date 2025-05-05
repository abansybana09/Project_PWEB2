<?php
$host = 'localhost';
$dbname = 'db_projectpweb2';
$user = 'root'; // Sesuaikan jika berbeda
$pass = '';   // Sesuaikan jika ada password

// Buat koneksi
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Periksa koneksi
if (!$conn) {
    // Hentikan eksekusi dan tampilkan error jika koneksi gagal
    // Ini penting agar kita tahu jika frontend tidak bisa konek
    die("Koneksi Frontend/Model Gagal: " . mysqli_connect_error());
}

// Opsional: Set charset (rekomendasi)
mysqli_set_charset($conn, "utf8mb4");
?>