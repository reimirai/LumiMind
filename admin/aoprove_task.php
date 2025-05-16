<?php
include 'db_connection.php';

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    // Update task status to 'Approved'
    $stmt = $conn->prepare("UPDATE task SET Status = 'Approved' WHERE ID = ?");
    $stmt->bind_param("s", $task_id);
    $stmt->execute();

    header("Location: taskmanage.php");
    exit();
}
?>
