<?php
session_start();
@include 'config.php';

if (isset($_POST['update_stock'])) {
    $id = $_POST['product_id'];
    $product_stock = $_POST['product_stock'];

    if (empty($product_stock)) {
        $message[] = 'Please enter a stock quantity!';
    } else {
        $update_stock = "UPDATE products SET stock = stock + '$product_stock' WHERE id='$id'";
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
    exit;
}


if (isset($_POST['checkout'])) {
    $user_id = $_SESSION['user_id'];


    $cart_query = mysqli_query($conn, "
        SELECT product_id, quantity 
        FROM user_cart 
        WHERE user_id = '$user_id'
    ");

    $is_stock_sufficient = true;

    while ($cart_item = mysqli_fetch_assoc($cart_query)) {
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];

        $product_query = mysqli_query($conn, "SELECT stock FROM products WHERE id = '$product_id'");
        $product = mysqli_fetch_assoc($product_query);

        if ($product['stock'] < $quantity) {
            $is_stock_sufficient = false;
            $message[] = "Insufficient stock for product ID: $product_id.";
            break;
        }
    }

    if ($is_stock_sufficient) {

        mysqli_data_seek($cart_query, 0);

        while ($cart_item = mysqli_fetch_assoc($cart_query)) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];

            mysqli_query($conn, "
                UPDATE products 
                SET stock = stock - '$quantity' 
                WHERE id = '$product_id'
            ");
        }

        $address = mysqli_real_escape_string($conn, $_POST['address']);
        mysqli_query($conn, "
            INSERT INTO orders (user_id, address, status, created_at) 
            VALUES ('$user_id', '$address', 'To Ship', NOW())
        ");

        $order_id = mysqli_insert_id($conn);

        mysqli_data_seek($cart_query, 0);
        while ($cart_item = mysqli_fetch_assoc($cart_query)) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];

            mysqli_query($conn, "
                INSERT INTO order_items (order_id, product_id, quantity, price) 
                SELECT '$order_id', product_id, quantity, price 
                FROM user_cart 
                WHERE product_id = '$product_id' AND user_id = '$user_id'
            ");
        }

        mysqli_query($conn, "DELETE FROM user_cart WHERE user_id = '$user_id'");
        $message[] = 'Order placed successfully!';
    }
}

$products = mysqli_query($conn, "SELECT * FROM products");
$out_of_stock_products = mysqli_query($conn, "SELECT * FROM products WHERE stock = 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="inventory.css">
    <title>Inventory Management</title>
    <style>
        .container { padding: 20px; }
        .product-display-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .product-display-table th, .product-display-table td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        .product-display-table th { background-color: #a77d54; color: white; }
        .btn { padding: 5px 10px; background-color: #a77d54; color: white; border: none; cursor: pointer; }
        .btn:hover { background-color: #855b3a; }
    </style>
</head>
<body>
<header>
    <nav>
        <div class="nav-links">
            <a href="admin_page.php">BOOK MANAGEMENT</a>
            <a href="transaction_management.php">TRANSACTION MANAGEMENT</a>
            <a href="inventory.php" class="active">INVENTORY MANAGEMENT</a>
            <a href="user_management.php">USER MANAGEMENT</a>
        </div>
    </nav>
</header>

<div class="container">
    <?php if (isset($message)) {
        foreach ($message as $msg) {
            echo '<span class="message">' . $msg . '</span>';
        }
    } ?>

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
            <?php while ($row = mysqli_fetch_assoc($products)) { ?>
                <tr>
                    <td><img src="uploaded_img/<?php echo $row['image']; ?>" height="100"></td>
                    <td><?php echo $row['name']; ?></td>
                    <td>₱<?php echo $row['price']; ?></td>
                    <td><?php echo $row['stock']; ?></td>
                    <td>
                        <form action="" method="post" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="number" name="product_stock" placeholder="Add Stock" min="1" required>
                            <input type="submit" name="update_stock" value="Update" class="btn">
                        </form>
                    </td>
                    <td><a href="inventory_management.php?delete=<?php echo $row['id']; ?>" class="btn">Delete</a></td>
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
            <?php while ($row = mysqli_fetch_assoc($out_of_stock_products)) { ?>
                <tr>
                    <td><?php echo $row['name']; ?></td>
                    <td><a href="inventory_management.php?delete=<?php echo $row['id']; ?>" class="btn">Delete</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>