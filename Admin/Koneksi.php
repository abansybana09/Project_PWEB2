<?php
$host = 'localhost';
$dbname = 'db_projectpweb2';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    error_log("FATAL: Koneksi Database Gagal: " . mysqli_connect_error());
    die("Tidak dapat terhubung ke database.");
}

if (!mysqli_set_charset($conn, "utf8mb4")) {
     error_log("Warning: Gagal set charset utf8mb4: " . mysqli_error($conn));
}
?>