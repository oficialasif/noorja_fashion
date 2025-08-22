<?php
global $conn;
$cart_count = 0;
if (isLoggedIn()) {
    $cart_items = getCartItems($conn, $_SESSION['user_id']);
    $cart_count = count($cart_items);
}

// Get site settings
$site_name = getSetting($conn, 'site_name', 'NOORJA');

// Determine the current context (public, user, or admin)
$current_path = $_SERVER['REQUEST_URI'] ?? '';
$is_user_area = strpos($current_path, '/user/') !== false;
$is_admin_area = strpos($current_path, '/admin/') !== false;

// Set base paths based on context
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
?>
<header class="header">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="<?php echo $base_path; ?>index.php">
                <h1 class="brand-name"><?php echo htmlspecialchars($site_name); ?></h1>
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Shop
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo $base_path; ?>shop.php">All Products</a></li>
                            <?php foreach (getCategories($conn) as $category): ?>
                            <li><a class="dropdown-item" href="<?php echo $base_path; ?>shop.php?category=<?php echo $category['id']; ?>"><?php echo $category['name']; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>offers.php">Offers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>contact.php">Contact</a>
                    </li>
                </ul>

                <!-- Search Bar -->
                <form class="d-flex me-3" action="<?php echo $base_path; ?>shop.php" method="GET">
                    <div class="input-group">
                        <input class="form-control" type="search" name="search" placeholder="Search products..." aria-label="Search">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                <!-- User Menu -->
                <ul class="navbar-nav">
                    <!-- Cart -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?php echo $base_path; ?>cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                <?php echo $cart_count; ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <?php if (isLoggedIn()): ?>
                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (isAdmin()): ?>
                            <li><a class="dropdown-item" href="<?php echo $admin_base; ?>index.php">Admin Dashboard</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?php echo $user_base; ?>dashboard.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo $user_base; ?>profile.php">My Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo $user_base; ?>orders.php">My Orders</a></li>
                            <li><a class="dropdown-item" href="<?php echo $user_base; ?>wishlist.php">Wishlist</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo $base_path; ?>auth.php?action=logout">Logout</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <!-- Login/Register -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>auth.php">Login / Register</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php $flash_message = getFlashMessage(); ?>
    <?php if ($flash_message): ?>
    <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show m-3" role="alert">
        <?php echo $flash_message['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
</header>
