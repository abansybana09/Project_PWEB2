<?php include __DIR__ . '/../../include/header.php'; ?>

<header class="hero-header">
    <div class="overlay-content"></div>
    <img src="http://localhost/PROJECR2/img/bakar.jpg" alt="..." class="hero-bg">   
</header>

<div class="container mb-5 mt-5 pt-5">
    <div class="row justify-content-center">
        <?php if (isset($data) && is_array($data)): ?>
            <?php foreach ($data as $menu): ?>
            <div class="col-md-4">
                <div class="menu-card">
                    <div class="menu-title"><?= htmlspecialchars($menu['title']) ?></div>
                    <img src="<?= htmlspecialchars($menu['image']) ?>" alt="<?= htmlspecialchars($menu['title']) ?>">
                    <?php foreach ($menu['items'] as $item): ?>
                    <div class="menu-item">
                        <i class="fa-solid fa-check checkmark"></i><span><?= htmlspecialchars($item) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <div class="divider"></div>
                    <button class="order-btn">
                        <i class="fab fa-whatsapp whatsapp-icon"></i> Pesan Sekarang
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Menu belum tersedia.</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../include/footer.php'; ?>
