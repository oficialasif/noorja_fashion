<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('../auth.php');
}

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $image_url = sanitizeInput($_POST['image_url']);
                $status = $_POST['status'];
                
                $stmt = $conn->prepare("INSERT INTO categories (name, description, image_url, status) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$name, $description, $image_url, $status])) {
                    setFlashMessage('success', 'Category added successfully');
                } else {
                    setFlashMessage('error', 'Failed to add category');
                }
                break;
                
            case 'update':
                $id = intval($_POST['id']);
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $image_url = sanitizeInput($_POST['image_url']);
                $status = $_POST['status'];
                
                $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, image_url = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$name, $description, $image_url, $status, $id])) {
                    setFlashMessage('success', 'Category updated successfully');
                } else {
                    setFlashMessage('error', 'Failed to update category');
                }
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                
                // Check if category has products
                $check_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                $check_stmt->execute([$id]);
                $product_count = $check_stmt->fetchColumn();
                
                if ($product_count > 0) {
                    setFlashMessage('error', 'Cannot delete category. It has ' . $product_count . ' products associated with it.');
                } else {
                    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                    if ($stmt->execute([$id])) {
                        setFlashMessage('success', 'Category deleted successfully');
                    } else {
                        setFlashMessage('error', 'Failed to delete category');
                    }
                }
                break;
        }
        redirect('categories.php');
    }
}

// Get categories with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$where_conditions = ["1=1"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "name LIKE ?";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE $where_clause");
$count_stmt->execute($params);
$total_categories = $count_stmt->fetchColumn();

$pagination = getPaginationInfo($total_categories, $page, 10);

// Get categories
$stmt = $conn->prepare("SELECT c.*, 
                       (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count 
                       FROM categories c 
                       WHERE $where_clause 
                       ORDER BY c.created_at DESC 
                       LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $pagination['items_per_page'], PDO::PARAM_INT);
$stmt->bindParam(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Manage Categories';
include '../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Categories</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus"></i> Add Category
    </button>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <input type="text" class="form-control" name="search" placeholder="Search categories..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="categories.php" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Categories Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <?php if ($category['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($category['image_url']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-folder text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                        </td>
                        <td>
                            <?php echo htmlspecialchars(substr($category['description'], 0, 100)); ?>
                            <?php if (strlen($category['description']) > 100): ?>
                            ...
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo $category['product_count']; ?> products</span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $category['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($category['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo date('M d, Y', strtotime($category['created_at'])); ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($category['product_count'] == 0): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete category with products">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <nav aria-label="Categories pagination">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['has_previous']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['previous_page']; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagination['has_next']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="url" class="form-control" name="image_url" placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image URL</label>
                        <input type="url" class="form-control" name="image_url" id="edit_image_url" placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit_status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this category? This action cannot be undone.</p>
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
function editCategory(category) {
    document.getElementById('edit_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_description').value = category.description || '';
    document.getElementById('edit_image_url').value = category.image_url || '';
    document.getElementById('edit_status').value = category.status;
    
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

function deleteCategory(id) {
    document.getElementById('delete_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteCategoryModal')).show();
}
</script>

<?php include '../includes/admin_footer.php'; ?>
