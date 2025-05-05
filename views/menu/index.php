<?php
// File: PROJECR2/views/menu/index.php

// Include header (ensure the path is correct)
include __DIR__ . '/../../include/header.php';
?>

<header class="hero-header">
  <div class="overlay-content"></div>
  <img src="img/bakar.jpg" alt="Ayam Bakar Background" class="hero-bg">
</header>

<div class="container mb-5 mt-5 pt-5">
  <h2 class="text-center mb-4">Menu Kami</h2>

  <div class="row justify-content-center">
    <?php if (isset($data) && is_array($data) && !empty($data)): ?>
      <?php foreach ($data as $item): ?>
        <div class="col-md-4 mb-4 d-flex align-items-stretch">
          <div class="menu-card">
            <img src="<?= htmlspecialchars($item['image'] ?? 'img/placeholder.png') ?>"
               alt="<?= htmlspecialchars($item['title'] ?? 'Menu') ?>"
               class="menu-image"
               onerror="this.onerror=null; this.src='img/placeholder.png';">

            <div class="menu-card-content">
              <div class="menu-title"><?= htmlspecialchars($item['title'] ?? 'Menu') ?></div>

              <p class="menu-description">
                <?= htmlspecialchars($item['description'] ?? 'Deskripsi tidak tersedia.') ?>
              </p>

              <div class="menu-price">
                <input type="hidden" class="base-price" value="<?= htmlspecialchars($item['price'] ?? 0) ?>">
                Rp <span class="total-price"><?= number_format($item['price'] ?? 0, 0, ',', '.') ?></span>
                <small class="text-muted d-block">(Rp <?= number_format($item['price'] ?? 0, 0, ',', '.') ?>/porsi)</small>
              </div>

              <div class="quantity-selector text-center">
                <button class="btn btn-outline-secondary btn-sm quantity-btn minus" aria-label="Kurangi jumlah">-</button>
                <input type="number" class="quantity-input no-spinner" value="1" min="1" max="<?= max(1, htmlspecialchars($item['stock'] ?? 1)) ?>" aria-label="Jumlah">
                <button class="btn btn-outline-secondary btn-sm quantity-btn plus" aria-label="Tambah jumlah">+</button>
              </div>
            </div>

            <div class="menu-item">
              <button class="order-btn"
                  onclick="showOrderForm(this)"
                  data-item-title="<?= htmlspecialchars($item['title'] ?? 'Menu') ?>"
                  data-base-price="<?= htmlspecialchars($item['price'] ?? 0) ?>"
                  data-whatsapp="<?= htmlspecialchars($item['whatsapp_link'] ?? 'https://wa.me/6289630152631') ?>">
                <i class="bi bi-whatsapp whatsapp-icon"></i> Pesan Sekarang
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <p><?= isset($data) && is_array($data) && empty($data) ? 'Belum ada menu yang tersedia saat ini.' : 'Tidak dapat memuat data menu saat ini.' ?></p>
      </div>
    <?php endif; ?>
  </div>
</div>

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
            <input type="tel" class="form-control" id="customerPhone" pattern="[0-9]*" title="Nomor HP harus berupa angka" required>
          </div>
          <div class="mb-3">
            <label for="customerAddress" class="form-label">Alamat Pengiriman *</label>
            <textarea class="form-control" id="customerAddress" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Detail Pesanan</label>
            <div class="card p-3 bg-light">
              <p id="orderDetails">Nama Item: -<br>Jumlah: -</p>
              <p id="orderPrice" class="fw-bold mt-2">Total Harga: Rp -</p>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" onclick="submitOrder()">
          <i class="bi bi-whatsapp"></i> Kirim via WhatsApp
        </button>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../include/footer.php'; ?>
<?php include __DIR__ . '/../../include/script.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.quantity-selector').forEach(selector => {
    const minusBtn = selector.querySelector('.minus');
    const plusBtn = selector.querySelector('.plus');
    const input = selector.querySelector('.quantity-input');
    const card = selector.closest('.menu-card');
    const basePrice = parseFloat(card.querySelector('.base-price').value);
    const totalPriceSpan = card.querySelector('.total-price');
    const maxQty = parseInt(input.getAttribute('max') || 10);

    const updatePrice = () => {
      let quantity = parseInt(input.value.replace(/\D/g, ''), 10); // Bersihkan input hanya angka
      if (isNaN(quantity)) quantity = 1;
      quantity = Math.max(1, Math.min(quantity, maxQty));
      input.value = quantity;
      totalPriceSpan.textContent = (basePrice * quantity).toLocaleString('id-ID', {
        minimumFractionDigits: 0
      });
    };

    minusBtn.addEventListener('click', () => {
      let quantity = parseInt(input.value) || 1;
      quantity = Math.max(1, quantity - 1);
      input.value = quantity;
      updatePrice();
    });

    plusBtn.addEventListener('click', () => {
      let quantity = parseInt(input.value) || 1;
      quantity = Math.min(maxQty, quantity + 1);
      input.value = quantity;
      updatePrice();
    });

    input.addEventListener('input', updatePrice);

    // Jalankan pertama kali untuk inisialisasi
    updatePrice();
  });
});
</script>
