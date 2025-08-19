<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('../auth.php');
}

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $price = floatval($_POST['price']);
                $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
                $category_id = intval($_POST['category_id']);
                $stock = intval($_POST['stock']);
                $image_url = sanitizeInput($_POST['image_url']);
                $status = $_POST['status'];
                $featured = isset($_POST['featured']) ? 1 : 0;
                
                // Determine badge based on checkboxes
                $badge = null;
                if (isset($_POST['flash_sale'])) {
                    $badge = 'Flash Sale';
                } elseif (isset($_POST['trending'])) {
                    $badge = 'Trending';
                }
                
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, sale_price, category_id, stock, image_url, status, featured, badge) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $description, $price, $sale_price, $category_id, $stock, $image_url, $status, $featured, $badge])) {
                    setFlashMessage('success', 'Product added successfully');
                } else {
                    setFlashMessage('error', 'Failed to add product');
                }
                break;
                
            case 'update':
                $id = intval($_POST['id']);
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $price = floatval($_POST['price']);
                $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
                $category_id = intval($_POST['category_id']);
                $stock = intval($_POST['stock']);
                $image_url = sanitizeInput($_POST['image_url']);
                $status = $_POST['status'];
                $featured = isset($_POST['featured']) ? 1 : 0;
                
                // Determine badge based on checkboxes
                $badge = null;
                if (isset($_POST['flash_sale'])) {
                    $badge = 'Flash Sale';
                } elseif (isset($_POST['trending'])) {
                    $badge = 'Trending';
                }
                
                $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, sale_price = ?, category_id = ?, stock = ?, image_url = ?, status = ?, featured = ?, badge = ? WHERE id = ?");
                if ($stmt->execute([$name, $description, $price, $sale_price, $category_id, $stock, $image_url, $status, $featured, $badge, $id])) {
                    setFlashMessage('success', 'Product updated successfully');
                } else {
                    setFlashMessage('error', 'Failed to update product');
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                if ($stmt->execute([$id])) {
                    setFlashMessage('success', 'Product deleted successfully');
                } else {
                    setFlashMessage('error', 'Failed to delete product');
                }
                break;
        }
        redirect('products.php');
    }
}

// Get products with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : '';

$where_conditions = ["1=1"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "p.name LIKE ?";
    $params[] = "%$search%";
}

if (!empty($category_filter)) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM products p WHERE $where_clause");
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();

$pagination = getPaginationInfo($total_products, $page, 10);

// Get products
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE $where_clause 
                       ORDER BY p.created_at DESC 
                       LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $pagination['items_per_page'], PDO::PARAM_INT);
$stmt->bindParam(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categories = getCategories($conn);

$page_title = 'Manage Products';
include '../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Products</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="fas fa-plus"></i> Add Product
    </button>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="products.php" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Badges</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if ($product['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                            <br><small class="text-muted"><?php echo substr(htmlspecialchars($product['description']), 0, 50); ?>...</small>
                        </td>
                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                        <td>
                                                            ৳<?php echo number_format($product['price']); ?>
                            <?php if ($product['sale_price']): ?>
                                                          <br><small class="text-danger">Sale: ৳<?php echo number_format($product['sale_price']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $product['stock'] <= 10 ? 'danger' : ($product['stock'] <= 50 ? 'warning' : 'success'); ?>">
                                <?php echo $product['stock']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $product['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($product['featured']): ?>
                            <span class="badge bg-primary me-1">Featured</span>
                            <?php endif; ?>
                            <?php if ($product['badge'] === 'Trending'): ?>
                            <span class="badge bg-warning me-1">Trending</span>
                            <?php endif; ?>
                            <?php if ($product['badge'] === 'Flash Sale'): ?>
                            <span class="badge bg-danger me-1">Flash Sale</span>
                            <?php endif; ?>
                            <?php if ($product['badge'] === 'New'): ?>
                            <span class="badge bg-success me-1">New</span>
                            <?php endif; ?>
                            <?php if ($product['badge'] === 'Offer'): ?>
                            <span class="badge bg-info">Offer</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <nav aria-label="Products pagination">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['has_previous']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['previous_page']; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>">Previous</a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagination['has_next']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Price (৳)</label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Sale Price (৳)</label>
                                <input type="number" class="form-control" name="sale_price" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Stock</label>
                                <input type="number" class="form-control" name="stock" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="url" class="form-control" name="image_url" placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Badges</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="featured" value="1">
                                    <label class="form-check-label">Featured</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="trending" value="1">
                                    <label class="form-check-label">Trending</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="flash_sale" value="1">
                                    <label class="form-check-label">Flash Sale</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id" id="edit_category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Price (৳)</label>
                                <input type="number" class="form-control" name="price" id="edit_price" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Sale Price (৳)</label>
                                <input type="number" class="form-control" name="sale_price" id="edit_sale_price" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Stock</label>
                                <input type="number" class="form-control" name="stock" id="edit_stock" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="url" class="form-control" name="image_url" id="edit_image_url" placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="edit_status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Badges</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="featured" id="edit_featured" value="1">
                                    <label class="form-check-label">Featured</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="trending" id="edit_trending" value="1">
                                    <label class="form-check-label">Trending</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="flash_sale" id="edit_flash_sale" value="1">
                                    <label class="form-check-label">Flash Sale</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('edit_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_description').value = product.description;
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_sale_price').value = product.sale_price || '';
    document.getElementById('edit_category_id').value = product.category_id;
    document.getElementById('edit_stock').value = product.stock;
    document.getElementById('edit_image_url').value = product.image_url || '';
    document.getElementById('edit_status').value = product.status;
    document.getElementById('edit_featured').checked = product.featured == 1;
    document.getElementById('edit_trending').checked = product.badge === 'Trending';
    document.getElementById('edit_flash_sale').checked = product.badge === 'Flash Sale';
    
    new bootstrap.Modal(document.getElementById('editProductModal')).show();
}

function deleteProduct(id) {
    document.getElementById('delete_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteProductModal')).show();
}
</script>

<?php include '../includes/admin_footer.php'; ?>
