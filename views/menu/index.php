<?php
// File: PROJECR2/views/menu/index.php

// Diasumsikan $data (data menu) sudah dikirim dari MenuController
// Diasumsikan session_start() sudah dipanggil di index.php utama (router)
// Diasumsikan autoloader Composer dan .env sudah di-load di index.php utama (router)

// Ambil Client Key dari environment variable
// Beri nilai default yang jelas jika .env gagal dibaca atau tidak di-load di sini
$midtransClientKey = $_ENV['MIDTRANS_CLIENT_KEY'] ?? $_SERVER['MIDTRANS_CLIENT_KEY'] ?? 'SB-Mid-client-jc7jNfByRWeW_9IW'; // GANTI DENGAN CLIENT KEY SANDBOX ANDA JIKA PERLU
$isProduction = (strtolower($_ENV['MIDTRANS_IS_PRODUCTION'] ?? $_SERVER['MIDTRANS_IS_PRODUCTION'] ?? 'false') === 'true');

// Include header (pastikan path benar)
include __DIR__ . '/../../include/header.php';
?>

<header class="hero-header">
  <div class="overlay-content"></div>
  <img src="img/bakar.jpg" alt="Ayam Bakar Background" class="hero-bg"> <!-- Path relatif dari root index.php -->
</header>

<div class="container mb-5 mt-5 pt-5">
  <div class="d-flex justify-content-center align-items-center mb-4 position-relative">
    <h2 class="mb-0 text-center">Menu Kami</h2>
    <button class="btn position-absolute end-0" id="viewCartButton" data-bs-toggle="modal" data-bs-target="#cartModal">
      <img src="img/putih.png" alt="troli" class="cart-icon">
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartItemCountHeader">
        0
        <span class="visually-hidden">item di keranjang</span>
      </span>
    </button>
  </div>

  <div class="row justify-content-center">
    <?php if (isset($data) && is_array($data) && !empty($data)): ?>
      <?php foreach ($data as $item): ?>
        <div class="col-md-4 mb-4 d-flex align-items-stretch">
          <div class="menu-card h-100 d-flex flex-column">
            <img src="<?= htmlspecialchars($item['image'] ?? 'img/placeholder.png') ?>"
               alt="<?= htmlspecialchars($item['title'] ?? 'Menu') ?>"
               class="menu-image"
               onerror="this.onerror=null; this.src='img/placeholder.png';">

            <div class="menu-card-content flex-grow-1 d-flex flex-column">
              <div class="menu-title"><?= htmlspecialchars($item['title'] ?? 'Menu') ?></div>
              <p class="menu-description flex-grow-1">
                <?= !empty($item['description']) ? htmlspecialchars($item['description']) : 'Tidak ada deskripsi.' ?>
              </p>
              <div class="menu-price mt-auto">
                <input type="hidden" class="base-price" value="<?= htmlspecialchars($item['price'] ?? 0) ?>">
                Rp <span class="total-price-display"><?= number_format($item['price'] ?? 0, 0, ',', '.') ?></span>
                <small class="text-muted d-block">(Rp <?= number_format($item['price'] ?? 0, 0, ',', '.') ?>/porsi)</small>
              </div>
              <div class="quantity-selector text-center mt-2">
                <button class="btn btn-outline-secondary btn-sm quantity-btn minus" aria-label="Kurangi jumlah">-</button>
                <input type="number" class="quantity-input no-spinner" value="1" min="1" max="<?= max(1, htmlspecialchars($item['stock'] ?? 1)) ?>" aria-label="Jumlah" data-item-id="<?= htmlspecialchars($item['id'] ?? uniqid()) ?>">
                <button class="btn btn-outline-secondary btn-sm quantity-btn plus" aria-label="Tambah jumlah">+</button>
              </div>
            </div>

            <div class="menu-item-action mt-2">
              <button class="add-to-cart-btn btn btn-danger w-100"
                data-item-id="<?= htmlspecialchars($item['id'] ?? uniqid('item_')) ?>"
                data-item-title="<?= htmlspecialchars($item['title'] ?? 'Menu') ?>"
                data-base-price="<?= htmlspecialchars($item['price'] ?? 0) ?>"
                data-item-image="<?= htmlspecialchars($item['image'] ?? 'img/placeholder.png') ?>"
                data-item-stock="<?= htmlspecialchars($item['stock'] ?? 1) ?>">
                <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
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

