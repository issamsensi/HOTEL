<?php
require("connexion.php");
session_start();

if(!isset($_GET['id'])) {
    header("Location: news.php");
    exit();
}

$news_id = intval($_GET['id']);

$news_sql = "SELECT n.*, u.*
            FROM news n
            LEFT JOIN users u ON n.author_id = u.user_id
            WHERE n.news_id = $news_id";
$news_result = $conn->query($news_sql);

if($news_result->num_rows == 0) {
    header("Location: news.php");
    exit();
}

$news = $news_result->fetch_assoc();

$conn->query("UPDATE news SET views = views + 1 WHERE news_id = $news_id");

if(isset($_POST['submit_comment']) && isset($_SESSION['user_id'])) {
    $comment = $conn->real_escape_string($_POST['comment']);
    $user_id = $_SESSION['user_id'];

    $insert_sql = "INSERT INTO comments (news_id, user_id, comment, likes)
                  VALUES ($news_id, $user_id, '$comment', 0)";
    $conn->query($insert_sql);
}

if(isset($_POST['like_comment']) && isset($_SESSION['user_id'])) {
    $comment_id = intval($_POST['comment_id']);
    $user_id = $_SESSION['user_id'];

    $check_sql = "SELECT * FROM comment_likes WHERE comment_id = $comment_id AND user_id = $user_id";
    $check_result = $conn->query($check_sql);

    if($check_result->num_rows == 0) {
        $conn->query("INSERT INTO comment_likes (comment_id, user_id) VALUES ($comment_id, $user_id)");
        $conn->query("UPDATE comments SET likes = likes + 1 WHERE comment_id = $comment_id");
    } else {
        $conn->query("DELETE FROM comment_likes WHERE comment_id = $comment_id AND user_id = $user_id");
        $conn->query("UPDATE comments SET likes = likes - 1 WHERE comment_id = $comment_id");
    }
}

$comments_sql = "SELECT c.*, u.first_name, u.last_name, u.profile_image,
                (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.comment_id AND user_id = ".(isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0).") as is_liked
                FROM comments c
                LEFT JOIN users u ON c.user_id = u.user_id
                WHERE c.news_id = $news_id
                ORDER BY c.created_at DESC";
