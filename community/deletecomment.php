<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentId = $_POST['comment_id'];
    $userId = $_SESSION['user_id'];
    $ref = $_POST['ref'];

    // Check ownership
    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    $stmt->bind_result($commentOwner);
    $stmt->fetch();
    $stmt->close();

    if ($commentOwner == $userId) {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
    }

    header("Location: $ref");
    exit();
}
?>