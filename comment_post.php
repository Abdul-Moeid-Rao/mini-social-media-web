<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$content = isset($_POST['content']) ? sanitize($_POST['content']) : '';

if ($post_id <= 0 || empty($content)) {
    header('HTTP/1.1 400 Bad Request');
    exit();
}

try {
    $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $post_id, $content]);
    
    // Get the new comment with user info
    $comment_id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("
        SELECT c.*, u.username, u.profile_pic, u.full_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'comment' => $comment]);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>