<?php
include 'db_connection.php';

if (isset($_GET['id'])) {
    $task_id = $_GET['id'];

    // Delete associated steps and descriptions
    $stmt1 = $conn->prepare("DELETE FROM task_step_description WHERE TSID = ?");
    $stmt1->bind_param("s", $task_id);
    $stmt1->execute();

    // Delete the task
    $stmt2 = $conn->prepare("DELETE FROM task WHERE ID = ?");
    $stmt2->bind_param("s", $task_id);
    $stmt2->execute();

    header("Location: taskmanage.php");
    exit();
}
?>
