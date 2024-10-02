<?php
session_start();

// Database connection
$host = 'localhost';
$dbUsername = 'root';  // Replace with your database username
$dbPassword = '';  // Replace with your database password
$dbName = 'booktradedb';

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Register User
if (isset($_POST['register'])) {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);  // Hashing the password for security

    $query = "INSERT INTO users (first_name, last_name, email, password) VALUES ('$firstname', '$lastname', '$email', '$password')";

    if ($conn->query($query) === TRUE) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }
}

// Login User
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $row['password'])) {
            $_SESSION['user'] = $row['first_name'];  // Store the user's first name in session
            header("Location: homepage.html");  // Redirect to the homepage
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found with that email!";
    }
}

$conn->close();
?>