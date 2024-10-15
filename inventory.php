<?php

@include 'config.php';

if (isset($_POST['update_stock'])) {
    $id = $_POST['product_id'];
    $product_stock = $_POST['product_stock'];

    if (empty($product_stock)) {
        $message[] = 'Please enter a stock quantity!';
    } else {
        $update_stock = "UPDATE products SET stock=stock + '$product_stock' WHERE id='$id'";
        if (mysqli_query($conn, $update_stock)) {
            $message[] = 'Stock updated successfully!';
        } else {
            $message[] = 'Error updating stock: ' . mysqli_error($conn);
        }
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM products WHERE id = $id");
    header('location:inventory_management.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Inventory Management</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600&display=swap');

        :root{
            --green:#27ae60;
            --black:#333;
            --white:#fff;
            --bg-color:#eee;
            --box-shadow:0 .5rem 1rem rgba(0,0,0,.1);
            --border:.1rem solid var(--black);
        }

        *{
            font-family: 'Poppins', sans-serif;
            margin:0; padding:0;
            box-sizing: border-box;
            outline: none; border:none;
            text-decoration: none;
            text-transform: capitalize;
        }

        html{
            font-size: 62.5%;
            overflow-x: hidden;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: #a77d54;
            border-bottom: 1px solid #94949450;
            box-shadow: 0px 0px 8px #44444441;
            z-index: 100;
        }

        header nav {
            width: 85%;
            margin: auto;
            height: 100px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header nav .logo {
            color: #ffffff;
            font-size: 40px;
            font-weight: 800;
            text-transform: uppercase;
        }

        header nav .nav-links a {
            color: #ffffff;
            font-weight: 600;
            letter-spacing: 1.2px;
            font-size: 18px;
            margin: 0 35px;
            text-transform: capitalize;
        }

        header nav .nav-links a:hover {
            color: #c9c5c2;
        }

        .btn {
            display: block;
            width: 100%;
            cursor: pointer;
            border-radius: .5rem;
            margin-top: 1rem;
            font-size: 1.7rem;
            padding: 1rem 3rem;
            background: #a77d54;
            color: var(--white);
            text-align: center;
        }

        .btn:hover {
            background: var(--black);
        }

        .message {
            display: block;
            margin-top: 5%;
            background: var(--bg-color);
            padding: 1.5rem 1rem;
            font-size: 2rem;
            color: var(--black);
            margin-bottom: 2rem;
            text-align: center;
        }

        .container {
            max-width: 1200px;
            padding: 2rem;
            margin: 0 auto;
            margin-top: 7%;
        }

        .admin-product-form-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .admin-product-form-container form {
            max-width: 50rem;
            margin: 0 auto;
            padding: 2rem;
            border-radius: .5rem;
            background: var(--bg-color);
        }

        .admin-product-form-container form h3 {
            text-transform: uppercase;
            color: var(--black);
            margin-bottom: 1rem;
            text-align: center;
            font-size: 2.5rem;
        }

        .admin-product-form-container form .box {
            width: 100%;
            border-radius: .5rem;
            padding: 1.2rem 1.5rem;
            font-size: 1.7rem;
            margin: 1rem 0;
            background: var(--white);
            text-transform: none;
        }

        .product-display {
            margin: 2rem 0;
        }

        .product-display .product-display-table {
            width: 100%;
            text-align: center;
        }

        .product-display .product-display-table thead {
            background: var(--bg-color);
        }

        .product-display .product-display-table th {
            padding: 1rem;
            font-size: 2rem;
        }

        .product-display .product-display-table td {
            padding: 1rem;
            font-size: 2rem;
            border-bottom: var(--border);
        }

        .product-display .product-display-table .btn:first-child {
            margin-top: 0;
        }

        .product-display .product-display-table .btn:last-child {
            background: crimson;
        }

        .product-display .product-display-table .btn:last-child:hover {
            background: var(--black);
        }

        @media (max-width: 991px) {
            html {
                font-size: 55%;
            }
        }

        @media (max-width: 768px) {
            .product-display {
                overflow-y: scroll;
            }

            .product-display .product-display-table {
                width: 80rem;
            }
        }

        @media (max-width: 450px) {
            html {
                font-size: 50%;
            }
        }
    </style>
</head>
<body>

<header>
<nav>

<div class="nav-links">
    <a href="admin_page.php" >BOOK MANAGEMENT</a>
    <a href="">TRANSACTION MANAGEMENT</a>
    <a href="inventory.php" class="active">INVENTORY MANAGEMENT</a>
    <a href="user_management.php">USER MANAGEMENT</a>
</div>
</nav>
</header>

<div class="container">
    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '<span class="message">' . $message . '</span>';
        }
    }
    ?>

    <div class="product-display">
        <h3>Product Inventory</h3>
        <table class="product-display-table">
            <thead>
            <tr>
                <th>Product Image</th>
                <th>Product Name</th>
                <th>Product Price</th>
                <th>Stock Levels</th>
                <th>Update Stock</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $select = mysqli_query($conn, "SELECT * FROM products");
            while ($row = mysqli_fetch_assoc($select)) {
                $low_stock_warning = $row['stock'] < 5 ? "<span style='color: red;'>Low Stock!</span>" : "";
                ?>
                <tr>
                    <td><img src="uploaded_img/<?php echo $row['image']; ?>" height="100" alt=""></td>
                    <td><?php echo $row['name']; ?></td>
                    <td>₱<?php echo $row['price']; ?></td>
                    <td>
                        <?php echo $row['stock']; ?> <?php echo $low_stock_warning; ?>
                    </td>
                    <td>
                        <form action="" method="post" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="number" name="product_stock" placeholder="Add Stock" min="1" required>
                            <input type="submit" name="update_stock" value="Update" class="btn">
                        </form>
                    </td>
                    <td>
                        <a href="inventory_management.php?delete=<?php echo $row['id']; ?>" class="btn">Delete</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>


    <h4>Out of Stock Books</h4>
    <div class="product-display">
        <table class="product-display-table">
            <thead>
            <tr>
                <th>Product Name</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $out_of_stock_select = mysqli_query($conn, "SELECT * FROM products WHERE stock = 0");
            while ($row = mysqli_fetch_assoc($out_of_stock_select)) {
                ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td>
                        <a href="inventory_management.php?delete=<?php echo $row['id']; ?>" class="btn">Delete</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>