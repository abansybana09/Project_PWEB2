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
        <i class=""></i> Pesan Sekarang
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
    <input type="tel" 
           class="form-control" 
           id="customerPhone" 
           pattern="[0-9]*" 
           title="Nomor HP harus berupa angka"
           required>
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

<?php include __DIR__ . '/../../include/footer.php'; ?>
<?php include __DIR__ . '/../../include/script.php'; ?>