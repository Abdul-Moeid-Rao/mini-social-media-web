<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$content = sanitize($_POST['content']);

// Handle file upload
$image = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '.' . $file_ext;
    $file_path = $upload_dir . $file_name;

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($_FILES['image']['type'], $allowed_types)) {
        move_uploaded_file($_FILES['image']['tmp_name'], $file_path);
        $image = $file_name;
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $content, $image]);
    
    header('Location: index.php');
    exit();
} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to create post: " . $e->getMessage();
    header('Location: index.php');
    exit();
}
?>