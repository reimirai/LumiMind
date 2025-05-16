<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get the note's parent_note_id (if any)
    $stmt = $conn->prepare("SELECT id, status FROM notes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $parent_id = $row['parent_note_id'] ? $row['parent_note_id'] : $id;
    $is_completed = $row['status'] === 'Completed';

    // Delete the note and all its related data
    $conn->query("DELETE FROM subtasks WHERE note_id = '$id'");
    $conn->query("DELETE FROM reminders WHERE note_id = '$id'");
    
    // For repeat tasks, only delete pending tasks unless it's a completed note
    if ($parent_id == $id && !$is_completed) {
        // This is a parent note, delete all pending tasks in the series
        $conn->query("DELETE FROM notes WHERE (id = '$id' OR parent_note_id = '$id') AND status = 'Pending'");
        $conn->query("DELETE FROM repeat_task WHERE note_id = '$parent_id'");
    } else {
        // This is a single note, a child note, or a completed note - delete it
        $conn->query("DELETE FROM notes WHERE id = '$id'");
    }
}

// Redirect back to StickyNote with success parameter
header("Location: StickyNote.php?deleted=success");
exit;
?>
