<?php
// File: PROJECR2/views/menu/index.php

// Diasumsikan $data (data menu) sudah dikirim dari MenuController
// Diasumsikan session_start() sudah dipanggil di index.php utama
// Diasumsikan autoloader Composer sudah di-require di index.php utama
// Diasumsikan .env sudah di-load di index.php utama ATAU kita load di sini

// Load .env jika belum di index.php utama (opsional, lebih baik di index.php)
// try {
//     if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
//         require_once __DIR__ . '/../../vendor/autoload.php';
//         if (class_exists('Dotenv\Dotenv') && file_exists(__DIR__ . '/../../.env')) {
//             $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
//             $dotenv->load();
//         }
//     }
// } catch (\Throwable $e) {
//      error_log("Error loading .env in view: " . $e->getMessage());
// }


// Ambil Client Key dari environment variable
// Beri nilai default yang jelas jika .env gagal dibaca
$midtransClientKey = getenv('MIDTRANS_CLIENT_KEY') ?: 'PASTIKAN_CLIENTKEY_BENAR_DISINI_ATAU_DI_ENV';
$isProduction = (getenv('MIDTRANS_IS_PRODUCTION') === 'true');

// Include header (pastikan path benar)
include __DIR__ . '/../../include/header.php';
?>

<header class="hero-header">
  <div class="overlay-content"></div>
  <img src="img/bakar.jpg" alt="Ayam Bakar Background" class="hero-bg"> <!-- Path relatif dari root index.php -->
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
                <?= !empty($item['description']) ? htmlspecialchars($item['description']) : ' ' ?>
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
                  data-whatsapp="<?= htmlspecialchars($item['whatsapp_link'] ?? 'https://wa.me/6289630152631') ?>"> <!-- Ganti nomor WA default jika perlu -->
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

<!-- Modal Pemesanan -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Pemesanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Form untuk data pelanggan -->
        <form id="orderForm"> <!-- ID ini digunakan untuk reset -->
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
            <input type="tel" class="form-control" id="customerPhone" pattern="[0-9]{10,15}" title="Nomor HP valid (10-15 digit angka)" required>
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
        <!-- Div untuk menampilkan pesan status Snap -->
        <div id="snap-payment-message" class="mt-3"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" id="payAndWhatsAppButton" onclick="processSnapAndWhatsApp()">
          <i class="bi bi-credit-card-2-front"></i> Bayar & Kirim Pesanan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Include Footer dan Script -->
<?php include __DIR__ . '/../../include/footer.php'; ?>
<?php include __DIR__ . '/../../include/script.php'; ?>

<!-- Script Midtrans Snap -->
<script type="text/javascript"
    src="<?= $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' ?>"
    data-client-key="<?= htmlspecialchars($midtransClientKey) ?>"></script>

