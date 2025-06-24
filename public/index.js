
// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing mobile menu');
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    const body = document.body;

    console.log('Mobile menu toggle element:', mobileMenuToggle);
    console.log('Mobile menu element:', mobileMenu);

    if (mobileMenuToggle && mobileMenu) {
        console.log('Mobile menu elements found, adding event listener');
        mobileMenuToggle.addEventListener('click', function(e) {
            console.log('Mobile menu toggle clicked');
            e.preventDefault();
            e.stopPropagation();
            
            mobileMenu.classList.toggle('active');
            body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
            
            console.log('Menu active state:', mobileMenu.classList.contains('active'));
            
            // Animate hamburger menu
            const spans = mobileMenuToggle.querySelectorAll('span');
            if (mobileMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans[0].style.transform = '';
                spans[1].style.opacity = '1';
                spans[2].style.transform = '';
            }
        });

        // Close mobile menu when clicking on links
        const mobileMenuLinks = document.querySelectorAll('.mobile-menu-content a');
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
                body.style.overflow = '';
                
                // Reset hamburger menu
                const spans = mobileMenuToggle.querySelectorAll('span');
                spans[0].style.transform = '';
                spans[1].style.opacity = '1';
                spans[2].style.transform = '';
            });
        });

        // Close mobile menu when clicking outside
        mobileMenu.addEventListener('click', function(e) {
            if (e.target === mobileMenu) {
                mobileMenu.classList.remove('active');
                body.style.overflow = '';
                
                // Reset hamburger menu
                const spans = mobileMenuToggle.querySelectorAll('span');
                spans[0].style.transform = '';
                spans[1].style.opacity = '1';
                spans[2].style.transform = '';
            }
        });
    } else {
        console.log('Mobile menu elements not found!');
    }

    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = 'none';
            }
        });
    }

    // Smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 80; // Account for fixed navbar
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Disable parallax on mobile for better performance
    const isMobile = window.innerWidth <= 768;
    if (!isMobile) {
        // Parallax effect for hero section (desktop only)
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const heroBackground = document.querySelector('.hero-background');
            if (heroBackground) {
                heroBackground.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
        });
    }

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements for animation
    const animatedElements = document.querySelectorAll('.feature-card, .testimonial-card, .pricing-card');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    console.log('🚀 DataTable Pro Landing Page chargée avec succès !');
});

// Utility functions
function scrollToDemo() {
    const demoSection = document.getElementById('demo');
    if (demoSection) {
        demoSection.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }
}
