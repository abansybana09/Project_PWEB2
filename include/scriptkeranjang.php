
<!-- Script Kustom Anda -->
<script>
  function showOrderForm(buttonElement) {
    const card = buttonElement.closest('.menu-card');
    const titleElement = card.querySelector('.menu-title');
    const inputElement = card.querySelector('.quantity-input');
    const priceElement = card.querySelector('.base-price');
    const whatsappLink = buttonElement.getAttribute('data-whatsapp');
    if (!titleElement || !inputElement || !priceElement) {
      console.error("showOrderForm Error: Elemen menu tidak ditemukan.");
      alert("Terjadi kesalahan.");
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
        bootstrap.Modal.getOrCreateInstance(orderModalElement).show();
      } else {
        console.error("showOrderForm Error: Elemen modal #orderModal tidak ditemukan.");
        alert("Tidak dapat menampilkan form pemesanan.");
      }
    } catch (e) {
      console.error("showOrderForm Error saat menampilkan modal:", e);
      alert("Terjadi kesalahan.");
    }
  }

  // Fungsi untuk memulai pembayaran Snap dan kirim WA
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
      console.error('[processSnapAndWhatsApp] Elemen penting tidak ditemukan.');
      alert('Kesalahan internal form.');
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
      return;
    }
    if (!/^[0-9]{10,15}$/.test(customerPhone)) {
      alert('Format Nomor HP tidak valid (10-15 digit angka).');
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
      const response = await fetch('Proses/CreateSnapToken.php', {
        method: 'POST',
        body: formData
      });
      console.log('[processSnapAndWhatsApp] Menerima respons. Status:', response.status);
      const responseText = await response.text();
      console.log('[processSnapAndWhatsApp] Respons Teks:', responseText);
      if (!response.ok) {
        let errorMsg = `Gagal mendapatkan token: ${response.status}.`;
        try {
          const errorJson = JSON.parse(responseText);
          errorMsg += ` Pesan: ${errorJson.message || 'Tidak ada detail.'}`;
        } catch (e) {
          errorMsg += ` Respons: ${responseText}`;
        }
        throw new Error(errorMsg);
      }
      const result = JSON.parse(responseText);
      console.log('[processSnapAndWhatsApp] Respons JSON:', result);
      if (result.success && result.snap_token) {
        paymentMsgDiv.innerHTML = '';
        console.log('[processSnapAndWhatsApp] Memanggil snap.pay() token:', result.snap_token);
        if (typeof snap === 'undefined') {
          throw new Error("Library pembayaran (snap.js) tidak siap.");
        }
        snap.pay(result.snap_token, {
          onSuccess: function(paymentResult) {
            console.log('[Snap Callback] onSuccess:', paymentResult);
            paymentMsgDiv.innerHTML = `<div class="alert alert-success">Pembayaran berhasil! Menyiapkan WhatsApp...</div>`;
            setTimeout(() => {
              sendWhatsAppConfirmation(customerName, customerPhone, customerAddress, itemName, quantity, totalPrice, baseWhatsappLink, paymentResult.order_id);
              try {
                const modalEl = document.getElementById('orderModal');
                if (modalEl) {
                  const modalInstance = bootstrap.Modal.getInstance(modalEl);
                  if (modalInstance) modalInstance.hide();
                }
              } catch (e) {
                console.error("Error hide modal on success:", e);
              }
            }, 1500);
          },
          onPending: function(paymentResult) {
            console.log('[Snap Callback] onPending:', paymentResult);
            paymentMsgDiv.innerHTML = `<div class="alert alert-warning">Pembayaran tertunda.</div>`;
            payButton.disabled = false;
          },
          onError: function(paymentResult) {
            console.error('[Snap Callback] onError:', paymentResult);
            paymentMsgDiv.innerHTML = `<div class="alert alert-danger">Pembayaran gagal: ${paymentResult.status_message || 'Error'}</div>`;
            payButton.disabled = false;
          },
          onClose: function() {
            console.log('[Snap Callback] onClose.');
            if (!paymentMsgDiv.querySelector('.alert-success') && !paymentMsgDiv.querySelector('.alert-warning') && !paymentMsgDiv.querySelector('.alert-danger')) {
              paymentMsgDiv.innerHTML = '<div class="alert alert-secondary">Popup pembayaran ditutup.</div>';
            }
            payButton.disabled = false;
          }
        });
      } else {
        throw new Error(result.message || 'Token Snap tidak diterima.');
      }
    } catch (error) {
      console.error('[processSnapAndWhatsApp] Error:', error);
      paymentMsgDiv.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan: ${error.message}</div>`;
      payButton.disabled = false;
    }
  }

  // Fungsi untuk kirim konfirmasi WhatsApp
  function sendWhatsAppConfirmation(name, phone, address, item, qty, total, baseLink, midtransOrderId) {
    console.log("[sendWhatsAppConfirmation] Menyiapkan pesan...");
    let message = `*KONFIRMASI PESANAN*\n\nNama Pelanggan: ${name}\nNo. HP: ${phone}\nAlamat Pengiriman: ${address}\n\nPesanan:\n- ${item} (${qty} porsi)\nTotal Harga: Rp ${parseInt(total.toString().replace(/[^0-9]/g, '')).toLocaleString('id-ID')}\n\n*Catatan: Pembayaran via Midtrans telah diproses.*\n`;
    if (midtransOrderId) {
      message += `(Order ID Midtrans: ${midtransOrderId})\n\n`;
    }
    message += `Mohon segera diproses. Terima kasih.`;
    const whatsappUrl = `${baseLink || 'https://wa.me/6289630152631'}?text=${encodeURIComponent(message)}`;
    console.log("[sendWhatsAppConfirmation] Membuka URL:", whatsappUrl);
    window.open(whatsappUrl, '_blank');
  }

  document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM Loaded - Initializing quantity selectors...");

    const menuContainer = document.querySelector('.row.justify-content-center');
    if (!menuContainer) {
      console.warn("Menu container '.row.justify-content-center' not found.");
      return;
    }

    // Fungsi helper untuk update harga dan validasi input
    const updateQuantityAndPrice = (inputElement) => {
      const card = inputElement.closest('.menu-card');
      if (!card) return;
      const basePriceElement = card.querySelector('.base-price');
      const totalPriceSpan = card.querySelector('.total-price');
      if (!basePriceElement || !totalPriceSpan) return;

      const basePrice = parseFloat(basePriceElement.value);
      const maxQty = parseInt(inputElement.getAttribute('max') || '1');
      let currentValue = parseInt(inputElement.value);

      if (isNaN(currentValue) || currentValue < 1) {
        currentValue = 1;
      } else if (currentValue > maxQty) {
        currentValue = maxQty;
      }

      if (inputElement.value !== currentValue.toString()) {
        inputElement.value = currentValue;
      }

      const newTotal = basePrice * currentValue;
      totalPriceSpan.textContent = newTotal.toLocaleString('id-ID', {
        minimumFractionDigits: 0
      });
    };

    // Listener untuk klik pada tombol +/- (Delegated)
    menuContainer.addEventListener('click', function(event) {
      const target = event.target;
      let inputElement = null;
      let valueChanged = false;

      if (target.matches('.quantity-btn.plus')) {
        console.log("Plus button clicked");
        const selector = target.closest('.quantity-selector');
        if (selector) inputElement = selector.querySelector('.quantity-input');
        if (inputElement) {
          const maxQty = parseInt(inputElement.getAttribute('max') || '10');
          let currentValue = parseInt(inputElement.value) || 0;
          if (currentValue < maxQty) {
            inputElement.value = currentValue + 1;
            valueChanged = true;
          }
        }
      } else if (target.matches('.quantity-btn.minus')) {
        console.log("Minus button clicked");
        const selector = target.closest('.quantity-selector');
        if (selector) inputElement = selector.querySelector('.quantity-input');
        if (inputElement) {
          let currentValue = parseInt(inputElement.value) || 1;
          if (currentValue > 1) {
            inputElement.value = currentValue - 1;
            valueChanged = true;
          }
        }
      }

      if (inputElement && valueChanged) {
        updateQuantityAndPrice(inputElement);
      }
    });

    // Listener untuk perubahan input (ketik/paste) (Delegated)
    menuContainer.addEventListener('input', function(event) {
      const target = event.target;
      if (target.matches('.quantity-input')) {
        console.log("Input event on quantity input");
        updateQuantityAndPrice(target);
      }
    });

    menuContainer.addEventListener('change', function(event) {
      const target = event.target;
      if (target.matches('.quantity-input')) {
        console.log("Change event on quantity input");
        updateQuantityAndPrice(target);
      }
    });


    // Inisialisasi harga saat halaman pertama kali dimuat
    document.querySelectorAll('.quantity-input').forEach(inputElement => {
      updateQuantityAndPrice(inputElement);
    });

    console.log("Quantity selector initialization complete.");

    // Reset modal saat ditutup
    const orderModalElement = document.getElementById('orderModal');
    if (orderModalElement) {
      orderModalElement.addEventListener('hidden.bs.modal', function() {
        const orderForm = document.getElementById('orderForm');
        if (orderForm) orderForm.reset();
        const paymentMsgDiv = document.getElementById('snap-payment-message');
        if (paymentMsgDiv) paymentMsgDiv.innerHTML = '';
        const payButton = document.getElementById('payAndWhatsAppButton');
        if (payButton) payButton.disabled = false;
      });
    }
  });
</script>