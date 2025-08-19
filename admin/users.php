<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('../auth.php');
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $user_id = intval($_POST['user_id']);
                $status = $_POST['status'];
                
                $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
                if ($stmt->execute([$status, $user_id])) {
                    setFlashMessage('success', 'User status updated successfully');
                } else {
                    setFlashMessage('error', 'Failed to update user status');
                }
                break;
                
            case 'update_role':
                $user_id = intval($_POST['user_id']);
                $role = $_POST['role'];
                
                // Prevent admin from changing their own role
                if ($user_id == $_SESSION['user_id']) {
                    setFlashMessage('error', 'You cannot change your own role');
                } else {
                    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                    if ($stmt->execute([$role, $user_id])) {
                        setFlashMessage('success', 'User role updated successfully');
                    } else {
                        setFlashMessage('error', 'Failed to update user role');
                    }
                }
                break;
                
            case 'delete':
                $user_id = intval($_POST['user_id']);
                
                // Prevent admin from deleting themselves
                if ($user_id == $_SESSION['user_id']) {
                    setFlashMessage('error', 'You cannot delete your own account');
                } else {
                    // Check if user has orders
                    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
                    $check_stmt->execute([$user_id]);
                    $order_count = $check_stmt->fetchColumn();
                    
                    if ($order_count > 0) {
                        setFlashMessage('error', 'Cannot delete user. They have ' . $order_count . ' orders associated with their account.');
                    } else {
                        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                        if ($stmt->execute([$user_id])) {
                            setFlashMessage('success', 'User deleted successfully');
                        } else {
                            setFlashMessage('error', 'Failed to delete user');
                        }
                    }
                }
                break;
        }
        redirect('users.php');
    }
}

// Get users with pagination and filters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$where_conditions = ["1=1"];
$params = [];

if (!empty($role_filter)) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
}

if (!empty($status_filter)) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE $where_clause");
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();

$pagination = getPaginationInfo($total_users, $page, 15);

// Get users
$stmt = $conn->prepare("SELECT u.*, 
                       (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
                       (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = u.id) as total_spent
                       FROM users u 
                       WHERE $where_clause 
                       ORDER BY u.created_at DESC 
                       LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $pagination['items_per_page'], PDO::PARAM_INT);
$stmt->bindParam(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Manage Users';
include '../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Manage Users</h1>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="role">
                    <option value="">All Roles</option>
                    <option value="user" <?php echo $role_filter === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="users.php" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Orders</th>
                        <th>Total Spent</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                <span class="badge bg-primary ms-1">You</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div>
                                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                <?php if ($user['phone']): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'info'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo $user['order_count']; ?> orders</span>
                        </td>
                        <td>
                            <strong>৳<?php echo number_format($user['total_spent']); ?></strong>
                        </td>
                        <td>
                            <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary" onclick="updateUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')">
                                    <i class="fas fa-toggle-on"></i>
                                </button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-sm btn-outline-warning" onclick="updateUserRole(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')">
                                    <i class="fas fa-user-tag"></i>
                                </button>
                                <?php if ($user['order_count'] == 0): ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete user with orders">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <nav aria-label="Users pagination">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['has_previous']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['previous_page']; ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagination['has_next']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $pagination['next_page']; ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>&search=<?php echo urlencode($search); ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update User Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="user_id" id="status_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">User Status</label>
                        <select class="form-select" name="status" id="status_select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
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

<!-- Update Role Modal -->
<div class="modal fade" id="updateRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update User Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_role">
                <input type="hidden" name="user_id" id="role_user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">User Role</label>
                        <select class="form-select" name="role" id="role_select" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateUserStatus(userId, currentStatus) {
    document.getElementById('status_user_id').value = userId;
    document.getElementById('status_select').value = currentStatus;
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}

function updateUserRole(userId, currentRole) {
    document.getElementById('role_user_id').value = userId;
    document.getElementById('role_select').value = currentRole;
    new bootstrap.Modal(document.getElementById('updateRoleModal')).show();
}

function deleteUser(userId) {
    document.getElementById('delete_user_id').value = userId;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}
</script>

<?php include '../includes/admin_footer.php'; ?>
