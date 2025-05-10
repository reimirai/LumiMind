<?php
session_start();

$host = 'localhost';
$dbname = 'lumimind';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

if (
    isset($_POST['username']) &&
    isset($_POST['email']) &&
    isset($_POST['dob']) &&
    isset($_POST['password'])
) {
    $name = trim($_POST['username']);
    $email = trim($_POST['email']);
    $dob = trim($_POST['dob']);
    $plainPassword = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE Email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "Email is already registered.";
        exit;
    }

    // Generate a new ID (e.g., U0001)
    $idStmt = $pdo->query("SELECT ID FROM users ORDER BY ID DESC LIMIT 1");
    if ($row = $idStmt->fetch(PDO::FETCH_ASSOC)) {
        $lastId = $row['ID'];
        $num = intval(substr($lastId, 1)) + 1;
        $newId = 'A' . str_pad($num, 4, '0', STR_PAD_LEFT);
    } else {
        $newId = 'A0001';
    }

    // Insert new user
    $insertStmt = $pdo->prepare("
        INSERT INTO users (ID, Name, Email, BirthDate, Password, Points)
        VALUES (:id, :name, :email, :birthdate, :password, 0)
    ");
    $insertStmt->bindParam(':id', $newId);
    $insertStmt->bindParam(':name', $name);
    $insertStmt->bindParam(':email', $email);
    $insertStmt->bindParam(':birthdate', $dob);
    $insertStmt->bindParam(':password', $plainPassword); // use password_hash in production

    if ($insertStmt->execute()) {
        $_SESSION['user_id'] = $newId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        echo "success";
    } else {
        echo "Registration failed. Please try again.";
    }
} else {
    echo "Missing required fields.";
}
?>
