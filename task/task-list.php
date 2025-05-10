<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "LumiMind";

// Establish connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql1 = "SELECT id, points FROM Users WHERE id = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("s", $_SESSION['user_id']);
$stmt1->execute();
$result1 = $stmt1->get_result();

if ($result1->num_rows > 0) {
    $row = $result1->fetch_assoc();
    $userId = $row['id'];
    $userPoints = $row['points'];
} else {
    echo "No records found.";
}


$sql = "
    SELECT 
        ut.current_step,
        ut.total_steps,
        t.id AS task_id,
        t.task_title,
        t.points,
        GROUP_CONCAT(tsd.description SEPARATOR '\n') AS descriptions
    FROM user_task ut
    JOIN task t ON ut.TID = t.id
    JOIN task_step ts ON t.id = ts.TID
    JOIN task_step_description tsd ON ts.TSID = tsd.TSID
    WHERE ut.UID = ?
    GROUP BY t.id, ut.current_step, ut.total_steps, t.task_title, t.points
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $taskId = $row['task_id'];
        $tasks[$taskId] = [
            'task_title'   => $row['task_title'],
            'task_point'   => $row['points'],
            'current_step' => $row['current_step'],
            'total_steps'  => $row['total_steps'],
            'descriptions' => explode("\n", $row['descriptions'])
        ];
    }
} else {
    echo '<p>No accepted tasks available.</p>';
}



$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lumimind</title>
    <link rel="stylesheet" href="../css/task.css" />
</head>
<body>
    <div class="container">
        <?php
        include '../sidebar/sidebar.html';
        ?>
    <style>
  .progress-container {
        background-color: #f1f1f1;
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 10px;
    }
    .progress-bar {
        height: 20px;
        background-color: #4caf50;
        text-align: center;
        color: white;
        line-height: 20px;
        transition: width 0.3s ease;
    }
    
    .giveup-btn {
        padding: 8px 18px;
        background-color: #865DFF;
        color: white;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .giveup-btn:hover {
        background-color: #6b47d9;
    }
    
    .next-btn {
        padding: 8px 18px;
        background-color: #865DFF;
        color: white;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .next-btn:hover {
        background-color: #6b47d9;
    }
    
    .complete-btn {
        padding: 8px 18px;
        background-color: #865DFF;
        color: white;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .complete-btn:hover {
        background-color: #6b47d9;
    }</style>
        <main class="main-content" id="mainContent">
            <header class="header">
                <h2>Task</h2>
                <span class="points">Points Obtained: <strong><?php echo $userPoints; ?>pts</strong></span>
             
            </header>

          <?php if (!empty($tasks)) : ?>
                <?php foreach ($tasks as $taskId => $task) : ?>
                    <?php
                    $progress = 0;
                    if ($task['total_steps'] > 0) {
                        $progress = round(($task['current_step'] / $task['total_steps']) * 100);
                    }
                    ?>
                    <section class="task">
                        <h3><?php echo htmlspecialchars($task['task_title']); ?> 
                            <span class="points-tag"><?php echo htmlspecialchars($task['task_point']); ?> Points</span>
                        </h3>

                     
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo $progress; ?>%;">
                                <?php echo $progress; ?>%
                            </div>
                        </div>

                        <ul>
                            <?php foreach ($task['descriptions'] as $description) : 
                                $trimmedItem = trim($description);
                                if (!empty($trimmedItem)) :
                            ?>
                                    <li><?php echo htmlspecialchars($trimmedItem); ?></li>
                            <?php 
                                endif;
                            endforeach; ?>
                        </ul>

                        <button class="giveup-btn" data-user-id="<?php echo $userId; ?>" data-task-id="<?php echo $taskId; ?>">Give Up</button>

<?php if ($task['current_step'] == $task['total_steps']) : ?>
    <button class="complete-btn"  data-points="<?php echo $task['task_point']; ?>" data-user-id="<?php echo $userId; ?>" data-task-id="<?php echo $taskId; ?>">Complete</button>
<?php else : ?>
    <button class="next-btn" data-user-id="<?php echo $userId; ?>" data-task-id="<?php echo $taskId; ?>">Next Step</button>
<?php endif; ?>
                    </section>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No accepted tasks available.</p>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
<script>document.addEventListener('DOMContentLoaded', function () {
    const nextButtons = document.querySelectorAll('.next-btn');
    nextButtons.forEach(button => {
        button.addEventListener('click', function () {
            const taskId = this.getAttribute('data-task-id');
            const userId = this.getAttribute('data-user-id');
            
            fetch('next_step.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ task_id: taskId, user_id: userId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Progress updated successfully!');
                    location.reload(); 
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
});
const completeButtons = document.querySelectorAll('.complete-btn');
completeButtons.forEach(button => {
    button.addEventListener('click', function () {
        const taskId = this.getAttribute('data-task-id');
        const userId = this.getAttribute('data-user-id');
const point = this.getAttribute('data-points');
        fetch('complete_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ task_id: taskId, user_id: userId , points:point})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Task completed successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});

</script>