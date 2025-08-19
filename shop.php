<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : null;
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get products
$products = getProducts($conn, $category_id, $search, $sort, $page);

// Get total products count for pagination
$total_products = getTotalProducts($conn, $category_id, $search);
$pagination_info = getPaginationInfo($total_products, $page);

// Get categories for filter
$categories = getCategories($conn);

// Get current category
$current_category = null;
if ($category_id) {
    $current_category = getCategoryById($conn, $category_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - NOORJA</title>
    <meta name="description" content="Shop our elegant collection of women's fashion, beauty products, and accessories at NOORJA.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body<?php echo isLoggedIn() ? ' class="logged-in"' : ''; ?>>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="page-title">
                        <?php if ($current_category): ?>
                            <?php echo $current_category['name']; ?>
                        <?php elseif ($search): ?>
                            Search Results for "<?php echo htmlspecialchars($search); ?>"
                        <?php else: ?>
                            Shop All Products
                        <?php endif; ?>
                    </h1>
                    <p class="text-muted">
                        <?php echo $total_products; ?> products found
                    </p>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-md-end">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Shop</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Shop Section -->
    <section class="shop-section py-5">
        <div class="container">
            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-lg-3">
                    <div class="filter-sidebar">
                        <div class="filter-header d-flex justify-content-between align-items-center mb-3">
                            <h5>Filters</h5>
                            <button class="btn btn-sm btn-outline-primary d-lg-none filter-toggle">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>

                        <!-- Search Filter -->
                        <div class="filter-group mb-4">
                            <h6>Search</h6>
                            <form action="shop.php" method="GET" class="search-filter">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" 
                                           placeholder="Search products..." 
                                           value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <?php if ($category_id): ?>
                                <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                                <?php endif; ?>
                            </form>
                        </div>

                        <!-- Category Filter -->
                        <div class="filter-group mb-4">
                            <h6>Categories</h6>
                            <div class="category-filters">
                                <a href="shop.php<?php echo $search ? '?search=' . urlencode($search) : ''; ?>" 
                                   class="category-filter <?php echo !$category_id ? 'active' : ''; ?>">
                                    All Categories
                                </a>
                                <?php foreach ($categories as $category): ?>
                                <a href="shop.php?category=<?php echo $category['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="category-filter <?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                                    <?php echo $category['name']; ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Sort Filter -->
                        <div class="filter-group mb-4">
                            <h6>Sort By</h6>
                            <form action="shop.php" method="GET" class="sort-filter">
                                <select name="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                                </select>
                                <?php if ($category_id): ?>
                                <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                                <?php endif; ?>
                                <?php if ($search): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <?php endif; ?>
                            </form>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="filter-group mb-4">
                            <h6>Price Range</h6>
                            <div class="price-range">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_range" id="price_all" checked>
                                    <label class="form-check-label" for="price_all">All Prices</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_range" id="price_under_1000">
                                    <label class="form-check-label" for="price_under_1000">Under ৳1,000</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_range" id="price_1000_3000">
                                    <label class="form-check-label" for="price_1000_3000">৳1,000 - ৳3,000</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="price_range" id="price_over_3000">
                                    <label class="form-check-label" for="price_over_3000">Over ৳3,000</label>
                                </div>
                            </div>
                        </div>

                        <!-- Clear Filters -->
                        <?php if ($category_id || $search || $sort != 'newest'): ?>
                        <div class="filter-group">
                            <a href="shop.php" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-times"></i> Clear All Filters
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="col-lg-9">
                    <?php if (empty($products)): ?>
                    <!-- No Products Found -->
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h3>No Products Found</h3>
                        <p class="text-muted">Try adjusting your search criteria or browse our categories.</p>
                        <a href="shop.php" class="btn btn-primary">Browse All Products</a>
                    </div>
                    <?php else: ?>
                    <!-- Products Grid -->
                    <div class="row">
                        <?php foreach ($products as $product): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="product-card">
                                <?php if ($product['badge']): ?>
                                <div class="product-badge <?php echo strtolower(str_replace(' ', '-', $product['badge'])); ?>-badge">
                                    <?php echo $product['badge']; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="product-image">
                                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                                    <div class="product-overlay">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <button class="btn btn-sm btn-outline-primary quick-view-btn" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-search"></i> Quick View
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="product-content">
                                    <h3 class="product-title">
                                        <a href="product.php?id=<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a>
                                    </h3>
                                    
                                    <div class="product-price">
                                        <?php if ($product['sale_price']): ?>
                                                                        <span class="current-price">৳<?php echo number_format($product['sale_price']); ?></span>
                                <span class="original-price">৳<?php echo number_format($product['price']); ?></span>
                                        <?php else: ?>
                                        <span class="current-price">৳<?php echo number_format($product['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <button class="btn btn-sm btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-shopping-cart"></i> Add to Cart
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary wishlist-toggle" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                    
                                    <?php if ($product['stock'] <= 0): ?>
                                    <div class="product-stock">
                                        <span class="badge bg-danger">Out of Stock</span>
                                    </div>
                                    <?php elseif ($product['stock'] <= 5): ?>
                                    <div class="product-stock">
                                        <span class="badge bg-warning">Only <?php echo $product['stock']; ?> left</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination_info['total_pages'] > 1): ?>
                    <nav aria-label="Product pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination_info['has_previous']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination_info['previous_page']; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $sort != 'newest' ? '&sort=' . $sort : ''; ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination_info['total_pages']; $i++): ?>
                            <li class="page-item <?php echo $i == $pagination_info['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $sort != 'newest' ? '&sort=' . $sort : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination_info['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination_info['next_page']; ?><?php echo $category_id ? '&category=' . $category_id : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $sort != 'newest' ? '&sort=' . $sort : ''; ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/cart.js"></script>
</body>
</html>
