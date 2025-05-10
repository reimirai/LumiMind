<link rel="stylesheet" href="/sidebar/sidebar.css" />
<aside class="main-sidebar1" id="shortSidebar">
    <h2 class="main-logo1">
        
        <img src="../icon/Logo.png" id="Logo" alt="Logo">
    </h2>
    <ul>
        <li class="menuitem" id="toggleShortSidebar">
            ☰ 
        </li>
        <li>
            <a href="Home.php" class="menu-item">
                <img src="../icon/home.png" alt="Home">
            </a>
        </li>
        <li>
            <a href="StickyNote.php" class="menu-item">
                <img src="../icon/note.jpg" alt="Notes">
            </a>
        </li>
        <li>
          <a href="Task.php" class="menu-item">
              <img src="../icon/task.png" alt="Notes">
          </a>
      </li>
        <li>
            <a href="community.php" class="menu-item">
                <img src="../icon/community.png" alt="Community">
            </a>
        </li>
        <li id="logout">
            <a href="accountManagement/logout.php">Logout</a>
        </li>
    </ul>
</aside>






<script>
  document.addEventListener('DOMContentLoaded', function () {
    const toggleShortSidebar = document.getElementById('toggleShortSidebar');  // For short sidebar
    const toggleLongSidebar = document.getElementById('toggleLongSidebar');  // For long sidebar
    
    const shortSidebar = document.getElementById('shortSidebar');
    const longSidebar = document.getElementById('longSidebar');
    const content = document.querySelector('.content');

    if (toggleShortSidebar && toggleLongSidebar) {
        toggleShortSidebar.addEventListener('click', function () {
            shortSidebar.style.display = 'none';
            longSidebar.style.display = 'block';
            content.style.marginLeft = '250px';  
        });

        toggleLongSidebar.addEventListener('click', function () {
            longSidebar.style.display = 'none';
            shortSidebar.style.display = 'block';
            content.style.marginLeft = '60px'; 
        });
    }
});
</script>
