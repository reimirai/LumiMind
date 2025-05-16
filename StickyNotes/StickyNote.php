<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../accountManagement/login.html');
    exit;
}
    
require_once('../sidebar/sidebar.html');    
$activePage = 'StickyNote';
include 'success_popup.php';
include 'db_connect.php';

if (isset($_GET['added']) && $_GET['added'] == 'success') {
    echo "<script>showPopup();</script>";
}
if (isset($_GET['deleted']) && $_GET['deleted'] == 'success') {
    echo "<script>showDeletePopup();</script>";
}
if (isset($_GET['statusupdate']) && $_GET['statusupdate'] == 'success') {
    echo "<script>window.location.href='StickyNote.php?filter=All';</script>";
    exit();
}

$fk_user = $_SESSION['user_id'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'All';
$filterSql = $filter == 'All' ? "" : "AND category = '$filter'";
$today = date('Y-m-d');
?>

<link rel="stylesheet" href="../css/StickyNote.css">

<script src="../js/sidebar.js"></script>
<script src="../js/ToogleList.js"></script>
<<h1 class="main-content"id="main-content"></h1>>
<script>
</script>

<main class="content" id="mainContent">
    <h1><span class="yellow-bar"></span>Sticky Note</h1>

    <!-- Filter Tags -->
    <div class="filter-bar">
        <?php
        $categories = ['All', 'Work', 'Personal', 'Wishlist', 'Birthday', 'Daily'];
        foreach ($categories as $cat) {
            $activeClass = ($filter == $cat) ? 'active' : '';
            echo "<a href='StickyNote.php?filter=$cat'><button class='$activeClass'>$cat</button></a>";
        }
        ?>
        <a href="add_note.php" class="add-note-btn" id="add-note-btn">add new note</a>
    </div>

    <!-- Reusable function to render notes -->
    <?php
    function renderNote($note, $filter, $isCompleted = false)
    {
        $statusBtn = $isCompleted
            ? "<img src='../icon/uncomplete-btn.png' alt='Uncomplete' style='width:25px;height:25px;cursor:pointer;'onclick=\"event.stopPropagation(); window.location.href='update_status.php?id={$note['id']}&status=Pending&filter={$filter}'\"></button>"
            : "<img src='../icon/complete-btn.png' alt='Complete' style='width:25px;height:25px;cursor:pointer;'onclick=\"event.stopPropagation(); window.location.href='update_status.php?id={$note['id']}&status=Completed&filter={$filter}'\">";

        $title = $isCompleted ? "{$note['title']}" : $note['title'];

        return "
        <div class='note-wrapper'>
            <div class='note-card'>
                $statusBtn
                <div class='note-title' style='cursor: pointer;' onclick=\"window.location.href='view_note.php?id={$note['id']}'\">$title</div>
                <div class='note-meta'>
                    <img src='../icon/date.png' alt='Date' class='note-meta-icon' style='width:20px;height:20px;'/><span>{$note['note_date']}</span>
                    <img src='../icon/time.png' alt='Time' class='note-meta-icon' style='width:20px;height:20px;'/><span>{$note['note_time']}</span>
                </div>
                <button class='menu-btn' onclick='toggleDropdown(event, this)'>⋯</button>
                <div class='dropdown-menu'>
                    <button title='Edit' onclick=\"window.location.href='edit_note.php?id={$note['id']}'\"> Edit</button>
                    <button title='Delete' onclick=\"if(confirm('Are you sure you want to delete this note?')) window.location.href='delete_note.php?id={$note['id']}'\">Delete</button>
                </div>
            </div>
        </div>";
    }
    ?>

    <!-- Sections: Previous, Future, Completed Today -->
    <?php
    $sections = [
        'Previous' => "SELECT * FROM notes WHERE fk_user = '$fk_user' AND note_date <= '$today' AND status = 'Pending' $filterSql ORDER BY note_date ASC, note_time ASC",
        'Future' => "SELECT * FROM notes n WHERE n.fk_user = '$fk_user' AND n.note_date > '$today' AND n.status = 'Pending' AND (n.repeat_task = 0 OR n.id = ( SELECT id FROM notes WHERE fk_user = n.fk_user AND title = n.title AND category = n.category AND repeat_task = 1 AND note_date > '$today' AND status = 'Pending' $filterSql ORDER BY note_date ASC, note_time ASC LIMIT 1) ) $filterSql ORDER BY n.note_date ASC, n.note_time ASC",
        'Completed Today' => "SELECT * FROM notes WHERE fk_user = '$fk_user' AND status = 'Completed' AND note_date = '$today' $filterSql ORDER BY note_time DESC"
    ];

    foreach ($sections as $section => $query) {
        $listId = strtolower(str_replace(' ', '', $section)) . "List";
        echo "<div class='notes-list'>
                <div class='list-header'>
                    <h3>$section<button onclick=\"toggleList('$listId', this)\" class='toggle-btn'>
                        <img src='../icon/Chevrondown.png' alt='Toggle'>
                    </button></h3>
                    
                </div>
                <div id='$listId' class='list-content'>";

        $result = $conn->query($query);
        if ($result->num_rows == 0) {
            echo "<p>No notes in this section.</p>";
        }
        while ($note = $result->fetch_assoc()) {
            echo renderNote($note, $filter, $section === 'Completed Today');
        }

        echo "</div></div>";
    }
    ?>

    <div style="margin-top: 30px;text-align:center;">
        <a href="completed_task.php" class="add-note-btn" style="margin-top:20px;">Check All Completed Tasks</a>
    </div>
</main>

<script>
    if (window.location.search.includes('deleted=success')) {
        showDeletePopup();
    }

    function toggleDropdown(event, button) {
        event.stopPropagation();
        const menu = button.nextElementSibling;

        document.querySelectorAll('.dropdown-menu').forEach(m => {
            if (m !== menu) m.style.display = 'none';
        });

        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    // Close dropdowns on outside click
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu').forEach(m => m.style.display = 'none');
    })


    // Request notification permission
    function requestNotificationPermission() {
        if (!("Notification" in window)) {
            alert("This browser does not support desktop notifications");
            return;
        }

        if (Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission();
        }
    }

    // Show notification
    function showNotification(title, body, icon) {
        if (Notification.permission === "granted") {
            new Notification(title, {
                body: body,
                icon: icon
            });
        }
    }

    // Check for reminders
    function checkReminders() {
        fetch('check_reminders.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.notifications && data.notifications.length > 0) {
                        data.notifications.forEach(notification => {
                            showNotification(notification.title, notification.body, notification.icon);
                        });
                    }
                    // Log debug info
                    console.log('Reminder Check Debug:', data.debug);
                } else {
                    console.error('Error checking reminders:', data.error);
                }
            })
            .catch(error => {
                console.error('Error checking reminders:', error);
            });
    }

    setInterval(() => {
    fetch('check_reminders.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notifications.length > 0) {
                data.notifications.forEach(n => {
                    showNotification(n.title, n.body, n.icon);
                });
            }
        })
        .catch(error => console.error("Reminder check failed:", error));
}, 60000); // check every 60 seconds


    // Request permission when page loads
    document.addEventListener('DOMContentLoaded', function() {
        if (!("Notification" in window)) {
            console.error("This browser does not support desktop notifications");
            return;
        }

        if (Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission().then(function(permission) {
                console.log('Notification permission:', permission);
            });
        }

        // Check reminders every minute
        setInterval(checkReminders, 60000);
        // Initial check
        checkReminders();
    });

</script>

<?php $conn->close(); ?>