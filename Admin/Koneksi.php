<?php
    $conn = mysqli_connect(
        "localhost",
        "root",
        "",
        "db_projectpweb2");
    if (!$conn) {
        echo "Koneksi gagal";
    }
?>