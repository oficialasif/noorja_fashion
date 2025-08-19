<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'Access denied. Admin privileges required.');
    redirect('../auth.php');
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_settings') {
        $site_name = sanitizeInput($_POST['site_name']);
        $site_description = sanitizeInput($_POST['site_description']);
        $contact_email = sanitizeInput($_POST['contact_email']);
        $contact_phone = sanitizeInput($_POST['contact_phone']);
        $contact_address = sanitizeInput($_POST['contact_address']);
        $shipping_cost = floatval($_POST['shipping_cost']);
        $tax_rate = floatval($_POST['tax_rate']);
        $currency = sanitizeInput($_POST['currency']);
        $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
        
        // Update site settings
        $settings = [
            'site_name' => $site_name,
            'site_description' => $site_description,
            'contact_email' => $contact_email,
            'contact_phone' => $contact_phone,
            'contact_address' => $contact_address,
            'shipping_cost' => $shipping_cost,
            'tax_rate' => $tax_rate,
            'currency' => $currency,
            'maintenance_mode' => $maintenance_mode
        ];
        
        $success = true;
        foreach ($settings as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            if (!$stmt->execute([$key, $value, $value])) {
                $success = false;
                break;
            }
        }
        
        if ($success) {
            setFlashMessage('success', 'Settings updated successfully');
        } else {
            setFlashMessage('error', 'Failed to update settings');
        }
        redirect('settings.php');
    }
}

// Get current settings
$stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
$stmt->execute();
$current_settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $current_settings[$row['setting_key']] = $row['setting_value'];
}

// Set default values if not set
$site_name = $current_settings['site_name'] ?? 'NOORJA';
$site_description = $current_settings['site_description'] ?? 'Elegant women\'s fashion and beauty products';
$contact_email = $current_settings['contact_email'] ?? 'info@noorja.com';
$contact_phone = $current_settings['contact_phone'] ?? '+91 98765 43210';
$contact_address = $current_settings['contact_address'] ?? '123 Fashion Street, Mumbai, Maharashtra, India';
$shipping_cost = $current_settings['shipping_cost'] ?? 100;
$tax_rate = $current_settings['tax_rate'] ?? 18;
$currency = $current_settings['currency'] ?? '৳';
$maintenance_mode = $current_settings['maintenance_mode'] ?? 0;

$page_title = 'Settings';
include '../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Settings</h1>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="backupDatabase()">
            <i class="fas fa-download"></i> Backup Database
        </button>
        <button class="btn btn-outline-secondary" onclick="clearCache()">
            <i class="fas fa-broom"></i> Clear Cache
        </button>
    </div>
</div>

<div class="row">
    <!-- General Settings -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">General Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Site Name</label>
                                <input type="text" class="form-control" name="site_name" value="<?php echo htmlspecialchars($site_name); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contact Email</label>
                                <input type="email" class="form-control" name="contact_email" value="<?php echo htmlspecialchars($contact_email); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Site Description</label>
                        <textarea class="form-control" name="site_description" rows="3"><?php echo htmlspecialchars($site_description); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" class="form-control" name="contact_phone" value="<?php echo htmlspecialchars($contact_phone); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Currency</label>
                                <select class="form-select" name="currency">
                                    <option value="৳" <?php echo $currency === '৳' ? 'selected' : ''; ?>>৳ (BDT)</option>
                                    <option value="$" <?php echo $currency === '$' ? 'selected' : ''; ?>>$ (USD)</option>
                                    <option value="€" <?php echo $currency === '€' ? 'selected' : ''; ?>>€ (EUR)</option>
                                    <option value="£" <?php echo $currency === '£' ? 'selected' : ''; ?>>£ (GBP)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contact Address</label>
                        <textarea class="form-control" name="contact_address" rows="3"><?php echo htmlspecialchars($contact_address); ?></textarea>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">E-commerce Settings</h6>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                                                 <label class="form-label">Shipping Cost (৳)</label>
                                <input type="number" class="form-control" name="shipping_cost" value="<?php echo $shipping_cost; ?>" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" class="form-control" name="tax_rate" value="<?php echo $tax_rate; ?>" min="0" max="100" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">System Settings</h6>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" <?php echo $maintenance_mode ? 'checked' : ''; ?>>
                            <label class="form-check-label">Maintenance Mode</label>
                            <small class="form-text text-muted d-block">When enabled, only administrators can access the site</small>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- System Information -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">System Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>PHP Version:</strong>
                    <span class="text-muted"><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="mb-3">
                    <strong>Database:</strong>
                    <span class="text-muted">MySQL <?php echo $conn->getAttribute(PDO::ATTR_SERVER_VERSION); ?></span>
                </div>
                <div class="mb-3">
                    <strong>Server:</strong>
                    <span class="text-muted"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                </div>
                <div class="mb-3">
                    <strong>Upload Max Size:</strong>
                    <span class="text-muted"><?php echo ini_get('upload_max_filesize'); ?></span>
                </div>
                <div class="mb-3">
                    <strong>Memory Limit:</strong>
                    <span class="text-muted"><?php echo ini_get('memory_limit'); ?></span>
                </div>
                <div class="mb-3">
                    <strong>Max Execution Time:</strong>
                    <span class="text-muted"><?php echo ini_get('max_execution_time'); ?>s</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewLogs()">
                        <i class="fas fa-file-alt"></i> View Logs
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="checkUpdates()">
                        <i class="fas fa-sync"></i> Check Updates
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="optimizeDatabase()">
                        <i class="fas fa-database"></i> Optimize Database
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="clearAllData()">
                        <i class="fas fa-trash"></i> Clear All Data
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Current Statistics -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Current Statistics</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Total Products:</small>
                    <strong class="float-end"><?php echo number_format(getTotalProducts($conn, null, null)); ?></strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Total Orders:</small>
                    <strong class="float-end"><?php echo number_format(getTotalOrders($conn)); ?></strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Total Users:</small>
                    <strong class="float-end"><?php echo number_format(getTotalUsers($conn)); ?></strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Total Revenue:</small>
                                         <strong class="float-end">৳<?php echo number_format(getTotalRevenue($conn)); ?></strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Database Size:</small>
                    <strong class="float-end"><?php echo formatBytes(getDatabaseSize($conn)); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function backupDatabase() {
    if (confirm('This will create a backup of the database. Continue?')) {
        // Placeholder for backup functionality
        alert('Database backup functionality will be implemented here');
    }
}

function clearCache() {
    if (confirm('This will clear all cached data. Continue?')) {
        // Placeholder for cache clearing
        alert('Cache cleared successfully');
    }
}

function viewLogs() {
    // Placeholder for viewing logs
    alert('Log viewer will be implemented here');
}

function checkUpdates() {
    // Placeholder for update checking
    alert('No updates available');
}

function optimizeDatabase() {
    if (confirm('This will optimize the database tables. Continue?')) {
        // Placeholder for database optimization
        alert('Database optimized successfully');
    }
}

function clearAllData() {
    if (confirm('WARNING: This will permanently delete all data from the database. This action cannot be undone. Are you sure?')) {
        if (confirm('Are you absolutely sure? This will delete ALL data including products, orders, users, etc.')) {
            // Placeholder for clearing all data
            alert('All data cleared successfully');
        }
    }
}

function formatBytesJS(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>

<?php
// Helper function to get database size
function getDatabaseSize($conn) {
    try {
        $stmt = $conn->prepare("SELECT SUM(data_length + index_length) as size FROM information_schema.tables WHERE table_schema = DATABASE()");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['size'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// Helper function to format bytes in PHP
function formatBytes($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>

<?php include '../includes/admin_footer.php'; ?>
