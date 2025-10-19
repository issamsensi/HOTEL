<?php
require("connexion.php");
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: register.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
  $target_dir = "../images/profiles/";
  $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
  $uploadOk = 1;
  $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
  if ($check !== false) {
    $uploadOk = 1;
  } else {
    $uploadOk = 0;
  }

  if ($_FILES["profile_image"]["size"] > 5000000) {
    $uploadOk = 0;
  }

  if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
  && $imageFileType != "gif" ) {
    $uploadOk = 0;
  }

  if ($uploadOk == 1) {
    $new_filename = "profile_" . $_SESSION['user_id'] . "_" . time() . "." . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
      $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE user_id = ?");
      $stmt->bind_param("si", $target_file, $_SESSION['user_id']);
      $stmt->execute();
      $stmt->close();
      
      $user_id = $_SESSION['user_id'];
      $user_result = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
      $user = $user_result->fetch_assoc();
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modify_booking'])) {
    $booking_id = $_POST['booking_id'];
    $new_check_in = $_POST['new_check_in'];
    $new_check_out = $_POST['new_check_out'];
    
    $booking_query = $conn->query("SELECT * FROM bookings WHERE booking_id = $booking_id");
    $current_booking = $booking_query->fetch_assoc();
    
    $room_query = $conn->query("SELECT r.room_id, rt.base_price 
                               FROM bookings b
                               JOIN rooms r ON b.room_id = r.room_id
                               JOIN room_types rt ON r.type_id = rt.type_id
                               WHERE b.booking_id = $booking_id");
    $room_data = $room_query->fetch_assoc();
    $room_id = $room_data['room_id'];
    
    $check_in_date = new DateTime($new_check_in);
    $check_out_date = new DateTime($new_check_out);
    $nights = $check_out_date->diff($check_in_date)->days;
    $new_total_price = $nights * $room_data['base_price'];
    
    $availability_sql = "SELECT * FROM bookings 
                        WHERE room_id = ? 
                        AND booking_id != ?
                        AND NOT (check_out <= ? OR check_in >= ?)";
    $stmt_avail = $conn->prepare($availability_sql);
    $stmt_avail->bind_param("iiss", $room_id, $booking_id, $new_check_in, $new_check_out);
    $stmt_avail->execute();
    $availability_result = $stmt_avail->get_result();

    if($availability_result->num_rows > 0) {
        $_SESSION['booking_message'] = "Sorry, the room is not available for the selected dates.";
    } else {
        $stmt = $conn->prepare("UPDATE bookings SET check_in = ?, check_out = ?, total_price = ? WHERE booking_id = ?");
        $stmt->bind_param("ssdi", $new_check_in, $new_check_out, $new_total_price, $booking_id);
        
        if($stmt->execute()) {
            $_SESSION['booking_message'] = "Booking modified successfully!";
        } else {
            $_SESSION['booking_message'] = "Error modifying your booking. Please try again.";
        }
        $stmt->close();
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_booking'])) {
  $booking_id = $_POST['booking_id'];
  
  $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
  $stmt->bind_param("i", $booking_id);
  $stmt->execute();
  $stmt->close();
  
  header("Location: ".$_SERVER['PHP_SELF']);
  exit();
}

$user_id = $_SESSION['user_id'];
$user_result = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$user = $user_result->fetch_assoc();

$bookings_result = $conn->query("
  SELECT b.*, r.* , rt.*
  FROM bookings b, room_types rt , rooms r
  where b.room_id = r.room_id
  and r.type_id = rt.type_id
  and b.user_id = $user_id AND b.check_out >= CURDATE()
  ORDER BY b.check_in ASC
");

if(isset($_POST["save_preferences"])) {
  $high_floor = $_POST['floor_preference'];
  $pillow_type = $_POST['pillow_type'];
  $special_requests = $_POST['special_requests'];

  $stmt = $conn->prepare("SELECT user_id FROM preferences WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->store_result();

  if($stmt->num_rows > 0) {
    $stmt->close();
    $stmt_update = $conn->query("UPDATE preferences SET floor_preference = '$high_floor', pillow_type = '$pillow_type', special_needs = '$special_requests' WHERE user_id = $user_id");
    if($stmt_update) {
      $_SESSION['preferences_message'] = "Preferences saved successfully!";
    } else {
      $_SESSION['preferences_message'] = "Error saving preferences. Please try again.";
    }
  } else {

    $stmt_insert = $conn->query("INSERT INTO user_preferences (user_id, floor_preference, pillow_type, special_needs) VALUES ($user_id, '$high_floor', '$pillow_type', '$special_requests')");
    if($stmt_insert) {
      $_SESSION['preferences_message'] = "Preferences saved successfully!";
    } else {
      $_SESSION['preferences_message'] = "Error saving preferences. Please try again.";
    }
  }
}
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name']) && isset($_POST['current_password'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'] ?? '';

        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current_password, $hashed_password)) {
          $profile_error = "Incorrect current password.";
        } elseif($email !== $user['email'] && $conn->query("SELECT email FROM users WHERE email = '$email'")->num_rows > 0) {
          $profile_error = "This email is already registered";
        }else{  
          $update_sql = "UPDATE users SET first_name=?, last_name=?, email=?, phone=?";
          $params = [$first_name, $last_name, $email, $phone];
          $types = "ssss";
          if (!empty($new_password)) {
            $update_sql .= ", password=?";
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
            $types .= "s";
          }
          $update_sql .= " WHERE user_id=?";
          $params[] = $user_id;
          $types .= "i";

          $stmt = $conn->prepare($update_sql);
          $stmt->bind_param($types, ...$params);
          if ($stmt->execute()) {
            $profile_success = "Profile updated successfully!";
            $user_result = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
            $user = $user_result->fetch_assoc();
          } else {
            $profile_error = "Error updating profile.";
          }
          $stmt->close();
        }
      }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>My Account | Grand Horizon</title>
  <link rel="stylesheet" href="output.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>  
  <style>
  :root {
    --primary: #0d4a9d;
    --secondary: #0a1a2b;
    --accent: #ffc107;
  }
  body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
    color: white;
    min-height: 100vh;
    margin: 0;
    padding: 0;
    line-height: 1.6;
  }
  .container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
  }
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
  .account-nav {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(8px);
    border-radius: 12px;
  }
  .account-nav a {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    color: rgba(255, 255, 255, 0.8);
    transition: all 0.3s;
  }
  .account-nav a:hover {
    color: white;
    background: rgba(255, 255, 255, 0.1);
  }
  .account-nav a.active {
    color: white;
    background: linear-gradient(135deg, var(--primary) 0%, #1a5cb0 100%);
  }
  .account-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
  }
  .booking-card {
    transition: all 0.3s;
    border-left: 4px solid var(--primary);
  }
  .booking-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(13, 74, 157, 0.3);
  }
  .btn {
    transition: all 0.3s;
    transform: translateY(0);
    background: linear-gradient(135deg, var(--primary) 0%, #1a5cb0 100%);
    border: none;
    border-radius: 8px;
    font-weight: 600;
    letter-spacing: 0.5px;
    padding: 12px 24px;
  }
  .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(13, 74, 157, 0.4);
    background: linear-gradient(135deg, #1a5cb0 0%, var(--primary) 100%);
  }
  .btn:active {
    transform: translateY(1px);
  }
  h1, h2, h3, h4 {
    font-weight: 700;
    color: white;
    margin-bottom: 1.5rem;
  }
  h2 {
    font-size: 2rem;
    position: relative;
    display: inline-block;
  }
  h2:after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 0;
    width: 60px;
    height: 3px;
    background: var(--primary);
    border-radius: 3px;
  }
  .download-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: linear-gradient(135deg, #0d4a9d 0%, #1a5cb0 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(13, 74, 157, 0.4);
    display: none;
    z-index: 1000;
    cursor: pointer;
  }
  
  .download-btn:hover {
    background: linear-gradient(135deg, #1a5cb0 0%, #0d4a9d 100%);
  }
  
  .modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
    z-index: 1001;
    overflow-y: auto;
  }
  
  .modal-content {
    background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
    margin: 5% auto;
    padding: 20px;
    border-radius: 10px;
    width: 80%;
    max-width: 600px;
    box-shadow: 0 5px 30px rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.1);
  }
  
  .close-modal {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
  }
  
  .close-modal:hover {
    color: white;
  }
  
  .profile-image-container {
    position: relative;
    display: inline-block;
  }
  
  .edit-profile-image {
    position: absolute;
    bottom: 0;
    right: 0;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
  }
  
  .form-group {
    margin-bottom: 15px;
  }
  
  .form-group label {
    display: block;
    margin-bottom: 5px;
  }
  
  .form-group input, .form-group select {
    width: 100%;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.1);
    color: white;
  }
  
  .invoice-preview {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 9999;
    display: none;
    overflow-y: auto;
    padding: 20px;
  }
  
  .invoice-preview-content {
    background: white;
    color: #333;
    max-width: 800px;
    margin: 20px auto;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 30px rgba(0,0,0,0.3);
  }
  
  .close-preview {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #f44336;
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 20px;
    cursor: pointer;
  }
  @media (max-width: 768px) {
    h2 {
    font-size: 1.75rem;
    }
    .container {
    padding: 0 15px;
    }
    .modal-content {
      width: 95%;
      margin: 10% auto;
    }
  }
  </style>
