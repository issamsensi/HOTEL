<?php
require("connexion.php");
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>Luxury Hotel | Grand Horizon</title>
  <link rel="stylesheet" href="./output.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link href="../assets/css/index.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
  
</head>
<body class="flex flex-col min-h-screen">

  <div class="top-notification bg-gradient-to-r from-blue-900 to-blue-700">
    <div class="container flex justify-between items-center py-2 text-sm">
      <span>Get 20% discount when booking for 5+ nights!</span>
      <div class="flex items-center">
        <a href="#" class="font-bold underline hover:text-blue-300 ml-2">Book Now</a>
        <i class="fas fa-times cursor-pointer hover:text-red-400 ml-4" onclick="this.parentElement.parentElement.style.display='none'"></i>
      </div>
    </div>
  </div>

  <?php include("../includes/header.php") ?>
  <main class="flex-grow">
    <section id="availability" class="mb-16">
      <div class="container">
        <h2 class="text-center mb-10">Check Room Availability</h2>
        <?php
        $availability_message = "";
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['checkin'], $_POST['checkout'], $_POST['adults'], $_POST['children'])) {
            $checkin = htmlspecialchars($_POST['checkin']);
            $checkout = htmlspecialchars($_POST['checkout']);
            $adults = htmlspecialchars($_POST['adults']);
            $children = htmlspecialchars($_POST['children']);

            if (empty($checkin) || empty($checkout) || empty($adults) || $adults === "") {
                $availability_message = '<div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">Please fill in all required fields.</div>';
            } else {
                $availability_message = '<div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">Rooms are available for your selected dates!</div>';
            }
        }
        if (!empty($availability_message)) {
            echo $availability_message;
        }
        ?>
        <form action="" class="flex flex-col sm:flex-row flex-wrap justify-center gap-4 max-w-4xl mx-auto" method="POST">
          <div class="flex-1 min-w-[200px]">
              <input aria-label="Check In" class="bg-transparent placeholder-gray-400 text-white text-sm w-full outline-none" name="checkin" placeholder="Check In" type="date" value="<?php echo isset($_POST['checkin']) ? htmlspecialchars($_POST['checkin']) : ''; ?>" required />
          </div>
          <div class="flex-1 min-w-[200px]">
              <input aria-label="Check Out" class="bg-transparent placeholder-gray-400 text-white text-sm w-full outline-none" name="checkout" placeholder="Check Out" type="date" value="<?php echo isset($_POST['checkout']) ? htmlspecialchars($_POST['checkout']) : ''; ?>" required />
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

          <div class="w-full sm:w-auto">
            <button class="btn w-full sm:w-auto px-8 py-3" type="submit">
              Search <i class="fas fa-search ml-2"></i>
            </button>
          </div>
        </form>
      </div>
    </section>


    <section id="rooms" class="mb-16">
      <div class="container">
      <div class="flex justify-between items-center mb-8">
        <h2>Our Luxury Rooms</h2>
        <a href="rooms.php" class="text-blue-400 text-sm font-semibold hover:underline flex items-center">
        View All <i class="fas fa-arrow-right ml-1 text-xs"></i>
        </a>
      </div>
      <div class="swiper rooms-swiper">
        <div class="swiper-wrapper">
        <?php
        $sql = "SELECT * FROM room_types rt, rooms r where rt.type_id = r.type_id LIMIT 6";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
          while ($room = $result->fetch_assoc()) {
          ?>
          <div class="swiper-slide">
            <div class="card">
            <div class="relative">
              <img alt="<?php echo htmlspecialchars($room['type_name']); ?>" class="w-full h-64 object-cover loaded" src="../<?php echo htmlspecialchars($room['featured_image']); ?>">
              <?php if (!empty($room['badge'])): ?>
              <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold room-badge">
                <?php echo htmlspecialchars($room['badge']); ?>
              </div>
              <?php endif; ?>
              <?php if (!empty($room['has_360'])): ?>
              <div class="absolute bottom-3 right-3 bg-black bg-opacity-70 text-white px-2 py-1 rounded text-xs">
                <i class="fas fa-expand-arrows-alt mr-1"></i> 360° View
              </div>
              <?php endif; ?>
            </div>
            <div class="p-4 flex-grow">
              <h3 class="text-white text-lg font-bold mb-2"><?php echo htmlspecialchars($room['type_name']); ?></h3>
              <p class="text-gray-400 text-sm mb-3"><?php echo htmlspecialchars($room['description']); ?></p>
              
              <div class="flex justify-between items-center">
              <div>
                <span class="text-white font-bold text-lg">$<?php echo htmlspecialchars($room['base_price']); ?></span>
                <span class="text-gray-400 text-sm ml-1">/night</span>
              </div>
              <a href="room-details.php?id=<?php echo $room['room_id']; ?>" class="btn py-2 px-4 text-sm inline-flex items-center">
  View Details <i class="fas fa-arrow-right ml-2"></i>
</a>
              </div>
            </div>
            </div>
          </div>
          <?php
          }
        } else {
          echo '<div class="swiper-slide"><div class="card p-6 text-center text-gray-400">No rooms available at the moment.</div></div>';
        }
        ?>
        </div>
        <div class="swiper-pagination mt-6"></div>
      </div>
      </div>
    </section>
    <section id="services" class="mb-16">
      <div class="container">
      <h2 class="text-center mb-10">Our Services</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        $services_sql = "SELECT * FROM services LIMIT 6";
        $services_result = $conn->query($services_sql);

        if ($services_result && $services_result->num_rows > 0) {
          while ($service = $services_result->fetch_assoc()) {
            ?>
            <div class="card p-6 group">
              <div class="flex items-center mb-4">
                <div class="bg-blue-900 bg-opacity-30 p-3 rounded-full mr-4 group-hover:bg-opacity-50 transition">
                  <i class="fas <?php echo htmlspecialchars($service['icon']); ?> text-blue-400 text-xl"></i>
                </div>
                <h3 class="text-white text-lg font-bold"><?php echo htmlspecialchars($service['name']); ?></h3>
              </div>
              <p class="text-gray-400">
                <?php echo htmlspecialchars($service['description']); ?>
              </p>
            </div>
            <?php
          }
        } else {
          echo '<div class="card p-6 text-center text-gray-400">No services available at the moment.</div>';
        }
        ?>
        </div>
      </div>
      </div>
    </section>

    <section id="gallery" class="mb-16">
      <div class="container">
      <div class="flex justify-between items-center mb-8">
        <h2>Photo Gallery</h2>
        <a href="gallery.php" class="text-blue-400 text-sm font-semibold hover:underline flex items-center">
        View All <i class="fas fa-arrow-right ml-1 text-xs"></i>
        </a>
      </div>
      <div class="swiper gallery-swiper">
        <div class="swiper-wrapper">
        <?php
        $gallery_sql = "SELECT * FROM gallery LIMIT 10";
        $gallery_result = $conn->query($gallery_sql);

        if ($gallery_result && $gallery_result->num_rows > 0) {
          while ($image = $gallery_result->fetch_assoc()) {
          ?>
          <div class="swiper-slide">
            <div class="relative group rounded-lg overflow-hidden h-64">
            <img alt="<?php echo htmlspecialchars($image['category']); ?>" class="w-full h-full object-cover loaded group-hover:scale-105 transition duration-500" src="../<?php echo htmlspecialchars($image['image_url']); ?>">
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 flex items-center justify-center opacity-0 group-hover:opacity-30 transition cursor-zoom-in">
              <i class="fas fa-search-plus text-white text-2xl"></i>
            </div>
            </div>
          </div>
          <?php
          }
        } else {
          echo '<div class="swiper-slide"><div class="card p-6 text-center text-gray-400">No gallery images available at the moment.</div></div>';
        }
        ?>
        </div>
        <div class="swiper-pagination mt-6"></div>
      </div>
      </div>
    </section>

    <section id="news" class="mb-16">
      <div class="container">
      <div class="flex justify-between items-center mb-8">
        <h2>Latest News</h2>
        <a href="news.php" class="text-blue-400 text-sm font-semibold hover:underline flex items-center">
        View All <i class="fas fa-arrow-right ml-1 text-xs"></i>
        </a>
      </div>
      <div class="swiper news-swiper">
        <div class="swiper-wrapper">
        <?php
        $news_sql = "SELECT * FROM news ORDER BY publish_date DESC LIMIT 6";
        $news_result = $conn->query($news_sql);

        if ($news_result && $news_result->num_rows > 0) {
          while ($news = $news_result->fetch_assoc()) {
          ?>
          <div class="swiper-slide">
            <article class="card group h-full">
            <img alt="<?php echo htmlspecialchars($news['title']); ?>" class="w-full h-48 object-cover loaded group-hover:scale-105 transition duration-500" src="../<?php echo htmlspecialchars($news['featured_image']); ?>">
            <?php if (!empty($news['category'])): ?>
                <div class="absolute top-3 left-3 bg-blue-500 text-white px-2 py-1 rounded text-xs font-bold category-badge">
                  <?php echo htmlspecialchars($news['category']); ?>
                </div>
              <?php endif; ?>
            <div class="p-4 flex-grow">
              <p class="text-blue-400 text-xs font-semibold mb-1">
              <?php echo date('F j, Y', strtotime($news['publish_date'])); ?>
              </p>
              <h3 class="text-white text-lg font-bold mb-2 group-hover:text-blue-400 transition">
              <?php echo htmlspecialchars($news['title']); ?>
              </h3>
              <p class="text-gray-400 text-sm mb-4">
              <?php echo htmlspecialchars($news['content']); ?>
              </p>
              <a href="new-details.php?id=<?php echo (int)$news['news_id']; ?>" class="text-blue-400 text-xs font-semibold uppercase tracking-wide hover:underline flex items-center">
              Read More <i class="fas fa-arrow-right ml-1 text-xs"></i>
              </a>
            </div>
            </article>
          </div>
          <?php
          }
        } else {
          echo '<div class="swiper-slide"><div class="card p-6 text-center text-gray-400">No news available at the moment.</div></div>';
        }
        ?>
        </div>
        <div class="swiper-pagination mt-6"></div>
      </div>
      </div>
    </section>

    <section id="reviews" class="mb-16">
      <div class="container">
        <div class="flex justify-between items-center mb-8">
      <h2 class="text-center mb-10">Guest Reviews</h2>
      <a href="reviews.php" class="text-blue-400 text-sm font-semibold hover:underline flex items-center">
        View All <i class="fas fa-arrow-right ml-1 text-xs"></i>
        </a>
        </div>
      <div class="swiper reviews-swiper">
        <div class="swiper-wrapper">
        <?php
        $reviews_sql = "SELECT * FROM reviews r, users u where u.user_id = r.user_id ORDER BY rating DESC LIMIT 8";
        $reviews_result = $conn->query($reviews_sql);

        if ($reviews_result && $reviews_result->num_rows > 0) {
          while ($review = $reviews_result->fetch_assoc()) {
          ?>
          <div class="swiper-slide">
            <div class="card p-6 h-full">
            <div class="flex items-center mb-4 text-yellow-400">
              <?php
              $stars = floor($review['rating']);
              $half = ($review['rating'] - $stars) >= 0.5;
              for ($i = 0; $i < $stars; $i++) echo '<i class="fas fa-star"></i>';
              if ($half) echo '<i class="fas fa-star-half-alt"></i>';
              for ($i = $stars + $half; $i < 5; $i++) echo '<i class="far fa-star"></i>';
              ?>
            </div>
            <p class="text-gray-300 mb-4 italic">
              "<?php echo htmlspecialchars($review['comment']); ?>"
            </p>
            <div class="flex items-center">
<img src="<?= htmlspecialchars(!empty($review['profile_image']) ? $review['profile_image'] : '../images/profiles/profile.png') ?>" alt="<?= htmlspecialchars($review['first_name'].' '.$review['last_name']) ?>" 
                               class="w-12 h-12 rounded-full object-cover mr-4">              <div>
              <p class="text-white font-semibold"><?php echo htmlspecialchars($review['first_name']." ".$review['last_name']); ?></p>
              <p class="text-gray-400 text-sm">
                <?php
                if (!empty($review['review_date'])){
                echo "Reviewed on " . date('F Y', strtotime($review['review_date']));
                }
                ?>
              </p>
              </div>
            </div>
            </div>
          </div>
          <?php
          }
        } else {
          echo '<div class="swiper-slide"><div class="card p-6 text-center text-gray-400">No reviews available at the moment.</div></div>';
        }
        ?>
        </div>
        <div class="swiper-pagination mt-6"></div>
      </div>
      </div>
    </section>

