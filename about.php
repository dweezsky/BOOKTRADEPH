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

if ($user_query && mysqli_num_rows($user_query) > 0) {
    $user = mysqli_fetch_assoc($user_query);
    $user['name'] = $user['first_name'] . ' ' . $user['last_name']; // Combine first and last names
} else {
    $user = ['name' => 'Unknown', 'email' => 'Not available']; // Fallback values if query fails
}
// Fetch products from the database
$select_products = mysqli_query($conn, "SELECT * FROM products");
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
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Responsive team section</title>
	<link rel="stylesheet" type="text/css" href="about.css">
	<link rel="stylesheet" href="homepage.css">

	<link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">

	<link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
	<section class="section">
        <div class="section__container">
          <div class="content">
            <p class="subtitle"></p>
            <h1 class="title">
        	<span>BOOKTRADE PH<br /></span>
            </h1>
            <p class="description">
				Welcome to BookTrade, your ultimate online marketplace for 
				buying, selling, and trading books! Whether you're a casual reader, a passionate collector, 
				or a student looking for affordable textbooks, 
				BookTrade connects bibliophiles from all walks of life.
            </p>
            <div class="action__btns">
			<a href="index.html"  button class="read_more">Home</button>
            </div>
          </div>
          <div class="image">
            <img src="img(about)/logo.png" alt="profile" />
          </div>
        </div>
      </section>
	  <header>
		<nav>
				<div class="logo">
					<a href="/"><img src="img/logo.png" alt="logo"></a>
				</div>
				<div class="nav-links">
					<a href="homepage.php">Home</a>
					<a href="my_purchases.php" >My Purchases</a>
					<a href="about.php"class="active">About</a>
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
                        <span id="notificationBadge" class="badge">0</span>
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
	<section class="team">
		<div class="center">
			<h1>Our Team</h1>
		</div>

		<div class="team-content">
			<div class="box">
				<img src="img(about)/01.png">
				<h3>Joshua Ferrer</h3>
				<h5>Project Manager</h5>
				<div class="icons">
					<a href="#"><i class="ri-facebook-box-fill"></i></a>
					<a href="#"><i class="ri-instagram-fill"></i></a>
				</div>
			</div>

			<div class="box">
				<img src="img(about)/02.png">
				<h3>Daniel Miran</h3>
				<h5>UI/UX</h5>
				<div class="icons">
					<a href="#"><i class="ri-facebook-box-fill"></i></a>
					<a href="#"><i class="ri-instagram-fill"></i></a>
				</div>
			</div>

			<div class="box">
				<img src="img(about)/03.png">
				<h3>Alpons De Venecia</h3>
				<h5>Back End Developer</h5>
				<div class="icons">
					<a href="#"><i class="ri-facebook-box-fill"></i></a>
					<a href="#"><i class="ri-instagram-fill"></i></a>
				</div>
			</div>

			<div class="box">
				<img src="img(about)/04.png">
				<h3>Francis Juguilon</h3>
				<h5>Front End Developer</h5>
				<div class="icons">
					<a href="#"><i class="ri-facebook-box-fill"></i></a>
					<a href="#"><i class="ri-instagram-fill"></i></a>
				</div>
			</div>

            <div class="box">
				<img src="img(about)/01.png">
				<h3>Rhomalyn Flores</h3>
				<h5>Back End Developer</h5>
				<div class="icons">
					<a href="#"><i class="ri-facebook-box-fill"></i></a>
					<a href="#"><i class="ri-instagram-fill"></i></a>
				</div>
			</div>
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

// Call this function when the page loads
window.onload = loadNotificationCount;

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
</script>
</body>

</html>