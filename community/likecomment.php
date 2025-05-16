<?php
session_start();
include 'db.php';

$userId = $_SESSION['user_id']; // Or adjust to how you store users
$commentId = $_POST['comment_id'];

// Check if already liked
$stmt = $conn->prepare("SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?");
$stmt->bind_param("si", $userId, $commentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Unlike
    $stmt = $conn->prepare("DELETE FROM comment_likes WHERE user_id = ? AND comment_id = ?");
    $stmt->bind_param("si", $userId, $commentId);
    $stmt->execute();
} else {
    // Like
    $stmt = $conn->prepare("INSERT INTO comment_likes (user_id, comment_id) VALUES (?, ?)");
    $stmt->bind_param("si", $userId, $commentId);
    $stmt->execute();
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;