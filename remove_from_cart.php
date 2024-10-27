<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo 'Unauthorized request';
    exit;
}

if (isset($_POST['product_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];

    $remove_query = mysqli_query($conn, "
        DELETE FROM user_cart 
        WHERE user_id = '$user_id' AND product_id = '$product_id'
    ");

    if ($remove_query) {
        echo 'Item removed';
    } else {
        echo 'Failed to remove item: ' . mysqli_error($conn);
    }
} else {
    echo 'Invalid request';
}
?>