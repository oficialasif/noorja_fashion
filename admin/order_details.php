<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    exit('Access denied');
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$order_id) {
    http_response_code(400);
    exit('Invalid order ID');
}

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    exit('Order not found');
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.image_url 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-md-6">
        <h6>Order Information</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Order #:</strong></td>
                <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
            </tr>
            <tr>
                <td><strong>Date:</strong></td>
                <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>
                    <span class="badge bg-<?php echo getOrderStatusBadge($order['order_status']); ?>">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Payment Method:</strong></td>
                <td><?php echo ucfirst($order['payment_method']); ?></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6>Customer Information</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Name:</strong></td>
                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td><?php echo htmlspecialchars($order['user_email']); ?></td>
            </tr>
            <tr>
                <td><strong>Phone:</strong></td>
                <td><?php echo htmlspecialchars($order['user_phone']); ?></td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <h6>Shipping Address</h6>
        <div class="border rounded p-3">
            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
        </div>
    </div>
    <div class="col-md-6">
        <h6>Billing Address</h6>
        <div class="border rounded p-3">
            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <h6>Order Items</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if ($item['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="img-thumbnail me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                </div>
                            </div>
                        </td>
                                                        <td>৳<?php echo number_format($item['price']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>৳<?php echo number_format($item['price'] * $item['quantity']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6 offset-md-6">
        <h6>Order Summary</h6>
        <table class="table table-sm">
            <tr class="table-active">
                <td><strong>Total:</strong></td>
                <td class="text-end"><strong>৳<?php echo number_format($order['total_amount']); ?></strong></td>
            </tr>
        </table>
    </div>
</div>

<?php if (!empty($order['notes'])): ?>
<div class="row mt-3">
    <div class="col-12">
        <h6>Order Notes</h6>
        <div class="border rounded p-3">
            <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
        </div>
    </div>
</div>
<?php endif; ?>