<!-- Script Kustom Anda -->
<script>
function showOrderForm(buttonElement) {
    const card = buttonElement.closest('.menu-card');
    const titleElement = card.querySelector('.menu-title');
    const inputElement = card.querySelector('.quantity-input');
    const priceElement = card.querySelector('.base-price');
    const whatsappLink = buttonElement.getAttribute('data-whatsapp');

    if (!titleElement || !inputElement || !priceElement) {
        console.error("showOrderForm Error: Elemen menu tidak ditemukan dalam kartu:", card);
        alert("Terjadi kesalahan saat memproses item ini.");
        return;
    }
    const itemName = titleElement.textContent;
    const quantity = inputElement.value;
    const basePrice = parseFloat(priceElement.value);
    let currentQuantity = parseInt(quantity);
    const maxQty = parseInt(inputElement.getAttribute('max') || '1');
    if (isNaN(currentQuantity) || currentQuantity < 1) currentQuantity = 1;
    if (currentQuantity > maxQty) currentQuantity = maxQty;
    const totalPrice = basePrice * currentQuantity;

    // Pastikan elemen target ada sebelum mengisi
    const itemNameInput = document.getElementById('orderItemName');
    const quantityInput = document.getElementById('orderQuantity');
    const totalPriceInput = document.getElementById('orderTotalPrice');
    const whatsappLinkInput = document.getElementById('orderWhatsappLink');
    const orderDetailsP = document.getElementById('orderDetails');
    const orderPriceP = document.getElementById('orderPrice');
    const paymentMsgDiv = document.getElementById('snap-payment-message');
    const payButton = document.getElementById('payAndWhatsAppButton');

    if (itemNameInput) itemNameInput.value = itemName;
    if (quantityInput) quantityInput.value = currentQuantity;
    if (totalPriceInput) totalPriceInput.value = totalPrice;
    if (whatsappLinkInput) whatsappLinkInput.value = whatsappLink;
    if (orderDetailsP) orderDetailsP.innerHTML = `Nama Item: ${itemName}<br>Jumlah: ${currentQuantity}`;
    if (orderPriceP) orderPriceP.textContent = `Total Harga: Rp ${totalPrice.toLocaleString('id-ID')}`;
    if (paymentMsgDiv) paymentMsgDiv.innerHTML = '';
    if (payButton) payButton.disabled = false;

    try {
        const orderModalElement = document.getElementById('orderModal');
        if (orderModalElement) {
             const orderModal = bootstrap.Modal.getOrCreateInstance(orderModalElement); // Gunakan getOrCreateInstance
             orderModal.show();
        } else {
             console.error("showOrderForm Error: Elemen modal #orderModal tidak ditemukan.");
             alert("Tidak dapat menampilkan form pemesanan.");
        }
    } catch (e) {
        console.error("showOrderForm Error saat menampilkan modal:", e);
        alert("Terjadi kesalahan saat menampilkan form pemesanan.");
    }
}

