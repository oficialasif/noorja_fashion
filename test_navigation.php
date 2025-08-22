<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h1>Navigation Test</h1>";

// Test current path detection
$current_path = $_SERVER['REQUEST_URI'] ?? '';
$is_user_area = strpos($current_path, '/user/') !== false;
$is_admin_area = strpos($current_path, '/admin/') !== false;

echo "<h2>Current Context:</h2>";
echo "<p>Current Path: " . htmlspecialchars($current_path) . "</p>";
echo "<p>Is User Area: " . ($is_user_area ? 'Yes' : 'No') . "</p>";
echo "<p>Is Admin Area: " . ($is_admin_area ? 'Yes' : 'No') . "</p>";

// Test base paths
if ($is_user_area) {
    $base_path = '../';
    $user_base = '';
    $admin_base = '../admin/';
} elseif ($is_admin_area) {
    $base_path = '../';
    $user_base = '../user/';
    $admin_base = '';
} else {
    $base_path = '';
    $user_base = 'user/';
    $admin_base = 'admin/';
}

echo "<h2>Generated Paths:</h2>";
echo "<p>Base Path: " . htmlspecialchars($base_path) . "</p>";
echo "<p>User Base: " . htmlspecialchars($user_base) . "</p>";
echo "<p>Admin Base: " . htmlspecialchars($admin_base) . "</p>";

echo "<h2>Test Links:</h2>";
echo "<ul>";
echo "<li><a href='" . $base_path . "index.php'>Home</a></li>";
echo "<li><a href='" . $base_path . "shop.php'>Shop</a></li>";
echo "<li><a href='" . $user_base . "dashboard.php'>User Dashboard</a></li>";
echo "<li><a href='" . $admin_base . "index.php'>Admin Dashboard</a></li>";
echo "<li><a href='" . $base_path . "auth.php'>Login/Register</a></li>";
echo "</ul>";

echo "<h2>User Status:</h2>";
if (isLoggedIn()) {
    echo "<p>Logged in as: " . htmlspecialchars($_SESSION['user_name'] ?? 'Unknown') . "</p>";
    echo "<p>User Role: " . htmlspecialchars($_SESSION['user_role'] ?? 'Unknown') . "</p>";
    echo "<p>Is Admin: " . (isAdmin() ? 'Yes' : 'No') . "</p>";
} else {
    echo "<p>Not logged in</p>";
}

echo "<h2>Navigation Context Test:</h2>";
echo "<p>This test shows how the header navigation would work in different contexts:</p>";

// Simulate different contexts
$contexts = [
    'public' => '',
    'user' => '/user/',
    'admin' => '/admin/'
];

foreach ($contexts as $context => $path) {
    echo "<h3>Context: $context</h3>";
    
    $is_user_area = strpos($path, '/user/') !== false;
    $is_admin_area = strpos($path, '/admin/') !== false;
    
    if ($is_user_area) {
        $base_path = '../';
        $user_base = '';
        $admin_base = '../admin/';
    } elseif ($is_admin_area) {
        $base_path = '../';
        $user_base = '../user/';
        $admin_base = '';
    } else {
        $base_path = '';
        $user_base = 'user/';
        $admin_base = 'admin/';
    }
    
    echo "<p>Base Path: $base_path</p>";
    echo "<p>User Base: $user_base</p>";
    echo "<p>Admin Base: $admin_base</p>";
    echo "<ul>";
    echo "<li>Home: <a href='$base_path" . "index.php'>$base_path" . "index.php</a></li>";
    echo "<li>Shop: <a href='$base_path" . "shop.php'>$base_path" . "shop.php</a></li>";
    echo "<li>User Dashboard: <a href='$user_base" . "dashboard.php'>$user_base" . "dashboard.php</a></li>";
    echo "<li>Admin Dashboard: <a href='$admin_base" . "index.php'>$admin_base" . "index.php</a></li>";
    echo "</ul>";
}
?>
