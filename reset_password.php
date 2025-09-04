<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset'])) {
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.history.back();</script>";
        exit();
    }

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit();
    }

    // Check if the email exists
    $check_query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('No user found with that email.'); window.history.back();</script>";
        exit();
    }

    // Hash and update the password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_query = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ss", $hashed_password, $email);

    if ($stmt->execute()) {
        // Redirect to login page with success message
        header("Location: signin_signup.html?reset=success");
        exit();
    } else {
        echo "<script>alert('Failed to reset password. Please try again.'); window.history.back();</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='reset_password.html';</script>";
    exit();
}
?>