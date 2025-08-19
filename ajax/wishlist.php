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
        
        // Check if already in wishlist
        if (isInWishlist($conn, $user_id, $product_id)) {
            echo json_encode(['success' => false, 'message' => 'Product already in wishlist']);
            exit;
        }
        
        // Add to wishlist
        $result = addToWishlist($conn, $user_id, $product_id);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Product added to wishlist successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product to wishlist']);
        }
        break;
        
    case 'remove':
        $product_id = (int)($_POST['product_id'] ?? 0);
        
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product']);
            exit;
        }
        
        // Remove from wishlist
        $result = removeFromWishlist($conn, $user_id, $product_id);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Product removed from wishlist'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove product from wishlist']);
        }
        break;
        
    case 'get_wishlist':
        $wishlist_items = getWishlistItems($conn, $user_id);
        
        echo json_encode([
            'success' => true,
            'wishlist_items' => $wishlist_items,
            'wishlist_count' => count($wishlist_items)
        ]);
        break;
        
    case 'check_wishlist':
        $product_id = (int)($_POST['product_id'] ?? 0);
        
        if ($product_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product']);
            exit;
        }
        
        $in_wishlist = isInWishlist($conn, $user_id, $product_id);
        
        echo json_encode([
            'success' => true,
            'in_wishlist' => $in_wishlist
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