</head>
<body class="flex flex-col min-h-screen">

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
    <div class="container flex flex-col items-center pt-16">
      <a href="index.php#availability" class="py-2 font-medium hover:text-white transition-colors">Availability</a>
      <a href="rooms.php" class="py-2 font-medium hover:text-white transition-colors">Rooms</a>
      <a href="gallery.php" class="py-2 font-medium hover:text-white transition-colors">Gallery</a>
      <a href="news.php" class="py-2 font-medium hover:text-white transition-colors">News</a>
      <a href="reviews.php" class="py-2 font-medium hover:text-white transition-colors">Reviews</a>
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="account.php" class="py-2 font-medium hover:text-white transition-colors">My Account</a>
        <a href="register.php" class="py-2 font-medium hover:text-white transition-colors">Logout</a>
      <?php else: ?>
        <a href="register.php" class="py-2 font-medium hover:text-white transition-colors">Login</a>
        <a href="register.php" class="py-2 font-medium hover:text-white transition-colors">Register</a>
      <?php endif; ?>
    </div>
  </div>
  <script>
    function toggleMobileMenu() {
      const menu = document.getElementById('mobile-menu');
      menu.classList.toggle('translate-x-full');
    }
    
    function toggleUserDropdown() {
      const dropdown = document.getElementById('user-dropdown');
      dropdown.classList.toggle('hidden');
    }
    document.addEventListener('click', function(event) {
      const userMenuBtn = document.getElementById('user-menu-btn');
      const userDropdown = document.getElementById('user-dropdown');
      if (!userMenuBtn.contains(event.target) && !userDropdown.contains(event.target)) {
        userDropdown.classList.add('hidden');
      }
    });
    document.addEventListener('DOMContentLoaded', function() {
      const downloadBtn = document.getElementById('download-btn');
      if (downloadBtn) {
        downloadBtn.style.display = 'block';
      }
    });
  </script>


  <main class="flex-grow pt-24 pb-12">
  <div class="container">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    <div class="account-nav rounded-lg overflow-hidden h-fit sticky top-24">
      <div class="p-4 border-b border-gray-700">
      <div class="flex items-center">
        <div class="profile-image-container">
          <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" 
             alt="User" class="w-12 h-12 rounded-full mr-3">
          <div class="edit-profile-image" onclick="document.getElementById('profile-image-input').click()">
            <i class="fas fa-pencil-alt text-xs"></i>
          </div>
          <form id="profile-image-form" method="post" enctype="multipart/form-data" style="display: none;">
            <input type="file" id="profile-image-input" name="profile_image" accept="image/*" onchange="document.getElementById('profile-image-form').submit()">
          </form>
        </div>
        <div>
        <h3 class="font-semibold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
        <p class="text-gray-400 text-sm">
          Member since <?php echo date('Y', strtotime($user['registration_date'])); ?>
        </p>
        </div>
      </div>
      </div>
      <nav>
      <a href="#profile" class="active">
        <i class="fas fa-user-circle mr-3"></i> Profile
      </a>
      <a href="#booking">
        <i class="fas fa-calendar-alt mr-3"></i> My Bookings
      </a>
      
      <a href="#preferences">
        <i class="fas fa-star mr-3"></i> Preferences
      </a>
      
      <a href="register.php">
        <i class="fas fa-sign-out-alt mr-3"></i> Logout
      </a>
      </nav>
    </div>
    
    <div class="lg:col-span-3">
      <h1 class="text-2xl md:text-3xl font-bold mb-6">My Account</h1>
      
      <div class="account-card p-6 mb-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <h2 class="mb-4 md:mb-0" id="profile">Profile Information</h2>
        <button id="edit-profile-btn" type="button" class="btn py-2 px-4 text-sm md:w-auto w-full">
        <i class="fas fa-pen mr-2"></i> Edit Profile
        </button>
      </div>
      
            <?php if (!empty($profile_error)): ?>
              <div class="alert alert-danger mb-4"><?php echo htmlspecialchars($profile_error); ?></div>
            <?php elseif (!empty($profile_success)): ?>
              <div class="alert alert-success mb-4"><?php echo htmlspecialchars($profile_success); ?></div>
            <?php endif; ?>
            <form id="profile-form" method="POST" action="" class="space-y-0" autocomplete="off">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium mb-2">First Name</label>
                <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>"
                class="bg-[rgba(255,255,255,0.1)] p-3 rounded w-full" readonly>
              </div>
              <div>
                <label class="block text-sm font-medium mb-2">Last Name</label>
                <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>"
                class="bg-[rgba(255,255,255,0.1)] p-3 rounded w-full" readonly>
              </div>
              <div>
                <label class="block text-sm font-medium mb-2">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                class="bg-[rgba(255,255,255,0.1)] p-3 rounded w-full" readonly>
              </div>
              <div>
                <label class="block text-sm font-medium mb-2">Phone</label>
                <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                class="bg-[rgba(255,255,255,0.1)] p-3 rounded w-full" readonly>
              </div>
              <div class="md:col-span-2 hidden" id="password-fields">
                <label class="block text-sm font-medium mb-2">Current Password <span class="text-red-500">*</span></label>
                <div class="flex items-center space-x-2">
              <input type="password" name="current_password" id="current_password"
                class="bg-[rgba(255,255,255,0.1)] p-3 rounded w-full" required>
                </div>
              </div>
              <div class="md:col-span-2 hidden" id="password-fields-new">
                <label class="block text-sm font-medium mb-2">New Password</label>
                <div class="flex items-center space-x-2">
              <input type="password" name="new_password" id="new_password"
                class="bg-[rgba(255,255,255,0.1)] p-3 rounded w-full">
                </div>
              </div>
              </div>
              
              <div class="flex justify-end mt-6 space-x-2">
              <button type="button" id="cancel-profile-btn" class="btn hidden">
                <i class="fas fa-times mr-2"></i> Cancel
              </button>
              <button type="submit" id="save-profile-btn" class="btn hidden">
                <i class="fas fa-save mr-2"></i> Save Profile
              </button>
              </div>
            </form>
            </div>
            <script>
            const editBtn = document.getElementById('edit-profile-btn');
            const saveBtn = document.getElementById('save-profile-btn');
            const cancelBtn = document.getElementById('cancel-profile-btn');
            const passwordFields = document.getElementById('password-fields');
            const passwordFieldsNew = document.getElementById('password-fields-new');
            const form = document.getElementById('profile-form');
            const inputs = [
              document.getElementById('first_name'),
              document.getElementById('last_name'),
              document.getElementById('email'),
              document.getElementById('phone')
            ];

            let originalValues = {};
            inputs.forEach(input => {
              originalValues[input.name] = input.value;
            });

            editBtn.addEventListener('click', function() {
              inputs.forEach(input => input.removeAttribute('readonly'));
              saveBtn.classList.remove('hidden');
              cancelBtn.classList.remove('hidden');
              editBtn.classList.add('hidden');
              passwordFields.classList.remove('hidden');
              passwordFields.style.display = '';
              passwordFieldsNew.classList.remove('hidden');
              passwordFieldsNew.style.display = '';
              document.getElementById('current_password').value = '';
              document.getElementById('new_password').value = '';
            });

            cancelBtn.addEventListener('click', function() {
              inputs.forEach(input => {
                input.value = originalValues[input.name];
                input.setAttribute('readonly', true);
              });
              saveBtn.classList.add('hidden');
              cancelBtn.classList.add('hidden');
              editBtn.classList.remove('hidden');
              passwordFields.classList.add('hidden');
              passwordFields.style.display = 'none';
              passwordFieldsNew.classList.add('hidden');
              passwordFieldsNew.style.display = 'none';
              document.getElementById('current_password').value = '';
              document.getElementById('new_password').value = '';
            });

            form.addEventListener('submit', function(e) {
              if (!editBtn.classList.contains('hidden')) {
                return;
              }
              const currentPassword = document.getElementById('current_password').value;
              if (!currentPassword) {
                alert('Please enter your current password to save changes.');
                document.getElementById('current_password').focus();
                e.preventDefault();
              }
            });
            </script>
      
      <div class="account-card p-6 mb-8">
      <h2 class="mb-6" id="booking">Upcoming Bookings</h2>
      <div class="space-y-4">
        <?php if ($bookings_result->num_rows > 0): ?>
        <?php while($booking = $bookings_result->fetch_assoc()): ?>
          <div class="booking-card bg-[rgba(255,255,255,0.05)] p-4 rounded">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-3">
            <div>
            <h3 class="font-semibold"><?php echo htmlspecialchars($booking['type_name']); ?></h3>
            <p class="text-gray-400 text-sm">
              Booking #<?php 
              echo htmlspecialchars(
                $booking['booking_id'] .
                $booking['user_id'] . 
                $booking['room_id'] .
                date('YmdHis', strtotime($booking['booking_date']))
              ); 
              ?>
            </p>
            </div>
            <span class="bg-<?php echo ($booking['status'] == 'confirmed') ? 'green' : 'blue'; ?>-500 text-white text-xs px-2 py-1 rounded mt-2 md:mt-0">
            <?php echo ucfirst($booking['status']); ?>
            </span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-3">
            <div>
            <label class="block text-sm font-medium mb-1">Check-In</label>
            <p><?php echo date('M j, Y', strtotime($booking['check_in'])); ?></p>
            </div>
            <div>
            <label class="block text-sm font-medium mb-1">Check-Out</label>
            <p><?php echo date('M j, Y', strtotime($booking['check_out'])); ?></p>
            </div>
            <div>
            <label class="block text-sm font-medium mb-1">Total</label>
            <p>$<?php echo number_format($booking['total_price'], 2); ?></p>
            </div>
          </div>
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <a
              onclick="generatePDF(<?php echo $booking['booking_id']; ?>, '<?php echo htmlspecialchars($booking['type_name']); ?>', '<?php echo date('M j, Y', strtotime($booking['check_in'])); ?>', '<?php echo date('M j, Y', strtotime($booking['check_out'])); ?>', <?php echo $booking['total_price']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . ($user['last_name'] ?? '')); ?>')" 
              class="text-blue-400 hover:underline text-sm mb-2 sm:mb-0">
              <i class="fas fa-file-invoice mr-1"></i> View Invoice
            </a>
            <div class="flex space-x-2">
            <button onclick="openModifyModal(<?php echo $booking['booking_id']; ?>, '<?php echo $booking['check_in']; ?>', '<?php echo $booking['check_out']; ?>', <?php echo $booking['total_price']; ?>)" 
                    class="bg-[rgba(255,255,255,0.1)] hover:bg-[rgba(255,255,255,0.2)] px-3 py-1 rounded text-sm transition">
              Modify
            </button>
            <button onclick="openCancelModal(<?php echo $booking['booking_id']; ?>)" 
                    class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm transition">
              Cancel
            </button>
            </div>
          </div>
          </div>
        <?php endwhile; ?>
        <?php else: ?>
        <p class="text-gray-400">You have no upcoming bookings.</p>
        <?php endif; ?>
      </div>
      <button class="btn w-full mt-6">
        <i class="fas fa-search mr-2"></i> View All Bookings
      </button>
      </div>
      
      <div class="account-card p-6">
      <h2 class="mb-6" id="preferences">My Preferences</h2>
      <form method="POST" action="account.php">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <label class="block text-sm font-medium mb-2">Room Preferences</label>
            <div class="form-group">
            <label for="floor_preference">Floor Preference</label>
            <select id="floor_preference" name="floor_preference" class="w-full">
              <option value="">No preference</option>
              <option value="low" <?php echo (isset($prefs['floor_preference']) && $prefs['floor_preference'] == 'low') ? 'selected' : ''; ?>>Low floor</option>
              <option value="middle" <?php echo (isset($prefs['floor_preference']) && $prefs['floor_preference'] == 'middle') ? 'selected' : ''; ?>>Middle floor</option>
              <option value="high" <?php echo (isset($prefs['floor_preference']) && $prefs['floor_preference'] == 'high') ? 'selected' : ''; ?>>High floor</option>
            </select>
            </div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-2">Pillow Type</label>
          <select name="pillow_type" class="w-full">
          <option value="standard" <?php echo ($prefs['pillow_type'] ?? '') == 'standard' ? 'selected' : ''; ?>>Standard</option>
          <option value="firm" <?php echo ($prefs['pillow_type'] ?? '') == 'firm' ? 'selected' : ''; ?>>Firm</option>
          <option value="soft" <?php echo ($prefs['pillow_type'] ?? '') == 'soft' ? 'selected' : ''; ?>>Soft</option>
          <option value="memory_foam" <?php echo ($prefs['pillow_type'] ?? '') == 'memory_foam' ? 'selected' : ''; ?>>Memory Foam</option>
          </select>
        </div>
        </div>
        <div>
        <label class="block text-sm font-medium mb-2">Special Requests</label>
        <textarea name="special_requests" rows="3" class="w-full" placeholder="Any special requests for your stay..."><?php 
          echo htmlspecialchars($prefs['special_requests'] ?? ''); 
        ?></textarea>
        </div>
        <button type="submit" class="btn mt-4" name="save_preferences">
        <i class="fas fa-save mr-2"></i> Save Preferences
        </button>
      </form>
      </div>
    </div>
    </div>
  </div>
  </main>

  <div id="modifyModal" class="modal">
    <div class="modal-content">
      <?php if (isset($_SESSION['booking_message'])): ?>
    <div class="alert <?php echo strpos($_SESSION['booking_message'], 'successfully') !== false ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo $_SESSION['booking_message']; ?>
    </div>
    <?php unset($_SESSION['booking_message']); ?>
