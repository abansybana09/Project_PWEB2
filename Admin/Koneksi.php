<?php
// File: PROJECR2/Admin/Koneksi.php
$host = 'localhost';
$dbname = 'db_projectpweb2';
$user = 'root'; // Sesuaikan jika berbeda
$pass = '';   // Sesuaikan jika ada password

// Buat koneksi
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Periksa koneksi
if (!$conn) {
    // Catat error fatal jika koneksi gagal
    error_log("FATAL: Koneksi Database Gagal: " . mysqli_connect_error());
    // Tampilkan pesan generik ke pengguna atau hentikan skrip
    die("Tidak dapat terhubung ke database.");
}

// Set charset (rekomendasi)
if (!mysqli_set_charset($conn, "utf8mb4")) {
     error_log("Warning: Gagal set charset utf8mb4: " . mysqli_error($conn));
}

// Variabel $conn sekarang tersedia untuk di-include
?>