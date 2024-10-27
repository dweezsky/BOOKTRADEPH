<?php
@include 'config.php';
session_start();

$id = $_GET['edit'];

if (isset($_POST['update_product'])) {
    // Escape special characters to prevent SQL syntax errors and SQL injection
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = mysqli_real_escape_string($conn, $_POST['product_price']);
    $product_stock = mysqli_real_escape_string($conn, $_POST['product_stock']);
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $product_image = $_FILES['product_image']['name'];
    $product_image_tmp_name = $_FILES['product_image']['tmp_name'];
    $product_image_folder = 'uploaded_img/' . $product_image;

    if (empty($product_name) || empty($product_price) || empty($product_stock) || empty($product_description)) {
        $message[] = 'Please fill out all fields!';
    } else {
        if (!empty($product_image)) {
            $update_data = "
                UPDATE products 
                SET name='$product_name', 
                    price='$product_price', 
                    stock='$product_stock', 
                    description='$product_description', 
                    image='$product_image' 
                WHERE id='$id'";
                
            $upload = mysqli_query($conn, $update_data);

            if ($upload) {
                move_uploaded_file($product_image_tmp_name, $product_image_folder);
                header('location:admin_page.php');
            } else {
                $message[] = 'Could not update the product!';
            }
        } else {
            $update_data = "
                UPDATE products 
                SET name='$product_name', 
                    price='$product_price', 
                    stock='$product_stock', 
                    description='$product_description' 
                WHERE id='$id'";
                
            $upload = mysqli_query($conn, $update_data);

            if ($upload) {
                header('location:admin_page.php');
            } else {
                $message[] = 'Could not update the product!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<header>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <link rel="stylesheet" href="adminstyle.css">

<body>

<nav>

<div class="nav-links">
        <a href="admin_page.php" class="active">BOOK MANAGEMENT</a>
        <a href="transaction_management.php">TRANSACTION MANAGEMENT</a>
        <a href="inventory.php">INVENTORY MANAGEMENT</a>
        <a href="user_management.php">USER MANAGEMENT</a>
        <a href="javascript:void(0);" onclick="openNotificationModal()">
            <i class="fa-regular fa-bell"></i>
        </a>
        <a href="javascript:void(0);" onclick="openProfileModal()">
                    <i class="fa-regular fa-user"></i>
                </a>
    </div>
</nav>
</header>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 400px;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .title {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .product-image {
            display: block;
            width: 100%;
            max-width: 200px;
            height: auto;
            margin: 0 auto 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .box {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .btn {
            width: 48%;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s;
        }
        .btn-green {
            background-color: #4CAF50;
            color: white;
        }
        .btn-green:hover {
            background-color: #45a049;
        }
        .btn-red {
            background-color: #f44336;
            color: white;
            text-decoration: none;
            line-height: 2.2rem;
        }
        .btn-red:hover {
            background-color: #d32f2f;
        }
        .message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '<span class="message">' . $msg . '</span>';
    }
}
?>

<div class="container">
    <h3 class="title">Update Product</h3>

    <?php
    $select = mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'");
    while ($row = mysqli_fetch_assoc($select)) {
    ?>
        <!-- Product Image -->
        <img src="uploaded_img/<?php echo $row['image']; ?>" class="product-image" alt="Product Image">

        <form action="" method="post" enctype="multipart/form-data">
            <input 
                type="text" 
                class="box" 
                name="product_name" 
                value="<?php echo $row['name']; ?>" 
                placeholder="Enter the product name"
                required
            >
            <input 
                type="number" 
                min="0" 
                class="box" 
                name="product_price" 
                value="<?php echo $row['price']; ?>" 
                placeholder="Enter the product price" 
                required
            >
            <input 
                type="number" 
                min="0" 
                class="box" 
                name="product_stock" 
                value="<?php echo $row['stock']; ?>" 
                placeholder="Enter the stock quantity" 
                required
            >
            <textarea 
                class="box" 
                name="product_description" 
                placeholder="Enter a brief description"
                rows="4" 
                required
            ><?php echo $row['description']; ?></textarea>

            <input 
                type="file" 
                class="box" 
                name="product_image" 
                accept="image/png, image/jpeg, image/jpg"
            >

            <div class="btn-container">
                <input type="submit" value="Update Product" name="update_product" class="btn btn-green">
                <a href="admin_page.php" class="btn btn-red">Go Back</a>
            </div>
        </form>
    <?php } ?>
</div>

</body>
</html>