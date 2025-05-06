<?php
$host = 'localhost';
$dbname = 'db_projectpweb2';
$user = 'root';
$pass = '';

// Buat koneksi
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Periksa koneksi
if (!$conn) {
    die("Koneksi Frontend/Model Gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>