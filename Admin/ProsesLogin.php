<?php
session_start();
include 'Koneksi.php';

$username = isset($_POST['username']) ? htmlentities($_POST['username']) : "";
$password = isset($_POST['password']) ? htmlentities($_POST['password']) : "";
if (!empty($_POST['submit_validasi'])) {
    $query = mysqli_query($conn, "SELECT * FROM tb_admin WHERE username = '$username' AND password = '$password'");
    $hasil = mysqli_fetch_array($query);
    if ($hasil) {
        $_SESSION['username_admin'] = $username;
        header('location: Home');
        exit();
    } else {
    }
}
?>
<script>
    alert('Username atau Password Salah');
    window.location.href = 'Login';
</script>