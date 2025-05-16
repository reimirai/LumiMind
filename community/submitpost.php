<?php
session_start();

// Include the database connection
include 'db.php';

$response = ['success' => false, 'message' => '']; // Initialize response

try {
    // Get form data
    $title = $_POST['title'];
    $content = $_POST['content'];
    $userId = $_SESSION['user_id'];
    $groupId = isset($_POST['group_id']) ? intval($_POST['group_id']) : 0;

    // Validate required fields
    if (empty($title) || empty($content)) {
        throw new Exception('Title and content are required.');
    }

    // Double-check user is a member of the selected group
    $stmt = $conn->prepare("SELECT 1 FROM peer_support_group_members WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $groupId, $userId);
    $stmt->execute();
    $is_member = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if (!$is_member) {
        throw new Exception('You must be a member of the group to post.');
    }

    // Insert the post into the `posts` table
    $stmt = $conn->prepare("INSERT INTO posts (group_id, user_id, title, content) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $groupId, $userId, $title, $content);
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
                $originalName = basename($_FILES['images']['name'][$key]);
                $imageName = time() . '_' . $originalName;
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