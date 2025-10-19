<?php
require("connexion.php");
session_start();

if(!isset($_GET['id'])) {
    header("Location: rooms.php");
    exit();
}

$room_id = intval($_GET['id']);

$room_sql = "SELECT rt.*, r.*
             FROM room_types rt
             JOIN rooms r ON rt.type_id = r.type_id
             WHERE r.room_id = ?";
$stmt = $conn->prepare($room_sql);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room_result = $stmt->get_result();

if($room_result->num_rows == 0) {
    header("Location: rooms.php");
    exit();
}

$room = $room_result->fetch_assoc();

$reviews_sql = "SELECT r.*, u.first_name, u.last_name
                FROM reviews r
                JOIN users u ON r.user_id = u.user_id
                WHERE r.room_id = ?
                ORDER BY r.review_date DESC";
$stmt_reviews = $conn->prepare($reviews_sql);
$stmt_reviews->bind_param("i", $room_id);
$stmt_reviews->execute();
$reviews_result = $stmt_reviews->get_result();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_room'])) {
    if(!isset($_SESSION['user_id'])) {
        $booking_message = "Please login to book this room.";
    } else {
        $user_id = $_SESSION['user_id'];
        $checkin = $_POST['checkin'];
        $checkout = $_POST['checkout'];
        $adults = $_POST['adults'];
        $children = $_POST['children'];
        $nights = 1;
        if ($checkout && $checkin) {
            $nights = (strtotime($checkout) - strtotime($checkin)) / (60 * 60 * 24);
            if ($nights < 1) $nights = 1;
        }
        $price = $room["base_price"] * $nights;

        $availability_sql = "SELECT * FROM bookings
                            WHERE room_id = ?
                            AND NOT (check_out <= ? OR check_in >= ?)";
        $stmt_avail = $conn->prepare($availability_sql);
        $stmt_avail->bind_param("iss", $room_id, $checkin, $checkout);
        $stmt_avail->execute();
        $availability_result = $stmt_avail->get_result();

        if($availability_result->num_rows > 0) {
            $booking_message = "Sorry, the room is not available for the selected dates.";
        } else {
            $date = date("Y-m-d H:i:s");
            $insert_sql = "INSERT INTO bookings (user_id, room_id, check_in, check_out, adults, children, total_price, booking_date)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_sql);
            $stmt_insert->bind_param("iissiids", $user_id, $room_id, $checkin, $checkout, $adults, $children, $price, $date);
            if($stmt_insert->execute()) {
                $booking_message = "Booking successful! Thank you for choosing Grand Horizon.";
            } else {
                $booking_message = "Error processing your booking. Please try again.";
            }
        }
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if(!isset($_SESSION['user_id'])) {
        $review_message = "Please login to submit a review.";
    } else {
        $user_id = $_SESSION['user_id'];
        $rating = intval($_POST['rating']);
        $comment = $conn->real_escape_string($_POST['comment']);

        if($rating >= 1 && $rating <= 5 && !empty($comment)) {
            $insert_review = "INSERT INTO reviews (user_id, room_id, rating, comment, review_date)
                            VALUES ($user_id, $room_id, $rating, '$comment', NOW())";
            if($conn->query($insert_review)) {
                $review_message = "Thank you for your review!";
            } else {
                $review_message = "Error submitting your review.";
            }
        } else {
            $review_message = "Please provide both rating and comment.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title><?php echo htmlspecialchars($room['type_name']); ?> | Grand Horizon</title>
  <link rel="stylesheet" href="output.css">
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
    }

    .room-gallery {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 15px;
      margin-top: 15px;
    }

    .room-gallery img {
      cursor: pointer;
      transition: transform 0.3s;
    }

    .room-gallery img:hover {
      transform: scale(1.05);
    }

    .amenity-icon {
      width: 24px;
      height: 24px;
      margin-right: 10px;
    }

    .star-rating {
      direction: ltr;
    }

    .star {
      color: #e2e8f0;
      transition: color 0.2s;
    }

    input[type="radio"]:checked ~ label,
    input[type="radio"]:checked ~ label ~ label {
      color: var(--accent);
    }

    .booking-form, .review-form {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .alert {
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .alert-success {
      background: rgba(40, 167, 69, 0.2);
      border: 1px solid rgba(40, 167, 69, 0.3);
    }

    .alert-error {
      background: rgba(220, 53, 69, 0.2);
      border: 1px solid rgba(220, 53, 69, 0.3);
    }
     select {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: white !important;
    padding: 10px;
    border-radius: 8px;
    width: 100%;
    outline: none;
    transition: all 0.3s;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
  }

  select:focus {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 2px rgba(13, 74, 157, 0.3);
  }

  select option {
    background: var(--secondary);
    color: white;
  }
  </style>
</head>
<body class="flex flex-col min-h-screen">

  <?php include("../includes/header.php"); ?>

  <main class="flex-grow pt-24 pb-12">
    <div class="container">
      <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
          <li class="inline-flex items-center">
            <a href="rooms.php" class="text-blue-300 hover:text-white">Rooms</a>
          </li>
          <li aria-current="page">
            <div class="flex items-center">
              <span class="mx-2 text-gray-400">/</span>
              <span class="text-white"><?php echo htmlspecialchars($room['type_name']); ?></span>
            </div>
          </li>
        </ol>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <div class="mb-8">
            <img src="../<?php echo htmlspecialchars($room['featured_image']); ?>" alt="<?php echo htmlspecialchars($room['type_name']); ?>" class="w-full h-96 object-cover rounded-lg">
            <?php if(!empty($room['gallery_images'])):
              $gallery_images = explode(',', $room['gallery_images']); ?>
              <div class="room-gallery">
                <?php foreach($gallery_images as $image): ?>
                  <img src="../<?php echo htmlspecialchars(trim($image)); ?>" alt="" class="w-full h-24 object-cover rounded">
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <div class="mb-8">
            <h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($room['type_name']); ?></h2>
            <div class="flex items-center mb-4">
              <div class="flex star-rating mr-4">
                <?php
                $avg_rating = $conn->query("SELECT AVG(rating) as avg FROM reviews WHERE room_id = $room_id")->fetch_assoc()['avg'];
                $avg_rating = round($avg_rating, 1);
                $full_stars = floor($avg_rating);
                $half_star = ($avg_rating - $full_stars) >= 0.5;

                for ($i = 0; $i < $full_stars; $i++) echo '<i class="fas fa-star text-yellow-500"></i>';
                if ($half_star) echo '<i class="fas fa-star-half-alt text-yellow-500"></i>';
                for ($i = $full_stars + $half_star; $i < 5; $i++) echo '<i class="far fa-star text-yellow-500"></i>';
                ?>
              </div>
              <span class="text-gray-400"><?php echo $avg_rating; ?> (<?php echo $reviews_result->num_rows; ?> reviews)</span>
            </div>

            <p class="text-gray-300 mb-6"><?php echo htmlspecialchars($room['description']); ?></p>

            <div class="mb-8">
              <h3 class="text-xl font-bold mb-4">Amenities</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php if(!empty($room['amenities'])):
                  $amenities = explode(',', $room['amenities']); ?>
                  <?php foreach($amenities as $amenity): ?>
                    <div class="flex items-center">
                      <i class="fas fa-check amenity-icon text-green-500"></i>
                      <span><?php echo htmlspecialchars(trim($amenity)); ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="mb-8">
            <h3 class="text-xl font-bold mb-4">Guest Reviews</h3>

            <?php if($reviews_result->num_rows > 0): ?>
              <div class="space-y-6">
                <?php while($review = $reviews_result->fetch_assoc()): ?>
                  <div class="bg-[rgba(255,255,255,0.05)] p-4 rounded-lg">
                    <div class="flex items-center mb-2">
                      <img src="../images/profiles/profile.png" alt="<?php echo htmlspecialchars($review['first_name'].' '.$review['last_name']); ?>" class="w-10 h-10 rounded-full mr-3">
                      <div>
                        <h4 class="font-bold"><?php echo htmlspecialchars($review['first_name'].' '.$review['last_name']); ?></h4>
                        <div class="flex star-rating">
                          <?php for($i=1; $i<=5; $i++): ?>
                            <?php if($i <= $review['rating']): ?>
                              <i class="fas fa-star text-yellow-500"></i>
                            <?php else: ?>
                              <i class="far fa-star text-yellow-500"></i>
                            <?php endif; ?>
                          <?php endfor; ?>
                        </div>
                      </div>
                    </div>
                    <p class="text-gray-300 italic">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                    <p class="text-gray-500 text-sm mt-2"><?php echo date('F j, Y', strtotime($review['review_date'])); ?></p>
                  </div>
                <?php endwhile; ?>
              </div>
            <?php else: ?>
              <p class="text-gray-400">No reviews yet for this room.</p>
            <?php endif; ?>
          </div>

          <div class="review-form p-6 rounded-lg">
            <h3 class="text-xl font-bold mb-4">Add Your Review</h3>

            <?php if(isset($review_message)): ?>
              <div class="alert <?php echo strpos($review_message, 'Thank you') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $review_message; ?>
              </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['user_id'])): ?>
              <form method="POST">
                <div class="mb-4">
                  <label class="block text-sm font-medium mb-2">Your Rating</label>
                  <div class="flex flex-row-reverse justify-start star-rating">
                    <?php for($i=5; $i>=1; $i--): ?>
                      <input type="radio" id="rating-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" class="peer hidden">
                      <label for="rating-<?php echo $i; ?>" class="star cursor-pointer text-2xl px-1">&#9733;</label>
                    <?php endfor; ?>
                  </div>
                </div>

                <div class="mb-4">
                  <label for="comment" class="block text-sm font-medium mb-2">Your Review</label>
                  <textarea id="comment" name="comment" rows="4" class="w-full px-4 py-2 rounded bg-[rgba(255,255,255,0.05)] border border-[rgba(255,255,255,0.1)]" required></textarea>
                </div>

                <button type="submit" name="submit_review" class="btn px-6 py-2 bg-blue-600 hover:bg-blue-700">
                  Submit Review
                </button>
              </form>
            <?php else: ?>
              <p class="text-gray-400">Please <a href="../login.php" class="text-blue-400 underline">login</a> to leave a review.</p>
            <?php endif; ?>
          </div>
        </div>

        <div>
          <div class="booking-form p-6 rounded-lg sticky top-24">
            <h3 class="text-xl font-bold mb-4">Book This Room</h3>

            <?php if(isset($booking_message)): ?>
              <div class="alert <?php echo strpos($booking_message, 'successful') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $booking_message; ?>
              </div>
            <?php endif; ?>

            <div class="mb-4">
              <div class="flex justify-between items-center mb-2">
                <span class="text-gray-400">Price per night:</span>
                <span class="text-xl font-bold">$<?php echo htmlspecialchars($room['base_price']); ?></span>
              </div>
              <div class="flex justify-between items-center mb-4">
                <span class="text-gray-400">Room Size:</span>
                <span><?php echo htmlspecialchars($room['size']); ?> sq.ft</span>
              </div>
              <div class="flex justify-between items-center mb-4">
                <span class="text-gray-400">Max Guests:</span>
                <span><?php echo htmlspecialchars($room['capacity']); ?></span>
              </div>

            </div>

            <form method="POST">
              <div class="mb-4">
                <label for="checkin" class="block text-sm font-medium mb-2">Check-in Date</label>
                <input type="date" id="checkin" name="checkin" class="w-full px-4 py-2 rounded bg-[rgba(255,255,255,0.05)] border border-[rgba(255,255,255,0.1)]" min="<?php echo date('Y-m-d'); ?>" required>
              </div>

              <div class="mb-4">
                <label for="checkout" class="block text-sm font-medium mb-2">Check-out Date</label>
                <input type="date" id="checkout" name="checkout" class="w-full px-4 py-2 rounded bg-[rgba(255,255,255,0.05)] border border-[rgba(255,255,255,0.1)]" required>
              </div>

              <div class="flex-1 min-w-[200px]">
  <select name="adults" aria-label="Adults"
    class="w-full bg-white text-gray-900 text-sm rounded-md px-4 py-3 cursor-pointer border border-gray-700 focus:border-blue-500 outline-none transition"
    required>
    <option value="">Adults</option>
    <option value="1" <?php if(isset($_POST['adults']) && $_POST['adults']=="1") echo "selected"; ?>>1</option>
    <option value="2" <?php if(isset($_POST['adults']) && $_POST['adults']=="2") echo "selected"; ?>>2</option>
    <option value="3" <?php if(isset($_POST['adults']) && $_POST['adults']=="3") echo "selected"; ?>>3</option>
    <option value="4" <?php if(isset($_POST['adults']) && $_POST['adults']=="4") echo "selected"; ?>>4</option>
  </select>
</div>
              <div class="flex-1 min-w-[200px]">
  <select name="children" aria-label="Children"
    class="w-full bg-white text-gray-900 text-sm rounded-md px-4 py-3 cursor-pointer border border-gray-700 focus:border-blue-500 outline-none transition">
    <option value="">Children</option>
    <option value="0" <?php if(isset($_POST['children']) && $_POST['children']=="0") echo "selected"; ?>>0</option>
    <option value="1" <?php if(isset($_POST['children']) && $_POST['children']=="1") echo "selected"; ?>>1</option>
    <option value="2" <?php if(isset($_POST['children']) && $_POST['children']=="2") echo "selected"; ?>>2</option>
    <option value="3" <?php if(isset($_POST['children']) && $_POST['children']=="3") echo "selected"; ?>>3</option>
    <option value="4" <?php if(isset($_POST['children']) && $_POST['children']=="4") echo "selected"; ?>>4</option>
  </select>
</div>


              <button type="submit" name="book_room" class="btn w-full py-3 bg-blue-600 hover:bg-blue-700">
                Book Now
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include("../includes/footer.php"); ?>

  <script>
    document.querySelectorAll('.room-gallery img').forEach(img => {
      img.addEventListener('click', function() {
        const mainImg = document.querySelector('.room-gallery').previousElementSibling;
        const tempSrc = mainImg.src;
        mainImg.src = this.src;
        this.src = tempSrc;
      });
    });

    document.getElementById('checkin').addEventListener('change', function() {
      const checkout = document.getElementById('checkout');
      checkout.min = this.value;
      if(checkout.value && checkout.value < this.value) {
        checkout.value = this.value;
      }
    });
  </script>
</body>
</html>
