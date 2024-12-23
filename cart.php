<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: homepage.php'); // Redirect to login page if not logged in
    exit;
}

// Fetch the user's cart and liked products from the database
$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT first_name, last_name, email FROM users WHERE id = '$user_id'");

if ($user_query && mysqli_num_rows($user_query) > 0) {
    $user = mysqli_fetch_assoc($user_query);
    $user['name'] = $user['first_name'] . ' ' . $user['last_name']; // Combine first and last names
} else {
    $user = ['name' => 'Unknown', 'email' => 'Not available']; // Fallback values
}
// Fetch products from the database
$select_products = mysqli_query($conn, "SELECT * FROM products");
// Fetch the user's cart and liked products from the database



$user_query = mysqli_query($conn, "SELECT first_name, last_name FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_query);

$cart_items = mysqli_query($conn, "
    SELECT products.*, user_cart.quantity 
    FROM products
    INNER JOIN user_cart ON products.id = user_cart.product_id
    WHERE user_cart.user_id = '$user_id'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="cart.css">
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

<section class="cart-section" style="padding: 50px; background-color: #f9f9f9;">
    <div class="container" style="max-width: 1000px; margin: auto;">
        <h2>Your Cart</h2>
        <table class="cart-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
    <thead>
        <tr>
            <th>Select</th>
            <th>Product Image</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="cartItems">
        <?php
        if (mysqli_num_rows($cart_items) > 0) {
            while ($item = mysqli_fetch_assoc($cart_items)) {
                $item_total = $item['price'] * $item['quantity'];
        ?>
        <tr data-id="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>">
            <td><input type="checkbox" class="select-item" onchange="updateSelectedTotal()"></td>
            <td><img src="uploaded_img/<?php echo $item['image']; ?>" style="width: 100px;"></td>
            <td><?php echo $item['name']; ?></td>
            <td class="price">₱<?php echo number_format($item['price'], 2); ?></td>
            <td>
    <input 
        type="number" 
        value="<?php echo $item['quantity']; ?>" 
        min="1" 
        onchange="updateQuantity(<?php echo $item['id']; ?>, this)">
</td>
            <td class="item-total">₱<?php echo number_format($item_total, 2); ?></td>
            <td><button class="btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">Remove</button></td>
        </tr>
        <?php
            }
        } else {
        ?>
        <tr>
            <td colspan="7">Your cart is empty</td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<div class="cart-total" style="text-align: right; margin-top: 20px;">
    <h3>Total Amount: ₱<span id="selectedTotalAmount">0.00</span></h3>
    <button class="btn" onclick="checkout()">Proceed to Checkout</button>
</div>
    </div>
</section>
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
<div id="checkoutModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center;">
    <div class="modal-content" style="background-color: #fff; padding: 20px; border-radius: 8px; width: 400px; text-align: center; position: relative;">
        <span class="close-btn" onclick="closeModal()" style="position: absolute; top: 10px; right: 20px; cursor: pointer; color: #888; font-size: 18px;">&times;</span>
        <h3>Confirm Checkout</h3>
        <p>Total Amount: ₱<span id="checkoutTotalAmount"><?php echo number_format($totalAmount, 2); ?></span></p>
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="addressInput">Delivery Address:</label>
            <textarea id="addressInput" placeholder="Enter your delivery address" style="width: 100%; padding: 10px; font-size: 14px; border: 1px solid #ddd; border-radius: 5px; resize: none;" required></textarea>
        </div>
        <button class="btn" onclick="confirmCheckout()">Confirm</button>
        <button class="btn" onclick="closeModal()">Cancel</button>
    </div>
</div>

<script>
function updateQuantity(productId, quantityInput) {
    const row = quantityInput.closest('tr'); // Get the row of the product
    const price = parseFloat(row.dataset.price); // Get the product price from the row's data attribute
    const quantity = parseInt(quantityInput.value); // Get the new quantity from the input
    const itemTotal = price * quantity; // Calculate the new item total

    // Update the item total display in the table
    row.querySelector('.item-total').textContent = `₱${itemTotal.toFixed(2)}`;

    // Update the quantity in the backend via AJAX
    let xhr = new XMLHttpRequest();
    xhr.open('POST', 'update_cart_quantity.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            updateSelectedTotal(); // Recalculate the total amount for selected items
        }
    };
    xhr.send('product_id=' + productId + '&quantity=' + quantity);
}

function updateSelectedTotal() {
    let total = 0;
    const rows = document.querySelectorAll('#cartItems tr'); // Select all rows in the cart

    // Loop through each row and calculate the total for selected items
    rows.forEach(row => {
        const checkbox = row.querySelector('.select-item'); // Get the checkbox in the row
        const price = parseFloat(row.dataset.price); // Get the price from the row's data attribute
        const quantity = parseInt(row.querySelector('input[type="number"]').value); // Get the quantity from the input field

        if (checkbox.checked) { // Only sum totals for selected items
            total += price * quantity;
        }
    });

    // Update the total amount display
    document.getElementById('selectedTotalAmount').textContent = total.toFixed(2);
    document.getElementById('checkoutTotalAmount').textContent = total.toFixed(2);
}

function checkout() {
    const selectedItems = Array.from(document.querySelectorAll('.select-item:checked')).map(item => {
        const row = item.closest('tr');
        const productId = row.dataset.id;
        const quantity = row.querySelector('input[type="number"]').value;

        return { id: productId, quantity: parseInt(quantity) }; // Collect product ID and quantity
    });

    if (selectedItems.length === 0) {
        alert('Please select at least one item to proceed to checkout.');
        return;
    }

    document.getElementById('checkoutModal').style.display = 'flex';
    document.getElementById('checkoutTotalAmount').textContent = document.getElementById('selectedTotalAmount').textContent;

    // Store selected items for checkout
    sessionStorage.setItem('selectedItems', JSON.stringify(selectedItems));
}
function confirmCheckout() {
    const address = document.getElementById('addressInput').value;
    const selectedItems = JSON.parse(sessionStorage.getItem('selectedItems'));

    if (!address.trim()) {
        alert('Please enter a delivery address.');
        return;
    }

    let xhr = new XMLHttpRequest();
    xhr.open('POST', 'process_checkout.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            alert('Checkout successful!');
            location.href = 'order_confirmation.php';
        } else {
            alert('Error processing your request.');
        }
    };

    xhr.send('address=' + encodeURIComponent(address) + '&items=' + encodeURIComponent(JSON.stringify(selectedItems)));
}

function closeModal() {
    document.getElementById('checkoutModal').style.display = 'none';
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
function removeFromCart(productId) {
    let xhr = new XMLHttpRequest();
    xhr.open('POST', 'remove_from_cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
        if (xhr.status === 200) {
            const row = document.querySelector(`tr[data-id="${productId}"]`);
            if (row) {
                row.remove(); // Remove the row from the table
            }
            updateSelectedTotal(); // Recalculate the total amount
        } else {
            alert('Failed to remove the item from the cart.');
        }
    };
    xhr.send('product_id=' + productId);
}
</script>

</body>
</html>