<!-- Modal Keranjang -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cartModalLabel">Keranjang Pesanan Anda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="cartModalBody">
        <div id="cartItemsContainer">
          <!-- Item keranjang akan dimuat di sini oleh JavaScript -->
        </div>
        <p class="text-center mt-3" id="cartEmptyMessage" style="display: none;">Keranjang Anda masih kosong.</p>
        <hr id="cartDivider" style="display: none;">
        <div class="text-end mt-3" id="cartTotalContainer" style="display: none;">
            <h4>Total: Rp <span id="cartTotalPrice">0</span></h4>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Lanjut Belanja</button>
        <button type="button" class="btn btn-success" id="proceedToOrderButton" disabled>
          Lanjutkan ke Pemesanan
        </button>
      </div>
    </div>
  </div>
</div>


<!-- Modal Pemesanan (Formulir Akhir) -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderModalLabel">Form Pemesanan Akhir</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="finalOrderForm"> <!-- ID form diubah agar unik -->
          <!-- Input hidden untuk data yang akan dikirim ke backend -->
          <input type="hidden" id="finalOrderItemsInput"> <!-- JSON string of items -->
          <input type="hidden" id="finalOrderTotalPriceInput">
          <input type="hidden" id="finalOrderWhatsappLinkDefault" value="https://wa.me/6289630152631"> <!-- Link WA default -->

          <div class="mb-3">
            <label for="finalCustomerName" class="form-label">Nama Pelanggan *</label>
            <input type="text" class="form-control" id="finalCustomerName" required>
          </div>
          <div class="mb-3">
            <label for="finalCustomerPhone" class="form-label">No. HP *</label>
            <input type="tel" class="form-control" id="finalCustomerPhone" pattern="[0-9]{10,15}" title="Nomor HP valid (10-15 digit angka)" required>
          </div>
          <div class="mb-3">
            <label for="finalCustomerAddress" class="form-label">Alamat Pengiriman *</label>
            <textarea class="form-control" id="finalCustomerAddress" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Ringkasan Pesanan</label>
            <div class="card p-3 bg-light">
              <div id="finalOrderDetailsSummary">
                <!-- Detail pesanan dari keranjang akan ditampilkan di sini -->
              </div>
              <p id="finalOrderPriceSummary" class="fw-bold mt-2">Total Harga: Rp -</p>
            </div>
          </div>
        </form>
        <div id="snap-payment-message" class="mt-3"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-target="#cartModal" data-bs-toggle="modal" data-bs-dismiss="modal">Kembali ke Keranjang</button>
        <button type="button" class="btn btn-primary" id="payAndConfirmOrderButton" onclick="processFinalOrder()">
          <i class="bi bi-credit-card-2-front"></i> Bayar & Konfirmasi Pesanan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Include Footer dan Script -->
<?php include __DIR__ . '/../../include/footer.php'; ?>
<?php include __DIR__ . '/../../include/script.php'; // Pastikan tidak ada duplikasi listener quantity di sini ?>

<!-- Script Midtrans Snap -->
<script type="text/javascript"
    src="<?= $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' ?>"
    data-client-key="<?= htmlspecialchars($midtransClientKey) ?>"></script>

<!-- Script Kustom Anda -->
<script>
// Variabel global untuk keranjang
let shoppingCart = [];

// Fungsi format Rupiah
function formatRupiah(angka) {
    return (angka || 0).toLocaleString('id-ID');
}

// Update harga di kartu menu
function updateMenuCardTotalPrice(quantityInput) {
    const card = quantityInput.closest('.menu-card');
    if (!card) return;
    const basePrice = parseFloat(card.querySelector('.base-price')?.value || 0);
    const quantity = parseInt(quantityInput.value) || 1;
    const totalPriceDisplay = card.querySelector('.total-price-display');
    if (totalPriceDisplay) {
        totalPriceDisplay.textContent = formatRupiah(basePrice * quantity);
    }
}

