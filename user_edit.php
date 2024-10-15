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
    <link rel="stylesheet" href="css/style.css">
    <title>Edit User</title>
</head>
<body>
<div class="container">
    <?php if (isset($message)) {
        foreach ($message as $message) {
            echo '<span class="message">' . $message . '</span>';
        }
    } ?>
    <h3>Edit User Information</h3>
    <div class="admin-product-form-container">
        <form action="" method="post">
            <input type="text" class="box" name="name" value="<?php echo $user['name']; ?>" required>
            <input type="email" class="box" name="email" value="<?php echo $user['email']; ?>" required>
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