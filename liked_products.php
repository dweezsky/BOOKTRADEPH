<?php
session_start();
@include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
// Fe
// Fetch liked products with product details
$liked_products = mysqli_query($conn, "
    SELECT products.* 
    FROM products
    INNER JOIN user_likes ON products.id = user_likes.product_id
    WHERE user_likes.user_id = '$user_id'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liked Products - BookTrade PH</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="liked_products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
		<nav>
				<div class="logo">
					<a href="/"><img src="img/logo.png" alt="logo"></a>
				</div>
				<div class="nav-links">
					<a href="homepage.php"class="active">Home</a>
					<a href="my_purchases.php" >My Purchases</a>
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
            <button class="btn btn-logout" onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </div>
</div>
		</header>

<section class="liked-products-section">
    <h3>Your Liked Products</h3>
    <div class="liked-products">
        <?php if (mysqli_num_rows($liked_products) > 0) { ?>
            <?php while ($product = mysqli_fetch_assoc($liked_products)) { ?>
                <div class="liked-item">
                    <img src="uploaded_img/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    <p><?php echo $product['name']; ?></p>
                    <div class="price">₱<?php echo $product['price']; ?></div>
                    <div class="icon-product">
                        <i class="fa-solid fa-cart-shopping" onclick="openModal(<?php echo $product['id']; ?>)"></i>
                        <i class="fa-solid fa-heart" onclick="unlikeProduct(<?php echo $product['id']; ?>)"></i>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>You have not liked any products yet.</p>
        <?php } ?>
    </div>
</section>

<div id="cartModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <img id="modalImage" src="" alt="Product Image">
        <h3 id="modalProductName"></h3>
        <p id="modalProductPrice"></p>
        <p>Total: ₱<span id="totalPriceValue"></span></p>
        <input type="number" id="quantityInput" value="1" min="1" oninput="updateTotalPrice()">
        <br>
        <button onclick="addToCart()">Add to Cart</button>
    </div>
</div>

<div id="feedback">Product added to cart</div>

<script>
    function unlikeProduct(product_id) {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'unlike_product.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = xhr.responseText.trim();
                if (response === "Product unliked.") {
                    location.reload(); // Reload the page
                } else {
                    alert(response);
                }
            }
        };
        xhr.onerror = function() {
            alert('An error occurred while processing the request.');
        };
        xhr.send('product_id=' + product_id);
    }

    let selectedProduct = {};

    function openModal(id) {
        selectedProduct = {
            id: id,
            name: document.querySelector(`.title-prod`).innerText,
            price: parseFloat(document.querySelector(`.price span`).innerText.replace('₱', ''))
        };

        document.getElementById('modalProductName').innerText = selectedProduct.name;
        document.getElementById('modalProductPrice').innerText = "₱" + selectedProduct.price;
        document.getElementById('totalPriceValue').innerText = selectedProduct.price;
        document.getElementById('cartModal').style.display = 'flex';
    }

    function updateTotalPrice() {
        let quantity = parseInt(document.getElementById('quantityInput').value);
        let totalPrice = selectedProduct.price * quantity;
        document.getElementById('totalPriceValue').innerText = totalPrice;
    }

    function addToCart() {
        let quantity = parseInt(document.getElementById('quantityInput').value);
        if (quantity > selectedProduct.stock) {
            alert("Sorry, only " + selectedProduct.stock + " items are available.");
            return;
        }

        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'add_to_cart.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('feedback').style.display = 'block';
                setTimeout(() => {
                    document.getElementById('feedback').style.display = 'none';
                }, 2000);
            }
        };
        xhr.send(`product_id=${selectedProduct.id}&quantity=${quantity}`);
        document.getElementById('cartModal').style.display = 'none';
    }

    function closeModal() {
        document.getElementById('cartModal').style.display = 'none';
    }
    // Open the notification modal
	function openNotificationModal() {
    const modal = document.getElementById('notificationModal');
    modal.classList.add('show'); // Open the modal

    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'notification.php', true); // Fetch notifications via AJAX

    xhr.onload = function () {
        if (xhr.status === 200) {
            console.log('Notification Response:', xhr.responseText); // Debugging

            document.getElementById('notificationContent').innerHTML = xhr.responseText;
        } else {
            document.getElementById('notificationContent').innerHTML = '<p>No notifications available.</p>';
        }
    };

    xhr.onerror = function () {
        console.error('Error loading notifications.');
        document.getElementById('notificationContent').innerHTML = '<p>Error loading notifications. Please try again.</p>';
    };

    xhr.send();
}

function closeNotificationModal() {
    document.getElementById('notificationModal').classList.remove('show');
}

 // Open and close the profile modal
 function openProfileModal() {
    document.getElementById('profileModal').style.display = 'block';
    sessionStorage.setItem('profileModalOpen', 'true'); // Store the state as open
}

function closeProfileModal() {
    document.getElementById('profileModal').style.display = 'none';
    sessionStorage.setItem('profileModalOpen', 'false'); // Store the state as closed
}

// Check modal state on page load and apply the correct state
window.onload = function () {
    const isModalOpen = sessionStorage.getItem('profileModalOpen');
    if (isModalOpen === 'true') {
        document.getElementById('profileModal').style.display = 'block';
    } else {
        document.getElementById('profileModal').style.display = 'none';
    }
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