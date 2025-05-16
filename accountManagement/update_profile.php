<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "LumiMind";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => "Connection failed: " . $conn->connect_error]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //  Sanitize and validate input data
    $newUsername = htmlspecialchars(trim($_POST['username']));
    $newEmail = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $newBirthDate = $_POST['dob']; //  Consider validating the date format

    if (!$newEmail) {
        die(json_encode(['status' => 'error', 'message' => "Invalid email format."]));
    }

    $userId = $_SESSION['user_id']; //  Get user ID from session

    try {
        $stmt = $conn->prepare("UPDATE Users SET Name = ?, Email = ?, BirthDate = ? WHERE ID = ?");
        $stmt->bind_param("sssi", $newUsername, $newEmail, $newBirthDate, $userId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => "Profile updated successfully!"]);
        } else {
            echo json_encode(['status' => 'info', 'message' => "No changes made."]);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Error updating profile: " . $e->getMessage()]);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => "Invalid request."]); //  If the page is accessed directly without submitting the form
}
?>