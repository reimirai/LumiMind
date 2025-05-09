<?php 
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "LumiMind";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get approved tasks along with their descriptions
$sql = "
    SELECT
        t.id AS task_id,
        t.task_title,
        t.points,
        tsd.description
    FROM
        task t
    JOIN
        task_step ts ON t.id = ts.TID
    JOIN
        task_step_description tsd ON ts.TSID = tsd.TSID
    WHERE t.status = 'Approved'
";

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Get the tasks into an associative array, using the task_id as the key
$tasks = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $curTaskId = $row['task_id'];
        if (!isset($tasks[$curTaskId])) {
            $tasks[$curTaskId] = [
                'task_title'   => $row['task_title'],
                'task_point'   => $row['points'],
                'descriptions' => []
            ];
        }
        $tasks[$curTaskId]['descriptions'][] = $row['description'];
    }
} else {
    echo '<p>No tasks available.</p>';
}

// Get current user details (id and points)
$sql1 = "SELECT id, points FROM Users WHERE id = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("s", $_SESSION['user_id']);
$stmt1->execute();
$stmt1->store_result();
$stmt1->bind_result($userId, $userPoints);
if (!$stmt1->fetch()) {
    echo "No records found for this user.";
}
$stmt1->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Lumimind - Tasks</title>
    <link rel="stylesheet" href="../css/task.css" />
     <style>
  .form-popup {
  display: none;
  position: fixed;
  top: 10%;
  left: 50%;
  transform: translateX(-50%);
  width: 80%;
  max-width: 700px;
  background: #fff;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 0 15px rgba(0,0,0,0.2);
  z-index: 1000;
  font-family: 'Arial', sans-serif;
}

.form-popup h5 {
  font-size: 28px;
  font-weight: bold;
  margin-bottom: 25px;
  border-left: 5px solid #FFD600;
  padding-left: 10px;
}

.form-popup input,
.form-popup textarea {
  width: 100%;
  padding: 12px;
  margin-bottom: 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  box-sizing: border-box;
}

.form-popup textarea {
  resize: vertical;
  height: 100px;
}

.form-popup .form-group {
  display: flex;
  gap: 15px;
}

.form-popup .form-group input {
  flex: 1;
}

.form-popup .submit-btn {
  background-color: #FFD600;
  color: black;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
}

.form-popup .submit-btn:hover {
  background-color: #e6c200;
}

.form-popup .close-btn {
  background: none;
  color: #333;
  border: none;
  margin-left: 10px;
  font-size: 14px;
  cursor: pointer;
}
 .overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background-color: rgba(0,0,0,0.5);
      z-index: 998;
    }
  </style>
</head>

<body>
    <div class="container">
        <?php include_once '../sidebar/sidebar.html'; ?>

        <main class="main-content" id="mainContent">
            <header class="header">
                <h2>Task</h2>
                <span class="add-new-task">
                    <button  onclick="showForm()" id="newtask-btn" class="newtask-btn">Add New Task</button>
                </span>
            </header>

<div class="overlay" id="overlay" onclick="hideForm()"></div>
<div class="form-popup" id="myForm">
  <h5 class="modal-title">Add New Task</h5>

  <div class="form-group">
    <input type="text" id="taskName" placeholder="Task Name">
    <input type="number" id="taskStep" placeholder="Task Step" min="1" max="10" onchange="generateSteps()">
  </div>

  <div id="stepsContainer"></div>

  <button class="submit-btn" onclick="submitForm()">Submit</button>
  <button class="close-btn" onclick="hideForm()">Close</button>
</div>

            <?php if (!empty($tasks)) : ?>
                <?php foreach ($tasks as $taskId => $task) : ?>
                    <section class="task">
                        <h3>
                            <?php echo htmlspecialchars($task['task_title']); ?> 
                            <span class="points-tag"><?php echo htmlspecialchars($task['task_point']); ?> Points</span>
                        </h3>
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
                        <button class="accept-btn" data-user-id="<?php echo $userId; ?>" data-task-id="<?php echo $taskId; ?>">
                            Accept
                        </button>
                    </section>
                <?php endforeach; ?>
            <?php else : ?>
                <p>No tasks available.</p>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.accept-btn');
        buttons.forEach(button => {
            button.addEventListener('click', function () {
                const taskId = this.getAttribute('data-task-id');
                const userId = this.getAttribute('data-user-id');
                fetch('accept_task.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ task_id: taskId, user_id: userId })
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
   
<script>
  function showForm() {
    document.getElementById("myForm").style.display = "block";
    document.getElementById("overlay").style.display = "block";
  }

  function hideForm() {
    document.getElementById("myForm").style.display = "none";
    document.getElementById("overlay").style.display = "none";
  }

  function submitForm() {
  const taskName = document.getElementById('taskName').value;
  const taskStep = parseInt(document.getElementById('taskStep').value);

  const stepInputs = document.querySelectorAll('.step-input');
  const steps = [];
  stepInputs.forEach((input) => {
      steps.push(input.value);
  });

  const data = {
      taskName: taskName,
      taskStep: taskStep,
      steps: steps
  };

  fetch('submit_task.php', {
      method: 'POST',
      headers: {
          'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(responseData => {
      if (responseData.success) {
          alert('Task submitted successfully!');
      } else {
          alert('Error: ' + responseData.message);
      }
  })
  .catch(error => {
      console.error('Fetch error:', error);
  });
}

  
function generateSteps() {
  const stepCount = parseInt(document.getElementById('taskStep').value);
  const container = document.getElementById('stepsContainer');
  container.innerHTML = ''; 

  if (!isNaN(stepCount) && stepCount > 0) {
    for (let i = 1; i <= stepCount; i++) {
      const input = document.createElement('input');
      input.type = 'text';
      input.placeholder = 'Step ' + i;
      input.className = 'step-input';
container.appendChild(input);
}
}
}
</script>

</body>
</html> 
