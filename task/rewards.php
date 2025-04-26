<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Lumimind</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="container">
    <?php
    include '../sidebar/sidebar.html';
?>
    <main class="main-content" id="mainContent">
      <header class="header">
        <h2>Rewards</h2>
         <?php
    include 'fetchdata.php';
        
$sql1 = "SELECT id, points FROM User";
$result1 = $conn->query($sql1);

if ($result1->num_rows > 0) {
    $row = $result1->fetch_assoc();
    $userId = $row['id'];
    $userPoints = $row['points'];
} else {
    echo "No records found.";
}

if (!$result) {
    die("Query failed: " . $conn->error);
}
$conn->close();
        ?>
        <span class="points">Points Obtained : <strong><?php echo $userPoints; ?>pts</strong></span>
      </header>
       
        <div class="cardContainer" id="cardContainer">
        <?php
        if ($result) {
    while ($row = $result->fetch_assoc()) {
        $imgData = base64_encode($row['pic']);
        echo '<div class="card">';
        echo '<img src="data:image/jpeg;base64,' . $imgData . '" alt="Card Image"/>';
        echo '<h3>' . htmlspecialchars($row['reward_title']) . '</h3>';
        echo '<p>' . htmlspecialchars($row['reward_description']) . '</p>';
        echo '<p>' . htmlspecialchars($row['point_cost']) . '</p>';
        echo '<button>Redeem</button>';
        echo '</div>';
    }
} else {
    echo 'Error retrieving rewards: ' . $mysqli->error;
}
?>
 </div>
 
    </main>
  </div>

</body>
</html>

<style>#cardContainer {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: flex-start;
    padding: 20px;
}

.card {
    border: 1px solid #ddd;
    border-radius: 10px;
    width: 250px;
    padding: 15px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s;
    background-color: white;
}

.card:hover {
    transform: translateY(-5px);
}

.card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
}

.card h3 {
    font-size: 1rem;
    margin: 10px 0 5px;
}

.card p {
    margin: 5px 0;
    font-size: 0.9rem;
}

.card p:last-of-type {
    font-weight: bold;
}</style>