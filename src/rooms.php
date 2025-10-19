<?php
require("connexion.php");
session_start();

$room_type = isset($_GET['room_type']) ? $_GET['room_type'] : '';
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$bed_type = isset($_GET['bed_type']) ? $_GET['bed_type'] : '';

$sql = "SELECT * FROM room_types rt, rooms r WHERE rt.type_id = r.type_id";

$conditions = array();

if (!empty($room_type) && $room_type != 'All Types') {
    $conditions[] = "rt.type_name = '" . $conn->real_escape_string($room_type) . "'";
}

if (!empty($price_range) && $price_range != 'All Prices') {
    if ($price_range == '$100 - $200') {
        $conditions[] = "rt.base_price BETWEEN 100 AND 200";
    } elseif ($price_range == '$200 - $300') {
        $conditions[] = "rt.base_price BETWEEN 200 AND 300";
    } elseif ($price_range == '$300 - $400') {
        $conditions[] = "rt.base_price BETWEEN 300 AND 400";
    } elseif ($price_range == '$400+') {
        $conditions[] = "rt.base_price >= 400";
    }
}

if (!empty($bed_type) && $bed_type != 'Any Bed') {
    $conditions[] = "rt.bed_type = '" . $conn->real_escape_string($bed_type) . "'";
}

if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

$sql .= " LIMIT 6";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>All Rooms | Grand Horizon</title>
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

    p {
      color: rgba(255, 255, 255, 0.85);
    }

    .room-badge {
      transition: all 0.3s;
      font-size: 0.75rem;
      padding: 4px 8px;
      border-radius: 4px;
      text-transform: uppercase;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .filter-section {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(8px);
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.1);
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



  .select-wrapper {
    position: relative;
  }

  .select-wrapper::after {
    content: '\f078';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    color: white;
    pointer-events: none;
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
      <h1 class="text-3xl md:text-4xl font-bold mb-2 text-center">Our Luxury Rooms & Suites</h1>
      <p class="text-gray-400 text-center max-w-2xl mx-auto mb-10">Experience unparalleled comfort and elegance in our meticulously designed accommodations</p>

      <div class="filter-section p-6 mb-12">
        <form method="GET" action="">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium mb-2">Room Type</label>
              <div class="selct-wrapper">
              <select name="room_type" class="w-full">
                <option value="All Types" <?php echo ($room_type == 'All Types' || empty($room_type)) ? 'selected' : ''; ?>>All Types</option>
                <option value="Deluxe Room" <?php echo ($room_type == 'Deluxe Room') ? 'selected' : ''; ?>>Deluxe Room</option>
                <option value="Executive Suite" <?php echo ($room_type == 'Executive Suite') ? 'selected' : ''; ?>>Executive Suite</option>
                <option value="Presidential Suite" <?php echo ($room_type == 'Presidential Suite') ? 'selected' : ''; ?>>Presidential Suite</option>
                <option value="Ocean View" <?php echo ($room_type == 'Ocean View') ? 'selected' : ''; ?>>Ocean View</option>
              </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">Price Range</label>
              <div class="selct-wrapper">
              <select name="price_range" class="w-full">
                <option value="All Prices" <?php echo ($price_range == 'All Prices' || empty($price_range)) ? 'selected' : ''; ?>>All Prices</option>
                <option value="$100 - $200" <?php echo ($price_range == '$100 - $200') ? 'selected' : ''; ?>>$100 - $200</option>
                <option value="$200 - $300" <?php echo ($price_range == '$200 - $300') ? 'selected' : ''; ?>>$200 - $300</option>
                <option value="$300 - $400" <?php echo ($price_range == '$300 - $400') ? 'selected' : ''; ?>>$300 - $400</option>
                <option value="$400+" <?php echo ($price_range == '$400+') ? 'selected' : ''; ?>>$400+</option>
              </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">Bed Type</label>
              <div class="selct-wrapper">
              <select name="bed_type" class="w-full">
                <option value="Any Bed" <?php echo ($bed_type == 'Any Bed' || empty($bed_type)) ? 'selected' : ''; ?>>Any Bed</option>
                <option value="King Size" <?php echo ($bed_type == 'King Size') ? 'selected' : ''; ?>>King Size</option>
                <option value="Queen Size" <?php echo ($bed_type == 'Queen Size') ? 'selected' : ''; ?>>Queen Size</option>
                <option value="Twin Beds" <?php echo ($bed_type == 'Twin Beds') ? 'selected' : ''; ?>>Twin Beds</option>
              </select>
              </div>
            </div>
            <div class="flex items-end">
              <button type="submit" class="btn w-full">
                Filter Rooms <i class="fas fa-filter ml-2"></i>
              </button>
            </div>
          </div>
        </form>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php
        if ($result && $result->num_rows > 0) {
          while ($room = $result->fetch_assoc()) {
        ?>
          <div class="card">
            <div class="relative">
              <img alt="<?php echo htmlspecialchars($room['type_name']); ?>" class="w-full h-64 object-cover loaded" src="../<?php echo htmlspecialchars($room['featured_image']); ?>">
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
        <?php
          }
        } else {
          echo '<div class="col-span-3 card p-6 text-center text-gray-400">No rooms found matching your criteria.</div>';
        }
        ?>
      </div>

    </div>
  </main>

  <?php include("../includes/footer.php"); ?>


</body>
</html>
