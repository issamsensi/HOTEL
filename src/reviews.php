<?php
require("connexion.php");
session_start();

$avg_rating = $conn->query("SELECT AVG(rating) as avg FROM reviews")->fetch_assoc()['avg'];
$avg_rating = round($avg_rating, 1);
$total_reviews = $conn->query("SELECT COUNT(*) as count FROM reviews")->fetch_assoc()['count'];
$five_star = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE rating = 5")->fetch_assoc()['count'];

if(isset($_SESSION["user_id"]) && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_review'])) {
    $user_id = $_SESSION["user_id"];
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($conn->real_escape_string($_POST['review'] ?? ''));
    $date_review = date('Y-m-d H:i:s');

    if ($rating >= 1 && $rating <= 5 && $comment !== "") {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, rating, comment, review_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $rating, $comment, $date_review);
        $stmt->execute();
        $stmt->close();
        $success_message = "Thank you for your review!";
    } else {
        $error_message = "Please fill in all fields and select a rating.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>Guest Reviews | Grand Horizon</title>
  <link rel="stylesheet" href="output.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --primary: #0d4a9d;
      --secondary: #0a1a2b;
      --accent: #ffc107;
      --light-bg: rgba(255, 255, 255, 0.05);
      --border-color: rgba(255, 255, 255, 0.1);
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
      color: white;
      min-height: 100vh;
    }

    .container {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .card {
      height: 100%;
      display: flex;
      flex-direction: column;
      transition: all 0.3s ease;
      border-radius: 12px;
      overflow: hidden;
      background: var(--light-bg);
      backdrop-filter: blur(8px);
      border: 1px solid var(--border-color);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 30px rgba(13, 74, 157, 0.3);
      border-color: rgba(13, 74, 157, 0.5);
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

    p {
      color: rgba(255, 255, 255, 0.85);
    }

    .star-rating {
      direction: ltr;
    }

    .star {
      color: #e2e8f0;
      transition: color 0.2s;
    }

    .star:hover,
    .star:hover ~ .star {
      color: var(--accent);
    }

    input[type="radio"]:checked ~ label,
    input[type="radio"]:checked ~ label ~ label {
      color: var(--accent);
    }

    .stat-card {
      background: var(--light-bg);
      backdrop-filter: blur(8px);
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .review-form {
      background: var(--light-bg);
      backdrop-filter: blur(8px);
      border: 1px solid var(--border-color);
    }

    textarea {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border-color);
      color: white;
    }

    textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 2px rgba(13, 74, 157, 0.3);
    }

    .alert {
      border-radius: 8px;
      padding: 12px 16px;
      margin-bottom: 20px;
    }

    .alert-success {
      background: rgba(40, 167, 69, 0.2);
      border: 1px solid rgba(40, 167, 69, 0.3);
      color: #28a745;
    }

    .alert-error {
      background: rgba(220, 53, 69, 0.2);
      border: 1px solid rgba(220, 53, 69, 0.3);
      color: #dc3545;
    }

    .alert-warning {
      background: rgba(255, 193, 7, 0.2);
      border: 1px solid rgba(255, 193, 7, 0.3);
      color: #ffc107;
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
  </style>
</head>
<body class="flex flex-col min-h-screen">

  <?php include("../includes/header.php"); ?>

  <main class="flex-grow pt-24 pb-12">
    <div class="container">
      <section class="text-center mb-16">
        <h1 class="text-3xl md:text-4xl font-bold mb-4">Guest Experiences</h1>
        <p class="text-gray-400 max-w-2xl mx-auto">Discover what our guests say about their stay at Grand Horizon</p>
      </section>

      <section class="mb-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="stat-card p-8 rounded-lg text-center">
            <div class="text-5xl font-bold mb-2"><?= $avg_rating ?>/5</div>
            <div class="flex justify-center mb-4 star-rating">
              <?php
              $full_stars = floor($avg_rating);
              $half_star = ($avg_rating - $full_stars) >= 0.5;

              for ($i = 0; $i < $full_stars; $i++) echo '<i class="fas fa-star text-yellow-500"></i>';
              if ($half_star) echo '<i class="fas fa-star-half-alt text-yellow-500"></i>';
              for ($i = $full_stars + $half_star; $i < 5; $i++) echo '<i class="far fa-star text-yellow-500"></i>';
              ?>
            </div>
            <p class="text-gray-400">Average Rating</p>
          </div>

          <div class="stat-card p-8 rounded-lg text-center">
            <div class="text-5xl font-bold mb-2"><?= $total_reviews ?></div>
            <div class="text-3xl mb-4 text-blue-400">
              <i class="fas fa-comment-alt"></i>
            </div>
            <p class="text-gray-400">Total Reviews</p>
          </div>

          <div class="stat-card p-8 rounded-lg text-center">
            <div class="text-5xl font-bold mb-2"><?= $five_star ?></div>
            <div class="flex justify-center mb-4">
              <?php for ($i = 0; $i < 5; $i++) echo '<i class="fas fa-star text-yellow-500"></i>'; ?>
            </div>
            <p class="text-gray-400">5-Star Reviews</p>
          </div>
        </div>
      </section>

      <section class="mb-16">
        <h2 class="mb-8">Guest Testimonials</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <?php
          $reviews_sql = "SELECT r.*, u.first_name, u.last_name, u.profile_image
                          FROM reviews r
                          JOIN users u ON r.user_id = u.user_id
                          ORDER BY r.rating DESC LIMIT 6";
          $reviews_result = $conn->query($reviews_sql);

          if ($reviews_result && $reviews_result->num_rows > 0) {
              while ($review = $reviews_result->fetch_assoc()) {
                  ?>
                  <div class="card p-6">
                      <div class="flex items-center mb-4">
                            <img src="<?= htmlspecialchars(!empty($review['profile_image']) ? $review['profile_image'] : '../images/profiles/profile.png') ?>" alt="<?= htmlspecialchars($review['first_name'].' '.$review['last_name']) ?>"
                               class="w-12 h-12 rounded-full object-cover mr-4">
                          <div>
                              <h4 class="font-bold"><?= htmlspecialchars($review['first_name'].' '.$review['last_name']) ?></h4>
                              <p class="text-gray-500 text-sm">
                                  <?= date('F j, Y', strtotime($review['review_date'])) ?>
                              </p>
                          </div>
                      </div>

                      <div class="flex mb-4 star-rating">
                          <?php
                          $rating = $review['rating'];
                          for ($i = 1; $i <= 5; $i++) {
                              echo $i <= $rating ? '<i class="fas fa-star text-yellow-500"></i>' : '<i class="far fa-star text-yellow-500"></i>';
                          }
                          ?>
                      </div>

                      <p class="text-gray-300 italic">
                          "<?= htmlspecialchars($review['comment']) ?>"
                      </p>
                  </div>
                  <?php
              }
          } else {
              echo '<div class="col-span-3 card p-6 text-center text-gray-400">No reviews available yet.</div>';
          }
          ?>
        </div>

      </section>

      <section>
        <div class="review-form p-8 rounded-lg">
          <h2 class="text-center mb-8">Share Your Experience</h2>

          <?php if(isset($success_message)): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
          <?php elseif(isset($error_message)): ?>
            <div class="alert alert-error"><?= $error_message ?></div>
          <?php endif; ?>

          <?php if(!isset($_SESSION["user_id"])): ?>
            <div class="alert alert-warning">
              Please <a href="../register.php" class="text-blue-400 underline">log in</a> to leave a review.
            </div>
          <?php endif; ?>

          <form method="POST" class="space-y-6">
            <div class="text-center">
              <label class="block text-sm font-medium mb-2">Your Rating</label>
              <div class="flex flex-row-reverse justify-center star-rating">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                  <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>"
                         class="peer hidden" required <?= (isset($_POST['rating']) && $_POST['rating'] == $i) ? 'checked' : '' ?>>
                  <label for="star<?= $i ?>" class="star cursor-pointer text-3xl px-1">&#9733;</label>
                <?php endfor; ?>
              </div>
            </div>

            <div>
              <label for="review" class="block text-sm font-medium mb-2">Your Review</label>
              <textarea id="review" name="review" rows="5" class="w-full px-4 py-3 rounded"
                        placeholder="Share your experience with us..." required><?= htmlspecialchars($_POST['review'] ?? '') ?></textarea>
            </div>

            <button type="submit" name="submit_review" class="btn w-full" <?= !isset($_SESSION["user_id"]) ? 'disabled' : '' ?>>
              Submit Review
            </button>
          </form>
        </div>
      </section>
    </div>
  </main>

  <?php include("../includes/footer.php"); ?>



  <script>


    document.querySelectorAll('.star-rating label').forEach(star => {
      star.addEventListener('mouseover', function() {
      const rating = this.getAttribute('for').replace('star', '');
      const stars = this.parentElement.querySelectorAll('label');
      stars.forEach((s, i) => {
        if (i >= 5 - rating) {
        s.style.color = '#ffc107';
        }
      });
    });

    document.querySelectorAll('.star-rating input[type="radio"]').forEach(function(radio) {
      radio.addEventListener('change', function() {
      const rating = parseInt(this.value);
      document.querySelectorAll('.star-rating label.star').forEach(function(label) {
        const labelFor = parseInt(label.getAttribute('for').replace('star', ''));
        if (labelFor <= rating) {
        label.classList.add('text-yellow-500');
        } else {
        label.classList.remove('text-yellow-500');
        }
      });
      });
    });

    window.addEventListener('DOMContentLoaded', function() {
      const checked = document.querySelector('.star-rating input[type="radio"]:checked');
      if (checked) {
      const rating = parseInt(checked.value);
      document.querySelectorAll('.star-rating label.star').forEach(function(label) {
        const labelFor = parseInt(label.getAttribute('for').replace('star', ''));
        if (labelFor <= rating) {
        label.classList.add('text-yellow-500');
        } else {
        label.classList.remove('text-yellow-500');
        }
      });
      }
    });

      star.addEventListener('mouseout', function() {
        const stars = this.parentElement.querySelectorAll('label');
        const checked = this.parentElement.querySelector('input:checked');

        stars.forEach(s => {
          s.style.color = checked && s.getAttribute('for') <= checked.value ? '#ffc107' : '#e2e8f0';
        });
      });
    });
  </script>
</body>
</html>
