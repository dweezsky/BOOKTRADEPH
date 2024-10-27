<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

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
</script>

</body>
</html>