async function processSnapAndWhatsApp() {
    console.log('[processSnapAndWhatsApp] Fungsi dimulai...');

    const customerNameEl = document.getElementById('customerName');
    const customerPhoneEl = document.getElementById('customerPhone');
    const customerAddressEl = document.getElementById('customerAddress');
    const itemNameEl = document.getElementById('orderItemName');
    const quantityEl = document.getElementById('orderQuantity');
    const totalPriceEl = document.getElementById('orderTotalPrice');
    const baseWhatsappLinkEl = document.getElementById('orderWhatsappLink');
    const paymentMsgDiv = document.getElementById('snap-payment-message');
    const payButton = document.getElementById('payAndWhatsAppButton');

    if (!customerNameEl || !customerPhoneEl || !customerAddressEl || !itemNameEl || !quantityEl || !totalPriceEl || !baseWhatsappLinkEl || !paymentMsgDiv || !payButton) {
        console.error('[processSnapAndWhatsApp] Salah satu elemen penting tidak ditemukan di DOM.');
        alert('Terjadi kesalahan internal pada form (elemen tidak ditemukan).');
        return;
    }

    const customerName = customerNameEl.value.trim();
    const customerPhone = customerPhoneEl.value.trim();
    const customerAddress = customerAddressEl.value.trim();
    const itemName = itemNameEl.value;
    const quantity = quantityEl.value;
    const totalPrice = totalPriceEl.value;
    const baseWhatsappLink = baseWhatsappLinkEl.value;

    if (!customerName || !customerPhone || !customerAddress) {
        alert('Mohon lengkapi Nama, No. HP, dan Alamat.');
        // Fokus ke field pertama yang kosong (opsional)
        if (!customerName) customerNameEl.focus();
        else if (!customerPhone) customerPhoneEl.focus();
        else customerAddressEl.focus();
        return;
    }
    // Validasi nomor HP sederhana (opsional)
    if (!/^[0-9]{10,15}$/.test(customerPhone)) {
        alert('Format Nomor HP tidak valid (10-15 digit angka).');
        customerPhoneEl.focus();
        return;
    }

    console.log('[processSnapAndWhatsApp] Validasi Lolos.');
    payButton.disabled = true;
    paymentMsgDiv.innerHTML = '<div class="alert alert-info">Memproses pembayaran...</div>';

    const formData = new FormData();
    formData.append('pelanggan', customerName);
    formData.append('nohp', customerPhone);
    formData.append('alamat', customerAddress);
    formData.append('pesanan', itemName);
    formData.append('jumlah_pesan', quantity);
    formData.append('total_harga', totalPrice.replace(/[^0-9]/g, ''));

    try {
        console.log('[processSnapAndWhatsApp] Mengirim request ke Proses/CreateSnapToken.php...');
        const response = await fetch('Proses/CreateSnapToken.php', { // Pastikan path ini benar
            method: 'POST',
            body: formData
        });
        console.log('[processSnapAndWhatsApp] Menerima respons. Status:', response.status);

        const responseText = await response.text(); // Baca respons sebagai teks dulu
        console.log('[processSnapAndWhatsApp] Respons Teks dari server:', responseText);

        if (!response.ok) {
            // Coba parse sebagai JSON jika mungkin, jika tidak tampilkan teks
            let errorMsg = `Gagal mendapatkan token pembayaran: ${response.status}.`;
            try {
                const errorJson = JSON.parse(responseText);
                errorMsg += ` Pesan: ${errorJson.message || 'Tidak ada pesan detail.'}`;
            } catch (e) {
                errorMsg += ` Respons server: ${responseText}`;
            }
            throw new Error(errorMsg);
        }

        // Coba parse JSON setelah memastikan response.ok
        const result = JSON.parse(responseText);
        console.log('[processSnapAndWhatsApp] Respons JSON:', result);

        if (result.success && result.snap_token) {
            paymentMsgDiv.innerHTML = '';
            console.log('[processSnapAndWhatsApp] Memanggil snap.pay() dengan token:', result.snap_token);

            if (typeof snap === 'undefined') {
                 console.error("Variabel 'snap' dari Midtrans tidak terdefinisi.");
                 throw new Error("Library pembayaran (snap.js) tidak siap.");
            }

            snap.pay(result.snap_token, {
                onSuccess: function(paymentResult){
                    console.log('[Snap Callback] onSuccess:', paymentResult);
                    paymentMsgDiv.innerHTML = `<div class="alert alert-success">Pembayaran berhasil! Menyiapkan pesan WhatsApp...</div>`;
                    setTimeout(() => {
                        sendWhatsAppConfirmation(customerName, customerPhone, customerAddress, itemName, quantity, totalPrice, baseWhatsappLink, paymentResult.order_id);
                        try {
                            const modalEl = document.getElementById('orderModal');
                            if(modalEl){
                                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                                if (modalInstance) modalInstance.hide();
                            }
                        } catch (e) { console.error("Error hide modal on success:", e); }
                    }, 1500);
                },
                onPending: function(paymentResult){
                    console.log('[Snap Callback] onPending:', paymentResult);
                    paymentMsgDiv.innerHTML = `<div class="alert alert-warning">Pembayaran tertunda. Mohon selesaikan.</div>`;
                    payButton.disabled = false;
                },
                onError: function(paymentResult){
                    console.error('[Snap Callback] onError:', paymentResult);
                    paymentMsgDiv.innerHTML = `<div class="alert alert-danger">Pembayaran gagal: ${paymentResult.status_message || 'Error tidak diketahui'}</div>`;
                    payButton.disabled = false;
                },
                onClose: function(){
                    console.log('[Snap Callback] onClose triggered.');
                    if (!paymentMsgDiv.querySelector('.alert-success') && !paymentMsgDiv.querySelector('.alert-warning') && !paymentMsgDiv.querySelector('.alert-danger')) {
                         paymentMsgDiv.innerHTML = '<div class="alert alert-secondary">Popup pembayaran ditutup.</div>';
                    }
                    payButton.disabled = false;
                }
            });

        } else {
            console.error('[processSnapAndWhatsApp] Gagal mendapatkan token dari backend:', result.message);
            throw new Error(result.message || 'Token Snap tidak diterima dari server.');
        }

    } catch (error) {
        console.error('[processSnapAndWhatsApp] Terjadi Error:', error);
        paymentMsgDiv.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan: ${error.message}</div>`;
        payButton.disabled = false;
    }
}

function sendWhatsAppConfirmation(name, phone, address, item, qty, total, baseLink, midtransOrderId) {
    console.log("[sendWhatsAppConfirmation] Menyiapkan pesan...");
    let message = `*KONFIRMASI PESANAN*\n\n`;
    message += `Nama Pelanggan: ${name}\n`;
    message += `No. HP: ${phone}\n`;
    message += `Alamat Pengiriman: ${address}\n\n`;
    message += `Pesanan:\n`;
    message += `- ${item} (${qty} porsi)\n`;
    // Ambil nilai numerik dari total sebelum format
    const numericTotal = total.toString().replace(/[^0-9]/g, '');
    message += `Total Harga: Rp ${parseInt(numericTotal).toLocaleString('id-ID')}\n\n`;
    message += `*Catatan: Pembayaran via Midtrans telah diproses.*\n`;
    if(midtransOrderId) { // Hanya tampilkan jika ada
        message += `(Order ID Midtrans: ${midtransOrderId})\n\n`;
    }
    message += `Mohon segera diproses. Terima kasih.`;

    const whatsappUrl = `${baseLink || 'https://wa.me/6289630152631'}?text=${encodeURIComponent(message)}`; // Ganti nomor default jika perlu
    console.log("[sendWhatsAppConfirmation] Membuka URL:", whatsappUrl);
    window.open(whatsappUrl, '_blank');
}

// Event listener quantity (gunakan versi yang sudah diperbaiki sebelumnya)
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM Loaded - Init Quantity Selectors");
    document.querySelectorAll('.quantity-selector').forEach((selector, index) => {
        if (!selector.id) selector.id = `qs-${index}`;
        const minusBtn = selector.querySelector('.minus');
        const plusBtn = selector.querySelector('.plus');
        const input = selector.querySelector('.quantity-input');
        const card = selector.closest('.menu-card');
        if (!minusBtn || !plusBtn || !input || !card) { console.warn(`Incomplete elements for selector #${selector.id}`); return; }
        const basePriceElement = card.querySelector('.base-price');
        const totalPriceSpan = card.querySelector('.total-price');
        if (!basePriceElement || !totalPriceSpan) { console.warn(`Price elements not found for selector #${selector.id}`); return; }
        const basePrice = parseFloat(basePriceElement.value);
        const maxQty = parseInt(input.getAttribute('max') || '10');

        function updateDisplayAndValidate() {
            let currentValue = parseInt(input.value);
            if (isNaN(currentValue) || currentValue < 1) currentValue = 1;
            if (currentValue > maxQty) currentValue = maxQty;
            input.value = currentValue;
            const newTotal = basePrice * currentValue;
            totalPriceSpan.textContent = newTotal.toLocaleString('id-ID', { minimumFractionDigits: 0 });
        }
        minusBtn.addEventListener('click', () => { let val = parseInt(input.value) || 1; if (val > 1) { input.value = val - 1; updateDisplayAndValidate(); } });
        plusBtn.addEventListener('click', () => { let val = parseInt(input.value) || 1; if (val < maxQty) { input.value = val + 1; updateDisplayAndValidate(); } });
        input.addEventListener('change', updateDisplayAndValidate);
        input.addEventListener('blur', updateDisplayAndValidate);
        updateDisplayAndValidate();
    });

    // Reset modal saat ditutup
    const orderModalElement = document.getElementById('orderModal');
    if (orderModalElement) {
        orderModalElement.addEventListener('hidden.bs.modal', function () {
            const orderForm = document.getElementById('orderForm');
            if(orderForm) orderForm.reset();
            const paymentMsgDiv = document.getElementById('snap-payment-message');
            if(paymentMsgDiv) paymentMsgDiv.innerHTML = '';
            const payButton = document.getElementById('payAndWhatsAppButton');
            if(payButton) payButton.disabled = false;
        });
    }
});
</script>