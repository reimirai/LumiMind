<?php
// Include the database connection
include 'db.php';

$response = ['success' => false, 'message' => '']; // Initialize response

try {
    // Get form data
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Validate required fields
    if (empty($title) || empty($content)) {
        throw new Exception('Title and content are required.');
    }

    // Insert the post into the `posts` table
    $stmt = $conn->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    if (!$stmt->execute()) {
        throw new Exception('Error inserting the post into the database.');
    }
    $postId = $stmt->insert_id; // Get the ID of the inserted post
    $stmt->close();

    // Handle multiple image uploads
    if (isset($_FILES['images'])) {
        $uploadDir = 'uploads/';

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $imageName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                $imagePath = $uploadDir . $imageName;

                // Move the uploaded file to the desired directory
                if (move_uploaded_file($tmpName, $imagePath)) {
                    // Save the image path to the `post_images` table
                    $stmt = $conn->prepare("INSERT INTO post_images (post_id, image_path) VALUES (?, ?)");
                    $stmt->bind_param("is", $postId, $imagePath);
                    if (!$stmt->execute()) {
                        throw new Exception('Error saving the image path to the database.');
                    }
                    $stmt->close();
                } else {
                    throw new Exception('Error moving the uploaded file.');
                }
            } else {
                throw new Exception('Error uploading one or more files.');
            }
        }
    }

    // Success response
    $response['success'] = true;
    $response['message'] = 'Post created successfully.';
} catch (Exception $e) {
    // Error response
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>