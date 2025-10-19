<?php
require("connexion.php");

$login_error = $register_error = "";
$register_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password_hash'])) {
            session_start();
            $date_login = date('Y-m-d H:i:s');
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['first_name']." ".$row['last_name'];
            $_SESSION['email'] = $row['email'];
            $req = "UPDATE users SET last_login = '$date_login' WHERE user_id = {$row['user_id']}";
            $conn->query($req);
            header("Location: index.php");
            exit();
        } else {
            $login_error = "Invalid email or password";
        }
    } else {
        $login_error = "Invalid email or password";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $date_login = date('Y-m-d H:i:s');
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $confirm_password = $conn->real_escape_string($_POST['confirm_password']);
    $phone = $conn->real_escape_string($_POST['phone']);

    if ($password !== $confirm_password) {
        $register_error = "Passwords don't match";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = "Invalid email format";
    } else {
        $check_email = $conn->query("SELECT user_id FROM users WHERE email = '$email'");
        if ($check_email->num_rows > 0) {
            $register_error = "Email already registered";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->query("INSERT INTO users (first_name, last_name, email, password_hash, phone, registration_date, last_login)
                                  VALUES ('$first_name', '$last_name', '$email', '$hashed_password', $phone, '$date_login', '$date_login')");

            if ($insert) {
                $register_success = true;
                $user_id = $conn->insert_id;
                session_start();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $first_name." ".$last_name;
                $_SESSION['email'] = $email;
                header("Location: index.php");
                exit();
            } else {
                $register_error = "Registration failed. Please try again.";
            }
        }
    }
}

$active_tab = 'login';
if (isset($_GET['tab'])) {
    $active_tab = ($_GET['tab'] == 'register') ? 'register' : 'login';
} elseif ($register_error || isset($_POST['register'])) {
    $active_tab = 'register';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>Login & Register | Grand Horizon</title>
  <link rel="stylesheet" href="./output.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
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
      background: linear-gradient(to right, var(--primary), #3a7cb0);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .auth-card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      border-radius: 12px;
      transition: all 0.3s ease;
    }

    .auth-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(13, 74, 157, 0.3);
      border-color: rgba(13, 74, 157, 0.5);
    }

    .tab-active {
      background: linear-gradient(135deg, var(--primary) 0%, #1a5cb0 100%);
      color: white;
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

    .error-message {
      color: #ff6b6b;
      font-size: 0.875rem;
      margin-top: 0.5rem;
    }

    .success-message {
      color: #51cf66;
      font-size: 0.875rem;
      margin-top: 0.5rem;
    }
  </style>
</head>
<body class="flex flex-col min-h-screen">

  <header class="py-4">
    <div class="container flex items-center justify-between">
      <a href="index.php" class="logo">Grand Horizon</a>

      <nav class="hidden md:flex space-x-6 lg:space-x-8">
        <a href="index.php">Home</a>
        <a href="index.php#rooms">Rooms</a>
        <a href="index.php#gallery">Gallery</a>
        <a href="index.php#news">News</a>
      </nav>

      <div class="flex items-center space-x-4">
        <button id="menu-btn" class="md:hidden text-white focus:outline-none" onclick="toggleMobileMenu()">
          <i class="fas fa-bars text-2xl"></i>
        </button>
      </div>
    </div>
  </header>

  <div id="mobile-menu" class="fixed inset-0 z-40 md:hidden opacity-0 invisible transition-all duration-400 transform translate-x-full bg-[#0a1a2b]">
    <div class="flex justify-end p-6">
      <button id="close-menu" class="text-white text-3xl focus:outline-none hover:text-blue-400 transition" onclick="toggleMobileMenu()">&times;</button>
    </div>
    <nav class="flex flex-col items-center space-y-8 mt-12 text-xl">
      <a href="index.php" class="text-white hover:text-blue-400 transition" onclick="toggleMobileMenu()">Home</a>
      <a href="index.php#rooms" class="text-white hover:text-blue-400 transition" onclick="toggleMobileMenu()">Rooms</a>
      <a href="index.php#gallery" class="text-white hover:text-blue-400 transition" onclick="toggleMobileMenu()">Gallery</a>
      <a href="index.php#news" class="text-white hover:text-blue-400 transition" onclick="toggleMobileMenu()">News</a>
      <div class="pt-8 border-t border-gray-700 w-3/4 text-center">
        <a href="register.php" class="text-blue-400 hover:underline"><i class="fas fa-sign-in-alt mr-2"></i>Login</a>
      </div>
    </nav>
  </div>

  <main class="flex-grow flex items-center justify-center py-16">
    <div class="container">
      <div class="max-w-4xl mx-auto">
        <div class="flex mb-8">
          <a href="?tab=login" id="login-tab" class="<?php echo ($active_tab == 'login') ? 'tab-active' : 'bg-[rgba(255,255,255,0.05)] hover:bg-[rgba(255,255,255,0.1)]'; ?> flex-1 py-3 px-4 rounded-tl-lg font-medium text-center focus:outline-none transition">
            Sign In
          </a>
          <a href="?tab=register" id="register-tab" class="<?php echo ($active_tab == 'register') ? 'tab-active' : 'bg-[rgba(255,255,255,0.05)] hover:bg-[rgba(255,255,255,0.1)]'; ?> flex-1 py-3 px-4 rounded-tr-lg font-medium text-center focus:outline-none transition">
            Create Account
          </a>
        </div>

        <div class="auth-card p-8 md:p-10 rounded-b-lg rounded-tr-lg">
          <div id="login-form" <?php if ($active_tab != 'login') echo 'class="hidden"'; ?>>
            <h2 class="text-center mb-2">Welcome Back</h2>
            <p class="text-center text-gray-400 mb-8">Sign in to your account to continue</p>

            <?php if ($login_error): ?>
              <div class="error-message text-center mb-4"><?php echo $login_error; ?></div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
              <input type="hidden" name="login" value="1">

              <div>
                <label for="login-email" class="block text-sm font-medium mb-2">Email Address</label>
                <input type="email" id="login-email" name="email" placeholder="Enter your email" class="w-full" required>
              </div>

              <div>
                <label for="login-password" class="block text-sm font-medium mb-2">Password</label>
                <input type="password" id="login-password" name="password" placeholder="Enter your password" class="w-full" required>
                <div class="flex justify-between items-center mt-2">
                  <div class="flex items-center">
                    <input type="checkbox" id="remember-me" class="mr-2">
                    <label for="remember-me" class="text-sm text-gray-400 cursor-pointer">Remember me</label>
                  </div>
                  <a href="#" class="text-sm text-blue-400 hover:underline">Forgot password?</a>
                </div>
              </div>

              <button type="submit" class="btn w-full py-3">
                Sign In
              </button>

              <p class="text-center text-gray-400 text-sm">
                Don't have an account?
                <a href="?tab=register" class="text-blue-400 hover:underline focus:outline-none">Sign up</a>
              </p>
            </form>
          </div>

          <div id="register-form" <?php if ($active_tab != 'register') echo 'class="hidden"'; ?>>
            <h2 class="text-center mb-2">Create Account</h2>
            <p class="text-center text-gray-400 mb-8">Join us to start your journey</p>

            <?php if ($register_error): ?>
              <div class="error-message text-center mb-4"><?php echo $register_error; ?></div>
            <?php endif; ?>

            <?php if ($register_success): ?>
              <div class="success-message text-center mb-4">Registration successful! You can now login.</div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
              <input type="hidden" name="register" value="1">

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="first-name" class="block text-sm font-medium mb-2">First Name</label>
                  <input type="text" id="first-name" name="first_name" placeholder="Enter your first name" class="w-full" required>
                </div>
                <div>
                  <label for="last-name" class="block text-sm font-medium mb-2">Last Name</label>
                  <input type="text" id="last-name" name="last_name" placeholder="Enter your last name" class="w-full" required>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="register-email" class="block text-sm font-medium mb-2">Email Address</label>
                  <input type="email" id="register-email" name="email" placeholder="Enter your email" class="w-full" required>
                </div>
                <div>
                <label for="register-phone" class="block text-sm font-medium mb-2">phone number</label>
                <input type="number" id="register-phone" name="phone" placeholder="Enter your phone number" class="w-full" required>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="register-password" class="block text-sm font-medium mb-2">Password</label>
                  <input type="password" id="register-password" name="password" placeholder="Create a password" class="w-full" required>
                </div>
                <div>
                  <label for="confirm-password" class="block text-sm font-medium mb-2">Confirm Password</label>
                  <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" class="w-full" required>
                </div>
              </div>

              <div class="flex items-center">
                <input type="checkbox" id="terms" class="mr-2" required>
                <label for="terms" class="text-sm text-gray-400 cursor-pointer">
                  I agree to the <a href="#" class="text-blue-400 hover:underline">Terms of Service</a> and <a href="#" class="text-blue-400 hover:underline">Privacy Policy</a>
                </label>
              </div>

              <button type="submit" class="btn w-full py-3">
                Create Account
              </button>

              <p class="text-center text-gray-400 text-sm">
                Already have an account?
                <a href="?tab=login" class="text-blue-400 hover:underline focus:outline-none">Sign in</a>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="bg-[#0a1a2b] py-12 mt-auto">
    <div class="container">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <div>
          <h3 class="text-white text-lg font-bold mb-4">Grand Horizon</h3>
          <p class="text-gray-400 text-sm">Luxury accommodations with world-class amenities in the heart of the city.</p>
          <div class="flex space-x-4 mt-4">
            <a href="#" class="text-gray-400 hover:text-blue-400 transition" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="text-gray-400 hover:text-blue-400 transition" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-gray-400 hover:text-blue-400 transition" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
        <div>
          <h3 class="text-white text-lg font-bold mb-4">Quick Links</h3>
          <ul class="space-y-2">
            <li><a href="index.php" class="text-gray-400 hover:text-blue-400 text-sm transition">Home</a></li>
            <li><a href="index.php#rooms" class="text-gray-400 hover:text-blue-400 text-sm transition">Rooms & Suites</a></li>
            <li><a href="index.php#gallery" class="text-gray-400 hover:text-blue-400 text-sm transition">Photo Gallery</a></li>
            <li><a href="index.php#news" class="text-gray-400 hover:text-blue-400 text-sm transition">Hotel News</a></li>
          </ul>
        </div>
        <div>
          <h3 class="text-white text-lg font-bold mb-4">Contact Us</h3>
          <ul class="space-y-2 text-gray-400 text-sm">
            <li class="flex items-start">
              <i class="fas fa-map-marker-alt mt-1 mr-2 text-blue-400"></i>
              <span>123 Hotel Street, City, Country</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-phone-alt mr-2 text-blue-400"></i>
              <span>+1 234 567 8900</span>
            </li>
            <li class="flex items-center">
              <i class="fas fa-envelope mr-2 text-blue-400"></i>
              <span>info@grandhorizon.com</span>
            </li>
          </ul>
        </div>
        <div>
          <h3 class="text-white text-lg font-bold mb-4">Opening Hours</h3>
          <ul class="space-y-2 text-gray-400 text-sm">
            <li class="flex justify-between">
              <span>Monday - Friday:</span>
              <span>24 hours</span>
            </li>
            <li class="flex justify-between">
              <span>Saturday - Sunday:</span>
              <span>24 hours</span>
            </li>
          </ul>
        </div>
      </div>
      <div class="border-t border-gray-800 pt-6 flex flex-col md:flex-row justify-between items-center text-gray-400 text-sm">
        <p>&copy; <?php echo date('Y'); ?> Grand Horizon Hotel. All rights reserved.</p>
        <div class="flex space-x-4 mt-4 md:mt-0">
          <a href="#" class="hover:text-blue-400 transition">Privacy Policy</a>
          <a href="#" class="hover:text-blue-400 transition">Terms of Service</a>
        </div>
      </div>
    </div>
  </footer>

  <button id="back-to-top" class="fixed bottom-8 right-8 w-12 h-12 rounded-full flex items-center justify-center hidden bg-gradient-to-br from-[#0d4a9d] to-[#1a5cb0] text-white shadow-lg hover:shadow-xl transition-all">
    <i class="fas fa-arrow-up"></i>
  </button>

  <script>
    function toggleMobileMenu() {
      const mobileMenu = document.getElementById('mobile-menu');
      mobileMenu.classList.toggle('opacity-0');
      mobileMenu.classList.toggle('invisible');
      mobileMenu.classList.toggle('translate-x-full');

      if (!mobileMenu.classList.contains('opacity-0')) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = 'auto';
      }
    }

    const backToTopButton = document.getElementById('back-to-top');

    if (backToTopButton) {
      window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
          backToTopButton.classList.remove('hidden');
          backToTopButton.classList.add('flex');
        } else {
          backToTopButton.classList.add('hidden');
          backToTopButton.classList.remove('flex');
        }
      });

      backToTopButton.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }
  </script>
</body>
</html>
<?php
$conn->close();
?>
