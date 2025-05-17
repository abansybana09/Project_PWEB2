<?php
// File: PROJECR2/views/menu/index.php
// Ambil Client Key
$midtransClientKey = $_ENV['MIDTRANS_CLIENT_KEY'] ?? $_SERVER['MIDTRANS_CLIENT_KEY'] ?? 'SB-Mid-client-YOUR_SANDBOX_CLIENT_KEY'; // GANTI DENGAN CLIENT KEY SANDBOX ANDA UNTUK TESTING
$isProduction = (strtolower($_ENV['MIDTRANS_IS_PRODUCTION'] ?? $_SERVER['MIDTRANS_IS_PRODUCTION'] ?? 'false') === 'true');

// Include header
include __DIR__ . '/../../include/header.php';
?>

<header class="hero-header">
  <div class="overlay-content"></div>
  <img src="img/bakar.jpg" alt="Ayam Bakar Background" class="hero-bg">
</header>

<div class="container mb-5 mt-5 pt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Menu Kami</h2>
    <button class="btn btn-primary" id="viewCartButton" data-bs-toggle="modal" data-bs-target="#cartModal">
      <i class="bi bi-cart3"></i> Keranjang (<span id="cartItemCountHeader"></span>)
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
              <button class="add-to-cart-btn btn btn-warning w-100"
                data-item-id="<?= htmlspecialchars($item['id'] ?? uniqid()) ?>"
                data-item-title="<?= htmlspecialchars($item['title'] ?? 'Menu') ?>"
                data-base-price="<?= htmlspecialchars($item['price'] ?? 0) ?>"
                data-item-image="<?= htmlspecialchars($item['image'] ?? 'img/placeholder.png') ?>"
                data-item-stock="<?= htmlspecialchars($item['stock'] ?? 1) ?>"
                data-whatsapp="<?= htmlspecialchars($item['whatsapp_link'] ?? 'https://wa.me/6289630152631') ?>">
                <i class="bi bi-cart-plus"></i> Tambah
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cartModalLabel">Keranjang Pesanan Anda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="cartModalBody">
        <div id="cartItemsContainer">
          <!-- Item keranjang akan dimuat di sini oleh JavaScript -->
        </div>
        <p class="text-center" id="cartEmptyMessage" style="display: none;">Keranjang Anda masih kosong.</p>
        <hr id="cartDivider" style="display: none;">
        <div class="text-end" id="cartTotalContainer" style="display: none;">
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
        <h5 class="modal-title" id="orderModalLabel">Form Pemesanan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="orderForm">
          <input type="hidden" id="orderItemsInput"> <!-- JSON string of items -->
          <input type="hidden" id="orderTotalPriceInput">
          <input type="hidden" id="orderWhatsappLinkDefault" value="https://wa.me/6289630152631"> <!-- Link WA default -->

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
              <div id="orderDetailsSummary">
                <!-- Detail pesanan dari keranjang akan ditampilkan di sini -->
              </div>
              <p id="orderPriceSummary" class="fw-bold mt-2">Total Harga: Rp -</p>
            </div>
          </div>
        </form>
        <div id="snap-payment-message" class="mt-3"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="payAndWhatsAppButton" onclick="processOrder()">
          <i class="bi bi-credit-card-2-front"></i> Bayar & Kirim Pesanan
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Include Footer dan Script -->
<?php include __DIR__ . '/../../include/script.php'; ?>
<?php include __DIR__ . '/../../include/footer.php'; ?>
<?php // Hapus include scriptkeranjang.php karena logikanya akan digabung di bawah ?>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= htmlspecialchars($midtransClientKey) ?>"></script>
<script>
// Variabel global
let shoppingCart = []; // { id, title, price, quantity, image, stock }
const midtransClientKey = '<?= htmlspecialchars($midtransClientKey) ?>';
const isProduction = <?= $isProduction ? 'true' : 'false' ?>;


// Fungsi untuk memformat angka menjadi format Rupiah
function formatRupiah(angka) {
    return angka.toLocaleString('id-ID');
}

