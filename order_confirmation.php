<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch the most recent order for the logged-in user
$order_query = mysqli_query($conn, "
    SELECT * FROM orders 
    WHERE user_id = '$user_id' 
    ORDER BY created_at DESC LIMIT 1
");

$order_details = mysqli_fetch_assoc($order_query);

if (!$order_details) {
    echo 'No order found!';
    exit;
}

$order_id = $order_details['id'];

// Fetch the items for the most recent order
$order_items_query = mysqli_query($conn, "
    SELECT order_items.*, products.name, products.image 
    FROM order_items
    INNER JOIN products ON order_items.product_id = products.id
    WHERE order_items.order_id = '$order_id'
");

$total_amount = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Receipt</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="order_confirmation.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="/"><img src="img/logo.png" alt="logo"></a>
            </div>
            <div class="nav-links">
                <a href="homepage.php">Home</a>
                <a href="my_purchases.php">My Purchases</a>
                <a href="about.html">About</a>
                <a href="contact.html">Contact</a>
                <a href="account.php">Account</a>
            </div>
        </nav>
    </header>

    <section class="order-confirmation-section">
        <div class="container receipt-container">
            <h2>Order Confirmation</h2>

            <div class="receipt-details">
                <p><strong>Order ID:</strong> <?php echo $order_details['id']; ?></p>
                <p><strong>Customer Name:</strong> 
                    <?php echo htmlspecialchars($order_details['first_name'] . ' ' . $order_details['last_name']); ?>
                </p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($order_details['address']); ?></p>
                <p><strong>Date:</strong> 
                    <?php echo date("F j, Y, g:i a", strtotime($order_details['created_at'])); ?>
                </p>
            </div>

            <h3>Order Items</h3>
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($order_items_query) > 0) {
                        while ($item = mysqli_fetch_assoc($order_items_query)) { 
                            $item_total = $item['price'] * $item['quantity']; 
                            $total_amount += $item_total;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>₱<?php echo number_format($item_total, 2); ?></td>
                    </tr>
                    <?php 
                        }
                    } else { 
                    ?>
                    <tr>
                        <td colspan="4">No items found in this order.</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <p class="total-summary">
                <strong>Total Amount:</strong> ₱<?php echo number_format($total_amount, 2); ?>
            </p>

            <a href="homepage.php" class="btn">Continue Shopping</a>
        </div>
    </section>
</body>
</html>