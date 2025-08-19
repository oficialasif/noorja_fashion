<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get featured products
$featured_products = getFeaturedProducts($conn, 8);

// Get categories
$categories = getCategories($conn);

// Get trending products
$trending_products = getTrendingProducts($conn, 6);

// Get flash sale products
$flash_sale_products = getFlashSaleProducts($conn, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOORJA - Elegant Women's Fashion & Beauty</title>
    <meta name="description" content="Discover elegant women's fashion, beauty products, and accessories at NOORJA. Premium quality with exclusive offers and flash sales.">
    
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-slider">
            <div class="hero-slide active">
                <div class="container">
                    <div class="row align-items-center min-vh-100">
                        <div class="col-lg-6">
                            <h1 class="hero-title">Elegant Fashion for the Modern Woman</h1>
                            <p class="hero-subtitle">Discover our curated collection of premium women's fashion and beauty products</p>
                            <div class="hero-buttons">
                                <a href="shop.php" class="btn btn-primary">Shop Now</a>
                                <a href="offers.php" class="btn btn-outline-primary">View Offers</a>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Elegant Fashion" class="hero-image">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Explore our diverse collection</p>
            </div>
            <div class="row">
                <?php foreach ($categories as $category): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="category-card">
                        <div class="category-image">
                            <img src="<?php echo $category['image_url']; ?>" alt="<?php echo $category['name']; ?>">
                        </div>
                        <div class="category-content">
                            <h3><?php echo $category['name']; ?></h3>
                            <a href="shop.php?category=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary">Shop Now</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Flash Sale Section -->
    <?php if (!empty($flash_sale_products)): ?>
    <section class="flash-sale-section py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Flash Sale</h2>
                <div class="countdown-timer" id="flashSaleCountdown">
                    <div class="countdown-item">
                        <span class="countdown-number" id="days">00</span>
                        <span class="countdown-label">Days</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="hours">00</span>
                        <span class="countdown-label">Hours</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="minutes">00</span>
                        <span class="countdown-label">Minutes</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-number" id="seconds">00</span>
                        <span class="countdown-label">Seconds</span>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php foreach ($flash_sale_products as $product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card flash-sale">
                        <div class="product-badge flash-sale-badge">Flash Sale</div>
                        <div class="product-image">
                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                            <div class="product-overlay">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                            </div>
                        </div>
                        <div class="product-content">
                            <h3 class="product-title"><?php echo $product['name']; ?></h3>
                            <div class="product-price">
                                <span class="current-price">৳<?php echo number_format($product['sale_price']); ?></span>
                                <span class="original-price">৳<?php echo number_format($product['price']); ?></span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-sm btn-primary add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Products -->
    <section class="featured-products py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Featured Products</h2>
                <p class="section-subtitle">Handpicked for you</p>
            </div>
            <div class="row">
                <?php foreach ($featured_products as $product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card">
                        <?php if ($product['badge']): ?>
                        <div class="product-badge <?php echo strtolower(str_replace(' ', '-', $product['badge'])); ?>-badge"><?php echo $product['badge']; ?></div>
                        <?php endif; ?>
                        <div class="product-image">
                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                            <div class="product-overlay">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                            </div>
                        </div>
                        <div class="product-content">
                            <h3 class="product-title"><?php echo $product['name']; ?></h3>
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
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="shop.php" class="btn btn-primary">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Trending Products -->
    <section class="trending-products py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Trending Now</h2>
                <p class="section-subtitle">Most popular this week</p>
            </div>
            <div class="row">
                <?php foreach ($trending_products as $product): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="product-card trending">
                        <div class="product-badge trending-badge">Trending</div>
                        <div class="product-image">
                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                            <div class="product-overlay">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                            </div>
                        </div>
                        <div class="product-content">
                            <h3 class="product-title"><?php echo $product['name']; ?></h3>
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
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center">
                    <h3>Stay Updated</h3>
                    <p>Subscribe to our newsletter for exclusive offers and updates</p>
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Enter your email" required>
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
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
    
    <script>
        // Flash sale countdown timer
        function updateCountdown() {
            const now = new Date().getTime();
            const endDate = new Date('2024-12-31T23:59:59').getTime();
            const distance = endDate - now;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').textContent = days.toString().padStart(2, '0');
            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
        }

        setInterval(updateCountdown, 1000);
        updateCountdown();
    </script>
</body>
</html>
