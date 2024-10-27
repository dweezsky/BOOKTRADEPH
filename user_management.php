<?php
@include 'config.php';

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    header('location:user_management.php');
}

if (isset($_POST['add_user'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'admin';

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $message[] = 'Please fill out all fields!';
    } else {
        $insert_user = "INSERT INTO users (first_name, last_name, email, password, role) VALUES ('$first_name', '$last_name', '$email', '$password', '$role')";
        if (mysqli_query($conn, $insert_user)) {
            $message[] = 'Admin added successfully!';
        } else {
            $message[] = 'Error: ' . mysqli_error($conn);
        }
    }
}

if (isset($_POST['update_user'])) {
    $id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    $update_query = "UPDATE users SET first_name='$first_name', last_name='$last_name', email='$email', role='$role'" .
        ($password ? ", password='$password'" : "") . " WHERE id='$id'";

    if (mysqli_query($conn, $update_query)) {
        header('location:user_management.php');
    } else {
        $message[] = 'Error: ' . mysqli_error($conn);
    }
}

$users = mysqli_query($conn, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="user_m.css">
    <title>User Management</title>
</head>
<body>

<header>
    <nav>
        <div class="nav-links">
            <a href="admin_page.php">BOOK MANAGEMENT</a>
            <a href="transaction_management.php">TRANSACTION MANAGEMENT</a>
            <a href="inventory.php">INVENTORY MANAGEMENT</a>
            <a href="user_management.php" class="active">USER MANAGEMENT</a>
        </div>
    </nav>
</header>

<div class="container">
    <?php if (isset($message)) {
        foreach ($message as $msg) {
            echo '<span class="message">' . htmlspecialchars($msg) . '</span>';
        }
    } ?>

    <button id="openAddModalBtn" class="btn">Add New Admin</button>

    <h3>Users List</h3>
    <div class="product-display">
        <table class="product-display-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users)): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['first_name']; ?></td>
                    <td><?php echo $user['last_name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['role']; ?></td>
                    <td><?php echo $user['created_at']; ?></td>
                    <td>
                        <button class="btn edit-btn" data-id="<?php echo $user['id']; ?>" 
                            data-first-name="<?php echo $user['first_name']; ?>"
                            data-last-name="<?php echo $user['last_name']; ?>"
                            data-email="<?php echo $user['email']; ?>"
                            data-role="<?php echo $user['role']; ?>">Edit</button>
                        <a href="user_management.php?delete=<?php echo $user['id']; ?>" class="btn">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Admin Modal -->
<div id="addAdminModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Add New Admin</h3>
        <form action="" method="post">
            <input type="text" class="box" name="first_name" placeholder="Enter First Name" required>
            <input type="text" class="box" name="last_name" placeholder="Enter Last Name" required>
            <input type="email" class="box" name="email" placeholder="Enter Email" required>
            <input type="password" class="box" name="password" placeholder="Enter Password" required>
            <input type="hidden" name="role" value="admin">
            <input type="submit" value="Add Admin" class="btn" name="add_user">
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Edit User</h3>
        <form action="" method="post">
            <input type="hidden" name="user_id" id="editUserId">
            <input type="text" class="box" name="first_name" id="editFirstName" required>
            <input type="text" class="box" name="last_name" id="editLastName" required>
            <input type="email" class="box" name="email" id="editEmail" required>
         
            <select name="role" id="editRole" class="box">
                <option value="buyer">Buyer</option>
                <option value="seller">Seller</option>
                <option value="admin">Admin</option>
            </select>
            <input type="submit" value="Update User" class="btn" name="update_user">
        </form>
    </div>
</div>

<script>
    // Modal handling
    const addModal = document.getElementById("addAdminModal");
    const editModal = document.getElementById("editUserModal");
    const openAddBtn = document.getElementById("openAddModalBtn");
    const closeBtns = document.querySelectorAll(".close");
    const editButtons = document.querySelectorAll(".edit-btn");

    openAddBtn.onclick = () => addModal.style.display = "block";

    closeBtns.forEach(btn => {
        btn.onclick = () => {
            addModal.style.display = "none";
            editModal.style.display = "none";
        };
    });

    window.onclick = (event) => {
        if (event.target === addModal || event.target === editModal) {
            addModal.style.display = "none";
            editModal.style.display = "none";
        }
    };

    editButtons.forEach(button => {
        button.onclick = function () {
            const userId = this.getAttribute("data-id");
            const firstName = this.getAttribute("data-first-name");
            const lastName = this.getAttribute("data-last-name");
            const email = this.getAttribute("data-email");
            const role = this.getAttribute("data-role");

            document.getElementById("editUserId").value = userId;
            document.getElementById("editFirstName").value = firstName;
            document.getElementById("editLastName").value = lastName;
            document.getElementById("editEmail").value = email;
            document.getElementById("editRole").value = role;

            editModal.style.display = "block";
        };
    });
</script>

</body>
</html>