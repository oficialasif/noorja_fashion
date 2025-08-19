<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Please login to checkout');
    redirect('auth.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Get cart items
$cart_items = getCartItems($conn, $_SESSION['user_id']);

if (empty($cart_items)) {
    setFlashMessage('warning', 'Your cart is empty');
    redirect('cart.php');
}

// Get user details
$user = getUserById($conn, $_SESSION['user_id']);

// Get dynamic settings
$dynamic_shipping_cost = (float) getSetting($conn, 'shipping_cost', 100);
$tax_rate = (float) getSetting($conn, 'tax_rate', 18);

// Calculate totals
$cart_total = getCartTotal($conn, $_SESSION['user_id']);
$shipping_cost = $cart_total >= 1000 ? 0 : $dynamic_shipping_cost;

$tax_amount = ($cart_total * $tax_rate) / 100;
$final_total = $cart_total + $shipping_cost + $tax_amount;

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $billing_name = sanitizeInput($_POST['billing_name']);
    $billing_email = sanitizeInput($_POST['billing_email']);
    $billing_phone = sanitizeInput($_POST['billing_phone']);
    $billing_address = sanitizeInput($_POST['billing_address']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    
    // Validation
    $errors = [];
    
    if (empty($billing_name)) $errors[] = 'Billing name is required';
    if (empty($billing_email)) $errors[] = 'Billing email is required';
    if (!validateEmail($billing_email)) $errors[] = 'Please enter a valid email address';
    if (empty($billing_phone)) $errors[] = 'Billing phone is required';
    if (empty($billing_address)) $errors[] = 'Billing address is required';
    if (empty($payment_method)) $errors[] = 'Please select a payment method';
    
    if (empty($errors)) {
        try {
            // Create order
            $order_data = [
                'total_amount' => $final_total,
                'shipping_address' => $billing_address,
                'payment_method' => $payment_method
            ];
            
            $order_id = createOrder($conn, $_SESSION['user_id'], $order_data);
            
            if ($order_id) {
                // Generate order number
                $order_number = generateOrderNumber();
                
                // Update order with order number
                $stmt = $conn->prepare("UPDATE orders SET order_number = ? WHERE id = ?");
                $stmt->execute([$order_number, $order_id]);
                
                setFlashMessage('success', 'Order placed successfully! Your order number is: ' . $order_number);
                redirect('user/orders.php');
            } else {
                setFlashMessage('error', 'Failed to create order. Please try again.');
            }
        } catch (Exception $e) {
            setFlashMessage('error', 'An error occurred while processing your order. Please try again.');
        }
    } else {
        setFlashMessage('error', implode(', ', $errors));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - NOORJA</title>
    <meta name="description" content="Complete your purchase at NOORJA with secure checkout.">
    
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

    <!-- Checkout Section -->
    <section class="checkout-section py-5">
        <div class="container">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="page-title">Checkout</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
                            <li class="breadcrumb-item active">Checkout</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <form method="POST" class="checkout-form">
                <div class="row">
                    <!-- Checkout Form -->
                    <div class="col-lg-8">
                        <div class="checkout-form-container">
                            <!-- Billing Information -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user"></i> Billing Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="billing_name" class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" id="billing_name" name="billing_name" 
                                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="billing_email" class="form-label">Email Address *</label>
                                            <input type="email" class="form-control" id="billing_email" name="billing_email" 
                                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="billing_phone" class="form-label">Phone Number *</label>
                                            <input type="tel" class="form-control" id="billing_phone" name="billing_phone" 
                                                   value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="billing_address" class="form-label">Shipping Address *</label>
                                            <textarea class="form-control" id="billing_address" name="billing_address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-credit-card"></i> Payment Method
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="payment-methods">
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="COD" checked>
                                            <label class="form-check-label" for="cod">
                                                <i class="fas fa-money-bill-wave text-success"></i> Cash on Delivery (COD)
                                            </label>
                                            <small class="form-text text-muted">Pay when you receive your order</small>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="online" value="online">
                                            <label class="form-check-label" for="online">
                                                <i class="fas fa-credit-card text-primary"></i> Online Payment
                                            </label>
                                            <small class="form-text text-muted">Pay securely with credit/debit card or UPI</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Online Payment Fields (hidden by default) -->
                                    <div id="online-payment-fields" style="display: none;">
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="card_number" class="form-label">Card Number</label>
                                                <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="expiry" class="form-label">Expiry</label>
                                                <input type="text" class="form-control" id="expiry" placeholder="MM/YY">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="cvv" class="form-label">CVV</label>
                                                <input type="text" class="form-control" id="cvv" placeholder="123">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="card_name" class="form-label">Name on Card</label>
                                            <input type="text" class="form-control" id="card_name" placeholder="John Doe">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Notes -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-sticky-note"></i> Order Notes (Optional)
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" name="order_notes" rows="3" placeholder="Any special instructions for delivery..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="order-summary">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Order Summary</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Order Items -->
                                    <div class="order-items mb-3">
                                        <?php foreach ($cart_items as $item): ?>
                                        <div class="order-item d-flex justify-content-between align-items-center mb-2">
                                            <div class="order-item-details">
                                                <h6 class="mb-0"><?php echo $item['name']; ?></h6>
                                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                            </div>
                                            <div class="order-item-price">
                                                ৳<?php echo number_format(($item['sale_price'] ?: $item['price']) * $item['quantity']); ?>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <hr>
                                    
                                    <!-- Order Totals -->
                                    <div class="order-totals">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span>৳<?php echo number_format($cart_total); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Shipping:</span>
                                            <span>
                                                <?php if ($shipping_cost == 0): ?>
                                                <span class="text-success">Free</span>
                                                <?php else: ?>
                                                ৳<?php echo number_format($shipping_cost); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Tax (<?php echo $tax_rate; ?>%):</span>
                                            <span>৳<?php echo number_format($tax_amount); ?></span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between mb-3">
                                            <strong>Total:</strong>
                                            <strong>৳<?php echo number_format($final_total); ?></strong>
                                        </div>
                                    </div>
                                    
                                    <!-- Place Order Button -->
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-lock"></i> Place Order
                                        </button>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-shield-alt"></i> Your payment information is secure
                                        </small>
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
                            
                            <!-- Return Policy -->
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h6><i class="fas fa-undo text-info"></i> Easy Returns</h6>
                                    <p class="small text-muted mb-0">
                                        Not satisfied? Return your order within 30 days for a full refund.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <script>
        // Toggle online payment fields
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const onlineFields = document.getElementById('online-payment-fields');
                if (this.value === 'online') {
                    onlineFields.style.display = 'block';
                } else {
                    onlineFields.style.display = 'none';
                }
            });
        });
        
        // Form validation
        document.querySelector('.checkout-form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
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
            const emailField = document.getElementById('billing_email');
            if (emailField.value && !isValidEmail(emailField.value)) {
                emailField.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate phone
            const phoneField = document.getElementById('billing_phone');
            if (phoneField.value && !isValidPhone(phoneField.value)) {
                phoneField.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields correctly');
            }
        });
        
        // Email validation
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Phone validation
        function isValidPhone(phone) {
            const phoneRegex = /^[0-9]{10}$/;
            return phoneRegex.test(phone.replace(/\D/g, ''));
        }
        
        // Card number formatting
        document.getElementById('card_number').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            this.value = value.substring(0, 19);
        });
        
        // Expiry date formatting
        document.getElementById('expiry').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.value = value.substring(0, 5);
        });
        
        // CVV formatting
        document.getElementById('cvv').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 4);
        });
    </script>
</body>
</html>
