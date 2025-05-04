<?php include __DIR__ . '/../../include/header.php'; ?>

<header class="hero-header">
    <div class="overlay-content"></div>
    <img src="http://localhost/PROJECR2/img/bakar.jpg" alt="Catering Background" class="hero-bg">   
</header>

<div class="container mb-5 mt-5 pt-5">
    <div class="row justify-content-center">
        <?php if (isset($data) && is_array($data) && !empty($data)): ?>
            <?php foreach ($data as $menu): ?>
                <?php if (isset($menu['title']) && isset($menu['items']) && is_array($menu['items'])): ?>
                <div class="col-md-4">
                    <div class="menu-card">
                        <div class="menu-title">
                            <?= isset($menu['title']) ? htmlspecialchars((string)$menu['title']) : 'Menu Item' ?>
                        </div>
                        
                        <?php if (isset($menu['image'])): ?>
                        <img src="<?= htmlspecialchars((string)$menu['image']) ?>" 
                             alt="<?= isset($menu['title']) ? htmlspecialchars((string)$menu['title']) : 'Menu Image' ?>">
                        <?php endif; ?>
                        
                        <?php if (isset($menu['items']) && is_array($menu['items'])): ?>
                            <?php foreach ($menu['items'] as $item): ?>
                                <?php if (!empty($item)): ?>
                                <div class="menu-item">
                                    <i class="fa-solid fa-check checkmark"></i>
                                    <span><?= htmlspecialchars((string)$item) ?></span>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div class="divider"></div>
                        
                        <?php
                        $whatsapp_number = "6289630152631"; // Replace with your number
                        $default_message = "Halo, saya ingin memesan box catering : " . 
                                         (isset($menu['title']) ? htmlspecialchars((string)$menu['title']) : "Menu");
                        ?>
                        <button class="order-btn" 
                                onclick="window.open('https://wa.me/<?= $whatsapp_number ?>?text=<?= urlencode($default_message) ?>', '_blank')">
                            <i class="fab fa-whatsapp whatsapp-icon"></i> Pesan Sekarang
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">Menu belum tersedia.</p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../include/footer.php'; ?>
<?php include __DIR__ . '/../../include/script.php'; ?>