<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
include 'db_connect.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');

function sendNotification($title, $body) {
    return [
        'title' => $title,
        'body' => $body,
        'icon' => '../icon/logo.png'
    ];
}

try {
    // Get current time
    $current_time = new DateTime();
    $current_time_str = $current_time->format('Y-m-d H:i:s');

    // Get all reminders that are due
 $query = "SELECT n.id, n.title, n.note_date, n.note_time, r.reminder_time 
          FROM notes n 
          JOIN reminders r ON n.id = r.note_id 
          WHERE n.status = 'Pending' 
          AND n.fk_user = " . $_SESSION['user_id'];

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $notifications = [];
    $debug_info = [
        'current_time' => $current_time_str,
        'total_reminders' => $result->num_rows,
        'checked_reminders' => []
    ];

    while ($row = $result->fetch_assoc()) {
        $note_time = new DateTime($row['note_date'] . ' ' . $row['note_time']);
        $reminder_time = $row['reminder_time'];
        
        if ($reminder_time === 'same') {
            $reminder_datetime = $note_time;
        } else {
            $reminder_datetime = clone $note_time;
            $reminder_datetime->modify("-{$reminder_time} minutes");
        }
    
        $debug_info['checked_reminders'][] = [
            'title' => $row['title'],
            'due_time' => $note_time->format('Y-m-d H:i:s'),
            'reminder_time' => $reminder_time,
            'reminder_datetime' => $reminder_datetime->format('Y-m-d H:i:s'),
        ];
    
        if ($current_time->format('Y-m-d H:i') === $reminder_datetime->format('Y-m-d H:i')) {
            $notifications[] = sendNotification(
                "Task Reminder: " . $row['title'],
                "Due: " . $note_time->format('Y-m-d H:i:s')
            );
        }
        
    }

    // Return a JSON response with debug info
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'debug' => $debug_info
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?> 