<section class="mb-16" id="reviews">
  <div class="container">
    <div class="review-form p-8 rounded-lg max-w-4xl mx-auto bg-gray-100">
      <h2 class="text-center mb-6 text-2xl font-semibold">Share Your Experience</h2>

      <?php
      
      if(isset($_SESSION["user_id"])) {
        
        $user_id = $_SESSION["user_id"];
        
      
      
      $review_message = "";
      if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_review'])) {
        $rating = intval($_POST['rating'] ?? 0);
        $comment = trim($_POST['review'] ?? '');
        $date_review = date('Y-m-d H:i:s');
      
        if(isset($_POST['submit_review'])) {
          
        if ($rating < 1 || $rating > 5 || $comment === "") {
          $review_message = '<div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">Please fill in all fields and select a rating.</div>';
        } else {
          $stmt = $conn->query("INSERT INTO reviews (user_id, rating, comment, review_date) VALUES ($user_id, $rating, '$comment', '$date_review')");
          if ($stmt) {
              $review_message = '<div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">Thank you for your review!</div>';
            
          } else {
            $review_message = '<div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">Database error. Please try again later.</div>';
          }
        }
      }
      }
    }else {
        $review_message = '<div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">Please <a href="register.php" class="text-blue-500 underline">log in</a> to leave a review.</div>';
      }
      if (!empty($review_message)) {
        echo $review_message;
      }
      ?>

      <form class="space-y-6" method="POST" action="#reviews">
        <div>
          <label class="block text-sm font-medium mb-2">Your Rating</label>
          <div class="flex flex-row-reverse justify-center">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" class="peer hidden" required <?= (isset($_POST['rating']) && intval($_POST['rating']) === $i) ? 'checked' : '' ?>>
              <label for="star<?= $i ?>" class="star cursor-pointer text-3xl text-white peer-checked:text-yellow-400">&#9733;</label>
            <?php endfor; ?>
          </div>
        </div>

        <div>
          <label for="review" class="block text-sm font-medium mb-2">Your Review</label>
          <textarea id="review" name="review" rows="4" class="w-full px-4 py-3 rounded border" required><?php echo htmlspecialchars($_POST['review'] ?? ''); ?></textarea>
        </div>

        <button type="submit" name="submit_review" class="btn w-full py-3 px-6 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition">
          Submit Review
        </button>
      </form>
    </div>
  </div>
