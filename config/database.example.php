<?php
// Database Configuration
// Copy this file to database.php and update with your database credentials

define('DB_HOST', 'localhost');
define('DB_NAME', 'noorja_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'http://localhost/noorja');
define('SITE_NAME', 'NOORJA');
define('SITE_EMAIL', 'info@noorja.com');
define('SITE_PHONE', '+880 1737 18****');
define('CURRENCY', '৳');

// Pagination
define('ITEMS_PER_PAGE', 12);

// Database Connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
