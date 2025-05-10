<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

require_once('../sidebar/sidebar.html'); 
$conn = new mysqli("localhost", "root", "", "LumiMind");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$currentDate = date("Y-m-d");
$startDate = date("Y-m-d", strtotime("-30 days"));

$stmt = $conn->prepare("SELECT mood, entry_date FROM mood_entries WHERE entry_date >= ? AND entry_date <= ? ORDER BY entry_date DESC");
$stmt->bind_param("ss", $startDate, $currentDate);
$stmt->execute();
$result = $stmt->get_result();
$moodHistory = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$monthlyMoodCounts = [];
$monthlyStmt = $conn->prepare("SELECT mood, DATE_FORMAT(entry_date, '%Y-%m') AS month FROM mood_entries ORDER BY month ASC");
$monthlyStmt->execute();
$monthlyResult = $monthlyStmt->get_result();
while ($row = $monthlyResult->fetch_assoc()) {
    if (!isset($monthlyMoodCounts[$row['month']])) {
        $monthlyMoodCounts[$row['month']] = [
            "happy-excited" => 0,
            "angry" => 0,
            "sad" => 0,
            "surprised" => 0,
            "happy" => 0,
            "confused" => 0,
            "worried" => 0,
            "neutral" => 0,
        ];
    }
    $monthlyMoodCounts[$row['month']][$row['mood']]++;
}
$monthlyStmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mood History</title>
    <link rel="stylesheet" href="mood_sections.css">
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f7f6;
        color: #333;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-bottom: 40px;
    }

    .history-container, .chart-container {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        margin: 20px;
        padding: 30px;
        width: 80%;
        max-width: 900px;
    }

    h2 {
        color: #555;
        text-align: center;
        margin-bottom: 25px;
    }

    .history-item {
        padding: 12px 0;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #666;
    }

    .history-item:last-child {
        border-bottom: none;
    }

    .history-item span:first-child {
        font-weight: bold;
        color: #444;
    }

    .chart-container p {
        color: #777;
        text-align: center;
        margin-bottom: 20px;
    }

    #monthlyMoodChart {
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        padding: 10px; /* Add some padding around the chart */
    }

    button {
        background-color: #5cb85c; /* Green button */
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 20px;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #4cae4c;
    }

    pre {
        background-color: #f8f8f8;
        border: 1px solid #eee;
        padding: 15px;
        border-radius: 6px;
        overflow-x: auto;
        font-size: 14px;
        color: #333;
    }
</style>
    </style>
</head>
<body>
    <div class="history-container">
        <h2>Past 30 Days Mood History</h2>
        <?php if (empty($moodHistory)): ?>
            <p>No mood entries found for the last 30 days.</p>
        <?php else: ?>
            <?php foreach ($moodHistory as $entry): ?>
                <div class="history-item">
                    <span><?php echo date("Y-m-d", strtotime($entry['entry_date'])); ?></span>
                    <span>Mood: <?php echo htmlspecialchars($entry['mood']); ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <button onclick="window.location.href='mood_sections.php'">Back to Mood Input</button>
    </div>

    <div class="chart-container">
        <h2>Monthly Mood Trends</h2>
        <p>This section will display a bar chart or histogram of your mood changes per month.</p>
    </div>

    <script>
        // JavaScript for Chart.js integration (example)
        document.addEventListener('DOMContentLoaded', () => {
            const chartCanvas = document.getElementById('monthlyMoodChart');
            if (chartCanvas && <?php echo !empty($monthlyMoodCounts) ? 'true' : 'false'; ?>) {
                const labels = <?php echo json_encode(array_keys($monthlyMoodCounts)); ?>;
                const datasets = [];
                const moodTypes = ["happy-excited", "angry", "sad", "surprised", "happy", "confused", "worried", "neutral"];
                const colors = ['#ffdd57', '#f44336', '#2196f3', '#4caf50', '#ff9800', '#9c27b0', '#607d8b', '#795548']; // Example colors

                moodTypes.forEach((mood, index) => {
                    const data = labels.map(month => $monthlyMoodCounts[month]?.[mood] || 0);
                    datasets.push({
                        label: mood,
                        data: data,
                        backgroundColor: colors[index],
                        borderColor: colors[index],
                        borderWidth: 1
                    });
                });

                new Chart(chartCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Entries'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>