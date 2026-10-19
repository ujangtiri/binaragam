// Main JavaScript for Bina Ragam Shop

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeNavigation();
    initializeAnimations();
    initializeScrollEffects();
    initializeForms();
    
    console.log('Bina Ragam Shop initialized successfully');
});

// Navigation functionality
function initializeNavigation() {
    // Sticky navigation background change on scroll
    window.addEventListener('scroll', function() {
        const nav = document.querySelector('.sticky-nav');
        if (window.scrollY > 100) {
            nav.style.background = 'rgba(249, 249, 249, 0.98)';
            nav.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
        } else {
            nav.style.background = 'rgba(249, 249, 249, 0.95)';
            nav.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
        }
    });
    
    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Animation initialization
function initializeAnimations() {
    // GSAP ScrollTrigger animations
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
        
        // Animate elements on scroll
        gsap.utils.toArray('.section-padding').forEach(section => {
            gsap.from(section.children, {
                y: 50,
                opacity: 0,
                duration: 0.8,
                stagger: 0.2,
                scrollTrigger: {
                    trigger: section,
                    start: 'top 80%',
                    end: 'bottom 20%',
                    toggleActions: 'play none none reverse'
                }
            });
        });
        
        // Parallax effect for images
        gsap.utils.toArray('img').forEach(img => {
            if (img.closest('.relative')) {
                gsap.to(img, {
                    yPercent: -20,
                    ease: 'none',
                    scrollTrigger: {
                        trigger: img.closest('.relative'),
                        start: 'top bottom',
                        end: 'bottom top',
                        scrub: true
                    }
                });
            }
        });
    }
    
    // AOS initialization (fallback)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });
    }
}

// Scroll effects
function initializeScrollEffects() {
    // Add scroll indicator
    const scrollIndicator = document.createElement('div');
    scrollIndicator.className = 'fixed top-0 left-0 h-1 bg-yellow-500 z-50 transition-all duration-300';
    scrollIndicator.id = 'scroll-indicator';
    document.body.appendChild(scrollIndicator);
    
    window.addEventListener('scroll', function() {
        const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        scrollIndicator.style.width = scrolled + '%';
    });
    
    // Back to top button
    const backToTop = document.createElement('button');
    backToTop.innerHTML = '<i data-lucide="arrow-up" class="w-6 h-6"></i>';
    backToTop.className = 'fixed bottom-8 right-8 w-12 h-12 bg-yellow-500 text-gray-900 rounded-full shadow-lg hover:bg-yellow-600 transition-all duration-300 opacity-0 pointer-events-none z-50';
    backToTop.id = 'back-to-top';
    document.body.appendChild(backToTop);
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTop.classList.remove('opacity-0', 'pointer-events-none');
            backToTop.classList.add('opacity-100');
        } else {
            backToTop.classList.add('opacity-0', 'pointer-events-none');
            backToTop.classList.remove('opacity-100');
        }
    });
    
    backToTop.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Form handling
function initializeForms() {
    // Contact form
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleContactForm(this);
        });
    }
    
    // Newsletter form
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleNewsletterForm(this);
        });
    }
}

// Contact form handler
function handleContactForm(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Simulate form submission
    setTimeout(() => {
        Swal.fire({
            title: 'Terima Kasih!',
            text: 'Pesan Anda telah terkirim. Kami akan segera menghubungi Anda.',
            icon: 'success',
            confirmButtonColor: '#F5B700',
            confirmButtonText: 'OK'
        });
        form.reset();
    }, 1000);
}

// Newsletter form handler
function handleNewsletterForm(form) {
    const email = form.querySelector('input[type="email"]').value;
    
    // Simulate form submission
    setTimeout(() => {
        Swal.fire({
            title: 'Berhasil!',
            text: 'Terima kasih telah berlangganan newsletter kami.',
            icon: 'success',
            confirmButtonColor: '#F5B700',
            confirmButtonText: 'OK'
        });
        form.reset();
    }, 1000);
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Loading states
function showLoading(element) {
    element.classList.add('opacity-50', 'pointer-events-none');
    element.style.cursor = 'wait';
}

function hideLoading(element) {
    element.classList.remove('opacity-50', 'pointer-events-none');
    element.style.cursor = 'default';
}

// Error handling
function showError(message) {
    Swal.fire({
        title: 'Error!',
        text: message,
        icon: 'error',
        confirmButtonColor: '#1C1C1C'
    });
}

function showSuccess(message) {
    Swal.fire({
        title: 'Success!',
        text: message,
        icon: 'success',
        confirmButtonColor: '#F5B700'
    });
}

// Export functions for global use
window.binaragam = {
    formatCurrency,
    debounce,
    throttle,
    showLoading,
    hideLoading,
    showError,
    showSuccess
};