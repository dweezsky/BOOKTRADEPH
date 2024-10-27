<?php
@include 'config.php';

// Mark all unread notifications as read
$update_query = "UPDATE orders SET is_read = 1 WHERE is_read = 0 AND status IN ('Canceled', 'Received')";
mysqli_query($conn, $update_query);
?>