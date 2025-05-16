<?php
include 'db_connection.php'; // Ensure this connects to your DB correctly

if (!isset($_GET['id'])) {
    die("Reward ID is required.");
}

$id = $_GET['id'];

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM reward WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Reward not found.");
}

$reward = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Reward</title>
</head>
<body>
    <h1>Edit Reward</h1>
    <form action="update_reward.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($reward['id']); ?>">

        <label>Title:</label><br>
        <input type="text" name="reward_title" value="<?php echo htmlspecialchars($reward['reward_title']); ?>" required><br><br>

        <label>Description:</label><br>
        <textarea name="reward_description" required><?php echo htmlspecialchars($reward['reward_description']); ?></textarea><br><br>

        <label>Point Cost:</label><br>
        <input type="number" name="point_cost" value="<?php echo htmlspecialchars($reward['point_cost']); ?>" required><br><br>

        <label>Current Image:</label><br>
        <img src="data:image/jpeg;base64,<?php echo base64_encode($reward['pic']); ?>" width="150"><br><br>

        <label>New Image (optional):</label><br>
        <input type="file" name="pic"><br><br>

        <button type="submit">Update Reward</button>
    </form>
</body>
</html>
