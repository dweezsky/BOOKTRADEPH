<?php

@include 'config.php';

session_start(); 


if (!isset($_SESSION['user_id'])) {
    header('Location: index(2).html'); 
}


$user_id = $_SESSION['user_id'];
$select_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");

if (mysqli_num_rows($select_user) > 0) {
    $user_data = mysqli_fetch_assoc($select_user);
    $first_name = $user_data['first_name'];
    $last_name = $user_data['last_name'];
    $email = $user_data['email'];
} else {
    echo "User not found!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - BookTrade PH</title>

    <link rel="stylesheet" href="homepage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <header>
    <nav>
            <div class="logo">
                <a href="/"><img src="img/logo.png" alt="logo"></a>
            </div>
            <div class="nav-links">
                <a href="homepage.php">Home</a>
                <a href="my_purchases.php" >My Purchases</a>
                <a href="about.html">About</a>
                <a href="contact.html">Contact</a>
                <a href="account.php" class="active">Account</a>
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
   <style>
  
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0;
        }

        .account-container {
            width: 100%;
            max-width: 600px;
            padding: 50px 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .account-container h3 {
            margin-bottom: 30px;
            font-size: 24px;
        }

        .account-details p {
            font-size: 18px;
            margin-bottom: 15px;
        }

        .logout-btn {
            display: block;
            margin: 30px auto;
            padding: 10px 20px;
            background-color: #a77d54;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #855b3a;
        }
    </style>
</head>
<body>

<div class="account-container">
    <h3>Account Details</h3>
    <div class="account-details">
        <p><strong>First Name:</strong> <?php echo $first_name; ?></p>
        <p><strong>Last Name:</strong> <?php echo $last_name; ?></p>
        <p><strong>Email:</strong> <?php echo $email; ?></p>
        <p><strong>Total Liked Products:</strong> <span id="totalLikedProducts">0</span></p>
    </div>
    <form method="POST">
        <button type="submit" name="logout" class="logout-btn">Logout</button>
    </form>
</div>

<?php
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index(2).html');
    exit();
}
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let likedProducts = JSON.parse(localStorage.getItem('liked')) || [];
    document.getElementById('totalLikedProducts').innerText = likedProducts.length;
});
</script>

</body>
</html>