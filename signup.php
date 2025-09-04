<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tem");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);  // assuming you have username field
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Hash the password before saving
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Signup successful! Please login.'); window.location.href='signin_signup.html';</script>";
        exit();
    } else {
        echo "<script>alert('Error during signup. Please try again.'); window.location.href='signup.html';</script>";
        exit();
    }
}
?>