<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $taskName = $data['taskName'];
    $taskStep = $data['taskStep'];
    $steps = $data['steps'];

    $conn = new mysqli("localhost", "root", "", "LumiMind");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
$idResult = $conn->query("SELECT ID FROM task ORDER BY ID DESC LIMIT 1");
if ($idResult && $idResult->num_rows > 0) {
    $lastId = $idResult->fetch_assoc()['ID'];
    $num = intval(substr($lastId, 1)) + 1;
    $newId = 'T' . str_pad($num, 4, '0', STR_PAD_LEFT);
} else {
    $newId = 'T0001';
}
    $stmt = $conn->prepare("INSERT INTO task (ID,task_title, Status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("ss", $newId,$taskName);
    $stmt->execute();

$stepResult = $conn->query("SELECT TSID FROM task_step ORDER BY TSID DESC LIMIT 1");
if ($stepResult && $stepResult->num_rows > 0) {
    $lastTSID = $stepResult->fetch_assoc()['TSID'];
    $nextTSID = str_pad((int)$lastTSID + 1, 5, '0', STR_PAD_LEFT);
} else {
    $nextTSID = '00001';
}
  $stmt3 = $conn->prepare("INSERT INTO task_step (TSID, TID, total_steps) VALUES (?, ?, ?)");
$stmt3->bind_param("ssi", $nextTSID, $newId, $taskStep);  
  if (!$stmt3->execute()) {
        echo json_encode(["success" => false, "message" => $stmt3->error]);
        exit;
    }
  foreach ($steps as $step) {
    $stmt2 = $conn->prepare("INSERT INTO task_step_description (TSID, Description) VALUES (?, ?)");
    $stmt2->bind_param("ss", $nextTSID, $step);
    if (!$stmt2->execute()) {
        echo json_encode(['success' => false, 'message' => $stmt2->error]);
        exit;
    }
}

    echo json_encode(["success" => true, "message" => "Task added successfully."]);

    $stmt->close();
    $stmt2->close();
    $stmt3->close();
    $conn->close();
}
?>
