<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo 'User not logged in';
    exit;
}

$user_id = $_SESSION['user_id'];

// Query to fetch unread notifications count
$unread_count_query = mysqli_query($conn, "
    SELECT COUNT(*) AS unread_count 
    FROM orders 
    WHERE user_id = '$user_id' AND is_read = 0 
      AND (status = 'Rejected' OR status = 'Shipped')
");

$unread_count = mysqli_fetch_assoc($unread_count_query)['unread_count'];

// Query to fetch all notifications
$notifications_query = mysqli_query($conn, "
    SELECT 
        orders.id AS order_id, 
        orders.status, 
        GROUP_CONCAT(products.name SEPARATOR ', ') AS product_names, 
        GROUP_CONCAT(products.image SEPARATOR ', ') AS product_images 
    FROM orders
    INNER JOIN order_items ON orders.id = order_items.order_id
    INNER JOIN products ON order_items.product_id = products.id
    WHERE orders.user_id = '$user_id' 
      AND (orders.status = 'Rejected' OR orders.status = 'Shipped')
    GROUP BY orders.id, orders.status
    ORDER BY orders.created_at DESC
");

ob_start(); // Start output buffering
if (mysqli_num_rows($notifications_query) > 0) {
    while ($notification = mysqli_fetch_assoc($notifications_query)) {
        $message = ($notification['status'] === 'Rejected') 
            ? "Your order '{$notification['product_names']}' has been rejected."
            : "Your order '{$notification['product_names']}' is on the way!";

        $images = explode(', ', $notification['product_images']);
        ?>
        <div class="notification-item">
            <div class="product-images">
                <?php foreach ($images as $image) { ?>
                    <img src="uploaded_img/<?php echo htmlspecialchars($image); ?>" alt="Product Image">
                <?php } ?>
            </div>
            <div>
                <p><?php echo htmlspecialchars($message); ?></p>
                <p><small>Order ID: <?php echo htmlspecialchars($notification['order_id']); ?></small></p>
            </div>
        </div>
        <?php
    }
} else {
    echo '<p>No notifications available.</p>';
}
$html_content = ob_get_clean(); // Get the buffered output

// Return the response as JSON
echo json_encode([
    'unread_count' => $unread_count,
    'html' => $html_content
]);
?>