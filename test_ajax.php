<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Test if user is logged in
if (!isLoggedIn()) {
    echo "User not logged in<br>";
    exit;
}

echo "User logged in: " . $_SESSION['user_id'] . "<br>";

// Test cart functions
echo "<h3>Testing Cart Functions:</h3>";
$user_id = $_SESSION['user_id'];

// Test getCartItems
$cart_items = getCartItems($conn, $user_id);
echo "Cart items count: " . count($cart_items) . "<br>";

// Test getCartTotal
$cart_total = getCartTotal($conn, $user_id);
echo "Cart total: " . $cart_total . "<br>";

// Test wishlist functions
echo "<h3>Testing Wishlist Functions:</h3>";

// Test getWishlistItems
$wishlist_items = getWishlistItems($conn, $user_id);
echo "Wishlist items count: " . count($wishlist_items) . "<br>";

// Test isInWishlist for first product
if (!empty($wishlist_items)) {
    $first_product_id = $wishlist_items[0]['product_id'];
    $in_wishlist = isInWishlist($conn, $user_id, $first_product_id);
    echo "Product $first_product_id in wishlist: " . ($in_wishlist ? 'Yes' : 'No') . "<br>";
}

// Test getProducts
echo "<h3>Testing Product Functions:</h3>";
$products = getProducts($conn, null, null, 'newest', 1);
echo "Products count: " . count($products) . "<br>";

if (!empty($products)) {
    $first_product = $products[0];
    echo "First product: " . $first_product['name'] . " (ID: " . $first_product['id'] . ")<br>";
    
    // Test getProductById
    $product = getProductById($conn, $first_product['id']);
    if ($product) {
        echo "Product found by ID: " . $product['name'] . "<br>";
    } else {
        echo "Product not found by ID<br>";
    }
}

echo "<h3>Testing AJAX Endpoints:</h3>";
echo "<p>To test AJAX endpoints, open browser console and try:</p>";
echo "<ul>";
echo "<li>Add to cart: fetch('ajax/cart.php', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'add', product_id: 1, quantity: 1})})</li>";
echo "<li>Toggle wishlist: fetch('ajax/wishlist.php', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'toggle', product_id: 1})})</li>";
echo "</ul>";
?>
