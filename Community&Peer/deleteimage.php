<?php
// Include the database connection
include 'db.php';

// Validate the `image_id` parameter
if (!isset($_POST['image_id']) || empty($_POST['image_id'])) {
    die("Invalid image ID.");
}

$imageId = $_POST['image_id']; // Get the image ID from the form

try {
    // Fetch the image details
    $stmt = $conn->prepare("SELECT post_id, image_path FROM post_images WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    $stmt->close();

    if (!$image) {
        die("Image not found.");
    }

    $postId = $image['post_id']; // Get the associated post ID
    $imagePath = $image['image_path']; // Get the image file path

    // Delete the image file from the server
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Delete the image record from the database
    $stmt = $conn->prepare("DELETE FROM post_images WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $stmt->close();

    // Redirect back to the edit post page
    header("Location: community.php?page=editpost&id=" . $postId);
    exit;
} catch (Exception $e) {
    die("Error deleting image: " . $e->getMessage());
}
?>