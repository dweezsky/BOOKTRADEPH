<?php

@include 'config.php';

session_start();

if (isset($_POST['add_product'])) {

    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_stock = $_POST['product_stock']; 
    $product_image = $_FILES['product_image']['name'];
    $product_image_tmp_name = $_FILES['product_image']['tmp_name'];
    $product_image_folder = 'uploaded_img/' . $product_image;

    if (!file_exists('uploaded_img')) {
        mkdir('uploaded_img', 0777, true);
    }

    if (empty($product_name) || empty($product_price) || empty($product_stock) || empty($product_image)) {
        $message[] = 'Please fill out all fields';
    } else {

        $insert = "INSERT INTO products(name, price, stock, image) VALUES('$product_name', '$product_price', '$product_stock', '$product_image')";
        $upload = mysqli_query($conn, $insert);

  
        if ($upload) {
            if ($_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        
                if (move_uploaded_file($product_image_tmp_name, $product_image_folder)) {
                    $message[] = 'New product added successfully';
                } else {
                    $message[] = 'Could not move the uploaded file';
                }
            } else {
                $message[] = 'Error during file upload';
            }
        } else {
            $message[] = 'Could not add the product to the database';
        }
    }
};

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM products WHERE id = $id");
    header('location:admin_page.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<header>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


    <link rel="stylesheet" href="adminstyle.css">

<body>

<nav>

    <div class="nav-links">
        <a href="admin_page.php" class="active">BOOK MANAGEMENT</a>
        <a href="transaction_management.php">TRANSACTION MANAGEMENT</a>
        <a href="inventory.php">INVENTORY MANAGEMENT</a>
        <a href="user_management.php">USER MANAGEMENT</a>
    </div>
</nav>
</header>
<?php
if (isset($message)) {
    foreach ($message as $message) {
        echo '<span class="message">' . $message . '</span>';
    }
}
?>

<div class="container">
    <div class="admin-product-form-container">
        <form action="<?php $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
            <h3>Add a New Product</h3>
            <input type="text" placeholder="Enter product name" name="product_name" class="box" required>
            <input type="number" placeholder="Enter product price" name="product_price" class="box" required>
            <input type="number" placeholder="Enter product stock" name="product_stock" class="box" required> <!-- Stock input -->
            <input type="file" accept="image/png, image/jpeg, image/jpg" name="product_image" class="box" required>
            <input type="submit" class="btn" name="add_product" value="Add Product">
        </form>
    </div>

    <?php
    $select = mysqli_query($conn, "SELECT * FROM products");
    ?>
    <div class="product-display">
        <table class="product-display-table">
            <thead>
            <tr>
                <th>Product Image</th>
                <th>Product Name</th>
                <th>Product Price</th>
                <th>Product Stock</th>
                <th>Action</th>
            </tr>
            </thead>
            <?php while ($row = mysqli_fetch_assoc($select)) { ?>
            <tr>
                <td><img src="uploaded_img/<?php echo $row['image']; ?>" height="100" alt=""></td>
                <td><?php echo $row['name']; ?></td>
                <td>₱<?php echo $row['price']; ?></td>
                <td><?php echo $row['stock']; ?></td> 
                <td>
                    <a href="admin_update.php?edit=<?php echo $row['id']; ?>" class="btn"> <i class="fas fa-edit"></i> Edit </a>
                    <a href="admin_page.php?delete=<?php echo $row['id']; ?>" class="btn"> <i class="fas fa-trash"></i> Delete </a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

</div>

</body>
</html>