// NOORJA - Cart JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const quantity = this.dataset.quantity || 1;
            addToCart(productId, quantity, this);
        });
    });

    // Update cart quantity
    const quantityInputs = document.querySelectorAll('.cart-quantity');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const cartId = this.dataset.cartId;
            const quantity = this.value;
            updateCartQuantity(cartId, quantity);
        });
    });

    // Remove from cart
    const removeCartButtons = document.querySelectorAll('.remove-from-cart');
    removeCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const cartId = this.dataset.cartId;
            removeFromCart(cartId);
        });
    });

    // Clear cart
    const clearCartButton = document.querySelector('.clear-cart');
    if (clearCartButton) {
        clearCartButton.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to clear your cart?')) {
                clearCart();
            }
        });
    }

    // Apply coupon
    const couponForm = document.querySelector('.coupon-form');
    if (couponForm) {
        couponForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const couponCode = this.querySelector('input[name="coupon_code"]').value;
            applyCoupon(couponCode);
        });
    }

    // Checkout form validation
    const checkoutForm = document.querySelector('.checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            if (!validateCheckoutForm(this)) {
                e.preventDefault();
            }
        });
    }
});

// Add to cart function
function addToCart(productId, quantity = 1, button = null) {
    if (!isLoggedIn()) {
        showNotification('Please login to add items to cart', 'warning');
        return;
    }

    // Show loading state
    if (button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<span class="loading"></span> Adding...';
        button.disabled = true;
    }

    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => {
        console.log('Cart response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Cart response data:', data);
        if (data.success) {
            showNotification('Product added to cart successfully!', 'success');
            updateCartCount(data.cart_count);
            updateCartTotal(data.cart_total);
            
            // Update button if it's a "Buy Now" button
            if (button && button.classList.contains('buy-now')) {
                window.location.href = 'cart.php';
            }
        } else {
            showNotification(data.message || 'Error adding product to cart', 'error');
        }
    })
    .catch(error => {
        console.error('Cart error:', error);
        showNotification('Error adding product to cart: ' + error.message, 'error');
    })
    .finally(() => {
        // Restore button state
        if (button) {
            button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
            button.disabled = false;
        }
    });
}

// Update cart quantity
function updateCartQuantity(cartId, quantity) {
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update',
            cart_id: cartId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartDisplay(data);
        } else {
            showNotification(data.message || 'Error updating cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating cart', 'error');
    });
}

// Remove from cart
function removeFromCart(cartId) {
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            cart_id: cartId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product removed from cart', 'success');
            updateCartDisplay(data);
            
            // Remove the cart item element
            const cartItem = document.querySelector(`[data-cart-id="${cartId}"]`);
            if (cartItem) {
                cartItem.remove();
            }
        } else {
            showNotification(data.message || 'Error removing product from cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error removing product from cart', 'error');
    });
}

// Clear cart
function clearCart() {
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'clear'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Cart cleared successfully', 'success');
            updateCartDisplay(data);
            
            // Clear cart items display
            const cartItemsContainer = document.querySelector('.cart-items');
            if (cartItemsContainer) {
                cartItemsContainer.innerHTML = '<p class="text-center text-muted">Your cart is empty</p>';
            }
        } else {
            showNotification(data.message || 'Error clearing cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error clearing cart', 'error');
    });
}

// Apply coupon
function applyCoupon(couponCode) {
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'apply_coupon',
            coupon_code: couponCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Coupon applied successfully!', 'success');
            updateCartDisplay(data);
        } else {
            showNotification(data.message || 'Invalid coupon code', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error applying coupon', 'error');
    });
}

// Update cart display
function updateCartDisplay(data) {
    // Update cart count in header
    updateCartCount(data.cart_count);
    
    // Update cart total
    updateCartTotal(data.cart_total);
    
    // Update cart summary if on cart page
    const cartSummary = document.querySelector('.cart-summary');
    if (cartSummary) {
        updateCartSummary(data);
    }
    
    // Update mini cart if exists
    const miniCart = document.querySelector('.mini-cart');
    if (miniCart) {
        updateMiniCart(data);
    }
}

