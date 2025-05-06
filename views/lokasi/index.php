<?php include __DIR__ . '/../../include/header.php'; ?>

<header class="hero-header">
    <div class="overlay-content"></div>
    <img src="img/bakar.jpg" alt="Ayam Bakar Background" class="hero-bg">
</header>

<div class="container mb-5 mt-5 pt-5">
    <!-- Judul Cabang -->
    <div class="judul-cabang text-center mb-5">
        <h1 class="judul-kecil">Cabang</h1>
        <h1 class="judul-besar">Warung Mang Oman</h1>
    </div>

    <div class="row justify-content-center">
        <!-- Jika data cabang berupa array multidimensi -->
        <?php foreach ($data as $cabang): ?>
            <a href="<?= htmlspecialchars($cabang['maps_url']) ?>" target="_blank" class="menu-card1 text-decoration-none">
    <img src="<?= htmlspecialchars($cabang['image']) ?>" alt="<?= htmlspecialchars($cabang['title']) ?>" class="branch-img">
    <div class="location-tag"><?= htmlspecialchars($cabang['title']) ?></div>
</a>

        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../../include/footer.php'; ?>
<?php include __DIR__ . '/../../include/script.php'; ?>