// Fungsi untuk mengupdate tampilan harga total per item di kartu menu
function updateMenuCardTotalPrice(quantityInput) {
    const card = quantityInput.closest('.menu-card');
    const basePrice = parseFloat(card.querySelector('.base-price').value);
    const quantity = parseInt(quantityInput.value);
    const totalPriceDisplay = card.querySelector('.total-price-display');
    if (totalPriceDisplay) {
        totalPriceDisplay.textContent = formatRupiah(basePrice * quantity);
    }
}

// Fungsi untuk merender item di dalam modal keranjang
function renderCartModalItems() {
    const cartItemsContainer = document.getElementById('cartItemsContainer');
    const cartItemCountHeader = document.getElementById('cartItemCountHeader');
    const cartTotalPriceEl = document.getElementById('cartTotalPrice');
    const proceedToOrderButton = document.getElementById('proceedToOrderButton');
    const cartEmptyMessage = document.getElementById('cartEmptyMessage');
    const cartDivider = document.getElementById('cartDivider');
    const cartTotalContainer = document.getElementById('cartTotalContainer');

    cartItemsContainer.innerHTML = ''; // Kosongkan kontainer
    let totalOverallPrice = 0;
    let totalItemCount = 0;

    if (shoppingCart.length === 0) {
        cartEmptyMessage.style.display = 'block';
        cartDivider.style.display = 'none';
        cartTotalContainer.style.display = 'none';
        proceedToOrderButton.disabled = true;
    } else {
        cartEmptyMessage.style.display = 'none';
        cartDivider.style.display = 'block';
        cartTotalContainer.style.display = 'block';
        proceedToOrderButton.disabled = false;

        shoppingCart.forEach(item => {
            const itemTotalPrice = item.price * item.quantity;
            totalOverallPrice += itemTotalPrice;
            totalItemCount += item.quantity;

            const itemElement = document.createElement('div');
            itemElement.classList.add('cart-item', 'd-flex', 'justify-content-between', 'align-items-center', 'mb-3', 'pb-3', 'border-bottom');
            itemElement.innerHTML = `
                <div class="d-flex align-items-center">
                    <img src="${item.image}" alt="${item.title}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px; margin-right: 15px;">
                    <div>
                        <h6 class="mb-0">${item.title}</h6>
                        <small class="text-muted">Rp ${formatRupiah(item.price)}/item</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="quantity-selector-modal me-3" style="min-width: 120px;">
                        <button class="btn btn-outline-secondary btn-sm quantity-btn-modal minus" data-item-id="${item.id}" aria-label="Kurangi jumlah">-</button>
                        <input type="number" class="quantity-input-modal no-spinner text-center" value="${item.quantity}" min="1" max="${item.stock}" data-item-id="${item.id}" style="width: 40px;" aria-label="Jumlah">
                        <button class="btn btn-outline-secondary btn-sm quantity-btn-modal plus" data-item-id="${item.id}" aria-label="Tambah jumlah">+</button>
                    </div>
                    <div class="me-3" style="min-width: 100px; text-align: right;">
                        <strong>Rp ${formatRupiah(itemTotalPrice)}</strong>
                    </div>
                    <button class="btn btn-sm btn-outline-danger remove-from-cart-btn" data-item-id="${item.id}" aria-label="Hapus item">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            cartItemsContainer.appendChild(itemElement);
        });
    }

    cartItemCountHeader.textContent = shoppingCart.reduce((acc, item) => acc + item.quantity, 0);
    cartTotalPriceEl.textContent = formatRupiah(totalOverallPrice);

    // Simpan ke localStorage (opsional, untuk persistensi)
    localStorage.setItem('shoppingCart', JSON.stringify(shoppingCart));
}

// Fungsi untuk menambah item ke keranjang
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
        if (newQuantity <= itemStock) {
            shoppingCart[existingItemIndex].quantity = newQuantity;
        } else {
            shoppingCart[existingItemIndex].quantity = itemStock; // Set to max stock
            alert(`Stok ${itemTitle} tidak mencukupi. Maksimal ${itemStock} item.`);
        }
    } else {
        if (quantity <= itemStock) {
            shoppingCart.push({
                id: itemId,
                title: itemTitle,
                price: basePrice,
                quantity: quantity,
                image: itemImage,
                stock: itemStock
            });
        } else {
             alert(`Stok ${itemTitle} tidak mencukupi. Anda hanya bisa menambahkan maksimal ${itemStock} item.`);
             return; // Jangan tambahkan jika melebihi stok
        }
    }
    
    renderCartModalItems();
    // Tampilkan modal keranjang
    const cartModal = bootstrap.Modal.getInstance(document.getElementById('cartModal')) || new bootstrap.Modal(document.getElementById('cartModal'));
    cartModal.show();

    // Reset quantity di kartu menu ke 1 setelah ditambahkan
    quantityInput.value = 1;
    updateMenuCardTotalPrice(quantityInput);
}

// Fungsi untuk mengubah kuantitas item di modal keranjang
function updateCartItemQuantity(itemId, newQuantity) {
    const itemIndex = shoppingCart.findIndex(item => item.id === itemId);
    if (itemIndex > -1) {
        newQuantity = parseInt(newQuantity);
        if (newQuantity >= 1 && newQuantity <= shoppingCart[itemIndex].stock) {
            shoppingCart[itemIndex].quantity = newQuantity;
        } else if (newQuantity > shoppingCart[itemIndex].stock) {
            shoppingCart[itemIndex].quantity = shoppingCart[itemIndex].stock;
            alert(`Stok ${shoppingCart[itemIndex].title} hanya ${shoppingCart[itemIndex].stock}.`);
        } else { // newQuantity < 1
             shoppingCart.splice(itemIndex, 1); // Hapus jika kuantitas jadi 0 atau kurang
        }
    }
    renderCartModalItems();
}

// Fungsi untuk menghapus item dari keranjang
function removeFromCart(itemId) {
    shoppingCart = shoppingCart.filter(item => item.id !== itemId);
    renderCartModalItems();
}

// Fungsi untuk mempopulasi form pemesanan
function populateOrderForm() {
    const orderDetailsSummary = document.getElementById('orderDetailsSummary');
    const orderPriceSummary = document.getElementById('orderPriceSummary');
    const orderItemsInput = document.getElementById('orderItemsInput');
    const orderTotalPriceInput = document.getElementById('orderTotalPriceInput');

    orderDetailsSummary.innerHTML = '';
    let finalTotal = 0;
    let itemsForMidtrans = [];

    if (shoppingCart.length === 0) {
        orderDetailsSummary.innerHTML = '<p>Tidak ada item dalam pesanan.</p>';
        orderPriceSummary.textContent = 'Total Harga: Rp 0';
        return;
    }

    shoppingCart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        finalTotal += itemTotal;
        const detailElement = document.createElement('div');
        detailElement.classList.add('mb-2');
        detailElement.innerHTML = `
            <strong>${item.title}</strong> (${item.quantity} x Rp ${formatRupiah(item.price)})
            <div class="text-end">Rp ${formatRupiah(itemTotal)}</div>
        `;
        orderDetailsSummary.appendChild(detailElement);

        // Untuk Midtrans item_details
        itemsForMidtrans.push({
            id: item.id,
            price: item.price,
            quantity: item.quantity,
            name: item.title.substring(0, 50) // Midtrans name limit
        });
    });

    orderPriceSummary.textContent = `Total Harga: Rp ${formatRupiah(finalTotal)}`;
    orderItemsInput.value = JSON.stringify(itemsForMidtrans); // Simpan item untuk Midtrans
    orderTotalPriceInput.value = finalTotal;
}


// Fungsi untuk memproses pesanan (pembayaran dan WhatsApp)
async function processOrder() {
    const orderForm = document.getElementById('orderForm');
    if (!orderForm.checkValidity()) {
        orderForm.reportValidity();
        return;
    }

    const customerName = document.getElementById('customerName').value;
    const customerPhone = document.getElementById('customerPhone').value;
    const customerAddress = document.getElementById('customerAddress').value;
    const orderTotalPrice = parseFloat(document.getElementById('orderTotalPriceInput').value);
    const itemsForMidtrans = JSON.parse(document.getElementById('orderItemsInput').value); // Sudah dalam format Midtrans

    const snapPaymentMessage = document.getElementById('snap-payment-message');
    snapPaymentMessage.innerHTML = '<div class="alert alert-info">Memproses pembayaran...</div>';
    document.getElementById('payAndWhatsAppButton').disabled = true;

    try {
        // 1. Panggil backend untuk mendapatkan Snap Token
        const response = await fetch('api/create_transaction.php', { // PASTIKAN PATH INI BENAR
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                items: itemsForMidtrans,
                total_price: orderTotalPrice,
                customer_details: {
                    first_name: customerName,
                    phone: customerPhone,
                    shipping_address: { // Tambahkan jika perlu
                        address: customerAddress,
                        // city: "Kota Anda", // Sesuaikan
                        // postal_code: "KodePos", // Sesuaikan
                        // country_code: "IDN" // Sesuaikan
                    }
                }
            })
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Gagal membuat transaksi Midtrans.');
        }

        const data = await response.json();
        if (!data.snap_token) {
            throw new Error('Token Snap tidak diterima dari server.');
        }
        const snapToken = data.snap_token;

        // 2. Tampilkan Midtrans Snap Popup
        snap.pay(snapToken, {
            onSuccess: function(result) {
                snapPaymentMessage.innerHTML = `<div class="alert alert-success">Pembayaran berhasil! ID Pesanan: ${result.order_id}. Status: ${result.transaction_status}</div>`;
                sendWhatsAppMessage(result.order_id, customerName, customerPhone, customerAddress, 'Pembayaran Berhasil');
                clearCartAndCloseModals();
            },
            onPending: function(result) {
                snapPaymentMessage.innerHTML = `<div class="alert alert-warning">Pembayaran tertunda. ID Pesanan: ${result.order_id}. Status: ${result.transaction_status}. Silakan selesaikan pembayaran.</div>`;
                sendWhatsAppMessage(result.order_id, customerName, customerPhone, customerAddress, 'Pembayaran Tertunda/Menunggu');
                clearCartAndCloseModals();
            },
            onError: function(result) {
                snapPaymentMessage.innerHTML = `<div class="alert alert-danger">Pembayaran gagal. ID Pesanan: ${result.order_id}. Status: ${result.transaction_status}. Error: ${result.status_message}</div>`;
                document.getElementById('payAndWhatsAppButton').disabled = false;
                 // sendWhatsAppMessage(result.order_id, customerName, customerPhone, customerAddress, 'Pembayaran Gagal');
            },
            onClose: function() {
                if (!snapPaymentMessage.querySelector('.alert-success') && !snapPaymentMessage.querySelector('.alert-warning')) {
                     snapPaymentMessage.innerHTML = '<div class="alert alert-info">Anda menutup popup pembayaran sebelum selesai.</div>';
                }
                document.getElementById('payAndWhatsAppButton').disabled = false;
            }
        });

    } catch (error) {
        console.error('Error processing order:', error);
        snapPaymentMessage.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan: ${error.message}</div>`;
        document.getElementById('payAndWhatsAppButton').disabled = false;
    }
}

function sendWhatsAppMessage(orderId, name, phone, address, paymentStatus) {
    let message = `Halo,\nSaya ingin mengkonfirmasi pesanan baru:\n\n`;
    message += `ID Pesanan: *${orderId}*\n`;
    message += `Status Pembayaran: *${paymentStatus}*\n\n`;
    message += `Nama Pelanggan: ${name}\n`;
    message += `No. HP: ${phone}\n`;
    message += `Alamat: ${address}\n\n`;
    message += "Detail Pesanan:\n";
    shoppingCart.forEach(item => {
        message += `- ${item.title} (${item.quantity} x Rp ${formatRupiah(item.price)}) = Rp ${formatRupiah(item.price * item.quantity)}\n`;
    });
    const total = shoppingCart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    message += `\nTotal Keseluruhan: *Rp ${formatRupiah(total)}*\n\n`;
    message += "Mohon segera diproses. Terima kasih!";

    const whatsappDefaultLink = document.getElementById('orderWhatsappLinkDefault').value;
    const encodedMessage = encodeURIComponent(message);
    const whatsappUrl = `${whatsappDefaultLink}?text=${encodedMessage}`;
    
    // Buka di tab baru
    window.open(whatsappUrl, '_blank');
}

function clearCartAndCloseModals() {
    shoppingCart = [];
    localStorage.removeItem('shoppingCart'); // Hapus dari localStorage
    renderCartModalItems(); // Update tampilan keranjang (jadi kosong)

    // Tutup semua modal
    const cartModalInstance = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
    if (cartModalInstance) cartModalInstance.hide();
    const orderModalInstance = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
    if (orderModalInstance) orderModalInstance.hide();
    
    // Reset form pemesanan
    document.getElementById('orderForm').reset();
    document.getElementById('snap-payment-message').innerHTML = '';
    document.getElementById('payAndWhatsAppButton').disabled = false;
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Muat keranjang dari localStorage jika ada
    const storedCart = localStorage.getItem('shoppingCart');
    if (storedCart) {
        shoppingCart = JSON.parse(storedCart);
        renderCartModalItems();
    }


    // Event untuk tombol "Tambah ke Keranjang" di kartu menu
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            addToCart(this);
        });
    });

    // Event untuk tombol +/- di kartu menu
    document.querySelectorAll('.menu-card .quantity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const card = this.closest('.menu-card');
            const input = card.querySelector('.quantity-input');
            let currentValue = parseInt(input.value);
            const maxStock = parseInt(input.max);

            if (this.classList.contains('plus')) {
                if (currentValue < maxStock) currentValue++;
            } else if (this.classList.contains('minus')) {
                if (currentValue > 1) currentValue--;
            }
            input.value = currentValue;
            updateMenuCardTotalPrice(input);
        });
    });

    // Event untuk input kuantitas di kartu menu (jika user ketik manual)
    document.querySelectorAll('.menu-card .quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            let value = parseInt(this.value);
            const min = parseInt(this.min);
            const max = parseInt(this.max);
            if (isNaN(value) || value < min) {
                this.value = min;
            } else if (value > max) {
                this.value = max;
            }
            updateMenuCardTotalPrice(this);
        });
        // Initial total price update
        updateMenuCardTotalPrice(input);
    });

    // Event delegation untuk tombol +/- dan hapus di dalam modal keranjang
    document.getElementById('cartItemsContainer').addEventListener('click', function(event) {
        const target = event.target;
        const itemId = target.dataset.itemId || target.closest('[data-item-id]')?.dataset.itemId;

        if (!itemId) return;

        if (target.classList.contains('quantity-btn-modal') || target.closest('.quantity-btn-modal')) {
            const actualButton = target.classList.contains('quantity-btn-modal') ? target : target.closest('.quantity-btn-modal');
            const input = actualButton.parentElement.querySelector('.quantity-input-modal');
            let currentValue = parseInt(input.value);
            if (actualButton.classList.contains('plus')) {
                currentValue++;
            } else if (actualButton.classList.contains('minus')) {
                currentValue--;
            }
            updateCartItemQuantity(itemId, currentValue);
        } else if (target.classList.contains('remove-from-cart-btn') || target.closest('.remove-from-cart-btn')) {
            removeFromCart(itemId);
        }
    });
    
    // Event delegation untuk input kuantitas di modal keranjang
    document.getElementById('cartItemsContainer').addEventListener('change', function(event) {
        const target = event.target;
        if (target.classList.contains('quantity-input-modal')) {
            const itemId = target.dataset.itemId;
            let newQuantity = parseInt(target.value);
             if (isNaN(newQuantity) || newQuantity < 1) {
                newQuantity = 1; // Default ke 1 jika tidak valid
            }
            updateCartItemQuantity(itemId, newQuantity);
        }
    });

    // Event untuk tombol "Lanjutkan ke Pemesanan" di modal keranjang
    document.getElementById('proceedToOrderButton').addEventListener('click', function() {
        if (shoppingCart.length > 0) {
            populateOrderForm();
            const cartModalInstance = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
            if (cartModalInstance) cartModalInstance.hide();
            
            const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
            orderModal.show();
        }
    });
    
    // Panggil renderCartModalItems sekali di awal untuk memastikan tampilan konsisten
    renderCartModalItems();
});
</script>

</body>
</html>