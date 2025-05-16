<?php

include('db_connection.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reward_title = $_POST['reward_title'];
    $reward_description = $_POST['reward_description'];
    $point_cost = $_POST['point_cost'];
    $pic = addslashes(file_get_contents($_FILES['pic']['tmp_name']));
 if ($_FILES['pic']['size'] > 0) {
        $fileTmpPath = $_FILES['pic']['tmp_name'];
        $fileType = $_FILES['pic']['type'];

        // Get the image dimensions
        list($width, $height) = getimagesize($fileTmpPath);

        // Set maximum dimensions
        $maxWidth = 800;
        $maxHeight = 800;

        // Resize if the image is larger than the max dimensions
        if ($width > $maxWidth || $height > $maxHeight) {
    $aspectRatio = $width / $height;
    if ($width > $height) {
        $newWidth = $maxWidth;
        $newHeight = $maxWidth / $aspectRatio;
    } else {
        $newHeight = $maxHeight;
        $newWidth = $maxHeight * $aspectRatio;
    }

    // Create a new image from the uploaded file
    $src = imagecreatefromstring(file_get_contents($fileTmpPath)); // This function requires GD
    $dst = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save the resized image to a temporary location
    ob_start();
    imagejpeg($dst); // Save as JPEG
    $imageData = ob_get_contents(); // Capture the binary data
    ob_end_clean();
} else {
    // No resizing needed, just read the image data
    $imageData = addslashes(file_get_contents($fileTmpPath));
}
 }
    // Generate the reward ID
    $result = mysqli_query($conn, "SELECT id FROM reward ORDER BY id DESC LIMIT 1");
    $last_id = mysqli_fetch_assoc($result);

    if ($last_id) {
        // Extract the numeric part of the last ID and increment it
        $last_number = (int) substr($last_id['id'], 1);  // Remove the 'R' and get the number part
        $new_id = 'R' . str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);  // Add 'R' and pad with zeros
    } else {
        // No records, so start with R0001
        $new_id = 'R0001';
    }

    // Insert the new reward with the generated ID
    $sql = "INSERT INTO reward (id, reward_title, reward_description, pic, point_cost) 
            VALUES ('$new_id', '$reward_title', '$reward_description', '$pic', '$point_cost')";
    
    if (mysqli_query($conn, $sql)) {
        echo "New reward added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

$sql = "SELECT * FROM reward";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Rewards</title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<div class="admin-container">
        <?php require_once 'admin_sidebar.html'; 
        ?>
        <div class="main-content">
            <h1>Add New Reward</h1>
            <form action="rewardmanage.php" method="POST" enctype="multipart/form-data">
                <label for="reward_title">Reward Title</label>
                <input type="text" id="reward_title" name="reward_title" required>

                <label for="reward_description">Reward Description</label>
                <textarea id="reward_description" name="reward_description" required></textarea>

                <label for="point_cost">Point Cost</label>
                <input type="number" id="point_cost" name="point_cost" required>

                <label for="pic">Reward Image</label>
                <input type="file" id="pic" name="pic" accept="image/*" required>

                <button type="submit">Add Reward</button>
            </form>

            <h2>Existing Rewards</h2>
            <div class="reward-list"> 
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="reward-item">
            <h3><?php echo htmlspecialchars($row['reward_title']); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($row['reward_description'])); ?></p>
            <p>Point Cost: <?php echo $row['point_cost']; ?></p>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($row['pic']); ?>" alt="Reward Image">

            <form action="edit_reward.php" method="get" style="display:inline;">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <button type="submit">Edit</button>
            </form>

            <form action="delete_reward.php" method="post" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <button type="submit">Delete</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>
        </div>
    </div>
</body>
</html>
