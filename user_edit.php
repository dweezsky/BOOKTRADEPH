<?php
@include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $id"));
}

if (isset($_POST['update_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if (empty($name) || empty($email)) {
        $message[] = 'Please fill out all fields!';
    } else {
        $update_user = "UPDATE users SET name='$name', email='$email', role='$role'" . ($password ? ", password='$password'" : "") . " WHERE id='$id'";
        if (mysqli_query($conn, $update_user)) {
            header('location:user_management.php');
        } else {
            $message[] = 'Error: ' . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 400px;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .admin-product-form-container {
            display: flex;
            flex-direction: column;
        }
        input.box, select.box {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .message {
            display: block;
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <?php if (isset($message)) {
        foreach ($message as $msg) {
            echo '<span class="message">' . htmlspecialchars($msg) . '</span>';
        }
    } ?>
    <h3>Edit User Information</h3>
    <div class="admin-product-form-container">
        <form action="" method="post">
            <input type="text" class="box" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            <input type="email" class="box" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <input type="password" class="box" name="password" placeholder="Leave blank to keep current password">
            <select name="role" class="box">
                <option value="buyer" <?php echo $user['role'] === 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                <option value="seller" <?php echo $user['role'] === 'seller' ? 'selected' : ''; ?>>Seller</option>
                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
            <input type="submit" value="Update User" name="update_user" class="btn">
        </form>
    </div>
</div>
</body>
</html>