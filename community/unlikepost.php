<?php
session_start();

include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

if ($post_id > 0) {
    $stmt = $conn->prepare("DELETE FROM post_likes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("is", $post_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Redirect back to the previous page
$previousPage = $_POST['ref'] ?? 'community.php?page=forum';
header("Location: $previousPage");
exit();
?>