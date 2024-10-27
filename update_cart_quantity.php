<?php
session_start();
@include 'config.php';

if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    mysqli_query($conn, "
        UPDATE user_cart 
        SET quantity = '$quantity' 
        WHERE product_id = '$product_id' AND user_id = '$user_id'
    ");

    $cart_items = mysqli_query($conn, "
        SELECT products.price, user_cart.quantity 
        FROM products
        INNER JOIN user_cart ON products.id = user_cart.product_id
        WHERE user_cart.user_id = '$user_id'
    ");

    $new_total = 0;
    $new_item_total = 0;

    while ($item = mysqli_fetch_assoc($cart_items)) {
        $item_total = $item['price'] * $item['quantity'];
        $new_total += $item_total;

        if ($item['product_id'] == $product_id) {
            $new_item_total = $item_total;
        }
    }

    echo json_encode([
        'success' => true,
        'new_item_total' => $new_item_total,
        'new_total' => $new_total
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>