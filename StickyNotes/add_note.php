<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="../css/add_note.css" />

<?php
include 'db_connect.php';
include 'generate_repeat.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $remarks = $_POST['remarks'];
  $category = $_POST['category'];
  $note_date = !empty($_POST['note_date']) ? $_POST['note_date'] : date('Y-m-d');
  $note_time = $_POST['note_time'];
  $fk_user = $_POST['fk_user'];
  $status = "pending";
  $repeat_task = isset($_POST['repeat_task']) ? 1 : 0;
  $reminder = isset($_POST['reminder']) ? 1 : 0;

  $sql = "INSERT INTO notes (title, remarks, note_date, note_time, status, fk_user, category, repeat_task, reminder)
            VALUES ('$title', '$remarks', '$note_date', '$note_time', '$status', '$fk_user', '$category', '$repeat_task', '$reminder')";

  if ($conn->query($sql) === TRUE) {
    $note_id = $conn->insert_id;

    if (!empty($_POST['subtasks_json'])) {
      $subtasks = json_decode($_POST['subtasks_json'], true);
      foreach ($subtasks as $subtask) {
        $text = trim($subtask['text']);
        $is_completed = isset($subtask['completed']) && $subtask['completed'] ? 1 : 0;
        if (!empty($text)) {
          $conn->query("INSERT INTO subtasks (note_id, subtask_text, is_completed) VALUES ('$note_id', '$text', '$is_completed')");
        }
      }
    }

    if ($repeat_task) {
      $repeat_type = $_POST['repeat_type'];
      $daily_interval = $_POST['daily_interval'] ?? null;
      $weekly_interval = $_POST['weekly_interval'] ?? null;
      $monthly_interval = $_POST['monthly_interval'] ?? null;
      $weekly_days = isset($_POST['weekly_days']) ? implode(',', $_POST['weekly_days']) : null;
      $repeat_end_type = $_POST['repeat_end_type'];
      $repeat_end_date = $_POST['repeat_end_date'] ?? null;

      $conn->query("INSERT INTO repeat_task
            (note_id, repeat_type, daily_interval, weekly_interval, monthly_interval, weekly_days, repeat_end_type, repeat_end_date)
            VALUES ('$note_id', '$repeat_type', '$daily_interval', '$weekly_interval', '$monthly_interval', '$weekly_days', '$repeat_end_type', '$repeat_end_date')");

      // Determine interval based on type
      $interval = $repeat_type === 'daily' ? $daily_interval : ($repeat_type === 'weekly' ? $weekly_interval : $monthly_interval);
      $end_date = $repeat_end_type === 'on' ? $repeat_end_date : null;

      $maxRepeats = 365; // or any reasonable number
      $count = 0;

      // Get subtasks for the original note
      $subtasks = [];
      if (!empty($_POST['subtasks_json'])) {
        $subtasks = json_decode($_POST['subtasks_json'], true);
      }

      while ((!$end_date || $current <= $end_date) && $count < $maxRepeats) {
        generateRepeatTask(
          $note_id,
          $repeat_type,
          $interval,
          $weekly_days,
          $end_date,
          $note_date,
          $note_time,
          $fk_user,
          $title,
          $remarks,
          $category,
          $reminder,
          $_POST['reminder_time'] ?? null,
          $subtasks
        );
        $current = new DateTime($note_date);
        $current->modify("+$interval days/weeks/months");
        $count++;
      }
    }

    if ($reminder && isset($_POST['reminder_time'])) {
      $reminder_time = $_POST['reminder_time'];
      $conn->query("INSERT INTO reminders (note_id, reminder_time) VALUES ('$note_id', '$reminder_time')");
    }

    header("Location: StickyNote.php?added=success");
    exit();
  } else {
    echo "Error: " . $stmt->error;
  }
  $conn->close();
}
?>

