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

// ❗ Check for unfinished task first
$check = $conn->prepare("SELECT * FROM user_task WHERE user_id = ? AND task_id = ? AND status != 'completed'");
$check->bind_param("si", $userId, $taskId);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You must complete the previous task before taking it again.']);
    $check->close();
    $conn->close();
    exit;
}
$check->close();

$stmt = $conn->prepare("SELECT step_number FROM task_step WHERE fk_task = ?");
$stmt->bind_param("i", $taskId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalSteps = $row['step_number'] ?? 0;
$stmt->close();

if ($totalSteps == 0) {
    echo json_encode(['success' => false, 'message' => 'Task has no steps.']);
    $conn->close();
    exit;
}

$stmt = $conn->prepare("INSERT INTO user_task (user_id, task_id, current_step, total_steps, status, task_lastdate) VALUES (?, ?, 0, ?, 'in_progress', NOW() + INTERVAL 30 Day)");
$stmt->bind_param("sii", $userId, $taskId, $totalSteps);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to accept task: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
