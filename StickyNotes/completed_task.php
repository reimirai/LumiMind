<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../accountManagement/login.html');
    exit;
}
include 'db_connect.php';
require_once('../sidebar/sidebar.html');    

$fk_user = $_SESSION['user_id'];
$sql = "SELECT * FROM notes WHERE fk_user = '$fk_user' AND status = 'Completed' ORDER BY completed_time DESC";
$result = $conn->query($sql);
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/StickyNote.css">

<main class="content">
    <h1><span class="yellow-bar"></span>All Completed Tasks</h1>

    <div class="notes-list">
        <?php
        if ($result->num_rows == 0) {
            echo "<p>No completed tasks yet.</p>";
        } else {
            while ($note = $result->fetch_assoc()) {
                echo "
                <div class='note-wrapper'>
                    <div class='note-card'>
                        <img src='../icon/uncomplete-btn.png' alt='Uncomplete' style='width:25px;height:25px;cursor:pointer;' onclick=\"event.stopPropagation(); window.location.href='update_status.php?id={$note['id']}&status=Pending'\">
                        <div class='note-title'>{$note['title']}</div>
                        <div class='note-meta'>
                            <img src='../icon/date.png' alt='Date' class='note-meta-icon' style='width:20px;height:20px;'/><span>{$note['note_date']}</span>
                            <img src='../icon/time.png' alt='Time' class='note-meta-icon' style='width:20px;height:20px;'/><span>{$note['note_time']}</span>
                            <span>Completed: " . (!empty($note['completed_time']) ? date('Y-m-d H:i:s', strtotime($note['completed_time'])) : 'Not recorded') . "</span>
                        </div>
                        <button class='menu-btn' onclick='toggleDropdown(event, this)'>⋯</button>
                        <div class='dropdown-menu'>
                            <button title='Edit' onclick=\"window.location.href='edit_note.php?id={$note['id']}'\"> Edit</button>
                            <button title='Delete' onclick=\"if(confirm('Are you sure you want to delete this note?')) window.location.href='delete_note.php?id={$note['id']}'\">Delete</button>
                        </div>
                    </div>
                </div>";
            }
        }
        ?>
    </div>

    <a href="StickyNote.php" class="add-note-btn" style="margin-top: 30px;">⬅ Back to Notes</a>
</main>

<!-- JS for dropdown menu -->
<script>
    function toggleDropdown(event, button) {
        event.stopPropagation();
        const menu = button.nextElementSibling;

        // Close other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(m => {
            if (m !== menu) m.style.display = 'none';
        });

        // Toggle current
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
    });
</script>

<?php $conn->close(); ?>
