<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liked Products - BookTrade PH</title>

    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

        .liked-products-section {
            padding: 50px 0;
        }

        .liked-products-section h3 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 30px;
        }

        .liked-products {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
        }

        .liked-products .liked-item {
            width: 250px; 
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px; 
            text-align: center;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .liked-products .liked-item img {
            width: 150px; 
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .liked-products .liked-item .price {
            font-size: 20px; 
            margin: 10px 0;
        }

        .liked-products .liked-item .icon-product {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .liked-products .liked-item .icon-product i {
            font-size: 22px; 
            cursor: pointer;
        }

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
            width: 400px;
            text-align: center;
            position: relative;
        }

        .modal-content img {
            width: 150px;
            height: 150px;
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
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <a href="/"><img src="img/logo.png" alt="logo"></a>
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
                <a href="account.php">Account</a>
                <a href="cart.php">Cart</a>
            </div>

            <form class="search-container">
                <input type="text" id="search-bar" placeholder="Search">
                <a href="#"><img class="search-icon" src="http://www.endlessicons.com/wp-content/uploads/2012/12/search-icon.png"></a>
            </form>

            <div class="right-nav">
                <a href="liked_product.php"><i class="fa-solid fa-heart"></i></a>
                <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
            </div>
        </nav>
    </header>

    <section class="liked-products-section">
        <h3>Your Liked Products</h3>
        <div class="liked-products" id="likedProductsList"></div>
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
        let selectedProduct = {};

        function loadLikedProducts() {
            let likedProducts = JSON.parse(localStorage.getItem('liked')) || [];
            let likedProductsList = document.getElementById('likedProductsList');
            likedProductsList.innerHTML = '';

            likedProducts.forEach(product => {
                likedProductsList.innerHTML += `
                    <div class="liked-item">
                        <img src="${product.image}" alt="${product.name}">
                        <p>${product.name}</p>
                        <div class="price">₱${product.price}</div>
                        <div class="icon-product">
                            <!-- Cart Icon -->
                            <i class="fa-solid fa-cart-shopping" onclick="openModal(${product.id}, '${product.name}', '${product.price}', '${product.image}', 10)"></i>
                            <!-- Solid Heart Icon for unliking -->
                            <i class="fa-solid fa-heart" onclick="unlikeProduct(${product.id})"></i>
                        </div>
                    </div>
                `;
            });
        }

        function unlikeProduct(productId) {
            let likedProducts = JSON.parse(localStorage.getItem('liked')) || [];
            likedProducts = likedProducts.filter(product => product.id !== productId); // Ensure we're filtering by ID
            localStorage.setItem('liked', JSON.stringify(likedProducts));

            loadLikedProducts();
        }

        function openModal(id, name, price, image, stock) {
            selectedProduct = { id, name, price, image, stock };


            document.getElementById('modalImage').src = image;
            document.getElementById('modalProductName').innerText = name;
            document.getElementById('modalProductPrice').innerText = "₱" + price;
            document.getElementById('totalPriceValue').innerText = price;
            document.getElementById('quantityInput').max = stock; 

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

            let cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            let existingProductIndex = cartItems.findIndex(item => item.id === selectedProduct.id);

            if (existingProductIndex !== -1) {
                let totalQuantity = cartItems[existingProductIndex].quantity + quantity;
                if (totalQuantity > selectedProduct.stock) {
                    alert("You can't add more than the available stock.");
                    return;
                }
                cartItems[existingProductIndex].quantity = totalQuantity;
            } else {
                cartItems.push({
                    id: selectedProduct.id,
                    name: selectedProduct.name,
                    price: selectedProduct.price,
                    quantity: quantity,
                    image: selectedProduct.image
                });
            }

            localStorage.setItem('cart', JSON.stringify(cartItems));
            document.getElementById('cartModal').style.display = 'none';
            showFeedback();
        }

        function showFeedback() {
            let feedback = document.getElementById('feedback');
            feedback.style.display = 'block';
            setTimeout(() => {
                feedback.style.display = 'none';
            }, 2000);
        }

        function closeModal() {
            document.getElementById('cartModal').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', loadLikedProducts);
    </script>
</body>
</html>