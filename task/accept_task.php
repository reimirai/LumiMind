<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['task_id']) || !isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing task_id or user_id']);
    exit;
}

$taskId = $data['task_id'];
$userId = $data['user_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "LumiMind";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check for an unfinished task first
$check = $conn->prepare("SELECT * FROM user_task WHERE UID = ? AND TID = ? AND status != 'completed'");
if (!$check) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$check->bind_param("si", $userId, $taskId);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You must complete the previous task before taking it again.']);
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// Retrieve total_steps for the task using bind_result()/fetch()
$stmt = $conn->prepare("SELECT total_steps FROM task_step WHERE TID = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("s", $taskId);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($totalSteps);
$stmt->fetch();
$stmt->close();

if ($totalSteps == 0) {
    echo json_encode(['success' => false, 'message' => 'Task has no steps.']);
    $conn->close();
    exit;
}

$stmt = $conn->prepare("INSERT INTO user_task (UID, TID, current_step, total_steps, status, task_lastdate) VALUES (?, ?, 0, ?, 'in_progress', DATE_ADD(NOW(), INTERVAL 30 DAY))");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}
$stmt->bind_param("ssi", $userId, $taskId, $totalSteps);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to accept task: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
