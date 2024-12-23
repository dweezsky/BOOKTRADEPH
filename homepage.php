<?php
// Include the database connection
@include 'config.php';
session_start();
// Check if the user is logged in
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
// Fetch the user's cart and liked products from the database
$cart_items = mysqli_query($conn, "SELECT * FROM user_cart WHERE user_id = '$user_id'");
// Fetch liked products to be used on the liked_products.php page
$liked_products = mysqli_query($conn, "SELECT * FROM user_likes WHERE user_id = '$user_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookTrade PH</title>

    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      
        #feedback {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: none;
        }
        #likeMessage{
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            display: none;
        }

        /* Liked Product Styling */
        .liked-products {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 50px;
        }

        .liked-products .liked-item {
            width: 150px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .liked-products .liked-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        .liked-products .liked-item .price {
            margin: 10px 0;
        }

        .liked-products .liked-item .icon-product {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .liked-products .liked-item .icon-product i {
            font-size: 18px;
            cursor: pointer;
        }
    </style>
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
            <a href="javascript:void(0);" onclick="openNotificationModal()" class="notification-icon-container">
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

    <section class="products">
        <div class="contnaier">
            <div class="top-sec">
                <h3>BookTradePH</h3>
            </div>

            <div class="items" id="productList">
                <!-- Loop through the fetched products from the database -->
                <?php while ($product = mysqli_fetch_assoc($select_products)) { ?>
                <div class="item">
                    <img src="uploaded_img/<?php echo $product['image']; ?>" alt="Product Image">
                    <div class="product-desc">
                        <a href="#" class="title-prod"><?php echo $product['name']; ?></a>
                        
                        <div class="price" style="text-align: center;">
                            <span>₱<?php echo $product['price']; ?></span>
                        </div>

                        <div class="stock" style="text-align: center;">
                            <p>Stock: <?php echo $product['stock']; ?></p>
                        </div>

                     
                        <div class="icon-product" style="text-align: center;">
                            <a href="javascript:void(0)" onclick="likeProduct(<?php echo $product['id']; ?>)">
                                <i class="fa-regular fa-heart"></i>
                            </a>

                            <?php if ($product['stock'] > 0) { ?>
                                <a href="javascript:void(0)" onclick="openModal(<?php echo $product['id']; ?>)">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </a>
                            <?php } else { ?>
                                <p style="color: red;">Out of Stock</p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

       

        </div>
    </section>
   
    <div id="cartModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            
            <h3 id="modalProductName"></h3>
            <p id="modalProductPrice"></p>
            <p id="totalPrice">Total: ₱<span id="totalPriceValue"></span></p>
            <input type="number" id="quantityInput" value="1" min="1" oninput="updateTotalPrice()">
            <br>
            <button onclick="addToCart()">Add to Cart</button>
        </div>
    </div>

    <div id="feedback">Product added to cart</div>
    <div id="likeMessage">Product added to cart</div>
    <script>
        
        // likeProduct Function
    function likeProduct(productId) {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'liked_product.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
            if (xhr.status === 200) {
                const likeMessageDiv = document.getElementById('likeMessage');
                likeMessageDiv.innerText = xhr.responseText;
                likeMessageDiv.classList.add('show');

                // Debug: Log the response to the console
                console.log('Response:', xhr.responseText);

                // Remove the message after 3 seconds
                setTimeout(() => {
                    likeMessageDiv.classList.remove('show');
                    likeMessageDiv.innerText = '';
                }, 3000);
            }
        };

        xhr.onerror = function () {
            console.error('An error occurred during the AJAX request.');
        };

        // Send the product ID to the server
        xhr.send('product_id=' + productId);
    }
        // Other existing modal and cart functions
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
        
                <a href="homepage.php"><img src="img/logo.png" alt="logo"></a>
                
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