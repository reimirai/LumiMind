<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Note</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/add_note.css" />
    <?php 
include '../StickyNotes/db_connect.php';
include '../StickyNotes/generate_repeat.php';

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
  }
}
    ?>
    <style>
        /* Popup Styles */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000; /* Ensure it's on top */
        }

        .popup-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            width: 90%; /* Adjust as needed */
            max-width: 600px; /* Maximum width */
            position: relative; /* For close button positioning */
        }

        .popup-content .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .popup-content .top-bar h1 {
            font-size: 24px;
            margin: 0;
        }

        .popup-content .top-bar .back-btn {
            text-decoration: none;
            color: #555;
            font-size: 16px;
        }

        .popup-content .form-container {
            /* Remove or adjust styles that might conflict with popup */
        }

        .popup-content .form-container .back-btn {
            /* Style for the back button inside the popup */
            display: inline-block;
            padding: 8px 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        .popup-content .form-container .back-btn:hover {
            background-color: #eee;
        }

        .popup-content .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .popup-content .form-container input[type="text"],
        .popup-content .form-container input[type="date"],
        .popup-content .form-container input[type="time"],
        .popup-content .form-container input[type="number"],
        .popup-content .form-container select {
            width: calc(100% - 12px);
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .popup-content .form-container .row {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .popup-content .form-container .column {
            flex: 1;
        }

        .popup-content .form-container .switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 20px;
        }

        .popup-content .form-container .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .popup-content .form-container .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 20px;
        }

        .popup-content .form-container .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        .popup-content .form-container input:checked + .slider {
            background-color: #2196F3;
        }

        .popup-content .form-container input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        .popup-content .form-container input:checked + .slider:before {
            transform: translateX(20px);
        }

        .popup-content .form-container .conditional-field {
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 4px;
            background-color: #f9f9f9;
        }

        .popup-content .form-container .repeat-options {
            margin-top: 10px;
        }

        .popup-content .form-container .repeat-options label {
            font-weight: normal;
        }

        .popup-content .form-container .repeat-options input[type="number"] {
            width: 60px;
            display: inline-block;
            margin-right: 5px;
        }

        .popup-content .form-container .repeat-options > div {
            margin-top: 5px;
        }

        .popup-content .form-container .add-subtask-btn {
            background-color: #f0ad4e;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
            display: inline-block;
            text-decoration: none;
        }

        .popup-content .form-container .add-subtask-btn:hover {
            background-color: #e0952d;
        }

        .popup-content .form-container .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }

        .popup-content .form-container .modal-content {
            background-color: #fefefe;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 8px;
            width: 80%; /* Could be more or less, depending on screen size */
            max-width: 400px;
        }

        .popup-content .form-container .modal-content h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }

        .popup-content .form-container .modal-content input[type="text"] {
            width: calc(100% - 12px);
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .popup-content .form-container .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .popup-content .form-container .modal-buttons button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .popup-content .form-container .modal-buttons button:first-child {
            background-color: #5cb85c;
            color: white;
        }

        .popup-content .form-container .modal-buttons button:last-child {
            background-color: #d9534f;
            color: white;
        }

        .popup-content .form-container .sub-task-list {
            list-style-type: none;
            padding: 0;
            margin-bottom: 10px;
            border: 1px solid #eee;
            border-radius: 4px;
            background-color: #f9f9f9;
            padding: 10px;
        }

        .popup-content .form-container .sub-task-list li {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .popup-content .form-container .sub-task-list li input[type="checkbox"] {
            margin-right: 8px;
        }

        .popup-content .form-container .sub-task-list li input[type="text"] {
            flex-grow: 1;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 8px;
        }

        .popup-content .form-container .sub-task-list li button {
            background: none;
            border: none;
            color: #d9534f;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
            padding: 0;
        }

        .popup-content .form-container .footer {
            margin-top: 20px;
            text-align: right;
        }

        .popup-content .form-container .footer .submit-btn {
            background-color: #5cb85c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .popup-content .form-container .footer .submit-btn:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>

    <div class="popup-overlay">
        <div class="popup-content">
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
    </div>
</body>

</html>