<?php
// filepath: c:\Users\jingy\Documents\LumiMind\deletepost.php

// Include the database connection
include 'db.php';

// Validate the `id` parameter
if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    die("Invalid post ID.");
}

$postId = $_POST['post_id']; // Get the post ID from the query parameter

try {
    // Fetch the images associated with the post
    $stmt = $conn->prepare("SELECT image_path FROM post_images WHERE post_id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Delete the images from the server
    foreach ($images as $image) {
        $imagePath = $image['image_path'];
        if (file_exists($imagePath)) {
            unlink($imagePath); // Delete the image file
        }
    }

    // Delete the images from the database
    $stmt = $conn->prepare("DELETE FROM post_images WHERE post_id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $stmt->close();

    // Delete the post from the database
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $stmt->close();

    // Redirect back to the forum page
    header("Location: community.php?page=forum");
    exit;
} catch (Exception $e) {
    die("Error deleting post: " . $e->getMessage());
}
?>