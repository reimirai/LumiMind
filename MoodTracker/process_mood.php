<?php
if (isset($_POST['mood'])) {
    $selectedMood = $_POST['mood'];
    $currentDate = date("Y-m-d");

    $conn = new mysqli("localhost", "root", "", "LumiMind");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if a mood entry already exists for today
    $checkStmt = $conn->prepare("SELECT entry_id FROM mood_entries WHERE entry_date = ?");
    $checkStmt->bind_param("s", $currentDate);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Mood already recorded today, redirect with an error
        header("Location: mood_sections.php?error=duplicate");
        $checkStmt->close();
        $conn->close();
        exit();
    } else {
        // No mood recorded today, proceed with insertion
        $insertStmt = $conn->prepare("INSERT INTO mood_entries (mood, entry_date) VALUES (?, ?)");
        $insertStmt->bind_param("ss", $selectedMood, $currentDate);

        if ($insertStmt->execute()) {
            header("Location: mood_sections.php?success=1");
            $insertStmt->close();
            $conn->close();
            exit();
        } else {
            echo "Error: " . $insertStmt->error;
            $insertStmt->close();
            $conn->close();
        }
    }
} else {
    echo "No mood selected.";
}
?>