// Render item di modal keranjang
function renderCartModalItems() {
    const cartItemsContainer = document.getElementById('cartItemsContainer');
    const cartItemCountHeader = document.getElementById('cartItemCountHeader');
    const cartTotalPriceEl = document.getElementById('cartTotalPrice');
    const proceedBtn = document.getElementById('proceedToOrderButton');
    const emptyMsg = document.getElementById('cartEmptyMessage');
    const divider = document.getElementById('cartDivider');
    const totalContainer = document.getElementById('cartTotalContainer');

    if (!cartItemsContainer || !cartItemCountHeader || !cartTotalPriceEl || !proceedBtn || !emptyMsg || !divider || !totalContainer) {
        console.error("Satu atau lebih elemen modal keranjang tidak ditemukan!");
        return;
    }

    cartItemsContainer.innerHTML = '';
    let totalOverallPrice = 0;
    let totalItemCount = 0;

    if (shoppingCart.length === 0) {
        emptyMsg.style.display = 'block';
        divider.style.display = 'none';
        totalContainer.style.display = 'none';
        proceedBtn.disabled = true;
    } else {
        emptyMsg.style.display = 'none';
        divider.style.display = 'block';
        totalContainer.style.display = 'block';
        proceedBtn.disabled = false;
        shoppingCart.forEach(item => {
            const itemTotalPrice = item.price * item.quantity;
            totalOverallPrice += itemTotalPrice;
            totalItemCount += item.quantity;
            const itemElement = document.createElement('div');
            itemElement.classList.add('cart-item', 'd-flex', 'justify-content-between', 'align-items-center', 'mb-3', 'pb-3', 'border-bottom');
            itemElement.innerHTML = `
                <div class="d-flex align-items-center">
                    <img src="${item.image}" alt="${item.title}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                    <div><h6 class="mb-0 small">${item.title}</h6><small class="text-muted">Rp ${formatRupiah(item.price)}</small></div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="quantity-selector-modal me-2" style="min-width: 100px;">
                        <button class="btn btn-outline-secondary btn-sm quantity-btn-modal minus" data-item-id="${item.id}">-</button>
                        <input type="number" class="quantity-input-modal no-spinner text-center" value="${item.quantity}" min="1" max="${item.stock}" data-item-id="${item.id}" style="width: 35px;">
                        <button class="btn btn-outline-secondary btn-sm quantity-btn-modal plus" data-item-id="${item.id}">+</button>
                    </div>
                    <div class="me-2" style="min-width: 80px; text-align: right;"><strong>Rp ${formatRupiah(itemTotalPrice)}</strong></div>
                    <button class="btn btn-sm btn-outline-danger remove-from-cart-btn" data-item-id="${item.id}"><i class="bi bi-trash"></i></button>
                </div>`;
            cartItemsContainer.appendChild(itemElement);
        });
    }
    cartItemCountHeader.textContent = totalItemCount;
    cartTotalPriceEl.textContent = formatRupiah(totalOverallPrice);
    localStorage.setItem('shoppingCart', JSON.stringify(shoppingCart));
}

// Tambah ke keranjang
function addToCart(button) {
    const itemId = button.getAttribute('data-item-id');
    const itemTitle = button.getAttribute('data-item-title');
    const basePrice = parseFloat(button.getAttribute('data-base-price'));
    const itemImage = button.getAttribute('data-item-image');
    const itemStock = parseInt(button.getAttribute('data-item-stock'));
    const card = button.closest('.menu-card');
    const quantityInput = card.querySelector('.quantity-input');
    const quantity = parseInt(quantityInput.value);

    const existingItemIndex = shoppingCart.findIndex(item => item.id === itemId);
    if (existingItemIndex > -1) {
        const newQuantity = shoppingCart[existingItemIndex].quantity + quantity;
        shoppingCart[existingItemIndex].quantity = Math.min(newQuantity, itemStock);
        if (newQuantity > itemStock) alert(`Stok ${itemTitle} hanya ${itemStock}. Kuantitas di keranjang disesuaikan.`);
    } else {
        if (quantity <= itemStock) {
            shoppingCart.push({ id: itemId, title: itemTitle, price: basePrice, quantity: quantity, image: itemImage, stock: itemStock });
        } else { alert(`Stok ${itemTitle} hanya ${itemStock}.`); return; }
    }
    renderCartModalItems();
    const cartModalEl = document.getElementById('cartModal');
    if(cartModalEl) bootstrap.Modal.getOrCreateInstance(cartModalEl).show();
    quantityInput.value = 1;
    updateMenuCardTotalPrice(quantityInput);
}

