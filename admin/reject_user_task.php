<?php
include 'db_connection.php';

if (isset($_GET['uid']) && isset($_GET['tid'])) {
    $user_id = $_GET['uid'];
    $task_id = $_GET['tid'];

    // Update user task status to "rejected"
    $stmt = $conn->prepare("UPDATE user_task SET Status = 'rejected' WHERE UID = ? AND TID = ?");
    $stmt->bind_param("ss", $user_id, $task_id);
    $stmt->execute();

    header("Location: taskmanage.php");
    exit();
}
?>
