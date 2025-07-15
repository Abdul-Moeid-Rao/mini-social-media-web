<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Get profile user data
$username = isset($_GET['username']) ? sanitize($_GET['username']) : $_SESSION['username'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$profile_user = $stmt->fetch();

if (!$profile_user) {
    header('Location: index.php');
    exit();
}

// Check if current user follows this profile user
$stmt = $pdo->prepare("SELECT * FROM followers WHERE follower_id = ? AND following_id = ?");
$stmt->execute([$current_user_id, $profile_user['id']]);
$is_following = $stmt->fetch();

// Get user's posts
$stmt = $pdo->prepare("
    SELECT p.*, 
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
    (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) AS liked_by_user
    FROM posts p
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$current_user_id, $profile_user['id']]);
$user_posts = $stmt->fetchAll();

// Get follower and following counts
$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE following_id = ?");
$stmt->execute([$profile_user['id']]);
$followers_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ?");
$stmt->execute([$profile_user['id']]);
$following_count = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $profile_user['full_name']; ?> | Social Media</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="logo">SocialCircle</h1>
            <nav class="nav">
                <a href="index.php">Home</a>
                <a href="profile.php?username=<?php echo $_SESSION['username']; ?>">Profile</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-picture">
                    <img src="uploads/<?php echo $profile_user['profile_pic']; ?>" alt="Profile Picture">
                </div>
                <div class="profile-info">
                    <h1><?php echo $profile_user['full_name']; ?></h1>
                    <p class="username">@<?php echo $profile_user['username']; ?></p>
                    <p class="bio"><?php echo nl2br(htmlspecialchars($profile_user['bio'])); ?></p>
                    
                    <div class="profile-stats">
                        <div class="stat">
                            <span class="count"><?php echo count($user_posts); ?></span>
                            <span class="label">Posts</span>
                        </div>
                        <div class="stat">
                            <span class="count"><?php echo $followers_count; ?></span>
                            <span class="label">Followers</span>
                        </div>
                        <div class="stat">
                            <span class="count"><?php echo $following_count; ?></span>
                            <span class="label">Following</span>
                        </div>
                    </div>

                    <?php if ($profile_user['id'] != $current_user_id): ?>
                        <button class="btn follow-btn <?php echo $is_following ? 'following' : ''; ?>" data-user-id="<?php echo $profile_user['id']; ?>">
                            <?php echo $is_following ? 'Following' : 'Follow'; ?>
                        </button>
                    <?php else: ?>
                        <a href="edit_profile.php" class="btn">Edit Profile</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-posts">
                <h2>Posts</h2>
                <?php if (empty($user_posts)): ?>
                    <div class="no-posts">
                        <p>No posts yet.</p>
                    </div>
                <?php else: ?>
                    <div class="posts-grid">
                        <?php foreach ($user_posts as $post): ?>
                            <div class="post-thumbnail">
                                <?php if ($post['image']): ?>
                                    <img src="uploads/<?php echo $post['image']; ?>" alt="Post Image">
                                <?php else: ?>
                                    <div class="text-post">
                                        <p><?php echo truncate_text($post['content'], 100); ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="post-overlay">
                                    <span><i class="fas fa-heart"></i> <?php echo $post['like_count']; ?></span>
                                    <span><i class="fas fa-comment"></i></span>
                                </div>
                                <a href="post.php?id=<?php echo $post['id']; ?>" class="post-link"></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="js/main.js"></script>
</body>
</html>