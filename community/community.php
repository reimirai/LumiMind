<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum Discussion Board</title>
    <link rel="stylesheet" href="community.css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body style="background-color: #f5f5f5;">
    <?php include '../sidebar/sidebar.html'; ?>
    <style>
        .longSidebar {
            display: none;
        }

        .shortSidebar {
            display: none;
        }
    </style>
    <div class="main" id="main">
        <div class="flex">

            <?php include 'sidebar1.php'; ?>
            <main class="main-layout">
                <?php
                $page = $_GET['page'] ?? 'forum';
                include "$page.php";
                ?>
                <?php include 'links.php'; ?>
            </main>
        </div>
    </div>
</body>

</html>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleShortSidebar = document.getElementById('toggleShortSidebar');
        const toggleLongSidebar = document.getElementById('toggleLongSidebar');
        const shortSidebar = document.getElementById('shortSidebar');
        const longSidebar = document.getElementById('longSidebar');
        const content = document.querySelector('.main');

        function updateContentMargin() {
            const sidebarWidth = shortSidebar.offsetWidth;
            content.style.marginLeft = `${sidebarWidth}px`;
        }
        longSidebar.style.display = 'none';
        shortSidebar.style.display = 'flex';
        updateContentMargin();

        toggleShortSidebar?.addEventListener('click', function () {
            longSidebar.style.display = 'none';
            shortSidebar.style.display = 'flex';
            updateContentMargin();
        });

        toggleLongSidebar?.addEventListener('click', function () {
            shortSidebar.style.display = 'none';
            longSidebar.style.display = 'flex';
            content.style.marginLeft = '200px';
        });

        document.querySelectorAll('.menu-heading').forEach(function (menuHeading) {
            menuHeading.addEventListener('click', function () {
                const parent = this.closest('.nav-item');
                const submenu = parent.querySelector('.submenu');
                if (submenu) {
                    const isVisible = submenu.style.display === 'block';
                    submenu.style.display = isVisible ? 'none' : 'block';
                }
            });
        });
    });
</script>