<script>
// Main Controller
document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initNavbarEffects();
    initAnimations();
    initQuantityControls();
    initPhoneValidation();
    updateCopyrightYear();
});

// Navbar Scroll Effect
function initNavbarEffects() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;

    window.addEventListener('scroll', function() {
        navbar.classList.toggle('scrolled', window.scrollY > 50);
    });
}

// Page Load Animations
function initAnimations() {
    // Navbar animation
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        setTimeout(() => {
            navbar.style.opacity = '1';
            navbar.style.transform = 'translateY(0)';
        }, 300);
    }

    // Footer animations
    const footerElements = document.querySelectorAll('.footer-section > *');
    footerElements.forEach((el, index) => {
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, 300 + (index * 100));
    });
}

// Quantity Controls
function initQuantityControls() {
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value) || 1;
            
            if (this.classList.contains('minus')) {
                value = Math.max(1, value - 1);
            } else if (this.classList.contains('plus')) {
                value = Math.min(10, value + 1);
            }
            
            input.value = value;
            updateTotalPrice(input);
        });
    });
}

// Update Total Price
function updateTotalPrice(input) {
    const menuCard = input.closest('.menu-card');
    if (!menuCard) return;
    
    const basePrice = parseFloat(menuCard.querySelector('.base-price')?.value) || 10000;
    const quantity = parseInt(input.value) || 1;
    const totalPrice = basePrice * quantity;
    
    const totalElement = menuCard.querySelector('.total-price');
    if (totalElement) {
        totalElement.textContent = totalPrice.toLocaleString('id-ID');
    }
}

// Phone Number Validation
function initPhoneValidation() {
    const phoneInput = document.getElementById('customerPhone');
    if (!phoneInput) return;
    
    phoneInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
}

// Copyright Year Update
function updateCopyrightYear() {
    const yearElement = document.getElementById('current-year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }
}

// Order Form Functions
function showOrderForm(buttonElement) {
    try {
        const menuCard = buttonElement.closest('.menu-card');
        if (!menuCard) throw new Error('Menu card not found');
        
        const itemName = menuCard.querySelector('.menu-title')?.textContent || 'Unknown Item';
        const quantity = menuCard.querySelector('.quantity-input')?.value || '1';
        const totalPrice = menuCard.querySelector('.total-price')?.textContent || '0';
        const whatsappLink = buttonElement.getAttribute('data-whatsapp') || '';
        
        // Fill modal data
        document.getElementById('orderItemName').value = itemName;
        document.getElementById('orderQuantity').value = quantity;
        document.getElementById('orderTotalPrice').value = totalPrice.replace(/\D/g, '');
        document.getElementById('orderWhatsappLink').value = whatsappLink;
        
        document.getElementById('orderDetails').innerHTML = `
            <strong>${itemName}</strong><br>
            Jumlah: ${quantity} porsi
        `;
        
        document.getElementById('orderPrice').innerHTML = `
            Total: Rp ${parseInt(totalPrice.replace(/\D/g, '')).toLocaleString('id-ID')}
        `;
        
        // Show modal
        const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
        orderModal.show();
        
    } catch (error) {
        console.error('Error showing order form:', error);
        alert('Terjadi kesalahan saat memproses pesanan. Silakan coba lagi.');
    }
}

// Submit Order with Validation
function submitOrder() {
    try {
        // Get form values
        const customerName = document.getElementById('customerName').value.trim();
        const customerPhone = document.getElementById('customerPhone').value.trim();
        const customerAddress = document.getElementById('customerAddress').value.trim();
        const itemName = document.getElementById('orderItemName').value;
        const quantity = document.getElementById('orderQuantity').value;
        const totalPrice = document.getElementById('orderTotalPrice').value;
        const baseWhatsappLink = document.getElementById('orderWhatsappLink').value;
        
        // Validate form
        if (!customerName || !customerPhone || !customerAddress) {
            alert('Harap isi semua data yang wajib diisi!');
            return false;
        }
        
        // Validate phone number
        if (!/^[0-9]+$/.test(customerPhone)) {
            alert('Nomor HP harus berupa angka!');
            document.getElementById('customerPhone').focus();
            return false;
        }
        
        // Validate minimum phone number length
        if (customerPhone.length < 10) {
            alert('Nomor HP harus minimal 10 digit!');
            document.getElementById('customerPhone').focus();
            return false;
        }
        
        // Format WhatsApp message
        const message = `Halo Warung Mang Oman,\n\nSaya mau pesan:\n\n*${itemName}*\nJumlah: ${quantity} porsi\nTotal: Rp ${parseInt(totalPrice).toLocaleString('id-ID')}\n\n*Data Pemesan:*\nNama: ${customerName}\nNo. HP: ${customerPhone}\nAlamat: ${customerAddress}`;
        const encodedMessage = encodeURIComponent(message);
        
        // Create WhatsApp URL
        const whatsappUrl = `${baseWhatsappLink.split('?')[0]}?text=${encodedMessage}`;
        
        // Open WhatsApp
        window.open(whatsappUrl, '_blank');
        
        // Close modal
        const orderModal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
        if (orderModal) {
            orderModal.hide();
        }
        
        // Reset form
        document.getElementById('orderForm').reset();
        
        return true;
        
    } catch (error) {
        console.error('Error submitting order:', error);
        alert('Terjadi kesalahan saat mengirim pesanan. Silakan coba lagi.');
        return false;
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>