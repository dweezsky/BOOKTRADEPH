<?php
@include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders based on their status for the logged-in user
$orders_query = mysqli_query($conn, "
    SELECT orders.id AS order_id, orders.status, orders.created_at, orders.received_at, 
           GROUP_CONCAT(products.name SEPARATOR ', ') AS products, 
           GROUP_CONCAT(products.image SEPARATOR ', ') AS product_images,
           SUM(order_items.price * order_items.quantity) AS total_amount
    FROM order_items
    INNER JOIN orders ON order_items.order_id = orders.id
    INNER JOIN products ON order_items.product_id = products.id
    WHERE orders.user_id = '$user_id'
    GROUP BY orders.id
    ORDER BY orders.id DESC
");

$orders = [];
while ($row = mysqli_fetch_assoc($orders_query)) {
    $orders[] = $row;
}

// Handle user confirmation that the item was received
if (isset($_POST['confirm_receive'])) {
    $order_id = $_POST['order_id'];
    mysqli_query($conn, "UPDATE orders SET status = 'Completed', received_at = NOW() WHERE id = $order_id");
    header('Location: my_purchases.php'); // Refresh the page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Purchases</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="my_purchases.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <header>
    <nav>
            <div class="logo">
                <a href="/"><img src="img/logo.png" alt="logo"></a>
            </div>
            <div class="nav-links">
                <a href="homepage.php">Home</a>
                <a href="my_purchases.php" class="active">My Purchases</a>
                <a href="about.html">About</a>
                <a href="contact.html">Contact</a>
                <a href="account.php">Account</a>
            </div>

            <form class="search-container">
                <input type="text" id="search-bar" placeholder="Search">
                <a href="#"><img class="search-icon" src="http://www.endlessicons.com/wp-content/uploads/2012/12/search-icon.png"></a>
            </form>

            <div class="right-nav">
                <a href="liked_products.php"><i class="fa-regular fa-heart"></i></a>
                <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
            </div>
        </nav>
    </header>

</head>
<body>

<div class="container">
    <h1>My Purchases</h1>

    <nav>
        <ul>
            <li><a href="#" class="tab-link" data-tab="to-ship">To Ship</a></li>
            <li><a href="#" class="tab-link" data-tab="to-receive">To Receive</a></li>
            <li><a href="#" class="tab-link" data-tab="history">History</a></li>
        </ul>
    </nav>

    <section class="transactions">

        <div class="transaction-tab active" id="to-ship">
            <h2>To Ship</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Products</th>
                        <th>Images</th>
                        <th>Total Amount</th>
                        <th>Time of Purchase</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <?php if ($order['status'] == 'To Ship'): ?>
                    <tr>
                        <td><?= $order['order_id']; ?></td>
                        <td><?= $order['products']; ?></td>
                        <td class="product-images">
                            <?php foreach (explode(', ', $order['product_images']) as $image): ?>
                                <img src="uploaded_img/<?= $image; ?>" alt="Product Image">
                            <?php endforeach; ?>
                        </td>
                        <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                        <td><?= date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


        <div class="transaction-tab" id="to-receive">
            <h2>To Receive</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Products</th>
                        <th>Images</th>
                        <th>Total Amount</th>

                        <th>Time of Purchase</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <?php if ($order['status'] == 'Pending'): ?>
                    <tr>
                        <td><?= $order['order_id']; ?></td>
                        <td><?= $order['products']; ?></td>
                        <td class="product-images">
                            <?php foreach (explode(', ', $order['product_images']) as $image): ?>
                                <img src="uploaded_img/<?= $image; ?>" alt="Product Image">
                            <?php endforeach; ?>
                        </td>
                        <td>₱<?= number_format($order['total_amount'], 2); ?></td>

                        <td><?= date("F j, Y, g:i a", strtotime($order['created_at'])); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?= $order['order_id']; ?>">
                                <button type="submit" name="confirm_receive" class="btn">Mark as Received</button>
                            </form>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- History -->
        <div class="transaction-tab" id="history">
            <h2>History</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Products</th>
                        <th>Images</th>
                        <th>Total Amount</th>
                        <th>Time of Delivery</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <?php if ($order['status'] == 'Completed'): ?>
                    <tr>
                        <td><?= $order['order_id']; ?></td>
                        <td><?= $order['products']; ?></td>
                        <td class="product-images">
                            <?php foreach (explode(', ', $order['product_images']) as $image): ?>
                                <img src="uploaded_img/<?= $image; ?>" alt="Product Image">
                            <?php endforeach; ?>
                        </td>
                        <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                        <td><?= date("F j, Y, g:i a", strtotime($order['received_at'])); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
    const tabs = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.transaction-tab');

    tabs.forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            const targetTab = this.getAttribute('data-tab');
            tabContents.forEach(content => content.classList.remove('active'));
            document.getElementById(targetTab).classList.add('active');
        });
    });
</script>

</body>
</html>