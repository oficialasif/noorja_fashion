<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Please login to view your cart');
    redirect('auth.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/cart.php'));
}

// Get cart items
$cart_items = getCartItems($conn, $_SESSION['user_id']);
$cart_total = getCartTotal($conn, $_SESSION['user_id']);

// Get dynamic settings
$dynamic_shipping_cost = (float) getSetting($conn, 'shipping_cost', 100);
$tax_rate = (float) getSetting($conn, 'tax_rate', 18);

// Calculate shipping and tax
$shipping_cost = $cart_total >= 1000 ? 0 : $dynamic_shipping_cost; // Free shipping above ৳1000

$tax_amount = ($cart_total * $tax_rate) / 100;
$final_total = $cart_total + $shipping_cost + $tax_amount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - NOORJA</title>
    <meta name="description" content="View and manage your shopping cart at NOORJA.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body<?php echo isLoggedIn() ? ' class="logged-in"' : ''; ?>>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Cart Section -->
    <section class="cart-section py-5">
        <div class="container">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="page-title">Shopping Cart</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Cart</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?php if (empty($cart_items)): ?>
            <!-- Empty Cart -->
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                <h3>Your cart is empty</h3>
                <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
                <a href="shop.php" class="btn btn-primary btn-lg">Start Shopping</a>
            </div>
            <?php else: ?>
            <!-- Cart Items -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-items">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-shopping-cart"></i> Cart Items (<?php echo count($cart_items); ?>)
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($cart_items as $item): ?>
                                <div class="cart-item mb-4" data-cart-id="<?php echo $item['id']; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>" class="img-fluid rounded">
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="cart-item-title"><?php echo $item['name']; ?></h6>
                                            <p class="text-muted mb-0">SKU: NOORJA-<?php echo str_pad($item['product_id'], 4, '0', STR_PAD_LEFT); ?></p>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="cart-item-price">
                                                <?php if ($item['sale_price']): ?>
                                                                                <span class="current-price">৳<?php echo number_format($item['sale_price']); ?></span>
                                <span class="original-price">৳<?php echo number_format($item['price']); ?></span>
                                                <?php else: ?>
                                                <span class="current-price">৳<?php echo number_format($item['price']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="quantity-controls">
                                                <button class="btn btn-sm btn-outline-secondary quantity-minus">-</button>
                                                <input type="number" class="form-control cart-quantity" value="<?php echo $item['quantity']; ?>" 
                                                       min="1" max="<?php echo $item['stock']; ?>" data-cart-id="<?php echo $item['id']; ?>">
                                                <button class="btn btn-sm btn-outline-secondary quantity-plus">+</button>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="cart-item-total">
                                                ৳<?php echo number_format(($item['sale_price'] ?: $item['price']) * $item['quantity']); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <button class="btn btn-sm btn-outline-danger remove-from-cart" data-cart-id="<?php echo $item['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-outline-secondary clear-cart">
                                        <i class="fas fa-trash"></i> Clear Cart
                                    </button>
                                    <a href="shop.php" class="btn btn-outline-primary">
                                        <i class="fas fa-arrow-left"></i> Continue Shopping
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="summary-item d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span class="cart-subtotal">৳<?php echo number_format($cart_total); ?></span>
                                </div>
                                
                                <div class="summary-item d-flex justify-content-between mb-2">
                                    <span>Shipping:</span>
                                    <span class="cart-shipping">
                                        <?php if ($shipping_cost == 0): ?>
                                        <span class="text-success">Free</span>
                                        <?php else: ?>
                                        ৳<?php echo number_format($shipping_cost); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                
                                <div class="summary-item d-flex justify-content-between mb-2">
                                    <span>Tax (<?php echo $tax_rate; ?>%):</span>
                                    <span class="cart-tax">৳<?php echo number_format($tax_amount); ?></span>
                                </div>
                                
                                <?php if ($shipping_cost > 0): ?>
                                <div class="alert alert-info">
                                    <small>
                                        <i class="fas fa-info-circle"></i> 
                                        Add ৳<?php echo number_format(1000 - $cart_total); ?> more to get free shipping!
                                    </small>
                                </div>
                                <?php endif; ?>
                                
                                <hr>
                                
                                <div class="summary-item d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong class="cart-total">৳<?php echo number_format($final_total); ?></strong>
                                </div>
                                
                                <!-- Coupon Code -->
                                <div class="coupon-section mb-3">
                                    <form class="coupon-form">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="coupon_code" placeholder="Enter coupon code">
                                            <button class="btn btn-outline-primary" type="submit">Apply</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Checkout Buttons -->
                                <div class="d-grid gap-2">
                                    <a href="checkout.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                                    </a>
                                    <a href="shop.php" class="btn btn-outline-primary">
                                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Security Info -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <h6><i class="fas fa-shield-alt text-success"></i> Secure Checkout</h6>
                                <p class="small text-muted mb-0">
                                    Your payment information is encrypted and secure. We never store your credit card details.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Payment Methods -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <h6>Accepted Payment Methods</h6>
                                <div class="payment-methods">
                                    <i class="fab fa-cc-visa text-primary"></i>
                                    <i class="fab fa-cc-mastercard text-warning"></i>
                                    <i class="fab fa-cc-amex text-info"></i>
                                    <i class="fab fa-cc-paypal text-primary"></i>
                                    <i class="fas fa-money-bill-wave text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/cart.js"></script>
    
    <script>
        // Update cart summary when quantities change
        function updateCartSummary() {
            const subtotalElement = document.querySelector('.cart-subtotal');
            const shippingElement = document.querySelector('.cart-shipping');
            const taxElement = document.querySelector('.cart-tax');
            const totalElement = document.querySelector('.cart-total');
            
            // Calculate totals from cart items
            let subtotal = 0;
            document.querySelectorAll('.cart-item').forEach(item => {
                const price = parseFloat(item.querySelector('.current-price').textContent.replace('৳', '').replace(',', ''));
                const quantity = parseInt(item.querySelector('.cart-quantity').value);
                subtotal += price * quantity;
            });
            
            const shipping = subtotal >= 1000 ? 0 : <?php echo $dynamic_shipping_cost; ?>;
            const tax = (subtotal * <?php echo $tax_rate; ?>) / 100;
            const total = subtotal + shipping + tax;
            
            // Update display
            subtotalElement.textContent = '৳' + subtotal.toLocaleString();
            shippingElement.innerHTML = shipping === 0 ? '<span class="text-success">Free</span>' : '৳' + shipping.toLocaleString();
            taxElement.textContent = '৳' + tax.toLocaleString();
            totalElement.textContent = '৳' + total.toLocaleString();
        }
        
        // Initialize quantity controls
        document.querySelectorAll('.quantity-controls').forEach(control => {
            const minusBtn = control.querySelector('.quantity-minus');
            const plusBtn = control.querySelector('.quantity-plus');
            const input = control.querySelector('.cart-quantity');
            
            minusBtn.addEventListener('click', function() {
                const currentValue = parseInt(input.value);
                if (currentValue > 1) {
                    input.value = currentValue - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
            
            plusBtn.addEventListener('click', function() {
                const currentValue = parseInt(input.value);
                const maxValue = parseInt(input.getAttribute('max'));
                if (currentValue < maxValue) {
                    input.value = currentValue + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        });
        
        // Update cart item total when quantity changes
        document.querySelectorAll('.cart-quantity').forEach(input => {
            input.addEventListener('change', function() {
                const cartItem = this.closest('.cart-item');
                const price = parseFloat(cartItem.querySelector('.current-price').textContent.replace('৳', '').replace(',', ''));
                const quantity = parseInt(this.value);
                const total = price * quantity;
                
                cartItem.querySelector('.cart-item-total').textContent = '৳' + total.toLocaleString();
                updateCartSummary();
            });
        });
    </script>
</body>
</html>
