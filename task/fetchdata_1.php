<?php
include 'db.php';

$sql1 = "SELECT id, points FROM User";
     
$result1 = $conn->query($sql1);

if ($result1->num_rows > 0) {
    $row = $result1->fetch_assoc();
    $userId = $row['id'];
    $userPoints = $row['points'];
} else {
    echo "No records found.";
}

$sql = "SELECT a.*
        FROM achievements a
        LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
        WHERE ua.achievement_id IS NULL AND a.is_hidden = FALSE";

 $stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

$stmt->bind_param('i', $userId); 
$stmt->execute();
$result = $stmt->get_result();
$data = [];

?>