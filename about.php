<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - NOORJA</title>
    <meta name="description" content="Learn about NOORJA's mission to provide elegant fashion and beauty products for the modern woman.">
    
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
                    <h1 class="page-title">About NOORJA</h1>
                    <p class="text-muted">Discover our story and mission</p>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-md-end">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">About</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Hero Section -->
    <section class="about-hero py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-content">
                        <h2 class="section-title mb-4">Elegant Fashion for the Modern Woman</h2>
                        <p class="lead mb-4">NOORJA is more than just a fashion brand - we're a celebration of elegance, confidence, and the timeless beauty of women. Our journey began with a simple vision: to create fashion that empowers and inspires.</p>
                        <p class="mb-4">Founded in 2020, we've grown from a small boutique to a trusted destination for women who appreciate quality, style, and sophistication. Every piece in our collection is carefully curated to reflect the diverse tastes and lifestyles of modern women.</p>
                        <div class="about-stats row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="stat-number">10K+</h3>
                                    <p class="stat-label">Happy Customers</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="stat-number">500+</h3>
                                    <p class="stat-label">Products</p>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="stat-number">50+</h3>
                                    <p class="stat-label">Cities Served</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-image">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="NOORJA Fashion" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="mission-vision py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="mission-card">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="mission-icon mb-3">
                                    <i class="fas fa-bullseye fa-3x text-primary"></i>
                                </div>
                                <h3 class="card-title">Our Mission</h3>
                                <p class="card-text">To empower women through elegant fashion choices that reflect their unique personality and lifestyle. We believe every woman deserves to feel confident and beautiful in what she wears.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="vision-card">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <div class="vision-icon mb-3">
                                    <i class="fas fa-eye fa-3x text-primary"></i>
                                </div>
                                <h3 class="card-title">Our Vision</h3>
                                <p class="card-text">To become the leading destination for women's fashion and beauty, known for our commitment to quality, style, and customer satisfaction. We envision a world where every woman can express herself through fashion.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="values-section py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Our Values</h2>
                <p class="section-subtitle">The principles that guide everything we do</p>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="value-card text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-gem fa-2x text-primary"></i>
                        </div>
                        <h4>Quality</h4>
                        <p>We never compromise on quality. Every product in our collection meets the highest standards of craftsmanship and materials.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="value-card text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-heart fa-2x text-primary"></i>
                        </div>
                        <h4>Customer First</h4>
                        <p>Our customers are at the heart of everything we do. We listen, learn, and continuously improve based on their feedback.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="value-card text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-leaf fa-2x text-primary"></i>
                        </div>
                        <h4>Sustainability</h4>
                        <p>We're committed to sustainable practices and ethical sourcing. Our goal is to minimize our environmental impact.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="value-card text-center">
                        <div class="value-icon mb-3">
                            <i class="fas fa-star fa-2x text-primary"></i>
                        </div>
                        <h4>Excellence</h4>
                        <p>We strive for excellence in every aspect of our business, from product design to customer service.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team -->
    <section class="team-section py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Meet Our Team</h2>
                <p class="section-subtitle">The passionate people behind NOORJA</p>
            </div>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="team-card text-center">
                        <div class="team-image mb-3">
                            <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Team Member" class="img-fluid rounded-circle">
                        </div>
                        <h4>Priya Sharma</h4>
                        <p class="text-muted">Founder & CEO</p>
                        <p class="small">With over 15 years of experience in fashion, Priya leads NOORJA with passion and vision.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="team-card text-center">
                        <div class="team-image mb-3">
                            <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Team Member" class="img-fluid rounded-circle">
                        </div>
                        <h4>Anjali Patel</h4>
                        <p class="text-muted">Creative Director</p>
                        <p class="small">Anjali brings her artistic vision and trend forecasting expertise to every collection.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="team-card text-center">
                        <div class="team-image mb-3">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Team Member" class="img-fluid rounded-circle">
                        </div>
                        <h4>Rahul Verma</h4>
                        <p class="text-muted">Operations Manager</p>
                        <p class="small">Rahul ensures smooth operations and exceptional customer service across all touchpoints.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="team-card text-center">
                        <div class="team-image mb-3">
                            <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?ixlib=rb-4.0.3&auto=format&fit=crop&w=300&q=80" alt="Team Member" class="img-fluid rounded-circle">
                        </div>
                        <h4>Meera Singh</h4>
                        <p class="text-muted">Customer Success</p>
                        <p class="small">Meera is dedicated to ensuring every customer has an exceptional shopping experience.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Story -->
    <section class="story-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="story-image">
                        <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Our Story" class="img-fluid rounded">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="story-content">
                        <h2 class="section-title mb-4">Our Story</h2>
                        <p class="mb-4">NOORJA was born from a simple observation: women deserve fashion that's both beautiful and practical. Our founder, Priya Sharma, noticed that many women struggled to find clothing that reflected their modern lifestyle while maintaining elegance and comfort.</p>
                        <p class="mb-4">What started as a small collection of carefully curated pieces has grown into a comprehensive fashion destination. Today, we offer everything from everyday essentials to statement pieces, all designed with the modern woman in mind.</p>
                        <p class="mb-4">Our commitment to quality, customer satisfaction, and sustainable practices has earned us the trust of thousands of women across India. We're proud to be part of their journey and look forward to serving many more.</p>
                        <a href="shop.php" class="btn btn-primary">Explore Our Collection</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials-section py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">What Our Customers Say</h2>
                <p class="section-subtitle">Real stories from real customers</p>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="testimonial-card">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="testimonial-rating mb-3">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                </div>
                                <p class="card-text">"NOORJA has transformed my wardrobe! The quality is exceptional and the styles are perfect for my lifestyle. I love how every piece makes me feel confident and elegant."</p>
                                <div class="testimonial-author">
                                    <strong>Sarah Johnson</strong>
                                    <small class="text-muted d-block">Mumbai</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="testimonial-card">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="testimonial-rating mb-3">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                </div>
                                <p class="card-text">"The customer service is outstanding! They helped me find the perfect outfit for my sister's wedding. The attention to detail and personalized recommendations are unmatched."</p>
                                <div class="testimonial-author">
                                    <strong>Priya Desai</strong>
                                    <small class="text-muted d-block">Delhi</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="testimonial-card">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="testimonial-rating mb-3">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                </div>
                                <p class="card-text">"I appreciate NOORJA's commitment to sustainable fashion. The quality of their products and their ethical practices make me feel good about my purchases."</p>
                                <div class="testimonial-author">
                                    <strong>Anita Reddy</strong>
                                    <small class="text-muted d-block">Bangalore</small>
                                </div>
                            </div>
                        </div>
                    </div>
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
</body>
</html>
