<?php
// Include the database connection
@include 'config.php';

// Set headers to trigger download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=transactions.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Query to fetch all transactions
$order_items_query = mysqli_query($conn, "
    SELECT 
        orders.id AS order_id, 
        orders.status, 
        orders.created_at, 
        orders.address, 
        GROUP_CONCAT(products.name SEPARATOR ', ') AS product_names, 
        GROUP_CONCAT(order_items.quantity SEPARATOR ', ') AS quantities,
        GROUP_CONCAT(order_items.price SEPARATOR ', ') AS product_prices,
        SUM(order_items.price * order_items.quantity) AS total_amount,
        users.first_name, 
        users.last_name
    FROM order_items
    INNER JOIN orders ON order_items.order_id = orders.id
    INNER JOIN products ON order_items.product_id = products.id
    INNER JOIN users ON orders.user_id = users.id
    GROUP BY orders.id
    ORDER BY orders.id DESC
");

// Create the table structure for Excel export
echo "<table border='1'>";
echo "<tr>
        <th>Order ID</th>
        <th>Customer Name</th>
        <th>Products</th>
        <th>Quantities</th>
        <th>Product Prices</th>
        <th>Total Amount</th>
        <th>Address</th>
        <th>Time of Purchase</th>
        <th>Status</th>
      </tr>";

// Populate the table rows with data from the query
while ($order = mysqli_fetch_assoc($order_items_query)) {
    echo "<tr>
            <td>{$order['order_id']}</td>
            <td>{$order['first_name']} {$order['last_name']}</td>
            <td>{$order['product_names']}</td>
            <td>{$order['quantities']}</td>
            <td>₱{$order['product_prices']}</td>
            <td>₱" . number_format($order['total_amount'], 2) . "</td>
            <td>{$order['address']}</td>
            <td>" . date("F j, Y, g:i a", strtotime($order['created_at'])) . "</td>
            <td>{$order['status']}</td>
          </tr>";
}

echo "</table>";
?>