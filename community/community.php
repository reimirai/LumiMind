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
  <div class="flex">
    <?php include '../sidebar/sidebar.html'; ?>
    <?php include 'sidebar1.php'; ?>
    <main class="main-layout">
      <?php
      $page = $_GET['page'] ?? 'forum';
      include "$page.php";
      ?>
      <?php include 'links.php'; ?>
    </main>
  </div>
</body>

</html>