// Update cart count in header and sidebar
function updateCartCount(count) {
    // Update cart badge in header
    const headerCartBadge = document.querySelector('.navbar-nav .nav-link[href*="cart.php"] .badge');
    
    if (headerCartBadge) {
        if (count > 0) {
            headerCartBadge.textContent = count;
            headerCartBadge.style.display = 'block';
        } else {
            headerCartBadge.style.display = 'none';
        }
    } else if (count > 0) {
        // If badge doesn't exist but count > 0, create it
        const cartLink = document.querySelector('.navbar-nav .nav-link[href*="cart.php"]');
        if (cartLink) {
            const newBadge = document.createElement('span');
            newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary';
            newBadge.textContent = count;
            cartLink.appendChild(newBadge);
        }
    }
    
    // Update cart badge in user dashboard sidebar (all user pages)
    const sidebarCartBadges = document.querySelectorAll('.dashboard-nav .nav-link[href*="cart.php"] .badge');
    sidebarCartBadges.forEach(badge => {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    });
    
    // If no sidebar badges exist but count > 0, create them
    if (count > 0 && sidebarCartBadges.length === 0) {
        const sidebarCartLinks = document.querySelectorAll('.dashboard-nav .nav-link[href*="cart.php"]');
        sidebarCartLinks.forEach(link => {
            const newBadge = document.createElement('span');
            newBadge.className = 'badge bg-primary ms-2';
            newBadge.textContent = count;
            link.appendChild(newBadge);
        });
    }
}

// Update cart total
function updateCartTotal(total) {
    const totalElements = document.querySelectorAll('.cart-total');
    totalElements.forEach(element => {
        element.textContent = formatPrice(total);
    });
}

// Update cart summary
function updateCartSummary(data) {
    const subtotalElement = document.querySelector('.cart-subtotal');
    const taxElement = document.querySelector('.cart-tax');
    const shippingElement = document.querySelector('.cart-shipping');
    const totalElement = document.querySelector('.cart-total');
    const discountElement = document.querySelector('.cart-discount');
    
    if (subtotalElement) subtotalElement.textContent = formatPrice(data.subtotal);
    if (taxElement) taxElement.textContent = formatPrice(data.tax);
    if (shippingElement) shippingElement.textContent = formatPrice(data.shipping);
    if (totalElement) totalElement.textContent = formatPrice(data.total);
    if (discountElement && data.discount > 0) {
        discountElement.textContent = `-${formatPrice(data.discount)}`;
        discountElement.style.display = 'block';
    }
}

// Update mini cart
function updateMiniCart(data) {
    const miniCartItems = document.querySelector('.mini-cart-items');
    const miniCartTotal = document.querySelector('.mini-cart-total');
    
    if (miniCartItems) {
        if (data.items && data.items.length > 0) {
            let itemsHtml = '';
            data.items.forEach(item => {
                itemsHtml += `
                    <div class="mini-cart-item">
                        <img src="${item.image_url}" alt="${item.name}" class="mini-cart-item-image">
                        <div class="mini-cart-item-details">
                            <h6>${item.name}</h6>
                            <p>${formatPrice(item.price)} x ${item.quantity}</p>
                        </div>
                        <button class="btn btn-sm btn-outline-danger remove-from-cart" data-cart-id="${item.cart_id}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            });
            miniCartItems.innerHTML = itemsHtml;
        } else {
            miniCartItems.innerHTML = '<p class="text-center text-muted">Your cart is empty</p>';
        }
    }
    
    if (miniCartTotal) {
        miniCartTotal.textContent = formatPrice(data.cart_total);
    }
}

// Validate checkout form
function validateCheckoutForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Validate email
    const emailField = form.querySelector('input[type="email"]');
    if (emailField && emailField.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailField.value)) {
            emailField.classList.add('is-invalid');
            isValid = false;
        }
    }
    
    // Validate phone
    const phoneField = form.querySelector('input[name="phone"]');
    if (phoneField && phoneField.value) {
        const phoneRegex = /^[0-9]{10}$/;
        if (!phoneRegex.test(phoneField.value.replace(/\D/g, ''))) {
            phoneField.classList.add('is-invalid');
            isValid = false;
        }
    }
    
    if (!isValid) {
        showNotification('Please fill in all required fields correctly', 'error');
    }
    
    return isValid;
}

// Format price helper function
function formatPrice(price) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(price);
}

// Check if user is logged in
function isLoggedIn() {
    return document.body.classList.contains('logged-in');
}

// Show notification helper function
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
