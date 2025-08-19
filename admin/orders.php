<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('../auth.php');
}

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $order_id = intval($_POST['order_id']);
        $status = $_POST['status'];
        
                        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        if ($stmt->execute([$status, $order_id])) {
            setFlashMessage('success', 'Order status updated successfully');
        } else {
            setFlashMessage('error', 'Failed to update order status');
        }
        redirect('orders.php');
    }
}

// Get orders with pagination and filters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$where_conditions = ["1=1"];
$params = [];

if (!empty($status_filter)) {
                $where_conditions[] = "o.order_status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM orders o 
                             JOIN users u ON o.user_id = u.id 
                             WHERE $where_clause");
$count_stmt->execute($params);
$total_orders = $count_stmt->fetchColumn();

$pagination = getPaginationInfo($total_orders, $page, 15);

// Get orders
$stmt = $conn->prepare("SELECT o.*, u.name as user_name, u.email as user_email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE $where_clause 
                       ORDER BY o.created_at DESC 
                       LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $pagination['items_per_page'], PDO::PARAM_INT);
$stmt->bindParam(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Manage Orders';
include '../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Orders</h1>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Search orders, customers..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
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

<!-- Orders Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                        </td>
                        <td>
                            <div>
                                <strong><?php echo htmlspecialchars($order['user_name']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($order['user_email']); ?></small>
                            </div>
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
                            <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="updateOrderStatus(<?php echo $order['id']; ?>, '<?php echo $order['order_status']; ?>')">
                                <i class="fas fa-edit"></i> Status
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
                    <a class="page-link" href="?page=<?php echo $pagination['previous_page']; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagination['has_next']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

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

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="status_order_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Order Status</label>
                        <select class="form-select" name="status" id="status_select" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    // Load order details via AJAX
    fetch(`order_details.php?id=${orderId}`)
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

function updateOrderStatus(orderId, currentStatus) {
    document.getElementById('status_order_id').value = orderId;
    document.getElementById('status_select').value = currentStatus;
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}
</script>

<?php include '../includes/admin_footer.php'; ?>
