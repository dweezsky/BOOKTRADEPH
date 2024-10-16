<?php
session_start();
@include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch liked products with product details
$liked_products = mysqli_query($conn, "
    SELECT products.* FROM products
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
            <p id="totalPrice">Total: ₱<span id="totalPriceValue"></span></p>
            <input type="number" id="quantityInput" value="1" min="1" oninput="updateTotalPrice()">
            <br>
            <button onclick="addToCart()">Add to Cart</button>
        </div>
    </div>

    <div id="feedback">Product added to cart</div>

    <script>
        function unlikeProduct(product_id) {
            // Send an AJAX request to remove the product from the liked products in the database
            let xhr = new XMLHttpRequest();
            xhr.open('POST', 'unlike_product.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = xhr.responseText.trim();
                    console.log("Response from server:", response);
                    if (response === "Product unliked.") {
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert(response); // Handle other responses or errors
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
    </script>
</body>
</html>