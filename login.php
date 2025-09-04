<?php
session_start();
$conn = new mysqli("localhost", "root", "", "tem");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password against hashed password stored
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password!'); window.location.href='signin_signup.html';</script>";
            exit();
        }
    } else {
        echo "<script>alert('User not found!'); window.location.href='signin_signup.html';</script>";
        exit();
    }
}
?>