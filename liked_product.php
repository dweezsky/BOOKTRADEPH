<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo 'User not logged in';
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Check if the product is already liked
    $check_liked = mysqli_query($conn, "
        SELECT * FROM user_likes 
        WHERE user_id = '$user_id' AND product_id = '$product_id'
    ");

    if (mysqli_num_rows($check_liked) > 0) {
        echo 'You already liked this product.';
    } else {
        // Insert the like into the user_likes table
        $insert_like = mysqli_query($conn, "
            INSERT INTO user_likes (user_id, product_id) 
            VALUES ('$user_id', '$product_id')
        ");

        if ($insert_like) {
            echo 'Product added to your liked list.';
        } else {
            echo 'Error liking the product.';
        }
    }
} else {
    echo 'No product selected.';
}
?>