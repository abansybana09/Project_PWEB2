<?php
// Diasumsikan $data sudah dikirim dari controller

// Include header (pastikan path benar)
include __DIR__ . '/../../include/header.php';
?>

<header class="hero-header">
    <div class="overlay-content"></div>
    <!-- Ganti gambar background jika perlu -->
    <img src="img/bakar.jpg" alt="Ayam Bakar Background" class="hero-bg"> <!-- Path relatif dari root index.php -->
</header>

<div class="container mb-5 mt-5 pt-5">
    <h2 class="text-center mb-4">Menu Kami</h2>

     <!-- Debugging: Tampilkan data mentah di view (hapus jika sudah OK) -->
     <!-- <?php echo "<pre>Data di View: "; var_dump($data); echo "</pre>"; ?> -->

    <div class="row justify-content-center">
        <?php
        // Periksa apakah $data ada dan merupakan array sebelum looping
        if (isset($data) && is_array($data) && !empty($data)):
            foreach ($data as $item):
                // Pastikan menggunakan key yang sama dengan yang dibuat di Model
                // 'title', 'image', 'description', 'price', 'stock'
        ?>
            <div class="col-md-4 mb-4">
                <div class="menu-card">
                    <!-- Gunakan $item['title'] -->
                    <div class="menu-title"><?= htmlspecialchars($item['title']) ?></div>
                    <!-- Gunakan $item['image'] untuk src -->
                    <img src="<?= htmlspecialchars($item['image']) ?>"
                         alt="<?= htmlspecialchars($item['title']) ?>"
                         class="menu-image img-fluid"
                         onerror="this.onerror=null; this.src='img/placeholder.png';"> <!-- Path relatif dari root index.php -->

                    <!-- Gunakan $item['description'] -->
                    <?php if (!empty($item['description'])): ?>
                        <p class="menu-description p-2"><?= htmlspecialchars($item['description']) ?></p>
                    <?php endif; ?>

                    <!-- Gunakan $item['price'] -->
                    <input type="hidden" class="base-price" value="<?= htmlspecialchars($item['price']) ?>">
                    <div class="menu-price">
                        Rp <span class="total-price"><?= number_format($item['price'], 0, ',', '.') ?></span>
                        <small class="text-muted d-block">(Rp <?= number_format($item['price'], 0, ',', '.') ?>/porsi)</small>
                    </div>

                     <!-- Tampilkan Stok jika perlu (gunakan $item['stock']) -->
                     <!-- <div class="menu-stock">Stok: <?= htmlspecialchars($item['stock']) ?></div> -->

                    <!-- Quantity Selector -->
                    <div class="quantity-selector text-center my-3">
                        <button class="btn btn-outline-secondary btn-sm quantity-btn minus">-</button>
                        <input type="number" class="quantity-input no-spinner" value="1" min="1" max="<?= max(1, htmlspecialchars($item['stock'])) ?>"> <!-- Batasi max dengan stok -->
                        <button class="btn btn-outline-secondary btn-sm quantity-btn plus">+</button>
                    </div>

                    <div class="menu-item">
                         <button class="order-btn"
                                onclick="showOrderForm(this)"
                                data-item-title="<?= htmlspecialchars($item['title']) ?>"
                                data-base-price="<?= htmlspecialchars($item['price']) ?>"
                                data-whatsapp="<?= htmlspecialchars($item['whatsapp_link'] ?? 'https://wa.me/6289630152631') ?>"> <!-- Link WA default -->
                             <i class=""></i> Pesan Sekarang
                         </button>
                         <!-- Tombol Admin tidak ditampilkan di sini sesuai permintaan awal -->
                    </div>
                </div>
            </div>
            <?php
            endforeach; // Akhir loop
        else: // Jika $data tidak ada, kosong, atau bukan array
            ?>
            <div class="col-12 text-center">
                <?php if (isset($data) && is_array($data) && empty($data)): ?>
                    <p>Belum ada menu yang tersedia saat ini.</p>
                <?php else: ?>
                    <!-- Pesan jika ada masalah saat mengambil data -->
                    <p>Tidak dapat memuat data menu saat ini.</p>
                     <!-- Tampilkan error jika ada dari model/controller jika perlu -->
                <?php endif; ?>
            </div>
        <?php endif; // Akhir pengecekan $data ?>
    </div>
