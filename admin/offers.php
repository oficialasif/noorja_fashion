<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('../auth.php');
}

// Handle offer/banner actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_offer':
                $description = sanitizeInput($_POST['description']);
                $discount_value = floatval($_POST['discount_percent']);
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                $status = $_POST['status'];
                
                $stmt = $conn->prepare("INSERT INTO coupons (code, description, discount_value, valid_from, valid_until, status) VALUES (?, ?, ?, ?, ?, ?)");
                $code = 'OFFER' . strtoupper(substr(md5(uniqid()), 0, 8));
                if ($stmt->execute([$code, $description, $discount_value, $start_date, $end_date, $status])) {
                    setFlashMessage('success', 'Offer added successfully');
                } else {
                    setFlashMessage('error', 'Failed to add offer');
                }
                break;
                
            case 'update_offer':
                $id = intval($_POST['id']);
                $description = sanitizeInput($_POST['description']);
                $discount_value = floatval($_POST['discount_percent']);
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                $status = $_POST['status'];
                
                $stmt = $conn->prepare("UPDATE coupons SET description = ?, discount_value = ?, valid_from = ?, valid_until = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$description, $discount_value, $start_date, $end_date, $status, $id])) {
                    setFlashMessage('success', 'Offer updated successfully');
                } else {
                    setFlashMessage('error', 'Failed to update offer');
                }
                break;
                
            case 'delete_offer':
                $id = intval($_POST['id']);
                $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
                if ($stmt->execute([$id])) {
                    setFlashMessage('success', 'Offer deleted successfully');
                } else {
                    setFlashMessage('error', 'Failed to delete offer');
                }
                break;
        }
        redirect('offers.php');
    }
}

// Get offers with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

$where_conditions = ["1=1"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(code LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM coupons WHERE $where_clause");
$count_stmt->execute($params);
$total_offers = $count_stmt->fetchColumn();

$pagination = getPaginationInfo($total_offers, $page, 10);

// Get offers
$stmt = $conn->prepare("SELECT * FROM coupons WHERE $where_clause ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $pagination['items_per_page'], PDO::PARAM_INT);
$stmt->bindParam(':offset', $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Offers & Banners';
include '../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Offers & Banners</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOfferModal">
        <i class="fas fa-plus"></i> Add Offer
    </button>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <input type="text" class="form-control" name="search" placeholder="Search offers..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="offers.php" class="btn btn-outline-secondary w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Offers Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Discount</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($offers as $offer): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($offer['code']); ?></strong>
                        </td>
                        <td>
                            <?php echo htmlspecialchars(substr($offer['description'], 0, 100)); ?>
                            <?php if (strlen($offer['description']) > 100): ?>
                            ...
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-success"><?php echo $offer['discount_value']; ?>% OFF</span>
                        </td>
                        <td>
                            <small>
                                <?php echo date('M d, Y', strtotime($offer['valid_from'])); ?> - 
                                <?php echo date('M d, Y', strtotime($offer['valid_until'])); ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-<?php echo $offer['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($offer['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editOffer(<?php echo htmlspecialchars(json_encode($offer)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteOffer(<?php echo $offer['id']; ?>)">
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
        <nav aria-label="Offers pagination">
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

<!-- Add Offer Modal -->
<div class="modal fade" id="addOfferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_offer">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Offer Code</label>
                        <input type="text" class="form-control" value="Auto-generated" disabled>
                        <small class="text-muted">Code will be automatically generated</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Percentage</label>
                        <input type="number" class="form-control" name="discount_percent" min="1" max="100" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>
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
                    <button type="submit" class="btn btn-primary">Add Offer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Offer Modal -->
<div class="modal fade" id="editOfferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_offer">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Offer Code</label>
                        <input type="text" class="form-control" id="edit_code" disabled>
                        <small class="text-muted">Code cannot be changed</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Percentage</label>
                        <input type="number" class="form-control" name="discount_percent" id="edit_discount_percent" min="1" max="100" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" id="edit_start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" id="edit_end_date" required>
                            </div>
                        </div>
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
                    <button type="submit" class="btn btn-primary">Update Offer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteOfferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Offer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this offer? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="action" value="delete_offer">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editOffer(offer) {
    document.getElementById('edit_id').value = offer.id;
    document.getElementById('edit_code').value = offer.code;
    document.getElementById('edit_description').value = offer.description || '';
    document.getElementById('edit_discount_percent').value = offer.discount_value;
    document.getElementById('edit_start_date').value = offer.valid_from;
    document.getElementById('edit_end_date').value = offer.valid_until;
    document.getElementById('edit_status').value = offer.status;
    
    new bootstrap.Modal(document.getElementById('editOfferModal')).show();
}

function deleteOffer(id) {
    document.getElementById('delete_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteOfferModal')).show();
}
</script>

<?php include '../includes/admin_footer.php'; ?>
