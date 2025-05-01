<?php
// Fungsi base_url agar tidak hardcode path
function base_url($path = '') {
    return '/PROJECR2/' . ltrim($path, '/');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warung Mang Oman</title>

    <!-- CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Crimson&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Asset lokal -->
    <link rel="stylesheet" href="<?= base_url('asset/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/cabang.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/fitur.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/hero.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/kontak.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/lokasi.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/navbar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('asset/footer.css') ?>">
</head>
<body>

<header>
<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('index.php') ?>">
            <img src="<?= base_url('img/logo.png') ?>" alt="Logo Warung Mang" width="130" height="130" class="rounded-circle">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php?page=project') ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php?page=menu') ?>">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php?page=boxcatering') ?>">Box Catering</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php?page=lokasi') ?>">Lokasi</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php?page=kontak') ?>">Kontak</a></li>
            </ul>
        </div>
    </div>
</nav>
</header>
