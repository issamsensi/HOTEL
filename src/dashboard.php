<?php
require("connexion.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$user_result = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$user = $user_result->fetch_assoc();

if (!$user || $user['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

function sanitizeInput($data) {
    global $conn;
    return htmlspecialchars(strip_tags($conn->real_escape_string($data)));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_booking'])) {
    $booking_id = (int)$_POST['booking_id'];
    $conn->query("DELETE FROM bookings WHERE booking_id = $booking_id");
    $_SESSION['message'] = "Booking deleted successfully";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = sanitizeInput($_POST['new_status']);
    $conn->query("UPDATE bookings SET status = '$new_status' WHERE booking_id = $booking_id");
    $_SESSION['message'] = "Booking status updated";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_room'])) {
    $type_id = (int)$_POST['type_id'];
    $room_number = sanitizeInput($_POST['room_number']);
    $status = sanitizeInput($_POST['status']);
    
    $check = $conn->query("SELECT room_id FROM rooms WHERE room_number = '$room_number'");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Room number already exists";
    } else {
        $conn->query("INSERT INTO rooms (type_id, room_number, status) VALUES ($type_id, '$room_number', '$status')");
        $_SESSION['message'] = "Room added successfully";
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_room_type'])) {
    $type_name = sanitizeInput($_POST['type_name']);
    $description = sanitizeInput($_POST['description']);
    $base_price = (float)$_POST['base_price'];
    $capacity = (int)$_POST['capacity'];
    $size = sanitizeInput($_POST['size']);
    $featured_image = 'images/default.jpg';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "images/";
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('room_', true) . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $featured_image = $target_file;
            }
        }
    }
    
    $check = $conn->query("SELECT type_id FROM room_types WHERE type_name = '$type_name'");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Room type already exists";
    } else {
        $conn->query("INSERT INTO room_types (type_name, description, base_price, capacity, size, featured_image) VALUES ('$type_name', '$description', $base_price, $capacity, '$size', '$featured_image')");
        $_SESSION['message'] = "Room type added successfully";
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_room_type'])) {
    $type_id = (int)$_POST['type_id'];
    
    $check = $conn->query("SELECT room_id FROM rooms WHERE type_id = $type_id");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Cannot delete - rooms exist with this type";
    } else {
        $conn->query("DELETE FROM room_types WHERE type_id = $type_id");
        $_SESSION['message'] = "Room type deleted";
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_room'])) {
    $room_id = (int)$_POST['room_id'];
    
    $check = $conn->query("SELECT booking_id FROM bookings WHERE room_id = $room_id AND status IN ('pending', 'confirmed')");
    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Cannot delete - room has active bookings";
    } else {
        $conn->query("DELETE FROM rooms WHERE room_id = $room_id");
        $_SESSION['message'] = "Room deleted";
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete yourself";
    } else {
        $conn->begin_transaction();
        try {
            $conn->query("DELETE FROM bookings WHERE user_id = $user_id");
            $conn->query("DELETE FROM preferences WHERE user_id = $user_id");
            $conn->query("DELETE FROM reviews WHERE user_id = $user_id");
            $conn->query("DELETE FROM comments WHERE user_id = $user_id");
            $conn->query("DELETE FROM users WHERE user_id = $user_id");
            
            $conn->commit();
            $_SESSION['message'] = "User deleted successfully";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error deleting user";
        }
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = sanitizeInput($_POST['new_role']);
    
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot change your own role";
    } else {
        $conn->query("UPDATE users SET role = '$new_role' WHERE user_id = $user_id");
        $_SESSION['message'] = "User role updated";
    }
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_news'])) {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    $category = sanitizeInput($_POST['category']);
    $author_id = $user['user_id'];
    $featured_image = 'images/news/default.jpg';
    
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "images/news/";
        $file_ext = strtolower(pathinfo($_FILES["featured_image"]["name"], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('news_', true) . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["featured_image"]["tmp_name"], $target_file)) {
                $featured_image = $target_file;
            }
        }
    }
    
    $conn->query("INSERT INTO news (title, content, author_id, category, featured_image) VALUES ('$title', '$content', $author_id, '$category', '$featured_image')");
    $_SESSION['message'] = "News added successfully";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_news'])) {
    $news_id = (int)$_POST['news_id'];
    $conn->query("DELETE FROM news WHERE news_id = $news_id");
    $_SESSION['message'] = "News deleted";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_gallery_image'])) {
    $category = sanitizeInput($_POST['category']);
    $description = sanitizeInput($_POST['description']);
    $room_id = !empty($_POST['room_id']) ? (int)$_POST['room_id'] : 'NULL';
    $image_url = 'images/default.jpg';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "images/gallery/";
        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $new_filename = uniqid('gallery_', true) . '.' . $file_ext;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            }
        }
    }
    
    $conn->query("INSERT INTO gallery (image_url, category, description, room_id) VALUES ('$image_url', '$category', '$description', $room_id)");
    $_SESSION['message'] = "Image added to gallery";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_gallery_image'])) {
    $image_id = (int)$_POST['image_id'];
    $conn->query("DELETE FROM gallery WHERE image_id = $image_id");
    $_SESSION['message'] = "Image deleted from gallery";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_review'])) {
    $review_id = (int)$_POST['review_id'];
    $conn->query("DELETE FROM reviews WHERE review_id = $review_id");
    $_SESSION['message'] = "Review deleted";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM bookings) as total_bookings,
        (SELECT COUNT(*) FROM bookings WHERE status = 'pending') as pending_bookings,
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM rooms) as total_rooms,
        (SELECT COUNT(*) FROM rooms WHERE status = 'available') as available_rooms,
        (SELECT IFNULL(SUM(total_price), 0) FROM bookings WHERE status = 'completed') as revenue,
        (SELECT COUNT(*) FROM reviews) as total_reviews,
        (SELECT COUNT(*) FROM news) as total_news
