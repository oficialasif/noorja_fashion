<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Please login to access your wishlist');
    redirect('../auth.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Redirect admin users to admin dashboard
if (isAdmin()) {
    redirect('../admin/index.php');
}

$user_id = $_SESSION['user_id'];
$user = getUserById($conn, $user_id);

// Get wishlist items with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Get total count
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$total_wishlist_items = $count_stmt->fetchColumn();

$pagination = getPaginationInfo($total_wishlist_items, $page, 12);

// Get wishlist items
$stmt = $conn->prepare("SELECT w.*, p.name, p.description, p.price, p.sale_price, p.image_url, p.stock, p.status, c.name as category_name
                       FROM wishlist w 
                       JOIN products p ON w.product_id = p.id 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE w.user_id = :user_id 
                       ORDER BY w.created_at DESC 
                       LIMIT :limit OFFSET :offset");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':limit', $pagination['items_per_page'], PDO::PARAM_INT);
$stmt->bindParam(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get cart items count
$cart_items = getCartItems($conn, $user_id);
$cart_count = count($cart_items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - NOORJA</title>
    <meta name="description" content="Manage your wishlist and saved products at NOORJA.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body<?php echo isLoggedIn() ? ' class="logged-in"' : ''; ?>>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <!-- Wishlist Section -->
    <section class="wishlist-section py-5">
        <div class="container">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="page-title">My Wishlist</h1>
                    <p class="text-muted">Your saved products and favorites</p>
                </div>
            </div>

            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="dashboard-sidebar">
                        <div class="card">
                            <div class="card-body">
                                <div class="user-profile text-center mb-4">
                                    <div class="user-avatar mb-3">
                                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                                    </div>
                                    <h5><?php echo htmlspecialchars($user['name']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                                
                                <nav class="dashboard-nav">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link" href="dashboard.php">
                                                <i class="fas fa-tachometer-alt"></i> Dashboard
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="profile.php">
                                                <i class="fas fa-user"></i> My Profile
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="orders.php">
                                                <i class="fas fa-shopping-bag"></i> My Orders
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link active" href="wishlist.php">
                                                <i class="fas fa-heart"></i> Wishlist
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="../cart.php">
                                                <i class="fas fa-shopping-cart"></i> Cart
                                                <?php if ($cart_count > 0): ?>
                                                <span class="badge bg-primary ms-2"><?php echo $cart_count; ?></span>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="../auth.php?action=logout">
                                                <i class="fas fa-sign-out-alt"></i> Logout
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <!-- Wishlist Items -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($wishlist_items)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                                <h5>Your Wishlist is Empty</h5>
                                <p class="text-muted">Start adding products to your wishlist to see them here</p>
                                <a href="../shop.php" class="btn btn-primary">Browse Products</a>
                            </div>
                            <?php else: ?>
                            <div class="row">
                                <?php foreach ($wishlist_items as $item): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card product-card h-100">
                                        <div class="product-image-container">
                                            <?php if ($item['image_url']): ?>
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <?php else: ?>
                                            <div class="card-img-top product-image-placeholder d-flex align-items-center justify-content-center">
                                                <i class="fas fa-image fa-3x text-muted"></i>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Product Badges -->
                                            <div class="product-badges">
                                                <?php if ($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                                <span class="badge bg-danger">Sale</span>
                                                <?php endif; ?>
                                                <?php if ($item['stock'] <= 10 && $item['stock'] > 0): ?>
                                                <span class="badge bg-warning">Low Stock</span>
                                                <?php endif; ?>
                                                <?php if ($item['stock'] == 0): ?>
                                                <span class="badge bg-secondary">Out of Stock</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Wishlist Button -->
                                            <button class="btn btn-sm btn-danger wishlist-btn position-absolute top-0 end-0 m-2" 
                                                    onclick="removeFromWishlist(<?php echo $item['product_id']; ?>)">
                                                <i class="fas fa-heart"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>...</p>
                                            
                                            <div class="mt-auto">
                                                <div class="price-section mb-2">
                                                    <?php if ($item['sale_price'] && $item['sale_price'] < $item['price']): ?>
                                                                            <span class="text-decoration-line-through text-muted">৳<?php echo number_format($item['price']); ?></span>
                        <span class="text-danger fw-bold ms-2">৳<?php echo number_format($item['sale_price']); ?></span>
                                                    <?php else: ?>
                                                    <span class="fw-bold">৳<?php echo number_format($item['price']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="d-grid gap-2">
                                                    <?php if ($item['stock'] > 0 && $item['status'] === 'active'): ?>
                                                    <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $item['product_id']; ?>)">
                                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                                    </button>
                                                    <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm" disabled>
                                                        <i class="fas fa-times"></i> Not Available
                                                    </button>
                                                    <?php endif; ?>
                                                    
                                                    <a href="../product.php?id=<?php echo $item['product_id']; ?>" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($pagination['total_pages'] > 1): ?>
                            <nav aria-label="Wishlist pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($pagination['has_previous']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['previous_page']; ?>">Previous</a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($pagination['has_next']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>">Next</a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/cart.js"></script>

    <script>
    function removeFromWishlist(productId) {
        if (confirm('Remove this product from your wishlist?')) {
            fetch('../ajax/wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=remove&product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page to update the wishlist
                    location.reload();
                } else {
                    alert(data.message || 'Failed to remove from wishlist');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while removing from wishlist');
            });
        }
    }

    function addToCart(productId) {
        fetch('../ajax/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=add&product_id=' + productId + '&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product added to cart successfully!');
                // Update cart count in header
                updateCartCount(data.cart_count);
            } else {
                alert(data.message || 'Failed to add to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding to cart');
        });
    }

    function updateCartCount(count) {
        const cartBadge = document.querySelector('.navbar-nav .nav-link[href="cart.php"] .badge');
        if (cartBadge) {
            if (count > 0) {
                cartBadge.textContent = count;
                cartBadge.style.display = 'inline';
            } else {
                cartBadge.style.display = 'none';
            }
        }
    }
    </script>
</body>
</html>
