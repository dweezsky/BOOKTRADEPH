<?php
session_start();

$host = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName = 'booktradedb';

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$adminEmail = 'admin@example.com';
$adminPassword = password_hash('adminpassword', PASSWORD_BCRYPT);

$adminQuery = "SELECT * FROM users WHERE email = '$adminEmail'";
$adminResult = $conn->query($adminQuery);

if ($adminResult->num_rows == 0) {
    $insertAdminQuery = "INSERT INTO users (first_name, last_name, email, password, role) 
                         VALUES ('Admin', 'User', '$adminEmail', '$adminPassword', 'admin')";
    $conn->query($insertAdminQuery);
}


if (isset($_POST['register'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $query = "INSERT INTO users (first_name, last_name, email, password, role) 
              VALUES ('$firstname', '$lastname', '$email', '$password', 'user')";

    if ($conn->query($query) === TRUE) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }
}


if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
   
            session_unset(); 
            session_destroy();


            session_start();

            $_SESSION['user_id'] = $row['id']; 
            $_SESSION['user_name'] = $row['first_name']; 
            $_SESSION['role'] = $row['role'];

      
            if ($row['role'] == 'admin') {
                header("Location: admin_page.php");
            } else {
                header("Location: homepage.php");
            }
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found with that email!";
    }
}


if (isset($_POST['logout'])) {
    session_unset(); 
    session_destroy();

    echo "<script>
        localStorage.removeItem('cart');
        localStorage.removeItem('liked');
        window.location.href = 'index(2).html'; // Redirect to login page after clearing localStorage
    </script>";
}

$conn->close();
?>