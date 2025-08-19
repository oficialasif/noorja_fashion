<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    setFlashMessage('error', 'Product not found');
    redirect('shop.php');
}

// Get product details
$product = getProductById($conn, $product_id);

if (!$product) {
    setFlashMessage('error', 'Product not found');
    redirect('shop.php');
}

// Get related products
$related_products = getRelatedProducts($conn, $product_id, $product['category_id'], 4);

// Check if product is in user's wishlist
$in_wishlist = false;
if (isLoggedIn()) {
    $in_wishlist = isInWishlist($conn, $_SESSION['user_id'], $product_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - NOORJA</title>
    <meta name="description" content="<?php echo htmlspecialchars($product['description']); ?>">
    
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

    <!-- Product Details Section -->
    <section class="product-details py-5">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                    <?php if ($product['category_name']): ?>
                    <li class="breadcrumb-item"><a href="shop.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active"><?php echo $product['name']; ?></li>
                </ol>
            </nav>

            <div class="row">
                <!-- Product Images -->
                <div class="col-lg-6 mb-4">
                    <div class="product-images">
                        <div class="main-image mb-3">
                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid rounded" id="mainProductImage">
                        </div>
                        <div class="thumbnail-images d-flex gap-2">
                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" class="img-thumbnail thumbnail-img active" onclick="changeMainImage(this.src)">
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-lg-6">
                    <div class="product-info">
                        <!-- Product Badge -->
                        <?php if ($product['badge']): ?>
                        <div class="product-badge <?php echo strtolower(str_replace(' ', '-', $product['badge'])); ?>-badge mb-3">
                            <?php echo $product['badge']; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Product Title -->
                        <h1 class="product-title mb-3"><?php echo $product['name']; ?></h1>

                        <!-- Product Price -->
                        <div class="product-price mb-4">
                            <?php if ($product['sale_price']): ?>
                                                            <span class="current-price">৳<?php echo number_format($product['sale_price']); ?></span>
                                <span class="original-price">৳<?php echo number_format($product['price']); ?></span>
                            <span class="discount-badge"><?php echo round((($product['price'] - $product['sale_price']) / $product['price']) * 100); ?>% OFF</span>
                            <?php else: ?>
                                                            <span class="current-price">৳<?php echo number_format($product['price']); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Product Description -->
                        <div class="product-description mb-4">
                            <h6>Description</h6>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>

                        <!-- Product Stock -->
                        <div class="product-stock mb-4">
                            <?php if ($product['stock'] <= 0): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> Out of Stock
                            </div>
                            <?php elseif ($product['stock'] <= 5): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-clock"></i> Only <?php echo $product['stock']; ?> items left in stock
                            </div>
                            <?php else: ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Add to Cart Section -->
                        <?php if ($product['stock'] > 0): ?>
                        <div class="add-to-cart-section mb-4">
                            <form class="add-to-cart-form">
                                <div class="row align-items-end">
                                    <div class="col-md-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <div class="quantity-controls">
                                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-minus">-</button>
                                            <input type="number" class="form-control quantity-input" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-plus">+</button>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-shopping-cart"></i> Add to Cart
                                            </button>
                                            <button type="button" class="btn btn-outline-primary btn-lg buy-now" data-product-id="<?php echo $product['id']; ?>">
                                                <i class="fas fa-bolt"></i> Buy Now
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>

                        <!-- Wishlist Button -->
                        <div class="wishlist-section mb-4">
                            <button class="btn btn-outline-secondary wishlist-toggle <?php echo $in_wishlist ? 'active' : ''; ?>" data-product-id="<?php echo $product['id']; ?>">
                                <i class="<?php echo $in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                                <?php echo $in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                            </button>
                        </div>

                        <!-- Product Features -->
                        <div class="product-features mb-4">
                            <h6>Features</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i> Premium Quality</li>
                                <li><i class="fas fa-check text-success me-2"></i> Fast Delivery</li>
                                <li><i class="fas fa-check text-success me-2"></i> Easy Returns</li>
                                <li><i class="fas fa-check text-success me-2"></i> Secure Payment</li>
                            </ul>
                        </div>

                        <!-- Share Product -->
                        <div class="share-product">
                            <h6>Share this product</h6>
                            <div class="social-share">
                                <a href="#" class="btn btn-sm btn-outline-primary" onclick="shareOnFacebook()">
                                    <i class="fab fa-facebook"></i> Facebook
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-info" onclick="shareOnTwitter()">
                                    <i class="fab fa-twitter"></i> Twitter
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-success" onclick="shareOnWhatsApp()">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Details Tabs -->
    <section class="product-tabs py-5 bg-light">
        <div class="container">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                        Description
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab">
                        Specifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                        Reviews
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="productTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <div class="p-4">
                        <h4>Product Description</h4>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="specifications" role="tabpanel">
                    <div class="p-4">
                        <h4>Product Specifications</h4>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>Category</th>
                                    <td><?php echo $product['category_name'] ?: 'N/A'; ?></td>
                                </tr>
                                <tr>
                                    <th>Stock</th>
                                    <td><?php echo $product['stock']; ?> units</td>
                                </tr>
                                <tr>
                                    <th>SKU</th>
                                    <td>NOORJA-<?php echo str_pad($product['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="p-4">
                        <h4>Customer Reviews</h4>
                        <div class="text-center py-4">
                            <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                            <?php if (isLoggedIn()): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                Write a Review
                            </button>
                            <?php else: ?>
                            <a href="auth.php" class="btn btn-primary">Login to Review</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <section class="related-products py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">Related Products</h2>
            <div class="row">
                <?php foreach ($related_products as $related_product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card">
                        <?php if ($related_product['badge']): ?>
                        <div class="product-badge <?php echo strtolower(str_replace(' ', '-', $related_product['badge'])); ?>-badge">
                            <?php echo $related_product['badge']; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="product-image">
                            <img src="<?php echo $related_product['image_url']; ?>" alt="<?php echo $related_product['name']; ?>">
                            <div class="product-overlay">
                                <a href="product.php?id=<?php echo $related_product['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                        
                        <div class="product-content">
                            <h3 class="product-title">
                                <a href="product.php?id=<?php echo $related_product['id']; ?>"><?php echo $related_product['name']; ?></a>
                            </h3>
                            
                            <div class="product-price">
                                <?php if ($related_product['sale_price']): ?>
                                <span class="current-price">৳<?php echo number_format($related_product['sale_price']); ?></span>
                                <span class="original-price">৳<?php echo number_format($related_product['price']); ?></span>
                                <?php else: ?>
                                <span class="current-price">৳<?php echo number_format($related_product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-actions">
                                <button class="btn btn-sm btn-primary add-to-cart" data-product-id="<?php echo $related_product['id']; ?>">
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

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Write a Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm">
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="rating-stars">
                                <i class="far fa-star" data-rating="1"></i>
                                <i class="far fa-star" data-rating="2"></i>
                                <i class="far fa-star" data-rating="3"></i>
                                <i class="far fa-star" data-rating="4"></i>
                                <i class="far fa-star" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="rating" id="rating" value="0">
                        </div>
                        <div class="mb-3">
                            <label for="review" class="form-label">Review</label>
                            <textarea class="form-control" id="review" name="review" rows="4" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitReview()">Submit Review</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/cart.js"></script>
    
    <script>
        // Change main product image
        function changeMainImage(src) {
            document.getElementById('mainProductImage').src = src;
            document.querySelectorAll('.thumbnail-img').forEach(img => img.classList.remove('active'));
            event.target.classList.add('active');
        }

        // Share functions
        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('<?php echo $product['name']; ?>');
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${text}`, '_blank');
        }

        function shareOnTwitter() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('Check out this amazing product: <?php echo $product['name']; ?>');
            window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
        }

        function shareOnWhatsApp() {
            const url = encodeURIComponent(window.location.href);
            const text = encodeURIComponent('Check out this amazing product: <?php echo $product['name']; ?>');
            window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
        }

        // Rating stars
        document.querySelectorAll('.rating-stars i').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.dataset.rating;
                document.getElementById('rating').value = rating;
                
                document.querySelectorAll('.rating-stars i').forEach(s => {
                    if (s.dataset.rating <= rating) {
                        s.className = 'fas fa-star text-warning';
                    } else {
                        s.className = 'far fa-star';
                    }
                });
            });
        });

        // Submit review
        function submitReview() {
            const rating = document.getElementById('rating').value;
            const review = document.getElementById('review').value;
            
            if (rating == 0) {
                alert('Please select a rating');
                return;
            }
            
            if (!review.trim()) {
                alert('Please write a review');
                return;
            }
            
            // Submit review via AJAX
            fetch('ajax/review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: <?php echo $product['id']; ?>,
                    rating: rating,
                    review: review
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Review submitted successfully!');
                    bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
                } else {
                    alert(data.message || 'Error submitting review');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting review');
            });
        }
    </script>
</body>
</html>
