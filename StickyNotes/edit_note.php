<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<?php
include 'db_connect.php';

$noteId = $_GET['id'];
$note = [];
$repeat_type = '';
$reminder_time = '';
$repeat_end_type = '';
$repeat_end_date = '';
$daily_interval = 1;
$weekly_interval = 1;
$weekly_days = [];
$monthly_interval = 1;

// Fetch note
$stmt = $conn->prepare("SELECT * FROM notes WHERE id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $note = $result->fetch_assoc();
} else {
    die("Note not found.");
}

$stmt = $conn->prepare("SELECT * FROM repeat_task WHERE note_id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $repeat_type = $row['repeat_type'];
    // Ensure repeat_end_type is set, defaulting to 'never' if it's not found
    $repeat_end_type = isset($row['repeat_end_type']) ? $row['repeat_end_type'] : 'never';
    $repeat_end_date = isset($row['repeat_end_date']) ? $row['repeat_end_date'] : '';
    $daily_interval = isset($row['daily_interval']) ? $row['daily_interval'] : 1;
    $weekly_interval = isset($row['weekly_interval']) ? $row['weekly_interval'] : 1;
    $weekly_days = isset($row['weekly_days']) ? explode(',', $row['weekly_days']) : [];
    $monthly_interval = isset($row['monthly_interval']) ? $row['monthly_interval'] : 1;
} else {
    // Default values if no repeat task found
    $repeat_end_type = 'never';
}

