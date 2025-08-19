<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!validateEmail($email)) $errors[] = 'Please enter a valid email address';
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (empty($errors)) {
        // Here you would typically save the contact form to database or send email
        // For now, we'll just show a success message
        setFlashMessage('success', 'Thank you for your message! We will get back to you soon.');
        redirect('contact.php');
    } else {
        setFlashMessage('error', implode(', ', $errors));
    }
}

// Get dynamic contact settings
$site_name = getSetting($conn, 'site_name', 'NOORJA');
$contact_phone = getSetting($conn, 'contact_phone', '+91 98765 43210');
$contact_email = getSetting($conn, 'contact_email', 'info@noorja.com');
$contact_address = getSetting($conn, 'contact_address', 'Rajshahi Mahanagar, Bangladesh');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo htmlspecialchars($site_name); ?></title>
    <meta name="description" content="Get in touch with NOORJA. We're here to help with any questions about our products or services.">
    
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
                    <h1 class="page-title">Contact Us</h1>
                    <p class="text-muted">We'd love to hear from you</p>
                </div>
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-md-end">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Contact</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section py-5">
        <div class="container">
            <div class="row">
                <!-- Contact Information -->
                <div class="col-lg-4 mb-5">
                    <div class="contact-info">
                        <h3 class="mb-4">Get in Touch</h3>
                        <p class="mb-4">Have questions about our products or need assistance? We're here to help! Reach out to us through any of the channels below.</p>
                        
                        <div class="contact-item mb-4">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                            </div>
                            <div class="contact-details">
                                <h5>Address</h5>
                                <p><?php echo nl2br(htmlspecialchars($contact_address)); ?></p>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="contact-icon">
                                <i class="fas fa-phone text-primary"></i>
                            </div>
                            <div class="contact-details">
                                <h5>Phone</h5>
                                <p><?php echo htmlspecialchars($contact_phone); ?></p>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="contact-icon">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div class="contact-details">
                                <h5>Email</h5>
                                <p><?php echo htmlspecialchars($contact_email); ?></p>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="contact-icon">
                                <i class="fas fa-clock text-primary"></i>
                            </div>
                            <div class="contact-details">
                                <h5>Business Hours</h5>
                                <p>Monday - Saturday: 9:00 AM - 8:00 PM<br>Sunday: 10:00 AM - 6:00 PM</p>
                            </div>
                        </div>
                        
                        <!-- Social Media -->
                        <div class="social-links mt-4">
                            <h5>Follow Us</h5>
                            <div class="social-icons">
                                <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-pinterest"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="contact-form">
                        <h3 class="mb-4">Send us a Message</h3>
                        <form method="POST" class="contact-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Subscribe to our newsletter for updates and offers
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section py-5 bg-light">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Find answers to common questions</p>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                    How can I track my order?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can track your order by logging into your account and visiting the "My Orders" section. You'll receive tracking updates via email and SMS as well.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                    What is your return policy?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We offer a 30-day return policy for most items. Products must be unused and in their original packaging. Some items may have different return conditions.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                    Do you ship internationally?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Currently, we ship to all major cities in India. We're working on expanding our international shipping options. Please check our shipping page for current availability.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                    How can I change or cancel my order?
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You can modify or cancel your order within 2 hours of placing it by contacting our customer support team. After that, the order will be processed for shipping.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We accept all major credit cards, debit cards, UPI, net banking, and cash on delivery. All online payments are secured with SSL encryption.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section py-5">
        <div class="container">
            <div class="section-header text-center mb-5">
                <h2 class="section-title">Find Us</h2>
                <p class="section-subtitle">Visit our store in Mumbai</p>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="map-container">
                        <div class="map-placeholder">
                            <div class="map-content text-center">
                                <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
                                <h4>NOORJA Store</h4>
                                <p><?php echo htmlspecialchars($contact_address); ?></p>
                                <p class="text-muted">Interactive map will be displayed here</p>
                                <a href="https://maps.google.com" target="_blank" class="btn btn-outline-primary">
                                    <i class="fas fa-directions"></i> Get Directions
                                </a>
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
    
    <script>
        // Form validation
        document.querySelector('.contact-form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Email validation
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.value && !emailRegex.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
        
        // Real-time validation
        document.querySelectorAll('.contact-form input, .contact-form textarea').forEach(field => {
            field.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
                
                // Email validation
                if (this.type === 'email' && this.value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(this.value)) {
                        this.classList.add('is-invalid');
                    } else {
                        this.classList.remove('is-invalid');
                    }
                }
            });
        });
    </script>
</body>
</html>
