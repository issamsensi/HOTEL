# 🏨 Grand Horizon Hotel - Luxury Accommodation Website

[![Portfolio](https://img.shields.io/badge/Portfolio-issamsensi.com-blue?style=flat-square)](https://issamsensi.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=flat-square&logo=tailwind-css)](https://tailwindcss.com)
[![Academic Project](https://img.shields.io/badge/Academic-Final%20Study%20Project-orange?style=flat-square)](https://issamsensi.com)

**🎓 Final Study Project** - A comprehensive web development showcase demonstrating full-stack PHP development, database design, and modern frontend technologies. This luxury hotel booking system serves as a capstone project highlighting advanced web development skills and best practices.

**👥 Team Collaboration**: Developed in collaboration with **Yassine KMD** as part of our final study project partnership.

![Grand Horizon Hotel](https://via.placeholder.com/1200x600/0a1a2b/ffffff?text=Grand+Horizon+Hotel+Screenshot)

## ✨ Features

### 🏠 Core Functionality
- **Room Availability Check** - Real-time room availability with date-based search
- **Dynamic Room Display** - Interactive room galleries with pricing and features
- **Secure User Authentication** - Registration, login, and session management
- **Advanced Booking System** - Complete reservation workflow with payment integration
- **User Profile Management** - Account settings, booking history, and preferences

### 🎨 User Experience
- **Responsive Design** - Mobile-first approach with Tailwind CSS
- **Interactive Galleries** - Photo galleries with Swiper.js carousels
- **Dynamic Content** - News articles, guest reviews, and service showcases
- **Newsletter Subscription** - Email marketing integration
- **Multi-language Support** - Internationalization ready

### 👨‍💼 Admin Panel
- **Dashboard Analytics** - Booking statistics and revenue tracking
- **Content Management** - News, gallery, and service administration
- **User Management** - Customer data and booking oversight
- **Room Management** - Inventory control and pricing management
- **Review Moderation** - Guest feedback management

### 🔧 Technical Features
- **Database Optimization** - Efficient MySQL queries with proper indexing
- **Security Implementation** - Input validation, SQL injection prevention
- **Performance Optimization** - Lazy loading, caching, and CDN integration
- **SEO Friendly** - Meta tags, structured data, and search optimization

## 🛠️ Tech Stack

### Backend
- **PHP 8.2+** - Server-side scripting and business logic
- **MySQL 8.0+** - Relational database management
- **PDO** - Secure database connectivity

### Frontend
- **Tailwind CSS 4.x** - Utility-first CSS framework
- **JavaScript (ES6+)** - Interactive client-side functionality
- **Swiper.js** - Touch-enabled sliders and carousels
- **FontAwesome 6.x** - Icon library and typography

### Development Tools
- **Composer** - PHP dependency management
- **NPM** - Node.js package management
- **Git** - Version control
- **VS Code** - Development environment

## 📋 Prerequisites

Before running this project, ensure you have the following installed:

- **Web Server**: Apache/Nginx with PHP support
- **PHP**: Version 8.2 or higher
- **MySQL**: Version 8.0 or higher
- **Node.js**: Version 16.x or higher (for Tailwind CSS compilation)
- **Composer**: PHP dependency manager
- **Git**: Version control system

### PHP Extensions Required
```
pdo_mysql
mysqli
mbstring
openssl
fileinfo
gd
```

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/issamsensi/grand-horizon-hotel.git
cd grand-horizon-hotel
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Set Up Environment Variables
Create a `.env` file in the root directory:
```env
DB_HOST=localhost
DB_NAME=hotel
DB_USER=your_username
DB_PASS=your_password
APP_URL=http://localhost/grand-horizon-hotel
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your_email@gmail.com
MAIL_PASS=your_app_password
```

### 5. Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE hotel;
EXIT;

# Import database schema
mysql -u root -p hotel < src/hotel.sql
```

### 6. Build Assets
```bash
# Compile Tailwind CSS
npm run build

# For development with watch mode
npm run dev
```

### 7. Configure Web Server
For Apache, create a `.htaccess` file:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### 8. Set Permissions
```bash
chmod 755 -R .
chmod 777 assets/uploads/
chmod 777 src/cache/
```

## 🗄️ Database Schema

### Core Tables
- **`users`** - User accounts and authentication
- **`rooms`** - Room inventory and details
- **`room_types`** - Room categories and pricing
- **`bookings`** - Reservation records
- **`payments`** - Transaction history
- **`reviews`** - Guest feedback
- **`news`** - Hotel announcements
- **`gallery`** - Photo management
- **`services`** - Hotel amenities

### Relationships
```
users (1) ──── (N) bookings
users (1) ──── (N) reviews
rooms (N) ──── (1) room_types
bookings (N) ──── (1) rooms
bookings (1) ──── (1) payments
```

## 📖 Usage

### For Guests
1. **Browse Rooms** - View available accommodations with pricing
2. **Check Availability** - Use the booking form to check dates
3. **Register/Login** - Create account or sign in
4. **Make Reservation** - Complete booking with payment
5. **Leave Reviews** - Share experience after stay

### For Administrators
1. **Access Dashboard** - Login with admin credentials
2. **Manage Content** - Update rooms, news, and gallery
3. **Monitor Bookings** - View and manage reservations
4. **User Management** - Handle customer accounts
5. **Analytics** - Review performance metrics

## 📁 Project Structure

```
grand-horizon-hotel/
├── src/                    # PHP source files
│   ├── index.php          # Homepage
│   ├── rooms.php          # Room listings
│   ├── room-details.php   # Individual room pages
│   ├── gallery.php        # Photo gallery
│   ├── news.php           # News articles
│   ├── reviews.php        # Guest reviews
│   ├── account.php        # User account management
│   ├── register.php       # Authentication
│   ├── dashboard.php      # Admin panel
│   ├── connexion.php      # Database connection
│   └── hotel.sql          # Database schema
├── includes/              # Reusable components
│   ├── header.php         # Site header
│   └── footer.php         # Site footer
├── assets/                # Static assets
│   ├── css/
│   │   ├── index.css      # Custom styles
│   │   └── tt.css         # Additional styles
│   ├── js/                # JavaScript files
│   └── images/            # Image assets
├── images/                # Uploaded images
│   ├── gallery/           # Gallery photos
│   ├── news/              # News images
│   └── profiles/          # User avatars
├── node_modules/          # Node dependencies
├── vendor/                # PHP dependencies
├── package.json           # Node configuration
├── tailwind.config.js     # Tailwind configuration
├── composer.json          # PHP dependencies
└── README.md              # Documentation
```

## 🔧 Development

### Available Scripts
```bash
# Start development server
php -S localhost:8000 -t .

# Build CSS for production
npm run build

# Watch CSS changes during development
npm run dev

# Run PHP tests
composer test

# Code formatting
composer format
```

### Code Style
- **PHP**: PSR-12 coding standards
- **JavaScript**: ESLint configuration
- **CSS**: Follows Tailwind CSS conventions
- **HTML**: Semantic markup with accessibility

## 🚀 Deployment

### Production Checklist
- [ ] Update database credentials
- [ ] Configure mail settings
- [ ] Set up SSL certificate
- [ ] Enable caching mechanisms
- [ ] Configure CDN for assets
- [ ] Set up backup systems
- [ ] Configure monitoring

### Recommended Hosting
- **VPS**: DigitalOcean, Linode, or Vultr
- **Shared Hosting**: SiteGround or Bluehost (PHP/MySQL)
- **Cloud**: AWS EC2, Google Cloud Compute Engine

### Performance Optimization
- Enable OPcache for PHP
- Use CDN for static assets
- Implement database query caching
- Enable gzip compression
- Optimize images and assets

## 🎓 Academic Project Context

This project was developed as a **Final Study Project** for web development studies, demonstrating:

### Learning Objectives Achieved
- **Full-Stack Development**: Complete PHP/MySQL application architecture
- **Database Design**: Normalized relational database with proper relationships
- **Frontend Development**: Modern responsive design with Tailwind CSS
- **User Experience**: Intuitive interface design and user flow optimization
- **Security Implementation**: Authentication, authorization, and data protection
- **Project Management**: Version control, documentation, and deployment

### Technical Competencies Demonstrated
- **Backend Development**: PHP 8.x with PDO, session management, file uploads
- **Database Management**: MySQL schema design, queries, and optimization
- **Frontend Technologies**: HTML5, CSS3, JavaScript ES6+, responsive design
- **Framework Integration**: Tailwind CSS, Swiper.js, FontAwesome
- **Development Tools**: Git, Composer, NPM, development workflows

## 🤝 Contributing

As an academic project, this repository serves as a portfolio piece. For educational purposes:

### Learning from this Project
1. **Study the Codebase** - Review implementation patterns and best practices
2. **Understand Architecture** - Learn full-stack PHP application structure
3. **Adapt for Your Projects** - Use as reference for similar web applications
4. **Improve and Extend** - Fork and enhance with additional features

### Development Guidelines (For Educational Use)
- Follow PSR-12 PHP standards
- Write meaningful commit messages
- Ensure responsive design works
- Test thoroughly before deployment
- Document any modifications

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Contact & Academic Recognition

**Issam Sensi**
- **Portfolio**: [issamsensi.com](https://issamsensi.com)
- **Email**: contact@issamsensi.com
- **LinkedIn**: [linkedin.com/in/issamsensi](https://linkedin.com/in/issamsensi)
- **GitHub**: [github.com/issamsensi](https://github.com/issamsensi)

### Academic Project Information
- **Institution**: [Your Institution Name]
- **Program**: Web Development / Computer Science
- **Academic Year**: [Year of Study]
- **Supervisor**: [Supervisor Name] (if applicable)
- **Team Members**: Issam Sensi, Yassine KMD

### Project Evaluation Criteria Met
- ✅ **Technical Implementation**: Full-stack PHP application
- ✅ **Database Design**: Normalized schema with relationships
- ✅ **User Interface**: Responsive, modern design
- ✅ **Security**: Authentication and data protection
- ✅ **Documentation**: Comprehensive README and code comments
- ✅ **Testing**: Functional verification and error handling
- ✅ **Deployment**: Production-ready configuration

### Support
For academic inquiries or technical discussions, feel free to reach out via the contact information above.

---

<div align="center">
  <p><strong>Experience luxury redefined at Grand Horizon Hotel</strong></p>
  <p>Made with ❤️ by <a href="https://issamsensi.com">Issam Sensi</a> & <strong>Yassine KMD</strong></p>
  <p><em>Final Study Project - Academic Collaboration</em></p>
</div>
