<?php
session_start();
if (empty($_SESSION['username_admin'])) {
    header('location: Login');
    exit();
}
include 'Koneksi.php';
$query = mysqli_query($conn, "SELECT * FROM tb_admin WHERE username = '$_SESSION[username_admin]'");
$hasil = mysqli_fetch_array($query);
?>

<!Doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemesanan Ayam Bakar Mang Oman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <!-- Header -->
    <?php include 'Header.php'; ?>
    <!-- End Header -->
    <div class="container-lg">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'Sidebar.php'; ?>
            <!-- End Sidebar -->

            <!-- Content -->
            <?php
            include $page;
            ?>
            <!-- End Content -->
        </div>

        <div class="fixed-bottom text-center py-1 text-white" style="background-color: #8B0000;">
            @Ayam bakar mang Oman
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>

</html>