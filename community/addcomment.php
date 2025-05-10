<?php
session_start();

include 'db.php';

$postId = intval($_POST['post_id']);
$parentId = $_POST['parent_id'] !== 'NULL' ? intval($_POST['parent_id']) : null;
$userId = $_SESSION['user_id'];
$content = $_POST['content'];

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, parent_id, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("iisi", $postId, $userId, $content, $parentId);
$stmt->execute();

header("Location: community.php?page=post&id=$postId");
exit;
?>