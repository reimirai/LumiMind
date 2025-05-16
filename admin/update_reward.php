<?php
include 'db_connection.php';

// Validate that the required POST data exists
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $title = $_POST['reward_title'];
    $description = $_POST['reward_description'];
    $point_cost = $_POST['point_cost'];

    if ($_FILES['pic']['size'] > 0) {
        // New image uploaded
        $imageData = file_get_contents($_FILES['pic']['tmp_name']);

        $query = "UPDATE reward 
                  SET reward_title = ?, reward_description = ?, point_cost = ?, pic = ? 
                  WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssiss", $title, $description, $point_cost, $imageData, $id);
    } else {
        // No image uploaded, keep the current one
        $query = "UPDATE reward 
                  SET reward_title = ?, reward_description = ?, point_cost = ? 
                  WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssis", $title, $description, $point_cost, $id);
    }

    // Execute the prepared statement
    if ($stmt->execute()) {
        header("Location: rewardmanage.php");
        exit();
    } else {
        echo "Error updating reward: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
