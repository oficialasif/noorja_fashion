<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Please login to access your dashboard');
    redirect('../auth.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Redirect admin users to admin dashboard
if (isAdmin()) {
    redirect('../admin/index.php');
}

$user_id = $_SESSION['user_id'];
$user = getUserById($conn, $user_id);

// Get user's recent orders
$recent_orders = getUserOrders($conn, $user_id, 5);

// Get cart items count
$cart_items = getCartItems($conn, $user_id);
$cart_count = count($cart_items);

// Get wishlist items count
$wishlist_items = getWishlistItems($conn, $user_id);
$wishlist_count = count($wishlist_items);

// Get order statistics
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_orders,
    SUM(total_amount) as total_spent,
    COUNT(CASE WHEN order_status = 'pending' THEN 1 END) as pending_orders,
    COUNT(CASE WHEN order_status = 'delivered' THEN 1 END) as delivered_orders
    FROM orders WHERE user_id = ?");
$stmt->execute([$user_id]);
$order_stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NOORJA</title>
    <meta name="description" content="Manage your account, orders, and preferences at NOORJA.">
    
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

    <!-- Dashboard Section -->
    <section class="dashboard-section py-5">
        <div class="container">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="page-title">Dashboard</h1>
                    <p class="text-muted">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
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
                                            <a class="nav-link active" href="dashboard.php">
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
                                            <a class="nav-link" href="wishlist.php">
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
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="stat-icon mb-2">
                                            <i class="fas fa-shopping-bag fa-2x text-primary"></i>
                                        </div>
                                        <h4 class="stat-number"><?php echo $order_stats['total_orders']; ?></h4>
                                        <p class="stat-label">Total Orders</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="stat-icon mb-2">
                                            <i class="fas fa-rupee-sign fa-2x text-success"></i>
                                        </div>
                                        <h4 class="stat-number">৳<?php echo number_format($order_stats['total_spent'] ?? 0); ?></h4>
                                        <p class="stat-label">Total Spent</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="stat-icon mb-2">
                                            <i class="fas fa-clock fa-2x text-warning"></i>
                                        </div>
                                        <h4 class="stat-number"><?php echo $order_stats['pending_orders']; ?></h4>
                                        <p class="stat-label">Pending Orders</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <div class="stat-icon mb-2">
                                            <i class="fas fa-heart fa-2x text-danger"></i>
                                        </div>
                                        <h4 class="stat-number"><?php echo $wishlist_count; ?></h4>
                                        <p class="stat-label">Wishlist Items</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <a href="../shop.php" class="btn btn-outline-primary w-100">
                                                <i class="fas fa-shopping-bag"></i> Shop Now
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="orders.php" class="btn btn-outline-success w-100">
                                                <i class="fas fa-list"></i> View Orders
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="profile.php" class="btn btn-outline-info w-100">
                                                <i class="fas fa-edit"></i> Edit Profile
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="../contact.php" class="btn btn-outline-secondary w-100">
                                                <i class="fas fa-headset"></i> Get Help
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Recent Orders</h5>
                                    <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recent_orders)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                        <h5>No Orders Yet</h5>
                                        <p class="text-muted">Start shopping to see your orders here</p>
                                        <a href="../shop.php" class="btn btn-primary">Start Shopping</a>
                                    </div>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Order #</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_orders as $order): ?>
                                                <tr>
                                                    <td>#<?php echo $order['order_number']; ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                    <td>৳<?php echo number_format($order['total_amount']); ?></td>
                                                                                                         <td>
                                                         <span class="badge bg-<?php echo getOrderStatusBadge($order['order_status']); ?>">
                                                             <?php echo ucfirst($order['order_status']); ?>
                                                         </span>
                                                     </td>
                                                    <td>
                                                        <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            View Details
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
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
</body>
</html>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>
