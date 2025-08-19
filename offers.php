<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get flash sale products
$flash_sale_products = getFlashSaleProducts($conn, 8);

// Get products with offers
$offer_products = getOfferProducts($conn, 8);

// Get trending products
$trending_products = getTrendingProducts($conn, 6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offers & Deals - NOORJA</title>
    <meta name="description" content="Discover amazing offers, flash sales, and exclusive deals on women's fashion and beauty products at NOORJA.">
    
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
                    <h1 class="page-title">Offers & Deals</h1>
                    <p class="text-muted">Discover amazing discounts and exclusive offers</p>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-md-end">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Offers</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Flash Sale Banner -->
    <section class="flash-sale-banner py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="flash-sale-content">
                        <h2 class="display-4 fw-bold text-white mb-3">Flash Sale</h2>
                        <p class="lead text-white mb-4">Limited time offers on selected products. Don't miss out on these incredible deals!</p>
                        <div class="countdown-timer mb-4" id="flashSaleCountdown">
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
                        <a href="#flash-sale-products" class="btn btn-warning btn-lg">
                            <i class="fas fa-bolt"></i> Shop Flash Sale
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="flash-sale-image">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Flash Sale" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Flash Sale Products -->
    <?php if (!empty($flash_sale_products)): ?>
    <section id="flash-sale-products" class="flash-sale-products py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Flash Sale Products</h2>
                <p class="section-subtitle">Limited time offers - Shop now before they're gone!</p>
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
                                <span class="discount-badge"><?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>% OFF</span>
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

    <!-- Special Offers -->
    <section class="special-offers py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Special Offers</h2>
                <p class="section-subtitle">Exclusive deals on premium products</p>
            </div>
            
            <!-- Offer Cards -->
            <div class="row mb-5">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="offer-card">
                        <div class="offer-image">
                            <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Beauty Offer" class="img-fluid">
                        </div>
                        <div class="offer-content">
                            <h4>Beauty Bundle</h4>
                            <p>Get 20% off on all beauty products when you buy 3 or more items.</p>
                            <a href="shop.php?category=2" class="btn btn-outline-primary">Shop Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="offer-card">
                        <div class="offer-image">
                            <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Fashion Offer" class="img-fluid">
                        </div>
                        <div class="offer-content">
                            <h4>Fashion Collection</h4>
                            <p>Buy 2 dresses and get 1 free! Limited time offer on selected styles.</p>
                            <a href="shop.php?category=1" class="btn btn-outline-primary">Shop Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="offer-card">
                        <div class="offer-image">
                            <img src="https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Accessories Offer" class="img-fluid">
                        </div>
                        <div class="offer-content">
                            <h4>Accessories Sale</h4>
                            <p>Up to 50% off on jewelry and accessories. Perfect for gifting!</p>
                            <a href="shop.php?category=3" class="btn btn-outline-primary">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Offer Products -->
    <?php if (!empty($offer_products)): ?>
    <section class="offer-products py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Products on Offer</h2>
                <p class="section-subtitle">Great deals on amazing products</p>
            </div>
            <div class="row">
                <?php foreach ($offer_products as $product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card">
                        <div class="product-badge offer-badge">Offer</div>
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
                                <span class="discount-badge"><?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>% OFF</span>
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

    <!-- Trending Products -->
    <?php if (!empty($trending_products)): ?>
    <section class="trending-products py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Trending Now</h2>
                <p class="section-subtitle">Most popular products this week</p>
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
    <?php endif; ?>

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
