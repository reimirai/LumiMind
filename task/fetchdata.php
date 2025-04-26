<?php
include 'db.php';

$sql = "SELECT id, reward_title, reward_description, point_cost,pic FROM reward";
$result = $conn->query($sql);

$data = [];

?>