<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo 'User not logged in';
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

$check_cart = mysqli_query($conn, "SELECT * FROM user_cart WHERE user_id = '$user_id' AND product_id = '$product_id'");

if (mysqli_num_rows($check_cart) > 0) {
    $update_cart = "UPDATE user_cart SET quantity = quantity + '$quantity' WHERE user_id = '$user_id' AND product_id = '$product_id'";
    mysqli_query($conn, $update_cart);
    echo 'Cart updated successfully';
} else {
    $add_to_cart = "INSERT INTO user_cart (user_id, product_id, quantity) VALUES ('$user_id', '$product_id', '$quantity')";
    mysqli_query($conn, $add_to_cart);
    echo 'Product added to cart successfully';
}
?>