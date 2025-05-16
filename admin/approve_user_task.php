<?php
include 'db_connection.php';

if (isset($_GET['uid']) && isset($_GET['tid'])) {
    $user_id = $_GET['uid'];
    $task_id = $_GET['tid'];

    // Fetch points for the task
    $stmt = $conn->prepare("SELECT Points FROM task WHERE ID = ?");
    $stmt->bind_param("s", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();

    // Add points to user
    $stmt = $conn->prepare("UPDATE users SET Points = Points + ? WHERE ID = ?");
    $stmt->bind_param("is", $task['Points'], $user_id);
    $stmt->execute();

    // Update user task status to "approved"
    $stmt = $conn->prepare("UPDATE user_task SET Status = 'approved' WHERE UID = ? AND TID = ?");
    $stmt->bind_param("ss", $user_id, $task_id);
    $stmt->execute();

    header("Location: taskmanage.php");
    exit();
}
?>
