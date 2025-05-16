<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentId = $_POST['comment_id'];
    $userId = $_SESSION['user_id'];
    $updatedContent = trim($_POST['updated_content']);
    $ref = $_POST['ref'];

    if (!empty($updatedContent)) {
        // Check ownership
        $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        $stmt->bind_result($commentOwner);
        $stmt->fetch();
        $stmt->close();

        if ($commentOwner == $userId) {
            $stmt = $conn->prepare("UPDATE comments SET content = ? WHERE id = ?");
            $stmt->bind_param("si", $updatedContent, $commentId);
            $stmt->execute();
        }
    }

    header("Location: $ref");
    exit();
}
?>