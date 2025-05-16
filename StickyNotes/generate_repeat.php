<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../accountManagement/login.html');
    exit;
}
include 'db_connect.php';
function generateRepeatTask($note_id, $repeat_type, $interval, $weekly_days, $end_date, $note_date, $note_time, $fk_user, $title, $remarks, $category, $reminder, $reminder_time, $subtasks = []) {
    $current = new DateTime($note_date);
    $end = $end_date ? new DateTime($end_date) : null;
  
    if ($repeat_type === 'weekly' && !empty($weekly_days)) {
      $daysOfWeek = explode(',', $weekly_days);
  
      while (!$end || $current <= $end) {
        foreach ($daysOfWeek as $day) {
          $next = clone $current;
          $next->modify("next $day");
          if ($end && $next > $end) continue;
          if ($next < new DateTime($note_date)) continue; // skip dates before start
          insertNoteCopy($next->format('Y-m-d'), $note_time, $fk_user, $title, $remarks, $category, $reminder, $reminder_time, $note_id, $subtasks);
        }
        $current->modify("+$interval weeks");
      }
    } else {
      // Daily and Monthly logic
      while (true) {
        if ($repeat_type === 'daily') {
          $current->modify("+$interval days");
        } elseif ($repeat_type === 'monthly') {
          $current->modify("+$interval months");
        }
  
        if ($end && $current > $end) break;
        insertNoteCopy($current->format('Y-m-d'), $note_time, $fk_user, $title, $remarks, $category, $reminder, $reminder_time, $note_id, $subtasks);
      }
    }
}
  
function insertNoteCopy($date, $time, $fk_user, $title, $remarks, $category, $reminder, $reminder_time, $parent_note_id, $subtasks = []) {
  global $conn;
  $status = 'pending';
  $repeat_task = 1;

  $stmt = $conn->prepare("INSERT INTO notes (title, remarks, note_date, note_time, status, fk_user, category, repeat_task, reminder, parent_note_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssssssii", $title, $remarks, $date, $time, $status, $fk_user, $category, $repeat_task, $reminder, $parent_note_id);
  $stmt->execute();
  $new_note_id = $stmt->insert_id;

  // Insert subtasks for the new note
  if (!empty($subtasks)) {
    foreach ($subtasks as $subtask) {
      $text = $conn->real_escape_string($subtask['text']);
      $is_completed = isset($subtask['completed']) && $subtask['completed'] ? 1 : 0;
      if (!empty($text)) {
        $conn->query("INSERT INTO subtasks (note_id, subtask_text, is_completed) VALUES ('$new_note_id', '$text', '$is_completed')");
      }
    }
  }

  if ($reminder && $reminder_time) {
    $conn->query("INSERT INTO reminders (note_id, reminder_time) VALUES ('$new_note_id', '$reminder_time')");
  }
}
?>
