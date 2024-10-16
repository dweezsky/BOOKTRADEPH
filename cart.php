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
                $totalAmount = 0;
                if (mysqli_num_rows($cart_items) > 0) {
                    while ($item = mysqli_fetch_assoc($cart_items)) {
                        $item_total = $item['price'] * $item['quantity'];
                        $totalAmount += $item_total;
                ?>
                    <tr>
                        <td><input type="checkbox" class="select-item" onchange="updateTotal()" data-price="<?php echo $item_total; ?>"></td>
                        <td><img src="uploaded_img/<?php echo $item['image']; ?>" style="width: 100px;"></td>
                        <td><?php echo $item['name']; ?></td>
                        <td class="price">₱<?php echo number_format($item['price'], 2); ?></td>
                        <td><input type="number" value="<?php echo $item['quantity']; ?>" min="1" onchange="updateQuantity(<?php echo $item['id']; ?>, this.value)"></td>
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
            <h3>Total Amount: ₱<span id="totalAmount"><?php echo number_format($totalAmount, 2); ?></span></h3>
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
    function updateQuantity(productId, quantity) {
        if (quantity < 1) return;

        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_cart_quantity.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                updateTotal();
            }
        };
        xhr.send('product_id=' + productId + '&quantity=' + quantity);
    }

    function removeFromCart(productId) {
        let xhr = new XMLHttpRequest();
        xhr.open('POST', 'remove_from_cart.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                location.reload();
            }
        };
        xhr.send('product_id=' + productId);
    }

    function updateTotal() {
        let total = 0;
        const rows = document.querySelectorAll('#cartItems tr');

        rows.forEach(row => {
            const price = parseFloat(row.querySelector('.price').textContent.replace('₱', ''));
            const quantity = parseInt(row.querySelector('input[type="number"]').value);
            const itemTotal = price * quantity;
            row.querySelector('.item-total').textContent = `₱${itemTotal.toFixed(2)}`;
            total += itemTotal;
        });

        document.getElementById('totalAmount').textContent = total.toFixed(2);
        document.getElementById('checkoutTotalAmount').textContent = total.toFixed(2);
    }

    function checkout() {
        document.getElementById('checkoutModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('checkoutModal').style.display = 'none';
    }

    function confirmCheckout() {
        const address = document.getElementById('addressInput').value;

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
        xhr.send('address=' + encodeURIComponent(address));
    }
</script>

</body>
</html>