<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

$host = "localhost";
$user = "root";
$password = "";
$dbname = "lumimind";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>