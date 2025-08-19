# NOORJA - Elegant Women's Fashion & Beauty eCommerce

A professional, minimal, and elegant eCommerce website for selling women's fashion and beauty products. Built with PHP, MySQL, HTML5, CSS3, and JavaScript.

## 🌟 Features

### User Features
- **User Authentication**: Registration, login, and profile management
- **Product Browsing**: Browse products by category, search, and filter
- **Shopping Cart**: Add, update, and remove items from cart
- **Wishlist**: Save favorite products for later
- **Order Management**: Place orders and track order status
- **Product Reviews**: Rate and review purchased products
- **Flash Sales**: Special offers with countdown timers
- **Responsive Design**: Mobile-first responsive design

### Admin Features
- **Dashboard**: Overview of sales, orders, and user statistics
- **Product Management**: Add, edit, and manage products
- **Category Management**: Organize products by categories
- **Order Management**: Process and update order statuses
- **User Management**: Manage user accounts and roles
- **Reports & Analytics**: Sales reports and analytics
- **Settings Management**: Configure site settings dynamically
- **Coupon Management**: Create and manage discount coupons

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.3
- **Icons**: Font Awesome 6.4
- **Fonts**: Google Fonts (Playfair Display, Poppins)
- **Charts**: Chart.js for analytics

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for clean URLs)

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/oficialasif/noorja_fashion.git
   cd noorja_fashion
   ```

2. **Set up the database**
   - Create a MySQL database named `noorja_db`
   - Import the database schema from `database/schema.sql`

3. **Configure database connection**
   ```bash
   cp config/database.example.php config/database.php
   ```
   Edit `config/database.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'noorja_db');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Set up web server**
   - Point your web server to the project directory
   - Ensure the web server has read/write permissions

5. **Access the application**
   - Frontend: `http://your-domain.com`
   - Admin Panel: `http://your-domain.com/admin`
   - Admin Login: `admin@noorja.com` / `admin123`

## 📁 Project Structure

```
noorja_fashion/
├── admin/                 # Admin panel files
│   ├── index.php         # Admin dashboard
│   ├── products.php      # Product management
│   ├── categories.php    # Category management
│   ├── orders.php        # Order management
│   ├── users.php         # User management
│   ├── reports.php       # Analytics and reports
│   ├── settings.php      # Site settings
│   └── offers.php        # Coupon management
├── ajax/                 # AJAX handlers
│   ├── cart.php          # Cart operations
│   ├── wishlist.php      # Wishlist operations
│   └── review.php        # Review submission
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   └── js/              # JavaScript files
├── config/               # Configuration files
│   └── database.php     # Database configuration
├── database/             # Database files
│   └── schema.sql       # Database schema
├── includes/             # PHP includes
│   ├── functions.php    # Core functions
│   ├── header.php       # Site header
│   ├── footer.php       # Site footer
│   ├── admin_header.php # Admin header
│   └── admin_footer.php # Admin footer
├── user/                 # User panel files
│   ├── dashboard.php    # User dashboard
│   ├── profile.php      # Profile management
│   ├── orders.php       # Order history
│   └── wishlist.php     # Wishlist management
├── index.php            # Homepage
├── shop.php             # Product listing
├── product.php          # Product details
├── cart.php             # Shopping cart
├── checkout.php         # Checkout process
├── auth.php             # Authentication
├── offers.php           # Special offers
├── about.php            # About page
├── contact.php          # Contact page
└── README.md            # This file
```

## 🎨 Design Features

- **Color Scheme**: 
  - Primary Deep Green: #1B3B36
  - Golden Yellow: #E5A823
  - Soft Beige: #FAF3E6
  - Warm Cream: #FFF9F2
  - Neutral Dark: #2A2A2A
  - White: #FFFFFF

- **Typography**:
  - Headings: Playfair Display
  - Body Text: Poppins

- **Responsive Design**: Mobile-first approach with Bootstrap 5.3

## 🔧 Configuration

### Site Settings
The admin panel allows you to configure:
- Site name and description
- Contact information
- Shipping costs and tax rates
- Currency settings
- Maintenance mode

### Dynamic Settings
All site settings are stored in the database and can be updated through the admin panel without editing code.

## 🔒 Security Features

- **SQL Injection Prevention**: Prepared statements with PDO
- **XSS Protection**: Input sanitization and output escaping
- **Password Security**: bcrypt hashing
- **Session Management**: Secure session handling
- **CSRF Protection**: Form token validation
- **Input Validation**: Server-side validation for all inputs

## 📱 Responsive Design

The website is fully responsive and optimized for:
- Desktop computers
- Tablets
- Mobile phones
- All modern browsers

## 🚀 Performance Features

- **Optimized Database Queries**: Efficient SQL with proper indexing
- **Caching**: Settings caching for improved performance
- **Image Optimization**: Responsive images with lazy loading
- **Minified Assets**: Optimized CSS and JavaScript

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**Asif Hossain**
- GitHub: [@oficialasif](https://github.com/oficialasif)
- Email: noorshoping@gmail.com

## 🙏 Acknowledgments

- Bootstrap for the responsive framework
- Font Awesome for the icons
- Google Fonts for typography
- Unsplash for sample images
- Chart.js for analytics visualization

## 📞 Support

For support and questions:
- Email: noorshoping@gmail.com
- Phone: +880 1737 18****
- Address: Rajshahi Mahanagar, Bangladesh

---

**NOORJA** - Elegant fashion for the modern woman. 🌸
