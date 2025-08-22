<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    echo "Please login first to test cart functionality.";
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_items = getCartItems($conn, $user_id);
$cart_count = count($cart_items);
$cart_total = getCartTotal($conn, $user_id);

echo "<h1>Cart Count Test</h1>";
echo "<p>Current cart count: $cart_count</p>";
echo "<p>Current cart total: ৳" . number_format($cart_total) . "</p>";

// Get some products for testing
$products = getProducts($conn, null, null, 'newest', 1);
if (!empty($products)) {
    $test_product = $products[0];
    echo "<h2>Test Product</h2>";
    echo "<p>Product: " . htmlspecialchars($test_product['name']) . "</p>";
    echo "<p>Price: ৳" . number_format($test_product['price']) . "</p>";
    echo "<p>Stock: " . $test_product['stock'] . "</p>";
    
    echo "<h2>Test Add to Cart</h2>";
    echo "<button onclick='testAddToCart(" . $test_product['id'] . ")'>Add to Cart</button>";
    echo "<button onclick='testRemoveFromCart()'>Remove from Cart</button>";
    echo "<button onclick='location.reload()'>Refresh Page</button>";
    
    echo "<h2>Current Cart Items</h2>";
    if (empty($cart_items)) {
        echo "<p>Cart is empty</p>";
    } else {
        echo "<ul>";
        foreach ($cart_items as $item) {
            echo "<li>" . htmlspecialchars($item['name']) . " - Qty: " . $item['quantity'] . " - ৳" . number_format($item['price']) . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p>No products available for testing</p>";
}
?>

<script>
function testAddToCart(productId) {
    console.log('Testing add to cart for product:', productId);
    
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Cart response:', data);
        if (data.success) {
            alert('Product added to cart! New count: ' + data.cart_count);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding to cart');
    });
}

function testRemoveFromCart() {
    // Get the first cart item
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_cart'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.cart_items.length > 0) {
            const firstItem = data.cart_items[0];
            console.log('Removing cart item:', firstItem.cart_id);
            
            return fetch('ajax/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove',
                    cart_id: firstItem.cart_id
                })
            });
        } else {
            alert('No items in cart to remove');
            return null;
        }
    })
    .then(response => response ? response.json() : null)
    .then(data => {
        if (data) {
            console.log('Remove response:', data);
            if (data.success) {
                alert('Product removed from cart! New count: ' + data.cart_count);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error removing from cart');
    });
}
</script>
