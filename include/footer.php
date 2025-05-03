<div class="wrapper">
<footer class="footer-section bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <!-- Logo dan Nama -->
            <div class="col-lg-4 text-center text-lg-start mb-4 mb-lg-0">
                <div class="footer-logo mb-3">
                    <img src="img/logo.png" alt="Warung Mang Oman" width="80" class="img-fluid">
                </div>
                <h5 class="mb-3 text-uppercase fw-bold" style="color: var(--kuning);">Warung Mang Oman</h5>
            </div>
            <!-- Informasi Kontak -->
            <div class="col-lg-4 text-center mb-4 mb-lg-0">
                <div class="footer-info">
                    <p class="mb-1"><i class="fas fa-clock me-2"></i> <strong>Buka Setiap Hari</strong> 08.00 - 22.00 WIB</p>
                    <p class="mb-1"><i class="fas fa-map-marker-alt me-2"></i> Jl. Otista No.55, Kuningan, Jawa Barat</p>
                    <p><i class="fas fa-phone-alt me-2"></i> <a href="tel:+6289630152631" class="text-white">0896-3015-2631</a></p>
                </div>
            </div>
            <!-- Sosial Media -->
            <div class="col-lg-4 text-center text-lg-end">
                <div class="social-icons mb-3">
                    <a href="https://facebook.com" target="_blank" class="social-icon mx-2">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://instagram.com" target="_blank" class="social-icon mx-2">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://wa.me/6289630152631" target="_blank" class="social-icon mx-2 whatsapp-icon">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://youtube.com" target="_blank" class="social-icon mx-2">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
                <div class="footer-links">
                    <a href="Menu.php" class="text-white mx-2">Menu</a>
                    <a href="Lokasi.php" class="text-white mx-2">Lokasi</a>
                    <a href="kontak.php" class="text-white mx-2">Kontak</a>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col text-center">
                <p class="mb-0">&copy; <span id="current-year">2025</span> Warung Mang Oman. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
</div>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/6289630152631" class="whatsapp-float" target="_blank">
    <i class="fab fa-whatsapp"></i>
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Efek scroll navbar
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Animasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        // Animasi navbar
        const navbar = document.querySelector('.navbar');
        setTimeout(() => {
            navbar.style.opacity = '1';
            navbar.style.transform = 'translateY(0)';
        }, 300);
        
        // Animasi elemen footer
        const footerElements = document.querySelectorAll('.footer-section > *');
        footerElements.forEach((el, index) => {
            setTimeout(() => {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, 300 + (index * 100));
        });
        
        // Update tahun copyright
        document.getElementById('current-year').textContent = new Date().getFullYear();
    });
</script>
</body>
</html>