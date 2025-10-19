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
  <title>Photo Gallery | Grand Horizon</title>
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
    
    .gallery-item {
      position: relative;
      border-radius: 12px;
      overflow: hidden;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .gallery-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(13, 74, 157, 0.3);
      border-color: rgba(13, 74, 157, 0.5);
    }
    
    .gallery-item img {
      transition: transform 0.5s ease;
    }
    
    .gallery-item:hover img {
      transform: scale(1.05);
    }
    
    .gallery-caption {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      background: linear-gradient(to top, rgba(10, 26, 43, 0.9), transparent);
      padding: 1.5rem;
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .gallery-item:hover .gallery-caption {
      opacity: 1;
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
    
    @media (max-width: 768px) {
      h2 {
        font-size: 1.75rem;
      }
      
      .container {
        padding: 0 15px;
      }
    }

    .no-results {
      grid-column: 1 / -1;
      text-align: center;
      padding: 2rem;
      color: rgba(255, 255, 255, 0.7);
    }
  </style>
</head>
<body class="flex flex-col min-h-screen">

  <?php include("../includes/header.php"); ?>

  <main class="flex-grow pt-24 pb-12">
    <div class="container">
      <h1 class="text-3xl md:text-4xl font-bold mb-2 text-center">Our Photo Gallery</h1>
      <p class="text-gray-400 text-center max-w-2xl mx-auto mb-10">Explore the beauty and elegance of Grand Horizon through our stunning visual collection</p>
      
      <div class="flex flex-wrap justify-center gap-3 mb-12">
        <a href="?category=all" class="filter-btn <?php echo $selected_category === 'All' ? 'active' : ''; ?>">All</a>
        <a href="?category=room" class="filter-btn <?php echo $selected_category === 'Rooms' ? 'active' : ''; ?>">Rooms</a>
        <a href="?category=dining" class="filter-btn <?php echo $selected_category === 'Dining' ? 'active' : ''; ?>">Dining</a>
        <a href="?category=spa" class="filter-btn <?php echo $selected_category === 'Spa' ? 'active' : ''; ?>">Spa</a>
        <a href="?category=pool" class="filter-btn <?php echo $selected_category === 'Pool' ? 'active' : ''; ?>">Pool</a>
        <a href="?category=events" class="filter-btn <?php echo $selected_category === 'Events' ? 'active' : ''; ?>">Events</a>
        <a href="?category=views" class="filter-btn <?php echo $selected_category === 'Views' ? 'active' : ''; ?>">Views</a>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php
        if ($selected_category === 'all') {
            $gallery_sql = "SELECT * FROM gallery";
        } else {
            $gallery_sql = "SELECT * FROM gallery WHERE category = '$selected_category'";
        }
        
        $gallery_result = $conn->query($gallery_sql);

        if ($gallery_result && $gallery_result->num_rows > 0) {
          while ($image = $gallery_result->fetch_assoc()) {
        ?>
          <div class="gallery-item" data-category="<?php echo htmlspecialchars(strtolower($image['category'])); ?>">
            <img alt="<?php echo htmlspecialchars($image['category']); ?>" class="w-full h-full object-cover" src="../<?php echo htmlspecialchars($image['image_url']); ?>">
            <div class="gallery-caption">
              <h3 class="text-white font-bold"><?php echo htmlspecialchars($image['category']); ?></h3>
              <p class="text-gray-300 text-sm"><?php echo htmlspecialchars($image['description']); ?></p>
            </div>
          </div>
        <?php
          }
        } else {
          echo '<div class="no-results">No images found in this category.</div>';
        }
        ?>
      </div>
    </div>
  </main>

  <?php include("../includes/footer.php"); ?>


  <script>
    

    document.addEventListener('DOMContentLoaded', function() {
      const filterButtons = document.querySelectorAll('.filter-btn');
      
      filterButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          filterButtons.forEach(btn => btn.classList.remove('active'));
          this.classList.add('active');
        });
      });
    });
  </script>
</body>
</html>