<?php
include 'db_connection.php';

// Fetch all pending tasks
$query_pending = "SELECT t.ID, t.task_title, t.Status FROM task t WHERE t.Status = 'Pending'";
$result_pending = $conn->query($query_pending);

// Fetch completed user tasks
$query_completed_tasks = "
    SELECT ut.UID, ut.TID, ut.Status, u.Name, t.task_title 
    FROM user_task ut
    JOIN task t ON t.ID = ut.TID
    JOIN users u ON u.ID = ut.UID
    WHERE ut.Status = 'completed'
";
$result_completed_tasks = $conn->query($query_completed_tasks);

// Fetch tasks for editing and deletion
$query_tasks = "SELECT * FROM task";
$result_tasks = $conn->query($query_tasks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Task Management</h2>
            <ul>
                <li><a href="#pending-tasks">Pending Tasks</a></li>
                <li><a href="#create-task">Create Task</a></li>
                <li><a href="#approve-completed-tasks">Approve Completed Tasks</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Manage Tasks</h1>

            <!-- Pending Tasks Section -->
            <div id="pending-tasks" class="task-section">
                <h2>Pending Tasks</h2>
                <?php while ($task = $result_pending->fetch_assoc()): ?>
                    <div class="task-card">
                        <h3><?= htmlspecialchars($task['task_title']) ?></h3>
                        <div class="action-buttons">
                            <a href="edit_task.php?id=<?= $task['ID'] ?>"><button>Edit</button></a>
                            <a href="delete_task.php?id=<?= $task['ID'] ?>" onclick="return confirm('Delete this task?')"><button class="decline">Delete</button></a>
                            <a href="approve_task.php?id=<?= $task['ID'] ?>"><button class="approve">Approve</button></a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Task Creation Section -->
            <div id="create-task" class="task-section">
                <h2>Create New Task</h2>
                <form action="create_task.php" method="post">
                    <label for="task_title">Task Title:</label>
                    <input type="text" id="task_title" name="task_title" required>

                    <label for="total_steps">Total Steps:</label>
                    <input type="number" id="total_steps" name="total_steps" required>

                    <button type="submit">Create Task</button>
                </form>
            </div>

            <!-- Approve Completed Tasks Section -->
            <div id="approve-completed-tasks" class="task-section">
                <h2>Approve Completed Tasks</h2>
                <?php while ($task = $result_completed_tasks->fetch_assoc()): ?>
                    <div class="task-card">
                        <h3><?= htmlspecialchars($task['task_title']) ?> (Completed by <?= htmlspecialchars($task['Name']) ?>)</h3>
                        <div class="action-buttons">
                            <a href="approve_user_task.php?uid=<?= $task['UID'] ?>&tid=<?= $task['TID'] ?>"><button>Approve</button></a>
                            <a href="reject_user_task.php?uid=<?= $task['UID'] ?>&tid=<?= $task['TID'] ?>"><button class="decline">Reject</button></a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Edit and Delete Task Section -->
            <div id="edit-delete-tasks" class="task-section">
                <h2>Existing Tasks</h2>
                <?php while ($task = $result_tasks->fetch_assoc()): ?>
                    <div class="task-card">
                        <h3><?= htmlspecialchars($task['task_title']) ?></h3>
                        <div class="action-buttons">
                            <a href="edit_task.php?id=<?= $task['ID'] ?>"><button>Edit</button></a>
                            <a href="delete_task.php?id=<?= $task['ID'] ?>" onclick="return confirm('Delete this task?')"><button class="decline">Delete</button></a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
