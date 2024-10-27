<?php
@include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: my_purchases.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT first_name, last_name, email FROM users WHERE id = '$user_id'");


$select_products = mysqli_query($conn, "SELECT * FROM products");
if ($user_query && mysqli_num_rows($user_query) > 0) {
    $user = mysqli_fetch_assoc($user_query);
    $user['name'] = $user['first_name'] . ' ' . $user['last_name']; // Combine first and last names
} else {
    $user = ['name' => 'Unknown', 'email' => 'Not available']; // Fallback values if query fails
}
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
// Handle order cancellation
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    mysqli_query($conn, "UPDATE orders SET status = 'Canceled' WHERE id = $order_id");
    header('Location: my_purchases.php');
    exit;
}

// Handle item received confirmation
if (isset($_POST['confirm_receive'])) {
    $order_id = $_POST['order_id'];
    mysqli_query($conn, "UPDATE orders SET status = 'Completed', received_at = NOW() WHERE id = $order_id");
    header('Location: my_purchases.php');
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
                <a href="my_purchases.php"class="active" >My Purchases</a>
                <a href="about.php">About</a>
                <a href="#contact-section">Contact</a>
                
            </div>

            <form class="search-container">
                <input type="text" id="search-bar" placeholder="Search">
                <a href="#"><img class="search-icon" src="http://www.endlessicons.com/wp-content/uploads/2012/12/search-icon.png"></a>
            </form>

            <div class="right-nav">
            <a href="liked_products.php"><i class="fa-regular fa-heart"></i></a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
                <a href="javascript:void(0);" onclick="openNotificationModal()">
                    <i class="fa-regular fa-bell"></i>
                </a>
                <a href="javascript:void(0);" onclick="openProfileModal()">
                    <i class="fa-regular fa-user"></i>
                </a>
               
            </div>

<!-- Notification Modal -->
<div id="notificationModal" class="notification-modal">
    <div class="notification-header">
        <h3>Notifications</h3>
        <span class="close-btn" onclick="closeNotificationModal()">&times;</span>
    </div>
    <div id="notificationContent" class="notification-content">
        <p>Loading notifications...</p>
    </div>
</div>
</div>
 <!-- Profile Modal -->
 <div id="profileModal" class="profile-modal">
    <div class="profile-modal-content">
        <span class="close-profile-btn" onclick="closeProfileModal()">&times;</span>
        <h3>Account Information</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>

        <div class="modal-buttons">
            <button class="btn" onclick="window.location.href='my_purchases.php'">Purchase History</button>
            <button class="btn btn-logout" onclick="window.location.href='index(2).html'">Logout</button>
        </div>
    </div>
</div>
    </header>

</head>
<body>

<div class="container">
    <h1>My Purchases</h1>

    <nav>
        <ul>
            <li><a href="#" class="tab-link" data-tab="to-ship">To Ship</a></li>
            <li><a href="#" class="tab-link" data-tab="to-receive">To Receive</a></li>
            <li><a href="#" class="tab-link" data-tab="rejected">Rejected</a></li>
            <li><a href="#" class="tab-link" data-tab="canceled">Canceled</a></li>
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
                        <th>Action</th>
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
                        <td>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?= $order['order_id']; ?>">
                                <button type="submit" name="cancel_order" class="btn btn-danger">Cancel</button>
                            </form>
                        </td>
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
        <!-- Rejected -->
        <div class="transaction-tab" id="rejected">
            <h2>Rejected Transactions</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Products</th>
                        <th>Images</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <?php if ($order['status'] == 'Rejected'): ?>
                    <tr>
                        <td><?= $order['order_id']; ?></td>
                        <td><?= $order['products']; ?></td>
                        <td class="product-images">
                            <?php foreach (explode(', ', $order['product_images']) as $image): ?>
                                <img src="uploaded_img/<?= $image; ?>" alt="Product Image">
                            <?php endforeach; ?>
                        </td>
                        <td>₱<?= number_format($order['total_amount'], 2); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<div class="transaction-tab" id="canceled">
        <h2>Canceled Transactions</h2>
        <table class="transaction-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Products</th>
                    <th>Images</th>
                    <th>Total Amount</th>
                    <th>Time of Cancellation</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <?php if ($order['status'] == 'Canceled'): ?>
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
            // Open the notification modal
            function openNotificationModal() {
    const modal = document.getElementById('notificationModal');
    modal.classList.add('show');

    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'notification.php', true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText); // Parse the JSON response

            // Update the notification content
            document.getElementById('notificationContent').innerHTML = response.html;

            // Reset the badge count after viewing notifications
            document.getElementById('notificationBadge').style.display = 'none';
        } else {
            document.getElementById('notificationContent').innerHTML = '<p>No notifications available.</p>';
        }
    };

    xhr.onerror = function () {
        document.getElementById('notificationContent').innerHTML = '<p>Error loading notifications. Please try again.</p>';
    };

    xhr.send();
}
// Load unread notification count on page load
function loadNotificationCount() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'notification.php', true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            const badge = document.getElementById('notificationBadge');

            if (response.unread_count > 0) {
                badge.style.display = 'inline-block';
                badge.textContent = response.unread_count;
            } else {
                badge.style.display = 'none';
            }
        }
    };

    xhr.send();
}

function closeNotificationModal() {
    document.getElementById('notificationModal').classList.remove('show');
}

// Open and close the profile modal
function openProfileModal() {
    const modal = document.getElementById('profileModal');
    modal.style.display = 'flex'; // Show modal using flexbox
}

// Close the profile modal
function closeProfileModal() {
    const modal = document.getElementById('profileModal');
    modal.style.display = 'none'; // Hide modal
}

// Ensure the modal state is correctly managed on page load
window.onload = function () {
    const modal = document.getElementById('profileModal');
    modal.style.display = 'none'; // Make sure modal is hidden on load
};
    </script>

  <footer class="footer">
    <div class="footer-container">
        <div class="footer-section logo-section">
        <div class="logo">
        
                <a href="/"><img src="img/logo.png" alt="logo"></a>
                
            </div>
        
        </div>
    
        <div class="footer-section site-map">
            <h3>Site Map</h3>
            <ul>
                <li><a href="homepage.php">Home</a></li>
                <li><a href="my_purchases.php">My Purchases</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="liked_products.php">Liked Products</a></li>
                <li><a href="cart.php">Cart</a></li>

            </ul>
        </div>

        <div class="footer-section contact-section"id="contact-section">
            <h3>Contact Us</h3>
            <form class="contact-form">
                <textarea placeholder="Message" rows="4" required></textarea>
                <button type="submit">Send</button>
            </form>
        </div>

    </div>
    <p class="copyright">Copyright © 2024 BookTrade PH. All Rights Reserved.</p>
</footer>


</body>
</html>