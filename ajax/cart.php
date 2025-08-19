<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product']);
            exit;
        }
        
        // Check if product exists and is active
        $product = getProductById($conn, $product_id);
        if (!$product || $product['status'] !== 'active') {
            echo json_encode(['success' => false, 'message' => 'Product not available']);
            exit;
        }
        
        // Check stock
        if ($product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            exit;
        }
        
        // Add to cart
        $result = addToCart($conn, $user_id, $product_id, $quantity);
        
        if ($result) {
            // Get updated cart data
            $cart_items = getCartItems($conn, $user_id);
            $cart_total = getCartTotal($conn, $user_id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Product added to cart successfully',
                'cart_count' => count($cart_items),
                'cart_total' => $cart_total
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
        }
        break;
        
    case 'update':
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($cart_id <= 0 || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }
        
        // Check if cart item belongs to user
        $stmt = $conn->prepare("SELECT c.*, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cart_item) {
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            exit;
        }
        
        // Check stock
        if ($cart_item['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            exit;
        }
        
        // Update cart
        $result = updateCartQuantity($conn, $cart_id, $quantity);
        
        if ($result) {
            // Get updated cart data
            $cart_items = getCartItems($conn, $user_id);
            $cart_total = getCartTotal($conn, $user_id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cart updated successfully',
                'cart_count' => count($cart_items),
                'cart_total' => $cart_total
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
        }
        break;
        
    case 'remove':
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        
        if ($cart_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
            exit;
        }
        
        // Check if cart item belongs to user
        $stmt = $conn->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
        
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Cart item not found']);
            exit;
        }
        
        // Remove from cart
        $result = removeFromCart($conn, $cart_id);
        
        if ($result) {
            // Get updated cart data
            $cart_items = getCartItems($conn, $user_id);
            $cart_total = getCartTotal($conn, $user_id);
            
            echo json_encode([
                'success' => true,
                'message' => 'Product removed from cart',
                'cart_count' => count($cart_items),
                'cart_total' => $cart_total
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove product']);
        }
        break;
        
    case 'clear':
        $result = clearCart($conn, $user_id);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Cart cleared successfully',
                'cart_count' => 0,
                'cart_total' => 0
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
        }
        break;
        
    case 'get_cart':
        $cart_items = getCartItems($conn, $user_id);
        $cart_total = getCartTotal($conn, $user_id);
        
        echo json_encode([
            'success' => true,
            'cart_items' => $cart_items,
            'cart_count' => count($cart_items),
            'cart_total' => $cart_total
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
