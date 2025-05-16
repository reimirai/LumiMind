<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure ID is provided
    if (isset($_POST['id'])) {
        $id = $_POST['id'];

        // Prepare and execute the DELETE statement
        $query = "DELETE FROM reward WHERE id = ?";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("s", $id);
            if ($stmt->execute()) {
                header("Location: rewardmanage.php");
                exit();
            } else {
                echo "Error executing delete: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "No reward ID provided.";
    }
} else {
    echo "Invalid request method.";
}
?>
