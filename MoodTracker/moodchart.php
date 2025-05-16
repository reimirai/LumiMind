
<?php
require_once('../sidebar/sidebar.html');
// Database connection
$conn = new mysqli("localhost", "root", "", "LumiMind");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare data for monthly mood chart
$monthlyMoodCounts = [];
$monthlyStmt = $conn->prepare("SELECT mood, DATE_FORMAT(entry_date, '%Y-%m') AS month FROM mood_entries ORDER BY month ASC");
$monthlyStmt->execute();
$monthlyResult = $monthlyStmt->get_result();
while ($row = $monthlyResult->fetch_assoc()) {
    if (!isset($monthlyMoodCounts[$row['month']])) {
        $monthlyMoodCounts[$row['month']] = [
            "happy-excited" => 0,
            "angry" => 0,
            "sad" => 0,
            "surprised" => 0,
            "happy" => 0,
            "confused" => 0,
            "worried" => 0,
            "neutral" => 0,
        ];
    }
    $monthlyMoodCounts[$row['month']][$row['mood']]++;
}
$monthlyStmt->close();

$conn->close();
?>
