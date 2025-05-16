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
        FROM user_achievements ua
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
    <link rel="stylesheet" href="account.css">
    <style>
        /* Existing styles from before */
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

        .user-info .avatar-container {
            position: relative; /* For positioning the modal */
            display: inline-block; /* Or block, depending on your layout */
        }

        .user-info img.avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 2px solid #FFD000;
            margin-right: 16px;
            cursor: pointer; /* Make it look clickable */
        }

        .user-info img.avatar:hover {
            opacity: 0.8; /* Slightly fade on hover */
            border: 2px solid #007bff; /* Add a border on hover */
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
            position: relative; /* For positioning the edit button */
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

        #edit {
            background-color: #007bff; /* Primary blue color */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease; /* Smooth hover effect */
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
        }

        #edit:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        #edit:focus {
            outline: none; /* Remove default focus outline */
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.5); /* Add a custom focus ring */
        }

        @media (max-width: 600px) {
            #edit {
                padding: 8px 12px;
                font-size: 14px;
            }
        }

        #edit {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #edit:active {
            background-color: #004085; /* Even darker blue when active (clicked) */
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
        }

  
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }

        /* Modal Content/Box */
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be 60% or whatever you want */
            max-width: 500px;
        }

        /* The Close Button */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
  .edit-modal-content {
            background-color: #fefefe;
            margin: 10% auto; /* Adjust top margin as needed */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Adjust width as needed */
            max-width: 500px;
            border-radius: 8px;
            position: relative;
        }

        /* Edit Profile Close Button */
        .edit-close {
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .edit-close:hover,
        .edit-close:focus {
            color: black;
            text-decoration: none;
        }

        .edit-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .edit-form input[type="text"],
        .edit-form input[type="email"],
        .edit-form input[type="date"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .edit-form button[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .edit-form button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .edit-form .error-message {
            color: red;
            margin-top: 5px;
        }

        .edit-form .success-message {
            color: green;
            margin-top: 5px;
        }

        #upload-message {
            margin-top: 10px;
            font-size: 14px;
        }
         #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
            z-index: 1; /* Behind the form, but above other content */
        }
    </style>
</head>
<body>
     <div id="overlay"></div> 
    <main class="content-wrapper">
        <div class="profile-card">
            <div class="profile-header">
                <h2>User Profile</h2>
                <div class="points"><?php echo $userPoints; ?> pts</div>
            </div>

            <div class="user-info">
                <div class="avatar-container">
                    <?php
                    if($profileimage !=null){
                        $base64Image1 = base64_encode($profileimage);
                        echo '<img src="data:image/jpeg;base64,' . $base64Image1 . '"  alt="Avatar" class="avatar clickable-avatar" />';
                    }
                    else{
                        echo  '<img src="https://cdn.builder.io/api/v1/image/assets/TEMP/cde03525093ccfc2c4647574ce03f227442b0cfc?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62" alt="Avatar" class="avatar clickable-avatar" />';
                    }
                    ?>
                </div>
                <div class="details">
                    <div class="username"><?php echo $userName; ?></div>
                </div>
            </div>

              <div class="section-title">
                 Personal Info
                 <button id='edit' name="edit" ">Edit</button>
             </div><div class="personal-info">
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
                            <?php
                            $base64Image = base64_encode($row['icon_blob']);
                            echo '<img src="data:image/jpeg;base64,' . $base64Image . '" alt="Avatar" class="avatar" />';
                            ?>
                            <div class="title"><?= htmlspecialchars($row['title']) ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No achievements yet.</p>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <div id="editProfileImageModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit Profile Image</h3>
            <form id="editProfileImageForm" enctype="multipart/form-data">
                <input type="file" name="profileImage" accept="image/*">
                <button type="submit">Upload</button>
            </form>
            <div id="upload-message"></div>
        </div>
    </div>
    
    <div id="editModal" class="modal">
    <div class="edit-modal-content">
        <span class="edit-close">&times;</span>
        <form id="editProfileForm" class="edit-form">
            <div class="form-group">
                <label for="username">Name:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($userName); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($Email); ?>" required>
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($DOB); ?>" required>
            </div>

            <button type="submit">Save Changes</button>
            <div id="edit-message" class="success-message"></div>
            <div id="edit-error" class="error-message"></div>
        </form>
    </div>
</div>


        <script>
            document.addEventListener('DOMContentLoaded', function() {
            const avatarContainer = document.querySelector('.avatar-container');
            const editProfileImageModal = document.getElementById('editProfileImageModal');
            const closeBtn = document.querySelector('#editProfileImageModal .close');
            const popupButton = document.getElementById('popupButton');
            const popupModal = document.getElementById('myPopupModal');
            const popupCloseBtn = document.querySelector('#myPopupModal .popup-close');

            if (avatarContainer && editProfileImageModal && closeBtn) {
                // Open the edit profile image modal
                avatarContainer.addEventListener('click', function() {
                    editProfileImageModal.style.display = 'block';
                });

                // Close the edit profile image modal
                closeBtn.addEventListener('click', function() {
                    editProfileImageModal.style.display = 'none';
                });

                // Close the edit profile image modal if clicked outside
                window.addEventListener('click', function(event) {
                    if (event.target == editProfileImageModal) {
                        editProfileImageModal.style.display = 'none';
                    }
                });

                // Form submission handling for edit profile image
                const form = document.getElementById('editProfileImageForm');
                const uploadMessage = document.getElementById('upload-message');

                if (form && uploadMessage) {
                    form.addEventListener('submit', function(event) {
                        event.preventDefault(); // Prevent default form submission

                        const formData = new FormData(form);

                        fetch('upload_profile_image.php', { // Replace with your PHP endpoint
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            uploadMessage.textContent = data;
                            if (data.includes('success')) {
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500); // Reload after a short delay
                            }
                        })
                        .catch(error => {
                            uploadMessage.textContent = 'An error occurred during upload.';
                        });
                    });
                }
            }
        });
    </script>
   <script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editModal');
    const editBtn = document.getElementById('edit');
    const closeBtn = document.querySelector('.edit-close');
    const editFormElement = document.getElementById('editProfileForm'); //  Get the form
    const editMessage = document.getElementById('edit-message'); //  Get the success message div
    const editError = document.getElementById('edit-error');   //  Get the error message div

    // Show modal when "Edit" is clicked
    if (editBtn) {
        editBtn.addEventListener('click', function () {
            modal.style.display = 'block';
        });
    }

    // Close modal when "×" is clicked
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });
    }

    // Optional: Close when clicking outside the modal content
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    //  ---  AJAX Form Submission  ---
    if (editFormElement && editMessage && editError) {
        editFormElement.addEventListener('submit', function (event) {
            event.preventDefault();  //  Prevent the default form submission

            const formData = new FormData(editFormElement);

            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    editMessage.textContent = data.message;
                    editError.textContent = '';
                    //  Optionally update displayed values on the page
                    //  (You'll need to adjust these selectors to match your page structure)
                    const usernameDisplay = document.querySelector('.user-name-display');
                    const emailDisplay = document.querySelector('.user-email-display');
                    const dobDisplay = document.querySelector('.user-dob-display');

                    if (usernameDisplay) usernameDisplay.textContent = formData.get('username');
                    if (emailDisplay) emailDisplay.textContent = formData.get('email');
                    if (dobDisplay) dobDisplay.textContent = formData.get('dob');

                    setTimeout(() => { modal.style.display = 'none'; }, 1500);  //  Close modal after a delay
                } else if (data.status === 'error') {
                    editError.textContent = data.message;
                    editMessage.textContent = '';
                } else {
                    editMessage.textContent = data.message;  //  For 'info' or other statuses
                    editError.textContent = '';
                }
            })
            .catch(error => {
                editError.textContent = 'An error occurred while updating the profile.';
                editMessage.textContent = '';
                console.error('Fetch error:', error);
            });
        });
    }
});
</script>
</body>
</html>