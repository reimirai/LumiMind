<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../accountManagement/login.html');
    exit;
}

include('db_connect.php');
include 'success_popup.php';

function formatReminderTime($time) {
    switch($time) {
        case 'same':
            return 'Same as due time';
        case '5':
            return '5 minutes before';
        case '10':
            return '10 minutes before';
        case '15':
            return '15 minutes before';
        case '30':
            return '30 minutes before';
        case '1440':
            return '1 day before';
        case '2880':
            return '2 days before';
        default:
            return $time;
    }
}

$note_id = $_GET['id'] ?? null;
if (!$note_id) {
    echo "Note ID missing.";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM notes WHERE id = ?");
$stmt->bind_param("i", $note_id);
$stmt->execute();
$result = $stmt->get_result();
$note = $result->fetch_assoc();

if (!$note) {
    echo "Note not found.";
    exit;
}

$subtasks = [];
$subtaskStmt = $conn->prepare("SELECT * FROM subtasks WHERE note_id = ?");
$subtaskStmt->bind_param("i", $note_id);
$subtaskStmt->execute();
$subtaskResult = $subtaskStmt->get_result();
while ($row = $subtaskResult->fetch_assoc()) {
    $subtasks[] = $row;
}

$repeatDetails = null;
if ($note['repeat_task']) {
    $repeatStmt = $conn->prepare("SELECT * FROM repeat_task WHERE note_id = ?");
    $repeatStmt->bind_param("i", $note_id);
    $repeatStmt->execute();
    $repeatResult = $repeatStmt->get_result();
    $repeatDetails = $repeatResult->fetch_assoc();
}

// Fetch reminder details if exists
$reminderDetails = null;
if ($note['reminder']) {
    $reminderStmt = $conn->prepare("SELECT * FROM reminders WHERE note_id = ?");
    $reminderStmt->bind_param("i", $note_id);
    $reminderStmt->execute();
    $reminderResult = $reminderStmt->get_result();
    $reminderDetails = $reminderResult->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($note['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/view_note.css">
</head>
<body>
    <div class="note-container">
        <div class="top-bar">
            <h1><span class="yellow-bar"></span>View Note</h1>
            <a href="StickyNote.php" class="back-btn">Back</a>
        </div>
        
        <div class="note-content">
            <div class="note-section">
                <h3><?= htmlspecialchars($note['title']) ?></h3>
                <div class="meta-info">
                    <div class="meta-item">
                        <img src="../icon/date.png" alt="Date">
                        <span><?= htmlspecialchars($note['note_date']) ?></span>
                    </div>
                    <div class="meta-item">
                        <img src="../icon/time.png" alt="Time">
                        <span><?= htmlspecialchars($note['note_time']) ?></span>
                    </div>
                </div>
            </div>

            <div class="note-section">
                <h3>Details</h3>
                <p><strong>Category:</strong> <?= htmlspecialchars($note['category']) ?></p>
                <p><strong>Remarks:</strong> <?= nl2br(htmlspecialchars($note['remarks'])) ?></p>
            </div>

            <?php if (!empty($subtasks)): ?>
            <div class="note-section">
                <h3>Sub-notes</h3>
                <ul class="subtasks-list">
                    <?php foreach ($subtasks as $subtask): ?>
                        <li class="<?= $subtask['is_completed'] ? 'completed' : '' ?>">
                            <?= htmlspecialchars($subtask['subtask_text']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if ($note['repeat_task'] && $repeatDetails): ?>
            <div class="note-section">
                <h3>Repeat Settings</h3>
                <p><strong>Repeat Type:</strong> <?= ucfirst(htmlspecialchars($repeatDetails['repeat_type'])) ?></p>
                <?php if ($repeatDetails['repeat_type'] === 'daily'): ?>
                    <p><strong>Repeat Every:</strong> <?= htmlspecialchars($repeatDetails['daily_interval']) ?> day(s)</p>
                <?php elseif ($repeatDetails['repeat_type'] === 'weekly'): ?>
                    <p><strong>Repeat Every:</strong> <?= htmlspecialchars($repeatDetails['weekly_interval']) ?> week(s)</p>
                    <?php if (!empty($repeatDetails['weekly_days'])): ?>
                        <p><strong>Repeat On:</strong> <?= htmlspecialchars($repeatDetails['weekly_days']) ?></p>
                    <?php endif; ?>
                <?php elseif ($repeatDetails['repeat_type'] === 'monthly'): ?>
                    <p><strong>Repeat Every:</strong> <?= htmlspecialchars($repeatDetails['monthly_interval']) ?> month(s)</p>
                <?php endif; ?>
                <?php if ($repeatDetails['repeat_end_type'] === 'on'): ?>
                    <p><strong>Ends On:</strong> <?= htmlspecialchars($repeatDetails['repeat_end_date']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($note['reminder'] && $reminderDetails): ?>
            <div class="note-section">
                <h3>Reminder</h3>
                <p><strong>Reminder Time:</strong> <?= formatReminderTime(htmlspecialchars($reminderDetails['reminder_time'])) ?></p>
            </div>
            <?php endif; ?>

            <div class="button-group">
                <a href="edit_note.php?id=<?= $note['id'] ?>" class="btn yellow">Edit</a>
                <button onclick="showDeleteDialog()" class="btn red">Delete</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <div id="deleteDialog" class="delete-dialog">
        <div class="dialog-content">
            <h3>Delete Note</h3>
            <p>Are you sure you want to delete this note?</p>
            <div class="dialog-buttons">
                <button class="dialog-btn cancel" onclick="hideDeleteDialog()">Cancel</button>
                <button class="dialog-btn delete" onclick="deleteNote()">Delete</button>
            </div>
        </div>
    </div>

    <script>
        function showDeleteDialog() {
            document.getElementById('deleteDialog').classList.add('active');
        }

        function hideDeleteDialog() {
            document.getElementById('deleteDialog').classList.remove('active');
        }

        function deleteNote() {
            window.location.href = 'delete_note.php?id=<?= $note['id'] ?>';
        }

        // Close dialog when clicking outside
        document.getElementById('deleteDialog').addEventListener('click', function(e) {
            if (e.target === this) {
                hideDeleteDialog();
            }
        });

        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 'success'): ?>
            showDeletePopup();
        <?php endif; ?>
    </script>
</body>
</html>
