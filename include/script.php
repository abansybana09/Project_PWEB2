<script>
// Main Controller - Simplified version
document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initNavbarEffects();
    initAnimations();
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
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>