// Update kuantitas di modal keranjang
function updateCartItemQuantity(itemId, newQuantity) {
    const itemIndex = shoppingCart.findIndex(item => item.id === itemId);
    if (itemIndex > -1) {
        newQuantity = parseInt(newQuantity);
        if (newQuantity >= 1 && newQuantity <= shoppingCart[itemIndex].stock) {
            shoppingCart[itemIndex].quantity = newQuantity;
        } else if (newQuantity > shoppingCart[itemIndex].stock) {
            shoppingCart[itemIndex].quantity = shoppingCart[itemIndex].stock;
            alert(`Stok ${shoppingCart[itemIndex].title} hanya ${shoppingCart[itemIndex].stock}.`);
        } else { shoppingCart.splice(itemIndex, 1); }
    }
    renderCartModalItems();
}

// Hapus dari keranjang
function removeFromCart(itemId) {
    shoppingCart = shoppingCart.filter(item => item.id !== itemId);
    renderCartModalItems();
}

// Populasi form order akhir
function populateOrderForm() {
    const detailsSummary = document.getElementById('finalOrderDetailsSummary');
    const priceSummary = document.getElementById('finalOrderPriceSummary');
    const itemsInput = document.getElementById('finalOrderItemsInput');
    const totalPriceInput = document.getElementById('finalOrderTotalPriceInput');

    if(!detailsSummary || !priceSummary || !itemsInput || !totalPriceInput) {
        console.error("Elemen form order akhir tidak ditemukan!"); return;
    }
    detailsSummary.innerHTML = '';
    let finalTotal = 0;
    let itemsForMidtrans = [];

    if (shoppingCart.length === 0) {
        detailsSummary.innerHTML = '<p>Keranjang kosong.</p>';
        priceSummary.textContent = 'Total Harga: Rp 0';
        return;
    }
    shoppingCart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        finalTotal += itemTotal;
        const detailP = document.createElement('p');
        detailP.innerHTML = `${item.title} (${item.quantity} x Rp ${formatRupiah(item.price)}) = Rp ${formatRupiah(itemTotal)}`;
        detailsSummary.appendChild(detailP);
        itemsForMidtrans.push({ id: item.id, price: item.price, quantity: item.quantity, name: item.title.substring(0, 50) });
    });
    priceSummary.textContent = `Total Harga: Rp ${formatRupiah(finalTotal)}`;
    itemsInput.value = JSON.stringify(itemsForMidtrans);
    totalPriceInput.value = finalTotal;
}

