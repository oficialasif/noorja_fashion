// NOORJA - Main JavaScript File

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Smooth scrolling for anchor links
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

    // Newsletter form submission
    const newsletterForms = document.querySelectorAll('.newsletter-form');
    newsletterForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (email) {
                // Show success message
                showNotification('Thank you for subscribing!', 'success');
                this.reset();
            }
        });
    });

    // Product image lazy loading
    const productImages = document.querySelectorAll('.product-image img');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        productImages.forEach(img => {
            if (img.dataset.src) {
                imageObserver.observe(img);
            }
        });
    }

    // Quantity input controls
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        const minusBtn = input.parentNode.querySelector('.quantity-minus');
        const plusBtn = input.parentNode.querySelector('.quantity-plus');
        
        if (minusBtn) {
            minusBtn.addEventListener('click', function() {
                const currentValue = parseInt(input.value);
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
        
        if (plusBtn) {
            plusBtn.addEventListener('click', function() {
                const currentValue = parseInt(input.value);
                const maxValue = parseInt(input.getAttribute('max')) || 999;
                if (currentValue < maxValue) {
                    input.value = currentValue + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });

    // Search functionality
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 500);
        });
    }

    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (mobileMenuToggle && navbarCollapse) {
        mobileMenuToggle.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navbarCollapse.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                navbarCollapse.classList.remove('show');
            }
        });
    }

    // Back to top button
    const backToTopBtn = document.createElement('button');
    backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopBtn.className = 'btn btn-primary back-to-top';
    backToTopBtn.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        display: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    `;
    document.body.appendChild(backToTopBtn);

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.display = 'block';
        } else {
            backToTopBtn.style.display = 'none';
        }
    });

    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Wishlist toggle
    const wishlistButtons = document.querySelectorAll('.wishlist-toggle');
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            toggleWishlist(productId, this);
        });
    });

    // Product quick view
    const quickViewButtons = document.querySelectorAll('.quick-view-btn');
    quickViewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            openQuickView(productId);
        });
    });

    // Filter toggle for mobile
    const filterToggle = document.querySelector('.filter-toggle');
    const filterSidebar = document.querySelector('.filter-sidebar');
    
    if (filterToggle && filterSidebar) {
        filterToggle.addEventListener('click', function() {
            filterSidebar.classList.toggle('show');
        });
    }
});

// Utility Functions

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    `;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function performSearch(query) {
    if (query.length < 2) return;
    
    // You can implement AJAX search here
    console.log('Searching for:', query);
}

function toggleWishlist(productId, button) {
    if (!isLoggedIn()) {
        showNotification('Please login to add items to wishlist', 'warning');
        return;
    }

    console.log('Toggling wishlist for product:', productId);

    fetch('ajax/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'toggle',
            product_id: productId
        })
    })
    .then(response => {
        console.log('Wishlist response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Wishlist response data:', data);
        if (data.success) {
            const icon = button.querySelector('i');
            if (data.in_wishlist) {
                icon.className = 'fas fa-heart text-danger';
                showNotification('Added to wishlist', 'success');
            } else {
                icon.className = 'far fa-heart';
                showNotification('Removed from wishlist', 'info');
            }
        } else {
            showNotification(data.message || 'Error updating wishlist', 'error');
        }
    })
    .catch(error => {
        console.error('Wishlist error:', error);
        showNotification('Error updating wishlist: ' + error.message, 'error');
    });
}

function openQuickView(productId) {
    fetch(`ajax/product.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showQuickViewModal(data.product);
            } else {
                showNotification('Error loading product details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading product details', 'error');
        });
}

function showQuickViewModal(product) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'quickViewModal';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${product.name}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img src="${product.image_url}" alt="${product.name}" class="img-fluid">
                        </div>
                        <div class="col-md-6">
                            <h4>${product.name}</h4>
                            <p class="text-muted">${product.description}</p>
                            <div class="price mb-3">
                                                <span class="current-price">৳${product.price}</span>
                ${product.sale_price ? `<span class="original-price">৳${product.sale_price}</span>` : ''}
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary add-to-cart" data-product-id="${product.id}">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                                <button class="btn btn-outline-primary wishlist-toggle" data-product-id="${product.id}">
                                    <i class="far fa-heart"></i> Add to Wishlist
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
    
    modal.addEventListener('hidden.bs.modal', function() {
        modal.remove();
    });
}

function isLoggedIn() {
    // Check if user is logged in (you can implement this based on your session management)
    return document.body.classList.contains('logged-in');
}

// Form validation
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Price formatting
function formatPrice(price) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(price);
}

// Debounce function
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

// Throttle function
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