</section>

<style>
  .star {
    transition: color 0.2s;
  }

  .star:hover,
  .star:hover ~ .star {
    color: #fbbf24;
  }

  input[type="radio"]:checked ~ label,
  input[type="radio"]:checked ~ label ~ label {
    color: #fbbf24;
  }
</style>


    <section id="newsletter" class="mb-16">
      <div class="container">
      <div class="bg-[#0a1a2b] p-8 rounded-lg shadow-lg text-center max-w-2xl mx-auto">
        <h2 class="mb-4">Subscribe to Our Newsletter</h2>
        <p class="text-gray-400 mb-6">Stay updated with our latest offers and news</p>
        <?php
        $newsletter_message = "";
        $nesletter_date = date('Y-m-d H:i:s');
        
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['newsletter_email'])) {
        $newsletter_email = trim($_POST['newsletter_email']);
        $already_subscribed = $conn->query("SELECT * FROM newsletter WHERE email = '$newsletter_email'");
        if ($newsletter_email === "") {
          $newsletter_message = '<div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">Please enter your email address.</div>';
        } elseif (!filter_var($newsletter_email, FILTER_VALIDATE_EMAIL)) {
          $newsletter_message = '<div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">Please enter a valid email address.</div>';
        } elseif($already_subscribed->num_rows > 0) {
          $newsletter_message = '<div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">You are already subscribed to our newsletter.</div>';
        } else {
          $stmt = $conn->query("INSERT INTO newsletter (email, subscribed_at) VALUES ('$newsletter_email', '$nesletter_date')");
          if ($stmt) {
            $newsletter_message = '<div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">Thank you for subscribing!</div>';
          
          } else {
          $newsletter_message = '<div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">Database error. Please try again later.</div>';
          }
        }
        }
        if (!empty($newsletter_message)) {
        echo $newsletter_message;
        }
        ?>
        <form action="#newsletter" class="flex flex-col sm:flex-row gap-4" method="POST">
        <input type="email" name="newsletter_email" placeholder="Enter your email address" aria-label="Email Address" required class="flex-grow px-4 py-3" value="<?php echo htmlspecialchars($_POST['newsletter_email'] ?? ''); ?>">
        <button type="submit" class="btn px-6 py-3">
          Subscribe
        </button>
        </form>
      </div>
      </div>
    </section>
  </main>

  <?php include("../includes/footer.php") ?>


  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

  <script>
  
    
    document.addEventListener('DOMContentLoaded', () => {
      const commonSwiperConfig = {
        slidesPerView: 1,
        spaceBetween: 20,
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        autoplay: {
          delay: 3000,
          disableOnInteraction: false,
        },
        loop: true,
        breakpoints: {
          640: {
            slidesPerView: 2,
            spaceBetween: 20
          },
          1024: {
            slidesPerView: 3,
            spaceBetween: 30
          }
        }
      };
      
      if (document.querySelector('.rooms-swiper')) {
        new Swiper('.rooms-swiper', commonSwiperConfig);
      }
      
      if (document.querySelector('.gallery-swiper')) {
        new Swiper('.gallery-swiper', {
          ...commonSwiperConfig,
          effect: 'coverflow',
          grabCursor: true,
          centeredSlides: true,
          coverflowEffect: {
            rotate: 0,
            stretch: 0,
            depth: 100,
            modifier: 2,
            slideShadows: true,
          },
        });
      }
      
      if (document.querySelector('.news-swiper')) {
        new Swiper('.news-swiper', commonSwiperConfig);
      }
      
      if (document.querySelector('.reviews-swiper')) {
        new Swiper('.reviews-swiper', {
          ...commonSwiperConfig,
          slidesPerView: 1,
          breakpoints: {
            640: {
              slidesPerView: 2
            },
            1024: {
              slidesPerView: 3
            },
            1280: {
              slidesPerView: 4
            }
          }
        });
      }
      
      const images = document.querySelectorAll('img');
      images.forEach(img => {
        if (img.complete) {
          img.classList.add('loaded');
        } else {
          img.addEventListener('load', function() {
            this.classList.add('loaded');
          });
        }
      });
      
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
          e.preventDefault();
          
          const targetId = this.getAttribute('href');
          if (targetId === '#') return;
          
          const targetElement = document.querySelector(targetId);
          if (targetElement) {
            window.scrollTo({
              top: targetElement.offsetTop - 80,
              behavior: 'smooth'
            });
          }
        });
      });
    });
  </script>
</body>
</html>
