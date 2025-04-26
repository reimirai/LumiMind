
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Lumimind</title>
<link rel="icon" href="../icon/Logo.png" type="image/x-icon">
</head>
<body>
  <div class="container">
    <?php
    include 'longsidebar.html';
      include 'shortsidebar.html';
    include 'task_subbar.html';
?>
        <link rel="stylesheet" href="styles.css" />
    <main class="main-content" id="mainContent">
      <header class="header">
        <h2>Create Task</h2>
        <span class="points">Points Obtained : <strong><?php echo $userPoints; ?>pts</strong></span>
      </header>

      <?php
      foreach ($tasks as $task) {
          echo '<section class="task">';
          echo '<h3>' . htmlspecialchars($task['title']) . ' <span class="points-tag">' . $task['points'] . ' Points</span></h3>';
          
          // Break the description into individual items
          $descriptionItems = explode("\n", $task['description']);
          echo '<ul>';
          foreach ($descriptionItems as $item) {
              echo '<li>' . htmlspecialchars($item) . '</li>';
          }
          echo '</ul>';
          echo '<button class="accept-btn">Accept</button>';
          echo '</section>';
      }
      ?>

    </main>
  </div>

  <script src="script.js"></script>
</body>
</html>