<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$product_id = (int)($input['product_id'] ?? 0);
$rating = (int)($input['rating'] ?? 0);
$review = sanitizeInput($input['review'] ?? '');

// Validation
if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
}

if (empty($review)) {
    echo json_encode(['success' => false, 'message' => 'Review text is required']);
    exit;
}

// Check if product exists and is active
$product = getProductById($conn, $product_id);
if (!$product || $product['status'] !== 'active') {
    echo json_encode(['success' => false, 'message' => 'Product not available']);
    exit;
}

// Check if user has already reviewed this product
$stmt = $conn->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);

if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
    exit;
}

// Check if user has purchased this product (optional validation)
$stmt = $conn->prepare("SELECT oi.id FROM order_items oi 
                       JOIN orders o ON oi.order_id = o.id 
                       WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'");
$stmt->execute([$user_id, $product_id]);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You can only review products you have purchased']);
    exit;
}

// Insert review
try {
    $stmt = $conn->prepare("INSERT INTO product_reviews (user_id, product_id, rating, review, created_at) VALUES (?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$user_id, $product_id, $rating, $review]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Review submitted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
