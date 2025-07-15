<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get posts from users the current user follows
$stmt = $pdo->prepare("
    SELECT p.*, u.username, u.profile_pic, u.full_name,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) AS liked_by_user
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id IN (SELECT following_id FROM followers WHERE follower_id = ?) OR p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$user_id, $user_id, $user_id]);
$posts = $stmt->fetchAll();

// Get suggested users to follow
$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.full_name, u.profile_pic
    FROM users u
    WHERE u.id != ? AND u.id NOT IN (SELECT following_id FROM followers WHERE follower_id = ?)
    ORDER BY RAND()
    LIMIT 5
");
$stmt->execute([$user_id, $user_id]);
$suggested_users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | Social Media</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="logo">SocialCircle</h1>
            <nav class="nav">
                <a href="index.php" class="active">Home</a>
                <a href="profile.php?username=<?php echo $user['username']; ?>">Profile</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <div class="main-content">
            <!-- Left sidebar - Create post -->
            <div class="sidebar left">
                <div class="create-post">
                    <div class="user-info">
                        <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Profile Picture" class="profile-pic">
                        <span><?php echo $user['full_name']; ?></span>
                    </div>
                    <form action="create_post.php" method="POST" enctype="multipart/form-data">
                        <textarea name="content" placeholder="What's on your mind?" required></textarea>
                        <div class="post-actions">
                            <label for="post-image" class="btn-icon">
                                <i class="fas fa-image"></i>
                                <input type="file" id="post-image" name="image" accept="image/*">
                            </label>
                            <button type="submit" class="btn">Post</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main content - Posts feed -->
            <div class="content">
                <?php if (empty($posts)): ?>
                    <div class="no-posts">
                        <p>No posts to show. Follow more users to see their posts!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post">
                            <div class="post-header">
                                <img src="uploads/<?php echo $post['profile_pic']; ?>" alt="Profile Picture" class="profile-pic">
                                <div class="post-user">
                                    <a href="profile.php?username=<?php echo $post['username']; ?>"><?php echo $post['full_name']; ?></a>
                                    <span class="post-time"><?php echo time_elapsed_string($post['created_at']); ?></span>
                                </div>
                            </div>
                            <div class="post-content">
                                <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                                <?php if ($post['image']): ?>
                                    <img src="uploads/<?php echo $post['image']; ?>" alt="Post Image" class="post-image">
                                <?php endif; ?>
                            </div>
                            <div class="post-actions">
                                <button class="btn-icon like-btn <?php echo $post['liked_by_user'] ? 'liked' : ''; ?>" data-post-id="<?php echo $post['id']; ?>">
                                    <i class="fas fa-heart">Likes</i>
                                    <span class="like-count"><?php echo $post['like_count']; ?></span>
                                </button>
                                <button class="btn-icon comment-btn" data-post-id="<?php echo $post['id']; ?>">
                                    <i class="fas fa-comment">Comment</i>
                                    <span class="comment-count" id="comment-count-<?php echo $post['id']; ?>">0</span>
                                </button>
                            </div>

                            <div class="comments-wrapper" id="wrapper-<?php echo $post['id']; ?>" style="display: none;">
                                <div class="comments-section" id="comments-<?php echo $post['id']; ?>">
                                    <!-- Comments will be loaded here via AJAX -->
                                </div>
                                <div class="add-comment">
                                    <form class="comment-form" data-post-id="<?php echo $post['id']; ?>">
                                        <input type="text" placeholder="Add a comment..." required>
                                       <button type="submit" class="btn">Post</button>
                                    </form>
                                </div>
                            </div>
                            
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Right sidebar - Suggested users -->
            <div class="sidebar right">
                <div class="suggested-users">
                    <h3>Suggested Users</h3>
                    <?php foreach ($suggested_users as $suggested_user): ?>
                        <div class="user">
                            <img src="uploads/<?php echo $suggested_user['profile_pic']; ?>" alt="Profile Picture" class="profile-pic">
                            <div class="user-info">
                                <a href="profile.php?username=<?php echo $suggested_user['username']; ?>"><?php echo $suggested_user['full_name']; ?></a>
                                <button class="btn follow-btn" data-user-id="<?php echo $suggested_user['id']; ?>">Follow</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
</body>
</html>