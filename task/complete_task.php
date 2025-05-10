<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header('Content-Type: application/json');

// Get raw POST data and decode JSON
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['task_id']) || !isset($data['user_id']) || !isset($data['points'])) {
    echo json_encode(['success' => false, 'message' => 'Missing task_id, user_id, or points']);
    exit;
}

$taskId = (int)$data['task_id'];      // Ensure taskId is an integer
$userId = $data['user_id'];           // userId is a string (VARCHAR)
$points = (int)$data['points'];       // Ensure points is an integer
error_log("points = $points");
include 'db.php';
$stmt = $conn->prepare("UPDATE user_task SET status = 'completed' WHERE user_id = ? AND task_id = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("si", $userId, $taskId);

if ($stmt->execute()) {

    $stmt1 = $conn->prepare("UPDATE user SET points = points + ? WHERE id = ?");
    if (!$stmt1) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt1->bind_param("is", $points, $userId);

    if ($stmt1->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task marked as completed and points added']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Task status updated but failed to add points']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to complete task']);
}
$stmt->close();
$stmt1->close();
$conn->close();
?>
