<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle logout
if ((isset($_GET['action']) && $_GET['action'] === 'logout') || isset($_GET['logout'])) {
    session_destroy();
    setFlashMessage('success', 'You have been logged out successfully');
    redirect('index.php');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login') {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            setFlashMessage('error', 'Please fill in all fields');
        } else {
            $user = loginUser($conn, $email, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                setFlashMessage('success', 'Welcome back, ' . $user['name'] . '!');
                
                // Redirect to intended page or dashboard
                $redirect_url = $_SESSION['redirect_url'] ?? '';
                unset($_SESSION['redirect_url']);
                
                if ($redirect_url) {
                    redirect($redirect_url);
                } else {
                    // Redirect based on user role
                    if ($user['role'] === 'admin') {
                        redirect('admin/index.php');
                    } else {
                        redirect('user/dashboard.php');
                    }
                }
            } else {
                setFlashMessage('error', 'Invalid email or password');
            }
        }
    } elseif ($action === 'register') {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address']);
        
        // Validation
        $errors = [];
        
        if (empty($name)) $errors[] = 'Name is required';
        if (empty($email)) $errors[] = 'Email is required';
        if (!validateEmail($email)) $errors[] = 'Please enter a valid email address';
        if (empty($password)) $errors[] = 'Password is required';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters long';
        if ($password !== $confirm_password) $errors[] = 'Passwords do not match';
        if (empty($phone)) $errors[] = 'Phone number is required';
        if (empty($address)) $errors[] = 'Address is required';
        
        if (empty($errors)) {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                setFlashMessage('error', 'Email address already registered');
            } else {
                // Register user
                $user_data = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'phone' => $phone,
                    'address' => $address
                ];
                
                if (registerUser($conn, $user_data)) {
                    setFlashMessage('success', 'Registration successful! Please login with your credentials.');
                } else {
                    setFlashMessage('error', 'Registration failed. Please try again.');
                }
            }
        } else {
            setFlashMessage('error', implode(', ', $errors));
        }
    }
}

// Store intended redirect URL
if (!isLoggedIn() && isset($_GET['redirect'])) {
    $_SESSION['redirect_url'] = $_GET['redirect'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register - NOORJA</title>
    <meta name="description" content="Login or register to access your NOORJA account and start shopping.">
    
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

    <!-- Auth Section -->
    <section class="auth-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="auth-container">
                        <div class="row">
                            <!-- Login Form -->
                            <div class="col-md-6">
                                <div class="auth-form">
                                    <h2 class="auth-title">Login</h2>
                                    <p class="auth-subtitle">Welcome back! Please login to your account.</p>
                                    
                                    <form method="POST" class="login-form">
                                        <input type="hidden" name="action" value="login">
                                        
                                        <div class="mb-3">
                                            <label for="login_email" class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                <input type="email" class="form-control" id="login_email" name="email" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="login_password" class="form-label">Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="login_password" name="password" required>
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('login_password')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="remember_me">
                                            <label class="form-check-label" for="remember_me">Remember me</label>
                                        </div>
                                        
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary btn-lg">Login</button>
                                        </div>
                                    </form>
                                    
                                    <div class="text-center mt-3">
                                        <a href="#" class="text-muted">Forgot your password?</a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Register Form -->
                            <div class="col-md-6">
                                <div class="auth-form">
                                    <h2 class="auth-title">Register</h2>
                                    <p class="auth-subtitle">Create a new account to start shopping.</p>
                                    
                                    <form method="POST" class="register-form">
                                        <input type="hidden" name="action" value="register">
                                        
                                        <div class="mb-3">
                                            <label for="register_name" class="form-label">Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="register_name" name="name" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="register_email" class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                <input type="email" class="form-control" id="register_email" name="email" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="register_phone" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                <input type="tel" class="form-control" id="register_phone" name="phone" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="register_address" class="form-label">Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                                <textarea class="form-control" id="register_address" name="address" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="register_password" class="form-label">Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="register_password" name="password" required>
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('register_password')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Password must be at least 6 characters long</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Confirm Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="agree_terms" required>
                                            <label class="form-check-label" for="agree_terms">
                                                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                            </label>
                                        </div>
                                        
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
                                        </div>
                                    </form>
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
    
    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.parentNode.querySelector('button');
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
        
        // Form validation
        document.querySelectorAll('.auth-form form').forEach(form => {
            form.addEventListener('submit', function(e) {
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
                
                // Password confirmation validation
                const password = document.getElementById('register_password');
                const confirmPassword = document.getElementById('confirm_password');
                
                if (password && confirmPassword && password.value !== confirmPassword.value) {
                    confirmPassword.classList.add('is-invalid');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
        
        // Real-time password confirmation validation
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                const password = document.getElementById('register_password');
                if (password.value !== this.value) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }
    </script>
</body>
</html>
