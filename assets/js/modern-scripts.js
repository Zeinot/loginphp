// Modern ListItAll Interactive Elements
document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations
    initAnimations();
    
    // Initialize parallax effects
    initParallax();
    
    // Initialize category card interactions
    initCategoryCards();
    
    // Initialize listing card interactions
    initListingCards();
    
    // Initialize how it works section interactions
    initHowItWorks();
    
    // Initialize search field enhancements
    initSearchEnhancements();
    
    // Initialize responsive navigation
    initResponsiveNav();
    
    console.log('Modern interactive elements initialized');
});

// Scroll animation function
function initAnimations() {
    // Get all elements that need to be animated
    const fadeElements = document.querySelectorAll('.fade-in-up');
    const staggerElements = document.querySelectorAll('.stagger-fade-in');
    
    // Create an observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            // If element is in view
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                // Unobserve after animation is triggered
                observer.unobserve(entry.target);
            }
        });
    }, {
        rootMargin: '-50px 0px',
        threshold: 0.1
    });
    
    // Observe all elements
    fadeElements.forEach(element => {
        observer.observe(element);
    });
    
    staggerElements.forEach(element => {
        observer.observe(element);
    });
    
    // Add hero floating elements animation
    animateHeroElements();
}

// Parallax effect for background elements
function initParallax() {
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Parallax for hero background
        const heroBackground = document.querySelector('.hero-background');
        if (heroBackground) {
            heroBackground.style.transform = `translateY(${scrollTop * 0.4}px)`;
        }
        
        // Parallax for floating elements
        const floatingElements = document.querySelectorAll('.floating-element');
        floatingElements.forEach((element, index) => {
            const speed = 0.2 + (index * 0.1);
            element.style.transform = `translateY(${scrollTop * speed}px)`;
        });
    });
}

// Hero animation elements
function animateHeroElements() {
    // Animate hero illustration elements
    const heroIllustration = document.querySelector('.hero-illustration');
    if (heroIllustration) {
        // Add subtle floating animation
        heroIllustration.style.animation = 'float 6s ease-in-out infinite';
    }
    
    // Add floating particles effect to hero section
    const hero = document.querySelector('.hero');
    if (hero) {
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.classList.add('hero-particle');
            
            // Random positioning
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.top = `${Math.random() * 100}%`;
            
            // Random size
            const size = Math.random() * 10 + 5;
            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            
            // Random animation duration
            const duration = Math.random() * 10 + 10;
            particle.style.animationDuration = `${duration}s`;
            
            // Random animation delay
            const delay = Math.random() * 5;
            particle.style.animationDelay = `${delay}s`;
            
            // Add to hero
            hero.appendChild(particle);
        }
    }
}

// Initialize category card interactions
function initCategoryCards() {
    const categoryCards = document.querySelectorAll('.category-card');
    
    categoryCards.forEach(card => {
        // Add mouse movement effect
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Calculate rotation based on mouse position
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            // Limit tilt effect
            const tiltX = (y - centerY) / 20;
            const tiltY = (centerX - x) / 20;
            
            // Apply the tilt effect
            this.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg) translateY(-10px) scale(1.02)`;
        });
        
        // Reset on mouse leave
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.transition = 'transform 0.5s ease';
        });
    });
}

// Initialize listing card interactions
function initListingCards() {
    const listingCards = document.querySelectorAll('.listing-card');
    
    listingCards.forEach(card => {
        // Add loading state for images
        const img = card.querySelector('.listing-img');
        if (img) {
            // Show loading state
            img.style.opacity = '0';
            img.parentElement.classList.add('loading');
            
            // When image loads
            img.addEventListener('load', function() {
                // Hide loading state
                this.style.opacity = '1';
                this.parentElement.classList.remove('loading');
            });
            
            // If image is already loaded
            if (img.complete) {
                img.style.opacity = '1';
                img.parentElement.classList.remove('loading');
            }
        }
        
        // Add subtle scale effect on hover
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            
            const cardImg = this.querySelector('.listing-img');
            if (cardImg) {
                cardImg.style.transform = 'scale(1.1) rotate(1deg)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            
            const cardImg = this.querySelector('.listing-img');
            if (cardImg) {
                cardImg.style.transform = 'scale(1)';
            }
        });
    });
}

// How it works section interactions
function initHowItWorks() {
    const howItWorksCards = document.querySelectorAll('.how-it-works-card');
    
    howItWorksCards.forEach((card, index) => {
        // Add delay to cards appearance
        card.style.transitionDelay = `${index * 0.1}s`;
        
        // Add icon animation on hover
        const icon = card.querySelector('.step-icon');
        if (icon) {
            card.addEventListener('mouseenter', function() {
                icon.style.transform = 'rotate(10deg)';
            });
            
            card.addEventListener('mouseleave', function() {
                icon.style.transform = 'none';
            });
        }
    });
}

// Enhanced search functionality
function initSearchEnhancements() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('.search-input');
    
    if (searchInput && searchForm) {
        // Add focus effects
        searchInput.addEventListener('focus', function() {
            searchForm.classList.add('search-form-focused');
        });
        
        searchInput.addEventListener('blur', function() {
            searchForm.classList.remove('search-form-focused');
        });
        
        // Add typing animation
        let placeholders = [
            'Search for products...',
            'Find local services...',
            'Discover community events...',
            'Explore job opportunities...'
        ];
        
        let currentPlaceholder = 0;
        let currentChar = 0;
        let isDeleting = false;
        let typingSpeed = 100;
        
        function typeEffect() {
            const placeholder = placeholders[currentPlaceholder];
            
            if (isDeleting) {
                // Removing characters
                searchInput.setAttribute('placeholder', placeholder.substring(0, currentChar--) + '|');
                typingSpeed = 50;
            } else {
                // Adding characters
                searchInput.setAttribute('placeholder', placeholder.substring(0, currentChar++) + '|');
                typingSpeed = 120;
            }
            
            // Change direction if reached end or start
            if (!isDeleting && currentChar === placeholder.length + 1) {
                isDeleting = true;
                typingSpeed = 1000; // Pause at the end
            } else if (isDeleting && currentChar === 0) {
                isDeleting = false;
                currentPlaceholder = (currentPlaceholder + 1) % placeholders.length;
                typingSpeed = 500; // Pause before starting new word
            }
            
            setTimeout(typeEffect, typingSpeed);
        }
        
        // Start the effect
        setTimeout(typeEffect, 1000);
    }
}

// Responsive navigation
function initResponsiveNav() {
    const navToggle = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navToggle && navbarCollapse) {
        // Add smooth animation to mobile navigation
        navToggle.addEventListener('click', function() {
            if (navbarCollapse.classList.contains('show')) {
                // Closing the menu
                navbarCollapse.style.maxHeight = '0';
                setTimeout(() => {
                    navbarCollapse.classList.remove('show');
                }, 300);
            } else {
                // Opening the menu
                navbarCollapse.classList.add('show');
                navbarCollapse.style.maxHeight = navbarCollapse.scrollHeight + 'px';
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInside = navbarCollapse.contains(event.target) || navToggle.contains(event.target);
            
            if (!isClickInside && navbarCollapse.classList.contains('show')) {
                navbarCollapse.style.maxHeight = '0';
                setTimeout(() => {
                    navbarCollapse.classList.remove('show');
                }, 300);
            }
        });
        
        // Adjust max-height on window resize
        window.addEventListener('resize', function() {
            if (navbarCollapse.classList.contains('show')) {
                navbarCollapse.style.maxHeight = navbarCollapse.scrollHeight + 'px';
            }
        });
    }
}
