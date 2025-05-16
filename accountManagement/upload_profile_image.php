<?php
session_start();

// Database connection details (adjust as needed)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "LumiMind";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a file was uploaded
if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif']; // Allowed image types
    if (in_array($_FILES['profileImage']['type'], $allowed_types)) {

        $image_data = file_get_contents($_FILES['profileImage']['tmp_name']);
        $user_id = $_SESSION['user_id']; // Assuming you have the user's ID in the session

        // Prepare and execute the SQL query to update the profile_image
        $stmt = $conn->prepare("UPDATE Users SET profile_image = ? WHERE id = ?");
        $stmt->bind_param("ss", $image_data, $user_id); 

        if ($stmt->execute()) {
            echo "Profile image updated successfully!";
        } else {
            echo "Error updating profile image: " . $stmt->error;
        }
        $stmt->close();

    } else {
        echo "Invalid file type. Only JPEG, PNG, and GIF images are allowed.";
    }
} else {
    echo "No file uploaded or an error occurred during upload.";
}

$conn->close();
?>