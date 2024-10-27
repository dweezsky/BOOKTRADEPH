<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    $check_like = mysqli_query($conn, "
        SELECT * FROM user_likes 
        WHERE user_id = '$user_id' AND product_id = '$product_id'
    ");

    if (mysqli_num_rows($check_like) > 0) {
        mysqli_query($conn, "
            DELETE FROM user_likes 
            WHERE user_id = '$user_id' AND product_id = '$product_id'
        ");
        echo "Product unliked.";
    } else {
        echo "Product not found in likes.";
    }
} else {
    echo "No product selected.";
}
?>