<?php endif; ?>
      <span class="close-modal" onclick="closeModifyModal()">&times;</span>
      <h2>Modify Booking</h2>
      <form id="modifyBookingForm" method="post">
        <input type="hidden" name="modify_booking" value="1">
        <input type="hidden" name="booking_id" id="modifyBookingId">
        
        <div class="form-group">
          <label for="new_check_in">New Check-In Date</label>
          <input type="date" id="new_check_in" name="new_check_in" required>
        </div>
        
        <div class="form-group">
          <label for="new_check_out">New Check-Out Date</label>
          <input type="date" id="new_check_out" name="new_check_out" required>
        </div>
        
        <div class="form-group">
          <label>New Total Price</label>
          <p id="newTotalPrice"></p>
        </div>
        
        <button type="submit" class="btn">Save Changes</button>
      </form>
    </div>
  </div>

  <div id="cancelModal" class="modal">
    <div class="modal-content">
      <span class="close-modal" onclick="closeCancelModal()">&times;</span>
      <h2>Cancel Booking</h2>
      <p>Are you sure you want to cancel this booking?</p>
      <form id="cancelBookingForm" method="post">
        <input type="hidden" name="cancel_booking" value="1">
        <input type="hidden" name="booking_id" id="cancelBookingId">
        
        <div class="flex justify-end space-x-2 mt-4">
          <button type="button" class="btn bg-gray-500 hover:bg-gray-600" onclick="closeCancelModal()">No, Keep It</button>
          <button type="submit" class="btn bg-red-500 hover:bg-red-600">Yes, Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <?php include("../includes/footer.php"); ?>

 

  <script>
  
  
 

  function openModifyModal(bookingId, checkIn, checkOut, totalPrice) {
    const modal = document.getElementById('modifyModal');
    document.getElementById('modifyBookingId').value = bookingId;
    document.getElementById('new_check_in').value = checkIn;
    document.getElementById('new_check_out').value = checkOut;
    document.getElementById('newTotalPrice').textContent = '$' + totalPrice.toFixed(2);
    
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('new_check_in').min = today;
    
    document.getElementById('new_check_in').addEventListener('change', updatePrice);
    document.getElementById('new_check_out').addEventListener('change', updatePrice);
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
  }

  function closeModifyModal() {
    document.getElementById('modifyModal').style.display = 'none';
    document.body.style.overflow = 'auto';
  }

  function updatePrice() {
    const checkIn = new Date(document.getElementById('new_check_in').value);
    const checkOut = new Date(document.getElementById('new_check_out').value);
    
    if (checkIn && checkOut && checkOut > checkIn) {
      const nights = Math.round((checkOut - checkIn) / (1000 * 60 * 60 * 24));
      const pricePerNight = parseFloat(document.getElementById('newTotalPrice').textContent.replace('$', '')) / 
                                (Math.round((new Date(document.getElementById('new_check_out').value) - 
                                new Date(document.getElementById('new_check_in').value)) / (1000 * 60 * 60 * 24)));
      const newTotal = nights * pricePerNight;
      document.getElementById('newTotalPrice').textContent = '$' + newTotal.toFixed(2);
    }
  }

  function openCancelModal(bookingId) {
    document.getElementById('cancelBookingId').value = bookingId;
    document.getElementById('cancelModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
  }

  function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
    document.body.style.overflow = 'auto';
  }

  window.onclick = function(event) {
    if (event.target == document.getElementById('modifyModal')) {
      closeModifyModal();
    }
    if (event.target == document.getElementById('cancelModal')) {
      closeCancelModal();
    }
  }