// Proses order akhir (panggil Snap)
async function processFinalOrder() {
    console.log('[processFinalOrder] Fungsi dimulai...');
    const finalOrderForm = document.getElementById('finalOrderForm');
    if (!finalOrderForm.checkValidity()) { finalOrderForm.reportValidity(); return; }

    const customerName = document.getElementById('finalCustomerName').value.trim();
    const customerPhone = document.getElementById('finalCustomerPhone').value.trim();
    const customerAddress = document.getElementById('finalCustomerAddress').value.trim();
    const orderItemsJson = document.getElementById('finalOrderItemsInput').value;
    const orderTotalPrice = document.getElementById('finalOrderTotalPriceInput').value;

    const paymentMsgDiv = document.getElementById('snap-payment-message');
    const payButton = document.getElementById('payAndConfirmOrderButton'); // ID tombol di modal order akhir

    if (!customerName || !customerPhone || !customerAddress) { alert('Mohon lengkapi data pelanggan.'); return; }
    if (!/^[0-9]{10,15}$/.test(customerPhone)) { alert('Format Nomor HP tidak valid.'); return; }

    payButton.disabled = true;
    paymentMsgDiv.innerHTML = '<div class="alert alert-info">Memproses pembayaran...</div>';

    const formData = new FormData();
    formData.append('customer_name', customerName); // Key untuk backend
    formData.append('customer_phone', customerPhone);
    formData.append('customer_address', customerAddress);
    formData.append('order_items', orderItemsJson); // Kirim JSON string item
    formData.append('order_total_price', orderTotalPrice);

    try {
        console.log('[processFinalOrder] Mengirim request ke Proses/CreateSnapToken.php...');
        const response = await fetch('Proses/CreateSnapToken.php', { method: 'POST', body: formData });
        const responseText = await response.text();
        console.log('[processFinalOrder] Respons Teks:', responseText);
        if (!response.ok) { let errorMsg = `Gagal token: ${response.status}.`; try { const eJ = JSON.parse(responseText); errorMsg += ` Pesan: ${eJ.message || 'No detail.'}`; } catch (e) { errorMsg += ` Resp: ${responseText}`; } throw new Error(errorMsg); }
        const result = JSON.parse(responseText);
        console.log('[processFinalOrder] Respons JSON:', result);
        if (result.success && result.snap_token) {
            paymentMsgDiv.innerHTML = '';
            if (typeof snap === 'undefined') { throw new Error("Snap.js tidak siap."); }
            snap.pay(result.snap_token, {
                onSuccess: function(paymentResult){
                    console.log('[Snap Callback] onSuccess:', paymentResult);
                    paymentMsgDiv.innerHTML = `<div class="alert alert-success">Pembayaran berhasil! ID: ${paymentResult.order_id}. Menyiapkan WhatsApp...</div>`;
                    setTimeout(() => {
                        sendWhatsAppConfirmationAfterPayment(customerName, customerPhone, customerAddress, paymentResult.order_id);
                        clearCartAndCloseModals();
                    }, 1500);
                },
                onPending: function(paymentResult){ /* ... */ payButton.disabled = false; },
                onError: function(paymentResult){ /* ... */ payButton.disabled = false; },
                onClose: function(){ /* ... */ payButton.disabled = false; }
            });
        } else { throw new Error(result.message || 'Token Snap tidak diterima.'); }
    } catch (error) { console.error('[processFinalOrder] Error:', error); paymentMsgDiv.innerHTML = `<div class="alert alert-danger">${error.message}</div>`; payButton.disabled = false; }
}

