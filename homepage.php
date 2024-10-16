<?php
// Include the database connection
@include 'config.php';

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

$user_id = $_SESSION['user_id'];

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
        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 350px;
            text-align: center;
            position: relative;
        }

        .modal-content img {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
        }

        .modal-content h3, .modal-content p {
            margin-bottom: 10px;
        }

        .modal-content input {
            width: 80px;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .modal-content button {
            padding: 10px 20px;
            background-color: #a77d54;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal-content button:hover {
            background-color: #855b3a;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 20px;
            cursor: pointer;
            color: #888;
            font-size: 18px;
        }

        .close-btn:hover {
            color: #000;
        }

        /* Feedback Notification */
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

    <section class="products">
        <div class="contnaier">
            <div class="top-sec">
                <h3>New Products</h3>
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
        // likeProduct Function
        function likeProduct(productId) {
            let xhr = new XMLHttpRequest();
            xhr.open('POST', 'like_product.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    if (xhr.responseText === 'liked') {
                        alert('Product added to your liked list.');
                    } else if (xhr.responseText === 'already_liked') {
                        alert('This product is already in your liked list.');
                    } else {
                        alert('Error: Could not process your request.');
                    }
                }
            };
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
    </script>
</body>
</html>