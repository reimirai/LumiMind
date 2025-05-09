<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
require_once('../sidebar/sidebar.html'); 
// Database connection
$conn = new mysqli("localhost", "root", "", "LumiMind");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$currentDate = date("Y-m-d");
$todaysMood = null;
$motivationMessage = "Select your mood to get a motivation!"; // Default message

// Check if a mood has been recorded for today to display motivation
$checkStmt = $conn->prepare("SELECT mood FROM mood_entries WHERE entry_date = ?");
$checkStmt->bind_param("s", $currentDate);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    $row = $checkResult->fetch_assoc();
    $todaysMood = $row['mood'];

    // Define motivational messages based on mood
    $motivations = [
        "happy-excited" => "Your enthusiasm is contagious! Keep shining!",
        "angry" => "It's okay to feel angry. Take a deep breath and find a constructive outlet.",
        "sad" => "It's alright to feel sad. Remember that tough times don't last, but tough people do.",
        "surprised" => "Embrace the unexpected! New possibilities might be unfolding.",
        "happy" => "Enjoy this wonderful feeling! Share your happiness with others.",
        "confused" => "Feeling lost is part of the journey. Seek clarity and don't be afraid to ask for help.",
        "worried" => "Acknowledge your worries, but don't let them control you. Focus on what you can influence.",
        "neutral" => "A calm and balanced state. Use this energy wisely for your next endeavor.",
    ];

    if (isset($motivations[$todaysMood])) {
        $motivationMessage = $motivations[$todaysMood];
    }
}

$checkStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LumiMind</title>
    <link rel="stylesheet" href="../css/mood_sections.css">
    <style>
        .motivation-popup, .error-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            text-align: center;
        }
        .motivation-popup button, .error-popup button {
            margin-top: 10px;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            background-color: #ffdd57;
            cursor: pointer;
        }
        .error-popup {
            background-color: #ffe0b2; /* Light orange for error */
            border-color: #ffb300; /* Orange border for error */
        }
        .error-popup h3 {
            color: #d32f2f; /* Dark red for error title */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-side">
            <section class="mood-input">
                <h2>Tell Us Your Mood</h2>

                <?php if (isset($_GET['success'])): ?>
                    <p style="color: green;">Your mood has been recorded!</p>
                    <div id="motivation-popup" class="motivation-popup">
                        <h3>Your Motivation</h3>
                        <p><?php echo $motivationMessage; ?></p>
                        <button onclick="closeMotivationPopup()">OK</button>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            document.getElementById('motivation-popup').style.display = 'block';
                        });
                        function closeMotivationPopup() {
                            document.getElementById('motivation-popup').style.display = 'none';
                        }
                    </script>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
                    <div id="error-popup" class="error-popup">
                        <h3>Oops!</h3>
                        <p>You have already recorded your mood for today.</p>
                        <button onclick="closeErrorPopup()">OK</button>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            document.getElementById('error-popup').style.display = 'block';
                        });
                        function closeErrorPopup() {
                            document.getElementById('error-popup').style.display = 'none';
                        }
                    </script>
                <?php endif; ?>

                <form action="process_mood.php" method="POST">
                    <div class="mood-faces">
                        <button type="submit" class="mood-face" name="mood" value="happy-excited"><img src="../icon/happy_excited.png" alt="Happy Excited"></button>
                        <button type="submit" class="mood-face" name="mood" value="angry"><img src="../icon/angry.png" alt="Angry"></button>
                        <button type="submit" class="mood-face" name="mood" value="sad"><img src="../icon/sad.png" alt="Sad"></button>
                        <button type="submit" class="mood-face" name="mood" value="surprised"><img src="../icon/surprised.png" alt="Surprised"></button>
                        <button type="submit" class="mood-face" name="mood" value="happy"><img src="../icon/happy.png" alt="Happy"></button>
                        <button type="submit" class="mood-face" name="mood" value="confused"><img src="../icon/confused.png" alt="Confused"></button>
                        <button type="submit" class="mood-face" name="mood" value="worried"><img src="../icon/worried.png" alt="Worried"></button>
                        <button type="submit" class="mood-face" name="mood" value="neutral"><img src="../icon/neutral.png" alt="Neutral"></button>
                    </div>
                </form>
            </section>

            <div class="bottom-left">
                <section class="motivation">
                    <h3>Motivation</h3>
                    <p><?php echo $motivationMessage; ?></p>
                </section>

                <section class="mood-history">
                    <h3>Mood History</h3>
                    <div class="yesterday-mood">
                        <div class="mood-display">
                            <img src="../icon/duck.png" alt="Yesterday's Mood">
                        </div>
                    </div>
                    <button class="view-more" onclick="window.location.href='moodhistory.php'">View More</button>
                </section>
            </div>
        </div>

        <section class="sticky-note">
            <div class="sticky-header">
                <h3>Sticky Note</h3>
                <button class="add-note">+</button>
            </div>
            <div class="note-items">
                <form action="process_note.php" method="POST" id="note-form">
                    <div id="note-list">
                        <label><input type="checkbox" name="note[]" value="drink_water"> Drink More Water</label><br>
                        <label><input type="checkbox" name="note[]" value="jogging"> Jogging</label><br>
                    </div>
                    <button type="submit">Save Notes</button>
                </form>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const noteItemsContainer = document.getElementById('note-list');
            const addNoteButton = document.querySelector('.sticky-note .add-note');

            addNoteButton.addEventListener('click', () => {
                const newNoteText = prompt('Enter a new sticky note:');
                if (newNoteText) {
                    const newLabel = document.createElement('label');
                    const newCheckbox = document.createElement('input');
                    newCheckbox.type = 'checkbox';
                    newCheckbox.name = 'note[]';
                    newCheckbox.value = newNoteText.toLowerCase().replace(/\s+/g, '_');
                    newLabel.appendChild(newCheckbox);
                    newLabel.appendChild(document.createTextNode(` ${newNoteText}`));
                    const newBr = document.createElement('br');
                    noteItemsContainer.appendChild(newLabel);
                    noteItemsContainer.appendChild(newBr);
                }
            });

            noteItemsContainer.addEventListener('change', (event) => {
                if (event.target.type === 'checkbox') {
                    const label = event.target.parentNode;
                    label.classList.toggle('checked', event.target.checked);
                }
            });
        });
    </script>
</body>
</html>