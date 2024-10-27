<?php
@include 'config.php';

if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $order_id");

    if ($status == 'Pending') {
        $result = mysqli_query($conn, "
            SELECT * FROM orders WHERE id = $order_id
        ");
        $order = mysqli_fetch_assoc($result);

        echo "<tr>
                <td>{$order['id']}</td>
                <td>{$order['customer_name']}</td>
                <td>{$order['products']}</td>
                <td>₱{$order['total_amount']}</td>
                <td>{$order['address']}</td>
                <td>{$order['created_at']}</td>
                <td>{$order['status']}</td>
              </tr>";
    }
}
?>