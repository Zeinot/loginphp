// Main JavaScript file for ListItAll (Craigslist Clone)

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Image preview for post images
    const imageInput = document.getElementById('post-images');
    const previewContainer = document.getElementById('image-preview-container');
    
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            
            if (this.files) {
                Array.from(this.files).forEach(file => {
                    if (!file.type.match('image.*')) {
                        return;
                    }
                    
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.classList.add('image-preview');
                        img.src = e.target.result;
                        previewContainer.appendChild(img);
                    }
                    
                    reader.readAsDataURL(file);
                });
            }
        });
    }
    
    // Post gallery thumbnail click handler
    const postThumbnails = document.querySelectorAll('.post-thumbnail');
    const mainImage = document.querySelector('.post-main-image');
    
    if (postThumbnails.length > 0 && mainImage) {
        postThumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                // Update main image src
                mainImage.src = this.src;
                
                // Update active state
                postThumbnails.forEach(thumb => thumb.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Category filter functionality
    const categoryButtons = document.querySelectorAll('.category-filter');
    
    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            const url = new URL(window.location);
            
            if (categoryId) {
                url.searchParams.set('category', categoryId);
            } else {
                url.searchParams.delete('category');
            }
            
            window.location.href = url.toString();
        });
    });
    
    // Price range filter
    const priceRange = document.getElementById('price-range');
    const priceValue = document.getElementById('price-value');
    
    if (priceRange && priceValue) {
        priceRange.addEventListener('input', function() {
            priceValue.textContent = '$' + this.value;
        });
    }
    
    // Contact buttons click handler
    const contactButtons = document.querySelectorAll('.contact-btn');
    
    contactButtons.forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.contactType;
            const value = this.dataset.contactValue;
            
            if (type === 'phone') {
                window.location.href = 'tel:' + value;
            } else if (type === 'email') {
                window.location.href = 'mailto:' + value;
            }
        });
    });
    
    // Admin dashboard charts (if Chart.js is included)
    const statsChart = document.getElementById('statsChart');
    
    if (typeof Chart !== 'undefined' && statsChart) {
        new Chart(statsChart, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'New Posts',
                    data: [65, 59, 80, 81, 56, 55],
                    borderColor: '#4e73df',
                    tension: 0.1,
                    fill: false
                }, {
                    label: 'New Users',
                    data: [28, 48, 40, 19, 86, 27],
                    borderColor: '#1cc88a',
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
