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
// Fungsi showOrderForm(buttonElement) dan submitOrder() tetap sama
// Pastikan fungsi-fungsi ini ada di sini atau di script.php yang di-include
function showOrderForm(buttonElement) {
    const card = buttonElement.closest('.menu-card');
    const titleElement = card.querySelector('.menu-title');
    const inputElement = card.querySelector('.quantity-input');
    const priceElement = card.querySelector('.base-price');

    if (!titleElement || !inputElement || !priceElement) {
        console.error("Elemen menu tidak ditemukan dalam kartu:", card);
        alert("Terjadi kesalahan saat memproses item ini.");
        return;
    }

    const itemName = titleElement.textContent;
    const quantity = inputElement.value;
    const basePrice = parseFloat(priceElement.value);
    const whatsappLink = buttonElement.getAttribute('data-whatsapp');

    // Ambil nilai quantity yang sudah divalidasi
    let currentQuantity = parseInt(quantity);
    const maxQty = parseInt(inputElement.getAttribute('max') || '1');
    if (isNaN(currentQuantity) || currentQuantity < 1) currentQuantity = 1;
    if (currentQuantity > maxQty) currentQuantity = maxQty;

    const totalPrice = basePrice * currentQuantity;

    document.getElementById('orderItemName').value = itemName;
    document.getElementById('orderQuantity').value = currentQuantity;
    document.getElementById('orderTotalPrice').value = totalPrice;
    document.getElementById('orderWhatsappLink').value = whatsappLink;

    document.getElementById('orderDetails').innerHTML = `Nama Item: ${itemName}<br>Jumlah: ${currentQuantity}`;
    document.getElementById('orderPrice').textContent = `Total Harga: Rp ${totalPrice.toLocaleString('id-ID')}`;

    try {
        const orderModalElement = document.getElementById('orderModal');
        if (orderModalElement) {
             const orderModal = new bootstrap.Modal(orderModalElement);
             orderModal.show();
        } else {
             console.error("Elemen modal #orderModal tidak ditemukan.");
             alert("Tidak dapat menampilkan form pemesanan.");
        }
    } catch (e) {
        console.error("Error saat menampilkan modal:", e);
        alert("Terjadi kesalahan saat menampilkan form pemesanan.");
    }
}

function submitOrder() {
    const itemName = document.getElementById('orderItemName').value;
    const quantity = document.getElementById('orderQuantity').value;
    const totalPrice = document.getElementById('orderTotalPrice').value;
    const customerName = document.getElementById('customerName').value;
    const customerPhone = document.getElementById('customerPhone').value;
    const customerAddress = document.getElementById('customerAddress').value;
    const baseWhatsappLink = document.getElementById('orderWhatsappLink').value;

    if (!customerName || !customerPhone || !customerAddress) {
        alert('Mohon lengkapi semua field yang bertanda *');
        return;
    }

    let message = `Halo Mang Oman, saya mau pesan:\n\n`;
    message += `Nama Pelanggan: ${customerName}\n`;
    message += `No. HP: ${customerPhone}\n`;
    message += `Alamat Pengiriman: ${customerAddress}\n\n`;
    message += `Pesanan:\n`;
    message += `- ${itemName} (${quantity} porsi)\n`;
    message += `Total Harga: Rp ${parseInt(totalPrice).toLocaleString('id-ID')}\n\n`;
    message += `Mohon konfirmasi pesanannya. Terima kasih.`;

    const whatsappUrl = `${baseWhatsappLink}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');

    try {
        const orderModalElement = document.getElementById('orderModal');
         if (orderModalElement) {
            const orderModalInstance = bootstrap.Modal.getInstance(orderModalElement);
            if (orderModalInstance) {
                 orderModalInstance.hide();
            }
         }
    } catch (e) {
         console.error("Error saat menyembunyikan modal:", e);
    }

    setTimeout(() => {
        const orderForm = document.getElementById('orderForm');
        if(orderForm) orderForm.reset();
        const orderDetails = document.getElementById('orderDetails');
        if(orderDetails) orderDetails.innerHTML = `Nama Item: -<br>Jumlah: -`;
        const orderPrice = document.getElementById('orderPrice');
        if(orderPrice) orderPrice.textContent = `Total Harga: Rp -`;
    }, 300);
}

// ===========================================================
// REVISI LOGIKA QUANTITY SELECTOR
// ===========================================================
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quantity-selector').forEach(selector => {
        const minusBtn = selector.querySelector('.minus');
        const plusBtn = selector.querySelector('.plus');
        const input = selector.querySelector('.quantity-input');
        const card = selector.closest('.menu-card');
        // Pastikan elemen-elemen ini ada sebelum melanjutkan
        if (!minusBtn || !plusBtn || !input || !card) {
            console.warn("Elemen quantity selector tidak lengkap pada salah satu kartu.", selector);
            return; // Lanjut ke kartu berikutnya
        }

        const basePriceElement = card.querySelector('.base-price');
        const totalPriceSpan = card.querySelector('.total-price');

        if (!basePriceElement || !totalPriceSpan) {
            console.warn("Elemen harga tidak ditemukan pada salah satu kartu.", card);
            return; // Lanjut ke kartu berikutnya
        }

        const basePrice = parseFloat(basePriceElement.value);
        const maxQty = parseInt(input.getAttribute('max') || '10'); // Default max 10 jika tidak ada

        // Fungsi untuk update harga dan validasi input
        function updateDisplay() {
            let currentValue = parseInt(input.value);

            // Validasi ketat
            if (isNaN(currentValue)) {
                currentValue = 1; // Default jika input bukan angka
            }
            currentValue = Math.max(1, Math.min(currentValue, maxQty)); // Batasi antara 1 dan maxQty

            // Hanya update DOM jika nilainya benar-benar berubah dari yang diinput
            // Ini untuk mencegah potensi loop dari event 'input' jika kita set .value lagi
            if (input.value !== currentValue.toString()) {
                 input.value = currentValue;
            }

            const newTotal = basePrice * currentValue;
            totalPriceSpan.textContent = newTotal.toLocaleString('id-ID', { minimumFractionDigits: 0 });
        }

        minusBtn.addEventListener('click', () => {
            let currentValue = parseInt(input.value) || 1; // Default ke 1 jika NaN
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateDisplay(); // Update setelah nilai diubah
            }
        });

        plusBtn.addEventListener('click', () => {
            let currentValue = parseInt(input.value) || 1; // Default ke 1 jika NaN
            if (currentValue < maxQty) {
                input.value = currentValue + 1;
                updateDisplay(); // Update setelah nilai diubah
            }
        });

        // Event 'change' lebih cocok untuk validasi akhir setelah user selesai input
        // Event 'input' bisa terlalu sering memicu jika validasinya mengubah .value lagi
        input.addEventListener('change', () => {
            updateDisplay(); // Validasi dan update saat user selesai mengubah input
        });
        input.addEventListener('blur', () => { // Juga saat kehilangan fokus
            updateDisplay();
        });


        // Inisialisasi harga saat halaman pertama kali dimuat
        updateDisplay();
    });
});
</script>
