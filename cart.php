<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <a href="cart.php" class="active">Cart</a>
            </div>
        </nav>
    </header>

    <section class="cart-section">
        <div class="container">
            <h2>Your Cart</h2>
            <table class="cart-table">
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
                
                </tbody>
            </table>

            <div class="cart-total">
                <h3>Total Amount: ₱<span id="totalAmount">0</span></h3>
                <button class="btn" onclick="checkout()">Proceed to Checkout</button>
            </div>
        </div>
    </section>

    <script>
        const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
        const cartItemsContainer = document.getElementById('cartItems');
        const totalAmountElement = document.getElementById('totalAmount');

        function displayCartItems() {
            cartItemsContainer.innerHTML = ''; 
            let totalAmount = 0;

            cartItems.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;

                cartItemsContainer.innerHTML += `
                    <tr>
                        <td>
                            <input type="checkbox" class="select-item" onchange="calculateTotal()" data-price="${itemTotal}">
                        </td>
                        <td><img src="${item.image}" style="width: 100px;"></td>
                        <td>${item.name}</td>
                        <td>₱${item.price}</td>
                        <td>
                            <input type="number" value="${item.quantity}" min="1" onchange="updateQuantity(${index}, this.value)">
                        </td>
                        <td>₱${itemTotal.toFixed(2)}</td>
                        <td>
                            <button class="btn" onclick="removeFromCart(${index})">Remove</button>
                        </td>
                    </tr>
                `;
            });

    
            totalAmountElement.textContent = totalAmount.toFixed(2);
        }

        function updateQuantity(index, quantity) {
            if (quantity < 1) return;

            cartItems[index].quantity = parseInt(quantity);
            localStorage.setItem('cart', JSON.stringify(cartItems));
            displayCartItems(); 
        }

     
        function removeFromCart(index) {
            cartItems.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cartItems));
            displayCartItems(); 
        }

        function calculateTotal() {
            const selectedItems = document.querySelectorAll('.select-item:checked');
            let total = 0;

            selectedItems.forEach(item => {
                total += parseFloat(item.getAttribute('data-price'));
            });

            totalAmountElement.textContent = total.toFixed(2);
        }

        function checkout() {
            alert('Proceeding to checkout...');
        }

    
        displayCartItems();
    </script>

    <style>
        .cart-section {
            padding: 50px;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .cart-table th, .cart-table td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .cart-table th {
            background-color: #a77d54;
            color: white;
        }

        .cart-table td input[type="number"] {
            width: 60px;
            padding: 5px;
            text-align: center;
        }

        .cart-total {
            text-align: right;
            margin-top: 20px;
        }

        .cart-total h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .btn {
            padding: 10px 20px;
            background-color: #a77d54;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #855b3a;
        }
    </style>
</body>
</html>