<div class="form-container">
  <div class="top-bar">
    <h1><span class="yellow-bar"></span>Add New Note</h1>
    <a href="StickyNote.php" class="back-btn">Back</a>
  </div>

  <form action="add_note.php" method="POST">
    <label>Title</label>
    <input type="text" name="title" required>

    <label>Sub Note</label>
    <button type="button" class="add-subtask-btn" onclick="openModal()">+ Add Sub-Note</button>

    <ul id="subTaskList" class="sub-task-list"></ul>

    <!-- Hidden input to send subtask data -->
    <input type="hidden" name="subtasks_json" id="subtasksJson">

    <!-- Subtask Modal -->
    <div id="subtaskModal" class="modal" style="display: none;">
      <div class="modal-content">
        <h3>Add Sub Note</h3>
        <input type="text" id="newSubTaskInput" placeholder="Enter sub note...">
        <div class="modal-buttons">
          <button type="button" onclick="addSubTask()">Add</button>
          <button type="button" onclick="closeModal()">Cancel</button>
        </div>
      </div>
    </div>

    <label>Remarks</label>
    <input type="text" name="remarks">

    <label>Category</label>
    <select name="category">
      <option value="Work">Work</option>
      <option value="Personal">Personal</option>
      <option value="Wishlist">Wishlist</option>
      <option value="Birthday">Birthday</option>
      <option value="Daily">Daily</option>
    </select>
    <div class="row">
      <div class="column">
        <label>Date</label>
        <input type="date" name="note_date">
      </div>
      <div class="column">
        <label>Time</label>
        <input type="time" name="note_time">
      </div>
    </div>

    <div class="row">
      <div class="column">
        <!-- Repeat Toggle -->
        <label>Repeat Task</label>
        <label class="switch">
          <input type="checkbox" id="repeatToggle" name="repeat_task" onchange="toggleRepeatFields()">
          <span class="slider"></span>
        </label>

        <div class="conditional-field" id="repeatFields">
          <label>Repeat Type</label>
          <select name="repeat_type" onchange="toggleRepeatOptions(this.value)">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>

          <div id="dailyOptions" class="repeat-options">
            <label>Repeat every</label>
            <input type="number" name="daily_interval" min="1">
            day(s)
          </div>

          <div id="weeklyOptions" class="repeat-options" style="display: none;">
            <label>Repeat every</label>
            <input type="number" name="weekly_interval" min="1">
            week(s)
            <label>Repeat on:</label>
            <div>
              <?php
              $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
              foreach ($days as $day) {
                echo "<label><input type='checkbox' name='weekly_days[]' value='$day'> $day</label> ";
              }
              ?>
            </div>
          </div>

          <div id="monthlyOptions" class="repeat-options" style="display: none;">
            <label>Repeat every</label>
            <input type="number" name="monthly_interval" min="1">
            months(s)
          </div>

          <label>Ends</label>
          <select name="repeat_end_type" onchange="toggleEndDate(this.value)">
            <option value="never">Never</option>
            <option value="on">On Date</option>
          </select>

          <div id="endDateField" style="display: none;">
            <label>Repeat End Date</label>
            <input type="date" name="repeat_end_date">
          </div>
        </div>

      </div>

      <div class="column">
        <!-- Reminder Toggle -->
        <label>Reminder</label>
        <label class="switch">
          <input type="checkbox" id="reminderToggle" name="reminder" onchange="toggleReminderField()">
          <span class="slider"></span>
        </label>

        <div class="conditional-field" id="reminderField">
          <label>Reminder Time</label>
          <select name="reminder_time" id="reminder_time">
            <option value="">Select</option>
            <option value="same" <?php if (isset($reminder_time) && $reminder_time == 'same')
              echo 'selected'; ?>>Same as
              due time</option>
            <option value="5" <?php if (isset($reminder_time) && $reminder_time == '5')
              echo 'selected'; ?>>5 minutes
              before</option>
            <option value="10" <?php if (isset($reminder_time) && $reminder_time == '10')
              echo 'selected'; ?>>10 minutes
              before</option>
            <option value="15" <?php if (isset($reminder_time) && $reminder_time == '15')
              echo 'selected'; ?>>15 minutes
              before</option>
            <option value="30" <?php if (isset($reminder_time) && $reminder_time == '30')
              echo 'selected'; ?>>30 minutes
              before</option>
            <option value="1440" <?php if (isset($reminder_time) && $reminder_time == '1440')
              echo 'selected'; ?>>1 day
              before</option>
            <option value="2880" <?php if (isset($reminder_time) && $reminder_time == '2880')
              echo 'selected'; ?>>2 days
              before</option>
          </select>
        </div>

      </div>
    </div>

    <input type="hidden" name="fk_user" value="<?php echo $_SESSION['user_id']; ?>" >

    <div class="footer">
      <button type="submit" class="submit-btn">Add Note</button>
    </div>
  </form>
