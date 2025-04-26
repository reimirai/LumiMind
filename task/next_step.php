<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['task_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id or task_id']);
    exit;
}

$userId = $data['user_id'];
$taskId = $data['task_id'];
include 'db.php';

$stmt = $conn->prepare("SELECT current_step, total_steps FROM user_task WHERE user_id = ? AND task_id = ?");
$stmt->bind_param("si", $userId, $taskId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Task record not found for this user.']);
    $stmt->close();
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
$currentStep = $row['current_step'];
$totalSteps  = $row['total_steps'];
$stmt->close();

// Check if the task already reached its total steps
if ($currentStep >= $totalSteps) {
    echo json_encode(['success' => false, 'message' => 'Task is already completed.']);
    $conn->close();
    exit;
}

// Increment current_step by 1
$newStep = $currentStep + 1;

$stmt = $conn->prepare("UPDATE user_task SET current_step = ? WHERE user_id = ? AND task_id = ?");
$stmt->bind_param("isi", $newStep, $userId, $taskId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Task progress updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update task progress: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>