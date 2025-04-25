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
        console.log('DEBUG: Image input element found, setting up event listeners');
        
        // Log when file input changes
        imageInput.addEventListener('change', function() {
            console.log('DEBUG: Files selected:', this.files);
            previewContainer.innerHTML = '';
            
            if (this.files && this.files.length > 0) {
                console.log('DEBUG: Number of files selected:', this.files.length);
                
                Array.from(this.files).forEach((file, index) => {
                    console.log(`DEBUG: File ${index + 1} details:`, {
                        name: file.name,
                        type: file.type,
                        size: file.size,
                        lastModified: new Date(file.lastModified)
                    });
                    
                    if (!file.type.match('image.*')) {
                        console.warn(`DEBUG: File ${file.name} is not an image, skipping`);
                        return;
                    }
                    
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        console.log(`DEBUG: File ${file.name} loaded successfully`);
                        const img = document.createElement('img');
                        img.classList.add('image-preview');
                        img.src = e.target.result;
                        previewContainer.appendChild(img);
                    }
                    
                    reader.onerror = function(e) {
                        console.error(`DEBUG: Error reading file ${file.name}:`, e);
                    };
                    
                    reader.readAsDataURL(file);
                });
            } else {
                console.log('DEBUG: No files selected or files array is empty');
            }
        });
        
        // Monitor the form submission with files
        const form = imageInput.closest('form');
        if (form) {
            console.log('DEBUG: Found parent form, adding submit event listener');
            
            form.addEventListener('submit', function(e) {
                // Don't prevent the form from submitting, just log information
                console.log('DEBUG: Form is being submitted');
                
                if (imageInput.files && imageInput.files.length > 0) {
                    console.log('DEBUG: Form submission includes', imageInput.files.length, 'files');
                    
                    // Check if FormData is supported and log the data being sent
                    if (window.FormData) {
                        const formData = new FormData(form);
                        console.log('DEBUG: FormData entries:');
                        for (let pair of formData.entries()) {
                            if (pair[0] === 'images[]') {
                                console.log('DEBUG: FormData entry -', pair[0], ':', pair[1].name, '(', pair[1].size, 'bytes )');
                            } else {
                                console.log('DEBUG: FormData entry -', pair[0], ':', pair[1]);
                            }
                        }
                    }
                } else {
                    console.log('DEBUG: Form is being submitted without any files');
                }
            });
        } else {
            console.warn('DEBUG: Could not find parent form element');
        }
    } else {
        console.warn('DEBUG: Image input element #post-images not found in the DOM');
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
