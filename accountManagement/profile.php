<!DOCTYPE html>
<?php
session_start();
require_once('../sidebar/sidebar.html'); 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "LumiMind";

// Establish connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql1 = "SELECT * FROM Users WHERE id = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("s", $_SESSION['user_id']);
$stmt1->execute();
$result1 = $stmt1->get_result();

if ($result1->num_rows > 0) {
    $row = $result1->fetch_assoc();
    $userId = $row['ID'];
    $userName = $row['Name'];
    $Email = $row['Email']; 
    $userPoints = $row['Points'];
    $profileimage = $row['profile_image']; 
    $DOB = $row['BirthDate'];
} else {
    echo "No records found.";
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT a.title, a.icon_blob
        FROM user_achievement ua
        JOIN achievements a ON ua.achievement_id = a.id
        WHERE ua.user_id = ?";
$stmt= $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Profile Card</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: #f2f2f5;
      margin: 0;
      display: flex;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .content-wrapper {
      margin-left: 200px; /* Adjust if you have a sidebar */
      padding: 40px;
      min-height: 100vh;
      width: calc(100% - 200px);
    }

    .profile-card {
      width: 100%;
      max-width: none;
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 40px;
      box-sizing: border-box;
    }

    .profile-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .profile-header h2 {
      font-size: 20px;
      color: #333;
    }

    .profile-header .points {
      font-size: 14px;
      color: #777;
      display: flex;
      align-items: center;
    }

    .profile-header .points::before {
      content: '\1F4B0';
      margin-right: 4px;
    }

    .user-info {
      display: flex;
      align-items: center;
      margin-bottom: 16px;
    }

    .user-info img.avatar {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      border: 2px solid #FFD000;
      margin-right: 16px;
    }

    .user-info .details {
      display: flex;
      flex-direction: column;
    }

    .user-info .details .username {
      font-size: 18px;
      font-weight: 600;
      color: #222;
    }

    .user-info .details .handle {
      font-size: 14px;
      color: #888;
    }

    .section-title {
      font-size: 16px;
      font-weight: 600;
      color: #333;
      border-left: 4px solid #FFD000;
      padding-left: 8px;
      margin-bottom: 12px;
      margin-top: 16px;
    }

    .personal-info {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 8px 16px;
    }

    .personal-info .label {
      font-size: 13px;
      color: #555;
    }

    .personal-info .value {
      font-size: 14px;
      color: #222;
    }

    .achievements {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
      margin-top: 8px;
    }

    .achievement-card {
      background-color: #f9f9f9;
      border: 1px solid #eee;
      border-radius: 8px;
      padding: 12px;
      text-align: center;
    }

    .achievement-card img {
      width: 48px;
      height: 48px;
      margin-bottom: 8px;
    }

    .achievement-card .title {
      font-size: 12px;
      color: #555;
    }
  </style>
</head>
<body>
  <main class="content-wrapper">
    <div class="profile-card">
      <div class="profile-header">
        <h2>User Profile</h2>
        <div class="points"><?php echo $userPoints; ?> pts</div>
      </div>

      <div class="user-info">
          <?php 
                         if($profileimage !=null){
                         echo '<img src="data:image/jpeg;base64,' . $profileimage . '"  alt="Avatar" class="avatar" />';
                         }
                         else{
                          echo    '<img src="https://cdn.builder.io/api/v1/image/assets/TEMP/cde03525093ccfc2c4647574ce03f227442b0cfc?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"/>';
                         }
                         ?>
        <div class="details">
          <div class="username"><?php echo $userName; ?></div>
        </div>
      </div>

      <div class="section-title">Personal Info</div>
      <div class="personal-info">
        <div>
          <div class="label">Email</div>
          <div class="value"><?php echo $Email; ?></div>
        </div>
        <div>
          <div class="label">Date of Birth</div>
          <div class="value"><?php echo $DOB; ?></div>
        </div>
      </div>

      <div class="section-title">Achievements</div>
<div class="achievements">
  <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="achievement-card">
        <?php  echo '<img src="data:image/jpeg;base64,' . $row['icon_blob'] . '"  alt="Avatar" class="avatar" />';?>
        <div class="title"><?= htmlspecialchars($row['title']) ?></div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No achievements yet.</p>
  <?php endif; ?>
</div>

    </div>
  </main>
</body>
</html>
