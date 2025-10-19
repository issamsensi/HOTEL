<?php
require("connexion.php");
session_start();
$selected_category = isset($_GET['category']) ? $_GET['category'] : 'all';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>Latest News | Grand Horizon</title>
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

    .card {
      height: 100%;
      display: flex;
      flex-direction: column;
      transition: all 0.3s ease;
      border-radius: 12px;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 30px rgba(13, 74, 157, 0.3);
      border-color: rgba(13, 74, 157, 0.5);
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

    .category-badge {
      transition: all 0.3s;
      font-size: 0.75rem;
      padding: 4px 8px;
      border-radius: 4px;
      text-transform: uppercase;
      font-weight: 600;
      letter-spacing: 0.5px;
      background: rgba(255,255,255,0.1);
    }

    .filter-btn {
      transition: all 0.3s;
      border-radius: 8px;
      padding: 8px 16px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: white;
      text-decoration: none;
      display: inline-block;
      cursor: pointer;
    }

    .filter-btn:hover, .filter-btn.active {
      background: linear-gradient(135deg, var(--primary) 0%, #1a5cb0 100%);
      border-color: transparent;
    }

    @media (max-width: 768px) {
      h2 {
        font-size: 1.75rem;
      }

      .container {
        padding: 0 15px;
      }
    }
  </style>
</head>
<body class="flex flex-col min-h-screen">

  <?php include("../includes/header.php"); ?>

  <main class="flex-grow pt-24 pb-12">
    <div class="container">
      <h1 class="text-3xl md:text-4xl font-bold mb-2 text-center">Latest News & Updates</h1>
      <p class="text-gray-400 text-center max-w-2xl mx-auto mb-10">Stay informed about our latest offerings, events, and hospitality trends</p>

      <div class="flex flex-wrap justify-center gap-3 mb-12">
        <a href="?category=all" class="filter-btn <?php echo $selected_category === 'All' ? 'active' : ''; ?>">All</a>
        <a href="?category=announcement" class="filter-btn <?php echo $selected_category === 'Announcements' ? 'active' : ''; ?>">Announcements</a>
        <a href="?category=promotion" class="filter-btn <?php echo $selected_category === 'Promotion' ? 'active' : ''; ?>">Promotion</a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        if ($selected_category === 'all') {
        $news_sql = "SELECT * FROM news ORDER BY publish_date DESC";
        } else {
          $news_sql = "SELECT * FROM news WHERE category = '$selected_category' ORDER BY publish_date DESC";
        }
        $news_result = $conn->query($news_sql);

        if ($news_result && $news_result->num_rows > 0) {
          while ($news = $news_result->fetch_assoc()) {
        ?>
          <article class="card group h-full">
            <div class="relative">
              <img alt="<?php echo htmlspecialchars($news['title']); ?>" class="w-full h-48 object-cover loaded group-hover:scale-105 transition duration-500" src="../<?php echo htmlspecialchars($news['featured_image']); ?>">
              <?php if (!empty($news['category'])): ?>
                <div class="absolute top-3 left-3 bg-blue-500 text-white px-2 py-1 rounded text-xs font-bold category-badge">
                  <?php echo htmlspecialchars($news['category']); ?>
                </div>
              <?php endif; ?>
            </div>
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
        <?php
          }
        } else {
          echo '<div class="card p-6 text-center text-gray-400">No news available at the moment.</div>';
        }
        ?>

</div>

    </div>
  </main>

  <?php include("../includes/footer.php"); ?>


</body>
</html>
