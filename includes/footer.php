<?php
// Get site settings for footer
$site_name = getSetting($conn, 'site_name', 'NOORJA');
$site_description = getSetting($conn, 'site_description', 'Elegant fashion for the modern woman. Discover our curated collection of premium women\'s fashion and beauty products.');
$contact_phone = getSetting($conn, 'contact_phone', '+91 98765 43210');
$contact_email = getSetting($conn, 'contact_email', 'info@noorja.com');
$contact_address = getSetting($conn, 'contact_address', '123 Fashion Street, Mumbai, Maharashtra 400001');
?>
<footer class="footer bg-dark text-light py-5">
    <div class="container">
        <div class="row">
            <!-- Brand Info -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h3 class="brand-name mb-3"><?php echo htmlspecialchars($site_name); ?></h3>
                <p class="mb-3"><?php echo htmlspecialchars($site_description); ?></p>
                <div class="social-links">
                    <a href="https://web.facebook.com/Itsnoorja" class="social-link"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="footer-link">Home</a></li>
                    <li><a href="shop.php" class="footer-link">Shop</a></li>
                    <li><a href="offers.php" class="footer-link">Offers</a></li>
                    <li><a href="about.php" class="footer-link">About Us</a></li>
                    <li><a href="contact.php" class="footer-link">Contact</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="mb-3">Categories</h5>
                <ul class="list-unstyled">
                    <?php foreach (getCategories($conn) as $category): ?>
                    <li><a href="shop.php?category=<?php echo $category['id']; ?>" class="footer-link"><?php echo $category['name']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="mb-3">Contact Us</h5>
                <div class="contact-info">
                    <div class="contact-item mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <span><?php echo htmlspecialchars($contact_address); ?></span>
                    </div>
                    <div class="contact-item mb-2">
                        <i class="fas fa-phone me-2"></i>
                        <span><?php echo htmlspecialchars($contact_phone); ?></span>
                    </div>
                    <div class="contact-item mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        <span><?php echo htmlspecialchars($contact_email); ?></span>
                    </div>
                    <div class="contact-item mb-2">
                        <i class="fas fa-clock me-2"></i>
                        <span>Mon - Sat: 9:00 AM - 8:00 PM</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Newsletter -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="newsletter-section text-center">
                    <h5 class="mb-3">Subscribe to Our Newsletter</h5>
                    <p class="mb-3">Get updates about new products, exclusive offers, and fashion tips!</p>
                    <form class="newsletter-form d-flex justify-content-center">
                        <div class="input-group" style="max-width: 400px;">
                            <input type="email" class="form-control" placeholder="Enter your email address" required>
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="row mt-4 pt-4 border-top">
            <div class="col-md-6">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?>. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item"><a href="#" class="footer-link">Privacy Policy</a></li>
                    <li class="list-inline-item"><a href="#" class="footer-link">Terms of Service</a></li>
                    <li class="list-inline-item"><a href="#" class="footer-link">Shipping Policy</a></li>
                    <li class="list-inline-item"><a href="#" class="footer-link">Return Policy</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