// Fetch reminder time
$stmt = $conn->prepare("SELECT reminder_time FROM reminders WHERE note_id = ?");
$stmt->bind_param("i", $noteId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $reminder_time = $row['reminder_time'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $remarks = $_POST['remarks'];
    $note_date = $_POST['note_date'];
    $note_time = $_POST['note_time'];
    $repeat_task = isset($_POST['repeatToggle']) ? 1 : 0;
    $reminder = isset($_POST['reminder_toggle']) ? 1 : 0;
    $reminder_time = $_POST['reminder_time_dropdown'] ?? '';
    $subtasks = json_decode($_POST['subtasks'], true);
    $repeat_type = $_POST['repeat_type'] ?? '';
    $daily_interval = $_POST['daily_interval'] ?? 1;
    $weekly_interval = $_POST['weekly_interval'] ?? 1;
    $weekly_days = isset($_POST['weekly_days']) ? implode(',', $_POST['weekly_days']) : '';
    $monthly_interval = $_POST['monthly_interval'] ?? 1;
    $repeat_end_type = isset($_POST['repeat_end_type']) ? $_POST['repeat_end_type'] : 'never';
    $repeat_end_date = ($repeat_end_type === 'on' && !empty($_POST['repeat_end_date'])) ? $_POST['repeat_end_date'] : null;

    // Get the note's parent_note_id (if any)
    $stmt = $conn->prepare("SELECT id, parent_note_id FROM notes WHERE id = ?");
    $stmt->bind_param("i", $noteId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $parent_id = $row['parent_note_id'] ? $row['parent_note_id'] : $row['id'];

    // Update all notes in the series (original and all repeats)
    $stmt = $conn->prepare("UPDATE notes SET title=?, category=?, remarks=?, note_date=?, note_time=?, repeat_task=?, reminder=? WHERE id = ? OR parent_note_id = ?");
    $stmt->bind_param("sssssiiii", $title, $category, $remarks, $note_date, $note_time, $repeat_task, $reminder, $parent_id, $parent_id);
    $stmt->execute();

    // Update repeat_task table for the series (only for the parent/original note)
    if ($repeat_task) {
        // Check if repeat task entry exists
        $checkStmt = $conn->prepare("SELECT id FROM repeat_task WHERE note_id = ?");
        $checkStmt->bind_param("i", $parent_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Update existing repeat task
            $stmt = $conn->prepare("UPDATE repeat_task SET repeat_type=?, repeat_end_type=?, repeat_end_date=?, daily_interval=?, weekly_interval=?, weekly_days=?, monthly_interval=? WHERE note_id=?");
            $stmt->bind_param("ssssissi", $repeat_type, $repeat_end_type, $repeat_end_date, $daily_interval, $weekly_interval, $weekly_days, $monthly_interval, $parent_id);
        } else {
            // Insert new repeat task
            $stmt = $conn->prepare("INSERT INTO repeat_task (note_id, repeat_type, repeat_end_type, repeat_end_date, daily_interval, weekly_interval, weekly_days, monthly_interval) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssissi", $parent_id, $repeat_type, $repeat_end_type, $repeat_end_date, $daily_interval, $weekly_interval, $weekly_days, $monthly_interval);
        }
        $stmt->execute();
    } else {
        // If repeat task is disabled, delete the entry
        $stmt = $conn->prepare("DELETE FROM repeat_task WHERE note_id = ?");
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
    }

    // Update reminders for all notes in the series
    $conn->query("DELETE FROM reminders WHERE note_id = $parent_id OR note_id IN (SELECT id FROM notes WHERE parent_note_id = $parent_id)");
    if ($reminder && $reminder_time) {
        // Add reminder for each note in the series
        $result = $conn->query("SELECT id FROM notes WHERE id = $parent_id OR parent_note_id = $parent_id");
        while ($row = $result->fetch_assoc()) {
            $nid = $row['id'];
            $stmt = $conn->prepare("INSERT INTO reminders (note_id, reminder_time) VALUES (?, ?)");
            $stmt->bind_param("is", $nid, $reminder_time);
            $stmt->execute();
        }
    }

    // Update subtasks for all notes in the series
    $conn->query("DELETE FROM subtasks WHERE note_id = $parent_id OR note_id IN (SELECT id FROM notes WHERE parent_note_id = $parent_id)");
    $result = $conn->query("SELECT id FROM notes WHERE id = $parent_id OR parent_note_id = $parent_id");
    while ($row = $result->fetch_assoc()) {
        $nid = $row['id'];
        foreach ($subtasks as $subtask) {
            $subtaskText = $conn->real_escape_string($subtask['text']);
            $isCompleted = $subtask['completed'] ? 1 : 0;
            $conn->query("INSERT INTO subtasks (note_id, subtask_text, is_completed) VALUES ('$nid', '$subtaskText', '$isCompleted')");
        }
    }

    header("Location: StickyNote.php?updated=success");
    exit();
}

// Fetch subtasks
$subtasks = [];
$subtaskResult = $conn->query("SELECT * FROM subtasks WHERE note_id = '$noteId'");
while ($row = $subtaskResult->fetch_assoc()) {
    $subtasks[] = $row;
}
?>

<!-- HTML Part (unchanged except variable bindings) -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Note</title>
    <link rel="stylesheet" href="../css/edit_note.css">
</head>

<body>
    <div class="form-container">
        <div class="top-bar">
            <h1><span class="yellow-bar"></span>Edit Note</h1>
            <a href="StickyNote.php" class="back-btn">Back</a>
        </div>

        <form method="POST" onsubmit="prepareSubTasks()">
            <label>Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($note['title']); ?>" required>

            <label>Sub Note</label>
            <button type="button" class="add-subtask-btn" onclick="openModal()">+ Add Sub-Note</button>
            <ul id="subTaskList" class="sub-task-list">
                <?php foreach ($subtasks as $subtask): ?>
                    <li>
                        <input type="checkbox" <?php echo $subtask['is_completed'] ? 'checked' : ''; ?>
                            onclick="toggleComplete(this)">
                        <input type="text" value="<?php echo htmlspecialchars($subtask['subtask_text']); ?>">
                        <button type="button" class="remove-subtask-btn" onclick="removeSubTask(this)">&times;</button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <label>Remarks</label>
            <input type="text" name="remarks" value="<?php echo htmlspecialchars($note['remarks']); ?>">

            <label>Category</label>
            <select name="category" required>
                <?php
                $categories = ['Work', 'Personal', 'Wishlist', 'Birthday', 'Daily'];
                foreach ($categories as $cat) {
                    $selected = ($cat === $note['category']) ? 'selected' : '';
                    echo "<option value='$cat' $selected>$cat</option>";
                }
                ?>
            </select>

            <div class="row">
                <div class="column">
                    <label>Date</label>
                    <input type="date" name="note_date" value="<?php echo htmlspecialchars($note['note_date']); ?>">
                </div>
                <div class="column">
                    <label>Time</label>
                    <input type="time" name="note_time" value="<?php echo htmlspecialchars($note['note_time']); ?>">
                </div>
            </div>

            <div class="row">
                <div class="column">
                    <label>Repeat Task</label>
                    <label class="switch">
                        <input type="checkbox" id="repeatToggle" name="repeatToggle" value="1" <?= $note['repeat_task'] ? 'checked' : '' ?> onchange="toggleRepeatTaskOptions()">
                        <span class="slider round"></span>
                    </label>
                    <!-- Inside your existing file, just replace this part -->
                    <div id="repeatOptions" class="conditional-field" style="display: none;">
                        <label for="repeat_task">Repeat Type:</label>
                        <select name="repeat_type" id="repeat_task" onchange="toggleRepeatOptions()">
                            <option value="">Select</option>
                            <option value="daily" <?= $repeat_type === 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="weekly" <?= $repeat_type === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="monthly" <?= $repeat_type === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        </select>

                        <!-- Repeat Options (now not wrapped in a second #repeatOptions) -->
                        <div id="dailyOptions" style="display: none;">
                            <label>Repeat every:</label>
                            <input type="number" name="daily_interval" value="<?= $daily_interval ?>">
                            day(s)
                        </div>

                        <div id="weeklyOptions" style="display: none;">
                            <label>Repeat every:</label>
                            <input type="number" name="weekly_interval" value="<?= $weekly_interval ?>">
                            week(s)
                            <label>Repeat on:</label><br>
                            <?php
                            $selected_days = $weekly_days;
                            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                            foreach ($days as $day): ?>
                                <label>
                                    <input type="checkbox" name="weekly_days[]" value="<?= $day ?>" <?= in_array($day, $weekly_days) ? 'checked' : '' ?>>
                                    <?= $day ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div id="monthlyOptions" style="display: none;">
                            <label>Repeat every:</label>
                            <input type="number" name="monthly_interval" value="<?= $monthly_interval ?>">
                            month(s)
                        </div>

                        <div>
                            <label for="repeat_end_type">Ends:</label>
                            <select name="repeat_end_type" id="repeat_end_type" onchange="toggleRepeatEndDate()">
                                <option value="never" <?= $repeat_end_type === 'never' ? 'selected' : '' ?>>Never</option>
                                <option value="on" <?= $repeat_end_type === 'on' ? 'selected' : '' ?>>On</option>
                            </select>
                        </div>

                        <div id="repeatEndDateContainer" style="display: none;">
                            <label for="repeat_end_date">End Date:</label>
                            <input type="date" name="repeat_end_date" id="repeat_end_date"
                                value="<?= $repeat_end_date ?? '' ?>">
                        </div>
                    </div>
                </div>

                <div class="column">
                    <label>Reminder</label>
                    <label class="switch">
                        <input type="checkbox" id="reminderToggle" name="reminder_toggle" <?php if ($note['reminder'])
                            echo 'checked'; ?> onchange="toggleReminderOptions()">
                        <span class="slider round"></span>
                    </label>
                    <div id="reminderOptions" class="conditional-field" style="display: none;">
                        <label>Reminder Time</label>
                        <select name="reminder_time_dropdown">
                            <option value="">Select</option>
                            <option value="same" <?php if ($reminder_time == 'same')
                                echo 'selected'; ?>>Same as due time
                            </option>
                            <option value="5" <?php if ($reminder_time == '5')
                                echo 'selected'; ?>>5 minutes before
                            </option>
                            <option value="10" <?php if ($reminder_time == '10')
                                echo 'selected'; ?>>10 minutes before
                            </option>
                            <option value="15" <?php if ($reminder_time == '15')
                                echo 'selected'; ?>>15 minutes before
                            </option>
                            <option value="30" <?php if ($reminder_time == '30')
                                echo 'selected'; ?>>30 minutes before
                            </option>
                            <option value="1440" <?php if ($reminder_time == '1440')
                                echo 'selected'; ?>>1 day before
                            </option>
                            <option value="2880" <?php if ($reminder_time == '2880')
                                echo 'selected'; ?>>2 days before
                            </option>
                        </select>
                    </div>
                </div>
            </div>
    <input type="hidden" id="subtasksInput" name="subtasks">
    <div class="footer">
        <button type="submit" class="submit-btn" style="font-size: 16px;">Edit</button>
        </div>
    </div>
    </div>



    <!-- Modal -->
    <div id="subTaskModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Add Sub-Task</h2>
            <input type="text" id="subTaskInput" placeholder="Enter sub-task">
            <button type="button" class="adding-subtask" onclick="addSubTask()">Add</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            toggleRepeatTaskOptions();
            toggleRepeatOptions();
            toggleRepeatEndDate();
            toggleReminderOptions();
        });

        function toggleRepeatTaskOptions() {
            const isChecked = document.getElementById('repeatToggle').checked;
            document.getElementById('repeatOptions').style.display = isChecked ? 'block' : 'none';
        }

        function toggleReminderOptions() {
            const isChecked = document.getElementById('reminderToggle').checked;
            document.getElementById('reminderOptions').style.display = isChecked ? 'block' : 'none';
        }

        function toggleRepeatOptions() {
            const repeatType = document.getElementById('repeat_task').value;
            document.getElementById('dailyOptions').style.display = (repeatType === 'daily') ? 'block' : 'none';
            document.getElementById('weeklyOptions').style.display = (repeatType === 'weekly') ? 'block' : 'none';
            document.getElementById('monthlyOptions').style.display = (repeatType === 'monthly') ? 'block' : 'none';
        }

        function toggleRepeatEndDate() {
            const endType = document.getElementById('repeat_end_type').value;
            document.getElementById('repeatEndDateContainer').style.display = (endType === 'on') ? 'block' : 'none';
        }

        function openModal() {
            document.getElementById('subTaskModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('subTaskModal').style.display = 'none';
            document.getElementById('subTaskInput').value = '';
        }

        function addSubTask() {
            const input = document.getElementById('subTaskInput');
            const text = input.value.trim();
            if (!text) return;

            const ul = document.getElementById('subTaskList');
            const li = document.createElement('li');

            li.innerHTML = `
            <input type="checkbox" onclick="toggleComplete(this)">
            <input type="text" value="${text}">
            <button type="button" onclick="removeSubTask(this)">❌</button>
        `;
            ul.appendChild(li);
            input.value = '';
            closeModal();
        }

        function removeSubTask(button) {
            button.parentElement.remove();
        }

        function toggleComplete(checkbox) {
            const input = checkbox.nextElementSibling;
            if (checkbox.checked) {
                input.style.textDecoration = 'line-through';
            } else {
                input.style.textDecoration = 'none';
            }
        }

        function prepareSubTasks() {
            const subtasks = [];
            const listItems = document.querySelectorAll('#subTaskList li');

            listItems.forEach(li => {
                const textInput = li.querySelector('input[type="text"]');
                const checkbox = li.querySelector('input[type="checkbox"]');
                subtasks.push({
                    text: textInput.value,
                    completed: checkbox.checked
                });
            });

            document.getElementById('subtasksInput').value = JSON.stringify(subtasks);
        }
    </script>

</body>

</html>