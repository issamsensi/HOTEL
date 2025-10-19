<?php
require("connexion.php");
?>
<style>
    header {
      background: rgba(10, 26, 43, 0.95);
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
      transition: all 0.3s ease;
    }
    
    .logo {
      font-weight: 700;
      font-size: 1.75rem;
      background: linear-gradient(to right, var(--primary), #3a7bd5);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    nav a {
      position: relative;
      padding: 8px 0;
      color: rgba(255, 255, 255, 0.9);
      font-weight: 500;
    }
    
    nav a:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background: var(--primary);
      transition: width 0.3s;
    }
    
    nav a:hover {
      color: white;
    }
    
    nav a:hover:after {
      width: 100%;
    }
    .mobile-menu {
      transition: all 0.4s cubic-bezier(0.65, 0, 0.35, 1);
      background: rgba(10, 26, 43, 0.98);
      backdrop-filter: blur(15px);
    }
    
    .mobile-menu.open {
      opacity: 1;
      visibility: visible;
      transform: translateX(0);
    }
    
    .mobile-menu.closed {
      opacity: 0;
      visibility: hidden;
      transform: translateX(100%);
    }
    #back-to-top {
      transition: all 0.3s;
      background: linear-gradient(135deg, var(--primary) 0%, #1a5cb0 100%);
      box-shadow: 0 4px 15px rgba(13, 74, 157, 0.4);
    }
    
    #back-to-top.visible {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }
    
    #back-to-top.hidden {
      opacity: 0;
      visibility: hidden;
      transform: translateY(20px);
    }
  </style>
<header class="py-4 bg-secondary bg-opacity-95 backdrop-blur-md shadow-lg fixed w-full top-0 z-50 transition-all">
  <div class="container flex items-center justify-between">
    <a href="index.php" class="logo text-2xl font-bold bg-gradient-to-r from-primary to-blue-600 bg-clip-text text-transparent">Grand Horizon</a>
    
    <nav class="hidden md:flex space-x-6 lg:space-x-8">
      <a href="index.php#availability" class="relative py-2 font-medium hover:text-white transition-colors after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Availability</a>
      <a href="rooms.php" class="relative py-2 font-medium hover:text-white transition-colors after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Rooms</a>
      <a href="gallery.php" class="relative py-2 font-medium hover:text-white transition-colors after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Gallery</a>
      <a href="news.php" class="relative py-2 font-medium hover:text-white transition-colors after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">News</a>
      <a href="reviews.php" class="relative py-2 font-medium hover:text-white transition-colors after:absolute after:bottom-0 after:left-0 after:w-0 after:h-0.5 after:bg-primary after:transition-all hover:after:w-full">Reviews</a>
    </nav>
    
    <?php 
    if (isset($_SESSION["user_id"])) {
      $user_id = $_SESSION["user_id"];
      $reqq = "SELECT * FROM users WHERE user_id = $user_id";
      $res = $conn->query($reqq);
      $row = $res->fetch_assoc();
    }
    ?>
    
    <div class="flex items-center space-x-4">
      <?php if(isset($_SESSION['user_id'])): ?>
        <div class="relative">
          <button id="user-menu-btn" class="focus:outline-none" onclick="toggleUserDropdown()">
            <img src="<?php echo $row['profile_image'] ?? '../images/profiles/profile.png'; ?>" alt="User" class="w-8 h-8 rounded-full border-2 border-transparent hover:border-blue-500 transition">
          </button>
          <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-secondary rounded shadow-lg z-50 border border-gray-700">
            <a href="account.php" class="block px-4 py-2 hover:bg-primary"><i class="fas fa-user mr-2"></i>My Account</a>
            <a href="register.php" class="block px-4 py-2 hover:bg-primary"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
            <?php if($row['role'] === 'admin'): ?>
              <a href="dashboard.php" class="block px-4 py-2 bg-blue-900 hover:bg-blue-800"><i class="fas fa-cog mr-2"></i>Admin Panel</a>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <a href="register.php" class="hidden md:block px-4 py-2 rounded hover:bg-white hover:bg-opacity-10 transition">Login</a>
        <a href="register.php" class="hidden md:block px-4 py-2 bg-primary rounded hover:bg-blue-700 transition">Register</a>
      <?php endif; ?>
      
      <a href="cart.php" class="relative">
        <i class="fas fa-shopping-cart text-xl hover:text-blue-400 transition"></i>
        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
          <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo count($_SESSION['cart']); ?></span>
        <?php endif; ?>
      </a>
      
      <button id="menu-btn" class="md:hidden text-white focus:outline-none" onclick="toggleMobileMenu()">
        <i class="fas fa-bars text-2xl"></i>
      </button>
    </div>
  </div>
</header>

<div id="mobile-menu" class="mobile-menu fixed inset-0 z-40 md:hidden bg-secondary bg-opacity-98 backdrop-blur-lg transition-all duration-400 ease-[cubic-bezier(0.65,0,0.35,1)] translate-x-full">
  <div class="flex justify-end p-6">
    <button id="close-menu" class="text-white text-3xl focus:outline-none hover:text-blue-400 transition" onclick="toggleMobileMenu()">&times;</button>
  </div>
  <nav class="flex flex-col items-center space-y-8 mt-12 text-xl">
    <a href="index.php#availability" class="text-white hover:text-blue-400 transition" onclick="toggleMobileMenu()">Availability</a>
    <a href="rooms.php" class="text-white hover:text-blue-400 transition" onclick="toggleMobileMenu()">Rooms</a>
    <a href="gallery.php" class="text-white hover:text-blue-400 transition" onclick="toggleMobileMenu()">Gallery</a>
    <a href="news.php" class="text-white hover:text-blue-400 transition" onclick="toggleMobileMenu()">News</a>
    <a href="reviews.php" class="text-white hover:text-blue-400 transition" onclick="toggleMobileMenu()">Reviews</a>
    <div class="pt-8 border-t border-gray-700 w-3/4 text-center">
      <?php if(isset($_SESSION['user'])): ?>
        <a href="profile.php" class="text-blue-400 hover:underline"><i class="fas fa-user mr-2"></i>My Account</a>
        <span class="text-gray-500 mx-2">|</span>
        <a href="register.php" class="text-blue-400 hover:underline"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
      <?php else: ?>
        <a href="register.php" class="text-blue-400 hover:underline"><i class="fas fa-user mr-2"></i>Login</a>
        <span class="text-gray-500 mx-2">|</span>
        <a href="register.php" class="text-blue-400 hover:underline"><i class="fas fa-user-plus mr-2"></i>Register</a>
      <?php endif; ?>
    </div>
  </nav>
</div>