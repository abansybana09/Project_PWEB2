<?php include __DIR__ . '/../../include/header.php'; ?>

<div class="hero">
    <img src="<?= htmlspecialchars($data['hero']['image']) ?>" alt="Ayam Goreng Background">
    <div class="hero-text">
        <h1><span class="highlight"><?= htmlspecialchars($data['hero']['title']) ?></span></h1>
        <p><?= htmlspecialchars($data['hero']['subtitle']) ?></p>
    </div>
</div>

<section class="features py-5 bg-light">
    <div class="container text-center">
        <div class="row">
            <?php foreach ($data['features'] as $item): ?>
            <div class="col-md-4">
                <a href="<?= htmlspecialchars($item['link']) ?>" class="feature-link">
                    <div class="feature">
                        <img src="<?= htmlspecialchars($item['image']) ?>" 
                             alt="<?= htmlspecialchars($item['title']) ?>" 
                             width="161" height="159" class="rounded-circle">
                        <h3><?= htmlspecialchars($item['title']) ?></h3>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="lokasi py-5 text-center">
    <h1 class="mt-4">Info Lebih Lanjut</h1>
    <div class="container section">
        <div class="row justify-content-center align-items-center text-center">
            <div class="col-md-4 mb-4 mb-md-0">
                <iframe 
                    src="<?= htmlspecialchars($data['location']['map_url']) ?>" 
                    width="100%" 
                    height="250" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="icon-circle mb-3">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h5 class="fw-bold text-secondary">Lokasi Cabang Kami</h5>
            </div>

            <div class="col-md-4">
                <a href="https://wa.me/<?= htmlspecialchars($data['location']['whatsapp_number']) ?>?text=<?= urlencode($data['location']['whatsapp_message']) ?>" target="_blank">
                    <i class="fab fa-whatsapp whatsapp-icon1"></i>
                </a>
                <div class="whatsapp-text mt-2">Chat us</div>
                <div class="text-success">on Whatsapp for Catering & Partnership</div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../include/footer.php'; ?>