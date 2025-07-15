<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $bio = sanitize($_POST['bio']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Handle profile picture upload
    $profile_pic = $user['profile_pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_ext;
        $file_path = $upload_dir . $file_name;

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['profile_pic']['type'], $allowed_types)) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $file_path)) {
                // Delete old profile picture if it's not the default
                if ($profile_pic !== 'default_profile.jpg') {
                    @unlink($upload_dir . $profile_pic);
                }
                $profile_pic = $file_name;
            } else {
                $errors[] = "Failed to upload profile picture";
            }
        } else {
            $errors[] = "Invalid file type for profile picture";
        }
    }

    // Handle password change if provided
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password";
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        } elseif (empty($new_password)) {
            $errors[] = "New password is required";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        } else {
            $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        }
    }

    if (empty($errors)) {
        try {
            if (isset($password_hash)) {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, bio = ?, profile_pic = ?, password = ? WHERE id = ?");
                $stmt->execute([$full_name, $bio, $profile_pic, $password_hash, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, bio = ?, profile_pic = ? WHERE id = ?");
                $stmt->execute([$full_name, $bio, $profile_pic, $user_id]);
            }

            $_SESSION['success'] = "Profile updated successfully";
            header('Location: profile.php?username=' . $user['username']);
            exit();
        } catch (PDOException $e) {
            $errors[] = "Failed to update profile: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Social Media</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="logo">SocialCircle</h1>
            <nav class="nav">
                <a href="index.php">Home</a>
                <a href="profile.php?username=<?php echo $user['username']; ?>">Profile</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <div class="edit-profile-container">
            <h1>Edit Profile</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profile_pic">Profile Picture</label>
                    <div class="profile-pic-preview">
                        <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Current Profile Picture" id="profile-pic-preview">
                    </div>
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Change Password (leave blank to keep current password)</label>
                    <input type="password" name="current_password" placeholder="Current Password">
                    <input type="password" name="new_password" placeholder="New Password">
                    <input type="password" name="confirm_password" placeholder="Confirm New Password">
                </div>

                <button type="submit" class="btn">Save Changes</button>
                <a href="profile.php?username=<?php echo $user['username']; ?>" class="btn cancel">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        // Preview profile picture before upload
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('profile-pic-preview').src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>