</div>

<!-- Modal Form Pemesanan WA -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
  <!-- Kode modal order WA Anda ... -->
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

<!-- Include Footer dan Script -->
<?php include __DIR__ . '/../../include/footer.php'; // Pastikan path benar ?>
<?php include __DIR__ . '/../../include/script.php'; // Pastikan path benar ?>

<!-- Script untuk showOrderForm dan +/- quantity -->
<script>
// Salin kode JavaScript Anda yang relevan ke sini atau pastikan ada di script.php
function showOrderForm(buttonElement) {
    const card = buttonElement.closest('.menu-card');
    const itemName = card.querySelector('.menu-title').textContent;
    const quantity = card.querySelector('.quantity-input').value;
    const basePrice = parseFloat(card.querySelector('.base-price').value);
    const totalPrice = basePrice * parseInt(quantity);
    const whatsappLink = buttonElement.getAttribute('data-whatsapp'); // Ambil dari tombol

    document.getElementById('orderItemName').value = itemName;
    document.getElementById('orderQuantity').value = quantity;
    document.getElementById('orderTotalPrice').value = totalPrice;
    document.getElementById('orderWhatsappLink').value = whatsappLink; // Simpan link WA

    document.getElementById('orderDetails').innerHTML = `Nama Item: ${itemName}<br>Jumlah: ${quantity}`;
    document.getElementById('orderPrice').textContent = `Total Harga: Rp ${totalPrice.toLocaleString('id-ID')}`; // Format Rupiah

    const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
    orderModal.show();
}

function submitOrder() {
    const itemName = document.getElementById('orderItemName').value;
    const quantity = document.getElementById('orderQuantity').value;
    const totalPrice = document.getElementById('orderTotalPrice').value;
    const customerName = document.getElementById('customerName').value;
    const customerPhone = document.getElementById('customerPhone').value;
    const customerAddress = document.getElementById('customerAddress').value;
    const baseWhatsappLink = document.getElementById('orderWhatsappLink').value; // Ambil link WA dasar

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

    const orderModal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
    orderModal.hide();
    document.getElementById('orderForm').reset();
    document.getElementById('orderDetails').innerHTML = `Nama Item: -<br>Jumlah: -`;
    document.getElementById('orderPrice').textContent = `Total Harga: Rp -`;
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quantity-selector').forEach(selector => {
        const minusBtn = selector.querySelector('.minus');
        const plusBtn = selector.querySelector('.plus');
        const input = selector.querySelector('.quantity-input');
        const card = selector.closest('.menu-card');
        const basePrice = parseFloat(card.querySelector('.base-price').value);
        const totalPriceSpan = card.querySelector('.total-price');
        const maxQty = parseInt(input.getAttribute('max') || 10); // Ambil max dari input

        const updatePrice = () => {
            let quantity = parseInt(input.value);
             // Validasi ulang jika user mengetik
            if (isNaN(quantity) || quantity < 1) {
                quantity = 1;
                input.value = 1;
            } else if (quantity > maxQty) {
                 quantity = maxQty;
                 input.value = maxQty;
            }
            const newTotal = basePrice * quantity;
            totalPriceSpan.textContent = newTotal.toLocaleString('id-ID', { minimumFractionDigits: 0 });
        };

        minusBtn.addEventListener('click', () => {
            let currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updatePrice();
            }
        });

        plusBtn.addEventListener('click', () => {
            let currentValue = parseInt(input.value);
            if (currentValue < maxQty) { // Gunakan maxQty
                input.value = currentValue + 1;
                updatePrice();
            }
        });

        input.addEventListener('change', updatePrice); // Panggil updatePrice saat nilai berubah
        input.addEventListener('input', updatePrice); // Panggil updatePrice saat user mengetik

        // Panggil updatePrice sekali saat load untuk memastikan harga awal benar
        updatePrice();
    });
});
</script>