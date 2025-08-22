<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('warning', 'Please login to access your orders');
    redirect('../auth.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
}

// Redirect admin users to admin dashboard
if (isAdmin()) {
    redirect('../admin/index.php');
}

$user_id = $_SESSION['user_id'];
$user = getUserById($conn, $user_id);

// Get orders with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where_conditions = ["user_id = ?"];
$params = [$user_id];

if (!empty($status_filter)) {
    $where_conditions[] = "order_status = ?";
    $params[] = $status_filter;
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE $where_clause");
$count_stmt->execute($params);
$total_orders = $count_stmt->fetchColumn();

$pagination = getPaginationInfo($total_orders, $page, 10);

// Get orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindParam(1, $pagination['items_per_page'], PDO::PARAM_INT);
$stmt->bindParam(2, $pagination['offset'], PDO::PARAM_INT);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get cart items count
$cart_items = getCartItems($conn, $user_id);
$cart_count = count($cart_items);

// Get wishlist items count
$wishlist_items = getWishlistItems($conn, $user_id);
$wishlist_count = count($wishlist_items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - NOORJA</title>
    <meta name="description" content="View your order history and track your purchases at NOORJA.">
    
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

    <!-- Orders Section -->
    <section class="orders-section py-5">
        <div class="container">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="page-title">My Orders</h1>
                    <p class="text-muted">Track your orders and view order history</p>
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
                                            <a class="nav-link active" href="orders.php">
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
                    <!-- Filter -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <select class="form-select" name="status">
                                        <option value="">All Orders</option>
                                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="shipped" <?php echo $status_filter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="delivered" <?php echo $status_filter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="orders.php" class="btn btn-outline-secondary w-100">Clear</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Orders List -->
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($orders)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                                <h5>No Orders Found</h5>
                                <p class="text-muted">
                                    <?php if (!empty($status_filter)): ?>
                                    No orders found with status "<?php echo ucfirst($status_filter); ?>"
                                    <?php else: ?>
                                    You haven't placed any orders yet
                                    <?php endif; ?>
                                </p>
                                <a href="../shop.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                                <br><small class="text-muted"><?php echo date('H:i', strtotime($order['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                // Get order items count
                                                $items_stmt = $conn->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
                                                $items_stmt->execute([$order['id']]);
                                                $items_count = $items_stmt->fetchColumn();
                                                ?>
                                                <span class="badge bg-info"><?php echo $items_count; ?> items</span>
                                            </td>
                                            <td>
                                                <strong>৳<?php echo number_format($order['total_amount']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo getOrderStatusBadge($order['order_status']); ?>">
                                                    <?php echo ucfirst($order['order_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($pagination['total_pages'] > 1): ?>
                            <nav aria-label="Orders pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($pagination['has_previous']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['previous_page']; ?>&status=<?php echo urlencode($status_filter); ?>">Previous</a>
                                    </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                                    </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($pagination['has_next']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&status=<?php echo urlencode($status_filter); ?>">Next</a>
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

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>

    <script>
    function viewOrderDetails(orderId) {
        // Load order details via AJAX
        fetch(`order-details.php?id=${orderId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('orderDetailsContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
            })
            .catch(error => {
                console.error('Error loading order details:', error);
                alert('Error loading order details');
            });
    }
    </script>
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