</div>

<script>
  function toggleRepeatFields() {
    const checked = document.getElementById("repeatToggle").checked;
    document.getElementById("repeatFields").style.display = checked ? "block" : "none";
  }

  function toggleReminderField() {
    const checked = document.getElementById("reminderToggle").checked;
    document.getElementById("reminderField").style.display = checked ? "block" : "none";
  }

  function toggleRepeatOptions(value) {
    document.getElementById("dailyOptions").style.display = value === 'daily' ? 'block' : 'none';
    document.getElementById("weeklyOptions").style.display = value === 'weekly' ? 'block' : 'none';
    document.getElementById("monthlyOptions").style.display = value === 'monthly' ? 'block' : 'none';
  }

  function toggleEndDate(value) {
    document.getElementById("endDateField").style.display = value === 'on' ? 'block' : 'none';
  }

  let subTasks = [];

  function openModal() {
    document.getElementById('subtaskModal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('newSubTaskInput').value = '';
    document.getElementById('subtaskModal').style.display = 'none';
  }

  function addSubTask() {
    const input = document.getElementById('newSubTaskInput');
    const text = input.value.trim();
    if (text) {
      subTasks.push({ text, completed: false });
      renderSubTasks();
      closeModal();
    }
  }

  function renderSubTasks() {
    const list = document.getElementById('subTaskList');
    // Save focus and cursor position
    const active = document.activeElement;
    let activeIndex = null;
    let cursorPos = null;
    if (active && active.tagName === 'INPUT' && active.type === 'text') {
      activeIndex = Array.from(list.children).findIndex(
        li => li.querySelector('input[type="text"]') === active
      );
      cursorPos = active.selectionStart;
    }

    list.innerHTML = '';
    subTasks.forEach((task, index) => {
      const li = document.createElement('li');
      li.innerHTML = `
      <input type="checkbox" ${task.completed ? 'checked' : ''} onclick="toggleComplete(${index})">
      <input type="text" value="${task.text}" oninput="editSubTask(${index}, this.value)">
      <button type="button" onclick="removeSubTask(${index})">❌</button>
    `;
      list.appendChild(li);
    });

    // Restore focus and cursor position
    if (activeIndex !== null && list.children[activeIndex]) {
      const input = list.children[activeIndex].querySelector('input[type=\"text\"]');
      if (input) {
        input.focus();
        if (cursorPos !== null) input.setSelectionRange(cursorPos, cursorPos);
      }
    }

    // Update hidden input with JSON
    document.getElementById('subtasksJson').value = JSON.stringify(subTasks);
  }

  function toggleComplete(index) {
    subTasks[index].completed = !subTasks[index].completed;
    renderSubTasks();
  }

  function editSubTask(index, value) {
    subTasks[index].text = value;
    renderSubTasks();
  }

  function removeSubTask(index) {
    subTasks.splice(index, 1);
    renderSubTasks();
  }



</script>