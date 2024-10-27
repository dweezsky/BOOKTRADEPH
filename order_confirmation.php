<?php
session_start();
@include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: homepage.php'); // Redirect to login page if not logged in
    exit;
}
$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT first_name, last_name, email FROM users WHERE id = '$user_id'");

if ($user_query && mysqli_num_rows($user_query) > 0) {
    $user = mysqli_fetch_assoc($user_query);
    $user['name'] = $user['first_name'] . ' ' . $user['last_name']; // Combine first and last names
} else {
    $user = ['name' => 'Unknown', 'email' => 'Not available']; // Fallback values if query fails
}
// Fetch products from the database
$select_products = mysqli_query($conn, "SELECT * FROM products");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            
            <form class="search-container">
                <input type="text" id="search-bar" placeholder="Search">
                <a href="#"><img class="search-icon" src="http://www.endlessicons.com/wp-content/uploads/2012/12/search-icon.png"></a>
            </form>

            <div class="right-nav">
            <a href="liked_products.php"><i class="fa-regular fa-heart"></i></a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
            <a href="javascript:void(0);" onclick="openNotificationModal()" class="notification-icon-container">
        <i class="fa-regular fa-bell"></i>
        <span id="notificationBadge" class="badge">0</span>
    </a>
                <a href="javascript:void(0);" onclick="openProfileModal()">
                    <i class="fa-regular fa-user"></i>
                </a>
               
            </div>
        </nav>
    </header>
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
    <script>
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
</body>
</html>