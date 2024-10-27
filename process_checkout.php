<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo 'User not logged in';
    exit;
}

$user_id = $_SESSION['user_id'];
$address = mysqli_real_escape_string($conn, $_POST['address']);
$selected_items = json_decode($_POST['items'], true); // Get selected items

// Create a new order
mysqli_query($conn, "
    INSERT INTO orders (user_id, first_name, last_name, address, status, created_at) 
    VALUES (
        '$user_id', 
        (SELECT first_name FROM users WHERE id = '$user_id'), 
        (SELECT last_name FROM users WHERE id = '$user_id'), 
        '$address', 'To Ship', NOW()
    )
");

$order_id = mysqli_insert_id($conn);
$total_amount = 0;

// Loop through the selected items to insert them into `order_items` and update product stock
foreach ($selected_items as $product) {
    $product_id = $product['id'];
    $quantity = $product['quantity'];

    // Fetch the product price and stock
    $product_query = mysqli_query($conn, "SELECT price, stock FROM products WHERE id = '$product_id'");
    $product_data = mysqli_fetch_assoc($product_query);
    $price = $product_data['price'];
    $current_stock = $product_data['stock'];

    // Check if there is enough stock available
    if ($current_stock < $quantity) {
        echo "Not enough stock for product ID: $product_id";
        exit;
    }

    // Calculate item total and add to the total amount
    $item_total = $price * $quantity;
    $total_amount += $item_total;

    // Insert the item into the `order_items` table
    mysqli_query($conn, "
        INSERT INTO order_items (order_id, product_id, quantity, price) 
        VALUES ('$order_id', '$product_id', '$quantity', '$price')
    ");

    // Update the product stock
    $new_stock = $current_stock - $quantity;
    mysqli_query($conn, "
        UPDATE products 
        SET stock = '$new_stock' 
        WHERE id = '$product_id'
    ");

    // Remove the purchased items from the cart
    mysqli_query($conn, "
        DELETE FROM user_cart 
        WHERE product_id = '$product_id' AND user_id = '$user_id'
    ");
}

// Update the total amount in the orders table
mysqli_query($conn, "UPDATE orders SET total_amount = '$total_amount' WHERE id = '$order_id'");

echo 'success';
?>