$comments_result = $conn->query($comments_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title><?= htmlspecialchars($news['title']) ?> | Grand Horizon</title>
  <link rel="stylesheet" href="output.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --primary: #0d4a9d;
      --primary-light: #1a5cb0;
      --secondary: #0a1a2b;
      --accent: #ffc107;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
      color: white;
      min-height: 100vh;
    }

    .news-container {
      max-width: 800px;
      margin: 0 auto;
    }

    .featured-image {
      width: 100%;
      height: 450px;
      object-fit: cover;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .news-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 1.5rem 0;
    }

    .news-category {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      background: var(--primary);
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
    }

    .news-date {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.9rem;
    }

    .news-content {
      line-height: 1.8;
      margin-bottom: 2rem;
    }

    .news-content img {
      max-width: 100%;
      height: auto;
      border-radius: 8px;
      margin: 1.5rem 0;
    }

    .author-card {
      display: flex;
      align-items: center;
      background: rgba(255, 255, 255, 0.05);
      padding: 1.5rem;
      border-radius: 12px;
      margin: 2rem 0;
    }

    .author-image {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 1.5rem;
    }

    .social-share {
      display: flex;
      gap: 0.75rem;
      margin: 2rem 0;
    }

    .social-share a {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: all 0.3s ease;
    }

    .social-share a:hover {
      transform: translateY(-3px);
    }

    .comments-section {
      margin-top: 3rem;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      padding-top: 2rem;
    }

    .comments-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1.5rem;
    }

    .comment-form {
      background: rgba(255, 255, 255, 0.05);
      padding: 1.5rem;
      border-radius: 12px;
      margin-bottom: 2rem;
    }

    .comment-form textarea {
      width: 100%;
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      padding: 1rem;
      color: white;
      margin-bottom: 1rem;
      resize: vertical;
      min-height: 100px;
    }

    .comment-form button {
      background: var(--primary);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .comment-form button:hover {
      background: var(--primary-light);
    }

    .comment {
      display: flex;
      gap: 1rem;
      padding: 1.5rem 0;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .comment:last-child {
      border-bottom: none;
    }

    .comment-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
    }

    .comment-content {
      flex: 1;
    }

    .comment-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5rem;
    }

    .comment-author {
      font-weight: 600;
    }

    .comment-date {
      color: rgba(255, 255, 255, 0.6);
      font-size: 0.8rem;
    }

    .comment-text {
      margin-bottom: 0.75rem;
      line-height: 1.6;
    }

    .comment-actions {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .like-button {
      display: flex;
      align-items: center;
      gap: 0.25rem;
      background: none;
      border: none;
      color: rgba(255, 255, 255, 0.7);
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .like-button:hover {
      color: var(--accent);
    }

    .like-button.liked {
      color: var(--accent);
    }

    .login-message {
      background: rgba(255, 255, 255, 0.05);
      padding: 1rem;
      border-radius: 8px;
      text-align: center;
      margin-bottom: 2rem;
    }

    .login-message a {
      color: var(--accent);
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .featured-image {
        height: 300px;
      }

      .news-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
      }

      .author-card {
        flex-direction: column;
        text-align: center;
      }

      .author-image {
        margin-right: 0;
        margin-bottom: 1rem;
      }

      .comment {
        flex-direction: column;
      }

      .comment-avatar {
        margin-bottom: 1rem;
      }
    }
  </style>
</head>
<body class="flex flex-col min-h-screen">

  <?php include("../includes/header.php"); ?>

  <main class="flex-grow pt-24 pb-12">
    <div class="container px-4">
      <nav class="mb-6 text-sm">
        <a href="index.php" class="text-blue-300 hover:text-white">Home</a>
        <span class="text-gray-400 mx-2">/</span>
        <a href="news.php" class="text-blue-300 hover:text-white">News</a>
        <span class="text-gray-400 mx-2">/</span>
        <span class="text-white"><?= htmlspecialchars($news['title']) ?></span>
      </nav>

      <div class="news-container">
        <img src="../<?= htmlspecialchars($news['featured_image']) ?>"
             alt="<?= htmlspecialchars($news['title']) ?>"
             class="featured-image">

        <div class="news-meta">
          <?php if(!empty($news['category'])): ?>
            <span class="news-category"><?= htmlspecialchars($news['category']) ?></span>
          <?php endif; ?>
          <span class="news-date">
            <i class="far fa-calendar-alt mr-1"></i>
            <?= date('F j, Y', strtotime($news['publish_date'])) ?>
          </span>
        </div>

        <h1 class="text-3xl md:text-4xl font-bold mb-6"><?= htmlspecialchars($news['title']) ?></h1>

        <div class="news-content">
          <?= nl2br(htmlspecialchars($news['content'])) ?>

          <?php if(!empty($news['additional_content'])): ?>
            <?= $news['additional_content'] ?>
          <?php endif; ?>
        </div>

        <?php if(!empty($news['first_name'])): ?>
          <div class="author-card">
            <img src="<?= htmlspecialchars($news['profile_image']) ?>"
                 alt="<?= htmlspecialchars($news['last_name']) ?>"
                 class="author-image">
            <div>
              <h3 class="font-bold text-lg"><?= htmlspecialchars($news['first_name']." ".$news['last_name']) ?></h3>

            </div>
          </div>
        <?php endif; ?>

        <div class="social-share">
          <a href="#" class="bg-blue-600 text-white share-facebook">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="#" class="bg-blue-400 text-white share-twitter">
            <i class="fab fa-twitter"></i>
          </a>
          <a href="#" class="bg-red-600 text-white share-pinterest">
            <i class="fab fa-pinterest-p"></i>
          </a>
          <a href="#" class="bg-gray-700 text-white share-copy">
            <i class="fas fa-link"></i>
          </a>
        </div>

        <a href="news.php" class="inline-flex items-center text-blue-400 hover:text-white">
          <i class="fas fa-arrow-left mr-2"></i>
          Back to All News
        </a>

        <div class="comments-section">
          <h2 class="comments-title">Comments</h2>

          <?php if(isset($_SESSION['user_id'])): ?>
            <form method="POST" class="comment-form">
              <textarea name="comment" placeholder="Write your comment here..." required></textarea>
              <button type="submit" name="submit_comment">Post Comment</button>
            </form>
          <?php else: ?>
            <div class="login-message">
              <p>You must <a href="login.php">login</a> to post a comment.</p>
            </div>
          <?php endif; ?>

          <div class="comments-list">
            <?php if($comments_result->num_rows > 0): ?>
              <?php while($comment = $comments_result->fetch_assoc()): ?>
                <div class="comment">
                  <img src="<?= htmlspecialchars($comment['profile_image']) ?>"
                       alt="<?= htmlspecialchars($comment['first_name'].' '.$comment['last_name']) ?>"
                       class="comment-avatar">
                  <div class="comment-content">
                    <div class="comment-header">
                      <span class="comment-author"><?= htmlspecialchars($comment['first_name'].' '.$comment['last_name']) ?></span>
                      <span class="comment-date"><?= date('F j, Y \a\t g:i a', strtotime($comment['created_at'])) ?></span>
                    </div>
                    <p class="comment-text"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                    <div class="comment-actions">
                      <form method="POST">
                        <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                        <button type="submit" name="like_comment" class="like-button <?= $comment['is_liked'] ? 'liked' : '' ?>">
                          <i class="fas fa-thumbs-up"></i>
                          <span><?= $comment['likes'] ?></span>
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <p>No comments yet. Be the first to comment!</p>
            <?php endif; ?>
          </div>
        </div>


      </div>
    </div>
  </main>

  <?php include("../includes/footer.php"); ?>

  <script>
    document.querySelector('.share-facebook').addEventListener('click', function(e) {
      e.preventDefault();
      window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`, '_blank');
    });

    document.querySelector('.share-twitter').addEventListener('click', function(e) {
      e.preventDefault();
      window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(window.location.href)}&text=${encodeURIComponent(document.title)}`, '_blank');
    });

    document.querySelector('.share-pinterest').addEventListener('click', function(e) {
      e.preventDefault();
      window.open(`https://pinterest.com/pin/create/button/?url=${encodeURIComponent(window.location.href)}`, '_blank');
    });

    document.querySelector('.share-copy').addEventListener('click', function(e) {
      e.preventDefault();
      navigator.clipboard.writeText(window.location.href);
      alert('Link copied to clipboard!');
    });
  </script>
</body>
</html>
