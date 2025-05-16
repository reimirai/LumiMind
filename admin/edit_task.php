<?php
include 'db_connection.php';

// Get the task ID from the URL
if (isset($_GET['id'])) {
    $taskId = $_GET['id'];
    
    // Fetch task details
    $taskQuery = "SELECT * FROM task WHERE ID = ?";
    $stmt = $conn->prepare($taskQuery);
    $stmt->bind_param("s", $taskId);
    $stmt->execute();
    $taskResult = $stmt->get_result();
    $task = $taskResult->fetch_assoc();

    if (!$task) {
        echo "Task not found!";
        exit;
    }

    // Fetch task steps
    $stepsQuery = "SELECT * FROM task_step WHERE TID = ?";
    $stmtSteps = $conn->prepare($stepsQuery);
    $stmtSteps->bind_param("s", $taskId);
    $stmtSteps->execute();
    $stepsResult = $stmtSteps->get_result();

    // Fetch task step descriptions
    $descriptionsQuery = "SELECT * FROM task_step_description WHERE TSID IN (SELECT TSID FROM task_step WHERE TID = ?)";
    $stmtDescriptions = $conn->prepare($descriptionsQuery);
    $stmtDescriptions->bind_param("s", $taskId);
    $stmtDescriptions->execute();
    $descriptionsResult = $stmtDescriptions->get_result();
    
    // Don't close the connection yet, because we are still using it
    // $conn->close();  <-- Remove this line
} else {
    echo "Task ID is missing!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="taskmanage.php">Manage Tasks</a></li>
                <li><a href="achievementmanage.php">Manage Achievements</a></li>
                <!-- Add other links as needed -->
            </ul>
        </div>

        <div class="main-content">
            <h1>Edit Task: <?php echo htmlspecialchars($task['task_title']); ?></h1>

            <form action="update_task.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $task['ID']; ?>">

                <!-- Task Title -->
                <label for="task_title">Task Title:</label>
                <input type="text" name="task_title" id="task_title" value="<?php echo htmlspecialchars($task['task_title']); ?>" required>

                <!-- Task Steps -->
                <label for="task_step">Number of Steps:</label>
                <input type="number" name="task_step" id="task_step" value="<?php echo $stepsResult->num_rows; ?>" required>

                <!-- Task Step Descriptions -->
                <div id="task_steps">
                    <?php while ($step = $stepsResult->fetch_assoc()): ?>
                        <div class="task-step">
                            <label>Step <?php echo $step['TSID']; ?> Description:</label>
                            <textarea name="steps[<?php echo $step['TSID']; ?>]" required>
                                <?php
                                // Fetching description for the specific step
                                $descriptionQuery = "SELECT Description FROM task_step_description WHERE TSID = ?";
                                $descStmt = $conn->prepare($descriptionQuery);
                                $descStmt->bind_param("s", $step['TSID']);
                                $descStmt->execute();
                                $descResult = $descStmt->get_result();
                                $desc = $descResult->fetch_assoc();
                                echo htmlspecialchars($desc['Description']);
                                ?>
                            </textarea>
                        </div>
                    <?php endwhile; ?>
                </div>

                <button type="submit">Update Task</button>
            </form>
        </div>
    </div>
</body>
</html>
