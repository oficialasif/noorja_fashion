<?php
// Product functions
function getFeaturedProducts($conn, $limit = 8) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE featured = 1 AND status = 'active' ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrendingProducts($conn, $limit = 6) {
    $stmt = $conn->prepare("SELECT p.*, COUNT(o.id) as order_count FROM products p 
                           LEFT JOIN order_items oi ON p.id = oi.product_id 
                           LEFT JOIN orders o ON oi.order_id = o.id 
                           WHERE p.status = 'active' 
                           GROUP BY p.id 
                           ORDER BY order_count DESC, p.created_at DESC 
                           LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFlashSaleProducts($conn, $limit = 4) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE badge = 'Flash Sale' AND status = 'active' ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOfferProducts($conn, $limit = 8) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE sale_price > 0 AND status = 'active' ORDER BY (price - sale_price) DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProducts($conn, $category_id = null, $search = null, $sort = 'newest', $page = 1) {
    $where_conditions = ["status = 'active'"];
    $params = [];
    
    if ($category_id) {
        $where_conditions[] = "category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    
    if ($search) {
        $where_conditions[] = "(name LIKE :search1 OR description LIKE :search2)";
        $params[':search1'] = "%$search%";
        $params[':search2'] = "%$search%";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    $order_clause = match($sort) {
        'price_low' => 'ORDER BY price ASC',
        'price_high' => 'ORDER BY price DESC',
        'name' => 'ORDER BY name ASC',
        default => 'ORDER BY created_at DESC'
    };
    
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    $limit = ITEMS_PER_PAGE;
    
    $stmt = $conn->prepare("SELECT * FROM products WHERE $where_clause $order_clause LIMIT :limit OFFSET :offset");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalProducts($conn, $category_id = null, $search = null) {
    $where_conditions = ["status = 'active'"];
    $params = [];
    
    if ($category_id) {
        $where_conditions[] = "category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    
    if ($search) {
        $where_conditions[] = "(name LIKE :search1 OR description LIKE :search2)";
        $params[':search1'] = "%$search%";
        $params[':search2'] = "%$search%";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE $where_clause");
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function getProductById($conn, $id) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = :id AND p.status = 'active'");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCategories($conn) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategoryById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id AND status = 'active'");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// User functions
function registerUser($conn, $data) {
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address) VALUES (:name, :email, :password, :phone, :address)");
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':address', $data['address']);
    return $stmt->execute();
}

function loginUser($conn, $email, $password) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND status = 'active'");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

function getUserById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateUser($conn, $id, $data) {
    $stmt = $conn->prepare("UPDATE users SET name = :name, email = :email, phone = :phone, address = :address WHERE id = :id");
    $stmt->bindParam(':name', $data['name']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':address', $data['address']);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

// Cart functions
function addToCart($conn, $user_id, $product_id, $quantity = 1) {
    // Check if item already exists in cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_item) {
        // Update quantity
        $new_quantity = $existing_item['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = :quantity WHERE id = :id");
        $stmt->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
        $stmt->bindParam(':id', $existing_item['id'], PDO::PARAM_INT);
        return $stmt->execute();
    } else {
        // Add new item
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

function getCartItems($conn, $user_id) {
    $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.sale_price, p.image_url, p.stock 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateCartQuantity($conn, $cart_id, $quantity) {
    if ($quantity <= 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = :cart_id");
        $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
        return $stmt->execute();
    } else {
        $stmt = $conn->prepare("UPDATE cart SET quantity = :quantity WHERE id = :cart_id");
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

function removeFromCart($conn, $cart_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = :cart_id");
    $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_INT);
    return $stmt->execute();
}

function clearCart($conn, $user_id) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    return $stmt->execute();
}

function getCartTotal($conn, $user_id) {
    $items = getCartItems($conn, $user_id);
    $total = 0;
    
    foreach ($items as $item) {
        $price = $item['sale_price'] ?: $item['price'];
        $total += $price * $item['quantity'];
    }
    
    return $total;
}

// Order functions
function createOrder($conn, $user_id, $data) {
    try {
        $conn->beginTransaction();
        
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method, order_status) 
                               VALUES (:user_id, :total_amount, :shipping_address, :payment_method, 'pending')");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':total_amount', $data['total_amount']);
        $stmt->bindParam(':shipping_address', $data['shipping_address']);
        $stmt->bindParam(':payment_method', $data['payment_method']);
        $stmt->execute();
        $order_id = $conn->lastInsertId();
        
        // Get cart items
        $cart_items = getCartItems($conn, $user_id);
        
        // Add order items
        foreach ($cart_items as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)");
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':price', $price);
            $stmt->execute();
            
            // Update product stock
            $new_stock = $item['stock'] - $item['quantity'];
            $stmt = $conn->prepare("UPDATE products SET stock = :stock WHERE id = :product_id");
            $stmt->bindParam(':stock', $new_stock, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
            $stmt->execute();
        }
        
        // Clear cart
        clearCart($conn, $user_id);
        
        $conn->commit();
        return $order_id;
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

function getUserOrders($conn, $user_id, $limit = null) {
    if ($limit) {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    } else {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderById($conn, $order_id, $user_id = null) {
    $sql = "SELECT o.*, u.name as user_name, u.email as user_email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = :order_id";
    $params = [':order_id' => $order_id];
    
    if ($user_id) {
        $sql .= " AND o.user_id = :user_id";
        $params[':user_id'] = $user_id;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getOrderItems($conn, $order_id) {
    $stmt = $conn->prepare("SELECT oi.*, p.name, p.image_url 
                           FROM order_items oi 
                           JOIN products p ON oi.product_id = p.id 
                           WHERE oi.order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Wishlist functions
function addToWishlist($conn, $user_id, $product_id) {
    $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (:user_id, :product_id)");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    return $stmt->execute();
}

function removeFromWishlist($conn, $user_id, $product_id) {
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    return $stmt->execute();
}

function getWishlistItems($conn, $user_id) {
    $stmt = $conn->prepare("SELECT w.*, p.name, p.price, p.sale_price, p.image_url, p.stock 
                           FROM wishlist w 
                           JOIN products p ON w.product_id = p.id 
                           WHERE w.user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function isInWishlist($conn, $user_id, $product_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = :user_id AND product_id = :product_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

function getRelatedProducts($conn, $product_id, $category_id, $limit = 4) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = :category_id AND id != :product_id AND status = 'active' ORDER BY RAND() LIMIT :limit");
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Utility functions
function formatPrice($price) {
    return CURRENCY . number_format($price);
}

function generateOrderNumber() {
    return 'NOORJA-' . date('Ymd') . '-' . strtoupper(uniqid());
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function getPaginationInfo($total_items, $current_page, $items_per_page = ITEMS_PER_PAGE) {
    $total_pages = ceil($total_items / $items_per_page);
    $offset = ($current_page - 1) * $items_per_page;
    
    return [
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'items_per_page' => $items_per_page,
        'offset' => $offset,
        'has_previous' => $current_page > 1,
        'has_next' => $current_page < $total_pages,
        'previous_page' => $current_page - 1,
        'next_page' => $current_page + 1
    ];
}

function generatePaginationLinks($pagination_info, $base_url) {
    $links = [];
    
    if ($pagination_info['has_previous']) {
        $links[] = '<a href="' . $base_url . '?page=' . $pagination_info['previous_page'] . '" class="page-link">Previous</a>';
    }
    
    for ($i = 1; $i <= $pagination_info['total_pages']; $i++) {
        $active_class = $i == $pagination_info['current_page'] ? 'active' : '';
        $links[] = '<a href="' . $base_url . '?page=' . $i . '" class="page-link ' . $active_class . '">' . $i . '</a>';
    }
    
    if ($pagination_info['has_next']) {
        $links[] = '<a href="' . $base_url . '?page=' . $pagination_info['next_page'] . '" class="page-link">Next</a>';
    }
    
    return $links;
}

// Admin functions
function getTotalOrders($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders");
    $stmt->execute();
    return $stmt->fetchColumn();
}

function getTotalUsers($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $stmt->execute();
    return $stmt->fetchColumn();
}

function getTotalRevenue($conn) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status IN ('confirmed', 'processing', 'shipped', 'delivered')");
    $stmt->execute();
    return $stmt->fetchColumn();
}

function getRecentOrders($conn, $limit = 5) {
    $stmt = $conn->prepare("SELECT o.*, u.name as user_name FROM orders o 
                           JOIN users u ON o.user_id = u.id 
                           ORDER BY o.created_at DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLowStockProducts($conn, $limit = 5) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE stock <= 10 AND status = 'active' ORDER BY stock ASC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderStatusBadge($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'confirmed':
            return 'info';
        case 'processing':
            return 'primary';
        case 'shipped':
            return 'info';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

// Settings functions
function getSetting($conn, $key, $default = null) {
    static $settings_cache = [];
    
    // Check if settings are already cached
    if (empty($settings_cache)) {
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings_cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $settings_cache[$key] ?? $default;
}

function getSettings($conn) {
    static $settings_cache = [];
    
    // Check if settings are already cached
    if (empty($settings_cache)) {
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings_cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    
    return $settings_cache;
}

function updateSetting($conn, $key, $value) {
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value2");
    return $stmt->execute([':key' => $key, ':value' => $value, ':value2' => $value]);
}
?>