")->fetch_assoc();

$recent_bookings = $conn->query("
    SELECT b.*, u.first_name, u.last_name, r.room_number, rt.type_name 
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN rooms r ON b.room_id = r.room_id
    JOIN room_types rt ON r.type_id = rt.type_id
    ORDER BY b.booking_date DESC LIMIT 5
");

$new_users = $conn->query("SELECT * FROM users ORDER BY registration_date DESC LIMIT 5");

$room_types = $conn->query("SELECT * FROM room_types");

$rooms = $conn->query("
    SELECT r.*, rt.type_name 
    FROM rooms r
    JOIN room_types rt ON r.type_id = rt.type_id
");

$news = $conn->query("
    SELECT n.*, u.first_name, u.last_name 
    FROM news n 
    JOIN users u ON n.author_id = u.user_id 
    ORDER BY publish_date DESC
");

$gallery = $conn->query("SELECT * FROM gallery ORDER BY upload_date DESC");

$reviews = $conn->query("
    SELECT r.*, u.first_name, u.last_name, rt.type_name 
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    LEFT JOIN rooms rm ON r.room_id = rm.room_id
    LEFT JOIN room_types rt ON rm.type_id = rt.type_id
    ORDER BY review_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Grand Horizon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0d4a9d;
            --secondary: #0a1a2b;
            --accent: #ffc107;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .sidebar {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            color: white;
            width: 250px;
            position: fixed;
            height: 100vh;
            transition: all 0.3s;
            z-index: 100;
        }
        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.2);
        }
        .sidebar-menu {
            padding: 20px 0;
        }
        .sidebar-menu a {
            color: rgba(255,255,255,0.8);
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        .header {
            background: white;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 90;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        .card-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .table th {
            background: #f9f9f9;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
        .badge-confirmed {
            background: #d4edda;
            color: #155724;
        }
        .badge-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .badge-completed {
            background: #d1ecf1;
            color: #0c5460;
        }
        .badge-user {
            background: #e2e3e5;
            color: #383d41;
        }
        .badge-admin {
            background: #d1e7dd;
            color: #0f5132;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: #0b3d7c;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        select.form-control {
            height: 38px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            padding: 20px;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
        }
        .close-modal {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        .gallery-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .gallery-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .gallery-item .actions {
            position: absolute;
            top: 5px;
            right: 5px;
        }
        .gallery-item .info {
            padding: 10px;
            background: white;
        }
        .gallery-item .category {
            font-size: 12px;
            color: var(--primary);
            font-weight: 600;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 10px;
        }
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
            <h2>Grand Horizon</h2>
            <p>Admin Dashboard</p>
        </div>
        <div class="sidebar-menu">
            <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="#bookings"><i class="fas fa-calendar-alt"></i> Bookings</a>
            <a href="#rooms"><i class="fas fa-bed"></i> Rooms</a>
            <a href="#room-types"><i class="fas fa-list"></i> Room Types</a>
            <a href="#users"><i class="fas fa-users"></i> Users</a>
            <a href="#reviews"><i class="fas fa-star"></i> Reviews</a>
            <a href="#gallery"><i class="fas fa-images"></i> Gallery</a>
            <a href="#news"><i class="fas fa-newspaper"></i> News</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h3>Dashboard</h3>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($user['first_name']); ?></span>
                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="User" style="width: 40px; height: 40px; border-radius: 50%; margin-left: 15px;">
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <div class="card stat-card">
                <div class="stat-number"><?php echo $stats['total_bookings']; ?></div>
                <div class="stat-label">Total Bookings</div>
                <i class="fas fa-calendar-alt" style="font-size: 24px; color: var(--primary); margin-top: 10px;"></i>
            </div>
            <div class="card stat-card">
                <div class="stat-number"><?php echo $stats['pending_bookings']; ?></div>
                <div class="stat-label">Pending Bookings</div>
                <i class="fas fa-clock" style="font-size: 24px; color: var(--primary); margin-top: 10px;"></i>
            </div>
            <div class="card stat-card">
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Registered Users</div>
                <i class="fas fa-users" style="font-size: 24px; color: var(--primary); margin-top: 10px;"></i>
            </div>
            <div class="card stat-card">
                <div class="stat-number"><?php echo $stats['total_rooms']; ?></div>
                <div class="stat-label">Total Rooms</div>
                <i class="fas fa-bed" style="font-size: 24px; color: var(--primary); margin-top: 10px;"></i>
            </div>
            <div class="card stat-card">
                <div class="stat-number"><?php echo $stats['available_rooms']; ?></div>
                <div class="stat-label">Available Rooms</div>
                <i class="fas fa-door-open" style="font-size: 24px; color: var(--primary); margin-top: 10px;"></i>
            </div>
            <div class="card stat-card">
                <div class="stat-number">$<?php echo number_format($stats['revenue'], 2); ?></div>
                <div class="stat-label">Total Revenue</div>
                <i class="fas fa-dollar-sign" style="font-size: 24px; color: var(--primary); margin-top: 10px;"></i>
            </div>
            <div class="card stat-card">
                <div class="stat-number"><?php echo $stats['total_reviews']; ?></div>
                <div class="stat-label">Customer Reviews</div>
                <i class="fas fa-star" style="font-size: 24px; color: var(--primary); margin-top: 10px;"></i>
            </div>
            <div class="card stat-card">
                <div class="stat-number"><?php echo $stats['total_news']; ?></div>
                <div class="stat-label">News Articles</div>
                <i class="fas fa-newspaper" style="font-size: 24px; color: var(--primary); margin-top: 10px;"></i>
            </div>
        </div>

        <div class="card" id="bookings">
            <div class="card-header">
                <h4 class="card-title">Recent Bookings</h4>
                <button class="btn btn-primary btn-sm" onclick="openAddBookingModal()">
                    <i class="fas fa-plus"></i> Add Booking
                </button>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Room Type</th>
                        <th>Dates</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $booking['booking_id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['type_name']); ?></td>
                        <td>
                            <?php echo date('M j', strtotime($booking['check_in'])); ?> - 
                            <?php echo date('M j, Y', strtotime($booking['check_out'])); ?>
                        </td>
                        <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($booking['status']); ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="openEditBookingModal(<?php echo $booking['booking_id']; ?>, '<?php echo $booking['status']; ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                <button type="submit" name="delete_booking" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this booking?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="card" id="rooms">
            <div class="card-header">
                <h4 class="card-title">Rooms</h4>
                <button class="btn btn-primary btn-sm" onclick="openAddRoomModal()">
                    <i class="fas fa-plus"></i> Add Room
                </button>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Room ID</th>
                        <th>Room Number</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($room = $rooms->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $room['room_id']; ?></td>
                        <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($room['type_name']); ?></td>
                        <td>
                            <span class="badge <?php echo $room['status'] == 'available' ? 'badge-success' : ($room['status'] == 'occupied' ? 'badge-primary' : 'badge-warning'); ?>">
                                <?php echo ucfirst($room['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                <button type="submit" name="delete_room" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this room?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="card" id="room-types">
            <div class="card-header">
                <h4 class="card-title">Room Types</h4>
                <button class="btn btn-primary btn-sm" onclick="openAddRoomTypeModal()">
                    <i class="fas fa-plus"></i> Add Room Type
                </button>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Type ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Base Price</th>
                        <th>Capacity</th>
                        <th>Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($type = $room_types->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $type['type_id']; ?></td>
                        <td><?php echo htmlspecialchars($type['type_name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($type['description'], 0, 50) . (strlen($type['description']) > 50 ? '...' : '')); ?></td>
                        <td>$<?php echo number_format($type['base_price'], 2); ?></td>
                        <td><?php echo $type['capacity']; ?></td>
                        <td><?php echo htmlspecialchars($type['size']); ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="type_id" value="<?php echo $type['type_id']; ?>">
                                <button type="submit" name="delete_room_type" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this room type?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="card" id="users">
            <div class="card-header">
                <h4 class="card-title">Recent Users</h4>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = $new_users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower($user['role']); ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($user['registration_date'])); ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" onclick="openEditUserModal(<?php echo $user['user_id']; ?>, '<?php echo $user['role']; ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="card" id="reviews">
            <div class="card-header">
                <h4 class="card-title">Recent Reviews</h4>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Review ID</th>
                        <th>User</th>
                        <th>Room Type</th>
                        <th>Rating</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($review = $reviews->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $review['review_id']; ?></td>
                        <td><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($review['type_name'] ?? 'N/A'); ?></td>
                        <td>
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-empty'; ?>" style="color: <?php echo $i <= $review['rating'] ? '#ffc107' : '#ddd'; ?>"></i>
                            <?php endfor; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($review['review_date'])); ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i>
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                                <button type="submit" name="delete_review" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this review?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="card" id="gallery">
            <div class="card-header">
                <h4 class="card-title">Gallery</h4>
                <button class="btn btn-primary btn-sm" onclick="openAddGalleryModal()">
                    <i class="fas fa-plus"></i> Add Image
                </button>
            </div>
            <div class="gallery-grid">
                <?php while($image = $gallery->fetch_assoc()): ?>
                <div class="gallery-item">
                    <img src="../<?php echo htmlspecialchars($image['image_url']); ?>" alt="Gallery Image">
                    <div class="actions">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="image_id" value="<?php echo $image['image_id']; ?>">
                            <button type="submit" name="delete_gallery_image" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this image?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                    <div class="info">
                        <div class="category"><?php echo htmlspecialchars($image['category']); ?></div>
                        <p><?php echo htmlspecialchars(substr($image['description'], 0, 50) . (strlen($image['description']) > 50 ? '...' : '')); ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="card" id="news">
            <div class="card-header">
                <h4 class="card-title">News</h4>
                <button class="btn btn-primary btn-sm" onclick="openAddNewsModal()">
                    <i class="fas fa-plus"></i> Add News
                </button>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>News ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($news_item = $news->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $news_item['news_id']; ?></td>
                        <td><?php echo htmlspecialchars(substr($news_item['title'], 0, 30) . (strlen($news_item['title']) > 30 ? '...' : '')); ?></td>
                        <td><?php echo htmlspecialchars($news_item['first_name'] . ' ' . $news_item['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($news_item['category']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($news_item['publish_date'])); ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="news_id" value="<?php echo $news_item['news_id']; ?>">
                                <button type="submit" name="delete_news" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this news item?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>


    <div class="modal" id="addBookingModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Add New Booking</h4>
                <button class="close-modal" onclick="closeModal('addBookingModal')">&times;</button>
            </div>
            <form method="POST" action="process_booking.php">
                <div class="form-group">
                    <label>User</label>
                    <select class="form-control" name="user_id" required>
                        <option value="">Select User</option>
                        <?php 
                        $users = $conn->query("SELECT user_id, first_name, last_name FROM users");
                        while($user = $users->fetch_assoc()): ?>
                            <option value="<?php echo $user['user_id']; ?>"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room</label>
                    <select class="form-control" name="room_id" required>
                        <option value="">Select Room</option>
                        <?php 
                        $rooms = $conn->query("SELECT r.room_id, r.room_number, rt.type_name FROM rooms r JOIN room_types rt ON r.type_id = rt.type_id WHERE r.status = 'available'");
                        while($room = $rooms->fetch_assoc()): ?>
                            <option value="<?php echo $room['room_id']; ?>"><?php echo htmlspecialchars($room['room_number'] . ' - ' . $room['type_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Check-in Date</label>
                    <input type="date" class="form-control" name="check_in" required>
                </div>
                <div class="form-group">
                    <label>Check-out Date</label>
                    <input type="date" class="form-control" name="check_out" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Special Requests</label>
                    <textarea class="form-control" name="special_requests" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" name="add_booking">Add Booking</button>
            </form>
        </div>
    </div>

    <div class="modal" id="editBookingModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Update Booking Status</h4>
                <button class="close-modal" onclick="closeModal('editBookingModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="booking_id" id="editBookingId">
                <div class="form-group">
                    <label>New Status</label>
                    <select class="form-control" name="new_status" id="editBookingStatus" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" name="update_status">Update Status</button>
            </form>
        </div>
    </div>

    <div class="modal" id="addRoomModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Add New Room</h4>
                <button class="close-modal" onclick="closeModal('addRoomModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Room Type</label>
                    <select class="form-control" name="type_id" required>
                        <option value="">Select Room Type</option>
                        <?php 
                        $types = $conn->query("SELECT type_id, type_name FROM room_types");
                        while($type = $types->fetch_assoc()): ?>
                            <option value="<?php echo $type['type_id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" class="form-control" name="room_number" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status" required>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" name="add_room">Add Room</button>
            </form>
        </div>
    </div>

    <div class="modal" id="addRoomTypeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Add New Room Type</h4>
                <button class="close-modal" onclick="closeModal('addRoomTypeModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Type Name</label>
                    <input type="text" class="form-control" name="type_name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" name="description" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label>Base Price ($)</label>
                    <input type="number" step="0.01" class="form-control" name="base_price" required>
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" class="form-control" name="capacity" required>
                </div>
                <div class="form-group">
                    <label>Size (sq ft)</label>
                    <input type="text" class="form-control" name="size" required>
                </div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" class="form-control" name="image" required>
                </div>
                <button type="submit" class="btn btn-primary" name="add_room_type">Add Room Type</button>
            </form>
        </div>
    </div>

    <div class="modal" id="editUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Update User Role</h4>
                <button class="close-modal" onclick="closeModal('editUserModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="form-group">
                    <label>New Role</label>
                    <select class="form-control" name="new_role" id="editUserRole" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" name="update_role">Update Role</button>
            </form>
        </div>
    </div>

    <div class="modal" id="addNewsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Add News Article</h4>
                <button class="close-modal" onclick="closeModal('addNewsModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea class="form-control" name="content" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" class="form-control" name="category" required>
                </div>
                <div class="form-group">
                    <label>Featured Image</label>
                    <input type="file" class="form-control" name="featured_image">
                </div>
                <button type="submit" class="btn btn-primary" name="add_news">Publish News</button>
            </form>
        </div>
    </div>

    <div class="modal" id="addGalleryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Add Gallery Image</h4>
                <button class="close-modal" onclick="closeModal('addGalleryModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" class="form-control" name="image" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" class="form-control" name="category" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" name="description" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label>Associated Room (optional)</label>
                    <select class="form-control" name="room_id">
                        <option value="">None</option>
                        <?php 
                        $rooms = $conn->query("SELECT r.room_id, r.room_number, rt.type_name FROM rooms r JOIN room_types rt ON r.type_id = rt.type_id");
                        while($room = $rooms->fetch_assoc()): ?>
                            <option value="<?php echo $room['room_id']; ?>"><?php echo htmlspecialchars($room['room_number'] . ' - ' . $room['type_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" name="add_gallery_image">Add Image</button>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        function openAddBookingModal() {
            document.getElementById('addBookingModal').style.display = 'flex';
        }

        function openEditBookingModal(bookingId, currentStatus) {
            document.getElementById('editBookingId').value = bookingId;
            document.getElementById('editBookingStatus').value = currentStatus;
            document.getElementById('editBookingModal').style.display = 'flex';
        }

        function openAddRoomModal() {
            document.getElementById('addRoomModal').style.display = 'flex';
        }

        function openAddRoomTypeModal() {
            document.getElementById('addRoomTypeModal').style.display = 'flex';
        }

        function openEditUserModal(userId, currentRole) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUserRole').value = currentRole;
            document.getElementById('editUserModal').style.display = 'flex';
        }

        function openAddNewsModal() {
            document.getElementById('addNewsModal').style.display = 'flex';
        }

        function openAddGalleryModal() {
            document.getElementById('addGalleryModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>





