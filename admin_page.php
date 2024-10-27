<?php
@include 'config.php';
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: admin_page.php');
    exit;
}

// Fetch admin account details
$user_id = $_SESSION['user_id'];
$admin_query = mysqli_query($conn, "SELECT first_name, last_name, email FROM users WHERE id = '$user_id'");
$admin = mysqli_fetch_assoc($admin_query);

// Fetch notifications and unread count
$notifications_query = mysqli_query($conn, "
    SELECT 
        orders.id AS order_id, 
        GROUP_CONCAT(products.name SEPARATOR ', ') AS product_names, 
        orders.status 
    FROM orders
    INNER JOIN order_items ON orders.id = order_items.order_id
    INNER JOIN products ON order_items.product_id = products.id
    WHERE orders.status IN ('Canceled', 'Received')
    GROUP BY orders.id, orders.status
    ORDER BY orders.created_at DESC
");

// Test the unread notification count query
$unread_query = mysqli_query($conn, "
    SELECT COUNT(*) AS unread_count 
    FROM orders 
    WHERE is_read = 0 AND status IN ('Canceled', 'Received')
");

if (!$unread_query) {
    die("SQL Error: " . mysqli_error($conn));
}

$unread_result = mysqli_fetch_assoc($unread_query);
$unread_count = $unread_result['unread_count'];



// Add product logic
if (isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = $_POST['product_price'];
    $product_stock = $_POST['product_stock'];
    $product_description = mysqli_real_escape_string($conn, $_POST['product_description']);
    $product_image = $_FILES['product_image']['name'];
    $product_image_tmp_name = $_FILES['product_image']['tmp_name'];
    $product_image_folder = 'uploaded_img/' . $product_image;

    if (!file_exists('uploaded_img')) {
        mkdir('uploaded_img', 0777, true);
    }

    if (empty($product_name) || empty($product_price) || empty($product_stock) || empty($product_image) || empty($product_description)) {
        $message[] = 'Please fill out all fields';
    } else {
        $insert = "INSERT INTO products (name, price, stock, description, image) 
                   VALUES ('$product_name', '$product_price', '$product_stock', '$product_description', '$product_image')";
        $upload = mysqli_query($conn, $insert);

        if ($upload && move_uploaded_file($product_image_tmp_name, $product_image_folder)) {
            $message[] = 'New product added successfully';
        } else {
            $message[] = 'Could not add the product to the database';
        }
    }
}

// Delete product logic
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <link rel="stylesheet" href="adminstyle.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        nav {
            width: 100%;
      
            padding: 10px 0;
            text-align: center;
            position: relative;
        }

        .nav-links a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-size: 18px;
        }

        .nav-links a:hover {
            color: #f1c40f;
        }

        .notification-bell {
    position: relative;
    display: inline-block;
    cursor: pointer;
    font-size: 25px;
    color: white;
}
.profile-icon {
    position: relative;
    display: inline-block;
    cursor: pointer;
    font-size: 25px;
    color: white;

}

.notification-badge {
    position: absolute;
    top: -8px;
    right: -10px;
    background-color: red;
    color: white;
    border-radius: 50%;
    padding: 3px 8px;
    font-size: 10px;
    font-weight: bold;
    transition: opacity 0.3s ease-out;
}

.notification-badge.hidden {
    opacity: 0;
    visibility: hidden;
}

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
        }
    </style>
<body>

<nav>
    <div class="nav-links">
        <a href="admin_page.php" class="active">BOOK MANAGEMENT</a>
        <a href="transaction_management.php">TRANSACTION MANAGEMENT</a>
        <a href="inventory.php">INVENTORY MANAGEMENT</a>
        <a href="user_management.php">USER MANAGEMENT</a>
        <div class="notification-bell" onclick="openNotificationModal()">
            <i class="fa-regular fa-bell"></i>
            <?php if ($unread_count > 0) { ?>
                <span class="notification-badge"><?php echo $unread_count; ?></span>
            <?php } ?>
        </div>
        <div class="profile-icon" onclick="openProfileModal()">
      
            <i class="fa-regular fa-user"></i>

    </div>
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
                <input type="text" name="product_name" class="box" placeholder="Enter product name" required>
                <input type="number" name="product_price" class="box" placeholder="Enter product price" required>
                <input type="number" name="product_stock" class="box" placeholder="Enter product stock" required>
                <textarea name="product_description" class="box" placeholder="Enter product description" required></textarea>
                <input type="file" name="product_image" class="box" accept="image/png, image/jpeg, image/jpg" required>
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
                <th>Product Description</th>
                <th>Action</th>
            </tr>
        </thead>
        <?php while ($row = mysqli_fetch_assoc($select)) { ?>
        <tr>
            <td><img src="uploaded_img/<?php echo $row['image']; ?>" height="100" alt=""></td>
            <td><?php echo $row['name']; ?></td>
            <td>₱<?php echo $row['price']; ?></td>
            <td><?php echo $row['stock']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td>
                <a href="admin_update.php?edit=<?php echo $row['id']; ?>" class="btn"><i class="fas fa-edit"></i> Edit</a>
                <a href="admin_page.php?delete=<?php echo $row['id']; ?>" class="btn"><i class="fas fa-trash"></i> Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
<div class="container1">


    <div id="notificationModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeNotificationModal()">&times;</span>
            <h3>Notifications</h3>
            <div id="notificationContent">
                <?php if (mysqli_num_rows($notifications_query) > 0) { 
                    while ($notification = mysqli_fetch_assoc($notifications_query)) { 
                        $message = ($notification['status'] == 'Canceled') 
                            ? "Order canceled: {$notification['product_names']}" 
                            : "Order received: {$notification['product_names']}";
                ?>
                <div>
                    <p><?php echo $message; ?></p>
                    <p><small>Order ID: <?php echo $notification['order_id']; ?></small></p>
                </div>
                <?php } 
                } else { ?>
                    <p>No notifications available.</p>
                <?php } ?>
            </div>
        </div>
    </div>

    <div id="profileModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeProfileModal()">&times;</span>
            <h3>Admin Information</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></p>
            <button class="btn btn-logout" onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </div>
</div>

</div>
<script>
 function openNotificationModal() {
    // Open the modal
    document.getElementById('notificationModal').style.display = 'flex';

    // Send AJAX request to mark notifications as read
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'mark_notifications_read.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log('Notifications marked as read');
            // Hide the badge after marking notifications as read
            document.querySelector('.notification-badge').style.display = 'none';
        }
    };
    xhr.send();
}

function closeNotificationModal() {
    document.getElementById('notificationModal').style.display = 'none';
}
    function openProfileModal() {
        document.getElementById('profileModal').style.display = 'flex';
    }

    function closeProfileModal() {
        document.getElementById('profileModal').style.display = 'none';
    }
</script>
</body>
</html>
