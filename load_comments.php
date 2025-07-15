<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['post_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$post_id = (int)$_GET['post_id'];
if ($post_id <= 0) {
    echo json_encode(['success' => false]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT c.*, u.username, u.profile_pic, u.full_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll();

    echo json_encode(['success' => true, 'comments' => $comments]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
