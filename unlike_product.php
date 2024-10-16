<?php
@include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in.";
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    $check_like = mysqli_query($conn, "SELECT * FROM user_likes WHERE user_id = '$user_id' AND product_id = '$product_id'");

    if (mysqli_num_rows($check_like) > 0) {
        echo "Product is already liked.";
    } else {
  
        $like_product = mysqli_query($conn, "INSERT INTO user_likes (user_id, product_id) VALUES ('$user_id', '$product_id')");

        if ($like_product) {
            echo "Product successfully liked.";
        } else {
            echo "Failed to like the product.";
        }
    }
} else {
    echo "No product selected.";
}
?>