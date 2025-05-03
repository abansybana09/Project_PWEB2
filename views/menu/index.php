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
    <button class="order-btn" 
            onclick="showOrderForm(this)"
            data-whatsapp="<?= htmlspecialchars($item['whatsapp_link']) ?>">
        <i class="fab fa-whatsapp whatsapp-icon"></i> Pesan Sekarang
    </button>
</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Tambahkan modal form sebelum </body> -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Pemesanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="orderForm">
          <input type="hidden" id="orderItemName">
          <input type="hidden" id="orderQuantity">
          <input type="hidden" id="orderTotalPrice">
          <input type="hidden" id="orderWhatsappLink">
          
          <div class="mb-3">
            <label for="customerName" class="form-label">Nama Pelanggan *</label>
            <input type="text" class="form-control" id="customerName" required>
          </div>
          
          <div class="mb-3">
            <label for="customerPhone" class="form-label">No. HP *</label>
            <input type="tel" class="form-control" id="customerPhone" required>
          </div>
          
          <div class="mb-3">
            <label for="customerAddress" class="form-label">Alamat Pengiriman *</label>
            <textarea class="form-control" id="customerAddress" rows="3" required></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Detail Pesanan</label>
            <div class="card p-3">
              <p id="orderDetails"></p>
              <p id="orderPrice" class="fw-bold mt-2"></p>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" onclick="submitOrder()">
          <i class="fab fa-whatsapp"></i> Kirim via WhatsApp
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Fungsi untuk menampilkan modal form
function showOrderForm(buttonElement) {
    const menuCard = buttonElement.closest('.menu-card');
    const itemName = menuCard.querySelector('.menu-title').textContent;
    const quantity = menuCard.querySelector('.quantity-input').value;
    const totalPrice = menuCard.querySelector('.total-price').textContent;
    const whatsappLink = buttonElement.getAttribute('data-whatsapp');
    
    // Isi data ke modal
    document.getElementById('orderItemName').value = itemName;
    document.getElementById('orderQuantity').value = quantity;
    document.getElementById('orderTotalPrice').value = totalPrice;
    document.getElementById('orderWhatsappLink').value = whatsappLink;
    document.getElementById('orderDetails').innerHTML = `
        <strong>${itemName}</strong><br>
        Jumlah: ${quantity} porsi
    `;
    document.getElementById('orderPrice').innerHTML = `
        Total: Rp ${totalPrice}
    `;
    
    // Tampilkan modal
    const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
    orderModal.show();
}

// Fungsi untuk submit pesanan ke WhatsApp
function submitOrder() {
    const customerName = document.getElementById('customerName').value;
    const customerPhone = document.getElementById('customerPhone').value;
    const customerAddress = document.getElementById('customerAddress').value;
    const itemName = document.getElementById('orderItemName').value;
    const quantity = document.getElementById('orderQuantity').value;
    const totalPrice = document.getElementById('orderTotalPrice').value;
    const baseWhatsappLink = document.getElementById('orderWhatsappLink').value;
    
    // Validasi form
    if (!customerName || !customerPhone || !customerAddress) {
        alert('Harap isi semua data yang wajib diisi!');
        return;
    }
    
    // Format pesan WhatsApp
    const message = `Halo Warung Mang Oman,\n\nSaya mau pesan:\n\n*${itemName}*\nJumlah: ${quantity} porsi\nTotal: Rp ${totalPrice}\n\n*Data Pemesan:*\nNama: ${customerName}\nNo. HP: ${customerPhone}\nAlamat: ${customerAddress}`;
    const encodedMessage = encodeURIComponent(message);
    
    // Gabungkan dengan base link WhatsApp
    const whatsappUrl = `${baseWhatsappLink.split('?')[0]}?text=${encodedMessage}`;
    
    // Buka WhatsApp
    window.open(whatsappUrl, '_blank');
    
    // Tutup modal
    const orderModal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
    orderModal.hide();
    
    // Reset form
    document.getElementById('orderForm').reset();
}

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

<?php include __DIR__ . '/../../include/footer.php'; ?>