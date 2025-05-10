<link rel="stylesheet" href="/sidebar/longsidebar.css" />
<aside class="main-sidebar" id="longSidebar" style="display: none;">
    <h2 class="main-logo">
        <img src="../icon/Logo.png" id="Logo" alt="Logo">
    </h2>
    <ul>
        <li class="menuitem" id="toggleLongSidebar">
            ☰ Menu
        </li>
        <li class="menuitem" id="modulebar">
            <a href="Home.php" class="menu-item">
                <img src="../icon/home.png" alt="Mood Tracker">
                Mood Tracker
            </a>
        </li>
        <li class="menuitem" id="modulebar">
            <a href="StickyNote.php" class="menu-item">
                <img src="../icon/note.jpg" alt="Sticky Notes Planner">
                Sticky Notes Planner
            </a>
        </li>

        <li class="menuitem" id="modulebar">
            <a href="add-task.php" class="menu-item">
                <img src="../icon/task.png" alt="Sticky Notes Planner">
                Sticky Notes Planner
            </a>
        </li>

        <li class="menuitem" id="modulebar">
            <a href="community.php" class="menu-item">
                <img src="../icon/community.png" alt="Community & Peer Support">
                Community & Peer Support
            </a>
        </li>
    </ul>

    <li id="logout">
        <a href="../accountManagement/logout.php" class="menu-item">
            Logout
        </a>
    </li>
</aside>

<style>
    /* Basic styling for the main content area */
    .content {
        margin-left: 55px;
        /* Adjust based on main-sidebar width */
        padding: 20px;
    }

    .sidebar:not(.hidden)+.content {
        margin-left: 200px + 240px;
        /* Adjust based on both sidebar widths */
    }
</style>

