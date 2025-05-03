<?php include __DIR__ . '/../../include/header.php'; ?>

<header class="hero-header">
    <div class="overlay-content"></div>
    <img src="img/bakar.jpg" alt="Ayam Bakar Background" class="hero-bg">
</header>

<div class="container mb-5 mt-5 pt-5">
    <div class="row justify-content-center">
        <?php foreach ($data as $item): ?>
        <div class="col-md-4 mb-4">
            <div class="menu-card">
                <div class="menu-title"><?= htmlspecialchars($item['title']) ?></div>
                <img src="<?= htmlspecialchars($item['image']) ?>" 
                     alt="<?= htmlspecialchars($item['title']) ?>" 
                     class="menu-image img-fluid">
                
                <!-- Harga per item (hidden) -->
                <input type="hidden" class="base-price" value="10000"> <!-- Diubah menjadi 10000 -->
                
                <!-- Harga total yang akan diupdate -->
                <div class="menu-price">
                    Rp <span class="total-price">10.000</span> <!-- Diubah menjadi 10.000 -->
                    <small class="text-muted d-block">(Rp 10.000/porsi)</small> <!-- Diubah menjadi 10.000 -->
                </div>
                
                <!-- Quantity Selector -->
                <div class="quantity-selector text-center my-3">
                    <button class="btn btn-outline-secondary btn-sm quantity-btn minus">-</button>
                    <input type="number" class="quantity-input no-spinner" value="1" min="1" max="10">
                    <button class="btn btn-outline-secondary btn-sm quantity-btn plus">+</button>
                </div>
                
                <div class="menu-item">
                    <a href="<?= htmlspecialchars($item['whatsapp_link']) ?>" 
                       class="order-btn" 
                       target="_blank"
                       onclick="updateWhatsAppLink(this)">
                        <i class="fab fa-whatsapp whatsapp-icon"></i> Pesan Sekarang
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
// Fungsi untuk update quantity dan harga
document.querySelectorAll('.quantity-btn').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.quantity-input');
        let value = parseInt(input.value);
        
        if (this.classList.contains('minus') && value > 1) {
            value--;
        } else if (this.classList.contains('plus') && value < 10) {
            value++;
        }
        
        input.value = value;
        updateTotalPrice(input);
    });
});

// Fungsi untuk update harga total
function updateTotalPrice(input) {
    const menuCard = input.closest('.menu-card');
    const basePrice = parseFloat(menuCard.querySelector('.base-price').value);
    const quantity = parseInt(input.value);
    const totalPrice = basePrice * quantity;
    
    menuCard.querySelector('.total-price').textContent = 
        totalPrice.toLocaleString('id-ID');
}

// Fungsi untuk update link WhatsApp
function updateWhatsAppLink(linkElement) {
    const menuCard = linkElement.closest('.menu-card');
    const quantity = menuCard.querySelector('.quantity-input').value;
    const itemName = menuCard.querySelector('.menu-title').textContent;
    const totalPrice = menuCard.querySelector('.total-price').textContent;
    
    const message = `Saya mau pesan ${quantity} ${itemName} (Total: Rp ${totalPrice})`;
    const encodedMessage = encodeURIComponent(message);
    
    const originalLink = linkElement.getAttribute('href').split('?')[0];
    linkElement.setAttribute('href', `${originalLink}?text=${encodedMessage}`);
}
</script>

<style>
/* CSS untuk menghilangkan tombol spinner */
.no-spinner {
    -moz-appearance: textfield;
    width: 40px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
}

.no-spinner::-webkit-outer-spin-button,
.no-spinner::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.menu-price {
    font-size: 1.2rem;
    font-weight: bold;
    color: #d35400;
    margin: 10px 0;
}

</style>

<?php include __DIR__ . '/../../include/footer.php'; ?>