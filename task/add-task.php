<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "LumiMind";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
    SELECT
        t.id AS task_id,
        t.task_title,
        t.task_point,
        tsd.description
    FROM
        task t
    JOIN
        task_step ts ON t.id = ts.fk_task
    JOIN
        task_step_description tsd ON ts.id = tsd.task_step_id
    WHERE t.status='Approved';
";


$result = $conn->query($sql);

$sql1 = "SELECT id, points FROM User";
$result1 = $conn->query($sql1);

if ($result1->num_rows > 0) {
    $row = $result1->fetch_assoc();
    $userId = $row['id'];
    $userPoints = $row['points'];
} else {
    echo "No records found.";
}

if (!$result) {
    die("Query failed: " . $conn->error);
}

  $tasks = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $taskId = $row['task_id'];
        if (!isset($tasks[$taskId])) {
            $tasks[$taskId] = [
                'task_title' => $row['task_title'],
                'task_point' => $row['task_point'],
                'descriptions' => [],
            ];
        }
        $tasks[$taskId]['descriptions'][] = $row['description'];
    }
} else {
    echo '<p>No tasks available.</p>';
}

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
        include_once '../sidebar/sidebar.html';
        ?>

        <main class="main-content" id="mainContent">
            <header class="header">
                <h2>Task</h2>
                 <span class="add-new-task">
  <button id="newtask-btn" class="newtask-btn">Add New Task</button>
</span>
            </header>

                 <?php if (!empty($tasks)) : ?>
    <?php foreach ($tasks as $task) : ?>
        <section class="task">
            <h3><?php echo htmlspecialchars($task['task_title']); ?> <span class="points-tag"><?php echo htmlspecialchars($task['task_point']); ?> Points</span></h3>
            <ul>
                <?php foreach ($task['descriptions'] as $description) : ?>
                    <?php
                    
                    $descriptionItems = explode("\n", $description);
                    foreach ($descriptionItems as $item) :
                        $trimmedItem = trim($item);
                        if (!empty($trimmedItem)) :
                    ?>
                            <li><?php echo htmlspecialchars($trimmedItem); ?></li>
                    <?php
                        endif;
                    endforeach;
                    ?>
                <?php endforeach; ?>
            </ul>
            <button class="accept-btn" data-user-id="<?php echo $userId; ?>" data-task-id="<?php echo $taskId; ?>">Accept</button>
        </section>
    <?php endforeach; ?>
<?php else : ?>
    <p>No tasks available.</p>
<?php endif; ?>
        </main>
    </div>
</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.accept-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function () {
            const taskId = this.getAttribute('data-task-id');
            const userid = this.getAttribute('data-user-id');
            fetch('accept_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
               body: JSON.stringify({ task_id: taskId, user_id: userid })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Task accepted successfully!');
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
</script>