// Kirim WA setelah pembayaran
function sendWhatsAppConfirmationAfterPayment(name, phone, address, midtransOrderId) {
    console.log("[sendWhatsAppConfirmationAfterPayment] Menyiapkan pesan...");
    let message = `*PESANAN BARU TELAH DIBAYAR*\n\n`;
    message += `Nama Pelanggan: ${name}\nNo. HP: ${phone}\nAlamat: ${address}\n\n`;
    message += `ID Pesanan (Midtrans): *${midtransOrderId}*\n\n`;
    message += "Detail Item dari Keranjang:\n";
    const itemsFromStorage = JSON.parse(localStorage.getItem('shoppingCart') || '[]');
    let totalBelanja = 0;
    if (itemsFromStorage.length > 0) {
        itemsFromStorage.forEach(item => {
            message += `- ${item.title} (${item.quantity} x Rp ${formatRupiah(item.price)}) = Rp ${formatRupiah(item.price * item.quantity)}\n`;
            totalBelanja += item.price * item.quantity;
        });
        message += `\nTotal Belanja: *Rp ${formatRupiah(totalBelanja)}*\n\n`;
    } else {
        message += "(Detail item tidak tersedia di konfirmasi ini, silakan cek sistem)\n\n";
    }
    message += `Mohon segera diproses. Terima kasih.`;
    const baseWhatsappLink = document.getElementById('finalOrderWhatsappLinkDefault').value;
    const whatsappUrl = `${baseWhatsappLink || 'https://wa.me/6289630152631'}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

// Fungsi clear cart dan tutup modal
function clearCartAndCloseModals() {
    shoppingCart = [];
    localStorage.removeItem('shoppingCart');
    renderCartModalItems();
    const cartM = bootstrap.Modal.getInstance(document.getElementById('cartModal')); if (cartM) cartM.hide();
    const orderM = bootstrap.Modal.getInstance(document.getElementById('orderModal')); if (orderM) orderM.hide();
    document.getElementById('finalOrderForm').reset();
    const snapMsg = document.getElementById('snap-payment-message'); if(snapMsg) snapMsg.innerHTML = '';
    const payBtn = document.getElementById('payAndConfirmOrderButton'); if(payBtn) payBtn.disabled = false;
}


// Event Listeners Utama
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM Loaded - Initializing all event listeners for menu page...");
    const storedCart = localStorage.getItem('shoppingCart');
    if (storedCart) { shoppingCart = JSON.parse(storedCart); }
    renderCartModalItems(); // Render keranjang awal

    const menuContainer = document.querySelector('.row.justify-content-center');
    if (menuContainer) {
        // Listener untuk tombol +/- di kartu menu
        menuContainer.addEventListener('click', function(event) {
            const target = event.target;
            if (target.matches('.menu-card .quantity-btn')) {
                const card = target.closest('.menu-card');
                const input = card.querySelector('.quantity-input');
                let currentValue = parseInt(input.value);
                const maxStock = parseInt(input.max);
                if (target.classList.contains('plus') && currentValue < maxStock) input.value = currentValue + 1;
                else if (target.classList.contains('minus') && currentValue > 1) input.value = currentValue - 1;
                updateMenuCardTotalPrice(input);
            } else if (target.matches('.add-to-cart-btn')) {
                addToCart(target);
            }
        });
        // Listener untuk input kuantitas di kartu menu
        menuContainer.addEventListener('input', function(event) { if (event.target.matches('.menu-card .quantity-input')) { updateMenuCardTotalPrice(event.target); } });
        menuContainer.addEventListener('change', function(event) { if (event.target.matches('.menu-card .quantity-input')) { updateMenuCardTotalPrice(event.target); } });
        document.querySelectorAll('.menu-card .quantity-input').forEach(updateMenuCardTotalPrice); // Inisialisasi
    }

    // Listener untuk modal keranjang
    const cartItemsCont = document.getElementById('cartItemsContainer');
    if (cartItemsCont) {
        cartItemsCont.addEventListener('click', function(event) {
            const target = event.target;
            const itemId = target.dataset.itemId || target.closest('[data-item-id]')?.dataset.itemId;
            if (!itemId) return;
            if (target.matches('.quantity-btn-modal') || target.closest('.quantity-btn-modal')) {
                const actualButton = target.matches('.quantity-btn-modal') ? target : target.closest('.quantity-btn-modal');
                const input = actualButton.parentElement.querySelector('.quantity-input-modal');
                let currentValue = parseInt(input.value);
                if (actualButton.classList.contains('plus')) currentValue++;
                else if (actualButton.classList.contains('minus')) currentValue--;
                updateCartItemQuantity(itemId, currentValue);
            } else if (target.matches('.remove-from-cart-btn') || target.closest('.remove-from-cart-btn')) {
                removeFromCart(itemId);
            }
        });
        cartItemsCont.addEventListener('change', function(event) {
            if (event.target.matches('.quantity-input-modal')) {
                updateCartItemQuantity(event.target.dataset.itemId, parseInt(event.target.value));
            }
        });
    }

    // Listener untuk tombol "Lanjutkan ke Pemesanan"
    const proceedBtn = document.getElementById('proceedToOrderButton');
    if(proceedBtn) {
        proceedBtn.addEventListener('click', function() {
            if (shoppingCart.length > 0) {
                populateOrderForm();
                const cartM = bootstrap.Modal.getInstance(document.getElementById('cartModal')); if (cartM) cartM.hide();
                const orderM = new bootstrap.Modal(document.getElementById('orderModal')); orderM.show();
            }
        });
    }

    // Reset modal order akhir saat ditutup
    const orderModalEl = document.getElementById('orderModal');
    if (orderModalEl) {
        orderModalEl.addEventListener('hidden.bs.modal', function () {
            const orderF = document.getElementById('finalOrderForm'); if(orderF) orderF.reset();
            const paymentM = document.getElementById('snap-payment-message'); if(paymentM) paymentM.innerHTML = '';
            const payBtnFinal = document.getElementById('payAndConfirmOrderButton'); if(payBtnFinal) payBtnFinal.disabled = false;
        });
    }
    console.log("All event listeners for menu page initialized.");
});
</script>

</body>
</html>