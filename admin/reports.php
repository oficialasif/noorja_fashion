<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('../auth.php');
}

// Get date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // First day of current month
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); // Today

// Get overall statistics
$total_orders = getTotalOrders($conn);
$total_revenue = getTotalRevenue($conn);
$total_users = getTotalUsers($conn);
$total_products = getTotalProducts($conn, null, null);

// Get period-specific statistics
$stmt = $conn->prepare("SELECT COUNT(*) as orders, COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE DATE(created_at) BETWEEN ? AND ?");
$stmt->execute([$start_date, $end_date]);
$period_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get top-selling products
$stmt = $conn->prepare("SELECT p.name, p.price, COUNT(oi.id) as sold_count, SUM(oi.quantity) as total_quantity 
                       FROM products p 
                       LEFT JOIN order_items oi ON p.id = oi.product_id 
                       LEFT JOIN orders o ON oi.order_id = o.id 
                       WHERE o.order_status != 'cancelled' OR o.order_status IS NULL 
                       GROUP BY p.id 
                       ORDER BY total_quantity DESC 
                       LIMIT 10");
$stmt->execute();
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get order status distribution
$stmt = $conn->prepare("SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status");
$stmt->execute();
$order_statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get monthly revenue for chart
$stmt = $conn->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as revenue 
                       FROM orders 
                       WHERE order_status != 'cancelled' 
                       GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                       ORDER BY month DESC 
                       LIMIT 12");
$stmt->execute();
$monthly_revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Reports & Analytics';
include '../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Reports & Analytics</h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="exportReport()">
            <i class="fas fa-download"></i> Export Report
        </button>
        <button class="btn btn-outline-secondary" onclick="printReport()">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

<!-- Date Range Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <a href="reports.php" class="btn btn-outline-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Overall Statistics -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_orders); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Revenue</div>
                                                 <div class="h5 mb-0 font-weight-bold text-gray-800">৳<?php echo number_format($total_revenue); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_users); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_products); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Period Statistics -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Period Statistics (<?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d', strtotime($end_date)); ?>)</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary"><?php echo number_format($period_stats['orders']); ?></h4>
                            <p class="text-muted">Orders</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                                                         <h4 class="text-success">৳<?php echo number_format($period_stats['revenue']); ?></h4>
                            <p class="text-muted">Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Order Status Distribution</h6>
            </div>
            <div class="card-body">
                <canvas id="orderStatusChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Monthly Revenue Trend</h6>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Top Selling Products</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($top_products, 0, 5) as $product): ?>
                            <tr>
                                <td>
                                    <small><?php echo htmlspecialchars(substr($product['name'], 0, 20)); ?>...</small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $product['total_quantity'] ?: 0; ?></span>
                                </td>
                                <td>
                                    <small>৳<?php echo number_format(($product['total_quantity'] ?: 0) * $product['price']); ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Reports -->
<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
            </div>
            <div class="card-body">
                <?php
                $recent_orders = getRecentOrders($conn, 5);
                if (!empty($recent_orders)):
                ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>
                                    <small>#<?php echo htmlspecialchars($order['order_number']); ?></small>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($order['user_name']); ?></small>
                                </td>
                                <td>
                                    <small>৳<?php echo number_format($order['total_amount']); ?></small>
                                </td>
                                <td>
                                                                    <span class="badge bg-<?php echo getOrderStatusBadge($order['order_status']); ?>">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">No recent orders</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Low Stock Products</h6>
            </div>
            <div class="card-body">
                <?php
                $low_stock_products = getLowStockProducts($conn, 5);
                if (!empty($low_stock_products)):
                ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock_products as $product): ?>
                            <tr>
                                <td>
                                    <small><?php echo htmlspecialchars(substr($product['name'], 0, 25)); ?>...</small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $product['stock'] <= 10 ? 'danger' : 'warning'; ?>">
                                        <?php echo $product['stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?php echo $product['stock'] <= 10 ? 'Critical' : 'Low'; ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center">All products have sufficient stock</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Order Status Chart
const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
const orderStatusData = <?php echo json_encode($order_statuses); ?>;

new Chart(orderStatusCtx, {
    type: 'doughnut',
    data: {
        labels: orderStatusData.map(item => item.order_status.charAt(0).toUpperCase() + item.order_status.slice(1)),
        datasets: [{
            data: orderStatusData.map(item => item.count),
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e',
                '#e74a3b'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueData = <?php echo json_encode(array_reverse($monthly_revenue)); ?>;

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        }),
        datasets: [{
                         label: 'Revenue (৳)',
            data: revenueData.map(item => item.revenue),
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                                                 return '৳' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

function exportReport() {
    // Placeholder for export functionality
    alert('Export functionality will be implemented here');
}

function printReport() {
    window.print();
}
</script>

<?php include '../includes/admin_footer.php'; ?>
