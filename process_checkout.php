<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo 'User not logged in';
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = mysqli_query($conn, "SELECT first_name, last_name FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_query);
$first_name = $user['first_name'];
$last_name = $user['last_name'];

// Capture the address from the request
$address = mysqli_real_escape_string($conn, $_POST['address']);

// Fetch cart items and ensure correct quantities
$cart_items = mysqli_query($conn, "
    SELECT products.*, user_cart.quantity 
    FROM products
    INNER JOIN user_cart ON products.id = user_cart.product_id
    WHERE user_cart.user_id = '$user_id'
");

if (mysqli_num_rows($cart_items) > 0) {

    // Insert new order into the orders table
    $order_inserted = mysqli_query($conn, "
        INSERT INTO orders (user_id, first_name, last_name, total_amount, address, status, created_at) 
        VALUES ('$user_id', '$first_name', '$last_name', 0, '$address', 'To Ship', NOW())
    ");
    $order_id = mysqli_insert_id($conn);

    $total_amount = 0;

    // Insert each cart item as an order item
    while ($item = mysqli_fetch_assoc($cart_items)) {
        $quantity = $item['quantity'];
        $item_total = $item['price'] * $quantity;
        $total_amount += $item_total;

        $product_id = $item['id'];

        mysqli_query($conn, "
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES ('$order_id', '$product_id', '$quantity', '{$item['price']}')
        ");

        // Update the product stock based on the quantity purchased
        mysqli_query($conn, "
            UPDATE products 
            SET stock = stock - '$quantity' 
            WHERE id = '$product_id'
        ");
    }

    // Update the total amount in the orders table
    mysqli_query($conn, "UPDATE orders SET total_amount = '$total_amount' WHERE id = '$order_id'");

    // Clear the user's cart after the purchase
    mysqli_query($conn, "DELETE FROM user_cart WHERE user_id = '$user_id'");

    echo 'success';
} else {
    echo 'Cart is empty';
}
?>