<?php
include 'db_connect.php';

$id = $_GET['id'];
$status = $_GET['status'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'All';

if ($status == 'Completed') {
    $sql = "UPDATE notes SET status='Completed', completed_time=NOW() WHERE id='$id'";
} else {
    $sql = "UPDATE notes SET status='Pending', completed_time=NULL WHERE id='$id'";
}

$conn->query($sql);

header("Location: StickyNote.php?filter=$filter&statusupdate=success");
exit;
?>
