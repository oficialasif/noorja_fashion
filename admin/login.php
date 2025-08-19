<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// If already logged in as admin, redirect to dashboard
if (isLoggedIn() && isAdmin()) {
    redirect('index.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        setFlashMessage('error', 'Please fill in all fields');
    } else {
        $user = loginUser($conn, $email, $password);
        if ($user && $user['role'] === 'admin') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            setFlashMessage('success', 'Welcome back, ' . $user['name'] . '!');
            redirect('index.php');
        } else {
            setFlashMessage('error', 'Invalid credentials or insufficient privileges');
        }
    }
}

$page_title = "Admin Login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - NOORJA Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #1B3B36;
            --golden-yellow: #E5A823;
            --soft-beige: #FAF3E6;
            --warm-cream: #FFF9F2;
            --neutral-dark: #2A2A2A;
            --white: #FFFFFF;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-green) 0%, #2a4a45 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: var(--primary-green);
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }
        
        .login-header h3 {
            margin: 0;
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(27, 59, 54, 0.25);
        }
        
        .btn-primary {
            background: var(--primary-green);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #2a4a45;
            transform: translateY(-2px);
        }
        
        .back-link {
            color: var(--white);
            text-decoration: none;
            position: absolute;
            top: 2rem;
            left: 2rem;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            color: var(--golden-yellow);
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-link">
        <i class="fas fa-arrow-left me-2"></i>Back to Site
    </a>
    
    <div class="login-card">
        <div class="login-header">
            <h3>NOORJA</h3>
            <p class="mb-0">Admin Panel</p>
        </div>
        
        <div class="login-body">
            <?php
            $flash_message = getFlashMessage();
            if ($flash_message): ?>
                <div class="alert alert-<?php echo $flash_message['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($flash_message['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Admin Panel
                </button>
            </form>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <strong>Default Admin Credentials:</strong><br>
                    Email: admin@noorja.com<br>
                    Password: admin123
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
