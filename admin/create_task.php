<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task_title = $_POST['task_title'];
    $total_steps = $_POST['total_steps'];

    // Insert new task
    $stmt = $conn->prepare("INSERT INTO task (task_title, Status) VALUES (?, 'Pending')");
    $stmt->bind_param("s", $task_title);
    $stmt->execute();

    // Create task steps if needed (this example assumes steps will be created separately)

    header("Location: taskmanage.php");
    exit();
}
?>