document.addEventListener('DOMContentLoaded', function() {
  const { jsPDF } = window.jspdf;

  const invoicePreview = document.createElement('div');
  invoicePreview.className = 'invoice-preview';
  invoicePreview.innerHTML = `
    <button class="close-preview">&times;</button>
    <div class="invoice-preview-content" id="invoice-preview-content"></div>
  `;
  document.body.appendChild(invoicePreview);
  invoicePreview.style.display = 'none';

  const downloadBtn = document.createElement('button');
  downloadBtn.className = 'download-btn';
  downloadBtn.innerHTML = '<i class="fas fa-download mr-2"></i> Download PDF';
  downloadBtn.style.display = 'none';

  window.generatePDF = function(bookingId, roomType, checkIn, checkOut, totalPrice, customerName) {
    const checkInDate = new Date(checkIn);
    const checkOutDate = new Date(checkOut);
    const nights = Math.round((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));

    const doc = new jsPDF();

    doc.setFontSize(20);
    doc.setTextColor(13, 74, 157);
    doc.text('Grand Horizon Hotel', 105, 20, { align: 'center' });
    doc.setFontSize(12);
    doc.setTextColor(100, 100, 100);
    doc.text('123 Ocean View Drive, Coastal City', 105, 28, { align: 'center' });

    doc.setFontSize(16);
    doc.setTextColor(0, 0, 0);
    doc.text('INVOICE', 105, 40, { align: 'center' });

    doc.setFontSize(10);
    doc.text(`Invoice #: INV-${bookingId}-${new Date().getTime().toString().slice(-4)}`, 15, 50);
    doc.text(`Date: ${new Date().toLocaleDateString()}`, 15, 55);

    doc.text(`Customer: ${customerName}`, 105, 50, { align: 'center' });

    doc.text(`Booking Details:`, 15, 70);
    doc.text(`Room Type: ${roomType}`, 15, 75);
    doc.text(`Check-In: ${checkIn}`, 15, 80);
    doc.text(`Check-Out: ${checkOut}`, 15, 85);
    doc.text(`Nights: ${nights}`, 15, 90);

    doc.autoTable({
      startY: 100,
      head: [['Description', 'Rate', 'Nights', 'Amount']],
      body: [
        [roomType, `$${(totalPrice/nights).toFixed(2)}`, nights, `$${totalPrice.toFixed(2)}`],
        ['Total', '', '', `$${totalPrice.toFixed(2)}`]
      ],
      headStyles: {
        fillColor: [13, 74, 157],
        textColor: [255, 255, 255]
      },
      columnStyles: {
        0: { cellWidth: 'auto' },
        1: { cellWidth: 'auto' },
        2: { cellWidth: 'auto' },
        3: { cellWidth: 'auto' }
      },
      didDrawCell: (data) => {
        if (data.section === 'body' && data.row.index === 1) {
          doc.setFont(undefined, 'bold');
        }
      }
    });

    doc.text('Thank you for choosing Grand Horizon Hotel!', 15, doc.lastAutoTable.finalY + 15);
    doc.text('For any questions, please contact: reservations@grandhorizon.com', 15, doc.lastAutoTable.finalY + 20);

    const previewContent = document.getElementById('invoice-preview-content');
    previewContent.innerHTML = `
      <h2 style="color: #0d4a9d; text-align: center; margin-bottom: 20px;">Grand Horizon Hotel</h2>
      <p style="text-align: center; color: #666; margin-bottom: 30px;">123 Ocean View Drive, Coastal City</p>
      <h3 style="text-align: center; margin-bottom: 30px;">INVOICE</h3>
      <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
        <div>
          <p><strong>Invoice #:</strong> INV-${bookingId}-${new Date().getTime().toString().slice(-4)}</p>
          <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
        </div>
        <div>
          <p><strong>Customer:</strong> ${customerName}</p>
        </div>
      </div>
      <h4 style="margin-bottom: 15px;">Booking Details:</h4>
      <p><strong>Room Type:</strong> ${roomType}</p>
      <p><strong>Check-In:</strong> ${checkIn}</p>
      <p><strong>Check-Out:</strong> ${checkOut}</p>
      <p><strong>Nights:</strong> ${nights}</p>
      <table style="width: 100%; border-collapse: collapse; margin: 30px 0;">
        <thead>
          <tr style="background-color: #0d4a9d; color: white;">
            <th style="padding: 10px; text-align: left;">Description</th>
            <th style="padding: 10px; text-align: right;">Rate</th>
            <th style="padding: 10px; text-align: right;">Nights</th>
            <th style="padding: 10px; text-align: right;">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="padding: 10px; border-bottom: 1px solid #ddd;">${roomType}</td>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">$${(totalPrice/nights).toFixed(2)}</td>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">${nights}</td>
            <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">$${totalPrice.toFixed(2)}</td>
          </tr>
          <tr style="font-weight: bold; background-color: #f5f5f5;">
            <td colspan="3" style="padding: 10px; text-align: right;">Total</td>
            <td style="padding: 10px; text-align: right;">$${totalPrice.toFixed(2)}</td>
          </tr>
        </tbody>
      </table>
      <p style="margin-top: 30px;">Thank you for choosing Grand Horizon Hotel!</p>
      <p>For any questions, please contact: reservations@grandhorizon.com</p>
    `;

    if (!previewContent.querySelector('.download-btn')) {
      downloadBtn.style.display = 'block';
      downloadBtn.style.position = 'static';
      downloadBtn.style.margin = '30px auto 0 auto';
      downloadBtn.style.left = '';
      downloadBtn.style.top = '';
      downloadBtn.style.transform = '';
      downloadBtn.style.width = '100%';
      previewContent.appendChild(downloadBtn);
    }

    invoicePreview.style.display = 'block';
    document.body.style.overflow = 'hidden';

    downloadBtn.onclick = () => {
      doc.save(`GrandHorizon_Invoice_${bookingId}.pdf`);
      invoicePreview.style.display = 'none';
      downloadBtn.style.display = 'none';
      document.body.style.overflow = 'auto';
    };

    document.querySelector('.close-preview').onclick = () => {
      invoicePreview.style.display = 'none';
      downloadBtn.style.display = 'none';
      document.body.style.overflow = 'auto';
    };

    invoicePreview.onclick = (e) => {
      if (e.target === invoicePreview) {
        invoicePreview.style.display = 'none';
        downloadBtn.style.display = 'none';
        document.body.style.overflow = 'auto';
      }
    };
  };
});
  </script>
</body>
</html>
<?php
$conn->close();
?>
